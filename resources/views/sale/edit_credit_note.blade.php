@extends('layouts.dashboard')

@section('content')

{{-- {!! Breadcrumbs::render('invoice-detail-credit-note', request()->segment(2)) !!} --}}

    <form method="post" action="{{ route('update.sale.credit.note') }}">
        {{ csrf_field() }}
        <input type="hidden" value="{{ $note_no }}" name="search_by_note_no" />
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
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    @if($invoice->amount_type == 'exclusive') <strong>Invoice is Excl of Taxes</strong> @endif
                    @if($invoice->amount_type == 'inclusive') <strong>Invoice is Incl of Taxes</strong> @endif
                </div>
                <div class="col-md-6"><strong>Invoice no</strong> : @if($invoice->invoice_no != null) {{ $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix }} @else {{ $invoice->id }} @endif</div>
            </div>
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <strong>{{ $invoice->party->name }}</strong>
                </div>
                <div class="col-md-3">
                    <strong>Invoice Date</strong> : <span id="validate-against">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Due Date</strong> : {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                </div>
            </div>
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <strong>Note No</strong> : <input type="text" class="form-control" name="note_no" value="{{ $note_no }}" @if(isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->bill_no_type == 'auto') readonly @endif />
                </div>
                <div class="col-md-6">
                    <strong>Note Date</strong> : <input type="text" class="form-control custom_date" id="note_date" name="note_date" placeholder="DD/MM/YYYY" value="{{ \Carbon\Carbon::parse($note_date)->format('d/m/Y') }}" autocomplete="off" maxlength="10">
                    <p id="note_date_validation_error" style="color: red;"></p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Revised Price</th>
                            {{-- <th>Calculated Price</th> --}}
                            <th>Revised GST</th>
                            {{-- <th>Calculated GST</th> --}}
                            <th colspan="2">@if(auth()->user()->profile->inventory_type != "without_inventory") Revised Qty @endif</th>
                            {{-- <th>Calculated Qty</th> --}}
                            {{-- <th>Revised Discount</th> --}}
                            {{-- <th>Value</th> --}}
                            <th class="text-center" colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1; $items_price = 0; $items_gst = 0; $items_discount = 0; $items_total = 0;  @endphp
                        @if(count($credit_notes) > 0)
                            @foreach($credit_notes as $credit_note)
                            <tr>
                                <td>{{ $count++ }}<input type="hidden" name="row_id[]" value="{{ $credit_note->id }}" disabled /></td>
                                <td>{{ $credit_note->item_name }}<input type="hidden" name="item_id[]" value="{{ $credit_note->item_id }}" disabled /></td>
                                
                                <td><input data-gst="{{ auth()->user()->profile->registered == 0 || auth()->user()->profile->registered == 3 ? 0 : $credit_note->item_gst ?? 0 }}" type="text" class="form-control" name="price_difference[]" value="{{ $credit_note->price ?? 0 }}" disabled /></td>
                            
                                {{-- <td>{{ $sale->price - $sale->price_difference }}</td> --}}    
                                
                                <td><input type="text" class="form-control" name="gst_difference[]" value="{{ $credit_note->gst ?? 0 }}" disabled /></td>
                                
                                {{-- <td>{{ $sale->gst - $sale->gst_percent_difference }}</td> --}}

                                <td><input data-gst="{{ auth()->user()->profile->registered == 0 || auth()->user()->profile->registered == 3 ? 0 : $credit_note->item_gst ?? 0 }}" type="text" class="form-control" name="quantity_difference[]" value="{{ $credit_note->original_qty ?? 0 }}" @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif disabled /></td>
                                <td>
                                    {{ $credit_note->original_unit }}
                                </td>
                                

                                {{-- <td><input type="text" class="form-control" name="discount_difference[]" value="{{ $credit_note->discount ?? 0 }}" disabled /></td> --}}

                                {{-- <td><span class="row-value">{{ $credit_note->item_total }}</span></td> --}}

                                {{-- <td>{{ $sale->quantity - $sale->quantity_difference }}</td> --}}

                                {{-- <td><button class="btn btn-link edit-note" data-invoice_id="{{ $invoice_no }}" data-item_id="{{ $sale->item_id }}" data-price="{{ $sale->price }}" data-gst="{{ $sale->gst }}" data-qty="{{ $sale->quantity }}" data-discount="{{ $sale->discount }}">Edit Credit Note</button></td> --}}

                                {{-- <td class="text-center">
                                    <button class="btn btn-link edit-note" data-item_gst="{{ $sale->item_gst }}" data-invoice_id="{{ $invoice_no }}" data-item_id="{{ $sale->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-original_discount="{{ $sale->discount }}" data-price="{{ $sale->price_difference ? $sale->price_difference : $sale->price }}" data-gst="{{ $sale->gst_percent_difference ? $sale->gst_percent_difference : $sale->gst }}" data-qty="{{ $sale->quantity_difference ? $sale->quantity_difference : $sale->quantity }}" data-discount="{{ $sale->discount_difference ? $sale->discount_difference : $sale->discount }}">Edit</button>
                                </td> --}}

                                <td class="text-center">
                                    <button type="button" class="btn btn-link edit-row" style="color: blue;"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                </td>
                                <td class="text-center">
                                    <form  method="post" action="{{ route('sale.delete.credit.note') }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="row_id" value="{{ $credit_note->id }}" />
                                        <button class="btn btn-link"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    </form>

                                    {{-- <button type="button" class="btn btn-link delete-row" style="color: red;"><i class="fa fa-trash-o" aria-hidden="true"></i></button> --}}
                                </td>
                            </tr>
                            {{-- <tr style="padding-top: 0; padding-bottom: 0;">
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;"></td>
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;">
                                    <button class="btn btn-link edit-price" data-invoice_id="{{ $invoice_no }}" data-item_id="{{ $sale->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-price="{{ $sale->price_difference ? $sale->price_difference : $sale->price }}" data-gst="{{ $sale->gst_percent_difference ? $sale->gst_percent_difference : $sale->gst }}" data-qty="{{ $sale->quantity_difference ? $sale->quantity_difference : $sale->quantity }}">Edit Price</button>
                                </td>
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;">
                                    <button class="btn btn-link edit-gst" data-invoice_id="{{ $invoice_no }}" data-item_id="{{ $sale->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-gst="{{ $sale->gst_percent_difference ? $sale->gst_percent_difference : $sale->gst }}" data-price="{{ $sale->price_difference ? $sale->price_difference : $sale->price }}" data-qty="{{ $sale->quantity_difference ? $sale->quantity_difference : $sale->quantity }}">Edit GST</button>
                                </td>
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;">
                                    <button class="btn btn-link edit-qty" data-invoice_id="{{ $invoice_no }}" data-item_id="{{ $sale->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-qty="{{ $sale->quantity_difference ? $sale->quantity_difference : $sale->quantity }}" data-gst="{{ $sale->gst_percent_difference ? $sale->gst_percent_difference : $sale->gst }}" data-price="{{ $sale->price_difference ? $sale->price_difference : $sale->price }}">Edit Quantity</button>
                                </td>
                            </tr> --}}
                                @php
                                        $price = $credit_note->price ?? 0;
                                        $gst = $credit_note->gst ?? 0;
                                        $qty = $credit_note->original_qty ?? 0;
                                        // $discount = $credit_note->discount ?? 0;
                                        if(auth()->user()->profile->inventory_type == "without_inventory"){
                                            $items_price += $price;
                                        }else{ 
                                            $items_price += $price * $qty;
                                        }
                                        // $items_discount += $discount;
                                        $items_gst += $gst;
                                        // $items_total += ((($price * $qty) - $discount)  + $gst);
                                        $items_total = ($items_price + $gst);
                                @endphp
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Reason</th>
                            <th colspan="3">
                                <select class="form-control" name="reason_change" id="note_reason_change" required>
                                    <option value="" selected disabled>Select a reason</option>
                                    <option @if($credit_notes->first()->reason == 'sale_return') selected="selected" @endif value="sale_return">Sale Return</option>
                                    <option @if($credit_notes->first()->reason == 'new_rate_or_discount_value_with_gst') selected="selected" @endif value="new_rate_or_discount_value_with_gst">New Rate or Discount Value with GST</option>
                                    <option @if($credit_notes->first()->reason == 'discount_on_sale') selected="selected" @endif value="discount_on_sale">Discount</option>
                                </select>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="5">Item Value</th>
                            <th colspan="3"><input type="text" class="form-control" name="taxable_value" value="{{ $items_price }}" readonly /></th>
                        </tr>
                        {{-- <tr>
                            <th colspan="5">Discount</th>
                            <th colspan="3"><input type="text" class="form-control" name="discount_value" value="{{ $items_discount }}" readonly /></th>
                        </tr> --}}
                        <tr>
                            <th colspan="5">GST</th>
                            <th colspan="3"><input type="text" class="form-control" name="gst_value" value="{{ $items_gst }}" readonly /></th>
                        </tr>
                        <tr>
                            <th colspan="5">Note Value</th>
                            <th colspan="3"><input type="text" class="form-control" name="note_value" value="{{ $items_total }}" readonly /></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="submit" class="btn btn-success" id="update_note">Update Note</button>
        </div>

    </form>

    {{-- <div class="modal" id="edit-note-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Note</h4>
                </div>
                <div class="modal-body">
                    <form id="form-note" method="post" action="{{ route('sale.create.or.update.credit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="sale" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="invoice_id" name="invoice_id" placeholder="Invoice Id" readonly />
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" class="form-control" id="price" name="price" placeholder="Price" readonly />
                        </div>
                        <div class="form-group">
                            <label>Price Difference</label>
                            <input type="text" class="form-control" id="price_difference" name="price_difference" placeholder="Price Difference" />
                        </div>
                        <div class="form-group">
                            <label>GST</label>
                            <input type="text" class="form-control" id="gst" name="gst" placeholder="GST" readonly />
                        </div>
                        <div class="form-group">
                            <label>GST Difference</label>
                            <input type="text" class="form-control" id="gst_percent_difference" name="gst_percent_difference" placeholder="GST Difference" />
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Quantity" readonly />
                        </div>
                        <div class="form-group">
                            <label>Quantity Difference</label>
                            <input type="text" class="form-control" id="quantity_difference" name="quantity_difference" placeholder="Quantity Difference" />
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <select class="form-control" id="reason" name="reason">
                                <option value="discount">Discount</option>
                                <option value="goods returns">Good Returns</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                    <p id="note-error"></p>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- <div class="modal" id="edit-price-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Note</h4>
                </div>
                <div class="modal-body">
                    <form id="form-note" method="post" action="{{ route('sale.create.or.update.credit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="sale" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="invoice_id" name="invoice_id" placeholder="Invoice Id" readonly />
                        </div>
                        <div class="form-group">
                            <label>GST</label>
                            <input type="text" class="form-control" id="price_gst" name="price_gst" placeholder="GST" readonly />
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" id="price_qty" name="price_qty" placeholder="Quantity" readonly />
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" class="form-control" id="price" name="price" placeholder="Price" readonly />
                        </div>
                        <div class="form-group">
                            <label>Price Difference</label>
                            <input type="text" class="form-control" id="price_difference" name="price_difference" placeholder="Price Difference" />
                        </div>

                        <div class="form-group">
                            <label>Taxable Value</label>
                            <input type="text" class="form-control" id="price_taxable_value" name="taxable_value" placeholder="Taxable value" />
                        </div>
                        <div class="form-group">
                            <label>GST Value</label>
                            <input type="text" class="form-control" id="price_gst_value" name="gst_value" placeholder="GST value" />
                        </div>
                        <div class="form-group">
                            <label>Note Value</label>
                            <input type="text" class="form-control" id="price_note_value" name="note_value" placeholder="Note value" />
                        </div>

                        <div class="form-group">
                            <label>Reason</label>
                            <div class="form-group">
                                <select class="form-control" id="reason_price_change" name="reason_price_change">
                                    <option selected disabled>Please select a reason</option>
                                    <option value="discount_on_purchase">Discount on Purchase</option>
                                    <option value="discount_on_sale">Discount on Sale</option>
                                    <option value="sale_return">Sale Return</option>
                                    <option value="purchase_return">Purchase Return</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="reason_price_change_other" name="reason_price_change_other" placeholder="Please Specify" style="display: none;"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                    <p id="note-error"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="edit-gst-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Note</h4>
                </div>
                <div class="modal-body">
                    <form id="form-note" method="post" action="{{ route('sale.create.or.update.credit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="sale" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="invoice_id" name="invoice_id" placeholder="Invoice Id" readonly />
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" class="form-control" id="gst_price" name="gst_price" placeholder="Price" readonly />
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" id="gst_qty" name="gst_qty" placeholder="Quantity" readonly />
                        </div>
                        <div class="form-group">
                            <label>GST</label>
                            <input type="text" class="form-control" id="gst" name="gst" placeholder="GST" readonly />
                        </div>
                        <div class="form-group">
                            <label>GST Difference</label>
                            <input type="text" class="form-control" id="gst_percent_difference" name="gst_percent_difference" placeholder="GST Difference" />
                        </div>

                        <div class="form-group">
                            <label>Taxable Value</label>
                            <input type="text" class="form-control" id="gst_taxable_value" name="taxable_value" placeholder="Taxable value" />
                        </div>
                        <div class="form-group">
                            <label>GST Value</label>
                            <input type="text" class="form-control" id="gst_gst_value" name="gst_value" placeholder="GST value" />
                        </div>
                        <div class="form-group">
                            <label>Note Value</label>
                            <input type="text" class="form-control" id="gst_note_value" name="note_value" placeholder="Note value" />
                        </div>

                        <div class="form-group">
                            <label>Reason</label>
                            <div class="form-group">
                                <select class="form-control" id="reason_gst_change" name="reason_gst_change">
                                    <option selected disabled>Please select a reason</option>
                                    <option value="discount_on_purchase">Discount on Purchase</option>
                                    <option value="discount_on_sale">Discount on Sale</option>
                                    <option value="sale_return">Sale Return</option>
                                    <option value="purchase_return">Purchase Return</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="reason_gst_change_other" name="reason_gst_change_other" placeholder="Please specify" style="display: none;"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                    <p id="note-error"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="edit-qty-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Note</h4>
                </div>
                <div class="modal-body">
                    <form id="form-note" method="post" action="{{ route('sale.create.or.update.credit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="sale" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="invoice_id" name="invoice_id" placeholder="Invoice Id" readonly />
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" class="form-control" id="qty_price" name="qty_price" placeholder="Price" readonly />
                        </div>
                        <div class="form-group">
                            <label>GST</label>
                            <input type="text" class="form-control" id="qty_gst" name="qty_gst" placeholder="GST" readonly />
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Quantity" readonly />
                        </div>
                        <div class="form-group">
                            <label>Quantity Difference</label>
                            <input type="text" class="form-control" id="quantity_difference" name="quantity_difference" placeholder="Quantity Difference" />
                        </div>

                        <div class="form-group">
                            <label>Taxable Value</label>
                            <input type="text" class="form-control" id="qty_taxable_value" name="taxable_value" placeholder="Taxable value" />
                        </div>
                        <div class="form-group">
                            <label>GST Value</label>
                            <input type="text" class="form-control" id="qty_gst_value" name="gst_value" placeholder="GST value" />
                        </div>
                        <div class="form-group">
                            <label>Note Value</label>
                            <input type="text" class="form-control" id="qty_note_value" name="note_value" placeholder="Note value" />
                        </div>

                        <div class="form-group">
                            <label>Reason</label>
                            <div class="form-group">
                                <select class="form-control" id="reason_quantity_change" name="reason_quantity_change">
                                    <option selected disabled>Please select a reason</option>
                                    <option value="discount_on_purchase">Discount on Purchase</option>
                                    <option value="discount_on_sale">Discount on Sale</option>
                                    <option value="sale_return">Sale Return</option>
                                    <option value="purchase_return">Purchase Return</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="reason_quantity_change_other" name="reason_quantity_change_other" placeholder="Please specify" style="display: none;"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                    <p id="note-error"></p>
                </div>
            </div>
        </div>
    </div> --}}


    <div class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Note</h4>
                </div>
                <div class="modal-body">
                    <form id="form-note" method="post" action="{{ route('sale.create.or.update.credit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" id="item_gst" value="" />
                        <input type="hidden" name="note_type" value="sale" />
                        <div class="form-group">
                            <label>Invoice No.</label>
                            <input type="text" class="form-control" id="invoice_id" name="invoice_id" placeholder="Invoice Id" readonly />
                        </div>
                        <input type="hidden" name="price" id="price" />
                        <input type="hidden" name="gst" id="gst" />
                        <input type="hidden" name="quantity" id="quantity" />
                        <input type="hidden" name="discount" id="discount" />
                        <div class="form-group">
                            <label>Price(Difference)</label>
                            <input type="text" class="form-control" id="price_difference" name="price_difference" placeholder="Price Difference" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly @endif />
                        </div>
                        <div class="form-group">
                            <label>Quantity(Difference)</label>
                            <input type="text" class="form-control" id="quantity_difference" name="quantity_difference" placeholder="Quantity Difference" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly @endif />
                        </div>
                        <div class="form-group">
                            <label>GST(Difference)</label>
                            <input type="text" class="form-control" id="gst_percent_difference" name="gst_percent_difference" placeholder="GST Difference" />
                        </div>
                        <div class="form-group">
                            <label>Discount(Difference)</label>
                            <input type="text" class="form-control" id="discount_difference" name="discount_difference" placeholder="Discount Difference" />
                        </div>

                        {{-- <div class="form-group"> --}}
                            {{-- <label>Taxable Value</label> --}}
                            <input type="hidden" class="form-control" id="taxable_value" name="taxable_value" placeholder="Taxable value" readonly />
                        {{-- </div> --}}
                            <input type="hidden" class="form-control" id="discount_value" name="discount_value" placeholder="Discount value" readonly />
                        {{-- <div class="form-group"> --}}
                            {{-- <label>GST Value</label> --}}
                            <input type="hidden" class="form-control" id="gst_value" name="gst_value" placeholder="GST value" readonly />
                        {{-- </div> --}}
                        {{-- <div class="form-group"> --}}
                            {{-- <label>Note Value</label> --}}
                            <input type="hidden" class="form-control" id="note_value" name="note_value" placeholder="Note value" readonly />
                        {{-- </div> --}}

                        <div class="form-group">
                            <label>Reason</label>
                            <div class="form-group">
                                <select class="form-control" id="reason_change" name="reason_change" required>
                                    <option selected disabled>Please select a reason</option>
                                    <option value="discount_on_purchase">Discount on Purchase</option>
                                    <option value="discount_on_sale">Discount on Sale</option>
                                    <option value="sale_return">Sale Return</option>
                                    <option value="purchase_return">Purchase Return</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" id="reason_change_other" name="reason_change_other" placeholder="Please specify" style="display: none;"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                    <p id="note-error"></p>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="edit-note-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Note</h4>
                </div>
                <div class="modal-body">
                    <form id="form-note" method="post" action="{{ route('update.sale.credit.note.item') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="modal_row_id" value="" />
                        <div class="form-group">
                            <label>Revised Price</label>
                            <input type="text" class="form-control" name="modal_revised_price" value="" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly @endif />
                        </div>
                        <div class="form-group">
                            <label>Revised Gst</label>
                            <input type="text" class="form-control" name="modal_revised_gst" value="{{ auth()->user()->profile->registered == 0 || auth()->user()->profile->registered == 3 ? 0 : '' }}" @if(auth()->user()->profile->registered == 0 || auth()->user()->profile->registered == 3) readonly @endif />
                        </div>
                        <div class="form-group" @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif>
                            <label @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif>Revised Qty</label>
                            <div class="form-group">
                                <input type="text" class="form-control" name="modal_revised_qty" value="{{ auth()->user()->profile->inventory_type == "without_inventory" ? 0 : '' }}" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly @endif />
                            </div>
                            <select name="modal_measuring_unit" class="form-control select-measuring-unit" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly style="visibility:hidden" @endif>
                                <option @if($credit_note->original_unit == $credit_note->base_unit) selected="selected" @endif>{{ $credit_note->base_unit }}</option>
                                @if($credit_note->alternate_unit)
                                <option @if($credit_note->original_unit == $credit_note->alternate_unit) selected="selected" @endif>{{ $note->alternate_unit }}</option>
                                @endif
                                @if($credit_note->compound_unit)
                                <option @if($credit_note->original_unit == $credit_note->compound_unit) selected="selected" @endif>{{ $credit_note->compound_unit }}</option>
                                @endif
                            </select>
                        </div>
                        {{-- <div class="form-group"> --}}
                            {{-- <label>Revised Discount</label> --}}
                            <input type="hidden" class="form-control" name="modal_revised_discount" value="" />
                        {{-- </div> --}}
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            // $(".edit-note").on('click', function (){
            //     var id = $(this).attr('data-id');
            //     var qty = $(this).attr('data-qty');
            //     $("#row_id").val(id);
            //     $("#old_quantity").val(qty);
            //     $("#edit-note-modal").modal('show');


            //     var item_id = $(this).attr('data-item_id');
            //     var invoice_id = $(this).attr('data-invoice_id');
            //     var price = $(this).attr('data-price');
            //     var gst = $(this).attr('data-gst');
            //     var qty = $(this).attr('data-qty');
            //     var discount = $(this).attr('data-discount');

            //     $("#item_id").val(item_id);
            //     $("#invoice_id").val(invoice_id);
            //     $("#price").val(price);
            //     $("#gst").val(gst);
            //     $("#quantity").val(qty);
            //     $("#discount").val(discount);
            //     $("#edit-note-modal").modal('show');
            // });

            // $(".edit-price").on('click', function (){
            //     open_modal_set_values( $(this) );
            // });

            // $(".edit-gst").on('click', function (){
            //     open_modal_set_values( $(this) );
            // });
            
            // $(".edit-qty").on('click', function (){
            //     open_modal_set_values( $(this) );
            // });

            $(".edit-note").on('click', function (){
                open_modal_set_values( $(this) );
            });

            // ----------------------------------------------------------------------------------------------------

            $("#price_difference").on("keyup", function(){
                calculate_note_value();
            });

            $("#gst_percent_difference").on("keyup", function(){
                calculate_note_value();
            });

            $("#quantity_difference").on("keyup", function(){
                calculate_note_value();
            });

            $("#discount_difference").on("keyup", function(){
                calculate_note_value();
            });

            $(".edit-row").on("click", function() {
                $("#edit-note-modal").modal('show');
                var tr = $(this).closest('tr');
                attach_values_to_modal(tr);
            });

            function attach_values_to_modal(tr)
            {
                var id = tr.find('input[name="row_id[]"]').val();
                var price = tr.find('input[name="price_difference[]"]').val();
                var gstPercent = tr.find('input[name="price_difference[]"]').attr('data-gst');
                var gst = tr.find('input[name="gst_difference[]"]').val();
                var qty = tr.find('input[name="quantity_difference[]"]').val();
                var discount = tr.find('input[name="discount_difference[]"]').val();

                if(price == ''){
                    price = 0;
                }

                if(gstPercent == ''){
                    gstPercent = 0;
                }

                if(gst == ''){
                    gst = 0;
                }

                if(qty == ''){
                    qty = 0;
                }

                if(discount == ''){
                    discount = 0;
                }

                $('input[name="modal_row_id"]').val(id);
                $('input[name="modal_revised_price"]').val(price);
                $('input[name="modal_revised_price"]').attr('data-gst', gstPercent);
                $('input[name="modal_revised_gst"]').val(gst);
                $('input[name="modal_revised_qty"]').val(qty);
                $('input[name="modal_revised_qty"]').attr('data-gst', gstPercent);
                $('input[name="modal_revised_discount"]').val(0);
            }


            function open_modal_set_values( element ){
                
                var item_id = element.attr('data-item_id');
                var item_gst = element.attr('data-item_gst');
                var invoice_id = element.attr('data-invoice_id');
                var price = element.attr('data-price');
                var gst = element.attr('data-gst');
                var qty = element.attr('data-qty');
                var discount = element.attr('data-discount');

                if(item_gst.toLowerCase() == 'nil' || item_gst.toLowerCase() == 'exempt' || item_gst.toLowerCase() == 'export'){
                    item_gst = 0;
                }

                var original_price = element.attr('data-original_price');
                var original_gst = element.attr('data-original_gst');
                var original_qty = element.attr('data-original_qty');
                var original_discount = element.attr('data-original_discount');

                $("#item_id").val(item_id);
                $("#item_gst").val(item_gst);
                $("#invoice_id").val(invoice_id);
                $("#price_difference").val(price);
                $("#gst_percent_difference").val(gst);
                $("#quantity_difference").val(qty);
                $("#discount_difference").val(discount);

                $("#price").val(original_price);
                $("#gst").val(original_gst);
                $("#quantity").val(original_qty);
                $("#discount").val(original_discount);

                var taxable_value = qty*price;
                var discount_value = (qty*price) * discount/100;
                var gst_value = gst;
                var note_value = (parseFloat(taxable_value) - parseFloat(discount_value)) + parseFloat(gst_value);

                $("#taxable_value").val(taxable_value);
                $("#discount_value").val(discount_value);
                $("#gst_value").val(gst_value);
                $("#note_value").val(note_value);

                $("#edit-note-modal").modal('show');
            }


            function calculate_note_value()
            {
                var qty = $("#quantity_difference").val();
                var gst = $("#item_gst").val();
                var price = $("#price_difference").val();
                var discount = $("#discount_difference").val();

                if(gst == ''){
                    gst = 0;
                }

                if(price == ''){
                    price = 0;
                }

                if(qty == ''){
                    qty = 0;
                }

                if(discount == ''){
                    discount = 0;
                }

                var taxable_value = qty * price;
                var discount_value = (qty*price) * discount/100;
                var gst_value = taxable_value * gst / 100;
                var note_value = (parseFloat(taxable_value) - parseFloat(discount_value)) + parseFloat(gst_value);

                $("#taxable_value").val(taxable_value);
                $("#discount_value").val(discount_value);
                $("#gst_percent_difference").val(gst_value);
                $("#gst_value").val(gst_value);
                $("#note_value").val(note_value);
            }

            // $("#reason_price_change").on("change", function(){
            //     var selected_reason = $(this).val();

            //     if(selected_reason == 'other'){
            //         $("#reason_price_change_other").show();
            //     } else {
            //         $("#reason_price_change_other").hide();
            //     }
            // });

            // $("#reason_gst_change").on("change", function(){
            //     var selected_reason = $(this).val();

            //     if(selected_reason == 'other'){
            //         $("#reason_gst_change_other").show();
            //     } else {
            //         $("#reason_gst_change_other").hide();
            //     }
            // });

            // New code down

            $('input[name="modal_revised_price"]').on("keyup", function() {
                var gst_percent = $(this).data('gst');

                var price = $(this).val();
                var qty = $('input[name="modal_revised_qty"]').val();

                var amount = price * qty;

                var gst = amount * gst_percent / 100;

                $('input[name="modal_revised_gst"]').val(gst);
            });

            $('input[name="modal_revised_qty"]').on("keyup", function() {
                var gst_percent = $(this).data('gst');

                var price = $('input[name="modal_revised_price"]').val();
                var qty = $(this).val();

                var amount = price * qty;

                var gst = amount * gst_percent / 100;

                $('input[name="modal_revised_gst"]').val(gst);
            });

            // New code up

            $("#reason_change").on("change", function(){
                var selected_reason = $(this).val();

                if(selected_reason == 'other'){
                    $("#reason_change_other").show();
                } else {
                    $("#reason_change_other").hide();
                }
            });


            $(".delete-row").on("click", function() {
                $(this).parent().parent().remove();

                calculateTFoot();
            });

            $('input').on("keyup", function() {
                var tr = $(this).closest('tr');

                const value = calculateValue(tr);
                tr.find('.row-value').text(value);

                calculateTFoot();
            });

            function calculateTFoot(){
                
                //const price = calculatePriceValue();
                const gst = calculateGstValue();
                //const qty = calculateQtyValue();
                const discount = calculateDiscountValue();

                const item_value = calculateItemValue();
                const note_value = parseFloat(item_value) - parseFloat(discount) + parseFloat(gst);

                $('input[name="taxable_value"]').val(item_value);
                // $('input[name="discount_value"]').val(discount);
                $('input[name="gst_value"]').val(gst);
                $('input[name="note_value"]').val(note_value);
            }

            function calculateValue(tr) {

                var price = tr.find('input[name="price_difference[]"]').val() || 0;
                var gst = tr.find('input[name="gst_difference[]"]').val() || 0;
                var qty = tr.find('input[name="quantity_difference[]"]').val() || 0;
                // var discount = tr.find('input[name="discount_difference[]"]').val();

                if(price == ''){
                    price = 0;
                }

                if(gst == ''){
                    gst = 0;
                }

                if(qty == ''){
                    qty = 0;
                }

                // if(discount == ''){
                //     discount = 0;
                // }

                @if(auth()->user()->profile->inventory_type == "without_inventory")
                var item_amount = price;
                @else
                var item_amount = price * qty;
                @endif

                var item_amount_with_gst = parseFloat(item_amount) + parseFloat(gst);

                // var total_value = item_amount_with_gst - discount;

                var total_value = item_amount_with_gst;

                return total_value;

            };

            function calculateItemValue(){
                let totalPrice = 0;
                let price = [];
                let qty = [];

                $('input[name="price_difference[]"]').each(function(i, v) {
                    price[i] = v.value;
                });

                $('input[name="quantity_difference[]"]').each(function(i, v) {
                    qty[i] = v.value;
                });

                for(i=0; i<price.length; i++){
                    totalPrice += (price[i] * qty[i]);
                }

                return totalPrice;
            };

            function calculateGstValue(){
                let totalGst = 0;

                $('input[name="gst_difference[]"]').each(function(i, v) {
                    totalGst += parseFloat(v.value);
                });

                return totalGst;
            };

            // function calculateQtyValue(){
            //     let totalQty = 0;

            //     $('input[name="quantity_difference[]"]').each(function(i, v) {
            //         totalQty += parseFloat(v.value);
            //     });

            //     return totalQty;
            // };

            function calculateDiscountValue(){
                let totalDiscount = 0;

                $('input[name="discount_difference[]"]').each(function(i, v) {
                    value = v.value ? v.value : 0;
                    totalDiscount += parseFloat(value);
                });

                return totalDiscount;
            };

            $('#note_reason_change').on("change", function() {
                console.log($(this).val());
                if( $(this).val() == "discount_on_sale" ){
                    localStorage.setItem("gst", $('input[name="gst_difference[]"]').val());
                    $('input[name="gst_difference[]"]').val(0).attr("disabled", true).trigger('keyup');
                } else {
                    if( localStorage.getItem("gst") !== null ){
                        $('input[name="gst_difference[]"]').val( localStorage.getItem("gst") ).attr("disabled", true);
                        localStorage.removeItem("gst");
                    }
                }
            });

            $("#note_date").on("keyup", function() {
                var validate_against = $("#validate-against").text();
                var validate_date = $(this).val();
                validateTwoDates(validate_against, validate_date, "#", "update_note", "#", "note_date_validation_error");
            });

        });
    </script>
@endsection
