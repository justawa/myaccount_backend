@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('all-bills') !!}
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View All Purchases</div>

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
                                <th>Buyer Name</th>
                                <th>Amount Paid</th>
                                <th>Amount Remaining</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($bills) > 0)
                            @php $count = 1 @endphp
                            @foreach($bills as $bill)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $bill->bill_no }}</td>
                                <td>{{ $bill->party->name }}</td>
                                <td>{{ $bill->amount_paid }}</td>
                                <td>{{ $bill->amount_remaining }}</td>
                                <td><a href="{{ route('edit.bill.form', $bill->id) }}">Edit</a></td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Bills</td>
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
