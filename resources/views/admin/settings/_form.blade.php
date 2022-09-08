<div class="form-group">
  <div class="accordion" id="accordionExample">
    <div class="card">
      <div class="card-header" id="headingOne">
        <h2 class="mb-0">
          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            @lang('settings.localization.title')
          </button>
        </h2>
      </div>

      <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
        <div class="card-body">

          @lang('settings.localization.description')
          <br>

          {!! Form::checkbox('localization', true, null, ['class' => 'form-control' . ($errors->has('localization') ? ' is-invalid' : ''), 'data-toggle'=>'toggle', 'data-onstyle'=>'primary', 'data-width'=>'100', 'data-val'=>'true', $settings->localization ? 'checked' : '']) !!}

          @error('localization')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>

  </div>
</div>

