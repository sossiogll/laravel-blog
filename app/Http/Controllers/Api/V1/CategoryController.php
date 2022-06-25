<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriesRequest;
use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Categories as CategoriesResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;

class CategoryController extends Controller
{
    /**
     * Return the category.
     */
    public function index(Request $request): ResourceCollection
    {
        return CategoriesResource::collection(
            Category::latest()->paginate($request->input('limit', 20))
        );
    }

    /**
     * Return the specified resource.
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

}
