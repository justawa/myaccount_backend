@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('edit-bill', request()->segment(4)) !!}

<div class="container">

    @if($errors->any())
    <div class="alert alert-danger">
        <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
    @endif

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

    <form method="POST" action="{{ route('update.bill.form', $bill) }}">
        {{ csrf_field() }}
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-6">
            {{-- <div class="row">
            <div class="col-md-6">
                <input type="radio" name="tax_inclusive" id="tax_inclusive1" value="inclusive_of_tax" @if($bill->amount_type == 'inclusive') checked @endif /> <label for="tax_inclusive1">Bill is Incl of Taxes</label>
            </div>
            <div class="col-md-6">
                <input type="radio" name="tax_inclusive" id="tax_inclusive2" value="exclusive_of_tax" @if($bill->amount_type == 'exclusive') checked @endif /> <label for="tax_inclusive2">Bill is Excl of Taxes</label>
            </div>
            </div> --}}
            <h2 style="color: red">{{ $bill->type_of_bill == "cancel" ? "Cancelled" : "" }}</h2>
        </div>
        <div class="col-md-3">
            @php $bill_no = $bill->bill_no @endphp
            <strong>Bill No</strong> : <input type="text" id="bill_no" name="bill_no" class="form-control" value="{{ $bill_no }}" @if(auth()->user()->purchaseSetting->bill_no_type == 'auto') {{ 'disabled' }} @endif /> 
        </div>
        {{-- <div class="col-md-3">
            <label style="padding-top: 15px;">
                <button type="button" class="btn btn-link" id="want_to_update_bill_no">Change</button>
                <button type="button" class="btn btn-link" id="dont_want_to_update_bill_no" style="display: none;">Dont Change</button>
            </label>
        </div> --}}
        <div class="col-md-3 text-right">
            <button type="button" class="btn btn-success" id="select_options"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;More Options</button>
        </div>
    </div>

    {{-- <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-6">
            <strong>{{ $bill->party->name }}</strong>
        </div>
        <div class="col-md-3">
            <strong>Bill Date</strong> : <input type="text" class="form-control custom_date" id="bill_date" placeholder="DD/MM/YYYY" autocomplete="off" value="{{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}">
        </div>
    </div> --}}

    <div class="row" style="margin-bottom: 20px;">
            <input type="hidden" name="party_type" id="party_type" value="{{ $bill->party->registered }}" />
            <div class="col-md-3">
                <strong>Party Name</strong> : {{ $bill->party->name }}
                <div class="form-group" @if(!$bill->buyer_name) style="display: none" @endif>
                    <input type="text" id="buyer_name" name="buyer_name" class="form-control" placeholder="Buyer Name" value="{{ $bill->buyer_name }}" />
                </div>
            </div>
            <div class="col-md-2">
                <strong>Bill Date</strong> : <input type="text" class="form-control custom_date" id="bill_date" name="bill_date" placeholder="DD/MM/YYYY" autocomplete="off" value="{{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}">
                <p id="bill_date_validation_error" style="font-size: 12px; color: red;"></p>
            </div>
            {{-- <div class="col-md-2">
                <strong>Due Date</strong> : <input type="text" class="form-control custom_date" id="due_date" name="due_date" placeholder="DD/MM/YYYY" autocomplete="off" value="{{ \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') }}">
            </div> --}}
            <div class="col-md-2">
                <div class="form-group" id="purchase_order_block" @if(! isset($purchase_order_no) ) style="display: none;" @endif>
                    <label>Purchase Order No.</label>
                    <input type="text" class="form-control" id="purchase_order_no" name="purchase_order_no" placeholder="Purchase Order NO." @if( isset($purchase_order_no) ) value="{{ $purchase_order_no }}" readonly @endif/>
                    <div class="autosaleorder"></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group" id="reference_name_block" @if(! isset($bill->reference_name) ) style="display: none;" @endif>
                    <label>Reference Name</label>
                    <input type="text" class="form-control" name="reference_name" id="reference_name" placeholder="Reference Name" value="{{ $bill->reference_name }}" />
                </div>
            </div>
        </div>

    <div class="row" id="i_e_info" >
        <div class="col-md-2" @if(! $bill->shipping_bill_no) style="display: none;" @endif>
            <label>Shipping Bill No.</label>
            <input type="text" class="form-control" name="shipping_bill_no" id="shipping_bill_no" placeholder="Shipping Bill No." value="{{ $bill->shipping_bill_no }}" />
        </div>
        <div class="col-md-2" @if(!$bill->date_of_shipping) style="display: none;" @endif>
            <div class="form-group">
                <label>Date of Shipping</label>
                {{-- <input type="date" class="form-control" name="date_of_shipping" style="line-height: 1.7;" /> --}}
                
                <input type="text" class="form-control custom_date" id="date_of_shipping" name="date_of_shipping" placeholder="DD/MM/YYYY" autocomplete="off" value="{{ isset($bill->date_of_shipping) ? \Carbon\Carbon::parse($bill->date_of_shipping)->format('d/m/Y') : '' }}">
            </div>
        </div>
        <div class="col-md-2" @if(!$bill->code_of_shipping_port) style="display: none;" @endif>
            <div class="form-group">
                <label>Code of Shipping Port</label>
                <input type="text" class="form-control" name="code_of_shipping_port" id="code_of_shipping_port" placeholder="Shipping Port Code" value="{{ $bill->code_of_shipping_port }}" />
            </div>
        </div>
        <div class="col-md-2" @if(!$bill->conversion_rate) style="display:none;" @endif>
            <div class="form-group">
                <label>Conversion Rate</label>
                <input type="text" class="form-control" name="conversion_rate" id="conversion_rate" placeholder="Conversion Rate" value="{{ $bill->conversion_rate }}" />
            </div>
        </div>
        <div class="col-md-2" @if(!$bill->currency_symbol) style="display: none;" @endif>
            <div class="form-group">
                <label>Currency Symbol</label>
                <input type="text" class="form-control" name="currency_symbol" id="currency_symbol" placeholder="Currency Symbol" value="{{ $bill->currency_symbol }}" />
            </div>
        </div>
        <div class="col-md-2" @if(!$bill->export_type) style="display: none;" @endif>
            <div class="form-group">
                <label>Export Type</label>
                <select style="font-size: 10px;" class="form-control" name="export_type" id="export_type">
                    <option value="" selected disabled>Select Type</option>
                    <option @if($bill->export_type == "deemed exporter") selected @endif value="deemed exporter">Deemed Exporter</option>
                    <option @if($bill->export_type == "export with payment") selected @endif value="export with payment">Export with Payment</option>
                    <option @if($bill->export_type == "export without payment") selected @endif value="export without payment">Export without Payment</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row" id="c_c_info">
        <div class="col-md-6" @if(!$bill->consignee_info) style="display: none;" @endif>
            <div class="form-group">
                <label>Consignee Info</label>
                <textarea class="form-control" name="consignee_info" id="consignee_info" placeholder="Consignee Info">{{ $bill->consignee_info }}</textarea>
            </div>
        </div>
        <div class="col-md-6" @if(!$bill->consignor_info) style="display: none;" @endif>
            <div class="form-group">
                <label>Consignor Info</label>
                <textarea class="form-control" name="consignor_info" id="consignor_info" placeholder="Consignor Info">{{ $bill->consignor_info }}</textarea>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                @if($bill->is_add_lump_sump == 0)
                <th>GST Classification</th>
                @endif
                @if($bill->is_add_lump_sump == 0)
                <th>Discount</th>
                @endif
                @if($bill->is_add_lump_sump == 0)
                <th colspan="2">Quantity</th>
                <th>Free Quantity</th>
                @endif
                <th>Rate</th>
                <th>Amount</th>
                @if($bill->is_add_lump_sump == 0)
                <th>CESS</th>
                @endif
                <th>Tax(%)</th>
                <th>Calc. Tax</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach( $purchases as $purchase )
            <tr>
                <td class="item_name_td">{{ $purchase->item->name }}</td>
                @if($bill->is_add_lump_sump == 0)
                <td>{{ $purchase->gst_classification ?? 'NA' }}</td>
                @endif
                @if($bill->is_add_lump_sump == 0)
                <td class="item_discount_td"><span class="discount_value">{{ $purchase->discount }}</span> <span class="discount_type_value" style="visibility: hidden">{{ $purchase->discount_type }}</span></td>
                @endif
                @if($bill->is_add_lump_sump == 0)
                <td class="item_qty_td">
                    @if($purchase->qty_type == 'compound')
                        {{ $purchase->comp_qty }}    
                    @elseif($purchase->qty_type == 'alternate')
                        {{ $purchase->alt_qty }}
                    @else
                        {{ $purchase->qty }}
                    @endif
                </td>
                <td class="item_qty_measuring_td">{{ $purchase->item_measuring_unit }}</td>
                <td class="item_free_qty_td">{{ $purchase->free_qty }}</td>
                @endif
                <td class="item_price_td">{{ $purchase->price }}</td>
                <td class="item_amount_td">{{ $purchase->item_total }}</td>
                @if($bill->is_add_lump_sump == 0)
                <td class="item_cess_td">{{ $purchase->cess ?? 'NA' }}</td>
                @endif
                <td class="item_gst_td">{{ $purchase->gst_rate }}</td>
                <td class="item_calculated_gst_td">{{ $purchase->gst }}</td>
                <td><button type="button" class="btn btn-link edit-item" data-source="{{ $purchase->id }}" data-gst_tax_type="{{ $purchase->item_tax_type }}" data-base_unit="{{ $purchase->item->measuring_unit }}" data-base_unit_decimal_place="{{ $purchase->item->measuring_unit_decimal_place }}" data-alternate_unit="{{ $purchase->item->alternate_measuring_unit }}" data-alternate_unit_decimal_place="{{ $purchase->item->alternate_unit_decimal_place }}" data-compound_unit="{{ $purchase->item->compound_measuring_unit }}" data-compound_unit_decimal_place="{{ $purchase->item->compound_unit_decimal_place }}" data-measuring_unit="{{ $purchase->item_measuring_unit }}" data-free_qty="{{ $purchase->free_qty ?? 0 }}" data-cess="{{ $purchase->cess ?? 0 }}" data-is_add_lump_sump="{{ $bill->is_add_lump_sump }}">Edit</button></td>
            </tr>
            @endforeach
        </tbody>
    </table>

        {{-- <input type="hidden" name="bill_id" value="{{ $bill->id }}" />
        <input type="hidden" name="bill_no" value="{{ $bill->bill_no }}" disabled />
        <input type="hidden" name="party" value="{{ $bill->party->id }}" />
        <input type="hidden" name="bill_date" value="{{ $bill->bill_date }}" /> --}}
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Mode of Payment</label><br>
                    <div class="row">
                        <div class="col-md-3">
                            <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( 
                                $bill->type_of_payment == 'combined' || 
                                $bill->type_of_payment == 'cash+pos+discount' || 
                                $bill->type_of_payment == 'cash+bank+discount' || 
                                $bill->type_of_payment == 'cash+discount' || 
                                $bill->type_of_payment == 'cash+bank+pos' || 
                                $bill->type_of_payment == 'bank+cash' || 
                                $bill->type_of_payment == 'pos+cash' || 
                                $bill->type_of_payment == 'cash' ) checked="checked" @endif @endif /> <label for="cash">Cash</label>
                        </div>

                        <div class="col-md-9">
                            <div class="form-group" id="cash-list" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( $bill->type_of_payment == 'combined' || 
                                $bill->type_of_payment == 'cash+pos+discount' || 
                                $bill->type_of_payment == 'cash+bank+discount' || 
                                $bill->type_of_payment == 'cash+discount' || 
                                $bill->type_of_payment == 'cash+bank+pos' || 
                                $bill->type_of_payment == 'bank+cash' || 
                                $bill->type_of_payment == 'pos+cash' || 
                                $bill->type_of_payment == 'cash' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" value="{{ $bill->cash_payment }}">
                                <hr>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( 
                                    $bill->type_of_payment == 'combined' || 
                                    $bill->type_of_payment == 'bank+pos+discount' || 
                                    $bill->type_of_payment == 'cash+bank+discount' || 
                                    $bill->type_of_payment == 'bank+discount' || 
                                    $bill->type_of_payment == 'cash+bank+pos' || 
                                    $bill->type_of_payment == 'bank+cash' || 
                                    $bill->type_of_payment == 'pos+bank' || 
                                    $bill->type_of_payment == 'bank' ) checked="checked" @endif @endif> <label for="bank">Bank</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group" id="bank-list" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( $bill->type_of_payment == 'combined' || 
                                $bill->type_of_payment == 'bank+pos+discount' || 
                                $bill->type_of_payment == 'cash+bank+discount' || 
                                $bill->type_of_payment == 'bank+discount' || 
                                $bill->type_of_payment == 'cash+bank+pos' || 
                                $bill->type_of_payment == 'bank+cash' || 
                                $bill->type_of_payment == 'pos+bank' || 
                                $bill->type_of_payment == 'bank' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                <div class="form-group">
                                    <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" value="{{ $bill->bank_payment }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" value="{{ $bill->bank_cheque }}">
                                </div>
                                <div class="form-group">
                                    <label>Bank List</label>
                                    <select class="form-control" name="bank">
                                        @if(count($banks) > 0)
                                            @foreach($banks as $bank)
                                                <option @if($bill->bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( 
                                    $bill->type_of_payment == 'combined' || 
                                    $bill->type_of_payment == 'bank+pos+discount' || 
                                    $bill->type_of_payment == 'cash+pos+discount' || 
                                    $bill->type_of_payment == 'pos+discount' || 
                                    $bill->type_of_payment == 'cash+bank+pos' || 
                                    $bill->type_of_payment == 'pos+cash' || 
                                    $bill->type_of_payment == 'pos+bank' || 
                                    $bill->type_of_payment == 'pos' ) checked="checked" @endif @endif> <label for="pos">POS</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group" id="pos-bank-list"  @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( 
                                $bill->type_of_payment == 'combined' || 
                                $bill->type_of_payment == 'bank+pos+discount' || 
                                $bill->type_of_payment == 'cash+pos+discount' || 
                                $bill->type_of_payment == 'pos+discount' || 
                                $bill->type_of_payment == 'cash+bank+pos' || 
                                $bill->type_of_payment == 'pos+cash' || 
                                $bill->type_of_payment == 'pos+bank' || 
                                $bill->type_of_payment == 'pos' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                <div class="form-group">
                                    <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" value="{{ $bill->pos_payment }}">
                                </div>
                                <div class="form-group">
                                    <label>POS Bank List</label>
                                    <select class="form-control" name="pos_bank">
                                        @if(count($banks) > 0)
                                            @foreach($banks as $bank)
                                                <option @if($bill->pos_bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <input type="checkbox" name="type_of_payment[]" value="cash_discount" id="cash_discount" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( 
                                    $bill->type_of_payment == 'combined' || 
                                    $bill->type_of_payment == 'bank+pos+discount' || 
                                    $bill->type_of_payment == 'cash+pos+discount' || 
                                    $bill->type_of_payment == 'cash+bank+discount' || 
                                    $bill->type_of_payment == 'bank+discount' || 
                                    $bill->type_of_payment == 'cash+discount' || 
                                    $bill->type_of_payment == 'pos+discount' || 
                                    $bill->type_of_payment == 'discount' ) checked="checked" @endif @endif /> <label for="cash_discount">Cash Discount</label>
                        </div>
                        <div class="col-md-7">
                            {{-- <label>Discount Type</label> --}}
                            <div id="discount-list" class="row" @if(isset($bill->type_of_payment) && $bill->type_of_payment != "no_payment") @if( $bill->type_of_payment == 'combined' || 
                                $bill->type_of_payment == 'bank+pos+discount' || 
                                $bill->type_of_payment == 'cash+pos+discount' || 
                                $bill->type_of_payment == 'cash+bank+discount' || 
                                $bill->type_of_payment == 'bank+discount' || 
                                $bill->type_of_payment == 'cash+discount' || 
                                $bill->type_of_payment == 'pos+discount' || 
                                $bill->type_of_payment == 'discount' ) style="display:block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                <div class="col-md-6" style="padding-right: 0;">
                                    <select class="form-control" name="discount_type" id="discount_type">
                                        <option @if($bill->discount_type == 'fixed') selected="selected" @endif value="fixed">Fixed (Rs)</option>
                                        <option @if($bill->discount_type == 'percent') selected="selected" @endif value="percent">Percent (%)</option>
                                    </select>
                                </div>
                                <div class="col-md-6" style="padding-left: 0;">
                                    <input type="text" placeholder="Disc. Figure" name="discount_figure" id="discount_figure" class="form-control" value="{{ $bill->discount_figure }}" />
                                </div>
                                <div class="col-md-12">
                                    <input type="text" placeholder="Discount" name="discount_amount" id="discount_holder" class="form-control"  value="{{ $bill->discount_figure }}" readonly /> 
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <hr/>

                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amt Rec. (Rs)</label>
                                <input id="amount_paid" type="text" class="form-control" name="amount_paid" value="{{ $bill->amount_paid }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amt Rem. (Rs)</label>
                                <input id="amount_remaining" type="text" class="form-control" name="amount_remaining" value="{{ $bill->amount_remaining }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <textarea id="overall_remark" type="text" class="form-control" name="overall_remark" placeholder="Narration">{{ $bill->remark }}</textarea>
                </div>
            </div>
            
            <div class="col-md-4 col-md-offset-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Total Amt (Rs)</label>
                            <input type="text" class="form-control" name="item_total_amount" id="item_total_amount" value="{{ $bill->item_total_amount }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6" id="calculated_total_gst_block">
                        <div class="form-group">
                            <label>Total GST+CESS (Rs)</label>
                            <input type="text" class="form-control" value="{{ $bill->item_total_gst + $bill->item_total_cess }}" readonly>

                            <input type="hidden" name="total_cess_amounted" id="total_cess_amounted" value="{{ $bill->item_total_cess }}" />
                            <input type="hidden" name="total_gst_amounted" id="item_total_gst" value="{{ $bill->item_total_gst }}" />
                        </div>
                    </div>
                </div>

                <div class="form-group" id="tcs-block" @if(!$bill->tcs) style="display: none" @endif>
                    <label>TCS (Rs)</label>
                    <input type="text" id="tcs" name="tcs" class="form-control" value="{{ $bill->tcs }}" />
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Grand Total (Rs)</label>
                            <input type="text" id="total_amount_before_discount" name="total_amount_before_discount" class="form-control" value="{{ $bill->amount_before_round_off }}" readonly>
                        </div>
                    </div>
                    {{-- <div class="col-md-6"> --}}
                        {{-- <div class="form-group"> --}}
                            {{-- <label>Total Disc. (Rs)</label> --}}
                            <input type="hidden" id="total_discount" name="total_discount" class="form-control" value="{{ $bill->total_discount }}" readonly>
                        {{-- </div> --}}
                    {{-- </div> --}}
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Round off (Rs)</label>
                            @if(auth()->user()->roundOffSetting->sale_round_off_to == 'manual')
                            <select class="form-control" id="operation" name="round_off_operation"  style="width: 50%; float: left;">
                                <option @if($bill->round_off_operation == "+") selected="selected" @endif value="+">+</option>
                                <option @if($bill->round_off_operation == "-") selected="selected" @endif value="-">-</option>
                            </select>
                            @endif
                            <input type="text" class="form-control" id="round_offed" name="round_offed" @if(auth()->user()->roundOffSetting->sale_round_off_to != 'manual') readonly @endif data-roundType="{{ auth()->user()->roundOffSetting->sale_round_off_to }}" @if(auth()->user()->roundOffSetting->sale_round_off_to == 'manual') style="width: 50%; float: left;" @endif value="{{ $bill->round_offed }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">      
                            <label>Net Amount</label>
                            <input type="text" id="total_amount" name="total_amount" class="form-control" value="{{ $bill->total_amount }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Amt Rec. (Rs)</label>
                            <input id="amount_paid" type="text" class="form-control" name="amount_paid" value="{{ $bill->amount_paid }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Amt Rem. (Rs)</label>
                            <input id="amount_remaining" type="text" class="form-control" name="amount_remaining" value="{{ $bill->amount_remaining }}" readonly>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
        <button type="submit" class="btn btn-success" id="update_bill">Update Bill</button>
    </form>
</div>

<div class="modal" id="edit_item_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Edit Item</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('update.bill.item.form') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="source" id="item_source"  />
                    <input type="hidden" name="lump_sump" id="item_lump_sump" />
                    <div class="row">
                        <div class="col-md-6">
                            <input type="radio" name="item_tax_inclusive" id="item_tax_inclusive" value="inclusive_of_tax" /> <label for="item_tax_inclusive">Item is Incl of Taxes</label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="item_tax_inclusive" id="item_tax_exclusive" value="exclusive_of_tax" /> <label for="item_tax_exclusive">Item is Excl of Taxes</label>
                        </div>
                    </div>
                    {{-- <div class="row">
                        <div class="col-md-12">
                            <input type="checkbox" name="lump_sump" id="lump_sump" value="1" readonly="readonly" /> <label>Add Lump Sump</label>
                        </div>
                    </div> --}}
                    <div class="form-group">
                        <label>Product</label>
                        <input type="text" class="form-control" name="name" id="item_name" readonly/>
                    </div>
                    <div class="form-group">
                        <label>Rate</label>
                        <input type="text" class="form-control" name="rate" id="item_rate" />
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Discount Type</label>
                                <select class="form-control" id="item_discount_type" name="item_discount_type">
                                    <option selected disabled>Select Type</option>
                                    <option value="f">Fixed</option>
                                    <option value="%">Percentage</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label>Discount</label>
                                <input type="text" class="form-control" name="discount" id="item_discount" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="text" class="form-control" name="qty" id="item_qty" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Measuring Unit</label>
                                <select name="measuring_unit" id="item_measuring_unit" class="form-control select-measuring-unit">
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Free Quantity</label>
                                <input type="text" class="form-control" name="free_qty" id="free_qty" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>CESS</label>
                        <input type="text" class="form-control" name="cess" id="item_cess" />
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="text" class="form-control" name="amount" id="item_amount" readonly/>
                    </div>
                    {{-- <div class="form-group"> --}}
                        {{-- <label>Tax(%)</label> --}}
                        <input type="hidden" class="form-control" name="tax" id="item_tax" readonly/>
                    {{-- </div> --}}
                    {{-- <div class="form-group"> --}}
                        {{-- <label>Calc. Tax</label> --}}
                        <input type="hidden" class="form-control" name="calculated_tax" id="item_calculated_tax" readonly/>
                    {{-- </div> --}}
                    <button type="submit" class="btn btn-success">Update Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="show_options_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">More Options</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_buyer_name" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_buyer_name) checked @endif>Show Buyer Name</label>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_purchase_order" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_order) checked @endif>Show Purchase Order</label>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_reference_name" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_reference_name) checked @endif>Show Reference Name</label>
                </div>
                {{-- <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_gst_classification" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_gst_classification) checked @endif>Show GST Classification</label>
                </div> --}}
                {{-- <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_cess_charge" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_cess_charge) checked @endif>Show CESS Charge</label>
                </div> --}}
                {{-- <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_additional_charge">Show Additional Charge</label>
                </div> --}}
                {{-- <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_tcs_charge" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_tcs) checked @endif>Show TCS - Income tax</label>
                </div> --}}
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_consign_info" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_consign_info) checked @endif>Show Consigner &amp; Consignee Name &amp; Address</label>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_import_export_info" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_import_export_info) checked @endif>Show Export/Import Info</label>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

    <script>
        
        Number.prototype.toFixedDown = function(digits) {
            var re = new RegExp("(\\d+\\.\\d{" + digits + "})(\\d)"),
                m = this.toString().match(re);
            return m ? parseFloat(m[1]) : this.valueOf();
        };

        function roundToSomeNumber(num) {
            
            num = parseFloat(num);
            
            // return num.toFixedDown(2);

            return +num.toFixed(2);
            
        }

        function noRoundOff(num) {
            // return num.toFixedDown(2);

            num = parseFloat(num);

            return +num.toFixed(2);
        }

        $(document).ready(function(){

            $("#select_options").on("click", function () {
                $("#show_options_modal").modal("show");
            });

            $("#show_buyer_name").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#buyer_name").parent().show();
                } else {
                    $("#buyer_name").parent().hide();
                }
            });

            $("#show_consign_info").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#c_c_info div").show();
                } else {
                    $("#c_c_info div").hide();
                }
            });

            $("#show_import_export_info").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#i_e_info div").show();
                } else {
                    $("#i_e_info div").hide();
                }
            });

            $("#show_purchase_order").on("change", function () {
                if( $(this).is(":checked") ) {
                    $("#purchase_order_block").show();
                } else {
                    $("#purchase_order_block").hide();
                }
            });


            $("#show_reference_name").on("change", function () {
                if( $(this).is(":checked") ) {
                    $("#reference_name_block").show();
                } else {
                    $("#reference_name_block").hide();
                }
            });

            $("#bill_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "bill_date_validation_error", "#", "update_bill", "#");
            });
            
            $('input[name="type_of_payment[]"]').on("change", function(){

                var type_of_payment = $(this).val();

                // console.log("outside " + type_of_payment);

                if($(this).is(':checked')){
                    if (type_of_payment == 'bank') {
                        $("#bank-list").show();
                    } else if(type_of_payment == 'pos') {
                        $("#pos-bank-list").show();
                    } else if(type_of_payment == 'cash'){
                        $("#cash-list").show();
                    } else if(type_of_payment == 'cash_discount'){
                        $("#discount-list").show();
                    }
                } else {
                    // console.log("inside " + type_of_payment);
                    if (type_of_payment == 'bank') {
                        $("#bank-list").hide();
                        $("#banked_amount").val(0).trigger("keyup");
                    } else if(type_of_payment == 'pos') {
                        $("#pos-bank-list").hide();
                        $("#posed_amount").val(0).trigger("keyup");
                    } else if(type_of_payment == 'cash'){
                        $("#cash-list").hide();
                        $("#cashed_amount").val(0).trigger("keyup");
                    } else if(type_of_payment == 'cash_discount'){
                        $("#discount-list").hide();
                        $("#discount_figure").val(0).trigger("keyup");
                        $("#discount_holder").val(0).trigger("keyup");
                    }
                }

            });

            $(document).on('click', ".edit-item", function() {

                var tr = $(this).closest('tr');

                var item_name_td = tr.find('.item_name_td').text();
                var item_discount_td = tr.find('.item_discount_td .discount_value').text();
                var item_discount_type_td = tr.find('.item_discount_td .discount_type_value').text();
                var item_qty_td = tr.find('.item_qty_td').text().trim();
                var item_price_td = tr.find('.item_price_td').text();
                var item_amount_td = tr.find('.item_amount_td').text();
                var item_gst_td = tr.find('.item_gst_td').text();
                var item_calculated_gst_td = tr.find('.item_calculated_gst_td').text();

                var item_source = $(this).attr("data-source");
                var item_gst_tax_type = $(this).attr("data-gst_tax_type");

                var item_base_unit = $(this).attr("data-base_unit");
                var item_base_unit_decimal_place = $(this).attr("data-base_unit_decimal_place") || 0;

                var item_alternate_unit = $(this).attr("data-alternate_unit");
                var item_alternate_unit_decimal_place = $(this).attr("data-alternate_unit_decimal_place") || 0;
                
                var item_compound_unit = $(this).attr("data-compound_unit");
                var item_compound_unit_decimal_place = $(this).attr("data-compound_unit_decimal_place") || 0;
                
                var item_measuring_unit = $(this).attr("data-measuring_unit");
                var item_free_qty = $(this).attr("data-free_qty");
                var item_cess = $(this).attr("data-cess");

                var lump_sump = $(this).attr("data-is_add_lump_sump");

                if(lump_sump == 1){
                    $("#item_qty").attr("readonly", true);
                    $("#free_qty").attr("readonly", true);
                    $("#item_discount").attr("readonly", true);
                    $("#item_cess").attr("readonly", true);
                    $("#item_discount_type").attr("disabled", true);
                    $("#item_measuring_unit").attr("disabled", true);
                    $("#item_lump_sump").val(1);
                }

                // console.log(item_name_td);

                $('#item_name').val(item_name_td);
                $('#item_discount_type').val(item_discount_type_td);
                $('#item_discount').val(item_discount_td);
                $('#item_qty').val(item_qty_td);
                $('#free_qty').val(item_free_qty);
                $('#item_rate').val(item_price_td);
                $('#item_amount').val(item_amount_td);
                $('#item_tax').val(item_gst_td);
                $('#item_cess').val(item_cess);
                $('#item_calculated_tax').val(item_calculated_gst_td);

                $('#item_source').val(item_source);

                if( item_gst_tax_type == 'exclusive_of_tax' ){
                    $("#item_tax_exclusive").prop('checked', true);
                }

                if( item_gst_tax_type == 'inclusive_of_tax' ){
                    $("#item_tax_inclusive").prop('checked', true);
                }

                if(lump_sump == 1){
                    $("#lump_sump").prop('checked', true);
                    // $("#lump_sump").val(lump_sump);
                } else {
                    $("#item_lump_sump").val(0);
                }

                $(".select-measuring-unit").html('');

                if(item_base_unit != ""){
                    var isSelected = '';

                    if(item_measuring_unit == item_base_unit){
                        isSelected = 'selected="selected"';
                    }

                    $(".select-measuring-unit").append(`
                        <option data-decimal_place="${item_base_unit_decimal_place}" ${isSelected} value="${item_base_unit}">${item_base_unit}</option>
                    `);
                }

                if(item_alternate_unit != ""){
                    var isSelected = '';

                    if(item_measuring_unit == item_alternate_unit){
                        isSelected = 'selected="selected"';
                    }

                    $(".select-measuring-unit").append(`
                        <option data-decimal_place="${item_alternate_unit_decimal_place}" ${isSelected} value="${item_alternate_unit}">${item_alternate_unit}</option>
                    `);
                }

                if(item_compound_unit != ""){
                    var isSelected = '';

                    if(item_measuring_unit == item_compound_unit){
                        isSelected = 'selected="selected"';
                    }

                    $(".select-measuring-unit").append(`
                        <option data-decimal_place="${item_compound_unit_decimal_place}" ${isSelected} value="${item_compound_unit}">${item_compound_unit}</option>
                    `);
                }

                $("#edit_item_modal").modal("show");
            });

            $("#item_discount").on("keyup", function() {
                calculate_amount_and_tax();
            });

            $("#item_qty").on("keyup", function(){
                calculate_amount_and_tax();
            });

            $( "#item_qty" ).blur(function() {
                const decimal_place = $('#item_measuring_unit option:selected').attr('data-decimal_place') || 0;

                if(this.value){
                    this.value = parseFloat(this.value).toFixed(decimal_place);
                }
                $("#item_qty").trigger("keyup");
            });

            $("#item_rate").on("keyup", function(){
                calculate_amount_and_tax();
            });

            $('input[name="item_tax_inclusive"]').on("change", function () {
                console.log("change");
                calculate_amount_and_tax();
            });

            $('#lump_sump').on("change", function () {

                if($(this).is(":checked")){
                    $("#item_qty").attr("readonly", true);
                } else {
                    $("#item_qty").attr("readonly", false);
                }

                calculate_amount_and_tax();
            });

            $(document).on("keyup", "#cashed_amount", function() {
                var cashed_amount = $(this).val();
                var banked_amount = $("#banked_amount").val();
                var posed_amount = $("#posed_amount").val();

                if( cashed_amount == '' ) {
                    cashed_amount = 0;
                }

                if( banked_amount == '' ) {
                    banked_amount = 0;
                }

                if( posed_amount == '' ) {
                    posed_amount = 0;
                }

                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            $(document).on("keyup", "#banked_amount", function() {
                var banked_amount = $(this).val();
                var cashed_amount = $("#cashed_amount").val();
                var posed_amount = $("#posed_amount").val();

                if( cashed_amount == '' ) {
                    cashed_amount = 0;
                }

                if( banked_amount == '' ) {
                    banked_amount = 0;
                }

                if( posed_amount == '' ) {
                    posed_amount = 0;
                }

                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            $(document).on("keyup", "#posed_amount", function() {
                var posed_amount = $(this).val();
                var cashed_amount = $("#cashed_amount").val();
                var banked_amount = $("#banked_amount").val();

                if( cashed_amount == '' ) {
                    cashed_amount = 0;
                }

                if( banked_amount == '' ) {
                    banked_amount = 0;
                }

                if( posed_amount == '' ) {
                    posed_amount = 0;
                }

                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            $(document).on("keyup", "#amount_paid", function(){
                var amount_paid = $(this).val();
                var total_amount = $("#total_amount").val();

                if(total_amount == ""){
                    total_amount = 0;
                }

                if(amount_paid == ""){
                    amount_paid = 0;
                }

                if(total_amount !== ""){
                    var amount_remaining = total_amount - amount_paid;
                    amount_remaining = noRoundOff(amount_remaining);
                    $("#amount_remaining").val(amount_remaining);
                }
            });

            // $('input[name="tax_inclusive"]').on("change", function(){

            //     var qty = [];
            //     var discount = [];
            //     var rate = [];
            //     var gst = [];
            //     var amount = [];
            //     var calc_tax = [];

            //     $("#bill_amount_type").val($(this).val());

                
            //     $("tbody tr").each(function() {
            //         qty.push( parseFloat($(this).find(".item_qty_td").text()) );
            //         rate.push( parseFloat($(this).find(".item_price_td").text()) );
            //         gst.push( parseFloat($(this).find(".item_gst_td").text()) );
            //         amount.push( parseFloat($(this).find(".item_amount_td").text()) );
            //         calc_tax.push( parseFloat($(this).find(".item_calculated_gst_td").text()) );
            //     });
                
            //     // console.log(amount);
            //     // console.log(calc_tax);

            //     for (var i = 0; i < amount.length; i++) {
            //         if(amount[i].innerText !== ""){

            //             if( $(this).val() == 'inclusive_of_tax' ){
            //                 let first_part = rate[i];
            //                 let second_part = rate[i] * ( 100 / ( 100 + gst[i] ) );

            //                 calc_tax[i] = (first_part - second_part) * qty[i];

            //                 amount[i] -= calc_tax[i];
            //             }


            //             if( $(this).val() == 'exclusive_of_tax' ){
            //                 let first_part = rate[i] * qty[i];
            //                 let second_part = gst[i];

            //                 calc_tax[i] = first_part * second_part / 100;

            //                 amount[i] = first_part;
            //             }

            //         }
            //     }

            //     var j = 0;
            //     var total_amount = 0;
            //     var total_tax = 0;
            //     var amount_paid = $("#amount_paid").val();

            //     // console.log(amount);
            //     // console.log(calc_tax);

            //     $("tbody tr").each(function(j) {

            //         @if(auth()->user()->roundOffSetting->purchase_total == "yes")
            //             amount[j] = roundToTwo(amount[j]);
            //         @else
            //             amount[j] = noRoundOff(amount[j]);
            //         @endif

            //         @if(auth()->user()->roundOffSetting->purchase_gst_total == "yes")
            //             calc_tax[j] = roundToTwo(calc_tax[j]);
            //         @else
            //             calc_tax[j] = noRoundOff(calc_tax[j]);
            //         @endif
                    
            //         total_amount += amount[j];
            //         total_tax += calc_tax[j];

            //         $(this).find(".item_amount_td").text( amount[j] );
            //         $(this).find(".item_calculated_gst_td").text( calc_tax[j] );

            //         total_calc_amount = total_amount + total_tax;

            //         $("#item_total_amount").val(total_amount);
            //         $("#item_total_gst").val(total_tax);
            //         $("#total_amount").val(total_calc_amount);
            //         $("#amount_remaining").val(noRoundOff(total_calc_amount - amount_paid));

            //         j++
            //     });
            // });

            $("#tcs").on("keyup", function () {
                add_tcs();
            });

            function add_tcs() {
                var item_total_amount = $("#item_total_amount").val();
                var item_total_gst = $("#item_total_gst").val();
                var tcs_amounted = $("#tcs").val();
                
                var total_amount_before_discount = $("#total_amount_before_discount").val();

                console.log(tcs_amounted);

                if(item_total_amount == '') {
                    item_total_amount = 0;
                }

                if(item_total_gst == ''){
                    item_total_gst = 0;
                }

                if(tcs_amounted == '') {
                    tcs_amounted = 0;
                }

                var total_after_tcs = parseFloat(item_total_amount) + parseFloat(item_total_gst) + parseFloat(tcs_amounted);

                @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                    total_after_tcs = roundToSomeNumber(total_after_tcs);
                @else
                    total_after_tcs = noRoundOff(total_after_tcs);
                @endif

                $("#total_amount_before_discount").val(total_after_tcs);

                var total_discount = $("#total_discount").val();

                if(total_discount == ''){
                    total_discount = 0;
                }

                var total_amount_after_discount = total_after_tcs - total_discount;

                $("#total_amount").val(total_amount_after_discount);

                $("#round_offed").trigger('keyup');

            }

            function remove_tcs() {
                $("#tcs").val(0);
                add_tcs();
            }

            $("#round_offed").on("keyup", function() {
                roundOffed();
            });

            $("#operation").on("change", function () {
                roundOffed();
            });

            function roundOffed() {
                var roundType = $("#round_offed").data("roundtype");
                var total_amount = $("#total_amount").val();
                var round_off = $("#round_offed").val() == '' ? 0 : $("#round_offed").val();
                var operation = $("#operation option:selected").val() == '' ? '+' : $("#operation option:selected").val();
                var amount_paid = $("#amount_paid").val() == '' ? 0 : $("#amount_paid").val();

                if(roundType == 'manual'){

                    // console.log(operation);

                    if(operation == '-'){
                        var amount_to_pay = total_amount - round_off;
                    } else if(operation == '+') {
                        var amount_to_pay = parseFloat(total_amount) + parseFloat(round_off);
                    }

                    // console.log(amount_to_pay);

                    $("#total_amount").val(noRoundOff(amount_to_pay));

                    var amount_paid = $("#amount_paid").val() == '' ? 0 : $("#amount_paid").val();

                    var amount_remaining = amount_to_pay - amount_paid;

                    $("#amount_remaining").val(noRoundOff(amount_remaining));
                }
            }

            $("#discount_type").on("change", function () {
                calculate_discount();
            });

            $("#discount_figure").on("keyup", function () {
                calculate_discount();
            });

            function calculate_discount() {
                // var discount_type = $("#discount_type option:selected").val();

                // // console.log(discount_type);

                // var item_total_amount = $("#item_total_amount").val();
                // var item_total_gst = $("#item_total_gst").val();

                // if(item_total_amount == ''){
                //     item_total_amount = 0;
                // }

                // if(item_total_gst == ''){
                //     item_total_gst = 0;
                // }

                // var total_pending_payment_amount_in_modal = parseFloat(item_total_amount) + parseFloat(item_total_gst);
                // var discount_figure = 0;

                // // if(discount_figure == ''){
                // //     discount_figure = 0;
                // // }

                // if(total_pending_payment_amount_in_modal == ''){
                //     total_pending_payment_amount_in_modal = 0; 
                // }

                // if(discount_type == 'fixed') {
                //     discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();
                // }

                // if(discount_type == 'percent'){
                //     discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();
                //     discount_figure = (discount_figure * total_pending_payment_amount_in_modal) / 100;
                // }

                // discount_figure = noRoundOff(discount_figure);

                // $("#total_discount").val(discount_figure);
                // $("#discount_holder").val(discount_figure); 

                // $("#total_discount").trigger("keyup");

                var cashed_amount = $("#cashed_amount").val();
                var banked_amount = $("#banked_amount").val();
                var posed_amount = $("#posed_amount").val();

                var discount_type = $("#discount_type option:selected").val();
                var discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();


                if( cashed_amount == '' ) {
                    cashed_amount = 0;
                }

                if( banked_amount == '' ) {
                    banked_amount = 0;
                }

                if( posed_amount == '' ) {
                    posed_amount = 0;
                }

                if( cash_discount == '' ) {
                    cash_discount = 0;
                }

                if( discount_figure == '' ) {
                    discount_figure = 0;
                }

                var cash_discount = calculate_cash_discount(discount_type, discount_figure);

                console.log("cash_discount", cash_discount)
                
                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount) + parseFloat(cash_discount);

                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            }

            function calculate_cash_discount(discount_type = 'fixed', discount_figure = 0) {

                var amount_to_pay = $("#total_amount").val();

                if(discount_type == 'percent'){
                    discount_figure = (discount_figure * amount_to_pay) / 100;
                }

                discount_figure = noRoundOff(discount_figure);

                $("#discount_holder").val(discount_figure);

                // they dont want to subtract it from grand total but amount remaining
                return discount_figure;
            }

            $("#total_discount").on("keyup", function () {
                let discount_amount = $(this).val();
                let item_total_amount = $("#item_total_amount").val();
                let item_total_gst = $("#item_total_gst").val();
                let amount_paid = $("#amount_paid").val();
                

                if( discount_amount == '' ){
                    discount_amount = 0;
                }

                if( item_total_amount == '' ){
                    item_total_amount = 0;
                }

                if( item_total_gst == '' ){
                    item_total_gst = 0;
                }

                if( amount_paid == '' ){
                    amount_paid = 0;
                }
                

                total_amount = ((parseFloat(item_total_amount) + parseFloat(item_total_gst)) - parseFloat(discount_amount));

                @if(auth()->user()->roundOffSetting->sale_total_amount == "yes")
                    total_amount = roundToSomeNumber(total_amount);
                @else
                    total_amount = noRoundOff(total_amount);
                @endif

                $("#total_amount").val(total_amount);

                var rounded_off_total_amount = total_amount;
                var round_off_difference = 0;

                @if(auth()->user()->roundOffSetting->sale_round_off_to == "upward")
                    rounded_off_total_amount = Math.ceil(total_amount);
                    round_off_difference = rounded_off_total_amount - total_amount; 
                @elseif(auth()->user()->roundOffSetting->sale_round_off_to == "downward")
                    rounded_off_total_amount = Math.floor(total_amount);
                    round_off_difference = rounded_off_total_amount - total_amount;
                @elseif(auth()->user()->roundOffSetting->sale_round_off_to == "normal")
                    rounded_off_total_amount = Math.round(total_amount);
                    round_off_difference = rounded_off_total_amount - total_amount;
                @elseif(auth()->user()->roundOffSetting->sale_round_off_to == "manual")
                    rounded_off_total_amount = total_amount;
                    round_off_difference = 0;
                @endif

                // console.log(total_amount);
                round_off_difference = noRoundOff(round_off_difference);
                $("#round_offed").val(round_off_difference);
                $("#amount_to_pay").val(noRoundOff(rounded_off_total_amount));

                var amount_remaining = rounded_off_total_amount - amount_paid;

                $("#amount_remaining").val(noRoundOff(amount_remaining));
            });

            $("#item_discount_type").on("change", function() {
                calculate_amount_and_tax();
            });

            function calculate_amount_and_tax() {
                // var i_discount = $("#item_discount").val();
                // var i_qty = $("#item_qty").val();
                // var i_rate = $("#item_rate").val();
                // var i_tax_per = $("#item_tax").val();

                // var i_tax_type = $('input[name="item_tax_inclusive"]:checked').val();

                // var party_type = $("#party_type").val();

                // // var i_lump_sump = $('#lump_sump:checked').val();

                // var i_lump_sump = $('#item_lump_sump').val();

                // if( i_tax_type == '' ) {
                //     i_tax_type = 'exclusive_of_tax';
                // }

                // if(i_discount == '') {
                //     i_discount = 0;
                // }

                // if(i_qty == '') {
                //     i_qty = 0;
                // }

                // // if the item is lump sum then qty is not required that is why defaults to 1 rather than 0;
                // if(i_lump_sump == 1) {
                //     i_qty = 1;
                // }

                // if(i_rate == '') {
                //     i_rate = 0;
                // }

                // if(i_tax_per == ''){
                //     i_tax_per = 0;
                // }

                // i_item_rate = i_qty * i_rate;

                // if(i_discount > 0) {
                //     i_item_rate = i_item_rate - (i_item_rate * i_discount / 100);
                // }

                // if(party_type == 0 || party_type == 2 || party_type == 3){
                //     i_calculated_gst = 0;
                // } else {

                //     if( i_tax_type == 'exclusive_of_tax' ){
                //         i_calculated_gst = i_item_rate * i_tax_per / 100;
                //     }

                //     else if( i_tax_type == 'inclusive_of_tax' ){

                //         if (i_discount > 0) {
                //             var first_part = parseFloat(i_rate) - (parseFloat(i_rate) * parseFloat(i_discount) / 100);
                //         } else {
                //             var first_part = parseFloat(i_rate); 
                //         }

                //         var second_part = (first_part * (100 / ( 100 + parseFloat(i_tax_per) )));

                //         i_calculated_gst = (first_part - second_part) * i_qty;

                //         i_item_rate -= i_calculated_gst;
                //     }
                // }
                
                // i_item_rate = noRoundOff(i_item_rate);
                // i_calculated_gst = noRoundOff(i_calculated_gst);

                // console.log(i_item_rate);

                // $("#item_amount").val( i_item_rate );

                // $("#item_calculated_tax").val( i_calculated_gst );

                var i_discount = $("#item_discount").val();
                var i_discount_type = $("#item_discount_type").val();
                var i_qty = $("#item_qty").val();
                var i_rate = $("#item_rate").val();
                var i_tax_per = $("#item_tax").val();

                var i_tax_type = $('input[name="item_tax_inclusive"]:checked').val();

                // var i_lump_sump = $('#lump_sump:checked').val();
                // console.log("lump sump", i_lump_sump);

                var i_lump_sump = $('#item_lump_sump').val();

                if( i_tax_type == '' ) {
                    i_tax_type = 'exclusive_of_tax';
                }

                if(i_discount == '') {
                    i_discount = 0;
                }

                if(i_discount_type == '') {
                    i_discount_type = 'f';
                }

                if(i_qty == '') {
                    i_qty = 0;
                }

                // if the item is lump sum then qty is not required that is why defaults to 1 rather than 0;
                if(i_lump_sump == 1) {
                    i_qty = 1;
                }

                if(i_rate == '') {
                    i_rate = 0;
                }

                if(i_tax_per == ''){
                    i_tax_per = 0;
                }

                i_item_rate = i_qty * i_rate;

                // console.log('tax_per '+i_tax_per);

                if(i_discount > 0) {
                    if(i_discount_type == 'f'){
                        i_item_rate = i_item_rate - i_discount;
                    } else {
                        i_item_rate = i_item_rate - (i_item_rate * i_discount / 100);
                    }
                }

                if( i_tax_type == 'exclusive_of_tax' ){
                    i_calculated_gst = i_item_rate * parseFloat(i_tax_per) / 100;
                }

                if( i_tax_type == 'inclusive_of_tax' ){
                    if (i_discount > 0) {
                        var first_part = parseFloat(i_rate) - (parseFloat(i_rate) * parseFloat(i_discount) / 100);
                    } else {
                        var first_part = parseFloat(i_rate); 
                    }
                    // console.log('tax_per' + i_tax_per);
                    // console.log('rate' + i_rate);

                    var second_part = (parseFloat(first_part) * (100 / ( 100 + parseFloat(i_tax_per) )));

                    // console.log('first_part ' + first_part);
                    // console.log('second_part ' + second_part);
                    // calcu_2 = ;
                    // calcu_1 = i_rate * calcu_2;
                    // console.log('calcu_2 ' + calcu_2);
                    // console.log('calcu_1 ' + calcu_1);
                    // console.log('qty ' + i_qty);

                    i_calculated_gst = (first_part - second_part) * i_qty;

                    // console.log(calculated_gst);

                    i_item_rate -= i_calculated_gst;
                }

                i_item_rate = noRoundOff(i_item_rate);
                i_calculated_gst = noRoundOff(i_calculated_gst);

                $("#item_amount").val( i_item_rate );

                console.log(i_tax_type);

                $("#item_calculated_tax").val( i_calculated_gst );
            }
        });

        $("#total_discount").on("keyup", function () {
            let discount_amount = $(this).val();
            let item_total_amount = $("#item_total_amount").val();
            let item_total_gst = $("#item_total_gst").val();
            let amount_paid = $("#amount_paid").val();
            

            if( discount_amount == '' ){
                discount_amount = 0;
            }

            if( item_total_amount == '' ){
                item_total_amount = 0;
            }

            if( item_total_gst == '' ){
                item_total_gst = 0;
            }

            if( amount_paid == '' ){
                amount_paid = 0;
            }

            total_amount = ((parseFloat(item_total_amount) + parseFloat(item_total_gst)) - parseFloat(discount_amount));
            amount_remaining = parseFloat(total_amount) - parseFloat(amount_paid);

            @if(auth()->user()->roundOffSetting->purchase_total == "yes")
                total_amount = noRoundOff(total_amount);
            @else
                total_amount = noRoundOff(total_amount);
            @endif

            $("#total_amount").val(total_amount);
            $("#amount_remaining").val(noRoundOff(amount_remaining));
        });

        $("#bill_no").on("keyup", function () {
            // console.log($(this).val());
            $('input[name="bill_no"]').val($(this).val());
        });

        $("#bill_date").on("keyup", function () {
            $('input[name="bill_date"]').val($(this).val());
        });

        $(document).on("click", "#want_to_update_bill_no", function() {
            $("#bill_no").attr("disabled", false);
            $('input[name="bill_no"]').attr("disabled", false);
            $("#want_to_update_bill_no").hide();
            $("#dont_want_to_update_bill_no").show();
        });

        $(document).on("click", "#dont_want_to_update_bill_no", function() {
            $("#bill_no").attr("disabled", true);
            $('input[name="bill_no"]').attr("disabled", true);
            $("#dont_want_to_update_bill_no").hide();
            $("#want_to_update_bill_no").show();
        });

        $("#buyer_name").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'buyer_name';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#purchase_order_no").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'purchase_order_no';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#reference_name").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'reference_name';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#shipping_bill_no").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'shipping_bill_no';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#date_of_shipping").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'date_of_shipping';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#code_of_shipping_port").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'code_of_shipping_port';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#conversion_rate").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'conversion_rate';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#currency_symbol").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'currency_symbol';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#export_type").on("change", function() {
            const id = '{{ $bill->id }}';
            const type = 'export_type';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#consignee_info").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'consignee_info';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        $("#consignor_info").on("keyup", function() {
            const id = '{{ $bill->id }}';
            const type = 'consignor_info';
            const value = $(this).val();
            update_columns(id, type, value);
        });

        function update_columns(id, type, value) {
            $.ajax({
                type: 'post',
                url: '{{ route("update.bill.column") }}',
                data: {
                    "id": id,
                    "type": type,
                    "value": value,
                    "_token": '{{ csrf_token() }}'
                },
            });
        }
    </script>

@endsection
