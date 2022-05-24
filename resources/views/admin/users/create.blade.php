@extends('admin.layouts.app')

@section('content')
    <h1>@lang('users.create')</h1>

    {!! Form::open(['route' => ['admin.users.store'], 'method' =>'POST']) !!}
        @include('admin/users/_form')

        {{ link_to_route('admin.users.index', __('forms.actions.back'), [], ['class' => 'btn btn-secondary']) }}
        {!! Form::submit(__('forms.actions.save'), ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@endsection
