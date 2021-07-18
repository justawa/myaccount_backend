@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('sale.index') }}">View All Invoices</a>&nbsp;&nbsp;
            <a href="{{ route('sale.create') }}">Create New Sale</a>&nbsp;&nbsp;
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Purchase Bill</div>
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

                    <div class="row">
                        <div class="col-xs-12">Purchase bill</div>
                        <div class="col-md-8">
                            <p>Bill Number: {{ $purchase->bill_no }}</p>
                            <p>Bill Date: {{ $purchase->bought_on }}</p>
                        </div>
                        <div class="col-md-4">
                            {{-- <p>Buyer Name: {{ $party->name }}</p> --}}
                        </div>
                        <div class="col-md-6">
                            <p>Amount Paid: {{ $purchase->amount_paid }} <small>(exclusive of taxes)</small></p>
                        </div>
                        <div class="col-md-6">
                            <p>Amount Remaining: {{ $purchase->amount_remaining }} <small>(exclusive of taxes)</small></p>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $count = 1;
                                $amount = 0;
                            @endphp
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $purchase->qty }}</td>
                                @php $amount += $purchase->price * $purchase->qty @endphp
                                <td>{{ $amount }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total Taxable Value</td>
                                <td>{{ $amount }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">IGST {{ $purchase->igst }}%</td>
                                @php $igst = $purchase->igst * $amount / 100 @endphp
                                <td>{{ $igst }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">CGST {{ $purchase->cgst }}%</td>
                                @php $cgst = $purchase->cgst * $amount / 100 @endphp
                                <td>{{ $cgst }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">GST {{ $purchase->gst }}%</td>
                                @php $gst = $purchase->gst * $amount / 100 @endphp
                                <td>{{ $gst }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">Total Bill Value</td>
                                <td>{{ $amount + $gst }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
