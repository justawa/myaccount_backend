@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('find-purchases-by-party') !!}
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
    <form action="{{ route('post.find.purchase.by.party') }}">
        <div class="form-group">
            <select class="form-control" name="party" id="party">
                <option value="0">Select Party</option>
                @foreach($parties as $party)
                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            Pending Payable
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form id="period_form">
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
                            </div>
                        </div>
                    </div>
                </div>
                <div style="padding-top: 15px; display: flex">
                    <div class="col-md-6 text-left" style="display: none;" id="total_pending_payment_block">
                        Balance: <span id="total_pending_payment_amount"></span>
                    </div>
                    <div class="col-md-6 text-right">
                        <button style="display: none;" class="btn btn-success" id="btn_payment_against_party" data-party="">Pay on Account</button>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                {{-- <th>#</th> --}}
                                <th>Voucher No</th>
                                <th>Bill Date</th>
                                <th>Total Amount</th>
                                <th>Remaining Amount</th>
                                <th>Pay Bill Wise</th>
                            </tr>
                        </thead>
                        <tbody id="dynamic-body">
                            <tr>
                                <td colspan="6" class="text-center">Select party to get data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="payment_against_party_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Payment</h4>
            </div>
            <div class="modal-body">
                <p><strong>Pending Payment: <span id="total_pending_payment_amount_in_modal"></span></strong></p>
                <form id="form-note" method="post" action="{{ route('add.pending.payment.to.party.purchase') }}">
                    {{ csrf_field() }}
                    <input type="hidden" id="party_id" name="party_id" value="" />
                    <input type="hidden" id="total_pending_payment_amount_input" name="pending_balance" />
                    {{-- <div class="form-group">
                        <label>Amount</label>
                        <input type="text" class="form-control" id="amount" name="amount" placeholder="Amount" />
                    </div> --}}

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
                                        <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" />
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
                                    <hr/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" value="discount" id="discount" /> <label for="discount">Discount</label>
                            </div>

                            <div class="col-md-9">
                                <div id="discount-list" class="form-group" style="display: none;">
                                    <div class="form-group">
                                        <label>Discount Type</label>
                                        <select class="form-control" name="discount_type" id="discount_type">
                                            <option disabled selected>Discount Type</option>
                                            <option value="percent">%</option>
                                            <option value="fixed">Rs</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" placeholder="Discount Figure" name="discount_figure" id="discount_figure" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <input type="text" placeholder="Discount Amount" name="discounted_amount" id="discounted_amount" class="form-control" readonly />
                                        <hr/>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <input type="hidden" name="tds_income_tax_checked" id="tds_income_tax_checked" />
                    <input type="hidden" name="tds_gst_checked" id="tds_gst_checked" />
                    <input type="hidden" name="tcs_income_tax_checked" id="tcs_income_tax_checked" />
                    <input type="hidden" name="tcs_gst_checked" id="tcs_gst_checked" />

                    <div class="form-group" id="tds_income_tax" style="display: none;">
                        <label>TDS Income Tax Amount</label>
                        <input type="text" class="form-control" id="tds_income_tax_amount" name="tds_income_tax_amount" placeholder="TDS Income Tax Amount" />
                    </div>
                    <div class="form-group" id="tds_gst" style="display: none;">
                        <label>TDS GST Amount</label>
                        <input type="text" class="form-control" id="tds_gst_amount" name="tds_gst" placeholder="TDS GST Amount" />
                    </div>
                    <div class="form-group" id="tcs_income_tax" style="display: none;">
                        <label>TCS Income Tax Amount</label>
                        <input type="text" class="form-control" id="tcs_income_tax_amount" name="tcs_income_tax" placeholder="TCS Income Tax Amount" />
                    </div>
                    <div class="form-group" id="tcs_gst" style="display: none;">
                        <label>TCS GST Amount</label>
                        <input type="text" class="form-control" id="tcs_gst_amount" name="tcs_gst" placeholder="TCS GST Amount" />
                    </div>

                    <div class="form-group">
                        <label>Total Amt.</label>
                        <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" readonly />
                    </div>

                    {{-- <div class="form-group">
                        <label>Voucher No.</label>
                        <input type="text" class="form-control" name="voucher_no" placeholder="Voucher No." required />
                    </div> --}}

                    @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                    <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                        <label>Voucher No.</label>
                        <input id="voucher_no" type="text" class="form-control" name="voucher_no" placeholder="Voucher No." @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @endif @endif @endif />
                        @if ($myerrors->has('voucher_no'))
                        <span class="help-block">
                            <ul>
                                @foreach( $myerrors['voucher_no'] as $error )
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </span>
                        @endif
                        <span id="bill_no_error_msg" style="font-size: 12px; color: red;"></span>
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="text" class="form-control custom_date" id="payment_date" name="payment_date" placeholder="DD/MM/YYYY" autocomplete="off" required>
                        <p id="payment_date_validation_error" style="font-size: 12px; color: red;"></p>
                    </div>

                    {{-- <div class="form-group">
                        <label>Narration</label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks"></textarea>
                    </div> --}}

                    {{-- <div class="form-group">
                        <label>Type of Payment</label><br />
                        <input type="radio" name="type_of_payment" value="cash" id="cash" checked /> <label for="cash">Cash</label>
                        <input type="radio" name="type_of_payment" value="bank" id="bank" /> <label for="bank">Bank</label>
                    </div>
                    <div class="form-group" id="bank-list" style="display: none;">
                        <div class="form-group">
                            <input type="text" name="bank_cheque" id="bank_cheque" class="form-control" placeholder="Bank Cheque No." />
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="bank" id="bank-select-list">
                                @if(count($banks) > 0)
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div> --}}

                    <button id="btn-add-payment-modal" type="submit" class="btn btn-success btn-mine">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>



@endsection

@section('scripts')
    <script>
        function roundToTwo(num) {
            return +(Math.round(num + "e+2")  + "e-2");
        }
        $(document).ready(function() {

            $("#payment_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "payment_date_validation_error", "#", "btn-add-payment-modal", "#");
            });

            $("#voucher_no").on("keyup", function() {
                var bill_no = $("#voucher_no").val() ? $("#voucher_no").val() : null;
                var party = $("#party option:selected").val() ? $("#party option:selected").val() : null;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            $("#party").on("change", function() {
                var bill_no = $("#voucher_no").val() ? $("#voucher_no").val() : null;
                var party = $("#party option:selected").val() ? $("#party option:selected").val() : null;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            function validateBillNo(party = null, bill_no = null, userId = null) {
                console.log(party, bill_no, userId);
                if(party && bill_no && userId){
                    $.ajax({
                        type: 'post',
                        url: "{{ route('api.validate.purchase.party.payment.voucherno') }}",
                        data: {
                            "token": bill_no,
                            "party": party,
                            "user": userId
                        },
                        success: function(response){
                            $('#btn-add-payment-modal').attr('disabled', false);
                            $("#bill_no_error_msg").text('');
                        },
                        error: function(err){
                            // console.log(err);
                            // console.log(err.responseJSON.errors);
                            if(err.status == 400){
                                $("#bill_no_error_msg").text(err.responseJSON.errors);
                                $('#btn-add-payment-modal').attr('disabled', true);
                            }
                        }
                    });
                }
            }

            $('input[name="type_of_payment"]').on("change", function(){
                var type_of_payment = $(this).val();

                if(type_of_payment == 'bank'){
                    $("#bank-list").show();
                } else {
                    $("#bank-list").hide();
                }
            });

            $(document).on("change", "#party", function() {
                var selected_one = $(this).val();
                // console.log(selected_one);
                getPartyData(selected_one);
            });

            $(document).on("submit", "#period_form", function(e) {
                e.preventDefault();
                var selected_one = $("#party option:selected").val();
                var from_date = $('input[name="from_date"]').val();
                var to_date = $('input[name="to_date"]').val();
                getPartyData(selected_one, from_date, to_date);
            });

            function getPartyData(selected_one, from_date = null, to_date = null) {
                if (selected_one > 0) {
                    $("#btn_payment_against_party").show();
                    $("#total_pending_payment_block").show();
                    $("#btn_payment_against_party").attr("data-party", selected_one);
                    $.ajax({
                        type: "post",
                        url: "{{ route('post.find.purchase.by.party') }}",
                        data: {
                            "selected_party": selected_one,
                            "from_date": from_date,
                            "to_date": to_date,
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response){

                            console.log(response);
                            $("#total_pending_payment_amount").text(roundToTwo(response.total_pending));
                            $("#total_pending_payment_amount_input").val(roundToTwo(response.total_pending));
                            $("#total_pending_payment_amount_in_modal").text(roundToTwo(response.total_pending));
                            $("#dynamic-body").html('');
                            if (response.purchase.length > 0) {
                                
                                for(var i = 0; i < response.purchase.length; i++){
                                    var html_row = '';
                                    if (response.purchase[i].remaining_amount == null) {
                                        html_row = 'NA';
                                    } else {
                                        html_row = response.purchase[i].remaining_amount.amount_remaining;
                                    }
                                    $("#dynamic-body").append(`
                                        <tr>
                                            <td><a href="purchase/edit/bill/${response.purchase[i].id}">${response.purchase[i].bill_no}</a></td>
                                            <td>${response.purchase[i].bill_date}</td>
                                            <td>${response.purchase[i].total_amount}</td>
                                            <td>${html_row}</td>
                                            <td><a href="get-bill-info/${response.purchase[i].id}/party/${response.purchase[i].party_id}">Add</a></td>
                                        </tr>
                                    `);
                                }
                            } else {
                                $("#dynamic-body").append(`
                                    <tr>
                                        <td colspan="6" class="text-center">No data</td>
                                    </tr>
                                `);
                            }
                        }
                    });
                } else {
                    alert('Please select a valid party');
                    $("#btn_payment_against_party").hide();
                    $("#dynamic-body").html('');
                    $("#dynamic-body").append(`
                        <tr>
                            <td colspan="6" class="text-center">Select party to get data</td>
                        </tr>
                    `);
                }
            }

            $("#btn_payment_against_party").click( function () {
                $("#payment_against_party_modal").modal("show");

                var party_id = $(this).attr("data-party");

                $("#party_id").val(party_id);

                $.ajax({
                    type: "post",
                    url: "{{ route('post.find.party.details') }}",
                    data: {
                        "selected_party": party_id,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response){

                        console.log(response);

                        if( response.tcs_gst ) {
                            $("#tcs_gst").show();
                            $("#tcs_gst_checked").val("1");
                        } else {
                            $("#tcs_gst").hide();
                            $("#tcs_gst_checked").val("0");
                        }

                        if( response.tcs_income_tax ) {
                            $("#tcs_income_tax").show();
                            $("#tcs_income_tax_checked").val("1");
                        } else {
                            $("#tcs_income_tax").hide();
                            $("#tcs_income_tax_checked").val("0");
                        }

                        if( response.tds_gst ) {
                            $("#tds_gst").show();
                            $("#tds_gst_checked").val("1");
                        } else {
                            $("#tds_gst").hide();
                            $("#tds_gst_checked").val("0");
                        }

                        if( response.tds_income_tax ) {
                            $("#tds_income_tax").show();
                            $("#tds_income_tax_checked").val("1");
                        } else {
                            $("#tds_income_tax").hide();
                            $("#tds_income_tax_checked").val("0");
                        }

                    }
                });

            });

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
                    } else if(type_of_payment == 'discount'){
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
                    } else if(type_of_payment == 'discount'){
                        $("#discount-list").hide();

                        $("#discount_figure").val(0);
                        $("#discount_figure").trigger("keyup");
                    }
                }
            });

            $("#discount_type").on("change", function () {
                calculate_discount();
            });

            $("#discount_figure").on("keyup", function () {
                calculate_discount();
            });

            function calculate_discount() {
                var discount_type = $("#discount_type option:selected").val();

                // console.log(discount_type);

                var total_pending_payment_amount_in_modal = $("#total_pending_payment_amount_in_modal").text();
                var discount_figure = $("#discount_figure").val();

                if(discount_figure == ''){
                    discount_figure = 0;
                }

                if(total_pending_payment_amount_in_modal == ''){
                    total_pending_payment_amount_in_modal = 0; 
                }

                if(discount_type == 'percent'){
                    discount_figure = (discount_figure * total_pending_payment_amount_in_modal) / 100;
                }

                $("#discounted_amount").val(discount_figure);
            }

            $("#posed_amount").on("keyup", function () {
                calculate_amount();
            });

            $("#cashed_amount").on("keyup", function () {
                calculate_amount();
            });

            $("#banked_amount").on("keyup", function () {
                calculate_amount();
            });

            $("#discount_type").on("change", function () {
                calculate_amount();
            });

            $("#discount_figure").on("keyup", function () {
                calculate_amount();
            });

            $("#tds_income_tax_amount").on("keyup", function () {
                calculate_amount();
            });

            $("#tds_gst").on("keyup", function () {
                calculate_amount();
            });

            $("#tcs_income_tax").on("keyup", function () {
                calculate_amount();
            });

            $("#tcs_gst").on("keyup", function () {
                calculate_amount();
            });

            function calculate_amount() {
                var posed_amount = $("#posed_amount").val();
                var cashed_amount = $("#cashed_amount").val();
                var banked_amount = $("#banked_amount").val();
                var discounted_amount = $("#discounted_amount").val();
                var tds_income_tax = $("#tds_income_tax_amount").val();
                var tds_gst = $("#tds_gst_amount").val();
                var tcs_income_tax = $("#tcs_income_tax_amount").val();
                var tcs_gst = $("#tcs_gst_amount").val();


                // console.log("cashed_amount ", cashed_amount);
                // console.log("banked_amount", banked_amount);
                // console.log("posed_amount", posed_amount);
                // console.log("discounted_amount", discounted_amount);
                // console.log("tds_income_tax", tds_income_tax);
                // console.log("tds_gst", tds_gst);
                // console.log("tcs_income_tax", tcs_income_tax);
                // console.log("tcs_gst", tcs_gst);

                if( cashed_amount == '' ) {
                    cashed_amount = 0;
                }

                if( banked_amount == '' ) {
                    banked_amount = 0;
                }

                if( posed_amount == '' ) {
                    posed_amount = 0;
                }

                if( discounted_amount == '' ) {
                    discounted_amount = 0;
                }

                if(tds_income_tax == '') {
                    tds_income_tax = 0;
                }

                if(tds_gst == '') {
                    tds_gst = 0;
                }

                if(tcs_income_tax == '') {
                    tcs_income_tax = 0;
                }

                if(tcs_gst == '') {
                    tcs_gst = 0;
                }

                var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount) + parseFloat(discounted_amount) + parseFloat(tds_income_tax) + parseFloat(tds_gst) + parseFloat(tcs_income_tax) + parseFloat(tcs_gst);

                $("#total_amount").val(amount_paid);
            }
        });
    </script>
@endsection
