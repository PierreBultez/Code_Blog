<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Article::query()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->first();

        $recentArticles = Article::query()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->when($featured, fn ($query) => $query->where('id', '!=', $featured->id))
            ->take(3)
            ->get();

        $stats = [
            'articles' => Article::query()->published()->count(),
            'tags' => Tag::query()->whereHas('articles', fn ($q) => $q->published())->count(),
        ];

        return view('home', compact('featured', 'recentArticles', 'stats'));
    }
}
