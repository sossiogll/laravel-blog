<?php

namespace App\Http\Requests\Admin;

use App\Rules\CanBeAuthor;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class NewPostsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('title'))
        ]);

        $this->merge([
            'posted_at' => Carbon::parse($this->input('posted_at'))
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'category_id' => 'required',
            'posted_at' => 'required|date',
            'language' => 'required',
            'slug' => 'unique:posts,slug,' . (optional($this->post)->id ?: 'NULL'),
        ];
    }
}
