@extends('layouts.dashboard')

@section('content')

@if(request()->segment(3))
{!! Breadcrumbs::render('create-purchase-from-purchase-order', request()->segment(3)) !!}
@else
{!! Breadcrumbs::render('create-purchase') !!}
@endif

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
    <form method="POST" action="{{ route('purchase.store') }}" id="create-purchase">
    {{ csrf_field() }}
    <input type="hidden" name="submit_type" id="submit_type" />
    <div class="row">
        <div class="form-group col-md-6">
            @if($user_profile->add_lump_sump != 'yes')
            <div class="col-md-6 inc_exc_tax">
                <input type="radio" name="tax_inclusive" id="tax_inclusive1" value="inclusive_of_tax" @if( old('tax_inclusive') != null && old('tax_inclusive') == 'inclusive_of_tax' ) checked @endif /> <label for="tax_inclusive1">Bill is Incl of Taxes</label>
            </div>
            <div class="col-md-6 inc_exc_tax">
                <input type="radio" name="tax_inclusive" id="tax_inclusive2" value="exclusive_of_tax" @if( old('tax_inclusive') == null ) checked @else @if( old('tax_inclusive') == 'exclusive_of_tax' ) checked @endif @endif /> <label for="tax_inclusive2">Bill is Excl of Taxes</label>
            </div>
            @else
                <input type="hidden" name="tax_inclusive" value="exclusive_of_tax"  />
            @endif
        </div>
        <div class="col-md-6 text-right">
            <button type="button" class="btn btn-success" id="select_options"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;More Options</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <div class="form-group">
                    <label>Choose Party</label>
                    <select class="form-control" id="party" name="party" required>
                        <option disabled selected>Choose a party</option>
                        <option value="add party">Add Party</option>
                        @foreach($parties as $party)
                            <option @if( isset($involved_party) ) @if($involved_party->id == $party->id) selected="selected" @endif @endif @if( old('party') != null && old('party') == $party->id ) selected="selected" @endif data-party_type="{{ $party->registered }}" value="{{ $party->id }}">{{ $party->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" id="buyer_name" name="buyer_name" class="form-control" placeholder="Buyer Name" style="display: none;" />
                </div>
                {{-- <p><a href="{{ route('party.create') }}">Add Party</a></p> --}}
            </div>
            
        </div>
        @php $showErrors = $myerrors->has('bill_no') ? $myerrors->has('bill_no') : $errors->has('bill_no') @endphp
        <div class="col-md-2">
            <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                <label>Bill No.</label>
                <input id="bill_no" type="text" class="form-control" name="bill_no" placeholder="Bill NO." @if ( $myerrors->has('bill_no') ) required @else @if($errors->has('bill_no'))  @else @if(auth()->user()->purchaseSetting->bill_no_type == 'auto') value="{{ $bill_no }}" readonly @endif @endif @endif>
                @if ($myerrors->has('bill_no'))
                    <span class="help-block">
                        <ul>
                            @foreach( $myerrors['bill_no'] as $error )
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </span>
                @endif
                <p id="bill_no_error_msg" style="color: red; font-size: 12px;"></p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Bill Date</label>
                <input type="text" id="purchase_bill_date" class="form-control" name="bill_date" placeholder="DD/MM/YYYY" @if( old('bill_date') != null ) value="{{ old('bill_date') }}" @endif autocomplete="off" maxlength="10">
            </div>
            <p id="bill_date_error" style="font-size: 12px; color: red;"></p>
        </div>
        <div class="col-md-2">
            <div class="form-group" id="purchase_order_block" @if( !isset($purchase_order_no) ) style="display: none;" @endif>
                <label>Purchase Order No.</label>
                <input type="text" class="form-control" id="purchase_order_no" name="purchase_order_no" @if( isset($purchase_order_no) ) value="{{ $purchase_order_no }}" readonly @endif placeholder="Purchase Order NO." />
                <div class="autopurchaseorder"></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group" id="reference_name_block" @if(! isset($reference_name) ) style="display: none;" @endif>
                <label>Reference Name</label>
                <input type="text" class="form-control" name="reference_name" placeholder="Reference Name" @if( isset($reference_name) ) value="{{ $reference_name }}" @endif />
            </div>
        </div>
    </div>
    <div class="row" id="i_e_info" style="display: none;">
        <div class="col-md-2">
            <label>Shipping Bill No.</label>
            <input type="text" class="form-control" name="shipping_bill_no" placeholder="Shipping Bill No." />
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Date of Shipping</label>
                {{-- <input type="date" class="form-control" name="date_of_shipping" style="line-height: 1.7;" /> --}}
                <input type="text" class="form-control custom_date" id="date_of_shipping" name="date_of_shipping" placeholder="DD/MM/YYYY" autocomplete="off">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Code of Shipping Port</label>
                <input type="text" class="form-control" name="code_of_shipping_port" placeholder="Shipping Port Code" />
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Conversion Rate</label>
                <input type="text" class="form-control" name="conversion_rate" placeholder="Conversion Rate" />
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Currency Symbol</label>
                <input type="text" class="form-control" name="currency_symbol" placeholder="Currency Symbol" />
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Export Type</label>
                <select style="font-size: 10px;" class="form-control" name="export_type">
                    <option value="" selected disabled>Select Type</option>
                    <option value="deemed exporter">Deemed Exporter</option>
                    <option value="export with payment">Export with Payment</option>
                    <option value="export without payment">Export without Payment</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row" id="c_c_info" style="display: none;">
        <div class="col-md-6">
            <div class="form-group">
                <label>Consignee Info</label>
                <textarea class="form-control" name="consignee_info" placeholder="Consignee Info"></textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Consignor Info</label>
                <textarea class="form-control" name="consignor_info" placeholder="Consignor Info"></textarea>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Purchase Order No.</label>
                <input type="text" class="form-control" name="purchase_order_no" @if( isset($purchase_order_no) ) value="{{ $purchase_order_no }}" readonly @endif required/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Reference Name</label>
                <input type="text" class="form-control" name="reference_name"/>
            </div>
        </div>
    </div> --}}
    <div id="full-form-outer">
        <div id="full-form-inner">
            <div class="row">
                <div class="col-md-6 col-md-offset-6 text-right">
                    <label>Add Lump Sump Amount</label>
                    <input type="checkbox" name="add_lump_sump" @if($user_profile->add_lump_sump == 'yes') checked readonly="readonly" @endif value="yes" />
                </div>
            </div>
            <div class="row">
                {{-- <div class="col-md-3 col-md-offset-9">
                    <div class="form-group">
                        <label class="checkbox-inline"><input type="checkbox" id="add_amount_manual">Add Amount Manually</label>
                    </div>
                </div> --}}
                <div class="col-md-6">
                    <a href="{{ route('group.create') }}" class="btn btn-success">Add Group</a>&nbsp;&nbsp;&nbsp;
                    <a href="{{ route('item.create') }}" class="btn btn-success">Add Item</a>
                </div>
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                {{-- <th>#</th> --}}
                                {{-- <th>Group</th> --}}
                                <th>Item/Product</th>
                                <th>Bar/Marka</th>
                                <th class="gst-classification-col" style="display: none;">GST Classification</th>
                                <th class="quantity-col" colspan="2">Qty</th>
                                <th class="quantity-col"></th>
                                <th class="rate-col">Rate</th>
                                <th class="discount-col" style="width: 10%;">Discount</th>
                                <th>Amount</th>
                                <th class="cess-col" style="display:none;">CESS</th>
                                <th class="tax-col">Tax(%)</th>
                                <th class="calc-tax-col">Calculated Tax</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="dynamic-body">
                            @if(isset($purchase_orders) && count($purchase_orders) > 0)
                                @foreach($purchase_orders as $order)
                                <tr>
                                    {{-- <td>
                                        <select class="form-control group">
                                            <option value="0">Select Group</option>
                                            @foreach($groups as $group)
                                                <option @if($order->group_id == $group->id) selected="selected" @endif value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control item" name="item[]" required>
                                            <option value="{{ $order->item_id }}">{{ $order->item_name }}</option>
                                        </select>
                                        <p><button type="button" class="btn btn-link add-more-info" data-item="{{ $order->item_id }}">Add Info</a></p>
                                    </td> --}}
                                    <td class="item-search-td">
                                        <input type="hidden" name="item[]" class="item" value="{{ $order->item_id }}" />
                                        <input type="text" class="form-control item_search" value="{{ $order->item_name }}" placeholder="Product" @if( isset($purchase_order_no) ) disabled @endif />
                                        <div class="auto"></div>
                                        <button style="padding: 0; font-size: 8px; display: none;" type="button" class="btn btn-link add-more-info" data-item="">Add CESS</button>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control item_barcode" name="item_barcode[]" required placeholder="Barcode" value="{{ $order->item_barcode }}" />
                                    </td>
                                    <td style="display: none; min-width: 125px;" class="gst-classification-col">
                                        <select name="gst_classification[]" class="form-control select_gst_classification">
                                            <option disabled selected>Select GST Classification</option>
                                            <option value="rcm">under RCM</option>
                                            <option value="exempt">Exempt</option>
                                            <option value="export">Zero/Export</option>
                                        </select>
                                    </td>
                                    <td class="quantity-col">
                                        <input type="text" class="form-control quantity" name="quantity[]" value="{{ $order->qty }}" required placeholder="Quantity">
                                    </td>
                                    <td class="quantity-col" style="min-width: 142px">
                                        <select name="measuring_unit[]" class="form-control select-measuring-unit">
                                            <option>Select Unit</option>
                                             @if($order->measuring_unit)
                                                <option @if($order->unit == $order->measuring_unit) selected="selected" @endif value="{{ $order->measuring_unit }}">{{ $order->measuring_unit }}</option>
                                            @endif

                                            @if($order->alternate_unit)
                                                <option @if($order->unit == $order->alternate_unit) selected="selected" @endif value="{{ $order->alternate_unit }}">{{ $order->alternate_unit }}</option>
                                            @endif

                                            @if($order->compound_unit)
                                                <option @if($order->unit == $order->compound_unit) selected="selected" @endif value="{{ $order->compound_unit }}">{{ $order->compound_unit }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="quantity-col free-quantity-col" style="visibility: hidden;">
                                        <input type="text" class="form-control" name="free_quantity[]" placeholder="Free Qty" />
                                    </td>
                                    <td class="rate-col">
                                        <input type="text" class="form-control price trigger-price" name="price[]" id="price" required placeholder="Price" value="{{ $order->rate }}">
                                        {{-- <p><button type="button" class="btn btn-link add-price" data-item="">Add Price</a></p> --}}
                                    </td>
                                    <td class="discount-col">
                                        <div style="width: 100%">
                                            <div style="width: 40%; float: left;">
                                                <select class="form-control row_discount_type" name="item_discount_type[]">
                                                    <option value="%">%</option>
                                                    <option value="f">F</option>
                                                </select>
                                            </div>
                                            <div style="width: 60%; float: left;">
                                                <input type="text" class="form-control item_discount" name="item_discount[]" placeholder="Discount" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="amount amount-span"></span>
                                        <input type="text" name="amount[]" class="form-control amount_manual amount-input trigger-price" placeholder="Amount" @if(auth()->user()->profile->add_lump_sump == 'no') style="display:none;" @endif value="{{ $order->rate }}" />
                                    </td>
                                    <td style="display: none;" class="cess-col">
                                        <input type="text" class="form-control cess-input" name="cess_amount[]" placeholder="CESS Amount" />
                                    </td>
                                    <td class="tax-col">
                                        <span class="gst">@if($order->gst_percent) {{ $order->gst_percent }} @endif</span>
                                    </td>
                                    <td class="calc-tax-col">
                                        <input type="hidden" name="calculated_gst[]" class="calculated-gst-input">
                                        <input type="hidden" name="calculated_gst_rcm[]" class="calculated-gst-rcm-input">
                                        <input type="hidden" name="gst_tax_type[]" class="gst_tax_type" />
                                        <span class="calculated-gst"></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                {{-- <td>
                                    <select class="form-control group" required>
                                        <option value="0">Select Group</option>
                                        @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control item" name="item[]" required>
                                    </select>
                                    <p>
                                        <button style="padding: 0; font-size: 8px;" type="button" class="btn btn-link add-more-info" data-item="">Add INFO / Add CESS</button>
                                    </p>
                                </td> --}}
                                <td>
                                    <input type="hidden" name="item[]" class="item" />
                                    <input type="text" class="form-control item_search" placeholder="Product" />
                                    <div class="auto"></div>
                                    <button style="padding: 0; font-size: 8px; display: none;" type="button" class="btn btn-link add-more-info" data-item="">Add CESS</button>
                                </td>
                                <td>
                                    <input type="text" class="form-control item_barcode" name="item_barcode[]" placeholder="Barcode" required/>
                                </td>
                                <td style="display: none; min-width: 125px;" class="gst-classification-col">
                                    <select name="gst_classification[]" class="form-control select_gst_classification">
                                        <option disabled selected>Select GST Classification</option>
                                        <option value="rcm">under RCM</option>
                                        <option value="exempt">Exempt</option>
                                        <option value="export">Zero/Export</option>
                                    </select>
                                </td>
                                <td class="quantity-col">
                                    <input type="text" class="form-control quantity" name="quantity[]" required placeholder="Quantity">
                                </td>
                                <td class="quantity-col" style="min-width: 142px">
                                    <select name="measuring_unit[]" class="form-control select-measuring-unit">
                                        <option>Select Unit</option>
                                    </select>
                                </td>
                                <td class="quantity-col free-quantity-col" style="visibility: hidden;">
                                    <input type="text" class="form-control" name="free_quantity[]" placeholder="Free Qty" />
                                </td>
                                <td class="rate-col">
                                    <input type="text" class="form-control price trigger-price" name="price[]" id="price" required placeholder="Price">
                                    {{-- <p><button type="button" class="btn btn-link add-price" data-item="">Add Price</a></p> --}}
                                </td>
                                <td class="discount-col">
                                    <div style="width: 100%;">
                                        <div style="width: 40%; float: left;">
                                            <select class="form-control row_discount_type" name="item_discount_type[]">
                                                <option value="%">%</option>
                                                <option value="f">F</option>
                                            </select>
                                        </div>
                                        <div style="width: 60%; float: left;">
                                            <input type="text" class="form-control item_discount" name="item_discount[]" placeholder="Discount" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="amount amount-span"></span>
                                    <input type="text" name="amount[]" class="form-control amount_manual amount-input trigger-price" placeholder="Amount" style="display:none;" />
                                </td>
                                <td style="display: none;" class="cess-col">
                                    <input type="text" class="form-control cess-input" name="cess_amount[]" placeholder="CESS Amount" />
                                </td>
                                <td class="tax-col">
                                    <span class="gst"></span>
                                </td>
                                <td class="calc-tax-col">
                                    <input type="hidden" name="calculated_gst[]" class="calculated-gst-input">
                                    <input type="hidden" name="calculated_gst_rcm[]" class="calculated-gst-rcm-input">
                                    <input type="hidden" name="gst_tax_type[]" class="gst_tax_type" />
                                    <span class="calculated-gst"></span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="form-group">
                        <button type="button" id="add-more-items" class="btn btn-success">+ Add More</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <span id="payment_area_left">
                    <div class="form-group">
                        <label>Mode of Payment</label><br />
                        <div class="row">
                            <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( 
                                $type_of_payment == 'combined' || 
                                $type_of_payment == 'cash+pos+discount' || 
                                $type_of_payment == 'cash+bank+discount' || 
                                $type_of_payment == 'cash+discount' || 
                                $type_of_payment == 'cash+bank+pos' || 
                                $type_of_payment == 'bank+cash' || 
                                $type_of_payment == 'pos+cash' || 
                                $type_of_payment == 'cash' ) checked="checked" @endif @endif /> <label for="cash">Cash</label>
                            </div>

                            <div class="col-md-9">
                                <div class="form-group" id="cash-list" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( $type_of_payment == 'combined' || 
                                $type_of_payment == 'cash+pos+discount' || 
                                $type_of_payment == 'cash+bank+discount' || 
                                $type_of_payment == 'cash+discount' || 
                                $type_of_payment == 'cash+bank+pos' || 
                                $type_of_payment == 'bank+cash' || 
                                $type_of_payment == 'pos+cash' || 
                                $type_of_payment == 'cash' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                    <input type="text" placeholder="Cash Amount" id="cashed_amount" name="cashed_amount" class="form-control" @if(isset($purchase_orders) && isset($purchase_orders->first()->cash_amount)) value="{{ $purchase_orders->first()->cash_amount }}" @endif />
                                    <hr/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( 
                                    $type_of_payment == 'combined' || 
                                    $type_of_payment == 'bank+pos+discount' || 
                                    $type_of_payment == 'cash+bank+discount' || 
                                    $type_of_payment == 'bank+discount' || 
                                    $type_of_payment == 'cash+bank+pos' || 
                                    $type_of_payment == 'bank+cash' || 
                                    $type_of_payment == 'pos+bank' || 
                                    $type_of_payment == 'bank' ) checked="checked" @endif @endif /> <label for="bank">Bank</label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group" id="bank-list" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( $type_of_payment == 'combined' || 
                                $type_of_payment == 'bank+pos+discount' || 
                                $type_of_payment == 'cash+bank+discount' || 
                                $type_of_payment == 'bank+discount' || 
                                $type_of_payment == 'cash+bank+pos' || 
                                $type_of_payment == 'bank+cash' || 
                                $type_of_payment == 'pos+bank' || 
                                $type_of_payment == 'bank' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                    <div class="form-group">
                                        <input type="text" placeholder="Bank Amount" id="banked_amount"  name="banked_amount" class="form-control" @if(isset($purchase_orders) && isset($purchase_orders->first()->bank_amount)) value="{{ $purchase_orders->first()->bank_amount }}" @endif />
                                    </div>
                                    <div class="form-group">
                                        <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" @if(isset($purchase_orders) && isset($purchase_orders->first()->bank_cheque)) value="{{ $purchase_orders->first()->bank_cheque }}" @endif />
                                    </div>
                                    <div class="form-group">
                                        <label>Bank List</label>
                                        <select class="form-control" name="bank">
                                            @if(count($banks) > 0)
                                                @foreach($banks as $bank)
                                                    <option @if(isset($purchase_orders) && $purchase_orders->first()->bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" />
                                    </div>
                                    <hr/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( 
                                    $type_of_payment == 'combined' || 
                                    $type_of_payment == 'bank+pos+discount' || 
                                    $type_of_payment == 'cash+pos+discount' || 
                                    $type_of_payment == 'pos+discount' || 
                                    $type_of_payment == 'cash+bank+pos' || 
                                    $type_of_payment == 'pos+cash' || 
                                    $type_of_payment == 'pos+bank' || 
                                    $type_of_payment == 'pos' ) checked="checked" @endif @endif /> <label for="pos">POS</label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group" id="pos-bank-list" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( 
                                $type_of_payment == 'combined' || 
                                $type_of_payment == 'bank+pos+discount' || 
                                $type_of_payment == 'cash+pos+discount' || 
                                $type_of_payment == 'pos+discount' || 
                                $type_of_payment == 'cash+bank+pos' || 
                                $type_of_payment == 'pos+cash' || 
                                $type_of_payment == 'pos+bank' || 
                                $type_of_payment == 'pos' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                    <div class="form-group">
                                        <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" @if(isset($purchase_orders) && isset($purchase_orders->first()->pos_amount)) value="{{ $purchase_orders->first()->pos_amount }}" @endif />
                                    </div>
                                    <div class="form-group">
                                        <label>POS Bank List</label>
                                        <select class="form-control" name="pos_bank">
                                            @if(count($banks) > 0)
                                                @foreach($banks as $bank)
                                                    <option @if(isset($purchase_orders) && $purchase_orders->first()->pos_bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" />
                                    </div>
                                    <hr/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <input type="checkbox" name="type_of_payment[]" value="cash_discount" id="cash_discount" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( 
                                    $type_of_payment == 'combined' || 
                                    $type_of_payment == 'bank+pos+discount' || 
                                    $type_of_payment == 'cash+pos+discount' || 
                                    $type_of_payment == 'cash+bank+discount' || 
                                    $type_of_payment == 'bank+discount' || 
                                    $type_of_payment == 'cash+discount' || 
                                    $type_of_payment == 'pos+discount' || 
                                    $type_of_payment == 'discount' ) checked="checked" @endif @endif /> <label for="cash_discount">Cash Discount</label>
                            </div>
                            <div class="col-md-7">
                                {{-- <label>Discount Type</label> --}}
                                <div id="discount-list" class="row" @if(isset($type_of_payment) && $type_of_payment != "no_payment") @if( $type_of_payment == 'combined' || 
                                $type_of_payment == 'bank+pos+discount' || 
                                $type_of_payment == 'cash+pos+discount' || 
                                $type_of_payment == 'cash+bank+discount' || 
                                $type_of_payment == 'bank+discount' || 
                                $type_of_payment == 'cash+discount' || 
                                $type_of_payment == 'pos+discount' || 
                                $type_of_payment == 'discount' ) style="display:block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                    <div class="col-md-6" style="padding-right: 0;">
                                        <select class="form-control" name="discount_type" id="discount_type">
                                            <option value="fixed">Fixed (Rs)</option>
                                            <option value="percent">Percent (%)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" style="padding-left: 0;">
                                        <input type="text" placeholder="Disc. Figure" name="discount_figure" id="discount_figure" class="form-control" />
                                    </div>
                                    <div class="col-md-12">
                                       <input type="text" placeholder="Discount" name="discount_amount" id="discount_holder" class="form-control" readonly /> 
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <hr/>

                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Discount Type</label>
                                <div id="discount-list" class="row">
                                    <div class="col-md-6">
                                        <select class="form-control" name="discount_type" id="discount_type">
                                            <option disabled selected>None</option>
                                            <option value="percent">Percent (%)</option>
                                            <option value="fixed">Fixed (Rs)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" placeholder="Discount Figure" name="discount_figure" id="discount_figure" class="form-control" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amt Rec. (Rs)</label>
                                    <input id="amount_paid" type="text" class="form-control" name="amount_paid" @if(isset($purchase_orders) && isset($purchase_orders->first()->amount_received)) value="{{ $purchase_orders->first()->amount_received }}" @endif readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amt Rem. (Rs)</label>
                                    <input id="amount_remaining" type="text" class="form-control" name="amount_remaining" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    </span>

                    <hr/>

                    <div class="form-group">
                        <textarea id="overall_remark" type="text" class="form-control" name="overall_remark" placeholder="Narration"></textarea>
                    </div>

                </div>
                <div id="total-right-outer">
                    <div id="total-right-inner" class="col-md-5 col-md-offset-2">
                        {{-- <div class="form-group">
                            <button type="button" class="btn btn-link" id="btn-transporter-detail">Add Transporter Details</button>
                        </div> --}}
                        <span id="payment_area_right">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Amt (Rs)</label>
                                    <input type="text" class="form-control" name="item_total_amount" id="item_total_amount" readonly />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="calculated_total_gst_block">
                                    <label>Total GST+CESS (Rs)</label>
                                    <input type="text" class="form-control" name="item_total_gst" value="0" id="item_total_gst" readonly />

                                    <input type="hidden" class="form-control" name="item_total_rcm_gst" value="0" id="item_total_rcm_gst" />

                                    {{-- gst and cess to be shown combined but save them separately --}}
                                    <input type="hidden" name="total_cess_amounted" id="total_cess_amounted" />

                                    <input type="hidden" name="total_gst_amounted" id="total_gst_amounted" />
                                </div>                                
                            </div>
                        </div>

                        <div id="cess-charge-block" style="display: none;">
                            <div class="form-group" id="cess-charge-outer">
                                @php $total_cess = 0; @endphp

                                <div id="cess-charge-inner">
                                    @if( Session::has('item_cess') )
                                        @foreach( session('item_cess') as $cess )
                                            @php
                                                $thisCess = $cess['cess_amount'];
                                                $total_cess += $thisCess;
                                            @endphp
                                        @endforeach
                                    @endif
                                    <input type="hidden" name="item_total_cess" id="item_total_cess" value="{{ $total_cess }}" />
                                    <strong>Total CESS:</strong> {{ $total_cess }}
                                </div>
                            </div>
                            <input type="checkbox" id="add_cess_to_total" /> <label for="add_cess_to_total">Add cess to Total Amt</label>
                        </div>

                        <div id="additional-charge-block" style="display: none;">
                            <div class="form-group" id="additional-charges-outer">
                                <div id="additional-charges-inner">
                                    <label>Additional Charges {{-- <button style="padding: 0;" type="button" id="btn-additional-charge" class="btn btn-link">Add Charges</button>&nbsp;/&nbsp; --}}
                                        <button style="padding: 0; font-size:10px;" type="button" class="btn btn-link" id="btn-transporter-detail">Add Transporter Details / Add Charges</button></label>
                                    @php
                                        if (Session::has("additional_charges.labour_charge")) {
                                            $labour_charges = session('additional_charges.labour_charge');
                                            session()->forget('additional_charges.labour_charge');
                                        } else {
                                            $labour_charges = 0;
                                        }

                                        if (Session::has('additional_charges.freight_charge')) {
                                            $freight_charges = session('additional_charges.freight_charge');
                                            session()->forget('additional_charges.freight_charge');
                                        } else {
                                            $freight_charges = 0;
                                        }

                                        if (Session::has('additional_charges.transport_charge')) {
                                            $transport_charges = session('additional_charges.transport_charge');
                                            session()->forget('additional_charges.transport_charge');
                                        } else {
                                            $transport_charges = 0;
                                        }

                                        if (Session::has('additional_charges.insurance_charge')) {
                                            $insurance_charges = session('additional_charges.insurance_charge');
                                            session()->forget('additional_charges.insurance_charge');
                                        } else {
                                            $insurance_charges = 0;
                                        }
                                        if (Session::has('additional_charges.gst_charged')) {
                                            $gst_charged = session('additional_charges.gst_charged');
                                            session()->forget('additional_charges.gst_charged');
                                        } else {
                                            $gst_charged = 0;
                                        }
                                    @endphp
                                    <input type="hidden" name="labour_charges" id="additional_labour_charges" value="{{ $labour_charges }}" />
                                    <p style="color: #666;">Labour: Rs {{ $labour_charges }}</p>
                                    <input type="hidden" name="freight_charges" id="additional_freight_charges" value="{{ $freight_charges }}" />
                                    {{-- <p style="color: #666;">Freight: Rs {{ $freight_charges }}</p> --}}
                                    <input type="hidden" name="transport_charges" id="additional_transport_charges" value="{{ $transport_charges }}" />
                                    <p style="color: #666;">Transport: Rs {{ $transport_charges }}</p>
                                    <input type="hidden" name="insurance_charges" id="additional_insurance_charges" value="{{ $insurance_charges }}" />
                                    <p style="color: #666;">Insurance: Rs {{ $insurance_charges }}</p>
                                    @if( $insurance_charges > 0 )
                                        <div class="form-group">
                                            <select name="insurance_company" class="form-control">
                                                <option value="0">Select Insurance Company</option>
                                                @foreach($insurances as $insurance)
                                                    <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <input type="hidden" name="gst_charged" id="gst_charged" value="{{ $gst_charged }}" />
                                    <p style="color: #666;">GST Charged: Rs {{ $gst_charged }}</p>

                                    <input type="checkbox" id="add_additional_to_total" /> <label for="add_additional_to_total">Add additional charges to Total Amt</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="tcs-block" style="display: none">
                            <label>TCS (Rs)</label>
                            <input type="text" id="tcs" name="tcs" class="form-control" />
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Grand Total (Rs)</label>
                                    <input type="text" id="total_amount_before_discount" name="total_amount" class="form-control" readonly>
                                </div>
                            </div>
                            {{-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Disc. (Rs)</label> --}}
                                    <input type="hidden" id="total_discount" name="total_discount" class="form-control" readonly>
                                {{-- </div>
                            </div> --}}
                            {{-- <div class="col-md-6"> --}}
                                {{-- <div class="form-group"> --}}
                                    {{-- <span id="total_amount"></span> --}}
                                    {{-- <label>Total Amt (Rs)</label> --}}
                                    <input type="hidden" id="total_amount" class="form-control" readonly>
                                {{-- </div> --}}
                            {{-- </div> --}}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Round off (Rs)</label>
                                    @if(auth()->user()->roundOffSetting->purchase_round_off_to == 'manual')
                                    <select class="form-control" id="operation" name="round_off_operation" style="width: 50%; float: left;">
                                        <option value="+">+</option>
                                        <option value="-">-</option>
                                    </select>
                                    @endif
                                    <input type="text" class="form-control" id="round_offed" name="round_offed" @if(auth()->user()->roundOffSetting->purchase_round_off_to != 'manual') readonly @endif data-roundType="{{ auth()->user()->roundOffSetting->purchase_round_off_to }}" @if(auth()->user()->roundOffSetting->purchase_round_off_to == 'manual') style="width: 50%; float: left;" @endif>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Net Amount</label>
                                    <input type="text" class="form-control" id="amount_to_pay" name="amount_to_pay" readonly />
                                </div>
                            </div>
                        </div>
                        </span>

                        {{-- <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amt Paid (Rs)</label>
                                    <input id="amount_paid" type="text" class="form-control" name="amount_paid" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amt Rem. (Rs)</label>
                                    <input id="amount_remaining" type="text" class="form-control" name="amount_remaining" readonly>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row form-group">
        {{-- <div class="col-md-8 col-md-offset-4">
            <button type="submit" class="btn btn-success btn-mine" id="create-new-purchase">
                Create New Purchase!
            </button>
        </div> --}}

        <div class="col-md-3 text-center">
            <button type="button" id="save-bill" class="btn btn-success btn-mine create-new-purchase">
                Save Bill!
            </button>
        </div>
        <div class="col-md-3 text-center">
            <button type="button" id="save-and-create-bill" class="btn btn-success btn-mine create-new-purchase">
                Save & Create Bill!
            </button>
        </div>
        <div class="col-md-3 text-center">
            <button type="button" id="save-and-mail-bill" class="btn btn-success btn-mine create-new-purchase">
                Save & Mail bill!
            </button>
        </div>
    </div>
    </form>
</div>

{{-- @if( count($uploaded_bills) > 0 )
    <div id="mydraggable" style="min-height: 200px; min-width: 200px; background-repeat: no-repeat; background-size: 100% 100%; border: 1px solid #ccc; display: none; z-index: 99999;">
        <div id="mydrag-overlay" style="display: none;">
            <div class="row text-center" style="height: inherit;">
                <div class="col-md-6 text-center"><button type="button" id="change_row_status" data-row="" class="btn btn-success">Mark as done</button></div>
                <div class="col-md-6 text-center"><button type="button" id="close-draggable" class="btn btn-danger">Close</button></div>
            </div>
        </div>
    </div>
    <table class="table" style="margin-top: 50px">
        <thead>
            <tr>
                <th>#</th>
                <th>Document</th>
                <th>Month</th>
                <th>Uploaded on</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1; @endphp
            @foreach( $uploaded_bills as $bill )
            <tr>
                <td>{{ $count++ }}</td>
                <td><img src="{{ asset('storage/public/'.$bill->image_path) }}" class="img-responsive" style="max-width: 250px" /></td>
                <td>{{ $bill->month }}</td>
                <td>{{ $bill->created_at }}</td>
                <td><button type="button" class="btn btn-link view-full" data-row="{{ $bill->id }}" data-src="{{ asset('storage/public/'.$bill->image_path) }}">View Full</button></td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endif --}}


<div class="modal" id="combined-transport-and-additional-charge-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Details</h4>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs">
                    <li class="active"  id="transporter-details-li"><a data-toggle="tab" href="#transporter-details-tab">Transporter Details</a></li>
                    <li id="additional-charges-li"><a data-toggle="tab" href="#additional-charges-tab">Additional Charges</a></li>
                </ul>
                <div class="tab-content" style="padding-top: 15px;">
                    <div id="transporter-details-tab" class="tab-pane fade in active">
                        <form id="form-transporter-detail">
                            <!-- <input type="hidden" name="item_id" id="price_item_id" value="" /> -->
                            <div class="form-group">
                                <label>Select Transporter</label>
                                <select class="form-control" id="transporter">
                                    <option value="0">Select Transporter</option>
                                    @foreach($transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{ $transporter->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Vehicle Type</label>
                                <input type="text" class="form-control" id="vehicle_type" placeholder="Vehicle Type" />
                            </div>
                            <div class="form-group">
                                <label>Vehicle Number</label>
                                <input type="text" class="form-control" id="vehicle_number" placeholder="Vehicle Number" />
                            </div>
                            <div class="form-group">
                                <label>Delivery Date</label>
                                <input type="date" class="form-control" id="delivery_date" placeholder="Delivery Date" style="line-height: 1;" />
                            </div>
                            <!-- <div class="form-group">
                                <label>Insurance Charge</label>
                                <input type="text" class="form-control" id="insurance_charge" placeholder="Insurance Charge" />
                            </div> -->
                            <button type="submit" class="btn btn-success btn-mine">Submit</button>
                        </form>
                        <p id="transporter-detail-error"></p>
                    </div>
                    <div id="additional-charges-tab" class="tab-pane fade">
                        <form id="form-additional-charge">
                            <!-- <input type="hidden" name="item_id" id="price_item_id" value="" /> -->
                            <div class="form-group">
                                <label>Labour Charge</label>
                                <input type="text" class="form-control" id="labour_charge" placeholder="Labour Charge" />
                            </div>
                            <!-- <div class="form-group">
                                <label>Freight Charge</label>
                                <input type="text" class="form-control" id="freight_charge" placeholder="Freight Charge" />
                            </div> -->
                            <div class="form-group">
                                <label>Transport Charge</label>
                                <input type="text" class="form-control" id="transport_charge" placeholder="Transport Charge" />
                            </div>
                            <div class="form-group">
                                <label>Insurance Charge</label>
                                <input type="text" class="form-control" id="insurance_charge" placeholder="Insurance Charge" />
                            </div>
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <label>GST (%)</label>
                                    <input type="text" class="form-control" id="gst_percentage" placeholder="GST" />
                                </div>
                                <div class="col-md-6">
                                    <label>Calculated GST Charge</label>
                                    <input type="text" class="form-control" id="calculated_gst_charge" placeholder="Calculated GST" readonly />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-mine">Submit</button>
                        </form>
                        <p id="additional-charge-error"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="combined-additional-info-and-item-cess-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Details</h4>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs">
                    {{-- <li class="active" id="additional-info-li"><a data-toggle="tab" href="#additional-info-tab">Additional Info</a></li> --}}
                    <li class="active" id="item-cess-li"><a data-toggle="tab" href="#item-cess-tab">Item CESS</a></li>
                </ul>
                <div class="tab-content" style="padding-top: 15px;">
                    {{-- <div id="additional-info-tab" class="tab-pane fade in active">
                        <form id="form-additional-info">
                            <input type="hidden" name="item_id" id="item_id" value="" />
                            <div class="form-group">
                                <label>Manufacture</label>
                                <input type="date" class="form-control" name="manufacture" id="manufacture" placeholder="Manufacture" style="line-height: 1;" />
                            </div>
                            <div class="form-group">
                                <label>Expiry</label>
                                <input type="date" class="form-control" name="expiry" id="expiry" placeholder="Expiry" style="line-height: 1;" />
                            </div>
                            <div class="form-group">
                                <label>Batch</label>
                                <input type="text" class="form-control" name="batch" id="batch" placeholder="Batch" />
                            </div>
                            <div class="form-group">
                                <label>Size</label>
                                <input type="text" class="form-control" name="size" id="size" placeholder="Size" />
                            </div>
                            <div class="form-group">
                                <label>Pieces</label>
                                <input type="text" class="form-control" name="pieces" id="pieces" placeholder="Pieces" />
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        <p id="additional-info-error"></p>
                    </div> --}}
                    <div id="item-cess-tab" class="tab-pane fade in active">
                        <form id="form-add-cess">
                            <input type="hidden" name="item_id" id="cess_item_id" value="" />
                            <div class="form-group">
                                <label>Cess Amount</label>
                                <input type="text" class="form-control" name="cess_amount" id="cess_amount" placeholder="Cess Amount" />
                            </div>
                            <button type="submit" class="btn btn-success btn-mine">Submit</button>
                        </form>
                        <p id="add-cess-error"></p>
                    </div>
                </div>
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
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_gst_classification" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_gst_classification) checked @endif>Show GST Classification</label>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_cess_charge" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_cess_charge) checked @endif>Show CESS Charge</label>
                </div>
                {{-- <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_additional_charge">Show Additional Charge</label>
                </div> --}}
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" id="show_tcs_charge" @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_tcs) checked @endif>Show TCS - Income tax</label>
                </div>
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

{{-- <div class="modal" id="item-extra-info">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Additional Info</h4>
            </div>
            <div class="modal-body">
                <form id="form-additional-info">
                    <input type="hidden" name="item_id" id="item_id" value="" />
                    <div class="form-group">
                        <label>Manufacture</label>
                        <input type="date" class="form-control" name="manufacture" id="manufacture" placeholder="Manufacture" style="line-height: 1;" />
                    </div>
                    <div class="form-group">
                        <label>Expiry</label>
                        <input type="date" class="form-control" name="expiry" id="expiry" placeholder="Expiry" style="line-height: 1;" />
                    </div>
                    <div class="form-group">
                        <label>Batch</label>
                        <input type="text" class="form-control" name="batch" id="batch" placeholder="Batch" />
                    </div>
                    <div class="form-group">
                        <label>Size</label>
                        <input type="text" class="form-control" name="size" id="size" placeholder="Size" />
                    </div>
                    <div class="form-group">
                        <label>Pieces</label>
                        <input type="text" class="form-control" name="pieces" id="pieces" placeholder="Pieces" />
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <p id="additional-info-error"></p>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal" id="add-short-price">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Rate Details</h4>
            </div>
            <div class="modal-body">
                <form id="form-short-price">
                    <input type="hidden" name="item_id" id="price_item_id" value="" />
                    <div class="form-group">
                        <label>Gross Rate</label>
                        <input type="text" class="form-control" name="gross_rate" id="gross_rate" data-item="" placeholder="Gross Rate" />
                    </div>
                    <div class="form-group">
                        <label>Short Rate</label>
                        <input type="text" class="form-control" name="short_rate" id="short_rate" placeholder="Short Rate" style="line-height: 1;" />
                    </div>
                    <div class="form-group">
                        <label>Net Rate</label>
                        <input type="text" class="form-control" name="net_rate" id="net_rate" placeholder="Net Rate" />
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <p id="short-price-error"></p>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal" id="add-cess-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Cess Amount</h4>
            </div>
            <div class="modal-body">
                <form id="form-add-cess">
                    <input type="hidden" name="item_id" id="cess_item_id" value="" />
                    <div class="form-group">
                        <label>Cess Amount</label>
                        <input type="text" class="form-control" name="cess_amount" id="cess_amount" placeholder="Cess Amount" />
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <p id="add-cess-error"></p>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal" id="add-additional-charges">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Additional Charges</h4>
            </div>
            <div class="modal-body">
                <form id="form-additional-charge">
                    <!-- <input type="hidden" name="item_id" id="price_item_id" value="" /> -->
                    <div class="form-group">
                        <label>Labour Charge</label>
                        <input type="text" class="form-control" id="labour_charge" placeholder="Labour Charge" />
                    </div>
                    <!-- <div class="form-group">
                        <label>Freight Charge</label>
                        <input type="text" class="form-control" id="freight_charge" placeholder="Freight Charge" />
                    </div> -->
                    <div class="form-group">
                        <label>Transport Charge</label>
                        <input type="text" class="form-control" id="transport_charge" placeholder="Transport Charge" />
                    </div>
                    <div class="form-group">
                        <label>Insurance Charge</label>
                        <input type="text" class="form-control" id="insurance_charge" placeholder="Insurance Charge" />
                    </div>
                    <div class="row form-group">
                        <div class="col-md-6">
                            <label>GST (%)</label>
                            <input type="text" class="form-control" id="gst_percentage" placeholder="GST" />
                        </div>
                        <div class="col-md-6">
                            <label>Calculated GST Charge</label>
                            <input type="text" class="form-control" id="calculated_gst_charge" placeholder="Calculated GST" readonly />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <p id="additional-charge-error"></p>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal" id="add-transporter-detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Transporter Details</h4>
            </div>
            <div class="modal-body">
                <form id="form-transporter-detail">
                    <!-- <input type="hidden" name="item_id" id="price_item_id" value="" /> -->
                    <div class="form-group">
                        <label>Select Transporter</label>
                        <select class="form-control" id="transporter">
                            <option value="0">Select Transporter</option>
                            @foreach($transporters as $transporter)
                                <option value="{{ $transporter->id }}">{{ $transporter->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Vehicle Type</label>
                        <input type="text" class="form-control" id="vehicle_type" placeholder="Vehicle Type" />
                    </div>
                    <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" class="form-control" id="vehicle_number" placeholder="Vehicle Number" />
                    </div>
                    <div class="form-group">
                        <label>Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" placeholder="Delivery Date" style="line-height: 1;" />
                    </div>
                    <!-- <div class="form-group">
                        <label>Insurance Charge</label>
                        <input type="text" class="form-control" id="insurance_charge" placeholder="Insurance Charge" />
                    </div> -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <p id="transporter-detail-error"></p>
            </div>
        </div>
    </div>
</div> --}}

@endsection

@section('conflicting_scripts')
    <script>
        $( "#mydraggable" ).draggable().resizable();
    </script>
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

        @if(isset($purchase_orders) && count($purchase_orders) > 0)
            @if(auth()->user()->profile->add_lump_sump == 'yes')
                $('input[name="add_lump_sump"]').trigger("change");

                $('input[name="amount[]"]').each(function( index ) {
                    $(this).trigger("keyup");
                });
            @else
                $('input[name="price[]"]').each(function( index ) {
                    $(this).trigger("keyup");
                });
            @endif
        @endif

        $(document).on("change", 'input[name="tax_inclusive"]', function(){
            if($('input[name="add_lump_sump"]').is(":checked")){
                $('input[name="amount[]"]').each(function( index ) {
                    $(this).trigger("keyup");
                });
            } else {
                $('input[name="price[]"]').each(function( index ) {
                    $(this).trigger("keyup");
                });
            }
        });

        $("#purchase_bill_date").on(function() {
            $.ajax({
                type: 'get',
                url: "{{ route('validate.financial.date') }}",
                data: {
                    "date": date,
                },
                success: function(response){
                    if(err.status == 400){
                        $("#bill_date_error").text('Please provide date within the current financial year');
                        $(".create-new-purchase").attr('disabled', true);
                    } else {
                        $("#bill_date_error").text('');
                        $(".create-new-purchase").attr('disabled', false);
                    }
                }
            });
        })

        $(document).on('hidden.bs.modal', '#add-cess-modal', function() {
            $('#form-add-cess').trigger("reset");
        });

        $(document).ready(function () {

            $("#bill_no").on("keyup", function() {
                var bill_no = $("#bill_no").val() ? $("#bill_no").val() : undefined;
                var party = $("#party option:selected").val() ? $("#party option:selected").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            $("#party").on("change", function() {
                var bill_no = $("#bill_no").val() ? $("#bill_no").val() : undefined;
                var party = $("#party option:selected").val() ? $("#party option:selected").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                $(".quantity").val(0);
                $("#tcs").val(0);

                $(".quantity").each(function(i, obj) {
                    $(this).trigger("keyup")
                });

                validateBillNo(party, bill_no, userId)
            });

            function validateBillNo(party = undefined, bill_no = undefined, userId = undefined) {
                if(party && bill_no && userId){
                    $.ajax({
                        type: 'post',
                        url: "{{ route('api.validate.billno') }}",
                        data: {
                            "bill_no": bill_no,
                            "party": party,
                            "user": userId
                        },
                        success: function(response){
                            $(".create-new-purchase").attr('disabled', false);
                            $("#bill_no_error_msg").text('');
                        },
                        error: function(err){
                            // console.log(err);
                            // console.log(err.responseJSON.errors);
                            if(err.status == 400){
                                $("#bill_no_error_msg").text(err.responseJSON.errors);
                                $(".create-new-purchase").attr('disabled', true);
                            }
                        }
                    });
                }
            }

            //var status_of_registration = '{{ $user_profile->registered }}';

            //status of registration always 1 because even if party is not registered they have to pay taxes.
            var status_of_registration = '1';

            $('#purchase_bill_date').dateFormat({
                format: 'xx/xx/xxxx',
            });


            $(document).on("change", 'select[name="gst_classification[]"]', function(){
                var tr = $(this).closest('tr');

                // var original_gst = tr.find(".tax-col").find(".gst").text();
                
                
                if($(this).val() == 'rcm'){
                    var current_item_gst = tr.find(".item").data("gst");
                    // tr.find(".tax-col").find(".gst").text(current_item_gst);

                }

                

                if($(this).val() == 'rcm'){
                    // tr.find(".tax-col").find(".gst").text(current_item_gst);
                    console.log('rcm');
                    $('select[name="gst_classification[]"] option:selected').attr("selected",null);
                    $('select[name="gst_classification[]"] option[value="rcm"]').attr("selected", true);

                    $('select[name="gst_classification[]"] option:selected').prop("selected",null);
                    $('select[name="gst_classification[]"] option[value="rcm"]').prop("selected", true);
                }

                if($(this).val() == 'exempt'){
                    // tr.find(".tax-col").find(".gst").text(0);
                    $(".gst").text(0);
                    console.log('exempt');
                    $('select[name="gst_classification[]"] option:selected').attr("selected",null);
                    $('select[name="gst_classification[]"] option[value="exempt"]').attr("selected","selected");

                    $('select[name="gst_classification[]"] option:selected').prop("selected",null);
                    $('select[name="gst_classification[]"] option[value="exempt"]').prop("selected","selected");
                }

                if($(this).val() == 'export'){
                    // tr.find(".tax-col").find(".gst").text(0);
                    $(".gst").text(0);
                    console.log('export');
                    $('select[name="gst_classification[]"] option:selected').attr("selected",null);
                    $('select[name="gst_classification[]"] option[value="export"]').attr("selected","selected");

                    $('select[name="gst_classification[]"] option:selected').prop("selected",null);
                    $('select[name="gst_classification[]"] option[value="export"]').prop("selected","selected");
                }

                $(".trigger-price").val(0);
                $(".trigger-price").trigger("keyup");

                // uncomment if you want row wise
                // tr.find(".trigger-price").val(0);
                // tr.find(".trigger-price").trigger("keyup");
            });

            $("#purchase_bill_date").on("keyup", function(){
                var bill_date = $(this).val();
                if(bill_date == ''){
                    $("#bill_date_error").text('');
                }
                if(bill_date.length == 10){
                    $.ajax({
                        type: 'post',
                        url: "{{ route('api.bill.date.validation') }}",
                        data: {
                            "bill_date": bill_date,
                            "_token": '{{ csrf_token() }}'
                        },
                        success: function(response){
    
                            isSuccessful = response.success;
    
                            if(isSuccessful){
                                $(".create-new-purchase").attr("disabled", false);

                                $("#bill_date_error").text('');
                            } else {
                                $(".create-new-purchase").attr("disabled", true);

                                $("#bill_date_error").text(response.message);
                            }
    
                        }
                    });
                }
            });

            if(status_of_registration == 0 ){
                $("#calculated_total_gst_block").hide();
                $(".tax-col").hide();
                $(".calc-tax-col").hide();
            }

            $(document).on("change", "#party", function () {
                var party = $(this).val();

                if(party == 'add party'){
                    window.location.replace('{{ route("party.create") }}');
                }

                $.ajax({
                    type: 'post',
                    url: "{{ route('post.fetch.party.billing.address') }}",
                    data: {
                        "party": party,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(response){

                        status_of_registration = response.status_of_registration;

                        // if(status_of_registration == 0 || status_of_registration == 3){
                        //     $("#calculated_total_gst_block").hide();
                        //     $(".tax-col").hide();
                        //     $(".calc-tax-col").hide();
                        // }

                        // $("#billing_address").text(response.billing_address + ", " + response.billing_city + ", " + response.billing_state + ", " + response.billing_pincode);

                        // $("#billing_address").attr("readonly", true);
                    }
                });
            });

            $( ".quantity" ).blur(function() {
                var tr = $(this).closest('tr');

                const decimal_place = tr.find('.select-measuring-unit option:selected').attr('data-decimal_place') || 0;

                if(this.value)
                    this.value = parseFloat(this.value).toFixed(decimal_place);
            });

            $('input[name="add_lump_sump"]').on("change", function() {
                console.log("triggered");
                if( $(this).is(":checked") ){
                    $(".inc_exc_tax").hide();

                    $(".discount-col").hide();
                    $(".quantity-col").hide();
                    $(".rate-col").hide();
                    // $(".tax-col").hide();
                    // $(".calc-tax-col").hide();
                    $(".amount-span").hide();
                    // $("#calculated_total_gst_block").hide();

                    $(".amount-input").show();

                    if(status_of_registration == 0 ){
                        $("#calculated_total_gst_block").hide();
                        $(".tax-col").hide();
                        $(".calc-tax-col").hide();
                    } else {
                        $("#calculated_total_gst_block").show();
                        $(".tax-col").show();
                        $(".calc-tax-col").show();
                    }

                } else {

                    $(".inc_exc_tax").show();

                    $(".discount-col").show();
                    $(".quantity-col").show();
                    $(".rate-col").show();
                    // $(".tax-col").show();
                    // $(".calc-tax-col").show();
                    $(".amount-span").show();

                    $(".amount-input").hide();
                    // $("#calculated_total_gst_block").hide();

                    if(status_of_registration == 0 ){
                        $("#calculated_total_gst_block").hide();
                        $(".tax-col").hide();
                        $(".calc-tax-col").hide();
                    } else {
                        $("#calculated_total_gst_block").show();
                        $(".tax-col").show();
                        $(".calc-tax-col").show();
                    }
                }

                $("#payment_area_left").load(location.href + " #payment_area_left");
                $("#payment_area_right").load(location.href + " #payment_area_right");

            });

            $(document).on("click", "#save-bill", function (e) {
                $("#submit_type").val("save");

                runValidation(e);
            });

            $(document).on("click", "#save-and-create-bill", function (e) {
                $("#submit_type").val("print");

                runValidation(e);
            });

            $(document).on("click", "#save-and-mail-bill", function (e) {
                $("#submit_type").val("email");

                runValidation(e);
            });

            $("#create-purchase").on("submit", function(e){

                var formValidationSuccessful = true;

                var selected_party = $("#party option:selected").text();
                var amount_remaining = $("#amount_remaining").val() ? $("#amount_remaining").val() : 0;

                // console.log(selected_party.toLowerCase());
                // console.log(amount_remaining);

                var formValidationMessage = "<ul>";

                if( selected_party.toLowerCase() == 'cash' ){
                    if( amount_remaining != 0 ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Amount remaining for cash party should be zero.</li>";
                    }
                }

                if( $("#bill_no").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Bill no is required.</li>";
                }

                if( $("#party").val() == null ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Customer is required.</li>";
                }

                if( $("#purchase_bill_date").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Bill date is required.</li>";
                }

                
                // $('#dynamic-body tr').each(function(i, row){
                //     var thisRow = $(row);

                //     if(thisRow.find('input[name="item[]"]').val() == ''){
                //         e.preventDefault();
                //         formValidationSuccessful = false;
                //         formValidationMessage += "<li>Either the product field is empty or the product you input isn't in our database.</li>"; 
                //     }

                //     if( $('input[name="add_lump_sump"]').is(":checked") ){
                //         if( $('.amount-input').val() == '' ){
                //             e.preventDefault();
                //             formValidationSuccessful = false;
                //             formValidationMessage += "<li>Amount is required.</li>";
                //         }
                //     } else {
                //         if( $('.quantity').val() == '' ){
                //             e.preventDefault();
                //             formValidationSuccessful = false;
                //             formValidationMessage += "<li>Quantity is required.</li>";
                //         }

                //         if( $('.price').val() == "0" ){
                //             e.preventDefault();
                //             formValidationSuccessful = false;
                //             formValidationMessage += "<li>Rate should be greater than zero.</li>";
                //         }

                //         if( $('.price').val() == '' ){
                //             e.preventDefault();
                //             formValidationSuccessful = false;
                //             formValidationMessage += "<li>Rate is required.</li>";
                //         }
                //     }
                // });
                

                if( $("#cash").is(":checked") ){
                    
                    if($("#cashed_amount").val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Cash amount is required.</li>";
                    }
                }

                if( $("#bank").is(":checked") ){
                
                    if( $("#banked_amount").val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Bank amount is required.</li>";
                    }

                    if( $("#bank_cheque").val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Bank cheque is required.</li>";
                    }

                    if( $("#bank_payment_date").val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Bank payment date is required.</li>";
                    }
                }

                if( $("#pos").is(":checked") ){
                    
                    if( $("#posed_amount").val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>POS amount is required</li>";
                    }

                    if( $("#pos_payment_date").val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>POS payment date is required</li>";
                    }
                }

                if( $("#item_total_amount").val() == ''  ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Items Amt is required.</li>";
                }

                if( $("#item_total_amount").val() == "NaN" ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Items Amt should be a number.</li>";
                }

                formValidationMessage += "</ul>";

                if( ! formValidationSuccessful){
                    show_custom_alert(`<span style="color: red">${formValidationMessage}</span>`);
                }

            });

            function runValidation(e) {
                var validate_against = $("#validate-against").text();
                const validation = validatePurchaseForm();

                // validateTwoDates(validate_against, date, ".", "create-note", "#", "date_validation_error");

                if(validation.isValid) {
                    $("#create-purchase").trigger("submit");
                } else {
                    e.preventDefault();
                    show_custom_alert(validation.message, "red");
                }
            }

            // validationForPartyDateAndBillDate() {

            // }

            function validatePurchaseForm()
            {
                var validation = true;
                var message = "";
                var submit_barcodes = $(".item_barcode");
                var submit_quantities = $(".quantity");
  

                for(var i = 0; i < submit_barcodes.length; i++){
                    if(submit_barcodes[i].value == ""){
                        validation = false;
                        message = "Barcode is required";
                        break;
                    }
                }

                if (!$('input[name="add_lump_sump"]').is(':checked')) {
                    for(var i = 0; i < submit_quantities.length; i++){
                        if(submit_quantities[i].value == "" || submit_quantities[i].value <= 0){
                            validation = false;
                            message = "Qty is required and should be greater than 0";
                            break;
                        }
                    }
                }

                return {isValid: validation, message: message};
            }

            $(document).on("keyup", ".amount-input", function () {

                var tr = $(this).closest('tr');

                var amount_inputted = $(this).val();

                var this_gst = tr.find(".tax-col").find(".gst").text();

                if( this_gst == NaN || this_gst == '' ){
                    this_gst = 0;
                }

                var calculated_gst = amount_inputted * this_gst / 100;
 
                if(tr.find(".item").attr('data-rcm') == 'yes'  || tr.find('select[name="gst_classification[]"] option:selected').val() == 'rcm'){

                    tr.find(".calculated-gst-rcm-input").val(calculated_gst);

                    tr.find(".calculated-gst-input").val(0);
                    tr.find(".calculated-gst").text(0);
                } else {

                    tr.find(".calculated-gst-rcm-input").val(0);

                    var party_type = $('option:selected', "#party").attr('data-party_type') ? $('option:selected', "#party").attr('data-party_type') : null;

                    console.log("party_type", party_type);

                    if(party_type != null){
                        if(party_type == 0 || party_type == 2 || party_type == 3){
                        console.log("party_type inside", party_type);
                            tr.find(".calculated-gst-input").val(0);
                            tr.find(".calculated-gst").text(0);
                        } else {
                            tr.find(".calculated-gst-input").val(calculated_gst);
                            tr.find(".calculated-gst").text(calculated_gst);
                        }
                    } else {
                        tr.find(".calculated-gst-input").val(calculated_gst);
                        tr.find(".calculated-gst").text(calculated_gst);
                    }

                }

                // fixed value in case of no qty (ie lump sump)
                tr.find(".gst_tax_type").val("exclusive_of_tax");

                var amounts = $(".amount-input");
                var gsts = $(".calculated-gst");
                var total_amount = 0;
                var total_gst = 0;
                for(var i=0; i<amounts.length; i++){
                    if( amounts[i].value == '' ){
                        amounts[i].value = 0;
                    }
                    total_amount += parseFloat(amounts[i].value);
                }

                for(var i=0; i<gsts.length; i++){

                    if( gsts[i].innerText == '' ){
                        gsts[i].innerText = 0;
                    }
                    total_gst += parseFloat(gsts[i].innerText);
                }

                var calculated_total_amount = parseFloat(total_amount) + parseFloat(total_gst);

                @if(auth()->user()->roundOffSetting->purchase_gst_amount == "yes")
                    total_gst = roundToSomeNumber(total_gst);
                @else
                    total_gst = noRoundOff(total_gst);
                @endif

                $("#item_total_gst").val(total_gst);
                $("#item_total_amount").val(total_amount);

                $("#total_amount_before_discount").val(parseFloat(total_amount) + parseFloat(total_gst));
                
                @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                    calculated_total_amount = roundToSomeNumber(calculated_total_amount);
                @else
                    calculated_total_amount = noRoundOff(calculated_total_amount);
                @endif

                $("#total_amount").val(calculated_total_amount);

                var rounded_off_total_amount = calculated_total_amount;
                var round_off_difference = 0;

                @if(auth()->user()->roundOffSetting->purchase_round_off_to == "upward")
                    rounded_off_total_amount = Math.ceil(calculated_total_amount);
                    round_off_difference = rounded_off_total_amount - calculated_total_amount;
                @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "downward")
                    rounded_off_total_amount = Math.floor(calculated_total_amount);
                    round_off_difference = rounded_off_total_amount - calculated_total_amount;
                @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "normal")
                    rounded_off_total_amount = Math.round(calculated_total_amount);
                    round_off_difference = rounded_off_total_amount - calculated_total_amount;
                @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "manual")
                    rounded_off_total_amount = noRoundOff(calculated_total_amount);
                    round_off_difference = 0;
                @endif

                round_off_difference = noRoundOff(round_off_difference);
                $("#round_offed").val(round_off_difference);
                $("#amount_to_pay").val(noRoundOff(rounded_off_total_amount));

                var amount_remaining = rounded_off_total_amount - amount_paid;

                $("#amount_remaining").val(noRoundOff(calculated_total_amount));
            });

            $("#round_offed").on("keyup", function() {
                roundOffed();
            });

            $("#operation").on("change", function () {
                roundOffed();
            });

            function roundOffed() {
                var roundType = $("#round_offed").data("roundtype");
                var total_amount = $("#total_amount").val();
                var round_off = $("#round_offed").val() || 0;
                var operation = $("#operation option:selected").val() || '+';
                // var amount_paid = $("#amount_paid").val() || 0;

                var amount_to_pay = total_amount;

                if(roundType == 'manual') {

                    // console.log(operation);

                    if(operation == '-'){
                        amount_to_pay = total_amount - round_off;
                    } else if(operation == '+') {
                        amount_to_pay = parseFloat(total_amount) + parseFloat(round_off);
                    }

                    // console.log(amount_to_pay);
                }

                $("#amount_to_pay").val(noRoundOff(amount_to_pay));

                var amount_paid = $("#amount_paid").val() == '' ? 0 : $("#amount_paid").val();

                var amount_remaining = amount_to_pay - amount_paid;

                $("#amount_remaining").val(noRoundOff(amount_remaining));
            }

            $("#show_consign_info").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#c_c_info").show();
                } else {
                    $("#c_c_info").hide();
                }
            });

            $("#show_import_export_info").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#i_e_info").show();
                } else {
                    $("#i_e_info").hide();
                }
            });

            $("#show_gst_classification").on("change", function () {
                if ( $(this).is(":checked") ) {
                    $(".gst-classification-col").show();
                } else {
                    $(".gst-classification-col").hide();
                }
            });

            $(".view-full").on("click", function () {
                var src = $(this).attr("data-src");
                var row = $(this).attr("data-row");

                $("#mydraggable").css("background-image", "url('"+ src +"')");
                $("#change_row_status").attr("data-row", row);

                $("#mydraggable").show();
            });

            $("#mydraggable").on("mouseover", function () {

                $("#mydrag-overlay").addClass("draggable-hover");
                $("#mydrag-overlay").show();

            });

            $("#mydraggable").on("mouseleave", function () {

                $("#mydrag-overlay").removeClass("draggable-hover");
                $("#mydrag-overlay").hide();
            });

            $("#close-draggable").on("click", function () {

                $("#mydraggable").css("background-image", "");
                $("#change_row_status").attr("data-row", "");

                $("#mydraggable").hide();

            });


            $("#change_row_status").on("click", function () {

                var row_id = $(this).attr("data-row");

                $.ajax({
                    type: 'post',
                    url: '{{ route("api.change.document.status") }}',
                    data: {
                        "row_id": row_id,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(response){
                        console.log(response);
                        if (response == 'success') {
                            // $("#form-additional-info").trigger("reset");
                            $("#mydraggable").hide();

                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> Status updated successfully</span>`);

                            setTimeout(function(){ location.reload(); }, 3000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> Failed to update status</span>`);
                        }
                    }
                });

            });


            // $("#add_amount_manual").on("change", function(){

            //     // var tr = $(this).closest('tr');

            //     if( $(this).is(":checked") ) {
            //         // $(".amount").css("visibility", "hidden");
            //         $(".price").attr("disabled", true);
            //         $(".quantity").attr("disabled", true);
            //         $(".amount_manual").show();
            //     } else {
            //         // $(".amount").css("visibility", "visible");
            //         $(".price").attr("disabled", false);
            //         $(".quantity").attr("disabled", false);
            //         $(".amount_manual").hide();
            //     }

            // });

            // $(".amount_manual").on("keyup", function() {

            //     var thisElement = $(this);

            //     var tr = thisElement.closest('tr');

            //     tr.find('.amount').text( thisElement.val() );

            //     // inclusive_or_exclusive(thisElement);

            // });

            $("#select_options").on("click", function () {
                $("#show_options_modal").modal("show");
            });

            $("#show_buyer_name").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#buyer_name").show();
                } else {
                    $("#buyer_name").hide();
                }
            });


            $("#show_tcs_charge").on("change", function () {
                if( $(this).is(":checked") ){
                    $("#tcs-block").show();
                    add_tcs();
                } else {
                    $("#tcs-block").hide();
                    remove_tcs();
                }
            } );

            $("#tcs").on("keyup", function () {
                add_tcs();
            });

            function add_tcs() {
                // var item_total_amount = $("#item_total_amount").val();
                // var item_total_gst = $("#item_total_gst").val();
                // var tcs_amounted = $("#tcs").val();
                
                // // var total_amount_before_discount = $("#total_amount_before_discount").val();

                // // console.log(tcs_amounted);

                // if(item_total_amount == '') {
                //     item_total_amount = 0;
                // }

                // if(item_total_gst == ''){
                //     item_total_gst = 0;
                // }

                // if(tcs_amounted == '') {
                //     tcs_amounted = 0;
                // }

                // var total_after_tcs = parseFloat(item_total_amount) + parseFloat(item_total_gst) + parseFloat(tcs_amounted);

                // @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                //     total_after_tcs = roundToSomeNumber(total_after_tcs);
                // @else
                //     total_after_tcs = noRoundOff(total_after_tcs);
                // @endif

                // // $("#total_amount_before_discount").val(total_after_tcs);
                // $("#amount_to_pay").val(noRoundOff(total_after_tcs));

                // var total_discount = $("#total_discount").val();

                // if(total_discount == ''){
                //     total_discount = 0;
                // }

                // var total_amount_after_discount = total_after_tcs - total_discount;

                // $("#total_amount").val(total_amount_after_discount);

                // $("#round_offed").trigger('keyup');

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

                // $("#round_offed").trigger('keyup');
                roundOffed();

            }

            function remove_tcs() {
                $("#tcs").val(0);
                add_tcs();
            }



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

            // $(document).on("click", "#open-combined-modal", function (){
            //     $("#combined-transport-and-additional-charge-modal").modal('show');
            // });

            $(document).on("change", "#show_cess_charge", function () {
                if ( $(this).is(":checked") ) {
                    // $("#cess-charge-block").show();

                    $(".cess-col").show();
                } else {
                    // $("#cess-charge-block").hide();

                    $(".cess-col").hide();
                }
            });

            $(document).on("change", "#show_additional_charge", function () {

                if ( $(this).is(":checked") ) {
                    $("#additional-charge-block").show();
                } else {
                    $("#additional-charge-block").hide();
                }
            });

            $(document).on("keyup", "#labour_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#freight_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#transport_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#insurance_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#gst_percentage", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", ".item_search", function() {

                var key_to_search = $(this).val();
                var tr = $(this).closest('tr');

                var party = $("#party option:selected").val();

                // console.log(party);

                if(party == "Choose a party") {
                    show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-info-circle\" aria-hidden=\"true\"></i> Please select a party before adding an item</span>`);
                    tr.find(".item_search").val('');
                } else {
                    autocomplete( key_to_search, tr );
                }

            });

            function autocomplete( key_to_search, tr ) {
                if(key_to_search == ''){
                    key_to_search = 1;
                    $('.auto').removeClass('active');
                }
                $.ajax({
                    "type": "POST",
                    "url": "{{ route('api.search.item.by.keyword') }}",
                    "data": {
                        "key_to_search": key_to_search,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(data){

                        console.log(data);
                        var outWords = data;
                        if(outWords.length > 0) {
                            tr.find('.auto').html('');

                            

                            for(x = 0; x < outWords.length; x++){
                                let itemGST = outWords[x].gst;

                                const purchase_price = outWords[x].purchase_price || 0;

                                tr.find('.auto').prepend(`<div data-value_id="${outWords[x].id}" data-value_name="${outWords[x].name}" data-value_hsc="${outWords[x].hsc_code}" data-value_sac="${outWords[x].sac_code}" data-value_gst="${itemGST}" data-value_price="${purchase_price}" data-value_rcm="${outWords[x].item_under_rcm}" data-value_barcode="${outWords[x].barcode}" data-value_unit="${outWords[x].measuring_unit}" data-value_unit_decimal_place="${outWords[x].measuring_unit_decimal_place}" data-value_alt_unit="${outWords[x].alternate_measuring_unit}" data-value_alt_unit_decimal_place="${outWords[x].alternate_unit_decimal_place}" data-value_comp_unit="${outWords[x].compound_measuring_unit}" data-value_comp_unit_decimal_place="${outWords[x].compound_unit_decimal_place}" data-value_has_free_qty="${outWords[x].free_qty}">${outWords[x].name}</div>`); //Fills the .auto div with the options
                            }

                            tr.find('.auto').addClass('active');

                        }
                    }
                });
            }

            $(document).on('click', '.auto div', function(){
                var searched_value_id = $(this).attr('data-value_id');
                var searched_value_name = $(this).attr('data-value_name');
                var searched_value_hsc = $(this).attr('data-value_hsc');
                var searched_value_sac = $(this).attr('data-value_sac');
                var searched_value_gst = $(this).attr('data-value_gst');
                var searched_value_price = $(this).attr('data-value_price');
                var searched_value_barcode = $(this).attr('data-value_barcode');

                var searched_value_unit = $(this).attr('data-value_unit');
                var searched_value_unit_decimal_place = $(this).attr('data-value_unit_decimal_place') || 0;

                var searched_value_alt_unit = $(this).attr('data-value_alt_unit');
                var searched_value_alt_unit_decimal_place = $(this).attr('data-value_alt_unit_decimal_place') || 0;

                var searched_value_comp_unit = $(this).attr('data-value_comp_unit');
                var searched_value_comp_unit_decimal_place = $(this).attr('data-value_comp_unit_decimal_place') || 0;

                var searched_value_free_unit = $(this).attr('data-value_has_free_qty');

                var searched_value_rcm = $(this).attr('data-value_rcm');

                var selected_items = $('input[name="item[]"]');

                for(var i=0; i<selected_items.length; i++){
                    if( selected_items[i].value == searched_value_id ){
                        show_custom_alert('Item already added', 'red');
                        return;
                    }
                    
                }

                if(searched_value_price == "null" || searched_value_price == "" || searched_value_price == NaN){
                    searched_value_price = 0;

                    // console.log(searched_value_price);
                }

                var profile_type = '{{ auth()->user()->profile->registered }}';

                if(searched_value_barcode == "null"){
                    searched_value_barcode = "";
                }

                if(searched_value_rcm == "yes"){
                    $(this).closest('tr').find('select[name="gst_classification[]"]').attr('disabled', true);
                } else {
                    $(this).closest('tr').find('select[name="gst_classification[]"]').attr('disabled', false);
                }


                // if($('select[name="gst_classification[]"] option:selected').val() == 'rcm' || $('select[name="gst_classification[]"] option:selected').val() == 'exempt' || $('select[name="gst_classification[]"] option:selected').val() == 'export'){
                //     searched_value_gst = 0;
                // }

                // if(status_of_registration == 0 ){
                //     var searched_value_gst = 0;
                // } else {
                //     var searched_value_gst = $(this).attr('data-value_gst');
                // }

                // console.log(`searched_value-gst ${searched_value_gst}`);
                // console.log('this ' + $(this).attr('data-value_gst'));

                var tr = $(this).closest('tr');

                tr.find(".select-measuring-unit").html('');

                if(searched_value_unit != "null"){
                    tr.find(".select-measuring-unit").append(`
                        <option data-decimal_place="${searched_value_unit_decimal_place}" value="${searched_value_unit}">${searched_value_unit}</option>
                    `);
                }

                if(searched_value_alt_unit != "null"){
                    tr.find(".select-measuring-unit").append(`
                        <option data-decimal_place="${searched_value_alt_unit_decimal_place}" value="${searched_value_alt_unit}">${searched_value_alt_unit}</option>
                    `);
                }

                if(searched_value_comp_unit != "null"){
                    tr.find(".select-measuring-unit").append(`
                        <option data-decimal_place="${searched_value_comp_unit_decimal_place}" value="${searched_value_comp_unit}">${searched_value_comp_unit}</option>
                    `);
                }

                if(searched_value_free_unit == "yes"){
                    tr.find(".free-quantity-col").css('visibility', 'visible');
                }

                $('.auto').html('');
                $('.auto').removeClass('active');

                // if the profile of user is not unregistered or composition then show add cess button otherwise not
                if(profile_type != '0' && profile_type != '3') {
                    tr.find(".add-more-info").show();
                    tr.find(".add-more-info").attr('data-item', searched_value_id);
                }

                tr.find(".item_search").val(searched_value_name);
                tr.find(".item").val(searched_value_id);
                tr.find(".price").val(searched_value_price);
                tr.find(".item_barcode").val(searched_value_barcode);
                tr.find(".item").attr('data-hsc', searched_value_hsc);
                tr.find(".item").attr('data-sac', searched_value_sac);
                tr.find(".item").attr('data-gst', searched_value_gst);
                tr.find(".item").attr('data-rcm', searched_value_rcm);
                tr.find(".calculated-gst").text("0");

                setTimeout(function(){ tr.find(".item").trigger("change"); }, 1000);
            });

            $(document).on("change", 'input[name="type_of_payment[]"]', function(){

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

                        $("#banked_amount").val(0);
                        $("#bank_cheque").val('');
                        $("#bank_payment_date").val('');
                        $("#banked_amount").trigger("keyup");
                    } else if(type_of_payment == 'pos') {
                        $("#pos-bank-list").hide();

                        $("#posed_amount").val(0);
                        $("#pos_payment_date").val('');
                        $("#posed_amount").trigger("keyup");
                    } else if(type_of_payment == 'cash'){
                        $("#cash-list").hide();

                        $("#cashed_amount").val(0);
                        $("#cashed_amount").trigger("keyup");
                    } else if(type_of_payment == 'cash_discount'){
                        $("#discount-list").hide();

                        $("#discount_figure").val(0);
                        $("#discount_figure").trigger("keyup");
                    }
                }
            });

            $(document).on("change", "#discount_type", function () {
                var cashed_amount = $("#cashed_amount").val();
                var banked_amount = $("#banked_amount").val();
                var posed_amount = $("#posed_amount").val();

                var discount_type = $("#discount_type option:selected").val();
                var discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();

                var cash_discount = calculate_cash_discount(discount_type, discount_figure);

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

                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount) + parseFloat(cash_discount);

                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            $(document).on("keyup", "#discount_figure", function () {
                var cashed_amount = $("#cashed_amount").val();
                var banked_amount = $("#banked_amount").val();
                var posed_amount = $("#posed_amount").val();

                var discount_type = $("#discount_type option:selected").val();
                var discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();

                var cash_discount = calculate_cash_discount(discount_type, discount_figure);

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

                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount) + parseFloat(cash_discount);

                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            function calculate_cash_discount(discount_type = 'fixed', discount_figure = 0) {

                var amount_to_pay = $("#amount_to_pay").val();

                if(discount_type == 'percent'){
                    discount_figure = (discount_figure * amount_to_pay) / 100;
                }

                discount_figure = noRoundOff(discount_figure);

                $("#discount_holder").val(discount_figure);

                // they dont want to subtract it from grand total but amount remaining
                return discount_figure;
            }

            // function calculate_discount() {
            //     var discount_type = $("#discount_type option:selected").val();

            //     // console.log(discount_type);

            //     var item_total_amount = $("#item_total_amount").val();
            //     var item_total_gst = $("#item_total_gst").val();
            //     var amount_to_pay = $("#amount_to_pay").val();
            //     // var amount_remaining = $("#amount_remaining").val();

            //     if(item_total_amount == ''){
            //         item_total_amount = 0;
            //     }

            //     if(item_total_gst == ''){
            //         item_total_gst = 0;
            //     }

            //     if(amount_to_pay == ''){
            //         amount_to_pay = 0;
            //     }

            //     // if(amount_remaining == ''){
            //     //     amount_remaining = 0;
            //     // }

            //     var total_pending_payment_amount_in_modal = parseFloat(item_total_amount) + parseFloat(item_total_gst);
            //     var discount_figure = 0;

            //     // if(discount_figure == ''){
            //     //     discount_figure = 0;
            //     // }

            //     if(total_pending_payment_amount_in_modal == ''){
            //         total_pending_payment_amount_in_modal = 0; 
            //     }

            //     if(discount_type == 'fixed') {
            //         discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();
            //     }

            //     if(discount_type == 'percent'){
            //         discount_figure = $("#discount_figure").val() == '' ? 0 : $("#discount_figure").val();
            //         discount_figure = (discount_figure * total_pending_payment_amount_in_modal) / 100;
            //     }

            //     discount_figure = noRoundOff(discount_figure);

            //     // they dont want to subtract it from grand total but amount remaining
            //     //$("#total_discount").val(discount_figure);
            //     $("#discount_holder").val(discount_figure);

            //     amount_remaining = amount_to_pay - discount_figure;
            //     amount_remaining = noRoundOff(amount_remaining);

            //     $("#amount_remaining").val(amount_remaining);

            //     // $("#total_discount").trigger("keyup");
            // }

            // $(document).on('click', '.modal-close', function() {
            //     $("#form-additional-info").trigger("reset");
            //     $("#form-short-price").trigger("reset");
            //     $("#form-transporter-detail").trigger("reset");
            // });

            $("#gross_rate").on("keyup", function (){
                var thisGrossValue = $(this).val();
                var thisItemId = $(this).attr("data-item");

                $("#price"+thisItemId).val(thisGrossValue).trigger("change");
            });

            $(document).on("click", ".add-more-info", function () {

                // var thisis_item_id = $(this).attr('data-item');

                // // console.log(thisis_item_id);

                // $('#item_id').val(thisis_item_id); // or something else.
                // $('#cess_item_id').val(thisis_item_id);

                // $.ajax({
                //     type: 'post',
                //     url: '{{ route("api.find.item.extra.info") }}',
                //     data: {
                //         "item_id": thisis_item_id,
                //         "_token": '{{ csrf_token() }}'
                //     },
                //     success: function(response){
                //         // console.log(response);
                //         if (response != 'failure') {
                //             console.log(response);
                //         } else {
                //             console.log(response);
                //         }
                //     }
                // });

                // // $('#item-extra-info').modal('show');

                // // $("#item-cess-li").removeClass("active");
                // // $("#additional-info-li").addClass("active");

                // // $("#item-cess-tab").removeClass("in").removeClass("active");
                // // $("#additional-info-tab").addClass("in").addClass("active");

                // $('#combined-additional-info-and-item-cess-modal').modal('show');

                // $("thead .cess-col").show();
                // tr.find(".cess-col").show();
                var tr = $(this).closest('tr');
                tr.find(".cess-input").val("").trigger("keyup");
                tr.find(".cess-col").toggle();
            });

            $(document).on("click", ".add-cess", function () {
                $('#item_id').val($(this).attr('data-item'));
                $('#cess_item_id').val($(this).attr('data-item')); // or something else.
                // $('#add-cess-modal').modal('show');

                $("#additional-info-li").removeClass("active");
                $("#item-cess-li").addClass("active");

                $("#additional-info-tab").removeClass("in").removeClass("active");
                $("#item-cess-tab").addClass("in").addClass("active");

                $('#combined-additional-info-and-item-cess-modal').modal('show');
            });

            $(document).on("click", "#btn-additional-charge", function (){
                // $("#add-additional-charges").modal('show');

                $("#transporter-details-li").removeClass("active");
                $("#additional-charges-li").addClass("active");

                $("#transporter-details-tab").removeClass("in").removeClass("active");
                $("#additional-charges-tab").addClass("in").addClass("active");

                $("#combined-transport-and-additional-charge-modal").modal("show");
            });

            $(document).on("click", "#btn-transporter-detail", function (){
                // $("#add-transporter-detail").modal('show');

                $("#additional-charges-li").removeClass("active");
                $("#transporter-details-li").addClass("active");

                $("#additional-charges-tab").removeClass("in").removeClass("active");
                $("#transporter-details-tab").addClass("in").addClass("active");

                $("#combined-transport-and-additional-charge-modal").modal("show");
            });

            $(document).on("click", ".add-price", function () {
                $('#gross_rate').attr("data-item", $(this).attr('data-item'));
                $('#price_item_id').val($(this).attr('data-item'));
                $('#add-short-price').modal('show');
            });

            $(document).on("submit", "#form-additional-charge", function(e){
                e.preventDefault();
                var labour_charge = $("#labour_charge").val();
                // var freight_charge = $("#freight_charge").val();
                var transport_charge = $("#transport_charge").val();
                var insurance_charge = $("#insurance_charge").val();
                var gst_percentage = $("#gst_percentage").val();
                var calculated_gst_charge = $("#calculated_gst_charge").val();
                var processFurther = true;

                if(isNaN(labour_charge)){
                    $("#additional-charge-error").text("Labour charge must be a number");
                    processFurther = false;
                }

                if(isNaN(transport_charge)){
                    $("#additional-charge-error").text("Transport charge must be a number");
                    processFurther = false;
                }

                if(isNaN(insurance_charge)){
                    $("#additional-charge-error").text("Insurance charge must be a number");
                    processFurther = false;
                }

                if(isNaN(gst_percentage)){
                    $("#additional-charge-error").text("GST percent must be a number");
                    processFurther = false;
                }

                if(isNaN(calculated_gst_charge)){
                    $("#additional-charge-error").text("All charges must be number");
                    processFurther = false;
                }


                if(processFurther)
                {
                    $.ajax({
                        type: 'post',
                        url: '{{ route("api.add.additional.charges") }}',
                        data: {
                            "labour_charge": labour_charge,
                            "transport_charge": transport_charge,
                            "insurance_charge": insurance_charge,
                            "gst_charged": calculated_gst_charge,
                            "_token": '{{ csrf_token() }}'
                        },
                        success: function(response){
                            // console.log(response);
                            if (response == 'success') {
                                $("#add-additional-charges").modal('hide');
                                $("#additional-charge-error").text("Data saved successfully");
                                $('#additional-charges-outer').load(document.URL + ' #additional-charges-inner');
                            } else {
                                $("#additional-charge-error").text("Error while submitting form");
                            }
                        }
                    });
                }
            });

            $(document).on("submit", "#form-additional-info", function(e){
                e.preventDefault();
                var item_id = $("#item_id").val();
                var manufacture = $("#manufacture").val();
                var expiry = $("#expiry").val();
                var batch = $("#batch").val();
                var size = $("#size").val();
                var pieces = $("#pieces").val();

                // console.log("submitting...");

                $.ajax({
                    type: 'post',
                    url: '{{ route("api.add.item.extra.info") }}',
                    data: {
                        "manufacture": manufacture,
                        "expiry": expiry,
                        "batch": batch,
                        "item_id": item_id,
                        "size": size,
                        "pieces": pieces,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(response){
                        console.log(response);
                        if (response == 'success') {
                            // $("#form-additional-info").trigger("reset");
                            $("#item-extra-info").modal('hide');
                            $("#additional-info-error").html('<strong style="color: green;"><i class="fa fa-check" aria-hidden="true"></i> Data saved successfully</strong>');
                        } else {
                            $("#additional-info-error").html('<strong style="color: red"><i class="fa fa-times" aria-hidden="true"></i> Error while submitting form</strong>');
                        }
                    }
                });
            });

            $(document).on("submit", "#form-add-cess", function(e){
                e.preventDefault();

                //inclusive_or_exclusive(thisElement); here i am

                var item_id = $("#cess_item_id").val();
                var cess_amount = $("#cess_amount").val();
                var processFurther = true;

                if(isNaN(cess_amount)){
                    $("#add-cess-error").html('<strong style="color: red"><i class="fa fa-times" aria-hidden="true"></i> Amount must be a number</strong>');
                    processFurther = false;
                }

                if(processFurther){
                    $.ajax({
                        type: 'post',
                        url: '{{ route("api.add.item.cess") }}',
                        data: {
                            "cess_amount": cess_amount,
                            "item_id": item_id,
                            "_token": '{{ csrf_token() }}'
                        },
                        success: function(response){
                            console.log(response);
                            if (response == 'success') {
                                // $("#form-add-cess").trigger("reset");
                                $('#add-cess-modal').modal('hide');
                                $("#add-cess-error").html('<strong style="color: green;"><i class="fa fa-check" aria-hidden="true"></i> Data saved successfully</strong>');
                                $('#cess-charge-outer').load(document.URL + ' #cess-charge-inner');
                            } else {
                                $("#add-cess-error").html('<strong style="color: red"><i class="fa fa-times" aria-hidden="true"></i> Error while submitting form</strong>');
                            }
                        }
                    });
                }
            });

            $(document).on("submit", "#form-short-price", function(e){
                e.preventDefault();
                var item_id = $("#price_item_id").val();
                var gross_rate = $("#gross_rate").val();
                var short_rate = $("#short_rate").val();
                var net_rate = $("#net_rate").val();

                $.ajax({
                    type: 'post',
                    url: '{{ route("api.add.short.rate.info") }}',
                    data: {
                        "gross_rate": gross_rate,
                        "short_rate": short_rate,
                        "net_rate": net_rate,
                        "item_id": item_id,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(response){
                        console.log(response);
                        if (response == 'success') {
                            $("#form-short-price").trigger("reset");
                            $("#add-short-price").modal('hide');
                        } else {
                            $("#short-price-error").text("Error while submitting form");
                        }
                    }
                });
            });

            $(document).on("submit", "#form-transporter-detail", function(e){
                e.preventDefault();

                var vehicle_type = $("#vehicle_type").val();
                var vehicle_number = $("#vehicle_number").val();
                var delivery_date = $("#delivery_date").val();
                var transporter_id = $("#transporter option:selected").val();

                if(vehicle_type != '' && vehicle_number != '' && delivery_date != '' && transporter_id != 0){
                    $.ajax({
                        type: 'post',
                        url: '{{ route("api.add.transporter.details") }}',
                        data: {
                            "vehicle_type": vehicle_type,
                            "vehicle_number": vehicle_number,
                            "delivery_date": delivery_date,
                            "transporter_id": transporter_id,
                            "_token": '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            console.log(response);
                            if (response == 'success') {
                                // $("#form-transporter-detail").trigger("reset");
                                $("#transporter-detail-error").text("Data saved successfully");
                                $("#add-transporter-detail").modal('hide');
                            } else {
                                $("#transporter-detail-error").text("Error while submitting form");
                            }
                        }
                    });
                } else {
                    alert('All fields are mandatory.');
                }
            });

            // $(document).on("submit", "#create-purchase", function(e){
            //     // e.preventDefault();
            //     var validation = true;
            //     var submit_amount_paid = $("#amount_paid").val();
            //     var submit_bill_no = $("#bill_no").val();

            //     var submit_gsts = $(".gst");
            //     var submit_barcodes = $(".item_barcode");
            //     var submit_quantities = $(".quantity");
            //     var submit_items = $(".item");

            //     var submit_party = $("#party option:selected").val();

            //     if(submit_amount_paid == ''){
            //         validation = false;
            //     }

            //     if(submit_bill_no == ''){
            //         validation = false;
            //     }

            //     for(var i = 0; i < submit_barcodes.length; i++){
            //         if(submit_barcodes[i].value == ""){
            //             validation = false;
            //         }
            //     }

            //     for(var j = 0; j < submit_quantities.length; j++){
            //         if(submit_quantities[j].value == ""){
            //             validation = false;
            //         }
            //     }

            //     for(var k = 0; k < submit_items.length; k++){
            //         if(submit_items[k].value == ""){
            //             validation = false;
            //         }
            //     }

            //     if(submit_party == 0){
            //         validation = false;
            //     }


            //     if(validation) {
            //         $("#create-purchase").trigger( "submit" );

            //         // alert('submit');
            //     } else {
            //         e.preventDefault();
            //         alert("Please fill up all required fields.");
            //     }

            // });

            $(document).on("keyup", "#amount_paid", function(){
                var amount_paid = $(this).val();
                var total_amount = $("#amount_to_pay").val();

                if(total_amount == ""){
                    total_amount = 0;
                }

                if(amount_paid == ""){
                    amount_paid = 0;
                }

                if(total_amount !== ""){
                    var amount_remaining = total_amount - amount_paid;
                    amount_remaining = roundToSomeNumber(amount_remaining);
                    $("#amount_remaining").val(noRoundOff(amount_remaining));
                }

            });

            $(document).on("change", ".group", function(){
                var group = $("option:selected", this).val();
                var tr = $(this).closest('tr');

                if(group > 0){
                    $.ajax({
                        type: "GET",
                        url: "{{ route('api.fetch.item') }}",
                        data: { group: group },
                        success: function(response){

                            var arr = JSON.parse(response);
                            // console.log(response);
                            // var responseKey = Object.keys(response);
                            tr.find(".item").append(`<option value="0">Select Item</option>`);

                            for($i=0; $i<arr.length; $i++){
                                tr.find(".item").append(`<option data-hsc="${arr[$i].hsc_code}" data-sac="${arr[$i].sac_code}" data-gst="${arr[$i].gst}" value="${arr[$i].id}">${arr[$i].name}</option>`);
                            }
                        }
                    });
                }else{
                    alert("Please select a valid group");
                    tr.find(".item").html('');
                    tr.find(".hsc").text('');
                    tr.find(".sac").text('');
                    tr.find(".gst").text('');
                }
            });

            $(document).on("change", "#add_additional_to_total", function(){
                // console.log("change triggered");
                var thisCheckBox = $(this);
                var total_cess_amount = parseFloat($("#item_total_cess").val());
                var total_additional_charges = calculate_total_additional_charges();

                var total = 0;
                var total_gst = 0;

                var amounts = $(".amount");
                var gsts = $(".calculated-gst");

                for(var i = 0; i < amounts.length; i++){
                    if(amounts[i].innerText !== ""){
                        total += parseFloat(amounts[i].innerText);
                    }
                }

                for(var j = 0; j < gsts.length; j++){
                    if(gsts[j].innerText !== ""){
                        total_gst += parseFloat(gsts[j].innerText);
                    }
                }

                total += total_gst;
                // console.log(total);
                // console.log(total_additional_charges);
                if (thisCheckBox.is(':checked')) {
                    total += total_additional_charges;
                }

                if ($("#add_cess_to_total").is(':checked')) {
                    total += total_cess_amount;
                }

                var tcs = $("#tcs").val();

                if(tcs != ''){
                    total += parseFloat(tcs);
                }

                @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                    total = roundToSomeNumber(total);
                @else
                    total = noRoundOff(total);
                @endif

                $("#total_amount").val(total);

                var amount_paid = $("#amount_paid").val();
                var total_amount = $("#total_amount").val();

                if(total_amount == ""){
                    total_amount = 0;
                }

                if(amount_paid == ""){
                    amount_paid = 0;
                }

                if(total_amount !== ""){
                    var amount_remaining = total_amount - amount_paid;
                    amount_remaining = roundToSomeNumber(amount_remaining);
                    $("#amount_remaining").val(noRoundOff(amount_remaining));
                }
            });

            $(document).on("change", "#add_cess_to_total", function () {
                console.log("change triggered");
                var thisCheckBox = $(this);
                var total_cess_amount = parseFloat($("#item_total_cess").val());
                var total_additional_charges = calculate_total_additional_charges();

                var total = 0;
                var total_gst = 0;

                var amounts = $(".amount");
                var gsts = $(".calculated-gst");

                for(var i = 0; i < amounts.length; i++){
                    if(amounts[i].innerText !== ""){
                        total += parseFloat(amounts[i].innerText);
                    }
                }

                for(var j = 0; j < gsts.length; j++){
                    if(gsts[j].innerText !== ""){
                        total_gst += parseFloat(gsts[j].innerText);
                    }
                }

                total += total_gst;

                // console.log(total);
                // console.log(total_additional_charges);
                if (thisCheckBox.is(':checked')) {
                    total += total_cess_amount;
                }

                if ($("#add_additional_to_total").is(':checked')) {
                    total += total_additional_charges;
                }

                var tcs = $("#tcs").val();

                if(tcs != ''){
                    total += parseFloat(tcs);
                }

                @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                    total = roundToSomeNumber(total);
                @else
                    total = noRoundOff(total);
                @endif

                $("#total_amount").val(total);

                var amount_paid = $("#amount_paid").val();
                var total_amount = $("#total_amount").val();

                if(total_amount == ""){
                    total_amount = 0;
                }

                if(amount_paid == ""){
                    amount_paid = 0;
                }

                if(total_amount !== ""){
                    var amount_remaining = total_amount - amount_paid;
                    amount_remaining = roundToSomeNumber(amount_remaining);
                    $("#amount_remaining").val(noRoundOff(amount_remaining));
                }
            });

            // $(document).on("change", 'input[name="tax_inclusive"]', function(){

            //     $('#full-form-outer').load(document.URL + ' #full-form-inner');

            //     setTimeout(function(){ if( $('input[name="tax_inclusive"]:checked').val() == "inclusive_of_tax" ){
            //         $(".item_discount").prop("disabled", true);
            //     } else {
            //         $(".item_discount").prop("disabled", false);
            //     } }, 1200);


            // });

            $(document).on("keyup", "#purchase_order_no", function() {

                var key_to_search = $(this).val();

                auto_find_purchase_order_no( key_to_search );

            });

            function auto_find_purchase_order_no( key_to_search ) {
                if(key_to_search == ''){
                    key_to_search = '-';
                    $('.autopurchaseorder').removeClass('active');
                    $('.autopurchaseorder').css('position', 'static');
                }
                $('.autopurchaseorder').html('');
                $.ajax({
                    "type": "POST",
                    "url": "{{ route('api.search.purchase.order.name') }}",
                    "data": {
                        "key_to_search": key_to_search,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(data){

                        // console.log(data);
                        var outWords = data;
                        if(outWords.length > 0) {

                            for(x = 0; x < outWords.length; x++){
                                // $('.autosaleorder').append(`<div data-value="${outWords[x].token}" >${outWords[x].token}</div>`);
                                //Fills the .auto div with the options
                                $('.autopurchaseorder').append(`<a href="/purchase/create/${outWords[x].token}" data-value="${outWords[x].token}" ><div>${outWords[x].token}</div></a>`);
                            }

                            $('.autopurchaseorder').addClass('active');
                            $('.autopurchaseorder').css('position', 'absolute');

                        } else {
                            $('.autopurchaseorder').removeClass('active');
                            $('.autopurchaseorder').css('position', 'static');
                        }
                    }
                });
            }

            // $(document).on('click', '.autopurchaseorder div', function(){
            //     var searched_value = $(this).attr('data-value');

            //     $('.autopurchaseorder').html('');
            //     $('.autopurchaseorder').removeClass('active');

            //     $("#purchase_order_no").val(searched_value);

            // });

            $(document).on("change", ".item", function(){
                // var item = $("option:selected", this).val();
                var item = $(this).val();

                // console.log("item " + item);

                var tr = $(this).closest('tr');

                const thisElement = $(this);

                if(item > 0) {
                    // var hsc = $("option:selected", this).attr('data-hsc');
                    // var sac = $("option:selected", this).attr('data-sac');
                    // var gst = $("option:selected", this).attr('data-gst');

                    var hsc = $(this).attr('data-hsc');
                    var sac = $(this).attr('data-sac');
                    var gst = $(this).attr('data-gst');


                    tr.find(".hsc").text(hsc);
                    tr.find(".sac").text(sac);
                    if(gst.toLowerCase() == 'exempt' || gst.toLowerCase() == 'nil' || gst.toLowerCase() == 'export'){
                        tr.find(".gst").text("0");
                    } else {
                        tr.find(".gst").text(gst);
                    }
                    tr.find(".price").attr("id", "price"+item);
                    tr.find(".add-more-info").attr("data-item", item);
                    tr.find(".add-cess").attr("data-item", item);
                    tr.find(".add-price").attr("data-item", item);
                    tr.find(".delete-row").attr("data-item", item);

                    // var total_additional_charges = calculate_total_additional_charges();

                    // var total = 0;
                    // var items_amount = 0;
                    // var items_gst_amount = 0;

                    // var amounts = $(".amount");
                    // var gsts = $(".gst");

                    // for (var i = 0; i < amounts.length; i++) {
                    //     if(amounts[i].innerText !== ""){
                    //         items_amount += parseFloat(amounts[i].innerText);

                    //         $("#item_total_amount").val(items_amount);
                    //     }
                    // }

                    // for(var i = 0; i < amounts.length; i++){
                    //     if(amounts[i].innerText !== ""){
                    //         total += parseFloat(amounts[i].innerText);
                    //         if(gsts[i].innerText !== ""){
                    //             gst_amount = total * parseFloat(gsts[i].innerText) / 100;
                    //             tr.find(".calculated-gst").text(gst_amount);
                    //             tr.find(".calculated-gst-input").val(gst_amount);
                    //             items_gst_amount += gst_amount;
                    //             total += gst_amount;
                    //         }
                    //     }
                    // }

                    // total += total_additional_charges;

                    // $("#total_amount").val( total );
                    // $("#item_total_gst").val( items_gst_amount );

                    // var amount_paid = $("#amount_paid").val();
                    // var total_amount = $("#total_amount").val();

                    // if(total_amount == ""){
                    //     total_amount = 0;
                    // }

                    // if(amount_paid == ""){
                    //     amount_paid = 0;
                    // }

                    // if(total_amount !== ""){
                    //     var amount_remaining = total_amount - amount_paid;
                    //     // amount_remaining = Math.round(amount_remaining);
                    //     $("#amount_remaining").val(amount_remaining);
                    // }
                } else {
                    alert("Please select a valid item");
                    tr.find(".item").html('');
                    tr.find(".hsc").text('');
                    tr.find(".sac").text('');
                    tr.find(".gst").text('');
                    tr.find(".price").attr("id", "");
                    tr.find(".add-more-info").attr("data-item", "");
                    tr.find(".add-price").attr("data-item", "");
                }

                inclusive_or_exclusive(thisElement);
            });

            $(document).on("keyup", ".item_discount", function(){
                const thisElement = $(this);

                inclusive_or_exclusive(thisElement);
            });

            $(document).on("change", ".row_discount_type", function(){
                const thisElement = $(this);

                inclusive_or_exclusive(thisElement);
            });

            $(document).on("keyup", ".quantity", function(){

                const thisElement = $(this);

                inclusive_or_exclusive(thisElement);

                // var qqty = $(this).val();
                // var total = 0;
                // var items_amount = 0;
                // var items_gst_amount = 0;

                // var total_additional_charges = calculate_total_additional_charges();

                // var tr = $(this).closest('tr');

                // var qprice = tr.find('.price').val();
                // var qdiscount = tr.find(".item_discount").val();

                // if(qprice == ''){
                //     qprice = 0;
                // }

                // if(qdiscount == ''){
                //     qdiscount = 0;
                // }

                // var qamount = qqty * qprice;

                // var qtotaldiscount = qqty * qdiscount;

                // if(qamount > 0){
                //     qamount -= qtotaldiscount;
                // }

                // tr.find(".amount").text(qamount);

                // var amounts = $(".amount");
                // var gsts = $(".gst");

                // // console.log(discounts);

                // for (var i = 0; i < amounts.length; i++) {
                //     if(amounts[i].innerText !== ""){
                //         items_amount += parseFloat(amounts[i].innerText);

                //         $("#item_total_amount").val(items_amount);
                //     }
                // }

                // for(var i = 0; i < amounts.length; i++){
                //     total += parseFloat(amounts[i].innerText);
                //     if(gsts[i].innerText !== ""){
                //         gst_amount = total * parseFloat(gsts[i].innerText) / 100;
                //         tr.find(".calculated-gst").text(gst_amount);
                //         tr.find(".calculated-gst-input").val(gst_amount);
                //         items_gst_amount += gst_amount;
                //         total += gst_amount;
                //     }
                // }

                // total += total_additional_charges;

                // $("#total_amount").val( total );
                // $("#item_total_gst").val( items_gst_amount );

                // var amount_paid = $("#amount_paid").val();
                // var total_amount = $("#total_amount").val();

                // if(total_amount == ""){
                //     total_amount = 0;
                // }

                // if(amount_paid == ""){
                //     amount_paid = 0;
                // }

                // if(total_amount !== ""){
                //     var amount_remaining = total_amount - amount_paid;
                //     // amount_remaining = Math.round(amount_remaining);
                //     $("#amount_remaining").val(amount_remaining);
                // }
            });

            $(document).on("keyup", ".cess-input", function(){
                const thisElement = $(this);

                inclusive_or_exclusive(thisElement);
            });

            $(document).on("keyup", ".price", function(){

                const thisElement = $(this);

                inclusive_or_exclusive(thisElement);

                // var pprice = $(this).val();
                // var total = 0;
                // var items_amount = 0;
                // var items_gst_amount = 0;

                // var total_additional_charges = calculate_total_additional_charges();

                // var tr = $(this).closest('tr');

                // var pqty = tr.find(".quantity").val();
                // var pdiscount = tr.find(".item_discount").val();

                // if(pqty == ''){
                //     pqty = 0;
                // }

                // if(pdiscount == ''){
                //     pdiscount = 0;
                // }

                // var pamount = pqty * pprice;

                // var ptotaldiscount = pqty * pdiscount;

                // if(pamount > 0){
                //     pamount -= ptotaldiscount;
                // }

                // tr.find(".amount").text(pamount);

                // var thisGst = parseFloat(tr.find(".gst").text());
                // var thisAmount = parseFloat(tr.find(".amount").text());

                // var calculated_gst_amount = (thisGst / 100) * thisAmount;

                // tr.find(".calculated-gst").text(calculated_gst_amount);
                // tr.find(".calculated-gst-input").val(calculated_gst_amount);

                // var amounts = $(".amount");
                // var gsts = $(".gst");

                // for (var i = 0; i < amounts.length; i++) {
                //     if(amounts[i].innerText !== ""){
                //         items_amount += parseFloat(amounts[i].innerText);

                //         $("#item_total_amount").val(items_amount);
                //     }
                // }


                // for(var j = 0; j < amounts.length; j++){
                //     total += parseFloat(amounts[j].innerText);
                //     if(gsts[j].innerText !== ""){
                //         gst_amount = total * (parseFloat(gsts[j].innerText) / 100);
                //         items_gst_amount += gst_amount;
                //         total += gst_amount;
                //     }
                // }

                // total += total_additional_charges;

                // $("#total_amount").val( total );
                // $("#item_total_gst").val( items_gst_amount );

                // var amount_paid = $("#amount_paid").val();
                // var total_amount = $("#total_amount").val();

                // if(total_amount == ""){
                //     total_amount = 0;
                // }

                // if(amount_paid == ""){
                //     amount_paid = 0;
                // }

                // if(total_amount !== ""){
                //     var amount_remaining = total_amount - amount_paid;
                //     // amount_remaining = Math.round(amount_remaining);
                //     $("#amount_remaining").val(amount_remaining);
                // }

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

                @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                    total_amount = roundToSomeNumber(total_amount);
                @else
                    total_amount = noRoundOff(total_amount);
                @endif

                $("#total_amount").val(total_amount);

                var rounded_off_total_amount = total_amount;
                var round_off_difference = 0;

                @if(auth()->user()->roundOffSetting->purchase_round_off_to == "upward")
                    rounded_off_total_amount = Math.ceil(total_amount);
                    round_off_difference = rounded_off_total_amount - total_amount; 
                @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "downward")
                    rounded_off_total_amount = Math.floor(total_amount);
                    round_off_difference = rounded_off_total_amount - total_amount;
                @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "normal")
                    rounded_off_total_amount = Math.round(total_amount);
                    round_off_difference = rounded_off_total_amount - total_amount;
                @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "manual")
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

            $(document).on("click", "#add-more-items", function(){

                let taxed = $('input[name="tax_inclusive"]:checked').val();
                let disabledValue;

                if(taxed == "inclusive_of_tax") {
                    disabledValue = 'disabled="true"';
                } else {
                    disabledValue = '';
                }

                if( $('input[name="add_lump_sump"]').is(":checked") ){
                    show_add_lump_sump = "display: none;";

                    show_amount_text_field = "display: inline-cell;";
                    show_amount_span = "display: none;";
                } else {
                    show_add_lump_sump = "display: table-cell;";

                    show_amount_text_field = "display: none;";
                    show_amount_span = "display: inline-cell;";
                }

                if( !$('#show_gst_classification').is(":checked") ){
                    show_gst_classification = "display: none;";

                    
                    // show_amount_span = "display: none;";
                } else {
                    show_gst_classification = "display: block;"

                    
                    // show_amount_span = "display: inline-cell;";
                }

                if( $("#show_cess_charge").is(":checked") ){
                    show_cess_amount = "";
                } else {
                    show_cess_amount = "display: none;";
                }

                // checking if the status of registration (company) is not registered ie 0 or composition ie 3
                if(status_of_registration == 0 ){
                    show_tax_fields = "display: none";

                } else {
                    show_tax_fields = "display: table-cell";

                    // show_amount_text_field = "display: none;";
                    show_amount_span = "display: inline-cell;";
                }

                var selected_gst_classification = $('select[name="gst_classification[]"] option:selected').val();

                // console.log(selected_gst_classification);
                var rcm_selected = '';
                var exempt_selected = '';
                var export_selected = '';

                if(selected_gst_classification == 'rcm'){
                    rcm_selected = 'selected="selected"';
                }

                if(selected_gst_classification == 'exempt'){
                    exempt_selected = 'selected="selected"';
                }

                if(selected_gst_classification == 'export'){
                    export_selected = 'selected="selected"';
                }

                $("#dynamic-body").append(
                    `<tr>
                        <td>
                            <input type="hidden" name="item[]" class="item" />
                            <input type="text" class="form-control item_search" placeholder="Product" />
                            <div class="auto"></div>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="item_barcode[]" placeholder="Barcode" required/>
                        </td>
                        <td style="${show_gst_classification}" class="gst-classification-col">
                            <select name="gst_classification[]" class="form-control select_gst_classification">
                                <option disabled selected>Select GST Classification</option>
                                <option ${rcm_selected} value="rcm">under RCM</option>
                                <option ${exempt_selected} value="exempt">Exempt</option>
                                <option ${exempt_selected} value="export">Zero/Export</option>
                            </select>
                        </td>
                        <td style="${show_add_lump_sump}" class="quantity-col">
                            <input type="text" class="form-control quantity" name="quantity[]" required placeholder="Quantity" >
                        </td>
                        <td style="${show_add_lump_sump}" class="quantity-col" style="min-width: 142px">
                            <select name="measuring_unit[]" class="form-control select-measuring-unit">
                                <option>Select Unit</option>
                            </select>
                        </td>
                        <td style="${show_add_lump_sump} visibility: hidden;" class="quantity-col free-quantity-col">
                            <input type="text" class="form-control" name="free_quantity[]" placeholder="Free Qty" >
                        </td>
                        <td style="${show_add_lump_sump}" class="rate-col">
                            <input type="text" class="form-control price trigger-price" name="price[]" required placeholder="Price">
                        </td>
                        <td style="${show_add_lump_sump}" class="discount-col">
                            <div style="width: 100%">
                                <div style="width: 40%; float: left;">
                                    <select class="form-control row_discount_type" name="item_discount_type[]">
                                        <option value="%">%</option>
                                        <option value="f">F</option>
                                    </select>
                                </div>
                                <div style="width: 60%; float: left;">
                                    <input type="text" class="form-control item_discount" name="item_discount[]" placeholder="Discount" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="${show_amount_span}" class="amount amount-span"></span>
                            <input style="${show_amount_text_field}" type="text" class="form-control amount-input trigger-price" placeholder="Amount" name="amount[]" />
                        </td>
                        <td style="${show_cess_amount}" class="cess-col">
                            <input type="text" class="form-control cess-input" name="cess_amount[]" placeholder="CESS Amount" />
                        </td>
                        <td style="${show_tax_fields}" class="tax-col">
                            <span class="gst"></span>
                        </td>
                        <td style="${show_tax_fields}" class="calc-tax-col">
                            <input type="hidden" name="calculated_gst[]" class="calculated-gst-input">
                            <input type="hidden" name="calculated_gst_rcm[]" class="calculated-gst-rcm-input">
                            <input type="hidden" name="gst_tax_type[]" class="gst_tax_type" />
                            <span class="calculated-gst"></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>`
                );
            });

            $(document).on("click", ".delete-row", function(){

                var tr = $(this).closest('tr');

                var thisAmount = tr.find('.amount').text();
                var thisGST = tr.find('.calculated-gst').text();

                var currentAmount = $("#item_total_amount").val();
                var currentGST = $("#item_total_gst").val();
                var currentDiscount = $("#total_discount").val();
                var currentTotalAmount = $("#total_amount").val();
                var amount_paid = $("#amount_paid").val();

                var total_cess_amount = parseFloat($("#item_total_cess").val());
                var total_additional_charges = calculate_total_additional_charges();

                if(thisAmount == ''){
                    thisAmount = 0;
                }
                if(thisGST == ''){
                    thisGST = 0;
                }

                if(currentAmount == ''){
                    currentAmount = 0;
                }
                if(currentGST == ''){
                    currentGST = 0;
                }
                if(currentDiscount == ''){
                    currentDiscount = 0;
                }
                if(currentTotalAmount == ''){
                    currentTotalAmount = 0;
                }
                if(amount_paid == ''){
                    amount_paid = 0;
                }

                var newAmount = parseFloat(currentAmount) - parseFloat(thisAmount);
                var newGST = parseFloat(currentGST) - parseFloat(thisGST);

                var newTotalAmount = ( parseFloat(newAmount) + parseFloat(newGST) ) - parseFloat(currentDiscount);

                if ($("#add_cess_to_total").is(':checked')) {
                    newTotalAmount += total_cess_amount;
                }

                if ($("#add_additional_to_total").is(':checked')) {
                    newTotalAmount += total_additional_charges;
                }

                var newAmountRemaining = parseFloat(newTotalAmount) - parseFloat(amount_paid);

                $("#item_total_amount").val(newAmount);
                $("#item_total_gst").val(newGST);
                $("#total_amount_before_discount").val(parseFloat(newAmount) + parseFloat(newGST));
                $("#total_amount").val(newTotalAmount);
                $("#amount_remaining").val(noRoundOff(newAmountRemaining));

                var delete_item_id = $(this).attr("data-item");

                $.ajax({
                    type: "POST",
                    url: "{{ route('api.remove.all.extra.item.data') }}",
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "item_id": delete_item_id
                    },
                    success: function(response){
                        $( ".item_discount" ).trigger("keyup");
                    }
                });

                $(this).parent().parent().remove();

            });

            // add here
            @if($user_profile->add_lump_sump == 'yes')
                $('input[name="add_lump_sump"]').trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_buyer_name)
                $("#show_buyer_name").trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_order)
                $('#show_sale_order').trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_reference_name)
                $('#show_reference_name').trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_gst_classification)
                $('#show_gst_classification').trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_cess_charge)
                $('#show_cess_charge').trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_tcs)
                $('#show_tcs_charge').trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_consign_info)
                $("#show_consign_info").trigger("change");
            @endif

            @if(isset(auth()->user()->purchaseSelectOption) && auth()->user()->purchaseSelectOption->show_import_export_info)
                $("#show_import_export_info").trigger("change");
            @endif

            @if(isset($purchase_orders))
                $(".price").trigger("keyup");
            @endif


            $('input[name="gst_classification[]"]').on("change", function() {
                var changedValue = $("option:selected", this);

                console.log(changedValue);
            });

        });
        function calculate_total_additional_charges(){
            var additional_labour_charges = $('#additional_labour_charges').val();
            var additional_freight_charges = $('#additional_freight_charges').val();
            var additional_transport_charges = $('#additional_transport_charges').val();
            var additional_insurance_charges = $('#additional_insurance_charges').val();
            var gst_charged = $('#gst_charged').val();

            var total_additional_charges = parseFloat(additional_labour_charges) + parseFloat(additional_freight_charges) + parseFloat(additional_transport_charges) + parseFloat(additional_insurance_charges) + parseFloat(gst_charged);

            total_additional_charges = roundToSomeNumber(total_additional_charges);

            return total_additional_charges;
        }

        function calculate_additional_charges_gst(){
            var labour_charge = $("#labour_charge").val();
            // var freight_charge = $("#freight_charge").val();
            var transport_charge = $("#transport_charge").val();
            var insurance_charge = $("#insurance_charge").val();
            var gst_percentage = $("#gst_percentage").val();

            if(labour_charge == ''){
                labour_charge = 0;
            }
            // if(freight_charge == ''){
            //     freight_charge = 0;
            // }
            if(transport_charge == ''){
                transport_charge = 0;
            }
            if(insurance_charge == ''){
                insurance_charge = 0;
            }


            if(gst_percentage == ''){
                gst_percentage = 0;
            }
            else if(gst_percentage.toLowerCase() == 'exempt' || gst_percentage.toLowerCase() == 'nil' || gst_percentage.toLowerCase() == 'export'){
                gst_percentage = 0;
            }
            // else {
            //     gst_percentage = parseFloat(gst_percentage);
            // }

            var total_charge_amount = parseFloat(labour_charge) + parseFloat(transport_charge) + parseFloat(insurance_charge);

            var calculated_gst_charge = total_charge_amount * (gst_percentage / 100);

            calculated_gst_charge = roundToSomeNumber(calculated_gst_charge);

            return calculated_gst_charge;

        }



        function inclusive_or_exclusive(thisElement){

            // $('#cess-charge-outer').load(document.URL + ' #cess-charge-inner');

            var tr = thisElement.closest('tr');

            var qqty = tr.find('.quantity').val() || 0;
            var total = 0;
            var items_amount = 0;
            var items_gst_amount = 0;

            var total_additional_charges = calculate_total_additional_charges() || 0;

            var qprice = tr.find('.price').val() || 0;
            var row_discount_type = tr.find('.row_discount_type option:selected').val() || 0;
            if(row_discount_type == ''){
                row_discount_type = '%';
            }
            var qdiscount = tr.find(".item_discount").val() || 0;
            var qgst = tr.find(".gst").text() || '0';

            if(qgst.toLowerCase() == 'exempt' || qgst.toLowerCase() == 'nil' || qgst.toLowerCase() == 'export'){
                qgst = 0;
            }

            //console.log('qgst ' + qgst);

            if(qprice == ''){
                qprice = 0;
            }

            if(qdiscount == ''){
                qdiscount = 0;
            }

            var qamount = qqty * qprice;
            
            // if(qdiscount > 0){
            //     if(row_discount_type == '%'){
            //         qprice = qprice - (qprice * qdiscount / 100);
            //     } else {
            //         qprice = qprice - qdiscount;
            //     }
            // }

            if(qdiscount > 0){
                if(row_discount_type == '%'){
                    qamount = qamount - (qamount * qdiscount / 100);
                } else {
                    qamount = qamount - qdiscount;
                }
            }

            var thisCalculatedGstAmount = 0
            if ( $('input[name="tax_inclusive"]:checked').val() == "exclusive_of_tax" ) {

                // console.log("exclusive_of_tax");

                thisCalculatedGstAmount = qamount * parseFloat(qgst) / 100;

            } else {

                // console.log("not exclusive_of_tax");

                if(qamount > 0){
                    // let unit_amount = qprice;
                    // // console.log(qgst);

                    // let first_part = 100 * parseFloat(qgst);
                    // let second_part = parseFloat(unit_amount) + parseFloat(qgst);

                    // var thisCalculatedGstAmount = first_part / second_part;
                    // thisCalculatedGstAmount = thisCalculatedGstAmount * qqty;

                    // qamount -= thisCalculatedGstAmount;

                    // qamount = roundToSomeNumber(qamount);
                    // thisCalculatedGstAmount = roundToSomeNumber(thisCalculatedGstAmount);


                    // var GstOfDiscountedAmount = parseFloat(qamount) * parseFloat(qgst) / 100;
                    // var GstOfDiscountAmount = parseFloat(qtotaldiscount) * parseFloat(qgst) / 100;

                    // var thisCalculatedGstAmount = parseFloat(GstOfDiscountedAmount) - parseFloat(GstOfDiscountAmount);

                    // qamount -= thisCalculatedGstAmount;

                    // qamount = roundToSomeNumber(qamount);
                    // thisCalculatedGstAmount = roundToSomeNumber(thisCalculatedGstAmount);


                    if (qdiscount > 0) {
                        var first_part = parseFloat(qprice) - (parseFloat(qprice) * parseFloat(qdiscount) / 100);
                    } else {
                        var first_part = parseFloat(qprice); 
                    }

                    var second_part = first_part * ( 100 / ( 100 + parseFloat(qgst) ) );

                    var thisCalculatedGstAmount = (first_part - second_part) * qqty;

                    qamount -= thisCalculatedGstAmount;

                    qamount = roundToSomeNumber(qamount);
                    thisCalculatedGstAmount = roundToSomeNumber(thisCalculatedGstAmount);

                }

            }

            tr.find(".gst_tax_type").val($('input[name="tax_inclusive"]:checked').val());
            
            if(tr.find(".item").attr('data-rcm') == 'yes'  || tr.find('select[name="gst_classification[]"] option:selected').val() == 'rcm'){

                tr.find(".calculated-gst-rcm-input").val(thisCalculatedGstAmount);

                tr.find(".calculated-gst-input").val(0);
                tr.find(".calculated-gst").text(0);
            } else {

                tr.find(".calculated-gst-rcm-input").val(0);

                var party_type = $('option:selected', "#party").attr('data-party_type') ? $('option:selected', "#party").attr('data-party_type') : null;

                console.log("party_type", party_type);

                if(party_type != null){
                    if(party_type == 0 || party_type == 2 || party_type == 3){
                    console.log("party_type inside", party_type);
                        tr.find(".calculated-gst-input").val(0);
                        tr.find(".calculated-gst").text(0);
                    } else {
                        tr.find(".calculated-gst-input").val(thisCalculatedGstAmount);
                        tr.find(".calculated-gst").text(thisCalculatedGstAmount);
                    }
                } else {
                    tr.find(".calculated-gst-input").val(thisCalculatedGstAmount);
                    tr.find(".calculated-gst").text(thisCalculatedGstAmount);
                }

                
            }


            @if(auth()->user()->roundOffSetting->purchase_item_amount == "yes")
                qamount = roundToSomeNumber(qamount);
            @else
                qamount = noRoundOff(qamount);
            @endif

            tr.find(".amount").text(roundToSomeNumber(qamount));
            tr.find(".amount-input").val(roundToSomeNumber(qamount));

            var amounts = $(".amount");
            var gsts = $(".calculated-gst");
            var gst_rcm = $(".calculated-gst-rcm-input");


            for (var i = 0; i < amounts.length; i++) {
                if(amounts[i].innerText !== ""){
                    items_amount += parseFloat(amounts[i].innerText);

                    $("#item_total_amount").val(noRoundOff(items_amount));
                }
            }

            total = items_amount;
            var gst_amount = 0;
            var gst_rcm_amount = 0;
            for(var i = 0; i < gsts.length; i++){
                gst_amount += parseFloat(gsts[i].innerText);
                items_gst_amount = parseFloat(noRoundOff(gst_amount));
            }
            $("#total_gst_amounted").val(items_gst_amount);

            for(var i = 0; i < gst_rcm.length; i++){
                gst_rcm_amount += parseFloat(gst_rcm[i].value);
                items_gst_rcm_amount = parseFloat(noRoundOff(gst_rcm_amount));
            }

            $("#item_total_rcm_gst").val(items_gst_rcm_amount);


            // if($("#show_cess_charge").is(":checked")){
                var cesses = $(".cess-input");
                // console.log(cesses);
                var cessLen = cesses ? cesses.length : 0;
                var total_cess_calc = 0;
                for(var i = 0; i < cessLen; i++){
                    const current_cess = (cesses[i].value == '') ? 0 : cesses[i].value;

                    total_cess_calc += parseFloat(current_cess);

                    // console.log(current_cess);
                }
                var total_cess_amounted = total_cess_calc;
            // } else {
            //     var total_cess_amounted = 0;
            // }
            $("#total_cess_amounted").val(total_cess_amounted);

            items_gst_amount += parseFloat(total_cess_amounted);
            // console.log(total_cess_amounted);
            // console.log(items_gst_amount);


            var what_gst_classification = tr.find(".select_gst_classification").val();

            //if error check here
            if(what_gst_classification != 'rcm'){
                total += parseFloat(items_gst_amount);
            }

            total += parseFloat(total_additional_charges);
            

            var tcs = $("#tcs").val();

            if(tcs != ''){
                total += parseFloat(tcs);
            }

            @if(auth()->user()->roundOffSetting->purchase_total_amount == "yes")
                total = roundToSomeNumber(total);
            @else
                total = noRoundOff(total);
            @endif

            $("#total_amount").val(total);

            @if(auth()->user()->roundOffSetting->purchase_gst_amount == "yes")
                items_gst_amount = roundToSomeNumber(items_gst_amount);
            @else
                items_gst_amount = noRoundOff(items_gst_amount);
            @endif

            $("#item_total_gst").val(items_gst_amount);

            $('#total_amount_before_discount').val( parseFloat(items_amount) + parseFloat(items_gst_amount) );

            var amount_paid = $("#amount_paid").val();
            var total_amount = $("#total_amount").val();

            if(total_amount == ""){
                total_amount = 0;
            }

            if(amount_paid == ""){
                amount_paid = 0;
                $("#amount_paid").val(amount_paid);
            }

            var rounded_off_total_amount = total_amount;
            var round_off_difference = 0;

            @if(auth()->user()->roundOffSetting->purchase_round_off_to == "upward")
                rounded_off_total_amount = Math.ceil(total_amount);
                round_off_difference = rounded_off_total_amount - total_amount;
            @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "downward")
                rounded_off_total_amount = Math.floor(total_amount);
                round_off_difference = rounded_off_total_amount - total_amount;
            @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "normal")
                rounded_off_total_amount = Math.round(total_amount);
                round_off_difference = rounded_off_total_amount - total_amount;
            @elseif(auth()->user()->roundOffSetting->purchase_round_off_to == "manual")
                rounded_off_total_amount = noRoundOff(total_amount);
                round_off_difference = 0;  
            @endif


            round_off_difference = noRoundOff(round_off_difference);
            $("#round_offed").val(round_off_difference);
            $("#amount_to_pay").val(noRoundOff(rounded_off_total_amount));

            var amount_remaining = rounded_off_total_amount - amount_paid;

            $("#amount_remaining").val(noRoundOff(amount_remaining));

            // if(total_amount !== ""){
            //     var amount_remaining = total_amount - amount_paid;
            //     amount_remaining = noRoundOff(amount_remaining);
            //     $("#amount_remaining").val(amount_remaining);
            // }
        }

        $("#purchase_bill_date").on( "keyup", function () {
            $("#bank_payment_date").val($(this).val());
            $("#pos_payment_date").val($(this).val());
        } );


        $("#create-purchase").on("submit", function(e){

            var formValidationSuccessful = true;
            var formValidationMessage = "<ul>";

            if( $("#bill_no").val() == '' ){
                e.preventDefault();
                formValidationSuccessful = false;
                formValidationMessage += "<li>Bill no. is required.</li>";
            }

            if( $("#party").val() == null ){
                e.preventDefault();
                formValidationSuccessful = false;
                formValidationMessage += "<li>Customer is required.</li>";
            }

            if( $("#purchase_bill_date").val() == '' ){
                e.preventDefault();
                formValidationSuccessful = false;
                formValidationMessage += "<li>Bill date is required.</li>";
            }

            
            $('#dynamic-body tr').each(function(i, row){
                var thisRow = $(row);

                if(thisRow.find('input[name="item[]"]').val() == ''){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Either the product field is empty or the product you input isn't in our database.</li>"; 
                }

                if( $('input[name="add_lump_sump"]').is(":checked") ){
                    if( $('.amount-input').val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Amount is required.</li>";
                    }
                } else {
                    if( $('.quantity').val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Quantity is required.</li>";
                    }

                    if( $('.price').val() == "0" ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Rate should be greater than zero.</li>";
                    }

                    if( $('.price').val() == '' ){
                        e.preventDefault();
                        formValidationSuccessful = false;
                        formValidationMessage += "<li>Rate is required.</li>";
                    }
                }
            });
            

            if( $("#cash").is(":checked") ){
                
                if($("#cashed_amount").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Cash amount is required.</li>";
                }
            }

            if( $("#bank").is(":checked") ){
            
                if( $("#banked_amount").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Bank amount is required.</li>";
                }

                if( $("#bank_cheque").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Bank cheque is required.</li>";
                }

                if( $("#bank_payment_date").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>Bank payment date is required.</li>";
                }
            }

            if( $("#pos").is(":checked") ){
                
                if( $("#posed_amount").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>POS amount is required</li>";
                }

                if( $("#pos_payment_date").val() == '' ){
                    e.preventDefault();
                    formValidationSuccessful = false;
                    formValidationMessage += "<li>POS payment date is required</li>";
                }
            }

            if( $("#item_total_amount").val() == ''  ){
                e.preventDefault();
                formValidationSuccessful = false;
                formValidationMessage += "<li>Items Amt is required.</li>";
            }

            if( $("#item_total_amount").val() == "NaN" ){
                e.preventDefault();
                formValidationSuccessful = false;
                formValidationMessage += "<li>Items Amt should be a number.</li>";
            }

            formValidationMessage += "</ul>";

            if( ! formValidationSuccessful){
                show_custom_alert(`<span style="color: red">${formValidationMessage}</span>`);
            }

        });


    </script>
@endsection

{{-- <p><button type="button" class="btn btn-link add-price" data-item="">Add Price</a></p> --}}
{{-- ALTER TABLE `purchase_records` ADD CONSTRAINT uq_purchase_records UNIQUE(bill_no, party_id) --}}
