<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $categories = Project::categoryOptions();
        $category = request()->query('category');
        $category = is_string($category) && array_key_exists($category, $categories) ? $category : '';
        $search = request()->query('q');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';

        $query = Project::active()
            ->with('media')
            ->orderBy('sort_order')
            ->latest();

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $projects = $query->paginate(12)->withQueryString();

        return view('pages.project.index', compact('projects', 'categories', 'category', 'search'));
    }

    public function show(Project $project)
    {
        $project->load('media');
        $categories = Project::categoryOptions();

        $relatedProjects = Project::active()
            ->where('id', '!=', $project->id)
            ->with('media')
            ->limit(4)
            ->get();

        return view('pages.project.show', compact('project', 'relatedProjects', 'categories'));
    }
}
