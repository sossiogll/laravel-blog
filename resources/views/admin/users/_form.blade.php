  <div class="form-row">
    <div class="form-group col-md-6">
      {!! Form::label('name', __('users.attributes.name')) !!}
      {!! Form::text('name', null, ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => __('users.placeholder.name'), 'required']) !!}

      @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
      @enderror
    </div>

    <div class="form-group col-md-6">
      {!! Form::label('email', __('users.attributes.email')) !!}
      {!! Form::text('email', null, ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'placeholder' => __('users.placeholder.email'), 'required']) !!}

      @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
      @enderror
    </div>
  </div>
  
  <div class="form-row">

    <div class="form-group col-md-6">
      {!! Form::label('profile_picture_id', __('users.attributes.profile_picture')) !!}
      {!! Form::select('profile_picture_id', $media, null, ['placeholder' => __('users.placeholder.profile_picture'), 'class' => 'form-control' . ($errors->has('profile_picture_id') ? ' is-invalid' : '')]) !!}

      @error('profile_picture_id')
          <span class="invalid-feedback">{{ $message }}</span>
      @enderror
    </div>

    <div class="form-group col-md-6">
      {!! Form::label('secondary_profile_picture_id', __('users.attributes.secondary_profile_picture')) !!}
      {!! Form::select('secondary_profile_picture_id', $media, null, ['placeholder' => __('users.placeholder.secondary_profile_picture'), 'class' => 'form-control' . ($errors->has('secondary_profile_picture_id') ? ' is-invalid' : '')]) !!}

      @error('secondary_profile_picture_id')
          <span class="invalid-feedback">{{ $message }}</span>
      @enderror
    </div>
  </div>


  @if(isset($user))

    <div class="form-group">
      {!! Form::label('name', __('users.attributes.positions')) !!}
      {!! Form::text('raw_positions_value', null, ['class' => 'form-control']) !!}
    </div>


    <div class="form-group">
        {!! Form::label('bio', __('users.attributes.bio')) !!}
        {!! Form::textarea('bio', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
      <p><b>{{ __('users.security')}}</b></p>
    </div>

    <div class="form-group">
      <div class="checkbox">
          <label>
          {!! Form::radio("authenticable", 1, $user->authenticable, ['onchange' => 'this.form.submit()']) !!}
            {!! __('users.attributes.authenticable') !!}
        </label>
      </div>

      <div class="checkbox">
          <label>
          {!! Form::radio("authenticable", 0, !$user->authenticable, ['onchange' => 'this.form.submit()']) !!}
            {!! __('users.attributes.unauthenticable') !!}
        </label>
      </div>
    </div>


    <div class="form-row">
      <div class="form-group col-md-6">
        {!! Form::label('password', __('users.attributes.password')) !!}
        {!! Form::password('password', ['class' => 'form-control' . ($errors->has('password') ? ' is-invalid' : ''), 'placeholder' => __('users.placeholder.password'), $user->authenticable ? 'required' : 'disabled']) !!}

        @error('password')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group col-md-6">
        {!! Form::label('password_confirmation', __('users.attributes.password_confirmation')) !!}
        {!! Form::password('password_confirmation', ['class' => 'form-control' . ($errors->has('password_confirmation') ? ' is-invalid' : ''), 'placeholder' => __('users.placeholder.password_confirmation'), $user->authenticable ? 'required' : 'disabled']) !!}

        @error('password_confirmation')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>
    </div>

    <div class="form-group">
      {!! Form::label('roles', __('users.attributes.roles')) !!}

      @foreach($roles as $role)
        <div class="checkbox">
          <label>
            {!! Form::checkbox("roles[$role->id]", $role->id, $user->hasRole($role->name), [$user->authenticable ? '' : 'disabled']) !!}
            @if (Lang::has('roles.' . $role->name))
              {!! __('roles.' . $role->name) !!}
            @else
              {{ ucfirst($role->name) }}
            @endif
          </label>
        </div>
      @endforeach
    </div>
  @endif
 


