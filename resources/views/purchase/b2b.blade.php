@extends('layouts.dashboard')

@section('content')
    <div class="container">
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
        <div class="row">
            <div class="col-md-12">
                <form method="get">
                    <div class="col-md-5">
                        <div class="form-group">
                            <input type="date" class="form-control" name="from" style="line-height: 1;" />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <input type="date" class="form-control" name="to" style="line-height: 1;" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bill No</th>
                        <th>GST No</th>
                        <th>Bill Amount</th>
                        <th>Tax Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($purchase_records) > 0)
                        @php $count = 1; @endphp
                        @foreach($purchase_records as $purchases)
                            @foreach($purchases as $purchase)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $purchase->bill_no }}</td>
                                <td>{{ $purchase->gst_no }}</td>
                                <td>{{ $purchase->total_amount }}</td>
                                <td>{{ $purchase->item_total_gst }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5">No Data found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection