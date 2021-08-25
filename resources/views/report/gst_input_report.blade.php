@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('gst-input-report') !!}
    <div class="container">
        <form method="GET">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="from" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="to" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success" type="submit">Submit</button>
                </div>
            </div>
        </form>
        <div class="panel panel-default">
            <div class="panel-heading">GST Input</div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Bill No.</th>
                            <th>IGST</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>CESS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if( count($purchases) )
                            @php
                                $igst = 0;
                                $cgst = 0;
                                $sgst = 0;
                                $cess = 0;
                                $count = 1;
                            @endphp
                            @foreach($purchases as $purchase)
                            <tr>
                                @php 
                                    $igst += $purchase->igst;
                                    $cgst += $purchase->cgst;
                                    $sgst += $purchase->sgst;
                                    $cess += $purchase->item_total_cess;
                                @endphp
                                <td><a href="{{ route('edit.bill.form', $purchase->id) }}">{{ $purchase->bill_no }}</a></td>
                                <td>
                                    @if( $purchase->igst == null or $purchase->igst == '' )
                                        0
                                    @else
                                        {{ $purchase->igst }}
                                    @endif
                                </td>
                                <td>
                                    @if( $purchase->cgst == null or $purchase->cgst == '' )
                                        0
                                    @else
                                        {{ $purchase->cgst }}
                                    @endif
                                </td>
                                <td>
                                    @if( $purchase->sgst == null or $purchase->sgst == '' )
                                        0
                                    @else
                                    {{ $purchase->sgst }}
                                    @endif
                                </td>
                                <td>
                                    @if( $purchase->item_total_cess == null or $purchase->item_total_cess == '' )
                                        0
                                    @else
                                        {{ $purchase->item_total_cess }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <th>Total</th>
                                <th>{{ $igst }}</th>
                                <th>{{ $cgst }}</th>
                                <th>{{ $sgst }}</th>
                                <th>{{ $cess }}</th>
                            </tr>
                        @else
                            <tr>
                                <td colspan="4" class="text-center">No Data</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection