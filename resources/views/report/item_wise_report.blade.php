@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Items List</div>

                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($items) > 0)
                            @php $count = 1 @endphp
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td><a href="{{ route('single.item.report', $item->id) }}">View Report</a></td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Item</td>
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
