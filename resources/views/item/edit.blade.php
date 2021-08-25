{{-- @extends('layouts.dashboard')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Edit Item</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('item.update', $item->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Type</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="type_of_service" id="type_of_service1" @if($item->type == "service") checked @endif value="service"> Service
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="type_of_service" id="type_of_service2" @if($item->type == "physical") checked @endif value="physical" checked> Goods
                                </label>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('group') ? ' has-error' : '' }}">
                            <label for="group" class="col-md-4 control-label">Select Group</label>

                            <div class="col-md-6">
                                <select class="form-control" name="group" required>
                                    @foreach($groups as $group)
                                        <option @if($item->group_id == $group->id) {{ 'selected="selected"' }} @endif value="{{ $group->id }}">{{ $group->name }}</option>
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
                                <input id="name" type="text" class="form-control" name="name" value="{{ $item->name }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="category_block" class="form-group{{ $errors->has('category') ? ' has-error' : '' }}">
                            <label for="category" class="col-md-4 control-label">Item Category</label>

                            <div class="col-md-6">
                                <select class="form-control" name="category">
                                    <option @if( $item->category == "finished" ) selected="selected" @endif value="finished">Finished</option>
                                    <option @if( $item->category == "raw" ) selected="selected" @endif value="raw">Raw</option>
                                </select>

                                @if ($errors->has('category'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('category') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="hsc_code_block" class="form-group{{ $errors->has('hsc_code') ? ' has-error' : '' }}" @if($item->type == "service") style="display: none;" @endif>
                            <label for="hsc_code" class="col-md-4 control-label">HSN Code</label>

                            <div class="col-md-6">
                                <input id="hsc_code" type="text" class="form-control" name="hsc_code" value="{{ $item->hsc_code }}" >

                                @if ($errors->has('hsc_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('hsc_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="sac_code_block" class="form-group{{ $errors->has('sac_code') ? ' has-error' : '' }}"  @if($item->type == "physical") style="display: none;" @endif>
                            <label for="sac_code" class="col-md-4 control-label">SAC Code</label>

                            <div class="col-md-6">
                                <input id="sac_code" type="text" class="form-control" name="sac_code" value="{{ $item->sac_code }}" >

                                @if ($errors->has('sac_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sac_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST</label>

                            <div class="col-md-6">
                                <select class="form-control" name="gst" id="gst">
                                    <option disabled selected>Select GST</option>
                                    @foreach( $gsts as $gst )
                                        <option @if($item->gst == $gst->value) selected="selected" @endif value="{{ $gst->value }}">{{ $gst->name }}</option>
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
                                <input id="cess" type="text" class="form-control" name="cess" value="{{ $item->cess }}" >

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
                                <input id="manufacture" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="manufacture" @if($item->manufacture != null) value="{{ \Carbon\Carbon::parse($item->manufacture)->format('d/m/Y') }}" @endif >

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
                                <input id="expiry" type="text" class="form-control custom_date" name="expiry" placeholder="DD/MM/YYYY" @if($item->expiry != null) value="{{ \Carbon\Carbon::parse($item->expiry)->format('d/m/Y') }}" @endif >

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
                                <input id="batch" type="text" class="form-control" name="batch" value="{{ $item->batch }}" >

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
                                <input id="size" type="text" class="form-control" name="size" value="{{ $item->size }}" >

                                @if ($errors->has('size'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('size') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('mrp') ? ' has-error' : '' }} opening_stock_block">
                            <label for="mrp" class="col-md-4 control-label">Listing/MRP</label>

                            <div class="col-md-6">
                                <input id="mrp" type="text" class="form-control num-only" name="mrp" value="{{ $item->mrp }}" >

                                @if ($errors->has('mrp'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('mrp') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('purchase_price') ? ' has-error' : '' }} opening_stock_block">
                            <label for="purchase_price" class="col-md-4 control-label">Purchase Price</label>

                            <div class="col-md-6">
                                <input id="purchase_price" type="text" class="form-control num-only" name="purchase_price" value="{{ $item->purchase_price }}" >

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
                                <input id="sale_price" type="text" class="form-control num-only" name="sale_price" value="{{ $item->sale_price }}" >

                                @if ($errors->has('sale_price'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sale_price') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('barcode') ? ' has-error' : '' }} opening_stock_block">
                            <label for="barcode" class="col-md-4 control-label">Barcode</label>

                            <div class="col-md-6">
                                <input id="barcode" type="text" class="form-control" name="barcode" value="{{ $item->barcode }}" >

                                @if ($errors->has('barcode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('barcode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        </div>

                        <div class="form-group" style="display: none;">
                            <div class="col-md-4 control-label">
                                <label>Under RCM</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="item_under_rcm" id="item_under_rcm1" @if($item->item_under_rcm == "yes") checked @endif value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="item_under_rcm" id="item_under_rcm2" value="no" @if($item->item_under_rcm == "no") checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div id="measuring_unit_block" class="form-group{{ $errors->has('measuring_unit') ? ' has-error' : '' }}">
                            <label for="measuring_unit" class="col-md-4 control-label">Measuring Unit (UQC)</label>

                            <div class="col-md-6">
                                <select id="measuring_unit" class="form-control" name="measuring_unit" required>
                                    @foreach($measuring_units as $measuring_unit)
                                        <option @if( $item->measuring_unit == $measuring_unit->name . ' (' . $measuring_unit->description . ')' ) selected="selected" @endif value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('measuring_unit'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('measuring_unit') }}</strong>
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
                                    <input type="radio" name="has_alternate_unit" id="has_alternate_unit1" value="yes" @if($item->has_alternate_unit == "yes") checked @endif> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_alternate_unit" id="has_alternate_unit2" value="no" @if($item->has_alternate_unit == "no") checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div id="alternate_unit_block" @if($item->has_alternate_unit == "no") style="display: none;" @endif>

                            <div class="form-group">
                                <label for="alternate_measuring_unit" class="col-md-4 control-label">Alternative Unit</label>

                                <div class="col-md-6">
                                    <select id="alternate_measuring_unit" class="form-control" name="alternate_measuring_unit" >
                                        @foreach($measuring_units as $measuring_unit)
                                            <option @if( $item->alternate_measuring_unit == $measuring_unit->name . ' (' . $measuring_unit->description . ')' ) selected="selected" @endif value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="form-group{{ $errors->has('alternate_unit_short_name') ? ' has-error' : '' }}">
                                <label for="alternate_unit_short_name" class="col-md-4 control-label">Short Name</label>

                                <div class="col-md-6">
                                    <input type="text" name="alternate_unit_short_name" class="form-control" id="alternate_unit_short_name" value="{{ $item->alternate_unit_short_name }}" />

                                    @if($errors->has('alternate_unit_short_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('alternate_unit_short_name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="alternate_unit_decimal_place" class="col-md-4 control-label">Decimal Place</label>
                                <div class="col-md-6">
                                    <input type="text" name="alternate_unit_decimal_place" class="form-control" value="{{ $item->alternate_unit_decimal_place }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">1 <span id="selected_alternate_unit">{{ $item->alternate_measuring_unit }}</span> = </label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="conversion_of_alternate_to_base_unit_value" value="{{ $item->conversion_of_alternate_to_base_unit_value }}" />
                                </div>
                                <label class="col-md-3 control-label" style="text-align: left"><span id="selected_base_unit">{{ $item->measuring_unit }}</span></label>
                            </div>


                        </div>

                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Compound Unit?</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="has_compound_unit" id="has_compound_unit1" value="yes" @if($item->has_compound_unit == "yes") checked @endif> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_compound_unit" id="has_compound_unit2" value="no" @if($item->has_compound_unit == "no") checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div id="compound_unit_block" @if($item->has_compound_unit == "no") style="display: none;" @endif>

                            <div class="form-group">
                                <label for="compound_measuring_unit" class="col-md-4 control-label">Compound Unit</label>

                                <div class="col-md-6">
                                    <select id="compound_measuring_unit" class="form-control" name="compound_measuring_unit" >
                                        @foreach($measuring_units as $measuring_unit)
                                            <option @if( $item->compound_measuring_unit == $measuring_unit->name . ' (' . $measuring_unit->description . ')' ) selected="selected" @endif value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="form-group{{ $errors->has('compound_unit_short_name') ? ' has-error' : '' }}">
                                <label for="compound_unit_short_name" class="col-md-4 control-label">Short Name</label>

                                <div class="col-md-6">
                                    <input type="text" name="compound_unit_short_name" class="form-control" id="compound_unit_short_name" value="{{ $item->compound_unit_short_name }}" />

                                    @if($errors->has('compound_unit_short_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('compound_unit_short_name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="compound_unit_decimal_place" class="col-md-4 control-label">Decimal Place</label>
                                <div class="col-md-6">
                                    <input type="text" name="compound_unit_decimal_place" class="form-control" value="{{ $item->compound_unit_decimal_place }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">1 <span id="selected_compound_unit">{{ $item->compound_measuring_unit }}</span> = </label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="conversion_of_compound_to_alternate_unit_value" value="{{ $item->conversion_of_compound_to_alternate_unit_value }}" />
                                </div>
                                <label class="col-md-3 control-label" style="text-align: left"><span id="selected_alternate_unit_for_compound">{{ $item->alternate_measuring_unit }}</span></label>
                            </div>


                        </div>

                        <div class="form-group{{ $errors->has('opening_stock_date') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_date" class="col-md-4 control-label">Opening Stock Date</label>

                            <div class="col-md-6">
                                <input id="opening_stock_date" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="opening_stock_date" @if($item->opening_stock_date != null) value="{{ \Carbon\Carbon::parse($item->opening_stock_date)->format('d/m/Y') }}" @endif >

                                @if ($errors->has('opening_stock_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_date') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_stock') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock" class="col-md-4 control-label">Opening Stock Qty</label>

                            <div class="col-md-6">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <input id="opening_stock" type="text" class="form-control num-only" name="opening_stock" value="{{ $item->opening_stock }}" >
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control" id="opening_stock_unit" name="opening_stock_unit">
                                            <option disabled>Select Unit</option>
                                            <option selected value="{{ $item->measuring_unit }}">{{ $item->measuring_unit }}</option>
                                            @if($item->alternate_measuring_unit != null)
                                            <option value="{{ $item->alternate_measuring_unit }}">{{ $item->alternate_measuring_unit }}</option>
                                            @endif
                                            @if($item->compound_measuring_unit != null)
                                            <option value="{{ $item->compound_measuring_unit }}">{{ $item->compound_measuring_unit }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

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
                                <input id="opening_stock_rate" type="text" class="form-control num-only" name="opening_stock_rate" value="{{ $item->opening_stock_rate }}" >

                                @if ($errors->has('opening_stock_rate'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_rate') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_stock_rate') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_value" class="col-md-4 control-label">Opening Stock Value</label>

                            <div class="col-md-6">
                                <input id="opening_stock_value" type="text" class="form-control num-only" name="opening_stock_value" value="{{ $item->opening_stock_value }}" >

                                @if ($errors->has('opening_stock_value'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_value') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group" id="edit_applicable_date_block">
                            <label for="edit_applicable_date" class="col-md-4 control-label">Applicable Date</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control custom_date" id="edit_applicable_date" name="edit_applicable_date" placeholder="DD/MM/YYYY" value="{{ old('edit_applicable_date') }}" autocomplete="off" maxlength="10" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
                                    Edit Item
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="add_new_measuring_unit_modal">
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
</div>

@endsection


@section('scripts')
    <script>
        $.noConflict();
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

    </script>
@endsection --}}

@extends('layouts.dashboard')

@section('content')
{{-- {!! Breadcrumbs::render('create-item') !!} --}}
<div class="container">
    {{-- <div class="row">
        <div class="col-md-12">
            <a href="{{ route('item.index') }}">View All Items</a>&nbsp;&nbsp;
            <a href="{{ route('item.create') }}">Create New Item</a>&nbsp;&nbsp;
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Edit Item</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('item.update', $item->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Type</label>
                            </div>
                            <div class="col-md-6">
                                {{-- <label class="radio-inline">
                                    <input type="radio" name="type_of_service" id="type_of_service1" value="service" @if($item->type == "service") checked @endif> Service
                                </label> --}}
                                <label class="radio-inline">
                                    <input type="radio" name="type_of_service" id="type_of_service2" value="physical" @if($item->type == "physical") checked @endif value="physical"> Goods/Service
                                </label>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('group') ? ' has-error' : '' }}">
                            <label for="group" class="col-md-4 control-label">Select Group</label>

                            <div class="col-md-6">
                                <select class="form-control" name="group" required>
                                    @foreach($groups as $group)
                                        <option @if($item->group_id == $group->id) {{ 'selected="selected"' }} @endif value="{{ $group->id }}">{{ $group->name }}</option>
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
                                <input id="name" type="text" class="form-control" name="name" value="{{ $item->name }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="category_block" class="form-group{{ $errors->has('category') ? ' has-error' : '' }}">
                            <label for="category" class="col-md-4 control-label">Item Category</label>

                            <div class="col-md-6">
                                <select class="form-control" name="category">
                                    <option @if( $item->category == "finished" ) selected="selected" @endif value="finished">Finished</option>
                                    <option @if( $item->category == "raw" ) selected="selected" @endif value="raw">Raw</option>
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
                                <input id="hsc_code" type="text" class="form-control" name="hsc_code" value="{{ $item->hsc_code }}" >

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
                                <input id="sac_code" type="text" class="form-control" name="sac_code" value="{{ $item->sac_code }}" >

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
                                        <option @if($item->gst == $gst->value) selected="selected" @endif value="{{ $gst->value }}">{{ $gst->name }}</option>
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
                                <input id="cess" type="text" class="form-control" name="cess" value="{{ $item->cess }}" >

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
                                    <input type="radio" name="show_additional_option" value="yes" @if($item->has_additional_items == "yes") checked @endif> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="show_additional_option" value="no" @if($item->has_additional_items == "no") checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div id="additional-option-block" @if($item->has_additional_items == "no")style="display: none;" @endif>

                            <div id="manufacture_block" class="form-group{{ $errors->has('manufacture') ? ' has-error' : '' }}">
                                <label for="manufacture" class="col-md-4 control-label">Manufacture</label>

                                <div class="col-md-6">
                                    <input id="manufacture" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="manufacture" @if($item->manufacture != null) value="{{ \Carbon\Carbon::parse($item->manufacture)->format('d/m/Y') }}" @endif >

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
                                    <input id="expiry" type="text" class="form-control custom_date" name="expiry" placeholder="DD/MM/YYYY" @if($item->expiry != null) value="{{ \Carbon\Carbon::parse($item->expiry)->format('d/m/Y') }}" @endif >

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
                                    <input id="batch" type="text" class="form-control" name="batch" value="{{ $item->batch }}" >

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
                                    <input id="size" type="text" class="form-control" name="size" value="{{ $item->size }}" >

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
                                    <input id="mrp" type="text" class="form-control" name="mrp" value="{{ $item->mrp }}" >

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
                                    <input id="purchase_price" type="text" class="form-control" name="purchase_price" value="{{ $item->purchase_price }}" >

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
                                    <input id="sale_price" type="text" class="form-control" name="sale_price" value="{{ $item->sale_price }}" >

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
                                    <input id="barcode" type="text" class="form-control" name="barcode" value="{{ $item->barcode }}" >

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
                                        <input type="radio" name="free_qty" id="free_qty_yes" value="yes" @if($item->free_qty == "yes") checked @endif> Yes
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="free_qty" id="free_qty_no" value="no" @if($item->free_qty == "no") checked @endif> No
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
                                    <input type="radio" name="item_under_rcm" id="item_under_rcm1" value="yes" @if($item->item_under_rcm == "yes") checked @endif> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="item_under_rcm" id="item_under_rcm2" value="no" @if($item->item_under_rcm == "no") checked @endif> No
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
                                <select id="measuring_unit" class="form-control" name="measuring_unit" required>
                                    @foreach($measuring_units as $measuring_unit)
                                        <option @if( $item->measuring_unit == $measuring_unit->name . ' (' . $measuring_unit->description . ')' ) selected="selected" @endif value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
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
                                <input type="text" name="measuring_unit_decimal_place" class="form-control" id="measuring_unit_decimal_place" value="{{ $item->measuring_unit_decimal_place }}" />

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
                                    <input type="radio" name="has_alternate_unit" id="has_alternate_unit1" value="yes" @if($item->has_alternate_unit == "yes") checked @endif> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_alternate_unit" id="has_alternate_unit2" value="no" @if($item->has_alternate_unit == "no") checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div id="alternate_unit_block" @if($item->has_alternate_unit == "no") style="display: none;" @endif>

                            <div class="form-group">
                                <label for="alternate_measuring_unit" class="col-md-4 control-label">Alternative Unit</label>

                                <div class="col-md-6">
                                    <select id="alternate_measuring_unit" class="form-control" name="alternate_measuring_unit" >
                                        @foreach($measuring_units as $measuring_unit)
                                            <option @if( $item->alternate_measuring_unit == $measuring_unit->name . ' (' . $measuring_unit->description . ')' ) selected="selected" @endif value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            {{-- <div class="form-group{{ $errors->has('alternate_unit_short_name') ? ' has-error' : '' }}">
                                <label for="alternate_unit_short_name" class="col-md-4 control-label">Short Name</label>

                                <div class="col-md-6">
                                    <input type="text" name="alternate_unit_short_name" class="form-control" id="alternate_unit_short_name" value="{{ $item->alternate_unit_short_name }}" />

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
                                    <input type="text" name="alternate_unit_decimal_place" class="form-control" value="{{ $item->alternate_unit_decimal_place }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">1 <span id="selected_alternate_unit">{{ $item->alternate_measuring_unit }}</span> = </label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="conversion_of_alternate_to_base_unit_value" value="{{ $item->conversion_of_alternate_to_base_unit_value }}" />
                                </div>
                                <label class="col-md-3 control-label" style="text-align: left"><span id="selected_base_unit">{{ $item->measuring_unit }}</span></label>
                            </div>


                        </div>

                        <div class="form-group">
                            <div class="col-md-4 control-label">
                                <label>Compound Unit?</label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="has_compound_unit" id="has_compound_unit1" value="yes" @if($item->has_compound_unit == "yes") checked @endif> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="has_compound_unit" id="has_compound_unit2" value="no" @if($item->has_compound_unit == "no") checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div id="compound_unit_block" @if($item->has_compound_unit == "no") style="display: none;" @endif>

                            <div class="form-group">
                                <label for="compound_measuring_unit" class="col-md-4 control-label">Compound Unit</label>

                                <div class="col-md-6">
                                    <select id="compound_measuring_unit" class="form-control" name="compound_measuring_unit" >
                                        @foreach($measuring_units as $measuring_unit)
                                            <option @if( $item->compound_measuring_unit == $measuring_unit->name . ' (' . $measuring_unit->description . ')' ) selected="selected" @endif value="{{ $measuring_unit->name }} ({{ $measuring_unit->description }})">{{ $measuring_unit->name }} ({{ $measuring_unit->description }})</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            {{-- <div class="form-group{{ $errors->has('compound_unit_short_name') ? ' has-error' : '' }}">
                                <label for="compound_unit_short_name" class="col-md-4 control-label">Short Name</label>

                                <div class="col-md-6">
                                    <input type="text" name="compound_unit_short_name" class="form-control" id="compound_unit_short_name" value="{{ $item->compound_unit_short_name }}" />

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
                                    <input type="text" name="compound_unit_decimal_place" class="form-control" value="{{ $item->compound_unit_decimal_place }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">1 <span id="selected_compound_unit">{{ $item->compound_measuring_unit }}</span> = </label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="conversion_of_compound_to_alternate_unit_value" value="{{ $item->conversion_of_compound_to_alternate_unit_value }}" />
                                </div>
                                <label class="col-md-3 control-label" style="text-align: left"><span id="selected_alternate_unit_for_compound">{{ $item->alternate_measuring_unit }}</span></label>
                            </div>


                        </div>
                        @endif

                        <div class="form-group{{ $errors->has('opening_stock_date') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_date" class="col-md-4 control-label">Opening Stock Date</label>

                            <div class="col-md-6">
                                <input id="opening_stock_date" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="opening_stock_date" value="{{ \Carbon\Carbon::parse($item->opening_stock_date)->format('d/m/Y') }}" >

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
                                        <input id="opening_stock" type="text" class="form-control" name="opening_stock" value="{{ $item->original_opening_stock }}" >
                                    </div>
                                    <div class="col-xs-4" style="padding-left: 0;">
                                        <select class="form-control" id="opening_stock_unit" name="opening_stock_unit">
                                            <option disabled>Select Unit</option>
                                            <option @if($item->original_opening_stock_unit == $item->measuring_unit) selected @endif value="{{ $item->measuring_unit }}">{{ $item->measuring_unit }}</option>
                                            @if($item->alternate_measuring_unit != null)
                                            <option @if($item->original_opening_stock_unit == $item->alternate_measuring_unit) selected @endif value="{{ $item->alternate_measuring_unit }}">{{ $item->alternate_measuring_unit }}</option>
                                            @endif
                                            @if($item->compound_measuring_unit != null)
                                            <option @if($item->original_opening_stock_unit == $item->compound_measuring_unit) selected @endif value="{{ $item->compound_measuring_unit }}">{{ $item->compound_measuring_unit }}</option>
                                            @endif
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
                                <input id="opening_stock_rate" type="text" class="form-control" name="opening_stock_rate" value="{{ $item->opening_stock_rate }}" >

                                <p id="base_rate_converted"></p>
                                @if ($errors->has('opening_stock_rate'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_rate') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="form-group{{ $errors->has('opening_stock_rate') ? ' has-error' : '' }} opening_stock_block">
                            <label for="opening_stock_value" class="col-md-4 control-label">Opening Stock Value</label>

                            <div class="col-md-6">
                                <input id="opening_stock_value" type="text" class="form-control" name="opening_stock_value" value="{{ $item->opening_stock_value }}" >

                                @if ($errors->has('opening_stock_value'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_stock_value') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group" id="edit_applicable_date_block">
                            <label for="edit_applicable_date" class="col-md-4 control-label">Applicable Date</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control custom_date" id="edit_applicable_date" name="edit_applicable_date" placeholder="DD/MM/YYYY" value="{{ old('edit_applicable_date') }}" autocomplete="off" maxlength="10" required>
                                <p id="applicable_date_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div> --}}

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine update-item">
                                    Update Item
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

        $(document).ready(function() {

            $("#opening_stock_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "date_validation_error", "#", "update-item", ".");
            });

            $("#edit_applicable_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "applicable_date_validation_error", "#", "update-item", ".");
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
                // var alternate_measuring_unit = $(this).val();
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

            $('input[name="has_compound_unit"]').on("change", function() {
                var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
                if($('input[name="has_compound_unit"]:checked').val() == "no"){
                    $("#selected_compound_unit").text("BAG (BAGS)");
                    $(`#opening_stock_unit option[value="${compound_measuring_unit}"]`).remove();
                }
            });

            $(document).on("change", "#compound_measuring_unit", function() {
                // var compound_measuring_unit = $(this).val();
                // $("#selected_compound_unit").text(compound_measuring_unit);
                // // $("#selected_alternate_unit_for_compound").text( $("#alternate_measuring_unit").val() );
                // $("#opening_stock_unit").append(`<option value="${compound_measuring_unit}">${compound_measuring_unit}</option>`);
                appendMeasuringUnitsToOpeningStock();
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
                $("#opening_stock_unit").append(`<option value="${alternate_measuring_unit}">${alternate_measuring_unit}</option>`);
                $("#opening_stock_unit").append(`<option value="${compound_measuring_unit}">${compound_measuring_unit}</option>`);
            }

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

            function calculated_base_rate() {
                var measuring_unit = $("#measuring_unit option:selected").val();
                var alternate_measuring_unit = $("#alternate_measuring_unit option:selected").val();
                var compound_measuring_unit = $("#compound_measuring_unit option:selected").val();
                var selectedVal = $("#opening_stock_unit option:selected").val();
                var stock = $("#opening_stock").val();
                var rate = $("#opening_stock_rate").val();
                var value = $("#opening_stock_value").val();

                if(selectedVal == measuring_unit) {
                    $("#base_value_converted").html(`<span>${stock} ${measuring_unit}</span>`);
                    $("#base_rate_converted").html(`<span>Rs ${rate} per ${measuring_unit}</span>`);
                }
                else if(selectedVal == alternate_measuring_unit) {
                    var alternate_unit_input = $('input[name="alternate_unit_input"]').val() || 1;
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
                    var alternate_unit_input = $('input[name="alternate_unit_input"]').val() || 1;
                    var conversion_of_alternate_to_base_unit_value = $('input[name="conversion_of_alternate_to_base_unit_value"]').val();
                    var compound_unit_input = $('input[name="compound_unit_input"]').val() || 1;
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


            $("#opening_stock_unit").trigger("change");
        });

    </script>
@endsection
