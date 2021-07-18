@extends('layouts.dashboard')

<style>
    #document a[aria-expanded="false"]::before, #document a[aria-expanded="true"]::before, #document a[aria-expanded="true"]::before {
        content: ''
    }

</style>

@section('content')

{{-- {!! Breadcrumbs::render('gst-setoff') !!} --}}

<div class="container" id="document">
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            Pending Payment
                        </div>
                        <div class="col-md-4">
                            {{-- <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period Table <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">Date</label>
                                            <input type="text" name="fix_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
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
                    @php
                        $url = $type == "sale" ? route("update.party.pending.receivable.detail", $payment->id) : route("update.party.pending.payable.detail", $payment->id);
                    @endphp
                    <form id="form-note" method="post" action="{{ $url }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="party_id" name="party_id" value="{{ $payment->party_id }}" />
                        {{-- <div class="form-group">
                            <label>Amount</label>
                            <input type="text" class="form-control" id="amount" name="amount" placeholder="Amount" />
                        </div> --}}
    
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( 
                                        $payment->type_of_payment == 'combined' || 
                                        $payment->type_of_payment == 'cash+pos+discount' || 
                                        $payment->type_of_payment == 'cash+bank+discount' || 
                                        $payment->type_of_payment == 'cash+discount' || 
                                        $payment->type_of_payment == 'cash+bank+pos' || 
                                        $payment->type_of_payment == 'bank+cash' || 
                                        $payment->type_of_payment == 'pos+cash' || 
                                        $payment->type_of_payment == 'cash' ) checked="checked" @endif @endif /> <label for="cash">Cash</label>
                                </div>
    
                                <div class="col-md-8">
                                    <div class="form-group" id="cash-list" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( 
                                        $payment->type_of_payment == 'combined' || 
                                        $payment->type_of_payment == 'cash+pos+discount' || 
                                        $payment->type_of_payment == 'cash+bank+discount' || 
                                        $payment->type_of_payment == 'cash+discount' || 
                                        $payment->type_of_payment == 'cash+bank+pos' || 
                                        $payment->type_of_payment == 'bank+cash' || 
                                        $payment->type_of_payment == 'pos+cash' || 
                                        $payment->type_of_payment == 'cash' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" value="{{ $payment->cash_payment }}" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( 
                                        $payment->type_of_payment == 'combined' || 
                                        $payment->type_of_payment == 'bank+pos+discount' || 
                                        $payment->type_of_payment == 'cash+bank+discount' || 
                                        $payment->type_of_payment == 'bank+discount' || 
                                        $payment->type_of_payment == 'cash+bank+pos' || 
                                        $payment->type_of_payment == 'bank+cash' || 
                                        $payment->type_of_payment == 'pos+bank' || 
                                        $payment->type_of_payment == 'bank' ) checked="checked" @endif @endif /> <label for="bank">Bank</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group" id="bank-list" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( $payment->type_of_payment == 'combined' || 
                                    $payment->type_of_payment == 'bank+pos+discount' || 
                                    $payment->type_of_payment == 'cash+bank+discount' || 
                                    $payment->type_of_payment == 'bank+discount' || 
                                    $payment->type_of_payment == 'cash+bank+pos' || 
                                    $payment->type_of_payment == 'bank+cash' || 
                                    $payment->type_of_payment == 'pos+bank' || 
                                    $payment->type_of_payment == 'bank' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" value="{{ $payment->bank_payment }}" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" value="{{ $payment->bank_cheque }}" />
                                        </div>
                                        <div class="form-group">
                                            <label>Bank List</label>
                                            <select class="form-control" name="bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" @if($payment->bank_id == $bank->id) selected="selected" @endif>{{ $bank->name }} ({{ $bank->branch }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( 
                                        $payment->type_of_payment == 'combined' || 
                                        $payment->type_of_payment == 'bank+pos+discount' || 
                                        $payment->type_of_payment == 'cash+pos+discount' || 
                                        $payment->type_of_payment == 'pos+discount' || 
                                        $payment->type_of_payment == 'cash+bank+pos' || 
                                        $payment->type_of_payment == 'pos+cash' || 
                                        $payment->type_of_payment == 'pos+bank' || 
                                        $payment->type_of_payment == 'pos' ) checked="checked" @endif @endif /> <label for="pos">POS</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group" id="pos-bank-list" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( 
                                        $payment->type_of_payment == 'combined' || 
                                        $payment->type_of_payment == 'bank+pos+discount' || 
                                        $payment->type_of_payment == 'cash+pos+discount' || 
                                        $payment->type_of_payment == 'pos+discount' || 
                                        $payment->type_of_payment == 'cash+bank+pos' || 
                                        $payment->type_of_payment == 'pos+cash' || 
                                        $payment->type_of_payment == 'pos+bank' || 
                                        $payment->type_of_payment == 'pos' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" value="{{ $payment->pos_payment }}" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" @if($payment->pos_bank_id == $bank->id) selected="selected" @endif>{{ $bank->name }} ({{ $bank->branch }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="checkbox" name="type_of_payment[]" value="discount" id="discount" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( 
                                        $payment->type_of_payment == 'combined' || 
                                        $payment->type_of_payment == 'bank+pos+discount' || 
                                        $payment->type_of_payment == 'cash+pos+discount' || 
                                        $payment->type_of_payment == 'cash+bank+discount' || 
                                        $payment->type_of_payment == 'bank+discount' || 
                                        $payment->type_of_payment == 'cash+discount' || 
                                        $payment->type_of_payment == 'pos+discount' || 
                                        $payment->type_of_payment == 'discount' ) checked="checked" @endif @endif /> <label for="discount">Cash Discount</label>
                                </div>
    
                                <div class="col-md-8">
                                    <div id="discount-list" class="form-group" @if(isset($payment->type_of_payment) && $payment->type_of_payment != "no_payment") @if( $payment->type_of_payment == 'combined' || 
                                    $payment->type_of_payment == 'bank+pos+discount' || 
                                    $payment->type_of_payment == 'cash+pos+discount' || 
                                    $payment->type_of_payment == 'cash+bank+discount' || 
                                    $payment->type_of_payment == 'bank+discount' || 
                                    $payment->type_of_payment == 'cash+discount' || 
                                    $payment->type_of_payment == 'pos+discount' || 
                                    $payment->type_of_payment == 'discount' ) style="display:block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <div class="form-group">
                                            <label>Discount Type</label>
                                            <select class="form-control" name="discount_type" id="discount_type">
                                                <option disabled selected>Discount Type</option>
                                                <option value="percent" @if($payment->discount_figure == 'percent') selected="selected" @endif>%</option>
                                                <option value="fixed" @if($payment->discount_figure == 'fixed') selected="selected" @endif>Rs</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Discount Figure" name="discount_figure" id="discount_figure" class="form-control" value="{{ $payment->discount_figure }}" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Discount Amount" name="discounted_amount" id="discounted_amount" class="form-control" readonly />
                                            <hr/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- <input type="hidden" name="tds_income_tax_checked" id="tds_income_tax_checked" />
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
                        </div> --}}
    
                        <div class="form-group">
                            <label>Total Amt.</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" value="{{ $payment->amount }}" readonly />
                        </div>
    
                        {{-- <div class="form-group">
                            <label>Voucher No.</label>
                            <input type="text" class="form-control" name="voucher_no" placeholder="Voucher No." required />
                        </div> --}}
                        
                        <div class="form-group">
                            <label>Voucher No.</label>
                            <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No." value="{{ $payment->voucher_no }}" readonly />
                        </div>
    
                        <div class="form-group">
                            <label>Payment Date</label>
                            <input type="text" class="form-control custom_date" id="payment_date" name="payment_date" placeholder="DD/MM/YYYY" autocomplete="off" value="{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}" required readonly />
                        </div>
    
                        <div class="form-group">
                            <label>Narration</label>
                            <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks">{{ $payment->remarks }}</textarea>
                        </div>

    
                        <button id="btn-add-payment-modal" type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
        
                </div>
                    

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
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
                    } else if(type_of_payment == 'discount') {
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

                var total_pending_payment_amount_in_modal = $("#total_pending_payment_amount_in_modal").text() || 8000; // some random amount for testing
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
                    var posed_amount = $("#posed_amount").val() || 0;
                    var cashed_amount = $("#cashed_amount").val() || 0;
                    var banked_amount = $("#banked_amount").val() || 0;
                    var discounted_amount = $("#discounted_amount").val() || 0;
                    var tds_income_tax = $("#tds_income_tax_amount").val() || 0;
                    var tds_gst = $("#tds_gst_amount").val() || 0;
                    var tcs_income_tax = $("#tcs_income_tax_amount").val() || 0;
                    var tcs_gst = $("#tcs_gst_amount").val() || 0;


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
