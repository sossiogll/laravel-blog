@if ($user->hasSecondaryProfilePicture())
    {{ Html::image($user->secondaryProfilePicture->getUrl(), $user->secondaryProfilePicture->name, ['class' => 'img-thumbnail', 'width' => '350']) }}

    {!! Form::model($user, ['method' => 'DELETE', 'route' => ['admin.users_secondaryProfilePicture.destroy', $user], 'data-confirm' => __('forms.user.delete_secondaryProfilePicture')]) !!}
        <!--{!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> ' . __('user.delete_secondaryProfilePicture'), ['class' => 'btn btn-link text-danger', 'name' => 'submit', 'type' => 'submit']) !!}-->
    {!! Form::close() !!}
@endif
