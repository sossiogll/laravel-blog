<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\MediaLibrary;
use Illuminate\Http\RedirectResponse;


class UserSecondaryProfilePictureController extends Controller
{
    /**
     * Unset the user's profile picture.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->update(['secondary_profile_picture_id' => null]);

        /*return redirect()->route('admin.users.edit', [
            'user' => $user,
            'positions' => $user->raw_positions_value,
            'roles' => Role::all(),
            'media' => MediaLibrary::first()->media()->get()->pluck('name', 'id')
        ] )->withSuccess(__('user.updated'));    */
        return redirect()->back();

    }
}
