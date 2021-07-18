@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('purchase') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6">
            {{-- <form>
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
            </form> --}}
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
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            View All Purchases
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">To Date</label>
                                            <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li><button class="btn btn-success btn-block">Search</button></li>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <th>Party Name</th>
                                <th>Bill Date</th>
                                <th>Bill No.</th>
                                {{-- <th>Amount</th> --}}
                                <th>Amount Paid</th>
                                <th>Amount Remaining</th>
                                <th>Change Type</th>
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
                                <td>{{ $purchase->party_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d/m/Y') }}</td>
                                <td><a href="{{ route('edit.bill.form', $purchase->id) }}">{{ $purchase->bill_no }}</a></td>
                                {{-- <td>{{ $purchase->amount_paid + $purchase->amount_remaining }}</td> --}}
                                <td>{{ $purchase->amount_paid }}</td>
                                <td>{{ $purchase->amount_remaining }}</td>
                                @if($purchase->type_of_bill == 'regular')
                                <td><a href="{{ route('purchase.bill.type.cancel', $purchase->id) }}">Cancel</a></td>
                                @else
                                <td><a href="{{ route('purchase.bill.type.regular', $purchase->id) }}">Regular</a></td>
                                @endif
                                {{-- <td>{{ $purchase->bought_on }}</td> --}}
                                {{-- <td><a href="{{ route('show.tax.purchase', $purchase->id) }}">View Taxes</a></td> --}}
                                {{-- <td><a href="{{ route('purchase.edit', $purchase->id) }}">Edit</a></td> --}}
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
