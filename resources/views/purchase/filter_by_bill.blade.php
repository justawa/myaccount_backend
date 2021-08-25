@extends('layouts.dashboard')

@section('content')
<div class="container">
    <form action="{{ route('purchase.filter.by.date') }}">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label class="col-md-3 control-label">Date From</label>
                    <div class="col-md-6">
                        <input type="date" name="from" class="form-control" style="height: 40px; line-height: 20px;" >
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label class="col-md-3 control-label">Date To</label>
                    <div class="col-md-6">
                        <input type="date" name="to" class="form-control" style="height: 40px; line-height: 20px;" >
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </div>
        </div>
    </form>
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
                                <th>Bill No</th>
                                <th>Party Name</th>
                                <th>Amount Paid</th>
                                <th>Amount Remaining</th>
                                {{-- <th>Taxes</th>
                                <th>Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($purchases) > 0)
                            @php $count = 1 @endphp
                            @foreach($purchases as $purchase)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $purchase->bill_no }}</td>
                                <td>{{ $purchase->party_name }}</td>
                                <td>{{ $purchase->amount_paid }}</td>
                                <td>{{ $purchase->amount_remaining }}</td>
                                {{-- <td><a href="{{ route('show.tax.purchase', $purchase->id) }}">View Taxes</a></td>
                                <td><a href="{{ route('purchase.edit', $purchase->id) }}">Edit</a></td> --}}
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Purchases</td>
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
