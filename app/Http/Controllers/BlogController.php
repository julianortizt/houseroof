<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;

class BlogController extends Controller
{
    public function index()
    {
        $query = BlogPost::published()
            ->with(['category', 'tags', 'media']);

        if ($categoryId = request('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($tagSlug = request('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        $posts = $query->latest('published_at')->paginate(9)->withQueryString();
        $categories = Category::active()->roots()->withCount('blogPosts')->get();

        return view('pages.blog.index', compact('posts', 'categories'));
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->isPublished(), 404);

        $post->load(['category', 'tags', 'media']);

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->with('media')
            ->latest('published_at')
            ->limit(3)
            ->get();

        // Generate JSON-LD for blog post
        $jsonLdService = app(\App\Services\JsonLdService::class);
        $jsonLd = [
            $jsonLdService->blogPosting($post),
            $jsonLdService->breadcrumb([
                ['name' => 'Inicio', 'url' => config('app.url')],
                ['name' => 'Blog', 'url' => route('blog.index')],
                ['name' => $post->title, 'url' => route('blog.show', $post->slug)],
            ]),
        ];

        return view('pages.blog.show', compact('post', 'relatedPosts', 'jsonLd'));
    }
}
