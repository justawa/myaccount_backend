@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('purchases-account') !!}
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
        <div class="col-md-2 col-md-offset-10 text-right">
            {{-- <button type="button" id="btn_configuration" class="btn btn-success">Configuration</button> --}}
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-4">
                            Purchases Account
                        </div>
                        <div class="col-md-2">
                            <form method="post" action="{{ route('export.purchase.account') }}">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-success btn-sm">Export</button>
                            </form>
                        </div>
                        <div class="col-md-2">
                            <button id="show_detail" class="btn btn-success btn-sm">Details</button>
                            <button id="hide_detail" class="btn btn-success btn-sm" style="display:none">Hide Details</button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success btn-sm" id="print_section">Print</button>
                        </div>
                        <div class="col-md-2">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px; line-height: 2.5" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
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
                    <table class="table table-bordered table-hover" style="margin-bottom:0">
                        <thead>
                            <tr>
                                <th rowspan="3">Purchases Account</th>
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
                    </table>
                    <div class="scrollable-table" style="max-height: 45vh; overflow-x: hidden; overflow-y: scroll;">
                    <table class="table table-bordered table-hover" style="margin-bottom:0">    
                        <tbody>
                            <tr></tr>
                            @php $moving_cash = 0; @endphp

                            @if( count($combined_array) > 0 )
                                {{-- <tbody>
                                    <tr>
                                        <td>Opening Balance</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ $opening_balance }}</td>
                                    </tr>
                                </tbody> --}}
                                @php $moving_cash += $opening_balance @endphp
                                @foreach( $combined_array as $key => $value )
                                    @php $showHead = true; @endphp
                                    <tbody>
                                        <tr class="months" style="background-color: #f1f2fa">
                                            <td colspan="4" style="padding: 0;">
                                                <label for="{{ $key }}" style="text-transform: uppercase; display: block; margin: 0; padding: 8px;">{{ $key }}
                                                    <input type="checkbox" name="accounting" id="{{ $key }}" data-toggle="toggle" style="visibility: hidden;">
                                                </label>
                                            </td>
                                            {{-- <td>
                                                {{ $combined_array[$key]['debit_total'] }}
                                            </td>
                                            <td>
                                                {{ $combined_array[$key]['credit_total'] }}
                                            </td>
                                            <td>
                                                @php $prev_amount += $combined_array[$key]['closing_total'] @endphp
                                                {{ $prev_amount }}
                                            </td> --}}
                                        </tr>
                                    </tbody>
                                    <tbody class="mainData" style="display: none;">
                                    @foreach( $value as $data )
                                        @if( $data['type'] == 'showable' )
                                        <tr>
                                            {{-- <td>{{ $data['date'] }}</td> --}}
                                            <td>
                                                    @if($data['loop'] == 'purchase')
                                                        @php    
                                                            $route = 'edit.bill.form';
                                                        @endphp
                                                    @elseif($data['loop'] == 'credit_note')
                                                        @php
                                                            $route = 'show.purchase.credit.note';
                                                        @endphp
                                                    @elseif($data['loop'] == 'debit_note')
                                                        @php
                                                            $route = 'show.purchase.debit.note';
                                                        @endphp
                                                    @endif

                                                {{-- {{ $voucherType }} @if($route != null)<a href="{{ route($route, $data['routable']) }}">({{ $data['showable'] }})</a>@endif --}}
                                                <div class="table-responsive" style="width: 555px;">
                                                
                                                        <table class="table borderless table-condensed" style="background-color: transparent;">
                                                            @if($showHead)
                                                            <thead @if($hidePrint) class="hidePrint" @endif>
                                                                <tr>
                                                                    <th width="25%" class="text-center">Date</th>
                                                                    <th width="25%" class="text-center">Particular</th>
                                                                    <th width="25%" class="text-center">Voucher Type</th>
                                                                    <th width="25%" class="text-center">Voucher No.</th>
        
                                                                    <th class="reference_name_and_no" style="display: none">Show reference name and no</th>
                                                                    <th class="party_gst_no" style="display: none">Party GST no</th>
                                                                    <th class="party_shipping_address" style="display: none">party shipping address</th>
                                                                    <th class="gross_profit_percent" style="display: none">gross profit %</th>
                                                                    <th class="order_detail" style="display: none">order details</th>
                                                                    <th class="shipping_detail" style="display: none">shipping details</th>
                                                                    <th class="import_and_export" style="display: none">import/export</th>
                                                                    <th class="port_code" style="display: none">port code</th>
                                                                    <th class="item_name" style="display: none">item name</th>
                                                                    <th class="quantity_detail" style="display: none">quantity details</th>
                                                                    <th class="rate" style="display: none">rates</th>
                                                                    <th class="show_taxable_detail" style="display: none">show taxable details</th>
                                                                    <th class="gross_total" style="display: none;">gross total</th>
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
        
                                                                    <td class="reference_name_and_no" style="display: none;">{{ $data['reference_name'] }}</td>
                                                                    <td class="party_gst_no" style="display: none;">{{ $data['party_gst_no'] }}</td>
                                                                    <td class="party_shipping_address" style="display: none;">{{ $data['party_shipping_address'] }}</td>
                                                                    <td class="gross_profit_percent" style="display: none;">{{ $data['gross_profit_percent'] }}</td>
                                                                    <td class="order_detail" style="display: none;">{{ $data['order_detail'] }}</td>
                                                                    <td class="shipping_detail" style="display: none;">{{ $data['shipping_detail'] }}</td>
                                                                    <td class="import_and_export" style="display: none;">{{ $data['import_export'] }}</td>
                                                                    <td class="port_code" style="display: none;">{{ $data['port_code'] }}</td>
                                                                    <td class="item_name" style="display: none;">{{ $data['item_name'] }}</td>
                                                                    <td class="quantity_detail" style="display: none;">{{ $data['quantity_detail'] }}</td>
                                                                    <td class="rate" style="display: none;">{{ $data['rates'] }}</td>
                                                                    <td class="show_taxable_detail" style="display: none;">{{ $data['show_taxable_detail'] }}</td>
                                                                    <td class="gross_total" style="display: none;">{{ $data['gross_total'] }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                </div>
                                            </td>

                                            {{-- @if($data['transaction_type'] == 'credit_debit')
                                                @php $moving_cash -= $data['amount'] @endphp
                                                @php $moving_cash += $data['amount_paid'] @endphp
                                                <td>{{ $data['amount_paid'] }}</td>
                                                <td>{{ $data['amount'] }}</td>
                                            @endif --}}

                                            @if($data['transaction_type'] == 'debit_credit')
                                                @php $moving_cash += $data['amount']; $debitTotal += $data['amount']; @endphp
                                                @php $moving_cash -= $data['amount_paid']; $creditTotal += $data['amount_paid']; @endphp
                                                <td>{{ $data['amount'] }}</td>
                                                <td>{{ $data['amount_paid'] }}</td>
                                                <td></td>
                                            @endif

                                            @if($data['transaction_type'] == 'debit')
                                                @php $moving_cash += $data['amount']; $debitTotal += $data['amount']; @endphp
                                                <td>{{ $data['amount'] }}</td>
                                                <td></td>
                                            @endif

                                            @if($data['transaction_type'] == 'credit')
                                                <td></td>
                                                @php $moving_cash -= $data['amount']; $creditTotal += $data['amount']; @endphp
                                                <td>{{ $data['amount'] }}</td>
                                            @endif

                                            <td>{{ $moving_cash > 0 ? $moving_cash . 'DR' : $moving_cash . 'CR' }}</td>
                                            @php $closingTotal += $moving_cash; @endphp
                                        </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                @endforeach
                            @else
                                <tbody>
                                    <tr>
                                        <td colspan="4">No Data</td>
                                    </tr>
                                </tbody>
                            @endif
                        </tbody>
                    </table>
                    </div>
					<table class="table table-bordered table-hover" style="margin-bottom:0">
                        <tbody>
                            <tr>
                                <th>Grand Total</th>
                                <td>{{ $debitTotal }}</td>
                                <td>{{ $creditTotal }}</td>
                                @php $closing_total = $debitTotal - $creditTotal; @endphp
                                <td>{{ ($closing_total > 0) ? $closing_total .'DR' : $closing_total .'CR' }}</td>
                            </tr>
                        </tbody>
                    </table>
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
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_reference_name_and_no"> Show Reference Name & No.
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_party_gst_no"> Party GST No
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_party_shipping_address"> Party Shipping Address
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_quantity_detail"> Quantity Details
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_gross_profit"> Gross Profit %
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_gst_taxable_detail"> Show GST taxable details
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_order_detail"> Order Details
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_shipping_detail"> Shipping Details
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_import_export_detail"> Import/Export Details
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_item_name"> Item Name
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_rates"> Show Rates
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_gross_total"> Gross Total
                        </label>
                    </div>
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

            $("#btn_configuration").on("click", function(){
                $("#configuration_modal").modal("show");
            });

            $("#show_reference_name_and_no").on("change", function(){
                if($(this).is(":checked")){
                    $(".reference_name_and_no").show();
                } else {
                    $(".reference_name_and_no").hide();
                }
            });

            $("#show_party_gst_no").on("change", function(){
                if($(this).is(":checked")){
                    $(".party_gst_no").show();
                } else {
                    $(".party_gst_no").hide();
                }
            });

            $("#show_party_shipping_address").on("change", function(){
                if($(this).is(":checked")){
                    $(".party_shipping_address").show();
                } else {
                    $(".party_shipping_address").hide();
                }
            });

            $("#show_quantity_detail").on("change", function(){
                if($(this).is(":checked")){
                    $(".quantity_detail").show();
                } else {
                    $(".quantity_detail").hide();
                }
            });

            $("#show_gross_profit").on("change", function(){
                if($(this).is(":checked")){
                    $(".gross_profit_percent").show();
                } else {
                    $(".gross_profit_percent").hide();
                }
            });

            $("#show_gst_taxable_detail").on("change", function(){
                if($(this).is(":checked")){
                    $(".show_taxable_detail").show();
                } else {
                    $(".show_taxable_detail").hide();
                }
            });

            $("#show_order_detail").on("change", function(){
                if($(this).is(":checked")){
                    $(".order_detail").show();
                } else {
                    $(".order_detail").hide();
                }
            });

            $("#show_shipping_detail").on("change", function(){
                if($(this).is(":checked")){
                    $(".shipping_detail").show();
                } else {
                    $(".shipping_detail").hide();
                }
            });

            $("#show_import_export_detail").on("change", function(){
                if($(this).is(":checked")){
                    $(".import_and_export").show();
                } else {
                    $(".import_and_export").hide();
                }
            });

            $("#show_item_name").on("change", function(){
                if($(this).is(":checked")){
                    $(".item_name").show();
                } else {
                    $(".item_name").hide();
                }
            });

            $("#show_rates").on("change", function(){
                if($(this).is(":checked")){
                    $(".rate").show();
                } else {
                    $(".rate").hide();
                }
            });

            $("#show_gross_total").on("change", function(){
                if($(this).is(":checked")){
                    $(".gross_total").show();
                } else {
                    $(".gross_total").hide();
                }
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