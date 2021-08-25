@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('create-sale-order') !!}
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
    <form method="POST" action="{{ route('store.sale.order') }}" id="create-sale-order">
        {{ csrf_field() }}
        <input type="hidden" name="submit_type" id="submit_type" />
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Choose a party</label>
                    <select class="form-control" id="party" name="party" required>
                        <option value="0">Choose a party</option>
                        <option onclick="location.href = '{{ route('party.create') }}'">Add Party</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->name }}</option>
                        @endforeach
                    </select>
                    {{-- <p style="font-size: 12px;"><a href="{{ route('party.create') }}">Add Party</a></p> --}}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Reference Name</label>
                    <input type="text" class="form-control" name="reference_name" >
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Quotation/Order Date</label>
                    <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" id="sale_order_date" name="sale_order_date" autocomplete="off" required />
                    <p id="date_validation_error" style="font-size: 12px; color: red;"></p>
                </div>
            </div>
            <div class="col-md-3">
                @php $showErrors = $myerrors->has('token_no') ? $myerrors->has('token_no') : $errors->has('token_no') @endphp
                <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                    <label>Quotation/Order No.</label>
                    <input type="text" class="form-control" id="sale_token" name="sale_token" @if ( $myerrors->has('token_no') ) required @else @if($errors->has('token_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->saleOrderSetting) && auth()->user()->saleOrderSetting->bill_no_type == 'auto') value="{{ $order_no + 1 }}" readonly @endif @endif @endif required />
                    @if ($myerrors->has('token_no'))
                    <span class="help-block">
                        <ul>
                            @foreach( $myerrors['token_no'] as $error )
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </span>
                    @endif
                    <p id="bill_no_error_msg" style="color: red; font-size: 12px;"></p>
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
            {{-- <div class="col-md-3">
                <a href="{{ route('group.create') }}" class="btn btn-success">Add Group</a>&nbsp;&nbsp;&nbsp;
                <a href="{{ route('item.create') }}" class="btn btn-success">Add Item</a>
            </div> --}}
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            {{-- <th>#</th> --}}
                            {{-- <th>Group</th> --}}
                            <th>Product/Item</th>
                            <th>Quantity</th>
                            <th></th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="dynamic-body">
                        <tr>
                            {{-- <td>1</td> --}}
                            <td>
                                {{-- <select class="form-control item" name="item[]" required>
                                </select> --}}
                                <input type="hidden" name="item[]" />
                                <input type="text" class="form-control item_search" placeholder="Product" />
                                <div class="auto"></div>
                            </td>
                            <td>
                                <input type="text" class="form-control quantity" name="quantity[]" required>
                            </td>
                            <td class="quantity-col" style="min-width: 142px">
                                <select name="measuring_unit[]" class="form-control select-measuring-unit">
                                    <option>Select Unit</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control rate" name="rate[]" required>
                            </td>
                            <td>
                                <input type="text" class="form-control value" name="value[]" readonly required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group">
                    <button type="button" id="add-more-items" class="btn btn-success">+ Add More</button>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="form-group">
                    <label>Mode of Payment</label><br />
                    <div class="row">
                        <div class="col-md-3">
                            <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" /> <label for="cash">Cash</label>
                        </div>

                        <div class="col-md-9">
                            <div class="form-group" id="cash-list" style="display: none;">
                                <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" />
                                <hr/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" /> <label for="bank">Bank</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group" id="bank-list" style="display: none;">
                                <div class="form-group">
                                    <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Bank List</label>
                                    <select class="form-control" name="bank">
                                        @if(count($banks) > 0)
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" autocomplete="off" />
                                </div>
                                <hr/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" /> <label for="pos">POS</label>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group" id="pos-bank-list" style="display: none;">
                                <div class="form-group">
                                    <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>POS Bank List</label>
                                    <select class="form-control" name="pos_bank">
                                        @if(count($banks) > 0)
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" autocomplete="off" />
                                </div>
                                <hr/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount Received</label>
                                <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Received" readonly />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{-- <label>Amount Remaining</label> --}}
                                <input class="form-control" type="hidden" id="amount_remaining" name="amount_remaining" placeholder="Amount Remaining" readonly />
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
                </div>
            </div>
            <div class="col-md-4 col-md-offset-3">
                <div class="form-group">
                    <label>Total Amount</label>
                    <input class="form-control" type="text" id="total_amount" name="total_amount" placeholder="Total Amount" readonly />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <button type="button" name="save" class="btn btn-success btn-mine create-order">
                    Save
                </button>
            </div>

            <div class="col-md-4">
                <button type="button" name="save_and_print" class="btn btn-success btn-mine create-order">
                    Save & Print
                </button>
            </div>

            <div class="col-md-4">
                <button type="button" name="save_and_send" class="btn btn-success btn-mine create-order">
                    Save & Send
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
    <script>

        $(document).ready(function (){

            $("#sale_order_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "date_validation_error", "#", "create-order", ".");
            });

            $("#sale_token").on("keyup", function() {
                var bill_no = $("#sale_token").val() ? $("#sale_token").val() : undefined;
                var party = $("#party option:selected").val() ? $("#party option:selected").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            $("#party").on("change", function() {
                var bill_no = $("#sale_token").val() ? $("#sale_token").val() : undefined;
                var party = $("#party option:selected").val() ? $("#party option:selected").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            function validateBillNo(party = undefined, bill_no = undefined, userId = undefined) {
                console.log(party, bill_no, userId);
                if(party && bill_no && userId){
                    $.ajax({
                        type: 'post',
                        url: "{{ route('api.validate.saleorderno') }}",
                        data: {
                            "token": bill_no,
                            "party": party,
                            "user": userId
                        },
                        success: function(response){
                            $('button[type="submit"]').attr('disabled', false);
                            $("#bill_no_error_msg").text('');
                        },
                        error: function(err){
                            // console.log(err);
                            // console.log(err.responseJSON.errors);
                            if(err.status == 400){
                                $("#bill_no_error_msg").text(err.responseJSON.errors);
                                $('button[type="submit"]').attr('disabled', true);
                            }
                        }
                    });
                }
            }

            $(document).on("click", ".create-order", function(e){
                $("#submit_type").val($(this).attr("name"));
                validateOrderForm(e);
            });

            function validateOrderForm(e) {
                var validation = true;
                $(".create-order").attr("disabled", true);
                // console.log("create sale order");
                // console.log("------------");

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
                    // $("#create-sale-order").trigger("submit");

                    $("#create-sale-order").submit();

                    // alert('submit');
                } else {
                    e.preventDefault();
                    show_custom_alert("Please fill up all required fields.", "red");
                    $(".create-order").attr("disabled", false);
                }
            }

            $(document).on("change", ".group", function(){
                var group = $("option:selected", this).val();
                var tr = $(this).closest('tr');

                tr.find(".item").html('');

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

            $(document).on("click", "#add-more-items", function(){
                $("#dynamic-body").append(
                    `<tr>
                        <td>
                            <input type="hidden" name="item[]" />
                            <input type="text" class="form-control item_search" placeholder="Product" />
                            <div class="auto"></div>
                        </td>
                        <td>
                            <input type="text" class="form-control quantity" name="quantity[]" required>
                        </td>
                        <td class="quantity-col" style="min-width: 142px">
                            <select name="measuring_unit[]" class="form-control select-measuring-unit">
                                <option>Select Unit</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control rate" name="rate[]" required>
                        </td>
                        <td>
                            <input type="text" class="form-control value" name="value[]" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>`
                );
            });

            $(document).on("click", ".delete-row", function(){

                $(this).parent().parent().remove();
                
                var totalAmount = getAmountTotals();
                var amountReceived = getAmountReceived();
                var calculatedAmountRemaining = calculateAmountRemaining(totalAmount, amountReceived);

                setTotalAmount(totalAmount);
                setAmountRemaining(calculatedAmountRemaining);
            });


            // $(".create-order").on("click", function() {
            //     $(".create-order").attr("disabled", true);
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

            $("#amount_paid").on("keyup", function() {
                var totalAmount = getAmountTotals();
                var amountReceived = getAmountReceived();
                var calculatedAmountRemaining = calculateAmountRemaining(totalAmount, amountReceived);

                setAmountRemaining(calculatedAmountRemaining);
            });

            $(document).on("keyup", "#sale_token", function() {

                var key_to_search = $(this).val();
                // var tr = $(this).closest('tr');

                autocomplete( key_to_search );

            });

            function autocomplete( key_to_search ) {
                if(key_to_search == ''){
                    key_to_search = 1;
                    $('.auto').removeClass('active');
                }
                $.ajax({
                    "type": "POST",
                    "url": "{{ route('api.search.new.sale.order.name') }}",
                    "data": {
                        "key_to_search": key_to_search,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(data){

                        // console.log(data);
                        var outWords = data;
                        if(outWords.length > 0) {
                            $('.auto').html('');

                            for(x = 0; x < outWords.length; x++){
                                $('.auto').prepend(`<div data-value="${outWords[x]}" >${outWords[x]}</div>`); //Fills the .auto div with the options
                            }

                            $('.auto').addClass('active');

                        }
                    }
                });
            }

            //$(document).on('click', '.auto div', function(){
            //    var searched_value = $(this).attr('data-value');

            //    var tr = $(this).closest('tr');

            //    $('.auto').html('');
            //    $('.auto').removeClass('active');

                // tr.find(".add-more-info").show();
                // tr.find(".add-more-info").attr('data-item', searched_value);

            //    $("#sale_token").val(searched_value);


                // setTimeout(function(){ tr.find(".item").trigger("change"); }, 1000);
            //});

            $("#sale_order_date").on( "keyup", function () {
                $("#bank_payment_date").val($(this).val());
                $("#pos_payment_date").val($(this).val());
            } );


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
                    const val = v.value ? v.value : 0;
                    totalAmount += parseFloat(val);
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
