<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryObserver
{
    /**
     * Listen to the Category saving event.
     */
    public function saving(Category $category)//: void
    {
        
        $category->slug = Str::slug($category->name, '-');

    }
}
