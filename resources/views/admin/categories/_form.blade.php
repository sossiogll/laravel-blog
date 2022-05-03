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

