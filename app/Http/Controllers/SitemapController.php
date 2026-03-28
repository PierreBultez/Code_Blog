<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController
{
    public function __invoke(): Response
    {
        $articles = Article::query()
            ->published()
            ->select(['slug', 'published_at', 'updated_at'])
            ->latest('published_at')
            ->get();

        $xml = view('sitemap', ['articles' => $articles])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
