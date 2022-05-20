@php
    $posted_at = old('posted_at') ?? (isset($post) ? $post->posted_at->format('Y-m-d\TH:i') : null);
@endphp

<div class="form-group">
    {!! Form::label('title', __('posts.attributes.title')) !!}
    {!! Form::text('title', null, ['class' => 'form-control' . ($errors->has('title') ? ' is-invalid' : ''), 'required']) !!}

    @error('title')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-row">

    <div class="form-group col-md-4">
        {!! Form::label('category_id', __('posts.attributes.category')) !!}
        @if(isset($post))
            {!! Form::select('category_id', $categories, $post->category_id, ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
        @else
            {!! Form::select('category_id', $categories, null, ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
        @endif


        @error('category_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group col-md-4">
        {!! Form::label('author_id', __('posts.attributes.author')) !!}
        {!! Form::select('author_id', $user, null, ['class' => 'form-control' . ($errors->has('author_id') ? ' is-invalid' : ''), 'required', 'readonly']) !!}

        @error('author_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>



</div>

<div class="form-group">
    {!! Form::label('thumbnail_id', __('posts.attributes.thumbnail')) !!}
    {!! Form::select('thumbnail_id', $media, null, ['placeholder' => __('posts.placeholder.thumbnail'), 'class' => 'form-control' . ($errors->has('thumbnail_id') ? ' is-invalid' : '')]) !!}

    @error('thumbnail_id')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>





    @if($custom_fields != null)


        <p><b> @lang('categories.attributes.custom_fields') </b></p>
        @for($i=0; $i < count($custom_fields); $i++)

            <div class="form-group">
                {!! Form::label($custom_fields[$i]['description'], __($custom_fields[$i]['description'])) !!}
                {!! Form::text($custom_fields[$i]['id'], null, ['class' => 'form-control']) !!}
            </div>

        @endfor

    @endif




<div class="form-group">
    {!! Form::label('content', __('posts.attributes.content')) !!}
    {!! Form::textarea('content', null, ['class' => 'form-control trumbowyg-form' . ($errors->has('content') ? ' is-invalid' : ''), 'required']) !!}

    @error('content')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
