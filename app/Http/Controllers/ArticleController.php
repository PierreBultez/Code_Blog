<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::query()
            ->published()
            ->with('tags')
            ->when($request->query('tag'), function ($query, $tagSlug) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        $tags = Tag::query()
            ->whereHas('articles', fn ($q) => $q->published())
            ->orderBy('name')
            ->get();

        $activeTag = $request->query('tag');

        return view('articles.index', compact('articles', 'tags', 'activeTag'));
    }

    public function show(Article $article): View
    {
        abort_unless($article->is_published, 404);

        $article->load('tags');

        $tagIds = $article->tags->pluck('id');

        $relatedArticles = $tagIds->isNotEmpty()
            ? Article::query()
                ->published()
                ->where('id', '!=', $article->id)
                ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
                ->withCount(['tags as shared_tags_count' => fn ($q) => $q->whereIn('tags.id', $tagIds)])
                ->orderByDesc('shared_tags_count')
                ->latest('published_at')
                ->with('tags')
                ->take(3)
                ->get()
            : collect();

        return view('articles.show', compact('article', 'relatedArticles'));
    }
}
