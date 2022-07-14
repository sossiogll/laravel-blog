@extends('admin.layouts.app')

@section('content')
    <h1>@lang('media.create')</h1>

    <image-loader-form
        action="{{route('media.store')}}"
        image-label="@lang('media.attributes.image')"
        name-label="@lang('media.attributes.name')"
        description-placeholder="@lang('media.placeholder.description')"
        description-label="@lang('media.attributes.description')"
        save-button-label="@lang('forms.actions.save')"
        back-button-label="@lang('forms.actions.back')"
        back-button-link="{{route('admin.media.index')}}"
        remove-button-label="@lang('forms.actions.remove')"
        reset-button-label="@lang('forms.actions.reset')"
        add-button-label="@lang('forms.actions.add')"
        upload-init-message="@lang('forms.media.initial')"
        upload-success-message="@lang('forms.media.success')"
        upload-warning-message="@lang('forms.media.error')"
    >
    
    </image-loader-form>

@endsection
