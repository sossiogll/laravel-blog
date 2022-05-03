<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostsRequest;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Show the application posts index.
     */
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('posts')->paginate(50)
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostsRequest $request): RedirectResponse
    {
        $category = Category::create($request->only(['name']));

        return redirect()->route('admin.categories.edit', $category)->withSuccess(__('categories.created'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostsRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->only(['name']));

        return redirect()->route('admin.categories.edit', $post)->withSuccess(__('categories.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category  $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->withSuccess(__('categories.deleted'));
    }
}
