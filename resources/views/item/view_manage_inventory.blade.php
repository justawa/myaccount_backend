@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View All Managed Inventories</div>

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
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Update Date</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($inventories) > 0)
                            @php $count = 1 @endphp
                            @foreach($inventories as $inventory)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $inventory->item->name }}</td>
                                <td>{{ $inventory->qty }}</td>
                                <td>{{ $inventory->rate }}</td>
                                <td>{{ \Carbon\Carbon::parse($inventory->value_updated_on)->format('d/m/Y') }}</td>
                                <td>{{ $inventory->reason }}</td>
                                <td>
                                    <a href="{{ route('manage.inventory.edit', $inventory->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                                
                                    <a onclick="event.preventDefault();
										document.getElementById('inventory-delete-form').submit();" href="{{ route('manage.inventory.delete', $inventory->id) }}" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i></a>

                                    <form id="inventory-delete-form" action="{{ route('manage.inventory.delete', $inventory->id) }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
									</form>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Data found</td>
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
