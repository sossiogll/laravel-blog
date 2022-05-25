@php
    $posted_at = old('created_at') ?? (isset($category) ? $category->updated_at->format('Y-m-d\TH:i') : null);
@endphp

<div class="form-group">
    {!! Form::label('name', __('categories.attributes.name')) !!}
    {!! Form::text('name', null, ['class' => 'form-control' . ($errors->has('title') ? ' is-invalid' : ''), 'required']) !!}

    @error('title')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>


<div class="form-group">
    {!! Form::label('custom_fields', __('categories.attributes.custom_fields')) !!}
    {!! Form::textarea('raw_custom_fields', null, ['class' => 'form-control', 'disabled' => !$custom_fields_editable]) !!}

    @error('content')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
