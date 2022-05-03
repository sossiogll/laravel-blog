@extends('admin.layouts.app')

@section('content')


    {!! Form::model($category, ['route' => ['admin.categories.update', $category], 'method' =>'PUT']) !!}
    
        @include('admin/categories/_form')

        <div class="pull-left">
            {{ link_to_route('admin.categories.index', __('forms.actions.back'), [], ['class' => 'btn btn-secondary']) }}
            {!! Form::submit(__('forms.actions.update'), ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}

    {!! Form::model($category, ['method' => 'DELETE', 'route' => ['admin.categories.destroy', $category], 'class' => 'form-inline pull-right', 'data-confirm' => __('forms.categories.delete')]) !!}
        {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> ' . __('categories.delete'), ['class' => 'btn btn-link text-danger', 'name' => 'submit', 'type' => 'submit']) !!}
    {!! Form::close() !!}
@endsection
