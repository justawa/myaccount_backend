@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('item.index') }}">View All Items</a>&nbsp;&nbsp;
            <a href="{{ route('item.create') }}">Create New Item</a>&nbsp;&nbsp;
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View All Items</div>

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
                                <th>Quantity</th>
                                <th>Group</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($items) > 0)
                            @php $count = 1 @endphp
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->group_name }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Item Added</td>
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
