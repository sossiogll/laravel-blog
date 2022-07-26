<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomFields;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\CustomFields as CustomFieldsResource;



class CustomFieldsController extends Controller
{
    /**
     * Return the users.
     */
    public function index(Request $request, Post $post): ResourceCollection
    {
        return CustomFieldsResource::collection(
            CustomFields::where('post_id', $post->id)->get()
        );
    }

    /**
     * Return the specified resource.
     */
    public function show(Request $request, Post $post, Category $category): CustomFieldResource
    {

        $customFields = CustomFields::where([
            'post_id' => $post->id,
            'category_id' => $category->id,
        ])->get();

        return new CustomFieldsResource(
            $customFields
        );
    }

}
