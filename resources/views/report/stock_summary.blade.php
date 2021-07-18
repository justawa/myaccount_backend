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
                                    {{-- @if($value_type == "average")
                                    <th class="inwards_side rate_side">Rate</th>
                                    @endif --}}
                                    <th class="inwards_side value_side">Value</th>


                                    <th class="outwards_side qty_side">Qty</th>
                                    {{-- @if($value_type == "average")
                                    <th class="outwards_side rate_side">Rate</th>
                                    @endif --}}
                                    <th class="outwards_side value_side">Value</th>
                                    @endif


                                    {{-- <th>Physical Stock</th> --}}
                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <th class="closing_side qty_side">Qty</th>
                                    @endif
                                    {{-- @if($value_type == "average")
                                    <th class="closing_side rate_side">Rate</th>
                                    @endif --}}
                                    <th class="closing_side value_side">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($items) > 0)
                                @php $count = 1 @endphp
                                @foreach($items as $item)
                                <tr>
                                    {{-- <td>{{ $count++ }}</td> --}}
                                    <td class="name_side">
                                        <button class="btn btn-success show-data" data-item_sequence={{ $item->item_sequence }} data-price_sequence="{{ $item->price_sequence }}">View Array Data</button>
                                        {{-- <a target="_blank" href="{{ route('single.item.report', $item->id) }}"> --}}
                                            {{  $item->name   }}
                                        {{-- </a> --}}
                                        <select class="form-control rate-select">
                                            
                                            @php
                                                $opening_base_qty = $item->opening_qty ? $item->opening_qty : 0;
                                                $opening_alternate_qty = $item->opening_qty ? $item->opening_qty : 0;
                                                $opening_compound_qty = $item->opening_qty ? $item->opening_qty : 0;

                                                $purchase_base_qty = $item->purchase_qty ? $item->purchase_qty : 0;
                                                $purchase_alternate_qty = $item->purchase_qty ? $item->purchase_qty : 0;
                                                $purchase_compound_qty = $item->purchase_qty ? $item->purchase_qty : 0;
                                                
                                                $sale_base_qty = $item->sale_qty ? $item->sale_qty : 0;
                                                $sale_alternate_qty = $item->sale_qty ? $item->sale_qty : 0;
                                                $sale_compound_qty = $item->sale_qty ? $item->sale_qty : 0;
                                                
                                                $closing_base_qty = $item->closing_qty ? $item->closing_qty : 0;
                                                $closing_alternate_qty = $item->closing_qty ? $item->closing_qty : 0;
                                                $closing_compound_qty = $item->closing_qty ? $item->closing_qty : 0;

                                                if( $opening_alternate_qty > 0 ){
                                                    if($item->has_alternate_unit == "yes") {
                                                        $opening_alternate_qty = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $opening_alternate_qty / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
                                                if( $opening_compound_qty > 0 ){
                                                    if($item->has_compound_unit == "yes") {
                                                        $opening_compound_qty = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $opening_compound_qty / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                                if( $purchase_alternate_qty > 0 ){
                                                    if($item->has_alternate_unit == "yes"){
                                                        $purchase_alternate_qty = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $purchase_alternate_qty / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
                                                if( $purchase_compound_qty > 0 ){
                                                    if($item->has_compound_unit == "yes"){
                                                        $purchase_compound_qty = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $purchase_compound_qty / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                                if( $sale_alternate_qty > 0 ) {
                                                    if($item->has_alternate_unit == "yes") {
                                                        $sale_alternate_qty = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $sale_alternate_qty / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
                                                if( $sale_compound_qty > 0 ) {
                                                    if($item->has_compound_unit == "yes") {
                                                        $sale_compound_qty = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $sale_compound_qty / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                                if( $closing_alternate_qty > 0 ) {
                                                    if($item->has_alternate_unit == "yes") {
                                                        $closing_alternate_qty = ($item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $closing_alternate_qty / $item->conversion_of_alternate_to_base_unit_value;
                                                    }
                                                }
                                                if( $closing_compound_qty > 0 ) {
                                                    if($item->has_compound_unit == "yes") {
                                                        $closing_compound_qty = ($item->conversion_of_compound_to_alternate_unit_value == 0 || $item->conversion_of_alternate_to_base_unit_value == 0) ? 0 : $closing_compound_qty / ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);
                                                    }
                                                }

                                            @endphp
                                            <option data-opening_qty="{{ $opening_base_qty }}" data-purchase_qty="{{ $purchase_base_qty }}" data-sale_qty="{{ $sale_base_qty }}" data-closing_qty="{{ $closing_base_qty }}">{{ $item->measuring_unit }}</option>
                                            @if($item->alternate_measuring_unit)
                                            <option data-opening_qty="{{ $opening_alternate_qty }}" data-purchase_qty="{{ $purchase_alternate_qty }}" data-sale_qty="{{ $sale_alternate_qty }}" data-closing_qty="{{ $closing_alternate_qty }}">{{ $item->alternate_measuring_unit }}</option>
                                            @endif

                                            @if($item->compound_measuring_unit)
                                            <option data-opening_qty="{{ $opening_compound_qty }}" data-purchase_qty="{{ $purchase_compound_qty }}" data-sale_qty="{{ $sale_compound_qty }}" data-closing_qty="{{ $closing_compound_qty }}">{{ $item->compound_measuring_unit }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="opening_balance_side qty_side opening-qty-side">{{ $item->opening_qty }}</td>
                                    <td class="opening_balance_side value_side">{{ $item->opening_value }}</td>
                                    
                                    <td class="inwards_side qty_side inwards-qty-side">{{ $item->purchase_qty }}</td>
                                    {{-- <td class="inwards_side rate_side"><span class="inwards-rate-span">{{ $item->purchase_value }}</span></td> --}}
                                    <td class="inwards_side value_side">{{ $item->purchase_value }}</td>
                                    
                                    <td class="outwards_side qty_side outwards-qty-side">{{ $item->sale_qty }}</td>
                                    {{-- <td class="outwards_side rate_side"><span class="outwards-rate-span"></span></td> --}}
                                    <td class="outwards_side value_side">{{ $item->sale_value }}</td>
                                    
                                    <td class="closing_balance_side qty_side closing-qty-side">{{ $item->closing_qty }}</td>
                                    {{-- <td class="closing_balance_side rate_side"><span class="closing-rate-span"></span></td> --}}
                                    <td class="closing_balance_side value_side closing-value-span">{{ $item->closing_value }}</td>

                                    @if(auth()->user()->profile->add_lump_sump == "no")
                                    <td>
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
            var opening_qty = Number($('option:selected', this).attr('data-opening_qty')).toFixed(2) || 0;
            var purchase_qty = Number($('option:selected', this).attr('data-purchase_qty')).toFixed(2) || 0;
            var sale_qty = Number($('option:selected', this).attr('data-sale_qty')).toFixed(2) || 0;
            var closing_qty = Number($('option:selected', this).attr('data-closing_qty')).toFixed(2) || 0;

            $(this).closest('tr').find(".opening-qty-side").text(opening_qty);
            $(this).closest('tr').find(".inwards-qty-side").text(purchase_qty);
            $(this).closest('tr').find(".outwards-qty-side").text(sale_qty);
            $(this).closest('tr').find(".closing-qty-side").text(closing_qty);
        });
    });
</script>

@endsection
