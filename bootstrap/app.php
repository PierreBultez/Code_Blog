<?php

use App\Http\Controllers\OgImageController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(SubstituteBindings::class)
                ->get('/articles/{article:slug}/og-image.png', OgImageController::class)
                ->name('articles.og-image');

            Route::get('/sitemap.xml', SitemapController::class)
                ->name('sitemap');

            Route::get('/feed', RssFeedController::class)
                ->name('feed');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
