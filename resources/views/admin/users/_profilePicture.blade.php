
@if ($user->hasProfilePicture())

    {{ Html::image($user->profilePicture->getUrl(), $user->profilePicture->name, ['class' => 'img-thumbnail', 'width' => '350']) }}

    {!! Form::model($user, ['method' => 'DELETE', 'route' => ['admin.users_profilePicture.destroy', $user]]) !!}
        <!--{!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> ' . __('user.delete_profilePicture'), ['class' => 'btn btn-link text-danger', 'name' => 'submit', 'type' => 'submit']) !!}-->
    {!! Form::close() !!}
@endif
