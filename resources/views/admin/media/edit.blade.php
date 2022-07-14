@extends('admin.layouts.app')

@section('content')
    <h1>@lang('media.edit')</h1>

    {!! Form::open(['route' => ['admin.media.update', $medium], 'method' =>'PUT', 'files' => true]) !!}

    
        <div class="form-group">
            <a href="{{ $medium->getUrl() }}" target="_blank">
                <img src="{{ $medium->getUrl('thumb') }}" alt="{{ $medium->name }}">
            </a>
        </div>

        <div class="form-group">
            {!! Form::label('name', __('media.attributes.name')) !!}
            {!! Form::text('name', $medium->name, ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : '')]) !!}

            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            {!! Form::label('description', __('media.attributes.description')) !!}
            {!! Form::textarea('description', $medium->description, ['class' => 'form-control' . ($errors->has('description') ? ' is-invalid' : '')]) !!}

            @error('description')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        {{ link_to_route('admin.media.index', __('forms.actions.back'), [], ['class' => 'btn btn-secondary']) }}
    
    {!! Form::submit(__('forms.actions.update'), ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}

    @endsection
