@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>GSTIN/UIN of Recipient</th>
                    <th>Receiver Name</th>
                    <th>Invoice Number</th>
                    <th>Invoice date</th>
                    <th>Place of supply</th>
                    <th>Reverse Charge</th>
                    <th>Invoice Type</th>
                    <th>E-commerce GSTIN</th>
                    <th>Rate</th>
                    <th>Applicable % of Tax Rate</th>
                    <th>Taxable Value</th>
                    <th>Cess Amount</th>
                </tr>
            </thead>
            <tbody>
                @if( count($parties) > 0 && count($invoices) > 0 )
                    @php $count = 1; @endphp
                    @foreach($parties as $party)
                    @foreach($invoices[$party->id] as $invoice)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $invoice->party_gst }}</td>
                        <td>{{ $invoice->party_name }}</td>
                        <td>{{ $invoice->id }}</td>
                        <td>{{ $invoice->invoice_date }}</td>
                        <td>{{ $invoice->place_of_supply }}</td>
                        <td>No</td>
                        <td>{{ $invoice->type_of_bill }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $invoice->gst }}</td>
                        <td>{{ $invoice->total_amount }}</td>
                        <td>{{ $invoice->cess }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

@endsection