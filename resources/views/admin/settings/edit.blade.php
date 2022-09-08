@extends('admin.layouts.app')

@section('content')


    {!! Form::model($settings, ['route' => ['admin.settings.update', $settings], 'method' =>'PUT']) !!}

        <div class="page-header d-flex justify-content-between">
          <h1>@lang('dashboard.settings')</h1>
        </div>

        @include('admin/settings/_form')

        <div class="pull-left">
            {!! Form::submit(__('forms.actions.update'), ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}

@endsection

