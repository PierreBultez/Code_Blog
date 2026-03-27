<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TinyMceUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ]);

        $path = $request->file('file')->store('article-images', 'public');

        return response()->json([
            'location' => asset('storage/'.$path),
        ]);
    }
}
