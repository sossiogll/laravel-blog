<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\MediaLibrary;
use App\Models\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{

    /**
     * Display the specified resource edit form.
     */
    public function edit(Settings $settings): View
    {

        return view('admin.settings.edit', [
            'settings' => Settings::first()
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(SettingsRequest $request): RedirectResponse
    {
        
        
        if ($request->has('localization')) {
            $settings = Settings::query()->update(['localization' => true]);
        }
        else{
            $settings = Settings::query()->update(['localization' => false]);
        }

        return redirect()->route('admin.settings.edit', $settings)->withSuccess(__('settings.updated'));
        
    }




}
