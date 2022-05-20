@extends('admin.layouts.app')

@section('content')
    <p>@lang('users.show') : {{ link_to_route('users.show', route('users.show', $user), $user) }}</p>

    {!! Form::model($user, ['method' => 'PATCH', 'route' => ['admin.users.update', $user]]) !!}

        @include('admin/users/_form')

        {{ link_to_route('admin.users.index', __('forms.actions.back'), [], ['class' => 'btn btn-secondary']) }}
        {!! Form::submit(__('forms.actions.update'), ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@endsection
