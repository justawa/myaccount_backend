@extends('admin.layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">View All GSTs</div>
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
                                    <th>Created at</th>
                                    <th>Updated at</th>
                                    <th colspan="2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if( count($gsts) > 0 )
                                    @php $count = 1; @endphp
                                    @foreach($gsts as $gst)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $gst->name }}</td>
                                        <td>{{ $gst->created_at }}</td>
                                        <td>{{ $gst->updated_at }}</td>
                                        <td><a href="{{ route('edit.gst', $gst->id) }}">Edit</a></td>
                                        <td>
                                            <a href="{{ route('delete.gst', $gst->id) }}"
												onclick="event.preventDefault();
												document.getElementById('delete-gst-form-{{ $gst->id }}').submit();">
												Delete
                                            </a>
                                            
                                            <form id="delete-gst-form-{{ $gst->id }}" action="{{ route('delete.gst', $gst->id) }}" method="POST" style="display: none;">
                                                {{ csrf_field() }}
                                                <input name="_method" type="hidden" value="DELETE">
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
