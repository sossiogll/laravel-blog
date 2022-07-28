<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Media as MediaResource;



class CarouselController extends Controller
{
    /**
     * Return the users.
     */
    public function index(Request $request, Post $post)//: ResourceCollection
    {
        return MediaResource::collection(
            $post->carousel()->get()
        );
    }


}
