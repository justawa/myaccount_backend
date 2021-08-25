@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('day-book') !!}
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
    <div class="row">
        <div class="col-md-6">
            <form>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Day Book</div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th rowspan="3">Day Book</th>
                                <th colspan="2">
                                    @if( $from_date && $to_date )
                                        {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
                                    @endif
                                </th>
                                <th rowspan="2">{{ auth()->user()->profile->name }}</th>
                            </tr>
                            <tr>
                                <th colspan="2">Transactions</th>
                            </tr>
                            <tr>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Closing Balance</th>
                            </tr>
                        </thead>
                        {{-- <thead>
                            <tr>
                                <th>Date</th>
                                <th>Particulars</th>
                                <th>Receipt</th>
                                <th>Payment</th>
                                <th>Closing Balance</th>
                            </tr>
                        </thead> --}}
                        <tbody>
                            <tr></tr>
                            @if( count($combined_array) > 0 )
                                @php $moving_cash = 0; $prev_total = 0; @endphp
                                @foreach( $combined_array as $key => $value )
                                    @php $showHead = true; @endphp
                                    <tbody>
                                        <tr style="background-color: #f1f2fa">
                                            <td style="padding: 0;">
                                                <label for="{{ $key }}" style="text-transform: uppercase; display: block; margin: 0; padding: 8px;">{{ $key }}
                                                    <input type="checkbox" name="accounting" id="{{ $key }}" data-toggle="toggle" style="visibility: hidden;">
                                                </label>
                                            </td>
                                            <td>
                                                {{ $combined_array[$key]['debit_total'] }}
                                            </td>
                                            <td>
                                                {{ $combined_array[$key]['credit_total'] }}
                                            </td>
                                            <td>
                                                @php $prev_total += $combined_array[$key]['closing_total'] @endphp
                                                {{ $prev_total }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody style="display: none;">
                                    @foreach( $value as $data )
                                        @if( $data['type'] == 'showable' )
                                        <tr>
                                            {{-- <td>{{ $data['date'] }}</td> --}}
                                            <td>
                                                @if($data['loop'] == 'sale')
                                                    @php    
                                                        $route = 'edit.invoice.form';
                                                    @endphp
                                                @elseif($data['loop'] == 'purchase')
                                                    @php    
                                                        $route = 'edit.bill.form';
                                                    @endphp
                                                @elseif($data['loop'] == 'sale_order')
                                                    @php    
                                                        $route = 'edit.sale.order';
                                                    @endphp
                                                @elseif($data['loop'] == 'purchase_order')
                                                    @php
                                                        $route = 'edit.purchase.order';
                                                    @endphp
                                                @elseif($data['loop'] == 'gst_payment')
                                                    @php
                                                        $route = null;
                                                    @endphp
                                                @elseif($data['loop'] == 'receipt')
                                                    @php
                                                        $route = 'edit.sale.pending.payment';
                                                    @endphp
                                                @elseif($data['loop'] == 'payment')
                                                    @php
                                                        $route = 'edit.purchase.pending.payment';
                                                    @endphp
                                                @elseif($data['loop'] == 'sale_party_payment')
                                                    @php
                                                        $route = 'edit.sale.party.pending.payment';
                                                    @endphp
                                                @elseif($data['loop'] == 'purchase_party_payment')
                                                    @php 
                                                        $route = 'edit.purchase.party.pending.payment';
                                                    @endphp
                                                @elseif($data['loop'] == 'cash_withdraw')
                                                    @php
                                                        $route = 'edit.cash.withdraw';
                                                    @endphp
                                                @elseif($data['loop'] == 'cash_deposit')
                                                    @php
                                                        $route = 'edit.cash.deposit';
                                                    @endphp 
                                                @endif

                                                {{-- {{ $voucherType }} @if($route != null)<a href="{{ route($route, $data['routable']) }}">({{ $data['showable'] }})</a>@endif --}}
                                                <table class="table table-condensed" style="background-color: transparent;">
                                                    @if($showHead)
                                                    <thead>
                                                        <tr>
                                                            <th width="25%" class="text-center">Date</th>
                                                            <th width="25%" class="text-center">Particular</th>
                                                            <th width="25%" class="text-center">Voucher Type</th>
                                                            <th width="25%" class="text-center">Voucher No.</th>
                                                        </tr>
                                                    </thead>
                                                    @php $showHead = false @endphp
                                                    @endif
                                                    <tbody>
                                                        <tr>
                                                            <td width="25%" class="text-center" style="border-top: 0">{{ \Carbon\Carbon::parse($data['date'])->format('d/m/Y') }}</td>
                                                            <td width="25%" class="text-center" style="border-top: 0">{{ $data['particulars'] }}</td>
                                                            <td width="25%" class="text-center" style="border-top: 0">{{ $data['voucher_type'] }}</td>
                                                            <td width="25%" class="text-center" style="border-top: 0">
                                                                @if($route != null)
                                                                <a href="{{ route($route, $data['routable']) }}">
                                                                    {{ $data['voucher_no'] }}
                                                                </a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>

                                            @if($data['transaction_type'] == 'debit')
                                                @php $moving_cash += $data['amount'] @endphp
                                                <td>{{ $data['amount'] }}</td>
                                                <td></td>
                                            @endif

                                            @if($data['transaction_type'] == 'credit')
                                                <td></td>
                                                @php $moving_cash -= $data['amount'] @endphp
                                                <td>{{ $data['amount'] }}</td>
                                            @endif

                                            <td>{{ $moving_cash }}</td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="6" class="text-center">No Data</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('[data-toggle="toggle"]').change(function(){
                $(this).parent().parent().parent().parent().next('tbody').toggle();
            });
        });
    </script>
@endsection