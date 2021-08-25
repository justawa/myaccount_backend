@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            @php
                if( request()->from_date != null ){
                    $from_date = request()->from_date;
                }else{
                    $from_date = null;
                }
                if( request()->to_date != null ){
                    $to_date = request()->to_date;
                }else{
                    $to_date = null;
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
                        <input type="checkbox" name="value_type[0]" value="lifo" id="value_type1" @if( isset($value_type[0]) ) @if( $value_type[0] == 'lifo' ) checked @endif @endif /> <label for="value_type1">LIFO</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="value_type[1]" value="fifo" id="value_type2" @if( isset($value_type[1]) ) @if( $value_type[1] == 'fifo' ) checked @endif @else checked @endif /> <label for="value_type2">FIFO</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="checkbox" name="price_type[0]" class="price_type1" value="normal" id="price_type1" @if( isset($price_type[0]) ) @if($price_type[0] == 'normal') checked @endif @endif /> <label for="price_type1">Normal</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="price_type[1]" class="price_type1" value="standard" id="price_type2" @if( isset($price_type[1]) ) @if($price_type[1] == 'standard') checked @endif @endif /> <label for="price_type2">Standard</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="price_type[2]" class="price_type1" value="average" id="price_type3" @if( isset($price_type[2]) ) @if($price_type[2] == 'average') checked @endif @endif /> <label for="price_type3">average</label>
                    </div>
                </div>
                <div class="col-md-12" id="standard_price_block1" @if( !isset($price_type[1]) ) style="display: none;" @elseif($price_type[1] == null) style="display: none;" @endif>
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
        <div class="col-md-6">
            <form>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="item" id="search_by_item_name" class="form-control" placeholder="Search by Item" autocomplete="off" />
                        <div class="auto"></div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="checkbox" name="value_type[0]" value="lifo" id="value_type3" @if( isset($value_type[0]) ) @if( $value_type[0] == 'lifo' ) checked @endif @endif /> <label for="value_type3">LIFO</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="value_type[1]" value="fifo" id="value_type4" @if( isset($value_type[1]) ) @if( $value_type[1] == 'fifo' ) checked @endif @else checked @endif /> <label for="value_type4">FIFO</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="checkbox" name="price_type[0]" class="price_type2" value="normal" id="price_type4" @if( isset($price_type[0]) ) @if($price_type[0] == 'normal') checked @endif @endif /> <label for="price_type4">Normal</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="price_type[1]" class="price_type2" value="standard" id="price_type5" @if( isset($price_type[1]) ) @if($price_type[1] == 'standard') checked @endif @endif /> <label for="price_type5">Standard</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" name="price_type[2]" class="price_type2" value="average" id="price_type6" @if( isset($price_type[2]) ) @if($price_type[2] == 'average') checked @endif @endif /> <label for="price_type6">average</label>
                    </div>
                </div>
                <div class="col-md-12" id="standard_price_block2" @if( !isset($price_type[1]) ) style="display: none;" @elseif($price_type[1] == null) style="display: none;" @endif>
                    <div class="form-group">
                        <input type="text" name="price_value" class="form-control" placeholder="Price" value="{{ $price_value }}" />
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View Item Value</div>
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
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Hsc Code</th>
                                    <th>Closing Value(fifo)</th>
                                    <th>Gross Profit(fifo)</th>
                                    <th>Closing Value(lifo)</th>
                                    <th>Gross Profit(lifo)</th>
                                    <th>Closing Value(standard)(fifo)</th>
                                    <th>Gross Profit(standard)(fifo)</th>
                                    <th>Closing Value(average)(fifo)</th>
                                    <th>Gross Profit(average)(fifo)</th>
                                    <th>Closing Value(standard)(lifo)</th>
                                    <th>Gross Profit(standard)(lifo)</th>
                                    <th>Closing Value(average)(lifo)</th>
                                    <th>Gross Profit(average)(lifo)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if( count($items) > 0 )
                                    @php $count = 1; @endphp
                                    @foreach($items as $item)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->hsc_code }}</td>
                                        <td>{{ $item->closing_value_fifo }}</td>
                                        <td>{{ $item->gross_profit_fifo }}</td>
                                        <td>{{ $item->closing_value_lifo }}</td>
                                        <td>{{ $item->gross_profit_lifo }}</td>
                                        <td>{{ $item->standard_closing_value_fifo }}</td>
                                        <td>{{ $item->gross_profit_standard_fifo }}</td>
                                        <td>{{ $item->average_closing_value_fifo }}</td>
                                        <td>{{ $item->gross_profit_average_fifo }}</td>
                                        <td>{{ $item->standard_closing_value_lifo }}</td>
                                        <td>{{ $item->gross_profit_standard_lifo }}</td>
                                        <td>{{ $item->average_closing_value_lifo }}</td>
                                        <td>{{ $item->gross_profit_average_lifo }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4">No Data for this period</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')

<script>
    $(document).ready(function () {

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