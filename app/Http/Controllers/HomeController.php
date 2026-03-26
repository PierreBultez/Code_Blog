<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredArticles = Article::query()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->take(2)
            ->get();

        $featuredIds = $featuredArticles->pluck('id')->all();

        $recentArticles = Article::query()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->whereNotIn('id', $featuredIds)
            ->take(3)
            ->get();

        $stats = [
            'articles' => Article::query()->published()->count(),
            'tags' => Tag::query()->whereHas('articles', fn ($q) => $q->published())->count(),
        ];

        return view('home', compact('featuredArticles', 'recentArticles', 'stats'));
    }
}
