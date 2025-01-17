<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersRequest;
use App\Http\Resources\User as UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Return the users.
     */
    public function index(Request $request)//: ResourceCollection
    {
        return UserResource::collection(
           
        User::
        whereNotIn('id' ,
            User::
            select('id')
            ->whereHas('roles', function ($query) {
                $query->where('roles.name', Role::ROLE_ADMIN);
            })
        )->get()

        );
    }

    /**
     * Return the specified resource.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UsersRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        if ($request->filled('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password'))
            ]);
        }

        $user->update(array_filter($request->only(['name', 'email', 'password'])));

        return new UserResource($user);
    }
}
