@extends('admin.layouts.app')

@section('content')
    <h1>@lang('categories.create')</h1>

    {!! Form::open(['route' => ['admin.categories.store'], 'method' =>'POST']) !!}
        @include('admin/categories/_form')

        {{ link_to_route('admin.categories.index', __('forms.actions.back'), [], ['class' => 'btn btn-secondary']) }}
        {!! Form::submit(__('forms.actions.save'), ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@endsection
