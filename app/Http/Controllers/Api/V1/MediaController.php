<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaLibraryRequest;
use App\Http\Resources\Media as MediaResource;
use App\Models\Media;
use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    /**
     * Return the comments.
     */
    public function index(Request $request): ResourceCollection
    {
        return MediaResource::collection(
            MediaLibrary::first()->media()->latest()->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediaLibraryRequest $request): MediaResource
    {
        $this->authorize('store', Media::class);

        $image = $request->file('image');
        $name = $image->getClientOriginalName();
        $description = "";

        if ($request->filled('name')) {
            $name = $request->input('name');
        }

        if ($request->filled('description')) {
            $description = $request->input('description');
        }

        return new MediaResource(
            MediaLibrary::first()
                ->addMedia($image)
                ->usingName($name)
                ->withCustomProperties(['description' => $description])
                ->toMediaCollection()
        );
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $medium): Response
    {
        $this->authorize('delete', $medium);

        $medium->delete();

        return response()->noContent();
    }
}
