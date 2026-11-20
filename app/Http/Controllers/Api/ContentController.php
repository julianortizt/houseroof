<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function page(string $slug)
    {
        $page = Page::where('slug', $slug)->first();

        if (! $page) {
            return response()->json(['message' => 'Página no encontrada.'], 404);
        }

        return response()->json([
            'slug' => $page->slug,
            'title' => $page->title,
            'content' => $page->content,
            'image' => $page->getFirstMediaUrl('default') ?: null,
        ]);
    }

    public function blogIndex()
    {
        $posts = BlogPost::query()
            ->with('category')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(12);

        return response()->json([
            'data' => collect($posts->items())->map(function ($post) {
                return [
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'image' => $post->getFirstMediaUrl('default') ?: null,
                    'category' => $post->category?->name,
                    'published_at' => $post->published_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function blogShow(string $slug)
    {
        $post = BlogPost::with('category')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $post) {
            return response()->json(['message' => 'Artículo no encontrado.'], 404);
        }

        return response()->json([
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'image' => $post->getFirstMediaUrl('default') ?: null,
            'category' => $post->category?->name,
            'published_at' => $post->published_at?->toIso8601String(),
        ]);
    }
}
