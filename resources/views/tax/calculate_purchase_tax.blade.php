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
                <div class="panel-heading">View Purchase Tax</div>

                <div class="panel-body">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bill NO.</th>
                                <th>Total Amount</th>
                                <th>Igst</th>
                                <th>Cgst</th>
                                <th>Gst</th>
                                <th>Taxed Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><a href="{{ route('show.purchase.bill', $purchase->bill_no) }}">{{ $purchase->bill_no }}</a></td>
                                @php $total_amount = $purchase->qty * $purchase->price; @endphp
                                <td>{{ $total_amount }}</td>
                                <td>{{ $purchase->igst }}</td>
                                <td>{{ $purchase->cgst }}</td>
                                <td>{{ $purchase->gst }}</td>
                                <td>{{ $purchase->gst * $total_amount / 100 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
