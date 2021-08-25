@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form>
                <div class="form-group">
                    <label>From Date</label>
                    <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                </div>
                <div class="form-group">
                    <button class="btn btn-success" >Search</button>
                </div>
            </form>
        </div>

        <div class="col-md-6">
            <form>
                <div class="form-group">
                    <label>Search By</label>
                    <select class="form-control" name="query_by">
                        <option value="name">Name</option>
                        <option value="bill_no">Bill</option>
                        {{-- <option value="state">State</option> --}}
                    </select>
                </div>
                <div class="form-group">
                    <label>Query Term</label>
                    <input type="text" class="form-control" name="q" />
                </div>
                <div class="form-group">
                    <button class="btn btn-success" >Search</button>
                </div>
            </form>
        </div>
    </div>
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
                                <th>Bill No.</th>
                                <th>Party Name</th>
                                <th>Amount</th>
                                <th>Amount Paid</th>
                                <th>Amount Remaining</th>
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
                                <td>{{ $purchase->amount_paid + $purchase->amount_remaining }}</td>
                                <td>{{ $purchase->amount_paid }}</td>
                                <td>{{ $purchase->amount_remaining }}</td>
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
