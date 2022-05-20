<table class="table table-striped table-sm table-responsive-md">
    <caption>{{ trans_choice('categories.count', $categories->total()) }}</caption>
    <thead>
        <tr>
            <th>@lang('categories.attributes.name')</th>
            <th>@lang('categories.attributes.created_at')</th>
            <th><i class="fa fa-file-text" aria-hidden="true"></i></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>{{ humanize_date($category->created_at, 'd/m/Y H:i:s') }}</td>
                <td><span class="badge badge-pill badge-secondary">{{ $category->posts_count }}</span></td>
                <td>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                    </a>

                    {!! Form::model($category, ['method' => 'DELETE', 'route' => ['admin.categories.destroy', $category], 'class' => 'form-inline', 'data-confirm' => __('forms.comments.delete')]) !!}
                        {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>', ['class' => 'btn btn-danger btn-sm', 'name' => 'submit', 'type' => 'submit']) !!}
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $categories->links() }}
</div>
