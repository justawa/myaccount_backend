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
                <div class="panel-heading">View Invoice</div>
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
                        <div class="col-xs-12">GST Invoice</div>
                        <div class="col-md-8">
                            <p>Invoice Number: {{ $invoice->id }}</p>
                            <p>Invoice Date: {{ $invoice->created_at }}</p>
                        </div>
                        <div class="col-md-4">
                            <p>Buyer Name: {{ $party->name }}</p>
                        </div>
                    </div>
                    @if(count($invoice->items) > 0)
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
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->pivot->item_qty }}</td>
                                    @php $amount += $item->amount * $item->pivot->item_qty @endphp
                                    <td>{{ $item->amount * $item->pivot->item_qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total Taxable Value</td>
                                <td>{{ $amount }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">GST {{ $invoice->gst }}%</td>
                                @php $gst = $invoice->gst * $amount / 100 @endphp
                                <td>{{ $gst }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">Total Invoice Value</td>
                                <td>{{ $amount + $gst }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
