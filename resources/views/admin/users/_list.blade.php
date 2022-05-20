<table class="table table-striped table-sm">
    <caption>{{ trans_choice('users.count', $users->total()) }}</caption>
    <thead>
        <tr>
            <th>@lang('users.attributes.name')</th>
            <th>@lang('users.attributes.email')</th>
            <th>@lang('users.attributes.authentication')</th>
            <th>@lang('users.attributes.roles')</th>
            <th>@lang('users.attributes.registered_at')</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ link_to_route('admin.users.edit', $user->fullname, $user) }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->authenticable ? __('users.attributes.authenticable') : __('users.attributes.unauthenticable') }}</td>
                <td></td>
                <td>{{ humanize_date($user->registered_at, 'd/m/Y H:i:s') }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                    </a>
                    @if(!$user->isCurrentUser())
                        {!! Form::model($user, ['method' => 'DELETE', 'route' => ['admin.users.destroy', $user], 'class' => 'form-inline', 'data-confirm' => __('forms.posts.delete')]) !!}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>', ['class' => 'btn btn-danger btn-sm', 'name' => 'submit', 'type' => 'submit']) !!}
                        {!! Form::close() !!}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $users->links() }}
</div>
