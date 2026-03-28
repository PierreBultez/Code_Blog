<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class OgImageController extends Controller
{
    private const WIDTH = 1200;

    private const HEIGHT = 630;

    private const FONT_BOLD_PATH = __DIR__.'/../../../storage/fonts/DejaVuSans-Bold.ttf';

    private const ALLOWED_WIDTHS = [400, 800, 1200];

    public function __invoke(Article $article, Request $request): Response
    {
        abort_unless($article->is_published, 404);

        $width = $this->resolveWidth($request);
        $cacheKey = "og-image-{$article->id}-{$article->updated_at->timestamp}-w{$width}";

        $imageData = Cache::remember($cacheKey, now()->addYear(), function () use ($article, $width) {
            $fullImage = $this->generateImage($article);

            if ($width < self::WIDTH) {
                return $this->resizeImage($fullImage, $width);
            }

            return $fullImage;
        });

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function resolveWidth(Request $request): int
    {
        $w = (int) $request->query('w', self::WIDTH);

        return in_array($w, self::ALLOWED_WIDTHS) ? $w : self::WIDTH;
    }

    private function resizeImage(string $imageData, int $targetWidth): string
    {
        $image = imagecreatefromstring($imageData);
        $targetHeight = (int) round($targetWidth * self::HEIGHT / self::WIDTH);
        $resized = imagescale($image, $targetWidth, $targetHeight);

        ob_start();
        imagepng($resized, null, 9);
        $data = ob_get_clean();
        imagedestroy($image);
        imagedestroy($resized);

        return $data;
    }

    private function generateImage(Article $article): string
    {
        $width = self::WIDTH;
        $height = self::HEIGHT;

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, true);

        // Fond dégradé vertical : #000000 → #920021
        $this->drawVerticalGradient($canvas, $width, $height, [
            ['r' => 0, 'g' => 0, 'b' => 0],         // Noir (haut)
            ['r' => 146, 'g' => 0, 'b' => 33],      // Rouge profond (bas)
        ]);

        // Texte principal avec dégradé horizontal
        $text = $article->og_text ?: $article->title;
        $lines = $this->wrapText($text, 20);
        $fontSize = 64;
        $lineHeight = 84;
        $totalTextHeight = count($lines) * $lineHeight;
        $startY = (int) (($height - $totalTextHeight) / 2 + $fontSize / 2);

        // Couleurs du dégradé texte : #FFE17A → #FD5561
        $textColorFrom = ['r' => 255, 'g' => 225, 'b' => 122];  // Or/jaune
        $textColorTo = ['r' => 253, 'g' => 85, 'b' => 97];      // Corail/rouge

        foreach ($lines as $i => $line) {
            $y = $startY + ($i * $lineHeight);
            $this->drawGradientText($canvas, $line, $fontSize, $y, $width, $textColorFrom, $textColorTo);
        }

        ob_start();
        imagepng($canvas, null, 7);
        $data = ob_get_clean();
        imagedestroy($canvas);

        return $data;
    }

    /**
     * Dégradé vertical multi-stop.
     *
     * @param  array<int, array{r: int, g: int, b: int}>  $stops
     */
    private function drawVerticalGradient(\GdImage $image, int $width, int $height, array $stops): void
    {
        $segmentCount = count($stops) - 1;
        $segmentHeight = $height / $segmentCount;

        for ($y = 0; $y < $height; $y++) {
            $segment = min((int) ($y / $segmentHeight), $segmentCount - 1);
            $localProgress = ($y - ($segment * $segmentHeight)) / $segmentHeight;

            $from = $stops[$segment];
            $to = $stops[$segment + 1];

            $r = (int) ($from['r'] + ($to['r'] - $from['r']) * $localProgress);
            $g = (int) ($from['g'] + ($to['g'] - $from['g']) * $localProgress);
            $b = (int) ($from['b'] + ($to['b'] - $from['b']) * $localProgress);

            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width - 1, $y, $color);
        }
    }

    /**
     * Texte avec dégradé horizontal dans les lettres (approche par masque).
     *
     * @param  array{r: int, g: int, b: int}  $colorFrom
     * @param  array{r: int, g: int, b: int}  $colorTo
     */
    private function drawGradientText(
        \GdImage $canvas,
        string $text,
        int $fontSize,
        int $y,
        int $canvasWidth,
        array $colorFrom,
        array $colorTo,
    ): void {
        // Calculer la taille du texte pour le centrer
        $bbox = imagettfbbox($fontSize, 0, self::FONT_BOLD_PATH, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);
        $textHeight = abs($bbox[7] - $bbox[1]);
        $x = (int) (($canvasWidth - $textWidth) / 2);

        // Marges autour du texte dans le masque
        $padX = 20;
        $padY = 30;
        $maskWidth = $textWidth + $padX * 2;
        $maskHeight = $textHeight + $padY * 2;
        $textX = $padX;
        $textY = $padY + $textHeight;

        // 1. Créer un calque dégradé horizontal
        $gradient = imagecreatetruecolor($maskWidth, $maskHeight);
        for ($px = 0; $px < $maskWidth; $px++) {
            $progress = $maskWidth > 1 ? $px / ($maskWidth - 1) : 0;
            $r = (int) ($colorFrom['r'] + ($colorTo['r'] - $colorFrom['r']) * $progress);
            $g = (int) ($colorFrom['g'] + ($colorTo['g'] - $colorFrom['g']) * $progress);
            $b = (int) ($colorFrom['b'] + ($colorTo['b'] - $colorFrom['b']) * $progress);
            $color = imagecolorallocate($gradient, $r, $g, $b);
            imageline($gradient, $px, 0, $px, $maskHeight - 1, $color);
        }

        // 2. Créer le masque texte (blanc sur noir)
        $mask = imagecreatetruecolor($maskWidth, $maskHeight);
        $black = imagecolorallocate($mask, 0, 0, 0);
        $white = imagecolorallocate($mask, 255, 255, 255);
        imagefill($mask, 0, 0, $black);
        imagettftext($mask, $fontSize, 0, $textX, $textY, $white, self::FONT_BOLD_PATH, $text);

        // 3. Combiner : copier les pixels du dégradé sur le canvas là où le masque est non-noir
        imagealphablending($canvas, true);

        for ($px = 0; $px < $maskWidth; $px++) {
            for ($py = 0; $py < $maskHeight; $py++) {
                $maskPixel = imagecolorat($mask, $px, $py);
                $maskBrightness = ($maskPixel >> 16) & 0xFF;

                if ($maskBrightness > 10) {
                    $gradPixel = imagecolorat($gradient, $px, $py);
                    $gr = ($gradPixel >> 16) & 0xFF;
                    $gg = ($gradPixel >> 8) & 0xFF;
                    $gb = $gradPixel & 0xFF;

                    // Alpha basé sur la luminosité du masque (anti-aliasing)
                    $alpha = (int) (127 - ($maskBrightness / 255) * 127);
                    $finalColor = imagecolorallocatealpha($canvas, $gr, $gg, $gb, $alpha);

                    $destX = $x - $padX + $px;
                    $destY = $y - $textHeight - $padY + $py;
                    imagesetpixel($canvas, $destX, $destY, $finalColor);
                }
            }
        }

        imagedestroy($mask);
        imagedestroy($gradient);
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $text, int $maxCharsPerLine): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? "{$currentLine} {$word}" : $word;

            if (mb_strlen($testLine) > $maxCharsPerLine && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return array_slice($lines, 0, 3);
    }
}
