@extends('layouts.dashboard')

@section('content')

{{-- {!! Breadcrumbs::render('purchase-detail-debit-note', request()->segment(2)) !!} --}}

    <form method="post"  action="{{ route('update.purchase.debit.note') }}">
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
                    @if($purchase->amount_type == 'exclusive') <strong>Invoice is Excl of Taxes</strong> @endif
                    @if($purchase->amount_type == 'inclusive') <strong>Invoice is Incl of Taxes</strong> @endif
                </div>
                <div class="col-md-6"><strong>Bill no</strong> : {{ $purchase->bill_no }}</div>
            </div>
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <strong>{{ $purchase->party->name }}</strong>
                </div>
                <div class="col-md-3">
                    <strong>Bill Date</strong> : <span id="validate-against">{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d/m/Y') }}</span>
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
                            <th>Revised Rate</th>
                            {{-- <th>Diff Rate</th> --}}
                            {{-- <th>Calculated Rate</th> --}}
                            <th>Revised GST</th>
                            {{-- <th>Diff GST</th> --}}
                            {{-- <th>Calculated GST</th> --}}
                            <th colspan="2">@if(auth()->user()->profile->inventory_type != "without_inventory") Revised Qty @endif</th>
                            {{-- <th>Diff Qty</th> --}}

                            {{-- <th>Revised Discount</th> --}}
                            {{-- <th>Diff Discount</th> --}}
                            {{-- <th>Calculated Qty</th> --}}
                            {{-- <th>Value</th> --}}
                            <th class="text-center" colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1; $items_price = 0; $items_gst = 0; $items_total = 0; $items_discount = 0; @endphp
                        @if(count($debit_notes) > 0)
                            @foreach($debit_notes as $debit_note)
                            <tr>
                                <td>{{ $count++ }}<input type="hidden" name="row_id[]" value="{{ $debit_note->id }}" disabled /></td>
                                <td>{{ $debit_note->item_name }}<input type="hidden" name="item_id[]" value="{{ $debit_note->item_id }}" disabled /></td>
                                
                                <td><input data-gst="{{ $purchase->party->registered == 0 || $purchase->party->registered == 3 ? 0 : $debit_note->item_gst ?? 0 }}" type="text" class="form-control" name="price_difference[]" value="{{ $debit_note->price ?? 0 }}" disabled /></td>
                                
                                
                                {{-- <td>{{ $purchase->price_difference }}</td> --}}
                                {{-- <td>{{ $purchase->price - $purchase->price_difference }}</td> --}}
                                
                                <td><input type="text" class="form-control" name="gst_difference[]" value="{{ $debit_note->gst ?? 0 }}" disabled /></td>
                                
                                
                                {{-- <td>{{ $purchase->gst_percent_difference }}</td> --}}
                                {{-- <td>{{ $purchase->gst - $purchase->gst_percent_difference }}</td> --}}
                                
                                
                                <td><input data-gst="{{ $purchase->party->registered == 0 || $purchase->party->registered == 3 ? 0 : $debit_note->item_gst ?? 0 }}" type="text" class="form-control" name="quantity_difference[]" value="{{ $debit_note->original_qty ?? 0 }}" @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif disabled /></td>
                                <td>
                                    {{ $debit_note->original_unit }}
                                </td>
                                
                                
                                {{-- <td>{{ $purchase->quantity_difference }}</td> --}}
                                {{-- <td>{{ $purchase->quantity - $purchase->quantity_difference }}</td> --}}


                                {{-- <td><input type="text" class="form-control" name="discount_difference[]" value="{{ $debit_note->discount ?? 0 }}" disabled /></td> --}}

                                {{-- <td><span class="row-value">{{ $debit_note->item_total }}</span></td> --}}

                                {{-- <td>{{ $purchase->discount_difference }}</td> --}}
                                
                                {{-- <td><button class="btn btn-link edit-note" data-bill_no="{{ $bill }}" data-item_id="{{ $purchase->item_id }}" data-price="{{ $purchase->price }}" data-gst="{{ $purchase->gst }}" data-qty="{{ $purchase->quantity }}" data-discount="{{ $purchase->discount }}">Edit Debit Note</button></td> --}}

                                {{-- <td class="text-center">
                                    <button class="btn btn-link edit-note" data-item_gst="{{ $purchase->item_gst }}" data-bill_no="{{ $bill }}" data-item_id="{{ $purchase->item_id }}" data-original_price="{{ $purchase->price }}" data-original_gst="{{ $purchase->gst }}" data-original_qty="{{ $purchase->quantity }}" data-original_discount="{{ $purchase->discount }}" data-price="{{ $purchase->price_difference ? $purchase->price_difference : $purchase->price }}" data-gst="{{ $purchase->gst_percent_difference ? $purchase->gst_percent_difference : $purchase->gst }}" data-qty="{{ $purchase->quantity_difference ? $purchase->quantity_difference : $purchase->quantity }}" data-discount="{{ $purchase->discount_difference ? $purchase->discount_difference : $purchase->discount }}">Edit</button>
                                </td> --}}
                                <td class="text-center">
                                    <button type="button" class="btn btn-link edit-row" style="color: blue;"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                </td>
                                <td class="text-center">
                                    {{-- <button type="button" class="btn btn-link delete-row" style="color: red;"><i class="fa fa-trash-o" aria-hidden="true"></i></button> --}}
                                    <form  method="post" action="{{ route('purchase.delete.debit.note') }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="row_id" value="{{ $debit_note->id }}" />
                                        <button class="btn btn-link" style="color: red;"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    </form>
                                </td>
                            </tr>
                            {{-- <tr style="padding-top: 0; padding-bottom: 0;">
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;"></td>
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;">
                                    <button class="btn btn-link edit-price" data-bill_no="{{ $bill }}" data-item_id="{{ $purchase->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-price="{{ $purchase->price_difference ? $purchase->price_difference : $purchase->price }}" data-qty="{{ $purchase->quantity_difference ? $purchase->quantity_difference : $purchase->quantity }}" data-gst="{{ $purchase->gst_percent_difference ? $purchase->gst_percent_difference : $purchase->gst }}">Edit Rate</button>
                                </td>
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;">
                                    <button class="btn btn-link edit-gst" data-bill_no="{{ $bill }}" data-item_id="{{ $purchase->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-gst="{{ $purchase->gst_percent_difference ? $purchase->gst_percent_difference : $purchase->gst }}" data-price="{{ $purchase->price_difference ? $purchase->price_difference : $purchase->price }}" data-qty="{{ $purchase->quantity_difference ? $purchase->quantity_difference : $purchase->quantity }}">Edit GST</button>
                                </td>
                                <td colspan="2" style="border-top: none; padding-top: 0; padding-bottom: 0;">
                                    <button class="btn btn-link edit-qty" data-bill_no="{{ $bill }}" data-item_id="{{ $purchase->item_id }}" data-original_price="{{ $sale->price }}" data-original_gst="{{ $sale->gst }}" data-original_qty="{{ $sale->quantity }}" data-qty="{{ $purchase->quantity_difference ? $purchase->quantity_difference : $purchase->quantity }}" data-gst="{{ $purchase->gst_percent_difference ? $purchase->gst_percent_difference : $purchase->gst }}" data-price="{{ $purchase->price_difference ? $purchase->price_difference : $purchase->price }}">Edit Quantity</button>
                                </td>
                            </tr> --}}
                                @php
                                    $price = $debit_note->price ?? 0;
                                    $gst = $debit_note->gst ?? 0;
                                    $qty = $debit_note->original_qty ?? 0;
                                    // $discount = $debit_note->discount ?? 0;

                                    // $calculated_discount = ($price * $qty) * $discount/100;
                                    
                                    if(auth()->user()->profile->inventory_type == "without_inventory"){
                                        $items_price += $price;
                                    }else{ 
                                        $items_price += $price * $qty;
                                    }
                                    // $items_discount += $calculated_discount;
                                    $items_gst += $gst;
                                    // $items_total += ((($price * $qty) - $calculated_discount)  + $gst);
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
                                    <option @if($debit_notes->first()->reason == 'purchase_return') selected="selected" @endif value="purchase_return">Purchase Return</option>
                                    <option @if($debit_notes->first()->reason == 'new_rate_or_discount_value_with_gst') selected="selected" @endif value="new_rate_or_discount_value_with_gst">New Rate or Discount Value with GST</option>
                                    <option @if($debit_notes->first()->reason == 'discount_on_purchase') selected="selected" @endif value="discount_on_purchase">Discount</option>
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
                    <form id="form-note" method="post" action="{{ route('purchase.create.or.update.debit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="purchase" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="bill_no" name="bill_no" placeholder="Bill No" readonly />
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
                            <label>GST Percent Difference</label>
                            <input type="text" class="form-control" id="gst_percent_difference" name="gst_percent_difference" placeholder="GST Percent Difference" />
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
                    <form id="form-note" method="post" action="{{ route('purchase.create.or.update.debit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="purchase" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="bill_no" name="bill_no" placeholder="Bill No" readonly />
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
                    <form id="form-note" method="post" action="{{ route('purchase.create.or.update.debit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="purchase" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="bill_no" name="bill_no" placeholder="Bill No" readonly />
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
                    <form id="form-note" method="post" action="{{ route('purchase.create.or.update.debit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" name="note_type" value="purchase" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="bill_no" name="bill_no" placeholder="Bill No" readonly />
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
                    <form id="form-note" method="post" action="{{ route('purchase.create.or.update.debit.note') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="item_id" name="item_id" value="" />
                        <input type="hidden" id="item_gst" value="" />
                        <input type="hidden" name="note_type" value="purchase" />
                        <div class="form-group">
                            <label>Bill No.</label>
                            <input type="text" class="form-control" id="bill_no" name="bill_no" placeholder="Bill No" readonly />
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
                                {{-- <select class="form-control" id="reason_change" name="reason_change" readonly>
                                    <option selected disabled>Please select a reason</option>
                                    <option value="discount_on_purchase">Discount on Purchase</option>
                                    <option value="discount_on_sale">Discount on Sale</option>
                                    <option value="sale_return">Sale Return</option>
                                    <option value="purchase_return">Purchase Return</option>
                                    <option value="other">Other</option>
                                </select> --}}
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
                    <form id="form-note" method="post" action="{{ route('update.purchase.debit.note.item') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="modal_row_id" value="" />
                        <div class="form-group">
                            <label>Revised Price</label>
                            <input type="text" class="form-control" name="modal_revised_price" value="" @if($debit_notes->first()->reason == 'purchase_return') readonly @endif />
                        </div>
                        <div class="form-group">
                            <label>Revised Gst</label>
                            <input type="text" class="form-control" name="modal_revised_gst" value="{{ $purchase->party->registered == 0 || $purchase->party->registered == 3 ? 0 : '' }}" 
                            @if($purchase->party->registered == 0 || $purchase->party->registered == 3) readonly @endif 
                            @if($debit_notes->first()->reason == 'discount_on_purchase') readonly value="0" @endif 
                            />
                        </div>
                        <div class="form-group" @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif>
                            <label @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif>Revised Qty</label>
                            <div class="form-group">
                                <input type="text" class="form-control" name="modal_revised_qty" value="{{ auth()->user()->profile->inventory_type == "without_inventory" ? 0 : '' }}" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly style="visibility: hidden" @endif />
                            </div>
                            <select name="modal_measuring_unit" class="form-control select-measuring-unit" @if(auth()->user()->profile->inventory_type == "without_inventory") readonly style="visibility:hidden" @endif>
                                <option @if($debit_note->original_unit == $debit_note->base_unit) selected="selected" @endif>{{ $debit_note->base_unit }}</option>
                                @if($debit_note->alternate_unit)
                                <option @if($debit_note->original_unit == $debit_note->alternate_unit) selected="selected" @endif>{{ $note->alternate_unit }}</option>
                                @endif
                                @if($debit_note->compound_unit)
                                <option @if($debit_note->original_unit == $debit_note->compound_unit) selected="selected" @endif>{{ $debit_note->compound_unit }}</option>
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
            //     var item_id = $(this).attr('data-item_id');
            //     var bill_no = $(this).attr('data-bill_no');
            //     var price = $(this).attr('data-price');
            //     var gst = $(this).attr('data-gst');
            //     var qty = $(this).attr('data-qty');
            //     var discount = $(this).attr('data-discount');

            //     $("#item_id").val(item_id);
            //     $("#bill_no").val(bill_no);
            //     $("#price").val(price);
            //     $("#gst").val(gst);
            //     $("#quantity").val(qty);
            //     $("#discount").val(discount);
            //     $("#edit-note-modal").modal('show');
            // });

            // $(".edit-price").on('click', function (){

            //     var item_id = $(this).attr('data-item_id');
            //     var bill_no = $(this).attr('data-bill_no');
            //     var price = $(this).attr('data-price');
            //     var gst = $(this).attr('data-gst');
            //     var qty = $(this).attr('data-qty');

            //     $('input[name="item_id"]').val(item_id);
            //     $('input[name="bill_no"]').val(bill_no);
            //     $("#price").val(price);
            //     $("#price_gst").val(gst);
            //     $("#price_qty").val(qty);

            //     var price_taxable_value = qty*price;
            //     var price_gst_value = gst;
            //     var price_note_value = parseFloat(price_taxable_value) + parseFloat(gst);

            //     $("#price_taxable_value").val(price_taxable_value);
            //     $("#price_gst_value").val(price_gst_value);
            //     $("#price_note_value").val(price_note_value);

            //     $("#edit-price-modal").modal('show');
            // });

            // $("#price_difference").on("keyup", function(){
            //     var price = $(this).val();
            //     var gst = $(".edit-price").attr("data-gst");
            //     var qty = $(".edit-price").attr("data-qty");

            //     if(price == ''){
            //         price = 0;
            //     }

            //     if(gst == ''){
            //         gst = 0;
            //     }

            //     if(qty == ''){
            //         qty = 0;
            //     }

            //     var price_taxable_value = qty*price;
            //     var price_gst_value = gst;
            //     var price_note_value = parseFloat(price_taxable_value) + parseFloat(gst);

            //     $("#price_taxable_value").val(price_taxable_value);
            //     $("#price_gst_value").val(price_gst_value);
            //     $("#price_note_value").val(price_note_value);
            // });

            // $(".edit-gst").on('click', function (){

            //     var item_id = $(this).attr('data-item_id');
            //     var bill_no = $(this).attr('data-bill_no');
            //     var gst = $(this).attr('data-gst');
            //     var price = $(this).attr('data-price');
            //     var qty = $(this).attr('data-qty');
                

            //     $('input[name="item_id"]').val(item_id);
            //     $('input[name="bill_no"]').val(bill_no);
            //     $("#gst").val(gst);
            //     $("#gst_price").val(price);
            //     $("#gst_qty").val(qty);

            //     var gst_taxable_value = qty * price;
            //     var gst_gst_value = gst;
            //     var gst_note_value = parseFloat(gst_taxable_value) + parseFloat(gst);

            //     $("#gst_taxable_value").val(gst_taxable_value);
            //     $("#gst_gst_value").val(gst_gst_value);
            //     $("#gst_note_value").val(gst_note_value);

            //     $("#edit-gst-modal").modal('show');
            // });

            // $(".edit-qty").on('click', function (){

            //     var item_id = $(this).attr('data-item_id');
            //     var bill_no = $(this).attr('data-bill_no');
            //     var qty = $(this).attr('data-qty');
            //     var gst = $(this).attr('data-gst');
            //     var price = $(this).attr('data-price');

            //     $('input[name="item_id"]').val(item_id);
            //     $('input[name="bill_no"]').val(bill_no);
            //     $("#quantity").val(qty);
            //     $("#qty_gst").val(gst);
            //     $("#qty_price").val(price);

            //     var qty_taxable_value = qty * price;
            //     var qty_gst_value = gst;
            //     var qty_note_value = parseFloat(qty_taxable_value) + parseFloat(gst);

            //     $("#qty_taxable_value").val(qty_taxable_value);
            //     $("#qty_gst_value").val(qty_gst_value);
            //     $("#qty_note_value").val(qty_note_value);

            //     $("#edit-qty-modal").modal('show');
            // });

            // $("#quantity_difference").on("keyup", function(){
            //     var qty = $(this).val();
            //     var gst = $('.edit-qty').attr('data-gst');
            //     var price = $('.edit-qty').attr('data-price');

            //     if(gst == ''){
            //         gst = 0;
            //     }

            //     if(price == ''){
            //         price = 0;
            //     }

            //     if(qty == ''){
            //         qty = 0;
            //     }

            //     var qty_taxable_value = qty * price;
            //     var qty_gst_value = gst;
            //     var qty_note_value = parseFloat(qty_taxable_value) + parseFloat(gst);

            //     $("#qty_taxable_value").val(qty_taxable_value);
            //     $("#qty_gst_value").val(qty_gst_value);
            //     $("#qty_note_value").val(qty_note_value);
            // });

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

            // $("#reason_quantity_change").on("change", function(){
            //     var selected_reason = $(this).val();

            //     if(selected_reason == 'other'){
            //         $("#reason_quantity_change_other").show();
            //     } else {
            //         $("#reason_quantity_change_other").show();
            //     }
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
                var qty = tr.find('input[name="quantity_difference[]"]').val() || 0;
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
                var bill_no = element.attr('data-bill_no');
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
                $("#bill_no").val(bill_no);
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
                $('input[name="discount_value"]').val(discount);
                $('input[name="gst_value"]').val(gst);
                $('input[name="note_value"]').val(note_value);
            }

            function calculateValue(tr) {

                var price = tr.find('input[name="price_difference[]"]').val() || 0;
                var gst = tr.find('input[name="gst_difference[]"]').val() || 0;
                var qty = tr.find('input[name="quantity_difference[]"]').val() || 0;
                var discount = tr.find('input[name="discount_difference[]"]').val() || 0;

                if(price == ''){
                    price = 0;
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
                    @if(auth()->user()->profile->inventory_type == "without_inventory")
                    totalPrice += parseFloat(price[i]);
                    @else
                    totalPrice += (price[i] * qty[i]);
                    @endif
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


            // $('#note_reason_change').on("change", function() {
            //     console.log($(this).val());
            //     if( $(this).val() == "discount_on_purchase" ){
            //         localStorage.setItem("gst", $('input[name="gst_difference[]"]').val());
            //         $('input[name="gst_difference[]"]').val(0).attr("disabled", true).trigger('keyup');
            //     } else {
            //         if( localStorage.getItem("gst") !== null ){
            //             $('input[name="gst_difference[]"]').val( localStorage.getItem("gst") ).attr("disabled", false);
            //             localStorage.removeItem("gst");
            //         }
            //     }
            // });

            $('#note_reason_change').on("change", function() {
                console.log($(this).val());
                const reasonChange = [];
                if( $(this).val() == "discount_on_purchase" ){
                    $('input[name="modal_revised_price"]').attr("readonly", false);
                    localStorage.setItem("discount_on_purchase", "true");
                    $('input[name="gst_difference[]"]').each(function(){
                        reasonChange.push($(this).val());
                    });
                    localStorage.setItem("revised_gst", reasonChange);
                    // console.log(reasonChange);
                    $('input[name="gst_difference[]"]').val(0).trigger('keyup');
                    $('input[name="modal_revised_gst"]').attr("disabled", true);
                } 
                else if( $(this).val() == "purchase_return" ) {
                    $('input[name="modal_revised_price"]').attr("readonly", true);
                    checkDiscountOnPurchaseLocalStorageIsSet();
                } else {
                    $('input[name="modal_revised_price"]').attr("readonly", false);
                    checkDiscountOnPurchaseLocalStorageIsSet();
                }
            });

            function checkDiscountOnPurchaseLocalStorageIsSet() {
                if( localStorage.getItem("discount_on_purchase") === "true" ){
                    $("table > tbody > tr").each(function () {
                        var price = $(this).find('input[name="price_difference[]"]').val();
                        var gst_percent = $(this).find('input[name="price_difference[]"]').attr('data-gst');
                        var inventory_type = $(this).find('input[name="price_difference[]"]').attr('data-inventory_type');


                        if(inventory_type == "without_inventory") {
                            var qty = 1;
                        } else {
                            var qty = $(this).find('input[name="quantity_difference[]"]').val();
                        }

                        var amount = price * qty;

                        var gst = amount * gst_percent / 100;

                        gst = noRoundOff(gst);


                        // console.log("price", price);
                        // console.log("gst%", gst_percent);
                        // console.log("inventory", inventory_type);
                        // console.log("amount", amount);
                        // console.log("gst", gst);

                        $(this).find('input[name="gst_difference[]"]').attr("disabled", false);
                        $(this).find('input[name="gst_difference[]"]').val(gst);
                        $(this).find('input[name="price_difference[]"]').trigger('keyup');
                    });
                    localStorage.removeItem("discount_on_purchase");
                    localStorage.removeItem("revised_gst");
                }
            }

            $("#note_date").on("keyup", function() {
                var validate_against = $("#validate-against").text();
                var validate_date = $(this).val();
                validateTwoDates(validate_against, validate_date, "#", "update_note", "#", "note_date_validation_error");
            });

        });
    </script>
@endsection