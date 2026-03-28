<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class RssFeedController
{
    public function __invoke(): Response
    {
        $articles = Article::query()
            ->published()
            ->select(['title', 'slug', 'excerpt', 'published_at'])
            ->latest('published_at')
            ->take(20)
            ->get();

        $xml = view('feed', ['articles' => $articles])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
