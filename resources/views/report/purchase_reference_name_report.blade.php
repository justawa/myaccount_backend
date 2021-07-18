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
        <div class="col-md-6 col-md-offset-6">
            <form>
                <div class="col-md-10">
                    <div class="form-group">
                        <select class="form-control" name="reference_name">
                            @if( isset($reference_names) && count($reference_names) > 0 )
                                <option disabled selected>Select Reference Name</option>
                                @foreach($reference_names as $reference)
                                @if($reference->reference_name != null)
                                <option @if(request()->reference_name == $reference->reference_name) selected="selected" @endif value="{{ $reference->reference_name }}">{{ $reference->reference_name }}</option>
                                @endif
                                @endforeach
                            @else
                                <option disabled selected>No Record.</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Referenced Purchases</div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bill No</th>
                                <th>Bill Date</th>
                                <th>Party</th>
                                <th>Total Amount</th>
                                <th>Remaining Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( isset($purchases) && count($purchases) > 0 )
                                @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $purchase->bill_no }}</td>
                                    <td>{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d/m/Y') }}</td>
                                    <td>{{ $purchase->party->name }}</td>
                                    <td>{{ $purchase->total_amount }}</td>
                                    <td>{{ $purchase->amount_remaining }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No Record</td>
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