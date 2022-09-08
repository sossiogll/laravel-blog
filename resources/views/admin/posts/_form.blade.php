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

    <div class="form-group">
        {!! Form::label('thumbnail_id', __('posts.attributes.thumbnail')) !!}
        {!! Form::select('thumbnail_id', $media, null, ['placeholder' => __('posts.placeholder.thumbnail'), 'class' => 'form-control' . ($errors->has('thumbnail_id') ? ' is-invalid' : '')]) !!}

        @error('thumbnail_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <image-picker
        modal-title ="@lang('posts.carousel')"
        media-endpoint="{{route('media.index')}}"
        carousel-endpoint="{{$post != null ? route('posts.carousel', $post) : null}}"
        :is-multiple="true"
        close-button-label="@lang('forms.actions.close')"
        open-modal-label="@lang('posts.carousel')"
        add-button-label="@lang('forms.actions.add')">
    </image-picker>

    <category-selector
        name="category_id"
        id="category-id"
        categories-endpoint="{{route('categories.index')}}"
        custom-fields-endpoint="{{$post != null ? route('posts.customfields', $post) : null}}"
        selected="{{$post != null ? $post->category_id : ''}}"
        category-label="@lang('posts.attributes.category')"
        category-placeholder="@lang('posts.placeholder.category')">
    </category-selector>

    <div class="form-group">
        <label for="language">@lang('posts.language')</label>
        <select class="form-control" id="language" name="language">
            <option {!! $post->language == "it" ? 'selected="selected"' : '' !!} value="it">
                Italiano
            </option>
            <option {!! $post->language == "en" ? 'selected="selected"' : '' !!} value="en">
                English
            </option>
            <option {!! $post->language == "fr" ? 'selected="selected"' : '' !!} value="fr">
                Français
            </option>
        </select>
    </div>


    <div class="form-group">
        {!! Form::label('content', __('posts.attributes.content')) !!}
        {!! Form::textarea('content', null, ['class' => 'form-control trumbowyg-form' . ($errors->has('content') ? ' is-invalid' : '')]) !!}

        @error('content')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    
    <div class="form-group">
        {!! Form::label('summary_content', __('posts.attributes.summary_content')) !!}
        {!! Form::textarea('summary_content', null, ['class' => 'form-control' . ($errors->has('summary_content') ? ' is-invalid' : '')]) !!}

        @error('summary_content')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
