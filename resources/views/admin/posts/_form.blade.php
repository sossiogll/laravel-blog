@php
    $posted_at = old('posted_at') ?? (isset($post) ? $post->posted_at->format('Y-m-d\TH:i') : null);
@endphp

<!--<vue-select-image
  :dataImages="{{$media}}"
  @onselectimage="onSelectImage">
</vue-select-image>-->

<div class="form-group">
    {!! Form::label('title', __('posts.attributes.title')) !!}
    {!! Form::text('title', null, ['class' => 'form-control' . ($errors->has('title') ? ' is-invalid' : ''), 'required']) !!}

    @error('title')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>


    <div class="form-group">

        <category-selector
            name="category_id"
            id="category-id"
            categories-endpoint="{{route('categories.index')}}"
            custom-fields-endpoint="{{$post != null ? route('posts.customfields', $post) : null}}"
            selected="{{$post != null ? $post->category_id : ''}}"
            category-label="@lang('posts.attributes.category')"
            category-placeholder="@lang('posts.placeholder.category')">
        </category-selector>
    </div>




    <div class="form-group">
        {!! Form::label('thumbnail_id', __('posts.attributes.thumbnail')) !!}
        {!! Form::select('thumbnail_id', $media, null, ['placeholder' => __('posts.placeholder.thumbnail'), 'class' => 'form-control' . ($errors->has('thumbnail_id') ? ' is-invalid' : '')]) !!}

        @error('thumbnail_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>




    <div class="form-group">
        {!! Form::label('content', __('posts.attributes.content')) !!}
        {!! Form::textarea('content', null, ['class' => 'form-control trumbowyg-form' . ($errors->has('content') ? ' is-invalid' : '')]) !!}

        @error('content')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

