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
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Party</th>
                        <th>Bill No</th>
                        <th>Total Amount</th>
                        <th>Amount Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($purchase_records) > 0)
                        @php $count = 1; $total_amount = 0; $total_remaining = 0; @endphp
                        @foreach($purchase_records as $purchase)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $purchase->party_name }}</td>
                            <td>{{ $purchase->bill_no }}</td>
                            <td>{{ $purchase->total_amount }}</td>
                            <td>{{ $purchase->amount_remaining }}</td>
                        </tr>
                            @php
                                $total_amount += $purchase->total_amount;
                                $total_remaining += $purchase->amount_remaining;
                            @endphp
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <td>{{ $total_amount }}</td>
                        <td>{{ $total_remaining }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection