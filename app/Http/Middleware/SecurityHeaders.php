<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $nonce = Vite::cspNonce();

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $viteDevServer = $this->viteDevServerUrl();

        $csp = implode('; ', array_filter([
            "default-src 'self'",
            "script-src 'nonce-{$nonce}' 'strict-dynamic'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net"
                .($viteDevServer ? " {$viteDevServer}" : ''),
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data:",
            "connect-src 'self'"
                .($viteDevServer ? " {$viteDevServer} ws://".parse_url($viteDevServer, PHP_URL_HOST).':'.parse_url($viteDevServer, PHP_URL_PORT) : ''),
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Get the Vite dev server URL if it is running.
     */
    private function viteDevServerUrl(): ?string
    {
        $hotFile = public_path('hot');

        if (! file_exists($hotFile)) {
            return null;
        }

        return rtrim(file_get_contents($hotFile));
    }
}
