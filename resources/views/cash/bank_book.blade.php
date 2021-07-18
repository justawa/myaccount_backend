@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('bank-book', request()->segment(2)) !!}
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
    {{-- <div class="row">
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
    </div> --}}
    <div class="row">
        <div class="col-md-3 text-right col-md-offset-7"><button type="button" id="btn_opening_balance" class="btn btn-success">Update opening balance</button></div>
        <div class="col-md-2 text-right">
            {{-- <button type="button" id="btn_configuration" class="btn btn-success">Configuration</button> --}}
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Bank Book
                        </div>
                        <div class="col-md-2">
                            <button id="show_detail" class="btn btn-success btn-sm">Details</button>
                            <button id="hide_detail" class="btn btn-success btn-sm" style="display:none">Details</button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success btn-sm" id="print_section">Export as PDF</button>
                        </div>
                        <div class="col-md-2">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">To Date</label>
                                            <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li><button class="btn btn-success btn-block">Search</button></li>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body" id="printable">
                    @php  $debitTotal = 0; $creditTotal = 0; $closingTotal = 0; $hidePrint = false; @endphp
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th rowspan="3">Bank Book</th>
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
                                <th class="debit-side">Debit</th>
                                <th class="credit-side">Credit</th>
                                <th>Closing Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( isset($opening_balance) )
                                @php $moving_amount = $opening_balance+$closing_balance; $prev_amount = $opening_balance+$closing_balance; @endphp
                                <tr>
                                    {{-- <td>{{ $opening_balance_date }}</td> --}}
                                    <td>Opening Balance</td>
                                    <td class="debit-side"></td>
                                    <td class="credit-side"></td>
                                    <td>{{ abs($opening_balance + $closing_balance) }}</td>
                                </tr>
                                @if( count($combined_array) > 0 )
                                    @foreach( $combined_array as $key => $value )
                                        @php $showHead = true; $debitTotal += $combined_array[$key]['debit_total']; $creditTotal += $combined_array[$key]['credit_total']; $closingTotal += $combined_array[$key]['closing_total']; @endphp
                                        <tbody>
                                            <tr class="months" style="background-color: #f1f2fa">
                                                <td style="padding: 0;">
                                                    <label for="{{ $key }}" style="text-transform: uppercase; display: block; margin: 0; padding: 8px;">{{ $key }}
                                                        <input type="checkbox" name="accounting" id="{{ $key }}" data-toggle="toggle" style="visibility: hidden;">
                                                    </label>
                                                </td>
                                                <td class="debit-side">
                                                    {{ $combined_array[$key]['debit_total'] }}
                                                </td>
                                                <td class="credit-side">
                                                    {{ $combined_array[$key]['credit_total'] }}
                                                </td>
                                                <td>
                                                    @php $prev_amount += $combined_array[$key]['closing_total'] @endphp
                                                    {{ $prev_amount }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tbody class="mainData" style="display: none;">
                                        @foreach( $value as $data )
                                            @if( $data['type'] == 'showable' )
                                            <tr>
                                                {{-- <td>{{ $data['date'] }}</td> --}}
                                                <td>
                                                    @if($data['loop'] == 'sale')
                                                        @php    
                                                            $route = 'edit.invoice.form';
                                                        @endphp
                                                    @elseif($data['loop'] == 'sale_pos')
                                                        @php    
                                                            $route = 'edit.invoice.form';
                                                        @endphp
                                                    @elseif($data['loop'] == 'purchase')
                                                        @php    
                                                            $route = 'edit.bill.form';
                                                        @endphp
                                                    @elseif($data['loop'] == 'purchase_pos')
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
                                                    @elseif($data['loop'] == 'advanced_payment')
                                                        @php
                                                            $route = 'edit.advance.payment';
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
                                                    @elseif($data['loop'] == 'setoff_payment')
                                                        @php
                                                            $route = null;
                                                        @endphp
                                                    @elseif($data['loop'] == 'from_bank_transfers')
                                                        @php
                                                            $route = 'edit.bank.to.bank.transfer';
                                                        @endphp
                                                    @elseif($data['loop'] == 'to_bank_transfers')
                                                        @php
                                                            $route = 'edit.bank.to.bank.transfer';
                                                        @endphp
                                                    @endif

                                                    {{-- {{ $voucherType }} @if($route != null)<a href="{{ route($route, $data['routable']) }}">({{ $data['showable'] }})</a>@endif --}}
                                                    <table class="table borderless table-condensed" style="background-color: transparent;">
                                                        @if($showHead)
                                                        <thead @if($hidePrint) class="hidePrint" @endif>
                                                            <tr>
                                                                <th width="25%" class="text-center">Date</th>
                                                                <th width="25%" class="text-center">Particular</th>
                                                                <th width="25%" class="text-center">Voucher Type</th>
                                                                <th width="25%" class="text-center">Voucher No.</th>
                                                            </tr>
                                                        </thead>
                                                        @php $showHead = false; $hidePrint = true; @endphp
                                                        @endif
                                                        <tbody class="data-body">
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
                                                    @php $moving_amount += $data['amount'] @endphp
                                                    <td class="debit-side">{{ $data['amount'] }}</td>
                                                    <td class="credit-side"></td>
                                                @endif

                                                @if($data['transaction_type'] == 'credit')
                                                    <td class="debit-side"></td>
                                                    @php $moving_amount -= $data['amount'] @endphp
                                                    <td class="credit-side">{{ $data['amount'] }}</td>
                                                @endif

                                                @if($moving_amount < 0)
                                                <td><span>{{ abs($moving_amount) . ' CR' }}</span></td>
                                                @else
                                                <td>{{ $moving_amount . ' DR' }}</td>
                                                @endif
                                            </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    @endforeach
                                @endif
                                {{-- @if( count($sales) > 0 )
                                    @foreach( $sales as $sale )
                                        <tr>
                                            <td>{{ $sale->invoice_date->format('Y-m-d') }}</td>
                                            <td>Sale <a href="{{ route('edit.invoice.form', $sale->id) }}">({{ $sale->invoice_no }})</a></td>
                                            @php $moving_amount += $sale->bank_payment @endphp
                                            <td>{{ $sale->bank_payment }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($sales_pos) > 0 )
                                    @foreach( $sales_pos as $sale )
                                        <tr>
                                            <td>{{ $sale->invoice_date->format('Y-m-d') }}</td>
                                            <td>Sale <a href="{{ route('edit.invoice.form', $sale->id) }}">({{ $sale->invoice_no }})</a></td>
                                            @php $moving_amount += $sale->pos_payment @endphp
                                            <td>{{ $sale->pos_payment }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}
                                
                                {{-- @if( count($purchases) > 0 )
                                    @foreach( $purchases as $purchase )
                                        <tr>
                                            <td>{{ $purchase->bill_date->format('Y-m-d') }}</td>
                                            <td>Purchase <a href="{{ route('edit.bill.form', $purchase->id) }}">({{ $purchase->bill_no }})</a></td>
                                            <td></td>
                                            @php $moving_amount -= $purchase->bank_payment @endphp
                                            <td>{{ $purchase->bank_payment }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($purchases_pos) > 0 )
                                    @foreach( $purchases_pos as $purchase )
                                        <tr>
                                            <td>{{ $purchase->bill_date->format('Y-m-d') }}</td>
                                            <td>Purchase <a href="{{ route('edit.bill.form', $purchase->id) }}">({{ $purchase->bill_no }})</a></td>
                                            <td></td>
                                            @php $moving_amount -= $purchase->pos_payment @endphp
                                            <td>{{ $purchase->pos_payment }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($payments) > 0 )
                                    @foreach( $payments as $payment )
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                                            <td>Payments <a href="{{ route('edit.purchase.pending.payment', $payment->id) }}">({{ $payment->party_name }})</a></td>
                                            <td></td>
                                            @php $moving_amount -= $payment->bank_payment @endphp
                                            <td>{{ $payment->bank_payment }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($payments_pos) > 0 )
                                    @foreach( $payments_pos as $payment )
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                                            <td>Payments <a href="{{ route('edit.purchase.pending.payment', $payment->id) }}">({{ $payment->party_name }})</a></td>
                                            <td></td>
                                            @php $moving_amount -= $payment->pos_payment @endphp
                                            <td>{{ $payment->pos_payment }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($receipts) > 0 )
                                    @foreach( $receipts as $receipt )
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($receipt->payment_date)->format('Y-m-d') }}</td>
                                            <td>Receipts <a href="{{ route('edit.sale.pending.payment', $receipt->id) }}">({{ $receipt->party_name }})</a></td>
                                            @php $moving_amount += $receipt->bank_payment @endphp
                                            <td>{{ $receipt->bank_payment }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($receipts_pos) > 0 )
                                    @foreach( $receipts_pos as $receipt )
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($receipt->payment_date)->format('Y-m-d') }}</td>
                                            <td>Receipts <a href="{{ route('edit.sale.pending.payment', $receipt->id) }}">({{ $receipt->party_name }})</a></td>
                                            @php $moving_amount += $receipt->pos_payment @endphp
                                            <td>{{ $receipt->pos_payment }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($sale_orders) > 0 )
                                    @foreach( $sale_orders as $order )
                                        <tr>
                                            <td>{{ $order->date->format('Y-m-d') }}</td>
                                            <td>Sale Order <a href="{{ route('edit.sale.order', $order->token) }}">({{ $order->token }})</a></td>
                                            @php $moving_amount += $order->bank_amount @endphp
                                            <td>{{ $order->bank_amount }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($purchase_orders) > 0 )
                                    @foreach( $purchase_orders as $order )
                                        <tr>
                                            <td>{{ $order->date->format('Y-m-d') }}</td>
                                            <td>Purchase Order <a href="{{ route('edit.purchase.order', $order->token) }}">({{ $order->token }})</a></td>
                                            <td></td>
                                            @php $moving_amount -= $order->bank_amount @endphp
                                            <td>{{ $order->bank_amount }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- 
                                    GST not in bank book
                                    @if( count($gst_payments) > 0 )
                                    @foreach( $gst_payments as $payment )
                                        <tr>
                                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                            <td>GST Payment</td>
                                            <td></td>
                                            @php $moving_amount -= $payment->cash_amount @endphp
                                            <td>{{ $payment->cash_amount }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($cash_withdrawn) > 0 )
                                    @foreach( $cash_withdrawn as $cash )
                                        <tr>
                                            <td>{{ $cash->date->format('Y-m-d') }}</td>
                                            <td>Cash Withdrawn <a href="{{ route('edit.cash.withdraw', $cash->id) }}">({{ $cash->bank_name }})</a></td>
                                            @php $moving_amount -= $cash->amount @endphp
                                            <td></td>
                                            <td>{{ $cash->amount }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($cash_deposited) > 0 )
                                    @foreach( $cash_deposited as $cash )
                                        <tr>
                                            <td>{{ $cash->date->format('Y-m-d') }}</td>
                                            <td>Cash Deposited <a href="{{ route('edit.cash.deposit', $cash->id) }}">({{ $cash->bank_name }})</a></td>
                                            @php $moving_amount += $cash->amount @endphp
                                            <td>{{ $cash->amount }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($sale_party_payments) > 0 )
                                    @foreach( $sale_party_payments as $samount )
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($samount->payment_date)->format('Y-m-d') }}</td>
                                            <td>Sale Party Payment <a href="{{ route('edit.sale.party.pending.payment', $samount->id) }}">({{ $samount->party_name }})</a></td>
                                            @php $moving_amount += $samount->amount @endphp
                                            <td>{{ $samount->amount }}</td>
                                            <td></td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}

                                {{-- @if( count($purchase_party_payments) > 0 )
                                    @foreach( $purchase_party_payments as $pamount )
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($pamount->payment_date)->format('Y-m-d') }}</td>
                                            <td>Purchase Party Payment <a href="{{ route('edit.purchase.party.pending.payment', $pamount->id) }}">({{ $pamount->party_name }})</a></td>
                                            <td></td>
                                            @php $moving_amount -= $pamount->amount @endphp
                                            <td>{{ $pamount->amount }}</td>
                                            <td>{{ $moving_amount }}</td>
                                        </tr>
                                    @endforeach
                                @endif --}}
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No Data</td>
                                </tr>
                            @endif
                        </tbody>
                        <tbody>
                            <tr>
                                <th>Grand Total</th>
                                <td>{{ $debitTotal }}</td>
                                <td>{{ $creditTotal }}</td>
                                {{-- <td>{{ $closingTotal }}</td> --}}
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="opening_balance_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add/Update Opening Balance</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('update.bank.opening.balance', $bank->id) }}">

                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Opening Balance </label>
                                <input type="text" class="form-control" placeholder="Opening Balance" name="opening_balance"  @if( isset( $bank->opening_balance ) ) value="{{ $bank->opening_balance }}" @endif />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Opening Balance Type</label>
                                <select class="form-control" name="balance_type" >
                                    <option @if( isset( $bank->balance_type ) ) @if($bank->balance_type == 'creditor') selected="selected" @endif @endif value="creditor">Credit</option>
                                    <option @if( isset( $bank->balance_type ) ) @if($bank->balance_type == 'debitor') selected="selected" @endif @endif value="debitor">Debit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Opening Balance Date</label>
                            <input type="text" name="opening_balance_on_date" id="opening_balance_on_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($bank->opening_balance_on_date) ) value="{{ \Carbon\Carbon::parse($bank->opening_balance_on_date)->format('d/m/Y') }}" @endif @if( \Carbon\Carbon::parse($bank->opening_balance_on_date) >= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from) && \Carbon\Carbon::parse($bank->opening_balance_on_date) <= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to) ) readonly @endif />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" >Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="configuration_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Configuration</h4>
            </div>
            <div class="modal-body">
                {{-- <div class="checkbox">
                    <label>
                        <input type="checkbox" id="show_only_cheque"> Show Only Cheque No.
                    </label>
                </div> --}}
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="show_only_debit"> Show Only Debit balance
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="show_only_credit"> Show Only Credit balance
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $("#btn_opening_balance").on("click", function(){
                $("#opening_balance_modal").modal("show");
            });

            $("#btn_configuration").on("click", function(){
                $("#configuration_modal").modal("show");
            });
            
            $("#show_only_debit").on("change", function(){
                if( $(this).is(":checked") ){
                    $(".credit-side").css("visibility", "hidden");
                }else{
                    $(".credit-side").css("visibility", "visible");
                }
            });

            $("#show_only_credit").on("change", function(){
                if( $(this).is(":checked") ){
                    $(".debit-side").css("visibility", "hidden");
                } else {
                    $(".debit-side").css("visibility", "visible");
                }
            });

            $('[data-toggle="toggle"]').change(function(){
                $(this).parent().parent().parent().parent().next('tbody').toggle();
            });

            $("#show_detail").on("click", function(){
                $(this).hide();
                $("#hide_detail").show();
                $(".months").hide();
                $(".mainData").show();
            });

            $("#hide_detail").on("click", function(){
                $(this).hide();
                $("#show_detail").show();
                $(".months").show();
                $(".mainData").hide();
            });

            $('#print_section').on("click", function () {
                $('#printable').printThis();
            });

        });
    </script>
@endsection