@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('create-item') !!}
<div class="container">
    {{-- <div class="row">
        <div class="col-md-12">
            <a href="{{ route('item.index') }}">View All Items</a>&nbsp;&nbsp;
            <a href="{{ route('item.create') }}">Create New Item</a>&nbsp;&nbsp;
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">Add New Item</div>

                <div class="panel-body">
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

                    <form class="form-horizontal" method="POST" action="{{ route('item.store') }}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Type</label>
                            </div>
                            <div class="col-md-6">
                                {{-- <label class="radio-inline">
                                    <input type="radio" name="type_of_service" id="type_of_service1" value="service"> Service
                                </label> --}}
                                <label class="radio-inline">
                                    <input type="radio" name="type_of_service" id="type_of_service2" value="physical" checked> Goods/Service
                                </label>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('group') ? ' has-error' : '' }}">
                            <label for="group" class="col-md-4 control-label">Select Group</label>

                            <div class="col-md-6">
                                <select class="form-control" name="group" required>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('group'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('group') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Item Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                                <p id="name_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div>

                        <div id="category_block" class="form-group{{ $errors->has('category') ? ' has-error' : '' }}">
                            <label for="category" class="col-md-4 control-label">Item Category</label>

                            <div class="col-md-6">
                                <select class="form-control" name="category">
                                    <option value="finished">Finished</option>
                                    <option value="raw">Raw</option>
                                </select>

                                @if ($errors->has('category'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('category') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="hsc_code_block" class="form-group{{ $errors->has('hsc_code') ? ' has-error' : '' }}">
                            <label for="hsc_code" class="col-md-4 control-label">HSN Code</label>

                            <div class="col-md-6">
                                <input id="hsc_code" type="text" class="form-control" name="hsc_code" value="{{ old('hsc_code') }}" >

                                @if ($errors->has('hsc_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('hsc_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="sac_code_block" class="form-group{{ $errors->has('sac_code') ? ' has-error' : '' }}" style="display: none;">
                            <label for="sac_code" class="col-md-4 control-label">SAC Code</label>

                            <div class="col-md-6">
                                <input id="sac_code" type="text" class="form-control" name="sac_code" value="{{ old('sac_code') }}" >

                                @if ($errors->has('sac_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sac_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('igst') ? ' has-error' : '' }}">
                            <label for="igst" class="col-md-4 control-label">IGST</label>

                            <div class="col-md-6">
                                <input id="igst" type="text" class="form-control" name="igst" value="{{ old('igst') }}" >

                                @if ($errors->has('igst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('igst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        {{-- <div class="form-group{{ $errors->has('cgst') ? ' has-error' : '' }}">
                            <label for="cgst" class="col-md-4 control-label">CGST</label>

                            <div class="col-md-6">
                                <input id="cgst" type="text" class="form-control" name="cgst" value="{{ old('cgst') }}" >

                                @if ($errors->has('cgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('cgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('sgst') ? ' has-error' : '' }}">
                            <label for="sgst" class="col-md-4 control-label">SGST</label>

                            <div class="col-md-6">
                                <input id="sgst" type="text" class="form-control" name="sgst" value="{{ old('sgst') }}" >

                                @if ($errors->has('sgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}
                        <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST</label>

                            <div class="col-md-6">
                                {{-- <input id="gst" type="text" class="form-control" name="gst" value="{{ old('gst') }}" > --}}
                                <select class="form-control" name="gst" id="gst">
                                    <option disabled selected>Select GST</option>
                                    {{-- <option value="0">0</option>
                                    <option value="0.5">0.5</option>
                                    <option value="1">1</option>
                                    <option value="3">3</option>
                                    <option value="5">5</option>
                                    <option value="12">12</option>
                                    <option value="18">18</option>
                                    <option value="28">28</option>
                                    <option value="EXEMPT">EXEMPT</option>
                                    <option value="NIL">NIL</option>
                                    <option value="EXPORT">EXPORT</option> --}}
                                    @foreach( $gsts as $gst )
                                        <option value="{{ $gst->value }}">{{ $gst->name }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="cess_block" class="form-group{{ $errors->has('cess') ? ' has-error' : '' }}">
                            <label for="cess" class="col-md-4 control-label">Cess</label>

                            <div class="col-md-6">
                                <input id="cess" type="text" class="form-control" name="cess" value="{{ old('cess') }}" >

                                @if ($errors->has('cess'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('cess') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group show_additional_info_block">
                            <div class="col-md-offset-4 col-md-6">
                                <p style="padding: 0; margin: 0; line-height: 1;"><label>Show Additional Info</label></p>
                                <label class="radio-inline">
                                    <input type="radio" name="show_additional_option" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="show_additional_option" value="no" checked> No
                                </label>
                            </div>
                        </div>

                        <div id="additional-option-block" style="display: none;">

                            <div id="manufacture_block" class="form-group{{ $errors->has('manufacture') ? ' has-error' : '' }}">
                                <label for="manufacture" class="col-md-4 control-label">Manufacture</label>

                                <div class="col-md-6">
                                    <input id="manufacture" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="manufacture" value="{{ old('manufacture') }}" >

                                    @if ($errors->has('manufacture'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('manufacture') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div id="expiry_block" class="form-group{{ $errors->has('expiry') ? ' has-error' : '' }}">
                                <label for="expiry" class="col-md-4 control-label">Expiry</label>

                                <div class="col-md-6">
                                    <input id="expiry" type="text" class="form-control custom_date" name="expiry" placeholder="DD/MM/YYYY" value="{{ old('expiry') }}" >

                                    @if ($errors->has('expiry'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('expiry') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div id="batch_block" class="form-group{{ $errors->has('batch') ? ' has-error' : '' }}">
                                <label for="batch" class="col-md-4 control-label">Batch</label>

                                <div class="col-md-6">
                                    <input id="batch" type="text" class="form-control" name="batch" value="{{ old('batch') }}" >

                                    @if ($errors->has('batch'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('batch') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div id="size_block" class="form-group{{ $errors->has('size') ? ' has-error' : '' }}">
                                <label for="size" class="col-md-4 control-label">Size</label>

                                <div class="col-md-6">
                                    <input id="size" type="text" class="form-control" name="size" value="{{ old('size') }}" >

                                    @if ($errors->has('size'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('size') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- <div class="form-group{{ $errors->has('mrp') ? ' has-error' : '' }} opening_stock_block">
                                <label for="mrp" class="col-md-4 control-label">Listing/MRP</label>

                                <div class="col-md-6">
                                    <input id="mrp" type="text" class="form-control" name="mrp" value="{{ old('mrp') }}" >

                                    @if ($errors->has('mrp'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('mrp') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div> --}}

                            <div class="form-group{{ $errors->has('purchase_price') ? ' has-error' : '' }} opening_stock_block">
                                <label for="purchase_price" class="col-md-4 control-label">Purchase Price</label>

                                <div class="col-md-6">
                                    <input id="purchase_price" type="text" class="form-control" name="purchase_price" value="{{ old('purchase_price') }}" >

                                    @if ($errors->has('purchase_price'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('purchase_price') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('sale_price') ? ' has-error' : '' }} opening_stock_block">
                                <label for="sale_price" class="col-md-4 control-label">Sale Price</label>

                                <div class="col-md-6">
                                    <input id="sale_price" type="text" class="form-control" name="sale_price" value="{{ old('sale_price') }}" >

                                    @if ($errors->has('sale_price'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('sale_price') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- <div class="form-group{{ $errors->has('barcode') ? ' has-error' : '' }} opening_stock_block">
                                <label for="barcode" class="col-md-4 control-label">Barcode</label>

                                <div class="col-md-6">
                                    <input id="barcode" type="text" class="form-control" name="barcode" value="{{ old('barcode') }}" >

                                    @if ($errors->has('barcode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('barcode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div> --}}

                            <div class="form-group">
                                <div class="col-md-4 control-label">
                                    <label>Free Qty</label>
                                </div>
                                <div class="col-md-6">
                                    <label class="radio-inline">
                                        <input type="radio" name="free_qty" id="free_qty_yes" value="yes"> Yes
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="free_qty" id="free_qty_no" value="no" checked> No
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Under RCM</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="item_under_rcm" id="item_under_rcm1" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="item_under_rcm" id="item_under_rcm2" value="no" checked> No
                                </label>
                            </div>
                        </div>

                        {{-- <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Pieces?</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="has_pieces" id="has_pieces1" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_pieces" id="has_pieces2" value="no" checked> No
                                </label>
                            </div>
                        </div> --}}

                        {{-- <div id="pieces_block" class="form-group{{ $errors->has('pieces') ? ' has-error' : '' }}" style="display: none;">
                            <label for="pieces" class="col-md-4 control-label">Pieces Count</label>

                            <div class="col-md-6">
                                <input id="pieces" type="text" class="form-control" name="pieces" value="{{ old('pieces') }}" >

                                @if ($errors->has('pieces'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('pieces') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                        <div id="measuring_unit_block" class="form-group{{ $errors->has('measuring_unit') ? ' has-error' : '' }}">
                            <label for="measuring_unit" class="col-md-4 control-label">Measuring Unit (UQC)</label>

                            <div class="col-md-6">
                                <select id="measuring_unit" class="form-control" name="measuring_unit" required="required">
                                    <option value="" selected disabled>Select Measuring Unit</option>
                                    @foreach($measuring_units as $measuring_unit)
                                        <option value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                    @endforeach
                                </select>
                                {{-- <button type="button" class="btn btn-link" id="add_new_measuring_unit">Add New</button> --}}

                                @if ($errors->has('measuring_unit'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('measuring_unit') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('measuring_unit_short_name') ? ' has-error' : '' }}">
                            <label for="measuring_unit_short_name" class="col-md-4 control-label">Short Name</label>

                            <div class="col-md-6">
                                <input type="text" name="measuring_unit_short_name" class="form-control" id="measuring_unit_short_name" />

                                @if($errors->has('measuring_unit_short_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('measuring_unit_short_name') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div> --}}

                        <div class="form-group{{ $errors->has('measuring_unit_decimal_place') ? ' has-error' : '' }}">
                            <label for="measuring_unit_decimal_place" class="col-md-4 control-label">Decimal Place</label>

                            <div class="col-md-6">
                                <input type="text" name="measuring_unit_decimal_place" class="form-control" id="measuring_unit_decimal_place" />

                                @if($errors->has('measuring_unit_decimal_place'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('measuring_unit_decimal_place') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>

                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Alternate Unit?</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="has_alternate_unit" id="has_alternate_unit1" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_alternate_unit" id="has_alternate_unit2" value="no" checked> No
                                </label>
                            </div>
                        </div>

                        <div id="alternate_unit_block" style="display: none;">
                            {{-- <div class="form-group">
                                <label for="basic_measuring_unit" class="col-md-4 control-label">Basic Measuring Unit</label>

                                <div class="col-md-6">
                                    <select id="basic_measuring_unit" class="form-control" name="basic_measuring_unit" >
                                        @foreach($measuring_units as $measuring_unit)
                                            <option value="{{ $measuring_unit->name }}">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="basic_unit_decimal_place" class="col-md-4 control-label">Decimal Place</label>
                                <div class="col-md-6">
                                    <input type="text" name="basic_unit_decimal_place" class="form-control" />
                                </div>
                            </div> --}}

                            <div class="form-group">
                                <label for="alternate_measuring_unit" class="col-md-4 control-label">Alternative Unit</label>

                                <div class="col-md-6">
                                    <select id="alternate_measuring_unit" class="form-control" name="alternate_measuring_unit" >
                                        <option value="" selected disabled>Select Alternate Unit</option>
                                        @foreach($measuring_units as $measuring_unit)
                                            <option value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            {{-- <div class="form-group{{ $errors->has('alternate_unit_short_name') ? ' has-error' : '' }}">
                                <label for="alternate_unit_short_name" class="col-md-4 control-label">Short Name</label>

                                <div class="col-md-6">
                                    <input type="text" name="alternate_unit_short_name" class="form-control" id="alternate_unit_short_name" />

                                    @if($errors->has('alternate_unit_short_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('alternate_unit_short_name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                            </div> --}}

                            <div class="form-group">
                                <label for="alternate_unit_decimal_place" class="col-md-4 control-label">Decimal Place</label>
                                <div class="col-md-6">
                                    <input type="text" name="alternate_unit_decimal_place" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">
                                    <div class="row">
                                        <div class="col-md-5" style="padding-right: 2.5px;">
                                            <input type="text" class="form-control" name="alternate_unit_input" value="1" />
                                        </div>
                                        <div class="col-md-7" style="text-align: left; padding-left: 2.5px;">
                                            <span id="selected_alternate_unit">BAG (BAGS)</span> =
                                        </div>
                                    </div>
                                </label>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="conversion_of_alternate_to_base_unit_value" />
                                </div>
                                <label class="col-md-3 control-label" style="text-align: left"><span id="selected_base_unit">BAG (BAGS)</span></label>
                            </div>


                        </div>

                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Compound Unit?</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="has_compound_unit" id="has_compound_unit1" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_compound_unit" id="has_compound_unit2" value="no" checked> No
                                </label>
                            </div>
                        </div>

                        <div id="compound_unit_block" style="display: none;">
                            {{-- <div class="form-group">
                                <label for="compound_alternate_measuring_unit" class="col-md-4 control-label">Alternative Unit</label>

                                <div class="col-md-6">
                                    <select id="compound_alternate_measuring_unit" class="form-control" name="compound_alternate_measuring_unit" >
                                        @foreach($measuring_units as $measuring_unit)
                                            <option value="{{ $measuring_unit->name }}">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div> --}}

                            <div class="form-group">
                                <label for="compound_measuring_unit" class="col-md-4 control-label">Compound Unit</label>

                                <div class="col-md-6">
                                    <select id="compound_measuring_unit" class="form-control" name="compound_measuring_unit" >
                                        <option value="" selected disabled>Select Compound Unit</option>
                                        @foreach($measuring_units as $measuring_unit)
                                            <option value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            {{-- <div class="form-group{{ $errors->has('compound_unit_short_name') ? ' has-error' : '' }}">
                                <label for="compound_unit_short_name" class="col-md-4 control-label">Short Name</label>

                                <div class="col-md-6">
                                    <input type="text" name="compound_unit_short_name" class="form-control" id="compound_unit_short_name" />

                                    @if($errors->has('compound_unit_short_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('compound_unit_short_name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                            </div> --}}

                            <div class="form-group">
                                <label for="compound_unit_decimal_place" class="col-md-4 control-label">Decimal Place</label>
                                <div class="col-md-6">
                                    <input type="text" name="compound_unit_decimal_place" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">
                                    <div class="row">
                                        <div class="col-md-5" style="padding-right: 2.5px;">
                                            <input type="text" class="form-control" name="compound_unit_input" value="1" />
                                        </div>
                                        <div class="col-md-7" style="text-align: left; padding-left: 2.5px;">
                                            <span id="selected_compound_unit">BAG (BAGS)</span> =
                                        </div>
                                    </div>
                                </label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="conversion_of_compound_to_alternate_unit_value" />
                                </div>
                                <label class="col-md-3 control-label" style="text-align: left"><span id="selected_alternate_unit_for_compound">BAG (BAGS)</span></label>
                            </div>


                        </div>
                        @endif
                        <div class="form-group{{ $errors->has('opening_stock_date') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_date" class="col-md-4 control-label">Opening Stock Date</label>

                            <div class="col-md-6">
                                <input id="opening_stock_date" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="opening_stock_date" value="{{ old('opening_stock_date') }}" >

                                @if ($errors->has('opening_stock_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_date') }}</strong>
                                    </span>
                                @endif
                                <p id="date_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div>
                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                        <div class="form-group{{ $errors->has('opening_stock') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock" class="col-md-4 control-label">Opening Stock Qty</label>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-8" style="padding-right: 0;">
                                        <input id="opening_stock" type="text" class="form-control" name="opening_stock" value="{{ old('opening_stock') }}" >
                                    </div>
                                    <div class="col-xs-4" style="padding-left: 0;">
                                        <select class="form-control" id="opening_stock_unit" name="opening_stock_unit">
                                            <option selected disabled>Select Unit</option>
                                        </select>
                                    </div>
                                </div>
                                <p id="base_value_converted"></p>
                                @if ($errors->has('opening_stock'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_stock_rate') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_rate" class="col-md-4 control-label">Opening Stock Rate</label>
                            
                            <div class="col-md-6">
                                <input id="opening_stock_rate" type="text" class="form-control" name="opening_stock_rate" value="{{ old('opening_stock_rate') }}" >

                                @if ($errors->has('opening_stock_rate'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_rate') }}</strong>
                                    </span>
                                @endif
                                <p id="base_rate_converted"></p>
                            </div>
                        </div>
                        @endif

                        <div class="form-group{{ $errors->has('opening_stock_rate') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_value" class="col-md-4 control-label">Opening Stock Value</label>

                            <div class="col-md-6">
                                <input id="opening_stock_value" type="text" class="form-control" name="opening_stock_value" value="{{ old('opening_stock_value') }}" >

                                @if ($errors->has('opening_stock_value'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_value') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine add-item">
                                    Add Item
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal" id="add_new_measuring_unit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Measuring Unit</h4>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('measuringunit.store') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>Measuring Unit</label>
                        <input type="text" class="form-control" id="measuring_unit" name="name" placeholder="Measuring Unit" />
                    </div>
                    <button type="submit" class="btn btn-success btn-mine">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div> --}}

@endsection


@section('scripts')
    <script>
        $.noConflict();

        $("#opening_stock_date").on("keyup", function() {
            var date = $(this).val();

            validateDate(date, "date_validation_error", "#", "add-item", ".");
        });

        $('input[name="type_of_service"]').on('change', function(){
            var type_of_service = $(this).val();
            // alert(type_of_service);

            if(type_of_service == 'service'){
                $("#category_block").hide();
                $("#hsc_code_block").hide();
                $("#cess_block").hide();
                $("#measuring_unit_block").hide();
                $(".opening_stock_block").hide();
                $(".show_additional_info_block").hide();
                
                $("#sac_code_block").show();
            }

            if(type_of_service == 'physical'){
                $("#category_block").show();
                $("#hsc_code_block").show();
                $("#cess_block").show();
                $("#measuring_unit_block").show();
                $(".opening_stock_block").show();
                $(".show_additional_info_block").show();

                $("#sac_code_block").hide();
            }
        });

        $("#add_new_measuring_unit").on("click", function () {
            $("#add_new_measuring_unit_modal").modal('show');
        });

        $('input[name="has_pieces"]').on("change", function(){

            var has_pieces = $(this).val();

            if( has_pieces == 'yes' ) {
                $("#pieces_block").show();
            } else {
                $("#pieces_block").hide();
            }
        });

        $('input[name="has_alternate_unit"]').on("change", function(){

            var has_alternate_unit = $(this).val();

            if( has_alternate_unit == 'yes' ) {
                $("#alternate_unit_block").show();
            } else {
                $("#alternate_unit_block").hide();
            }
        });

        $('input[name="has_compound_unit"]').on("change", function(){

            var has_compound_unit = $(this).val();

            if( has_compound_unit == 'yes' ) {
                $("#compound_unit_block").show();
            } else {
                $("#compound_unit_block").hide();
            }
        });


        $('input[name="show_additional_option"]').on("change", function() {
            if( $(this).val() == 'yes' ){
                $("#additional-option-block").show();
            } else {
                $("#additional-option-block").hide();
            }
        });

        $('#opening_stock').on("keyup", function() {

            var opening_stock_qty = $(this).val();
            var opening_stock_rate = $('#opening_stock_rate').val();

            if(opening_stock_qty == ''){
                opening_stock_qty = 0;
            }

            if(opening_stock_rate == ''){
                opening_stock_rate = 0;
            }

            $("#opening_stock_value").val(opening_stock_qty * opening_stock_rate);

        });

        $('#opening_stock_rate').on("keyup", function() {

            var opening_stock_rate  = $(this).val();
            var opening_stock_qty = $('#opening_stock').val();

            if(opening_stock_qty == ''){
                opening_stock_qty = 0;
            }

            if(opening_stock_rate == ''){
                opening_stock_rate = 0;
            }

            $("#opening_stock_value").val(opening_stock_qty * opening_stock_rate);

        });

        $(document).on("change", "#measuring_unit", function () {
            // var measuring_unit = $(this).val();
            // $("#selected_base_unit").text( measuring_unit );
            // $("#opening_stock_unit").append(`<option value="${measuring_unit}">${measuring_unit}</option>`);
            appendMeasuringUnitsToOpeningStock(); 
        });

        $(document).on("change", "#alternate_measuring_unit", function() {
            // var alternate_measuring_unit = $("#alternate_measuring_unit option:selected").val();
            // $("#selected_alternate_unit").text( alternate_measuring_unit );
            // $("#selected_alternate_unit_for_compound").text( alternate_measuring_unit );
            // $("#opening_stock_unit").append(`<option value="${alternate_measuring_unit}">${alternate_measuring_unit}</option>`);
            appendMeasuringUnitsToOpeningStock();
        });

        $('input[name="has_alternate_unit"]').on("change", function() {
            var alternate_measuring_unit = $("#alternate_measuring_unit option:selected").val();
            if($('input[name="has_alternate_unit"]:checked').val() == "no"){
                $("#selected_alternate_unit").text( "BAG (BAGS)" );
                $("#selected_alternate_unit_for_compound").text( "BAG (BAGS)" );
                $(`#opening_stock_unit option[value="${alternate_measuring_unit}"]`).remove();
            }
        });

        $(document).on("change", "#compound_measuring_unit", function() {
            // var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
            // $("#selected_compound_unit").text(compound_measuring_unit);
            // // $("#selected_alternate_unit_for_compound").text( $("#alternate_measuring_unit").val() );
            // $("#opening_stock_unit").append(`<option value="${compound_measuring_unit}">${compound_measuring_unit}</option>`);
            appendMeasuringUnitsToOpeningStock();
        });

        $('input[name="has_compound_unit"]').on("change", function() {
            var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
            if($('input[name="has_compound_unit"]:checked').val() == "no"){
                $("#selected_compound_unit").text("BAG (BAGS)");
                $(`#opening_stock_unit option[value="${compound_measuring_unit}"]`).remove();
            }
        });

        function appendMeasuringUnitsToOpeningStock() {
            var measuring_unit = $("#measuring_unit option:selected").val();
            var alternate_measuring_unit = $("#alternate_measuring_unit option:selected").val();
            var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
            $("#opening_stock_unit").html("");
            $("#selected_base_unit").text( measuring_unit );
            $("#selected_alternate_unit").text( alternate_measuring_unit );
            $("#selected_alternate_unit_for_compound").text( alternate_measuring_unit );
            $("#selected_compound_unit").text(compound_measuring_unit);
            $("#opening_stock_unit").append(`<option value="${measuring_unit}">${measuring_unit}</option>`);
            if(alternate_measuring_unit != ''){
                $("#opening_stock_unit").append(`<option value="${alternate_measuring_unit}">${alternate_measuring_unit}</option>`);
            }
            if(compound_measuring_unit != '') {
                $("#opening_stock_unit").append(`<option value="${compound_measuring_unit}">${compound_measuring_unit}</option>`);
            }
        }


        $("#name").on("keyup", function() {
            const url = "{{ route('validate.item.name') }}";
            let name = $(this).val();
            validateIfNameUnique(url, name, ".", "add-item", "#", "name_validation_error");
        });

        $(document).on("change", "#opening_stock_unit", function() {
            // calculated_base_value();
            calculated_base_rate();
        });

        $(document).on("keyup", "#opening_stock", function() {
            // calculated_base_value();
            calculated_base_rate();
        });

        $(document).on("keyup", "#opening_stock_rate", function() {
            calculated_base_rate();
        });

        // function calculated_base_value() {
        //     var measuring_unit = $("#measuring_unit option:selected").val();
        //     var alternate_measuring_unit = $("#alternate_measuring_unit option:selected").val();
        //     var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
        //     var selectedVal = $("#opening_stock_unit option:selected").val();
        //     var stock = $("#opening_stock").val();

        //     if(selectedVal == measuring_unit) {
        //         $("#base_value_converted").html(`<span>${stock} ${measuring_unit}</span>`);
        //     }
        //     else if(selectedVal == alternate_measuring_unit) {
        //         var alternate_unit_input = $('input[name="alternate_unit_input"]').val();
        //         var conversion_of_alternate_to_base_unit_value = $('input[name="conversion_of_alternate_to_base_unit_value"]').val();

        //         // value can be 5 alt_unit = 10 base_unit
        //         // so getting 1 alt_unit = ? base_unit using below
        //         var single_alt_value = Math.floor(conversion_of_alternate_to_base_unit_value / alternate_unit_input);

        //         // calculating base value
        //         var calculated_value = stock * single_alt_value;

        //         $("#base_value_converted").html(`<span>${calculated_value} ${measuring_unit}</span>`);

        //     }
        //     else if(selectedVal == compound_measuring_unit) {
        //         var alternate_unit_input = $('input[name="alternate_unit_input"]').val();
        //         var conversion_of_alternate_to_base_unit_value = $('input[name="conversion_of_alternate_to_base_unit_value"]').val();
        //         var compound_unit_input = $('input[name="compound_unit_input"]').val();
        //         var conversion_of_compound_to_alternate_unit_value = $('input[name="conversion_of_compound_to_alternate_unit_value"]').val();

        //         // value can be 5 alt_unit = 10 base_unit
        //         // so getting 1 alt_unit = ? base_unit using below
        //         var single_alt_value = Math.floor(conversion_of_alternate_to_base_unit_value / alternate_unit_input);

        //         // value can be 5 comp_unit = 10 alt_unit
        //         // so getting 1 comp_unit = ? alt_unit using below
        //         var single_comp_value = Math.floor(conversion_of_compound_to_alternate_unit_value / compound_unit_input);

        //         // calculating base value
        //         var calculated_value = stock * single_alt_value * single_comp_value;

        //         $("#base_value_converted").html(`<span>${calculated_value} ${measuring_unit}</span>`);
        //     }
        // }

        function calculated_base_rate() {
            var measuring_unit = $("#measuring_unit option:selected").val();
            var alternate_measuring_unit = $("#alternate_measuring_unit option:selected").val();
            var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
            var selectedVal = $("#opening_stock_unit option:selected").val();
            var stock = $("#opening_stock").val();
            var rate = $("#opening_stock_rate").val() || 0;
            var value = $("#opening_stock_value").val();

            if(selectedVal == measuring_unit) {
                $("#base_value_converted").html(`<span>${stock} ${measuring_unit}</span>`);
                $("#base_rate_converted").html(`<span>Rs ${rate} per ${measuring_unit}</span>`);
            }
            else if(selectedVal == alternate_measuring_unit) {
                var alternate_unit_input = $('input[name="alternate_unit_input"]').val();
                var conversion_of_alternate_to_base_unit_value = $('input[name="conversion_of_alternate_to_base_unit_value"]').val();

                // value can be 5 alt_unit = 10 base_unit
                // so getting 1 alt_unit = ? base_unit using below
                var single_alt_value = Math.floor(conversion_of_alternate_to_base_unit_value / alternate_unit_input);

                // calculating base value
                var calculated_value = stock * single_alt_value;


                var rate_per_basic_unit = value / calculated_value;

                $("#base_value_converted").html(`<span>${calculated_value} ${measuring_unit}</span>`);
                $("#base_rate_converted").html(`<span>Rs ${rate_per_basic_unit} per ${measuring_unit}</span>`);
            }
            else if(selectedVal == compound_measuring_unit) {
                var alternate_unit_input = $('input[name="alternate_unit_input"]').val();
                var conversion_of_alternate_to_base_unit_value = $('input[name="conversion_of_alternate_to_base_unit_value"]').val();
                var compound_unit_input = $('input[name="compound_unit_input"]').val();
                var conversion_of_compound_to_alternate_unit_value = $('input[name="conversion_of_compound_to_alternate_unit_value"]').val();

                // value can be 5 alt_unit = 10 base_unit
                // so getting 1 alt_unit = ? base_unit using below
                var single_alt_value = Math.floor(conversion_of_alternate_to_base_unit_value / alternate_unit_input);

                // value can be 5 comp_unit = 10 alt_unit
                // so getting 1 comp_unit = ? alt_unit using below
                var single_comp_value = Math.floor(conversion_of_compound_to_alternate_unit_value / compound_unit_input);

                // calculating base value
                var calculated_value = stock * single_alt_value * single_comp_value;


                var rate_per_basic_unit = value / calculated_value;

                $("#base_value_converted").html(`<span>${calculated_value} ${measuring_unit}</span>`);
                $("#base_rate_converted").html(`<span>Rs ${rate_per_basic_unit} per ${measuring_unit}</span>`);
            }
        }

    </script>
@endsection

