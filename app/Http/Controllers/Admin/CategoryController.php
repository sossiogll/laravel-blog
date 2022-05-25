<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoriesRequest;
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
        $viewToShow = view('admin.categories.edit', [
            'category' => $category,
            'custom_fields_editable' => $category->areCustomFieldsEditable()
        ]);
        
        if(!$category->areCustomFieldsEditable())
            $viewToShow->withErrors(__('categories.not_editable'));

        return $viewToShow;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('admin.categories.create', [
            'custom_fields_editable' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriesRequest $request): RedirectResponse
    {
        $category = Category::create($request->only(['name', 'raw_custom_fields']));

        return redirect()->route('admin.categories.edit', $category)->withSuccess(__('categories.created'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoriesRequest $request, Category $category): RedirectResponse
    {
        if(!strcmp($request["custom_fields_raw"], $category->custom_fields) && $category->areCustomFieldsEditable())
            return redirect()->route('admin.categories.edit', $category);
        else{
            $category->update($request->only(['name', 'raw_custom_fields']));
            return redirect()->route('admin.categories.edit', $category)->withSuccess(__('categories.updated'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category  $category)
    {
        if($category->areCustomFieldsEditable()){
            $category->delete();
            return redirect()->route('admin.categories.index')->withSuccess(__('categories.deleted'));
        }
    }

}
