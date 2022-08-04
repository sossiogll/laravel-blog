<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostsRequest;
use App\Http\Requests\Admin\NewPostsRequest;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\User;
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

        if(!count(Category::all())){
            return view('admin.categories.create')->withErrors(__('posts.no_categories'));;
        }

        return view('admin.posts.edit', [
            'post' => $post,
            'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {

        if(!count(Category::all())){
            return view('admin.categories.create')->withErrors(__('posts.no_categories'));;
        }

        return view('admin.posts.create', [
            'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id'),
            'post' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewPostsRequest $request): RedirectResponse
    {

        $post = Post::create([
            'title' => $request['title'],
            'posted_at' => now(),
            'category_id' => $request['category_id'],
            'author_id' => Auth::id(),
            'content' => $request['content'],
            'summary_content' => $request['summary_content'],
        ]);

        $post->categories()->attach($request['category_id'], ['raw_custom_fields_values' => $this->generateJsonFilledFields($request, $post)]);
        
        if ($request->has('carousel')) {
            forEach($request['carousel'] as $image_id){
                $post->carousel()->attach($image_id);
            }
        }

        return redirect()->route('admin.posts.edit', $post)->withSuccess(__('posts.created'));


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostsRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->only(['title', 'content', 'posted_at', 'author_id', 'thumbnail_id', 'category_id', 'summary_content']));

        if ($request->has('carousel')) {
            $post->carousel()->sync([]);

            forEach($request['carousel'] as $image_id){
                $post->carousel()->attach($image_id);
            }
         }   
        
        if($this->isPostAlreadyAttachedToCategory($request, $post))
            $post->categories()->updateExistingPivot($request['category_id'], ['raw_custom_fields_values' => $this->generateJsonFilledFields($request, $post)]);
        else
            $post->categories()->attach($request['category_id'], ['raw_custom_fields_values' => $this->generateJsonFilledFields($request, $post)]);

        return redirect()->route('admin.posts.edit', $post)->withSuccess(__('posts.updated'));
       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->withSuccess(__('posts.deleted'));
    }

    private function generateFilledFields($request, Post $post){

        $filled_fields = array();
        $category = Category::find($request->category_id);
        $fields = $category->customFields;
        
        for($i = 0; $i<count($fields); $i++){

            $filled_fields[$fields[$i]["id"]] = $request[$fields[$i]["id"]];
        }

        return $filled_fields;
    }

    private function generateJsonFilledFields($request, Post $post){

        return json_encode($this->generateFilledFields($request, $post));

    }

    private function isPostAlreadyAttachedToCategory(PostsRequest $request,Post $post){

        return $post->categories()->where('category_id', $request['category_id'])->count() == 1;

    }

}
