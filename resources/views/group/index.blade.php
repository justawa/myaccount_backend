@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('group') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="{{ route('post.import.group') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="file" class="form-control" name="group_file" style="height: auto;" />
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success">Import Groups</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View All Groups</div>

                <div class="panel-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('failure'))
                        <div class="alert alert-danger">
                            {{ session('failure') }}
                        </div>
                    @endif

                    

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($groups) > 0)
                            @php $count = 1 @endphp
                            @foreach($groups as $group)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $group->name }}</td>
                            <td><a href="{{ route('group.edit', $group->id) }}">Edit</a></td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Group Added</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
