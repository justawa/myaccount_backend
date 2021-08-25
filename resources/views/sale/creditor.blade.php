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
                    @if(count($sale_records) > 0)
                        @php $count = 1; $total_amount = 0; $total_remaining = 0; @endphp
                        @foreach($sale_records as $sale)
                        <tr>
                            <td>{{ $count++ }}</td>
                            <td>{{ $sale->party_name }}</td>
                            <td>{{ $sale->invoice_id }}</td>
                            <td>{{ $sale->total_amount }}</td>
                            <td>{{ $sale->amount_remaining }}</td>
                        </tr>
                            @php
                                $total_amount += $sale->total_amount;
                                $total_remaining += $sale->amount_remaining;
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