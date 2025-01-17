<?php

use Illuminate\Support\Facades\Route;

Route::get('dashboard', 'ShowDashboard')->name('dashboard');
Route::resource('posts', 'PostController');
Route::delete('/posts/{post}/thumbnail', 'PostThumbnailController@destroy')->name('posts_thumbnail.destroy');
Route::resource('users', 'UserController')->only(['index', 'edit', 'update', 'create', 'store', 'destroy']);
Route::delete('/posts/{post}/profilePicture', 'UserProfilePictureController@destroy')->name('users_profilePicture.destroy');
Route::delete('/posts/{post}/secondaryProfilePicture', 'UserSecondaryProfilePictureController@destroy')->name('users_secondaryProfilePicture.destroy');
Route::resource('comments', 'CommentController')->only(['index', 'edit', 'update', 'destroy']);
Route::resource('media', 'MediaLibraryController')->only(['index','edit', 'update', 'show', 'create', 'store', 'destroy']);
Route::resource('categories', 'CategoryController');
Route::get('settings', 'SettingsController@edit')->name('settings.edit');
Route::put('settings/update', 'SettingsController@update')->name('settings.update');;