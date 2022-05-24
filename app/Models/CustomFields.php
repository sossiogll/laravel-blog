<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Post;


class CustomFields extends Model
{
    use HasFactory;

    protected $table = 'custom_fields_post_category';

    protected $fillable = [
        'custom_fields_values',
        'category_id',
        'post_id',
    ];
    

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }


    public static function createOrUpdate($request, $post){

        $category = Category::find($request->category_id);

        $customFieldsValues = CustomFields::where([
            ['category_id', $category->id],
            ['post_id', $post->id]
        ]);

        $fields = $category->fields;

        $filled_fields = array();
        
        for($i = 0; $i<count($fields); $i++){

            $filled_fields[$fields[$i]["id"]] = $request[$fields[$i]["id"]];
        }

        if($customFieldsValues->count() == 1){

            return $customFieldsValues->update([
                'category_id' => $request->category_id,
                'post_id' => $post->id,
                'custom_fields_values' => json_encode($filled_fields)
            ]);
        }
        else
            return (new static)::create([
                'category_id' => $request['category_id'],
                'post_id' => $post->id,
                'custom_fields_values' => json_encode($filled_fields)
            ]);

    }

    public function getCustomFieldsAttribute(){
        json_decode();
    }




}
