@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('stock-summary') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <form>
                <div class="col-md-8">
                    <div class="form-group">
                        <input type="text" name="item" id="search_by_item_name" class="form-control" placeholder="Search by Item" autocomplete="off" />
                        <div class="auto"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            @php
                if( request()->from_date != null ){
                    $from_date = request()->from_date;
                }else{
                    $from_date = \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)->format('d/m/Y');
                }
                if( request()->to_date != null ){
                    $to_date = request()->to_date;
                }else{
                    $to_date = \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to)->format('d/m/Y');
                }
                $value_type = request()->value_type;
                $price_type = request()->price_type;
                $price_value = request()->price_value;
                if( request()->item )
                {
                    $item = request()->item;
                } else {
                    $item = null;
                }
            @endphp
            <form>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" value="{{ $from_date }}" />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" value="{{ $to_date }}" />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{-- <input type="radio" name="value_type" value="lifo" id="value_type1" @if( isset($value_type) ) @if( $value_type == 'lifo' ) checked @endif @endif /> <label for="value_type1">LIFO</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="value_type" value="fifo" id="value_type2" @if( isset($value_type) ) @if( $value_type == 'fifo' ) checked @endif @else checked @endif /> <label for="value_type2">FIFO</label>&nbsp;&nbsp;&nbsp;&nbsp; --}}
                        {{-- <input type="radio" name="value_type" value="standard" id="value_type3" @if( isset($value_type) ) @if($value_type == 'standard') checked @endif @endif /> <label for="value_type3">Standard</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="value_type" value="average" id="value_type4" @if( isset($value_type) ) @if($value_type == 'average') checked @endif @else checked @endif /> <label for="value_type4">Average</label> --}}

                        {{-- <input type="radio" name="value_type" value="average" id="value_type5" @if( isset($value_type) ) @if($value_type == 'average') checked @endif @endif /> <label for="value_type5">Average</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="value_type" value="normal" id="value_type6" @if( isset($value_type) ) @if($value_type == 'normal') checked @endif @else checked @endif /> <label for="value_type6">Normal</label> --}}
                    </div>
                </div>
                {{-- <div class="col-md-12">
                    <div class="form-group">
                        <input type="radio" name="price_type" class="price_type1" value="normal" id="price_type1" @if( isset($price_type) ) @if($price_type == 'normal') checked @endif @endif /> <label for="price_type1">Normal</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="price_type" class="price_type1" value="standard" id="price_type2" @if( isset($price_type) ) @if($price_type == 'standard') checked @endif @endif /> <label for="price_type2">Standard</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="price_type" class="price_type1" value="average" id="price_type3" @if( isset($price_type) ) @if($price_type == 'average') checked @endif @endif /> <label for="price_type3">average</label>
                    </div>
                </div> --}}
                <div class="col-md-12" id="standard_price_block1" @if($value_type !== 'standard') style="display: none;" @endif>
                    <div class="form-group">
                        <input type="text" name="price_value" class="form-control" placeholder="Price" value="{{ $price_value }}" />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2 col-md-offset-10 text-right"><button type="button" id="btn_configuration" class="btn btn-success">Configuration</button></div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            Stock Summary Report
                        </div>
                        <div class="col-md-4">
                            {{-- <div class="dropdown">
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
                            </div> --}}
                        </div>
                    </div>
                </div>

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

                    <div class="table-responsive">

                        <table class="table table-bordered">
                            <thead>
                                {{-- <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Measuring Unit</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                </tr> --}}
    
                                <tr>
                                    <th rowspan="3" class="text-center">Item Name</th>
                                    <th colspan="{{ auth()->user()->profile->add_lump_sump == "yes" ? 4 : $value_type == "average" ? 10 : 8 }}" class="text-center">
                                        {{ auth()->user()->profile->name }}
                                        <p>{{ $from_date }} to {{ $to_date }}</p>
                                    </th>
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <th rowspan="3" colspan="2">
                                       Action 
                                    </th>
                                    @endif
                                </tr>
                                <tr>
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <th colspan="2" class="text-center opening_balance_side">Opening Balance</th>
                                    <th @if($value_type == "average") colspan="3" @else colspan="2" @endif class="text-center inwards_side">Inwards</th>
                                    <th @if($value_type == "average") colspan="3" @else colspan="2" @endif class="text-center outwards_side">Outwards</th>
                                    @endif
                                    <th @if(auth()->user()->profile->add_lump_sump == "no") colspan="2" @else @if($value_type == "average") colspan="3" @else colspan="2" @endif @endif class="text-center closing_balance_side">Closing Balance</th>
                                </tr>
                                <tr>
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <th class="opening_balance_side qty_side">Qty</th>
                                    {{-- <th class="opening_balance_side rate_side">Rate</th> --}}
                                    <th class="opening_balance_side value_side">Value</th>
    
                                    <th class="inwards_side qty_side">Qty</th>
                                    @if($value_type == "average")
                                    <th class="inwards_side rate_side">Rate</th>
                                    @endif
                                    <th class="inwards_side value_side">Value</th>


                                    <th class="outwards_side qty_side">Qty</th>
                                    @if($value_type == "average")
                                    <th class="outwards_side rate_side">Rate</th>
                                    @endif
                                    <th class="outwards_side value_side">Value</th>
                                    @endif


                                    {{-- <th>Physical Stock</th> --}}
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <th class="closing_side qty_side">Qty</th>
                                    @endif
                                    @if($value_type == "average")
                                    <th class="closing_side rate_side">Rate</th>
                                    @endif
                                    <th class="closing_side value_side">Value</th>
    
                                    {{-- <th class="closing_balance_side qty_side">Qty</th> --}}
                                    {{-- <th class="closing_balance_side rate_side">Rate</th>
                                    <th class="closing_balance_side value_side">Value</th> --}}
                                </tr>
                                {{-- <tr>
                                    <th>Base</th>
                                    <th>Alt</th>
                                    <th>Comp</th>

                                    <th>Base</th>
                                    <th>Alt</th>
                                    <th>Comp</th>

                                    <th>Base</th>
                                    <th>Alt</th>
                                    <th>Comp</th>
                                </tr> --}}
                            </thead>
                            <tbody>
                                @if(count($items) > 0)
                                @php $count = 1 @endphp
                                @foreach($items as $item)
                                <tr>
                                    {{-- <td>{{ $count++ }}</td> --}}
                                    <td class="name_side">
                                        <button class="btn btn-success show-data" data-item_sequence={{ $item->item_sequence }} data-price_sequence="{{ $item->price_sequence }}">View Array Data</button>
                                        <a target="_blank" href="{{ route('single.item.report', $item->id) }}">{{  $item->name   }}</a>
                                        <select class="form-control rate-select">
                                            {{-- @php
                                                // opening stock qty was incorrect, instead was fetching opening stock value so divided value by rate to get actual qty and set it to the property in item 
                                                $item->opening_stock = $item->opening_stock ? $item->opening_stock/$item->opening_stock_rate : 0;
                                            @endphp --}}
                                            @php
                                                $opening_base_value = $item->opening_stock ? $item->opening_stock : 0;
                                                $opening_alternate_value = $item->opening_stock ? $item->opening_stock : 0;
                                                $opening_compound_value = $item->opening_stock ? $item->opening_stock : 0;
                                                
                                                $physical_stock = $item->managedInventories()->orderBy('id', 'desc')->first() ? $item->managedInventories()->orderBy('id', 'desc')->first()->qty : 0;
                                                $physical_stock_qty = $item->managedInventories()->orderBy('id', 'desc')->first() ? $item->managedInventories()->orderBy('id', 'desc')->first()->measuring_unit : null;
                                                
                                                $stock_closing_value = $item->closing_value;


                                                $physical_stock_base = 0;
                                                $physical_stock_alternate = 0;
                                                $physical_stock_compound = 0;


                                                //-------------------------------------------------------------------------
                                                if( $opening_alternate_value > 0 ){
                                                    if($item->has_alternate_unit == "yes") {
                                                        $opening_alternate_value = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $opening_alternate_value / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
    
                                                if( $opening_compound_value > 0 ){
                                                    if($item->has_compound_unit == "yes") {
                                                        $opening_compound_value = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $opening_compound_value / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                                //-------------------------------------------------------------------------
                                                $inwards_base_value = $item->purchasedQty ? $item->purchasedQty : 0;
                                                $inwards_alternate_value = $item->purchasedQty ? $item->purchasedQty : 0;
                                                $inwards_compound_value = $item->purchasedQty ? $item->purchasedQty : 0;
                                                // calculating qty for alternate and compound units
                                                if( $inwards_alternate_value > 0 ){
                                                    if($item->has_alternate_unit == "yes"){
                                                        $inwards_alternate_value = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $inwards_alternate_value / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
    
                                                if( $inwards_compound_value > 0 ){
                                                    if($item->has_compound_unit == "yes"){
                                                        $inwards_compound_value = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $inwards_compound_value / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                                //--------------------------------------------------------------------------
                                                $outwards_base_value = $item->soldQty ? $item->soldQty : 0;
                                                $outwards_alternate_value = $item->soldQty ? $item->soldQty : 0;
                                                $outwards_compound_value = $item->soldQty ? $item->soldQty : 0;

                                                // calculating qty for alternate and compound units
                                                if( $outwards_alternate_value > 0 ) {
                                                    if($item->has_alternate_unit == "yes") {
                                                        $outwards_alternate_value = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $outwards_alternate_value / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
    
                                                if( $outwards_compound_value > 0 ) {
                                                    if($item->has_compound_unit == "yes") {
                                                        $outwards_compound_value = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $outwards_compound_value / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                                //---------------------------------------------------------------------------

                                                $opening_balance_opening_stock_rate = $item->opening_stock_rate;
                                                $inwards_opening_stock_rate = $item->purchasedRate;
                                                $outwards_opening_stock_rate = $item->soldRate;


                                                $closing_qty = ($physical_stock_base > 0) ? $physical_stock_base : $opening_base_value + $inwards_base_value - $outwards_base_value;
                                                $closing_rate = ($closing_qty == 0) ? 0 : $stock_closing_value / $closing_qty;

                                                $closing_rate = round($closing_rate, 2);

                                            @endphp
                                            <option data-opening="{{ $opening_balance_opening_stock_rate }}" data-inwards="{{ $inwards_opening_stock_rate }}" data-outwards="{{ $outwards_opening_stock_rate }}" data-closing="{{ $closing_rate }}" data-opening-qty="{{ $opening_base_value }}" data-inwards-qty="{{ $inwards_base_value }}" data-outwards-qty="{{ $outwards_base_value }}" data-closing-qty="{{ $closing_qty }}" data-fixed-closing-value="{{ $stock_closing_value }}" data-physical-qty="{{ $physical_stock_base }}">{{ $item->measuring_unit }}</option>
                                            
                                            @if($item->has_alternate_unit == "yes")
                                            @php 
                                                $opening_balance_alt_price_value = $item->opening_stock_rate * $item->conversion_of_compound_to_alternate_unit_value;

                                                $inwards_alt_price_value = $item->purchasedRate * $item->conversion_of_compound_to_alternate_unit_value;

                                                $outwards_alt_price_value = $item->soldRate * $item->conversion_of_compound_to_alternate_unit_value;


                                                $closing_qty = ($physical_stock_alternate > 0) ? $physical_stock_alternate : $opening_alternate_value + $inwards_alternate_value - $outwards_alternate_value;
                                                $closing_rate = ($closing_qty == 0) ? 0 : $stock_closing_value / $closing_qty;
                                            @endphp
                                            <option data-opening="{{ $opening_balance_alt_price_value }}" data-inwards="{{ $inwards_alt_price_value }}" data-outwards="{{ $outwards_alt_price_value }}" data-closing="{{ $closing_rate }}" data-opening-qty="{{ $opening_alternate_value }}" data-inwards-qty="{{ $inwards_alternate_value }}" data-outwards-qty="{{ $outwards_alternate_value }}" data-closing-qty="{{ $closing_qty }}" data-fixed-closing-value="{{ $stock_closing_value }}" data-physical-qty="{{ $physical_stock_alternate }}">{{ $item->alternate_measuring_unit }}</option>
                                            @endif
                                            
                                            @if($item->has_compound_unit == "yes")
                                            @php 
                                                $opening_balance_comp_price_value = $item->opening_stock_rate * ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);

                                                $inwards_comp_price_value = $item->purchasedRate * ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);

                                                $outwards_comp_price_value = $item->soldRate * ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);

                                                $inwards_compound_value = $item->purchasedQty ? $item->purchasedQty : 0;
                                                $inwards_compound_value = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $inwards_compound_value / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);

                                                $closing_qty = ($physical_stock_compound > 0) ? $physical_stock_compound : $opening_compound_value + $inwards_compound_value - $outwards_compound_value;
                                                $closing_rate = ($closing_qty == 0) ? 0 : $stock_closing_value / $closing_qty;
                                            @endphp
                                            <option data-opening="{{ $opening_balance_comp_price_value }}" data-inwards="{{ $inwards_comp_price_value }}" data-outwards="{{ $outwards_comp_price_value }}" data-closing="{{ $closing_rate }}" data-opening-qty="{{ $opening_compound_value }}" data-inwards-qty="{{ $inwards_compound_value }}" data-outwards-qty="{{ $outwards_compound_value }}" data-closing-qty="{{ $closing_qty }}" data-fixed-closing-value="{{ $stock_closing_value }}" data-physical-qty="{{ $physical_stock_compound }}">{{ $item->compound_measuring_unit }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    @php 
                                        $opening_stock = $item->opening_stock ?? 0;
                                        $opening_rate = $item->opening_stock_rate ?? 0;
                                        $opening_value = $item->opening_stock_value;

                                        $purchase_qty = $item->purchasedQty ?? 0;
                                        $purchase_rate = $item->purchasedRate ?? 0;
                                        $purchase_value = auth()->user()->profile->add_lump_sump == "no" ?  $purchase_rate : $purchase_rate;

                                        $sale_qty = $item->soldQty ?? 0;
                                        $sale_rate = $item->soldRate ?? 0;
                                        $sale_value = auth()->user()->profile->add_lump_sump == "no" ? $sale_rate : $sale_rate;

                                        $closing_qty = $opening_stock + $purchase_qty - $sale_qty;
                                        $closingRate = $value_type == "normal" ? 0 : $item->average_closing_rate;
                                        $closing_value = $opening_value + $purchase_value - $sale_value;

                                        if(auth()->user()->profile->add_lump_sump == "yes") {
                                            $closing_value = $opening_value + $purchase_value + (auth()->user()->profile->gp_percent_on_sale_value * $sale_value / 100) - $sale_value;
                                        } else if($value_type == "normal") {
                                            $closing_value = $opening_value + $purchase_value - $sale_value;
                                        }
                                    @endphp

                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <td class="opening_balance_side qty_side opening-qty-side">{{ $opening_stock }}</td>
                                    {{-- <td class="opening_balance_side rate_side">
                                        <span class="opening-rate-span">{{ $opening_rate }}</span>
                                    </td> --}}
                                    <td class="opening_balance_side value_side">{{ $opening_value }}</td>
                                    @endif

                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <td class="inwards_side qty_side inwards-qty-side">{{ $purchase_qty }}</td>
                                    @if($value_type == "average")
                                    <td class="inwards_side rate_side">
                                        <span class="inwards-rate-span">{{ $purchase_rate }}</span>
                                    </td>
                                    @endif
                                    <td class="inwards_side value_side">{{ $purchase_value }}</td>
                                    @endif
    
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <td class="outwards_side qty_side outwards-qty-side">{{ $sale_qty }}</td>
                                    @if($value_type == "average")
                                    <td class="outwards_side rate_side">
                                        <span class="outwards-rate-span">{{ $sale_rate }}</span>
                                    </td>
                                    @endif
                                    <td class="outwards_side value_side">{{ $sale_value }}</td>
                                    @endif
    
                                    {{-- below code is repetitive please make sure u change similar code above if u change this code --}}
                                    @php
                                        // $closingQty = ($physical_stock_base > 0) ? $physical_stock_base : $opening_base_value + $inwards_base_value - $outwards_base_value;
                                        // commented below 2 lines after above closing value created
                                        // $closingQty = $opening_base_value + $inwards_base_value - $outwards_base_value;
                                        // $closingRate = ($closingQty == 0) ? 0 : $stock_closing_value / $closingQty;
                                    @endphp

                                    @if(auth()->user()->profile->add_lump_sump == "yes")
                                        @php
                                            // $closing_value = 0;

                                            // $opening_stock = $item->opening_stock ? $item->opening_stock : 0;
                                            // $opening_rate = $item->opening_stock_rate ? $item->opening_stock_rate : 0;

                                            // $purchase_qty = $item->purchasedQty ? $item->purchasedQty : 0;
                                            // $purchase_rate = $item->purchasedRate ? $item->purchasedRate : 0;

                                            // $sale_qty = $item->soldQty ? $item->soldQty : 0;
                                            // $sale_rate = $item->soldRate ? $item->soldRate : 0;

                                            // $opening_value = $opening_stock * $opening_rate;
                                            // $purchase_value = $purchase_qty * $purchase_rate;
                                            // $sale_value = $sale_qty * $sale_rate;

                                            // $gross_profit_sale_percent = isset(auth()->user()->profile->gross_profit) ? auth()->user()->profile->gross_profit : 0;

                                            // $gross_profit_on_sale = $sale_value * $gross_profit_sale_percent / 100;

                                            // $closing_value = ($opening_stock + $purchase_value + $gross_profit_on_sale) - $sale_value;

                                            // calculating closing value of stock in controller
                                            // $closing_value = $item->closing_value;
                                        @endphp
                                    @endif

                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                        {{-- <td class="physical-qty-side">{{ $physical_stock_base }}</td> --}}
                                        <td class="closing_balance_side qty_side closing-qty-side">{{ $closing_qty }}</td>
                                        @if($value_type == "average")
                                        <td class="closing_balance_side rate_side"><span class="closing-rate-span">{{ $closingRate }}</span></td>
                                        @endif
                                        <td class="closing_balance_side value_side closing-value-span">{{ $closing_value }}</td>
                                    @else
                                        <td>{{ $closing_value }}</td>
                                    @endif
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    {{-- <td>
                                        <a href="{{ route('single.item.stock.summary', $item->id) }}">Stock Summary</a>
                                    </td>
                                    <td>
                                        <a href="{{ route('single.item.stock.summary.detail', $item->id) }}">Stock Summary Detail</a>
                                    </td> --}}
                                    <td>    
                                        {{-- <a href="{{ route('single.item.report', $item->id) }}" class="btn btn-default">View Detail</a>
                                        <a href="{{ route('single.item.stock.summary', $item->id) }}" class="btn btn-default">Stock Summary</a> --}}
                                        <a href="{{ route('single.item.stock.summary.detail', $item->id) }}" class="btn btn-default">Stock Summary Detail</a>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="5">No Item Added</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
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
                            <input type="checkbox" id="show_quantities"> Show Quantities
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_opening_balance"> Opening Balance
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_value"> Show Value
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_rate"> Show Rates
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_goods_inwards"> Show goods inwards
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_goods_outwards"> show goods outwards
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="data_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Data</h4>
                </div>
                <div class="modal-body">
                    <div id="item_section"></div>
                    <div id="price_section"></div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('scripts')

<script>
    $(document).ready(function () {

        $(".show-data").on("click", function() {
            var item = $(this).data('item_sequence');
            var price = $(this).data('price_sequence');

            $("#item_section").html(`<pre>Item => ${item}</pre>`);
            $("#price_section").html(`<pre>Price => ${price}</pre>`);

            $("#data_modal").modal("show");
        });

        $(document).on("keyup", "#search_by_item_name", function() {

            var key_to_search = $(this).val();

            auto_find_item( key_to_search );

        });

        function auto_find_item( key_to_search ) {
            if(key_to_search == ''){
                key_to_search = 0;
                $('.auto').removeClass('active');
            }
            $.ajax({
                "type": "POST",
                "url": "{{ route('api.search.item.by.name') }}",
                "data": {
                    "key_to_search": key_to_search,
                    "_token": '{{ csrf_token() }}'
                },
                success: function(data){

                    console.log(data);
                    var outWords = data;
                    if(outWords.length > 0) {

                        for(x = 0; x < outWords.length; x++){
                            $('.auto').append(`<div data-value="${outWords[x].name}" >${outWords[x].name}</div>`); //Fills the .auto div with the options
                        }

                        $('.auto').addClass('active');

                    }
                }
            });
        }

        $(document).on('click', '.auto div', function(){
            var searched_value = $(this).attr('data-value');

            $('.auto').html('');
            $('.auto').removeClass('active');

            $("#search_by_item_name").val(searched_value);

        });

        $(".measuring_unit").on("change", function() {
            var tr = $(this).closest('tr');
            tr.find("#match_the_qty").text($(this).val());
        });

        $("#btn_configuration").on("click", function(){
            $("#configuration_modal").modal("show");
        });


        $("#show_quantities").on("change", function(){
            if( $(this).is(":checked") ){
                $(".rate_side").css("visibility", "hidden");
                $(".value_side").css("visibility", "hidden");
            }else{
                $(".rate_side").css("visibility", "visible");
                $(".value_side").css("visibility", "visible");
            }
        });

        $("#show_opening_balance").on("change", function(){
            if( $(this).is(":checked") ){
                $(".inwards_side").css("visibility", "hidden");
                $(".outwards_side").css("visibility", "hidden");
            } else {
                $(".inwards_side").css("visibility", "visible");
                $(".outwards_side").css("visibility", "visible");
            }
        });

        $("#show_value").on("change", function(){
            if( $(this).is(":checked") ){
                $(".qty_side").css("visibility", "hidden");
                $(".rate_side").css("visibility", "hidden");
            } else {
                $(".qty_side").css("visibility", "visible");
                $(".rate_side").css("visibility", "visible");
            }
        });

        $("#show_rate").on("change", function(){
            if( $(this).is(":checked") ){
                $(".qty_side").css("visibility", "hidden");
                $(".value_side").css("visibility", "hidden");
            } else {
                $(".qty_side").css("visibility", "visible");
                $(".value_side").css("visibility", "visible");
            }
        });

        $("#show_goods_inwards").on("change", function(){
            if( $(this).is(":checked") ){
                $(".opening_balance_side").css("visibility", "hidden");
                $(".outwards_side").css("visibility", "hidden");
            } else {
                $(".opening_balance_side").css("visibility", "visible");
                $(".outwards_side").css("visibility", "visible");
            }
        });

        $("#show_goods_outwards").on("change", function(){
            if( $(this).is(":checked") ){
                $(".opening_balance_side").css("visibility", "hidden");
                $(".inwards_side").css("visibility", "hidden");
            } else {
                $(".opening_balance_side").css("visibility", "visible");
                $(".inwards_side").css("visibility", "visible");
            }
        });

        $(".rate-select").on("change", function() {
            var opening_rate = $('option:selected', this).attr('data-opening');
            var inwards_rate = $('option:selected', this).attr('data-inwards');
            var outwards_rate = $('option:selected', this).attr('data-outwards');
            var closing_rate = $('option:selected', this).attr('data-closing');
            
            if(opening_rate == ''){
                opening_rate = 0;
            }

            if(inwards_rate == ''){
                inwards_rate = 0;
            }

            if(outwards_rate == ''){
                outwards_rate = 0;
            }

            if(closing_rate == ''){
                closing_rate = 0;
            }

            // console.log(opening_rate);
            // console.log(inwards_rate);
            // console.log(outwards_rate);

            var opening_qty = $('option:selected', this).attr('data-opening-qty');
            var inwards_qty = $('option:selected', this).attr('data-inwards-qty');
            var outwards_qty = $('option:selected', this).attr('data-outwards-qty');
            var closing_qty = $('option:selected', this).attr('data-closing-qty');
            var physical_qty = $('option:selected', this).attr('data-physical-qty');

            if(opening_qty == ''){
                opening_qty = 0;
            }

            if(inwards_qty == ''){
                inwards_qty = 0;
            }

            if(outwards_qty == ''){
                outwards_qty = 0;
            }

            if(closing_qty == ''){
                closing_qty = 0;
            }

            if(physical_qty == ''){
                physical_qty = 0;
            }

            // console.log(opening_qty);
            // console.log(inwards_qty);
            // console.log(outwards_qty);

            var fixed_closing_value = $('option:selected', this).attr('data-fixed-closing-value');

            if(fixed_closing_value == ''){
                fixed_closing_value = 0;
            }

            // console.log(fixed_closing_value);

            $(this).closest('tr').find(".physical-qty-side").text(physical_qty);

            $(this).closest('tr').find(".opening-qty-side").text(opening_qty);
            $(this).closest('tr').find(".opening-rate-span").text(opening_rate);

            $(this).closest('tr').find(".inwards-qty-side").text(inwards_qty);
            $(this).closest('tr').find(".inwards-rate-span").text(inwards_rate);

            $(this).closest('tr').find(".outwards-qty-side").text(outwards_qty);
            $(this).closest('tr').find(".outwards-rate-span").text(outwards_rate);

            // opening_qty *= 1000000000000000000000;
            // inwards_qty *= 1000000000000000000000;
            // outwards_qty *= 1000000000000000000000;
            // var closing_qty = parseFloat(opening_qty) + parseFloat(inwards_qty) - parseFloat(outwards_qty);

            // closing_qty /= 1000000000000000000000;

            // closing_qty = closing_qty.toFixed(5);

            $(this).closest('tr').find(".closing-qty-side").text(closing_qty);
            $(this).closest('tr').find(".closing-rate-span").text(closing_rate);
            // $(this).closest('tr').find(".closing-value-span").text(fixed_closing_value);
        });

        $(document).on('change', 'input[name="value_type"]', function() {

            if( $(this).is(":checked") && $(this).val() == 'standard' ){
                $("#standard_price_block1").show();
            } else {
                $("#standard_price_block1").hide();
            }

        });


        $(document).on('change', '.price_type1', function() {

            if( $(this).is(":checked") && $(this).val() == 'standard' ){
                $("#standard_price_block1").show();
            }

            if( !$("#price_type2").is(":checked") ){
                $("#standard_price_block1").hide();
            }
            
            // else {
            //     $("#standard_price_block1").hide();
            // }

        });

        $(document).on('change', '.price_type2', function() {

            if( $(this).is(":checked") && $(this).val() == 'standard' ){
                $("#standard_price_block2").show();
            }

            if( !$("#price_type5").is(":checked") ){
                $("#standard_price_block2").hide();
            }

            // else {
            //     $("#standard_price_block2").hide();
            // }

        });

    });
</script>

@endsection
