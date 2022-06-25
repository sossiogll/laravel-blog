<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsersRequest;
use App\Http\Requests\Admin\NewUsersRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\MediaLibrary;




class UserController extends Controller
{
    /**
     * Show the application users index.
     */
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::latest()->paginate(50)
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(User $user): View
    {

        if($user->isCurrentUser()){

            $user = auth()->user();
            $this->authorize('update', $user);
            return view('users.edit', [
                'user' => $user,
                'roles' => Role::all(),
                'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id')
            ]);
        }
        else
            return view('admin.users.edit', [
                'user' => $user,
                'roles' => Role::all(),
                'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id')
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UsersRequest $request, User $user): RedirectResponse
    {
        if ($request->filled('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password'))
            ]);
        }

        $user->update($request->only(['name', 'email', 'password', 'bio', 'raw_positions_value', 'authenticable', 'profile_picture_id',"secondary_profile_picture_id" ]));

        $role_ids = array_values($request->get('roles', []));
        $user->roles()->sync($role_ids);

        return redirect()->route('admin.users.edit', $user)->withSuccess(__('users.updated'));

    }

    public function store(NewUsersRequest $request): RedirectResponse
    {
        $user = User::create($request->only(['name', 'email', 'bio', 'raw_positions_value','profile_picture_id',"secondary_profile_picture_id" ]));

        $role_ids = array_values($request->get('roles', []));
        $user->roles()->sync($role_ids);
        
        return redirect()->route('admin.users.edit', [
            'user' => $user,
            'positions' => "",
            'roles' => Role::all(),
            'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id')
        ] )->withSuccess(__('user.created'));
   
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('admin.users.create', ['media' => MediaLibrary::first()->media()->get()->pluck('name', 'id')]);    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->withSuccess(__('user.deleted'));
    }
}
