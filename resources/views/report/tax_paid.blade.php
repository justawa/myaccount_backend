@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td>#</td>
                    <td>Bill NO</td>
                    <td>Tax Paid</td>
                </tr>
            </thead>
            <tbody>
                @if( count($purchases) )
                    @php $count = 1; $total_gst = 0;  @endphp
                    @foreach($purchases as $purchase)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $purchase->bill_no }}</td>
                        @php 
                            $gst = $purchase->item_total_gst ? $purchase->item_total_gst : 0;
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