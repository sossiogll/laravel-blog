@extends('users.layout', ['tab' => 'profile'])

@section('main_content')
  <div class="card">
    <div class="card-body">
      <h1>@lang('users.profile')</h1>
      <hr class="my-4">

      {!! Form::model($user, ['method' => 'PATCH', 'route' => ['users.update']]) !!}

        <div class="form-group row">
          {!! Form::label('name', __('users.attributes.name'), ['class' => 'col-sm-2 col-form-label']) !!}

          <div class="col-sm-5">
            {!! Form::text('name', null, ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => __('users.placeholder.name'), 'required']) !!}

            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        </div>


      <div class="form-group row">
          {!! Form::label('profile_picture_id', __('users.attributes.profile_picture'), ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-5">
            {!! Form::select('profile_picture_id', $media, null, ['placeholder' => __('users.placeholder.profile_picture'), 'class' => 'form-control' . ($errors->has('profile_picture_id') ? ' is-invalid' : '')]) !!}

            @error('profile_picture_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
      </div>

      <div class="form-group row">
          {!! Form::label('secondary_profile_picture_id', __('users.attributes.secondary_profile_picture'), ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-5">
            {!! Form::select('secondary_', $media, null, ['placeholder' => __('users.placeholder.secondary_profile_picture'), 'class' => 'form-control' . ($errors->has('secondary_profile_picture_id') ? ' is-invalid' : '')]) !!}

            @error('secondary_profile_picture_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
      </div>

      <div class="form-group row">
          {!! Form::label('email', __('users.attributes.email'), ['class' => 'col-sm-2 col-form-label']) !!}

          <div class="col-sm-5">
            {!! Form::text('email', null, ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'placeholder' => __('users.placeholder.email'), 'required']) !!}

            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
      </div>

        <div class="form-group row">
          {!! Form::label('name', __('users.attributes.positions'), ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-5">
            {!! Form::text('raw_positions_value', null, ['class' => 'form-control']) !!}
          </div>
        </div>


        <div class="form-group row">
            {!! Form::label('bio', __('users.attributes.bio'), ['class' => 'col-sm-2 col-form-label']) !!}
            <div class="col-sm-5">
              {!! Form::textarea('bio', null, ['class' => 'form-control']) !!}  
            </div>
        </div>

        <div class="form-group offset-sm-2">
          {!! Form::submit(__('forms.actions.save'), ['class' => 'btn btn-success']) !!}
        </div>

      {!! Form::close() !!}
    </div>
  </div>
@endsection
