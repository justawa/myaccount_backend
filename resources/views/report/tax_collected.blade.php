@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td>#</td>
                    <td>Invoice #</td>
                    <td>Tax Paid</td>
                </tr>
            </thead>
            <tbody>
                @if( count($invoices) )
                    @php $count = 1; $total_gst = 0; @endphp
                    @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $invoice->id }}</td>
                        @php 
                            $gst = $invoice->gst ? $invoice->gst : 0;
                            $total_gst += $gst;
                        @endphp
                        <td>{{ $gst }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td>{{ $total_gst }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection