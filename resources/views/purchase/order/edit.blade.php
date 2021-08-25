{{-- @extends('layouts.dashboard')

@section('content')

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
    <form method="POST" action="" id="create-sale">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <select class="form-control" id="party" name="party" required>
                    <option value="0">Choose a party</option>
                    @foreach($parties as $party)
                        <option @if( isset($party_id) ) @if($party_id == $party->id) selected="selected"  @endif  @endif value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('party.create') }}">Add Party</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Purchase Order Date</label>
                <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" id="purchase_order_date" name="purchase_order_date" @if( isset($date) ) value="{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}" @endif />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Reference Name</label>
                <input type="text" class="form-control" id="reference_name" name="reference_name" @if( isset($reference_name) ) value="{{ $reference_name }}" @endif>
            </div>
        </div>
    </div>
    {{ -- }} <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Bill No.</label>
                <input id="bill_no" type="text" class="form-control" name="bill_no" value="{{ old('bill_no') }}" required>
            </div>
        </div>
    </div> 
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>
                <tbody id="dynamic-body">
                    @foreach( $purchase_orders as $purchase_order )
                    <tr> 
                        <td>
                            <select class="form-control item" name="item[]" required>
                                @foreach($items as $item)
                                    <option  @if( isset($purchase_order->item_id) ) @if($purchase_order->item_id == $item->id) selected="selected"  @endif @endif value="{{ $item->id }}">{{ $item->name }}</option>    
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control quantity" name="quantity[]" @if(isset($purchase_order->qty)) value="{{ $purchase_order->qty }}"  @endif required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-success btn-mine update-order" data-row="{{ $purchase_order->id }}">Update</button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="form-group">
                <button type="button" id="add-more-items" class="btn btn-success">+ Add More</button>
            </div>
        </div>
    </div>
    </form>
</div>

@endsection

@section('scripts')
    <script>

        $(document).ready(function (){

            $(document).on("click", ".update-order", function () {
                var tr = $(this).closest("tr");

                var row_id = $(this).attr("data-row");

                var item = tr.find(".item").val();

                var qty = tr.find(".quantity").val();

                $.ajax({
                    type: "post",
                    url: "{{ route('update.purchase.order') }}",
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "id": row_id,
                        "item_id": item,
                        "qty": qty
                    },
                    success: function(response){
                        console.log(response);

                        if( response == "success" ){
                            alert("Data updated successfully");
                            location.reload();
                        } else {
                            alert("Failed to update data");
                        }
                    }
                });
            });

            $(document).on("click", ".insert-order", function () {

                var tr = $(this).closest("tr");

                var token = $(this).attr("data-token");

                var item = tr.find(".item").val();

                var qty = tr.find(".quantity").val();

                var party = $("#party").val();

                var date = $("#purchase_order_date").val();

                var reference = $("#reference_name").val();

                $.ajax({
                    type: "post",
                    url: "{{ route('store.purchase.order.single.row') }}",
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "token": token,
                        "item_id": item,
                        "qty": qty,
                        "party_id": party,
                        "date": date,
                        "reference": reference
                    },
                    success: function(response){
                        console.log(response);

                        if( response == "success" ){
                            alert("Data inserted successfully");
                            location.reload();
                        } else {
                            alert("Failed to insert data");
                        }
                    }
                });

            });



            $(document).on("submit", "#create-sale", function(e){
                // e.preventDefault();
                var validation = true;

                var submit_quantities = $(".quantity");
                var submit_items = $(".item");

                var submit_party = $("#party option:selected").val();

                for(var j = 0; j < submit_quantities.length; j++){
                    if(submit_quantities[j].value == ""){
                        validation = false;
                    }
                }

                for(var k = 0; k < submit_items.length; k++){
                    if(submit_items[k].value == ""){
                        validation = false;
                    }
                }

                if(submit_party == 0){
                    validation = false;
                }


                if(validation) {
                    $("#create-sale").trigger( "submit" );

                    // alert('submit');
                } else {
                    e.preventDefault();
                    alert("Please fill up all required fields.");
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

            $(document).on("click", "#add-more-items", function(){
                $("#dynamic-body").append(
                    `<tr>
                        <td>
                            <select class="form-control item" name="item[]" required>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>    
                                @endforeach     
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control quantity" name="quantity[]" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary insert-order" data-token="{{ $purchase_order->token }}">Insert</button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>`
                );
            });

            $(document).on("click", ".delete-row", function(){

                $(this).parent().parent().remove();

            });

        });
    </script>
@endsection --}}


@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('edit-purchase-order', request()->segment(4)) !!}

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
    {{-- <form method="POST" action="" id="create-sale">
    {{ csrf_field() }} --}}
    <div class="row">
        <div class="col-md-offset-8 col-md-4 text-right">
            <p>
                <a href="{{ route('create.purchase.from.order', request()->segment(4)) }}" class="btn btn-link">Convert to Purchase</a>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Choose a party</label>
                <select class="form-control" id="party" name="party" required>
                    <option value="0">Choose a party</option>
                    <option onclick="location.href = '{{ route('party.create') }}'">Add Party</option>
                    @foreach($parties as $party)
                        <option @if( isset($party_id) ) @if($party_id == $party->id) selected="selected"  @endif  @endif value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                </select>
                {{-- <p style="font-size: 12px;"><a href="{{ route('party.create') }}">Add Party</a></p> --}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Reference Name</label>
                <input type="text" class="form-control" id="reference_name" name="reference_name" @if( isset($reference_name) ) value="{{ $reference_name }}" @endif>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Purchase Order Date</label>
                <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" id="purchase_order_date" name="purchase_order_date" @if( isset($date) ) value="{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}" @endif />
                <p id="purchase_order_date_validation_error" style="font-size: 12px; color: red;"></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Purchase Order No.</label>
                <input type="text" class="form-control" id="purchase_token" name="purchase_token" value="{{ $purchase_order_no }}" required @if(isset(auth()->user()->purchaseOrderSetting) && auth()->user()->purchaseOrderSetting->bill_no_type == 'auto') readonly @endif />
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Bill No.</label>
                <input id="bill_no" type="text" class="form-control" name="bill_no" value="{{ old('bill_no') }}" required>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        {{-- <th>#</th> --}}
                        <th>Product</th>
                        <th>Qty</th>
                        <th></th>
                        <th>Rate</th>
                        <th>Value</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>
                <tbody id="dynamic-body">
                    @foreach( $purchase_orders as $purchase_order )
                    <tr> 
                        <td style="min-width: 155px">
                            {{-- <select class="form-control item" name="item[]" required>
                                @foreach($items as $item)
                                    <option  @if( isset($purchase_order->item_id) ) @if($purchase_order->item_id == $item->id) selected="selected"  @endif @endif value="{{ $item->id }}">{{ $item->name }}</option>    
                                @endforeach
                            </select> --}}
                            <input type="hidden" name="item[]" value="{{ $purchase_order->item_id }}" />
                            <input type="text" class="form-control item_search" placeholder="Product" value="{{ $purchase_order->item_name }}" />
                            <div class="auto"></div>
                        </td>
                        <td>
                            <input type="text" class="form-control quantity" name="quantity[]" @if(isset($purchase_order->qty)) value="{{ $purchase_order->qty }}"  @endif required>
                        </td>
                        <td class="quantity-col" style="min-width: 142px">
                            <select name="measuring_unit[]" class="form-control select-measuring-unit">
                                <option>Select Unit</option>
                                @if( isset($purchase_order->base_unit) )
                                    <option @if($purchase_order->unit == $purchase_order->base_unit) selected="selected" @endif value="{{ $purchase_order->base_unit }}">{{  $purchase_order->base_unit }}</option>
                                @endif

                                @if( isset($purchase_order->alternate_unit) )
                                    <option @if($purchase_order->unit == $purchase_order->alternate_measuring_unit) selected="selected" @endif value="{{ $purchase_order->alternate_unit }}">{{  $purchase_order->alternate_unit }}</option>
                                @endif

                                @if( isset($purchase_order->compound_unit) )
                                    <option @if($purchase_order->unit == $purchase_order->compound_unit) selected="selected" @endif value="{{ $purchase_order->compound_unit }}">{{  $purchase_order->compound_unit }}</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control rate" name="rate[]" @if(isset($purchase_order->rate)) value="{{ $purchase_order->rate }}"  @endif required>
                        </td>
                        <td>
                            <input type="text" class="form-control value" name="value[]" @if(isset($purchase_order->value)) value="{{ $purchase_order->value }}"  @endif required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm btn-mine update-order" data-row="{{ $purchase_order->id }}">Edit</button>
                        </td>
                        {{-- <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td> --}}
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- <div class="form-group">
                <button type="button" id="add-more-items" class="btn btn-success">+ Add More</button>
            </div> --}}
        </div>
    
        {{-- <div class="form-group">
            <div class="col-md-8 col-md-offset-4">
                <button type="submit" class="btn btn-primary">
                    Update Sale Order
                </button>
            </div>
        </div> --}}
        {{-- </form> --}}
        <form method="POST" action="{{ route('update.purchase.order.remains', $purchase_order_no) }}">
        {{ csrf_field() }}
        <div class="col-md-5">
            <input type="hidden" name="selected_party_id" @if( isset($party_id) ) value="{{ $party_id }}" @endif />
            <input type="hidden" name="selected_order_date" @if( isset($date) ) value="{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}" @endif />
            <input type="hidden" name="selected_reference" @if( isset($reference_name) ) value="{{ $reference_name }}" @endif />
            <div class="form-group">
                <label>Mode of Payment</label><br />
                <div class="row">
                    <div class="col-md-3">
                        <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" @if($purchase_order->cash_amount > 0 ) checked @endif /> <label for="cash">Cash</label>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group" id="cash-list" @if($purchase_order->cash_amount == 0) style="display: none;" @endif >
                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" value="{{ $purchase_order->cash_amount }}" />
                            <hr/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" @if($purchase_order->bank_amount > 0) checked @endif /> <label for="bank">Bank</label>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group" id="bank-list" @if($purchase_order->bank_amount == 0) style="display: none;" @endif>
                            <div class="form-group">
                                <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" value="{{ $purchase_order->bank_amount }}" />
                            </div>
                            <div class="form-group">
                            <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" value="{{ $purchase_order->bank_cheque }}" />
                            </div>
                            <div class="form-group">
                                <label>Bank List</label>
                                <select class="form-control" name="bank">
                                    @if(count($banks) > 0)
                                        @foreach($banks as $bank)
                                            <option @if($purchase_order->bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <hr/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" @if($purchase_order->pos_amount > 0) checked @endif /> <label for="pos">POS</label>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group" id="pos-bank-list" @if($purchase_order->pos_amount == 0) style="display: none;" @endif>
                            <div class="form-group">
                            <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" value="{{ $purchase_order->pos_amount }}" />
                            </div>
                            <div class="form-group">
                                <label>POS Bank List</label>
                                <select class="form-control" name="pos_bank">
                                    @if(count($banks) > 0)
                                        @foreach($banks as $bank)
                                            <option @if($purchase_order->pos_bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <hr/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Amount Received</label>
                            <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Received" value="{{ $amount_received }}" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{-- <label>Amount Remaining</label> --}}
                            <input class="form-control" type="hidden" id="amount_remaining" name="amount_remaining" placeholder="Amount Remaining" value="{{ $amount_remaining }}" readonly />
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <textarea class="form-control" name="narration" placeholder="Narration"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" id="edit_purchase_order" class="btn btn-success btn-mine">
                                Save Purchase Order
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <div class="col-md-4 col-md-offset-3">
            <div class="form-group">
                <label>Total Amount</label>
                <input class="form-control" type="text" id="total_amount" name="total_amount" placeholder="Total Amount" value="{{ $total_amount }}" readonly />
            </div>
        </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
    <script>

        $(document).ready(function (){

            $("#purchase_order_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "purchase_order_date_validation_error", "#", "edit_purchase_order", "#");
            });

            $(document).on("keyup", ".quantity", function () {

                var tr = $(this).closest('tr');
                var qty = $(this).val();
                var rate = tr.find('.rate').val();

                if( qty == '' ){
                    qty = 0;
                }

                if( rate == '' ){
                    rate = 0;
                }

                var value = parseFloat(qty) * parseFloat(rate);
                tr.find('.value').val(value);

                var totalAmount = getAmountTotals();
                var amountReceived = getAmountReceived();
                var calculatedAmountRemaining = calculateAmountRemaining(totalAmount, amountReceived);

                setTotalAmount(totalAmount);
                setAmountRemaining(calculatedAmountRemaining);

            });

            $(document).on("keyup", ".rate", function () {

                var tr = $(this).closest('tr');
                var rate = $(this).val();
                var qty = tr.find('.quantity').val();

                if( qty == '' ){
                    qty = 0;
                }

                if( rate == '' ){
                    rate = 0;
                }

                var value = parseFloat(qty) * parseFloat(rate);
                tr.find('.value').val(value);

                var totalAmount = getAmountTotals();
                var amountReceived = getAmountReceived();
                var calculatedAmountRemaining = calculateAmountRemaining(totalAmount, amountReceived);

                setTotalAmount(totalAmount);
                setAmountRemaining(calculatedAmountRemaining);

            });

            $(document).on("click", ".update-order", function () {
                var tr = $(this).closest("tr");

                var row_id = $(this).attr("data-row");

                var item = tr.find('input[name="item[]"]').val();
                var qty = tr.find(".quantity").val();
                var unit = tr.find('select[name="measuring_unit[]"] option:selected').val();
                var rate = tr.find(".rate").val();
                var value = tr.find(".value").val();

                console.log(unit);

                $.ajax({
                    type: "post",
                    url: "{{ route('update.purchase.order') }}",
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "id": row_id,
                        "item_id": item,
                        "qty": qty,
                        "unit": unit,
                        "rate": rate,
                        "value": value
                    },
                    success: function(response){
                        console.log(response);

                        if( response == "success" ){
                            alert("Data updated successfully");
                            location.reload();
                        } else {
                            alert("Failed to update data");
                        }
                    }
                });
            });

            $(document).on("click", ".insert-order", function () {

                var tr = $(this).closest("tr");

                var token = $(this).attr("data-token");

                var item = tr.find(".item").val();

                var qty = tr.find(".quantity").val();

                var party = $("#party").val();

                var date = $("#purchase_order_date").val();

                var reference = $("#reference_name").val();

                $.ajax({
                    type: "post",
                    url: "{{ route('store.purchase.order.single.row') }}",
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "token": token,
                        "item_id": item,
                        "qty": qty,
                        "party_id": party,
                        "date": date,
                        "reference": reference
                    },
                    success: function(response){
                        console.log(response);

                        if( response == "success" ){
                            alert("Data inserted successfully");
                            location.reload();
                        } else {
                            alert("Failed to insert data");
                        }
                    }
                });

            });



            $(document).on("submit", "#create-sale", function(e){
                // e.preventDefault();
                var validation = true;

                var submit_quantities = $(".quantity");
                var submit_items = $(".item");

                var submit_party = $("#party option:selected").val();

                for(var j = 0; j < submit_quantities.length; j++){
                    if(submit_quantities[j].value == ""){
                        validation = false;
                    }
                }

                for(var k = 0; k < submit_items.length; k++){
                    if(submit_items[k].value == ""){
                        validation = false;
                    }
                }

                if(submit_party == 0){
                    validation = false;
                }


                if(validation) {
                    $("#create-sale").trigger( "submit" );

                    // alert('submit');
                } else {
                    e.preventDefault();
                    alert("Please fill up all required fields.");
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

            // $(document).on("click", "#add-more-items", function(){
            //     $("#dynamic-body").append(
            //         `<tr>
            //             <td>
            //                 <select class="form-control item" name="item[]" required>
            //                     @foreach($items as $item)
            //                         <option value="{{ $item->id }}">{{ $item->name }}</option>    
            //                     @endforeach     
            //                 </select>
            //             </td>
            //             <td>
            //                 <input type="text" class="form-control quantity" name="quantity[]" required>
            //             </td>
            //             <td>
            //                 <button type="button" class="btn btn-primary insert-order" data-token="{{ $purchase_order->token }}">Insert</button>
            //             </td>
            //             <td>
            //                 <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
            //             </td>
            //         </tr>`
            //     );
            // });

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
                    }
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

            $(document).on("click", ".delete-row", function(){

                $(this).parent().parent().remove();

                var totalAmount = getAmountTotals();
                var amountReceived = getAmountReceived();
                var calculatedAmountRemaining = calculateAmountRemaining(totalAmount, amountReceived);

                setTotalAmount(totalAmount);
                setAmountRemaining(calculatedAmountRemaining);

            });

            $("#party").on("change", function() {
                if($(this).val() == 0){
                    alert("Please select a valid party");
                    $("#edit_purchase_order").attr("disabled", true);
                } else {
                    $("#edit_purchase_order").attr("disabled", false);
                    $('input[name="selected_party_id"]').val($(this).val());
                }
            });

            $("#purchase_order_date").on("keyup", function(){
                if($(this).val() == ""){
                    alert("Please select a valid date in (dd/mm/yyyy) format");
                    $("#edit_purchase_order").attr("disabled", true);
                } else {
                    $("#edit_purchase_order").attr("disabled", false);
                    $('input[name="selected_order_date"]').val($(this).val());
                }
            });

            $("#amount_paid").on("keyup", function() {
                var totalAmount = getAmountTotals();
                var amountReceived = getAmountReceived();
                var calculatedAmountRemaining = calculateAmountRemaining(totalAmount, amountReceived);

                setAmountRemaining(calculatedAmountRemaining);
            });

            $("#reference_name").on("keyup", function(){
                $('input[name="selected_reference"]').val($(this).val());
            });


            $(document).on("keyup", ".item_search", function() {

                var key_to_search = $(this).val();
                var tr = $(this).closest('tr');

                autocomplete( key_to_search, tr );

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
                                tr.find('.auto').prepend(`<div data-value_id="${outWords[x].id}" data-value_name="${outWords[x].name}" data-value_hsc="${outWords[x].hsc_code}" data-value_sac="${outWords[x].sac_code}" data-value_gst="${outWords[x].gst}" data-value_price="${outWords[x].sale_price}" data-value_unit="${outWords[x].measuring_unit}" data-value_alt_unit="${outWords[x].alternate_measuring_unit}" data-value_comp_unit="${outWords[x].compound_measuring_unit}" data-value_has_free_qty="${outWords[x].free_qty}" >${outWords[x].name}</div>`); //Fills the .auto div with the options
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
                var searched_value_alt_unit = $(this).attr('data-value_alt_unit');
                var searched_value_comp_unit = $(this).attr('data-value_comp_unit');

                var searched_value_free_unit = $(this).attr('data-value_has_free_qty');

                if(searched_value_price == "null" || searched_value_price == "" || searched_value_price == NaN){
                    searched_value_price = 0;

                    // console.log(searched_value_price);
                }
                

                // if(status_of_registration == 0){
                //     var searched_value_gst = 0;
                // } else {
                //     var searched_value_gst = $(this).attr('data-value_gst');
                // }

                var tr = $(this).closest('tr');

                // console.log(".auto div", tr);
                // console.log(searched_value_name);

                tr.find(".select-measuring-unit").html('');

                // console.log(searched_value_unit);
                // console.log(typeof searched_value_alt_unit);
                // console.log(typeof searched_value_comp_unit);

                if(searched_value_unit != "null"){
                    tr.find(".select-measuring-unit").append(`
                        <option value="${searched_value_unit}">${searched_value_unit}</option>
                    `);
                }

                if(searched_value_alt_unit != "null"){
                    tr.find(".select-measuring-unit").append(`
                        <option value="${searched_value_alt_unit}">${searched_value_alt_unit}</option>
                    `);
                }

                if(searched_value_comp_unit != "null"){
                    tr.find(".select-measuring-unit").append(`
                        <option value="${searched_value_comp_unit}">${searched_value_comp_unit}</option>
                    `);
                }

                // if(searched_value_free_unit == "yes"){
                //     tr.find(".free-quantity-col").css('visibility', 'visible');
                // }

                $('.auto').html('');
                $('.auto').removeClass('active');

                tr.find(".add-more-info").show();
                tr.find(".add-more-info").attr('data-item', searched_value_id);

                tr.find(".item_search").val(searched_value_name);
                tr.find('input[name="item[]"]').val(searched_value_id);
                tr.find(".item").val(searched_value_id);
                tr.find(".price").val(searched_value_price);
                tr.find(".item").attr('data-hsc', searched_value_hsc);
                tr.find(".item").attr('data-sac', searched_value_sac);
                tr.find(".item").attr('data-gst', searched_value_gst);
                tr.find(".calculated-gst").text("0");

                setTimeout(function(){ tr.find(".item").trigger("change"); }, 1000);
            });

            function getAmountTotals() {
                let totalAmount = 0;

                $('input[name="value[]"]').each(function(i, v) {
                    totalAmount += parseFloat(v.value);
                });

                return totalAmount;
            }

            function getAmountReceived() {
                let totalAmountReceived = $("#amount_paid").val();

                if(totalAmountReceived == ''){
                    totalAmountReceived = 0;
                }

                return totalAmountReceived;
            }

            function calculateAmountRemaining(totalAmount, amountReceived) {
                if(totalAmount == ''){
                    totalAmount = 0;
                }

                if(amountReceived == ''){
                    amountReceived = 0;
                }

                return parseFloat(totalAmount) - parseFloat(amountReceived);
            }

            function setTotalAmount(totalAmount){
                if(totalAmount == ''){
                    totalAmount = 0;
                }
                $("#total_amount").val(totalAmount);
            }

            function setAmountRemaining(calculatedAmountRemaining){
                if(calculatedAmountRemaining == ''){
                    calculatedAmountRemaining = 0;
                }
                $("#amount_remaining").val(calculatedAmountRemaining);
            }

        });
    </script>
@endsection