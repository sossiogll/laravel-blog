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



class UserProfilePictureController extends Controller
{
    /**
     * Unset the user's profile picture.
     */
    public function destroy(User $user)
    {

        //var_dump($user->id);
        //return redirect()->route('admin.users.edit', $user)->withSuccess(__('users.updated'));
        return redirect()->back();
    }
}
