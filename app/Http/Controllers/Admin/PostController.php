<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostsRequest;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\CustomFields;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Show the application posts index.
     */
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::withCount('comments', 'likes')->with('author')->latest()->paginate(50)
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(Post $post): View
    {
        $custom_fields = null;
        $custom_fields_values = null;

        if(!is_null($post->category_id)){
            $custom_fields = Category::find($post->category_id)->fields;
        }

        return view('admin.posts.edit', [
            'post' => $post,
            'user' => Auth::user()->pluck('name', 'id'),
            'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id'),
            'categories' => Category::first()->get()->pluck('name', 'id'),
            'custom_fields' => $custom_fields,
            'custom_fields_value' => $custom_fields_values
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $custom_fields = null;
        $custom_fields_values = null;

        if(!count(Category::all())){
            return view('admin.categories.create')->withErrors(__('posts.no_categories'));;
        }
        
        return view('admin.posts.create', [
            'user' => Auth::user()->pluck('name', 'id'),
            'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id'),
            'categories' => Category::get()->pluck('name', 'id'),
            'default_category' => Category::all()->first()->id,
            'custom_fields' => Category::all()->first()->fields,
            'custom_fields_value' => $custom_fields_values
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostsRequest $request): RedirectResponse
    {
        $post = Post::create($request->only(['title', 'content', 'posted_at', 'author_id', 'thumbnail_id', 'category_id']));
        
        if(CustomFields::createOrUpdate($post, $request))
            return redirect()->route('admin.posts.edit', $post)->withSuccess(__('posts.updated'));
        else
            return redirect()->route('admin.posts.edit', $post)->withErrors(__('posts.custom_fields_update_error'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostsRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->only(['title', 'content', 'posted_at', 'author_id', 'thumbnail_id', 'category_id']));
        
        CustomFields::createOrUpdate($request, $post);
        return redirect()->route('admin.posts.edit', $post)->withSuccess(CustomFields::where([
            ['category_id', $post->category->id],
            ['post_id', $post->id]
        ])->get());
        if(CustomFields::createOrUpdate($request, $post))
            return redirect()->route('admin.posts.edit', $post)->withSuccess(__('posts.updated'));
        else
            return redirect()->route('admin.posts.edit', $post)->withErrors(__('posts.custom_fields_update_error'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post  $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->withSuccess(__('posts.deleted'));
    }
}
