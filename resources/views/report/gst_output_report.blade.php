@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('gst-output-report') !!}
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
            <div class="panel-heading">GST Output</div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>IGST</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>CESS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if( count($sales) )
                            @php
                                $igst = 0;
                                $cgst = 0;
                                $sgst = 0;
                                $cess = 0;
                                $count = 1;
                            @endphp
                            @foreach($sales as $sale)
                            <tr>
                                @php 
                                    $igst += $sale->igst;
                                    $cgst += $sale->cgst;
                                    $sgst += $sale->sgst;
                                    $cess += $sale->cess;
                                @endphp
                                <td>
                                    <a href="{{ route('edit.invoice.form', $sale->id) }}">
                                    @if( $sale->invoice_no != null )
                                        {{ $sale->invoice_no }}
                                    @else
                                        {{ $sale->id }}
                                    @endif
                                    </a>
                                </td>
                                <td>
                                    @if( $sale->igst == null or $sale->igst == '' )
                                        0
                                    @else
                                        {{ $sale->igst }}
                                    @endif
                                </td>
                                <td>
                                    @if( $sale->cgst == null or $sale->cgst == '' )
                                        0
                                    @else
                                        {{ $sale->cgst }}
                                    @endif
                                </td>
                                <td>
                                    @if( $sale->sgst == null or $sale->sgst == '' )
                                        0
                                    @else
                                    {{ $sale->sgst }}
                                    @endif
                                </td>
                                <td>
                                    @if( $sale->cess == null or $sale->cess == '' )
                                        0
                                    @else
                                        {{ $sale->cess }}
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