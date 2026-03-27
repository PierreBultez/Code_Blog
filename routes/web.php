<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TinyMceUploadController;
use App\Livewire\Dashboard\ArticleForm;
use App\Livewire\Dashboard\ArticleList;
use App\Livewire\Dashboard\TagForm;
use App\Livewire\Dashboard\TagList;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::redirect('/register', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::post('/tinymce/upload', TinyMceUploadController::class)->name('tinymce.upload');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/articles', ArticleList::class)->name('articles.index');
        Route::get('/articles/create', ArticleForm::class)->name('articles.create');
        Route::get('/articles/{article}/edit', ArticleForm::class)->name('articles.edit');

        Route::get('/tags', TagList::class)->name('tags.index');
        Route::get('/tags/create', TagForm::class)->name('tags.create');
        Route::get('/tags/{tag}/edit', TagForm::class)->name('tags.edit');
    });
});

require __DIR__.'/settings.php';
