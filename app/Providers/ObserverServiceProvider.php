<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Observers\CommentObserver;
use App\Observers\MediaObserver;
use App\Observers\PostObserver;
use App\Observers\UserObserver;
use App\Observers\CategoryObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);
        Category::observe(CategoryObserver::class);
        User::observe(UserObserver::class);
        Comment::observe(CommentObserver::class);
        Media::observe(MediaObserver::class);
    }
}
