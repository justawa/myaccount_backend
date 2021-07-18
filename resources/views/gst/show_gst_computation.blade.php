@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-computation') !!}

<div class="container">

    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <form class="form-horizontal" action="{{ route('gst.computation') }}" method="get">
                <div class="form-group">
                    <div class="col-md-5">
                        <select class="form-control" name="month">
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "01" ) ) selected="selected" @endif @endif value="01">January</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "02" ) ) selected="selected" @endif @endif value="02">February</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "03" ) ) selected="selected" @endif @endif value="03">March</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "04" ) ) selected="selected" @endif @endif value="04">April</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "05" ) ) selected="selected" @endif @endif value="05">May</option>
                            <option  @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "06" ) ) selected="selected" @endif @endif value="06">June</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "07" ) ) selected="selected" @endif @endif value="07">July</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "08" ) ) selected="selected" @endif @endif value="08">August</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "09" ) ) selected="selected" @endif @endif value="09">September</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "10" ) ) selected="selected" @endif @endif value="10">October</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "11" ) ) selected="selected" @endif @endif value="11">November</option>
                            <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "12" ) ) selected="selected" @endif @endif value="12">December</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" name="year">
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2018" ) ) selected="selected" @endif @endif value="2018">2018</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2019" ) ) selected="selected" @endif @endif value="2019">2019</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2020" ) ) selected="selected" @endif @endif value="2020">2020</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2021" ) ) selected="selected" @endif @endif value="2021">2021</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2022" ) ) selected="selected" @endif @endif value="2022">2022</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2023" ) ) selected="selected" @endif @endif value="2023">2023</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2024" ) ) selected="selected" @endif @endif value="2024">2024</option>
                            <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2025" ) ) selected="selected" @endif @endif value="2025">2025</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success btn-block">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">GST Computation</div>
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

                    @if( isset( $_GET['month'] ) )
                        @php $month = app('request')->input('month') @endphp
                    @else
                        @php $month = 'all'; @endphp
                    @endif

                    @if( isset( $_GET['year'] ) )
                        @php $year = app('request')->input('year') @endphp
                    @else
                        @php $year = 'all'; @endphp
                    @endif

                    <form method="POST" action="{{ route('post.gst.computation') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="post_month" value="{{ $month }}" />
                    <input type="hidden" name="post_year" value="{{ $year }}" />
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>GST Output</th>
                                <th>GST Input</th>
                                <th>GST Paid in Cash</th>
                                <th>GST Payable</th>
                                <th>GST Payable under Reverse Charge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $cgst_payable = ($invoice_cgst - $purchase_cgst) + $cash_gst->cgst;
                            @endphp
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" name="invoice_cgst" id="invoice_cgst_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_cgst" id="purchase_cgst_value" /></td>
                                <td><input type="text" class="form-control" name="cash_cgst" id="cash_gst_cgst_value" /></td>
                                <td><input type="text" class="form-control" name="payable_cgst" id="payable_cgst_value" /></td>
                                <td></td>
                            </tr>
                            @php
                                $sgst_payable = ($invoice_sgst - $purchase_sgst) + $cash_gst->sgst;
                            @endphp
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" name="invoice_sgst" id="invoice_sgst_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_sgst" id="purchase_sgst_value" /></td>
                                <td><input type="text" class="form-control" name="cash_sgst" id="cash_gst_sgst_value" /></td>
                                <td><input type="text" class="form-control" name="payable_sgst" id="payable_sgst_value" /></td>
                                <td></td>
                            </tr>
                            @php
                                $igst_payable = ($invoice_igst - $purchase_igst) + $cash_gst->igst;
                            @endphp
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" name="invoice_igst" id="invoice_igst_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_igst" id="purchase_igst_value" /></td>
                                <td><input type="text" class="form-control" name="cash_igst" id="cash_gst_igst_value" /></td>
                                <td><input type="text" class="form-control" name="payable_igst" id="payable_igst_value" /></td>
                                <td></td>
                            </tr>
                            @php
                                $cess_payable = ($invoice_cess - $purchase_cess) + $cash_gst->cess;
                            @endphp
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" name="invoice_cess" id="invoice_cess_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_cess" id="purchase_cess_value" /></td>
                                <td><input type="text" class="form-control" name="cash_cess" id="cash_gst_cess_value" /></td>
                                <td><input type="text" class="form-control" name="payable_cess" id="payable_cess_value" /></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Interest</th>
                                <td><input type="text" class="form-control" name="invoice_interest" id="invoice_interest_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_interest" id="purchase_interest_value" /></td>
                                <td><input type="text" class="form-control" name="cash_interest" id="cash_gst_interest_value" /></td>
                                <td><input type="text" class="form-control" name="payable_interest" id="payable_interest_value" /></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Late Fees</th>
                                <td><input type="text" class="form-control" name="invoice_late_fees" id="invoice_late_fees_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_late_fees" id="purchase_late_fees_value" /></td>
                                <td><input type="text" class="form-control" name="cash_late_fees" id="cash_gst_late_fees_value" /></td>
                                <td><input type="text" class="form-control" name="payable_late_fees" id="payable_late_fees_value" /></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Penalty</th>
                                <td><input type="text" class="form-control" name="invoice_penalty" id="invoice_penalty_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_penalty" id="purchase_penalty_value" /></td>
                                <td><input type="text" class="form-control" name="cash_penalty" id="cash_gst_penalty_value"  /></td>
                                <td><input type="text" class="form-control" name="payable_penalty" id="payable_penalty_value" /></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Other</th>
                                <td><input type="text" class="form-control" name="invoice_other_charge" id="invoice_other_charge_value" /></td>
                                <td><input type="text" class="form-control" name="purchase_other_charge" id="purchase_other_charge_value" /></td>
                                <td><input type="text" class="form-control" name="cash_other_charge" id="cash_gst_other_charge_value" /></td>
                                <td><input type="text" class="form-control" name="payable_other_charge" id="payable_other_charge_value" /></td>
                                <td></td>
                            </tr>
                            @php
                                $cash_gst_paid = $cash_gst->cgst + $cash_gst->sgst + $cash_gst->igst + $cash_gst->cess + $cash_gst->interest + $cash_gst->late_fees;
                            @endphp
                            <tr>
                                <th>Total</th>
                                <td><input type="text" class="form-control" name="invoice_total" id="invoice_total_value" readonly /></td>
                                <td><input type="text" class="form-control" name="purchase_total" id="purchase_total_value" readonly /></td>
                                <td><input type="text" class="form-control" name="cash_total" id="cash_gst_total_value" readonly /></td>
                                <td><input type="text" class="form-control" name="payable_total" id="payable_total_value" readonly /></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    
                        {{-- @if( isset( $_GET['month'] ) )
                            @php $month = app('request')->input('month') @endphp
                        @else
                            @php $month = 'all'; @endphp
                        @endif

                        @if( isset( $_GET['year'] ) )
                            @php $year = app('request')->input('year') @endphp
                        @else
                            @php $year = 'all'; @endphp
                        @endif
                        <input type="hidden" name="post_month" value="{{ $month }}" />
                        <input type="hidden" name="post_year" value="{{ $year }}" />
                        <input type="hidden" name="invoice_cgst" value="{{ $invoice_cgst }}" />
                        <input type="hidden" name="invoice_sgst" value="{{ $invoice_sgst }}" />
                        <input type="hidden" name="invoice_igst" value="{{ $invoice_igst }}" />
                        <input type="hidden" name="invoice_cess" value="{{ $invoice_cess }}" />
                        <input type="hidden" name="invoice_interest" value="0" />
                        <input type="hidden" name="invoice_late_fees" value="0" />
                        <input type="hidden" name="invoice_total" value="0" />

                        <input type="hidden" name="purchase_cgst" value="{{ $purchase_cgst }}" />
                        <input type="hidden" name="purchase_sgst" value="{{ $purchase_sgst }}" />
                        <input type="hidden" name="purchase_igst" value="{{ $purchase_igst }}" />
                        <input type="hidden" name="purchase_cess" value="{{ $purchase_cess }}" />
                        <input type="hidden" name="purchase_interest" value="0" />
                        <input type="hidden" name="purchase_late_fees" value="0" />
                        <input type="hidden" name="purchase_total" value="0" />

                        <input type="hidden" name="cash_cgst" value="{{ $cash_gst->cgst }}" />
                        <input type="hidden" name="cash_sgst" value="{{ $cash_gst->sgst }}" />
                        <input type="hidden" name="cash_igst" value="{{ $cash_gst->igst }}" />
                        <input type="hidden" name="cash_cess" value="{{ $cash_gst->cess }}" />
                        <input type="hidden" name="cash_interest" value="{{ $cash_gst->interest }}" />
                        <input type="hidden" name="cash_late_fees" value="{{ $cash_gst->late_fees }}" />
                        <input type="hidden" name="cash_total" value="{{ $cash_gst_paid }}" />

                        <input type="hidden" name="payable_cgst" value="{{ $cgst_payable }}" />
                        <input type="hidden" name="payable_sgst" value="{{ $sgst_payable }}" />
                        <input type="hidden" name="payable_igst" value="{{ $igst_payable }}" />
                        <input type="hidden" name="payable_cess" value="{{ $cess_payable }}" />
                        <input type="hidden" name="payable_interest" value="0" />
                        <input type="hidden" name="payable_late_fees" value="0" />
                        <input type="hidden" name="payable_total" value="0" /> --}}

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // $(document).ready(function () {

    //     calcuate_invoice_total();
    //     calculate_purchase_total();
    //     calculate_payable_total();

    //     $("#invoice_interest_value").on("keyup", function () {
    //         calcuate_invoice_total('interest');
    //     });

    //     $("#purchase_interest_value").on("keyup", function () {
    //         calculate_purchase_total('interest');
    //     });

    //     $("#payable_interest_value").on("keyup", function () {
    //         calculate_payable_total();
    //     });

    //     $("#invoice_late_fees_value").on("keyup", function () {
    //         calcuate_invoice_total('late_fees');
    //     });

    //     $("#purchase_late_fees_value").on("keyup", function () {
    //         calculate_purchase_total('late_fees');
    //     });

    //     $("#payable_late_fees_value").on("keyup", function () {
    //         calculate_payable_total();
    //     });

    //     function calcuate_invoice_total (part = null) {
    //         var invoice_cgst_value = $("#invoice_cgst_value").text();
    //         var invoice_sgst_value = $("#invoice_sgst_value").text();
    //         var invoice_igst_value = $("#invoice_igst_value").text();
    //         var invoice_cess_value = $("#invoice_cess_value").text();
    //         var invoice_interest_value = $("#invoice_interest_value").val();
    //         var invoice_late_fees_value = $("#invoice_late_fees_value").val();

    //         if( invoice_cgst_value == '' ){
    //             invoice_cgst_value = 0;
    //         }

    //         if( invoice_sgst_value == '' ){
    //             invoice_sgst_value = 0;
    //         }

    //         if( invoice_igst_value == '' ){
    //             invoice_igst_value = 0;
    //         }

    //         if( invoice_cess_value == '' ){
    //             invoice_cess_value = 0;
    //         }

    //         if( invoice_interest_value == '' ){
    //             invoice_interest_value = 0;
    //         }

    //         if( invoice_late_fees_value == '' ){
    //             invoice_late_fees_value = 0;
    //         }

    //         var invoice_total_value = parseInt(invoice_cgst_value) + parseInt(invoice_sgst_value) + parseInt(invoice_igst_value) + parseInt(invoice_cess_value) + parseInt(invoice_interest_value) + parseInt(invoice_late_fees_value);

    //         if( part == 'interest' ) {
    //             var purchase_interest_value = $("#purchase_interest_value").val();
    //             var cash_gst_interest_value = $("#cash_gst_interest_value").text();
    //             var calculated_interest_payable = parseInt(invoice_interest_value) - parseInt(purchase_interest_value) + parseInt(cash_gst_interest_value);

    //             $("#payable_interest_value").val(calculated_interest_payable);

    //             $("#payable_interest_value").trigger("keyup");
    //         }

    //         if( part == 'late_fees' ) {
    //             var purchase_late_fees_value = $("#purchase_late_fees_value").val();
    //             var cash_gst_late_fees_value = $("#cash_gst_late_fees_value").text();
    //             var calculated_late_fees_payable = parseInt(invoice_late_fees_value) - parseInt(purchase_late_fees_value) + parseInt(cash_gst_late_fees_value);

    //             $("#payable_late_fees_value").val(calculated_late_fees_payable);

    //             $("#payable_late_fees_value").trigger("keyup");
    //         }


    //         $('input[name="invoice_interest"]').val(invoice_interest_value);
    //         $('input[name="invoice_late_fees"]').val(invoice_late_fees_value);
    //         $("#invoice_total_value").text(invoice_total_value);
    //         $('input[name="invoice_total"]').val(invoice_total_value);
    //     }


    //     function calculate_purchase_total (part = null) {
    //         var purchase_cgst_value = $("#purchase_cgst_value").text();
    //         var purchase_sgst_value = $("#purchase_sgst_value").text();
    //         var purchase_igst_value = $("#purchase_igst_value").text();
    //         var purchase_cess_value = $("#purchase_cess_value").text();
    //         var purchase_interest_value = $("#purchase_interest_value").val();
    //         var purchase_late_fees_value = $("#purchase_late_fees_value").val();

    //         if( purchase_cgst_value == '' ){
    //             purchase_cgst_value = 0;
    //         }

    //         if( purchase_sgst_value == '' ){
    //             purchase_sgst_value = 0;
    //         }

    //         if( purchase_igst_value == '' ){
    //             purchase_igst_value = 0;
    //         }

    //         if( purchase_cess_value == '' ){
    //             purchase_cess_value = 0;
    //         }

    //         if( purchase_interest_value == '' ){
    //             purchase_interest_value = 0;
    //         }

    //         if( purchase_late_fees_value == '' ){
    //             purchase_late_fees_value = 0;
    //         }

    //         var purchase_total_value = parseInt(purchase_cgst_value) + parseInt(purchase_sgst_value) + parseInt(purchase_igst_value) + parseInt(purchase_cess_value) + parseInt(purchase_interest_value) + parseInt(purchase_late_fees_value);

    //         if( part == 'interest' ) {
    //             var invoice_interest_value = $("#invoice_interest_value").val();
    //             var cash_gst_interest_value = $("#cash_gst_interest_value").text();
    //             var calculated_interest_payable = parseInt(invoice_interest_value) - parseInt(purchase_interest_value) + parseInt(cash_gst_interest_value);

    //             $("#payable_interest_value").val(calculated_interest_payable);

    //             $("#payable_interest_value").trigger("keyup");
    //         }

    //         if( part == 'late_fees' ) {
    //             var invoice_late_fees_value = $("#invoice_late_fees_value").val();
    //             var cash_gst_late_fees_value = $("#cash_gst_late_fees_value").text();
    //             var calculated_late_fees_payable = parseInt(invoice_late_fees_value) - parseInt(purchase_late_fees_value) + parseInt(cash_gst_late_fees_value);

    //             $("#payable_late_fees_value").val(calculated_late_fees_payable);

    //             $("#payable_late_fees_value").trigger("keyup");
    //         }

    //         $('input[name="purchase_interest"]').val(purchase_interest_value);
    //         $('input[name="purchase_late_fees"]').val(purchase_late_fees_value);
    //         $("#purchase_total_value").text(purchase_total_value);
    //         $('input[name="purchase_total"]').val(purchase_total_value);
    //     }


    //     function calculate_payable_total () {
    //         var payable_cgst_value = $("#payable_cgst_value").text();
    //         var payable_sgst_value = $("#payable_sgst_value").text();
    //         var payable_igst_value = $("#payable_igst_value").text();
    //         var payable_cess_value = $("#payable_cess_value").text();
    //         var payable_interest_value = $("#payable_interest_value").val();
    //         var payable_late_fees_value = $("#payable_late_fees_value").val();

    //         if( payable_cgst_value == '' ){
    //             payable_cgst_value = 0;
    //         }

    //         if( payable_sgst_value == '' ){
    //             payable_sgst_value = 0;
    //         }

    //         if( payable_igst_value == '' ){
    //             payable_igst_value = 0;
    //         }

    //         if( payable_cess_value == '' ){
    //             payable_cess_value = 0;
    //         }

    //         if( payable_interest_value == '' ){
    //             payable_interest_value = 0;
    //         }

    //         if( payable_late_fees_value == '' ){
    //             payable_late_fees_value = 0;
    //         }

    //         var payable_total_value = parseInt(payable_cgst_value) + parseInt(payable_sgst_value) + parseInt(payable_igst_value) + parseInt(payable_cess_value) + parseInt(payable_interest_value) + parseInt(payable_late_fees_value);

    //         $('input[name="payable_interest"]').val(payable_interest_value);
    //         $('input[name="payable_late_fees"]').val(payable_late_fees_value);
    //         $("#payable_total_value").text(payable_total_value);
    //         $('input[name="payable_total"]').val(payable_total_value);
    //     }

    //     $("#cash_gst_interest_value").on("keyup", function () {
    //         calculate_cash_gst_total();
    //     });

    //     $("#cash_gst_late_fees_value").on("keyup", function () {
    //         calculate_cash_gst_total();
    //     });

    //     function calculate_cash_gst_total () {
    //         var cash_gst_cgst_value = $("#cash_gst_cgst_value").text();
    //         var cash_gst_sgst_value = $("#cash_gst_sgst_value").text();
    //         var cash_gst_igst_value = $("#cash_gst_igst_value").text();
    //         var cash_gst_cess_value = $("#cash_gst_cess_value").text();
    //         var cash_gst_interest_value = $("#cash_gst_interest_value").val();
    //         var cash_gst_late_fees_value = $("#cash_gst_late_fees_value").val();

    //         if( cash_gst_cgst_value == '' ){
    //             cash_gst_cgst_value = 0;
    //         }

    //         if( cash_gst_sgst_value == '' ){
    //             cash_gst_sgst_value = 0;
    //         }

    //         if( cash_gst_igst_value == '' ){
    //             cash_gst_igst_value = 0;
    //         }

    //         if( cash_gst_cess_value == '' ){
    //             cash_gst_cess_value = 0;
    //         }

    //         if( cash_gst_interest_value == '' ){
    //             cash_gst_interest_value = 0;
    //         }

    //         if( cash_gst_late_fees_value == '' ){
    //             cash_gst_late_fees_value = 0;
    //         }

    //         var cash_gst_total_value = parseInt(cash_gst_cgst_value) + parseInt(cash_gst_sgst_value) + parseInt(cash_gst_igst_value) + parseInt(cash_gst_cess_value) + parseInt(cash_gst_interest_value) + parseInt(cash_gst_late_fees_value);

    //         // $('input[name="payable_interest"]').val(payable_interest_value);
    //         // $('input[name="payable_late_fees"]').val(payable_late_fees_value);
    //         // $("#payable_total_value").text(payable_total_value);
    //         // $('input[name="payable_total"]').val(payable_total_value);

    //         $("#cash_gst_total_value").text(cash_gst_total_value);
    //     }


    //     $('input[name="type_of_payment[]"]').on("change", function(){

    //         var type_of_payment = $(this).val();

    //         // console.log("outside " + type_of_payment);

    //         if($(this).is(':checked')){
    //             if (type_of_payment == 'bank') {
    //                 $("#bank-list").show();
    //             } else if(type_of_payment == 'pos') {
    //                 $("#pos-bank-list").show();
    //             } else if(type_of_payment == 'cash'){
    //                 $("#cash-list").show();
    //             }
    //         } else {
    //             // console.log("inside " + type_of_payment);
    //             if (type_of_payment == 'bank') {
    //                 $("#bank-list").hide();
    //             } else if(type_of_payment == 'pos') {
    //                 $("#pos-bank-list").hide();
    //             } else if(type_of_payment == 'cash'){
    //                 $("#cash-list").hide();
    //             }
    //         }


    //     });
    // });

    $(document).ready(function(){

        //
        $("#invoice_cgst_value").on("keyup", function(){
            calculate_invoice_total();
        });

        $("#invoice_sgst_value").on("keyup", function(){
            calculate_invoice_total();
        });

        $("#invoice_igst_value").on("keyup", function(){
            calculate_invoice_total();
        });

        $("#invoice_cess_value").on("keyup", function(){
            calculate_invoice_total();
        });

        $("#invoice_interest_value").on("keyup", function(){
            calculate_invoice_total();
        });

        $("#invoice_late_fees_value").on("keyup", function(){
            calculate_invoice_total();
        });
        
        $("#invoice_penalty_value").on("keyup", function(){
            calculate_invoice_total();
        });
        
        $("#invoice_other_charge_value").on("keyup", function(){
            calculate_invoice_total();
        });

        // purchase
        $("#purchase_cgst_value").on("keyup", function(){
            calculate_purchase_total();
        });

        $("#purchase_sgst_value").on("keyup", function(){
            calculate_purchase_total();
        });

        $("#purchase_igst_value").on("keyup", function(){
            calculate_purchase_total();
        });

        $("#purchase_cess_value").on("keyup", function(){
            calculate_purchase_total();
        });

        $("#purchase_interest_value").on("keyup", function(){
            calculate_purchase_total();
        });

        $("#purchase_late_fees_value").on("keyup", function(){
            calculate_purchase_total();
        });
        
        $("#purchase_penalty_value").on("keyup", function(){
            calculate_purchase_total();
        });
        
        $("#purchase_other_charge_value").on("keyup", function(){
            calculate_purchase_total();
        });

        // gst paid in cash
        $("#cash_gst_cgst_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });

        $("#cash_gst_sgst_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });

        $("#cash_gst_igst_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });

        $("#cash_gst_cess_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });

        $("#cash_gst_interest_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });

        $("#cash_gst_late_fees_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });
        
        $("#cash_gst_penalty_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });
        
        $("#cash_gst_other_charge_value").on("keyup", function(){
            calculate_gst_paid_in_cash_total();
        });

        // gst payable
        $("#payable_cgst_value").on("keyup", function(){
            calculate_gst_payable_total();
        });

        $("#payable_sgst_value").on("keyup", function(){
            calculate_gst_payable_total();
        });

        $("#payable_igst_value").on("keyup", function(){
            calculate_gst_payable_total();
        });

        $("#payable_cess_value").on("keyup", function(){
            calculate_gst_payable_total();
        });

        $("#payable_interest_value").on("keyup", function(){
            calculate_gst_payable_total();
        });

        $("#payable_late_fees_value").on("keyup", function(){
            calculate_gst_payable_total();
        });
        
        $("#payable_penalty_value").on("keyup", function(){
            calculate_gst_payable_total();
        });
        
        $("#payable_other_charge_value").on("keyup", function(){
            calculate_gst_payable_total();
        });

        function calculate_invoice_total() {
           var invoice_cgst_value = $("#invoice_cgst_value").val();
           var invoice_sgst_value = $("#invoice_sgst_value").val();
           var invoice_igst_value = $("#invoice_igst_value").val();
           var invoice_cess_value = $("#invoice_cess_value").val();
           var invoice_interest_value = $("#invoice_interest_value").val();
           var invoice_late_fees_value = $("#invoice_late_fees_value").val();
           var invoice_penalty_value = $("#invoice_penalty_value").val();
           var invoice_other_charge_value = $("#invoice_other_charge_value").val();

           if(invoice_cgst_value == ''){
               invoice_cgst_value = 0;
           }

           if(invoice_sgst_value == ''){
               invoice_sgst_value = 0;
           }

           if(invoice_igst_value == ''){
               invoice_igst_value = 0;
           }

           if(invoice_cess_value == ''){
               invoice_cess_value = 0;
           }
           
           if(invoice_interest_value == ''){
               invoice_interest_value = 0;
           }

           if(invoice_late_fees_value == ''){
               invoice_late_fees_value = 0;
           }

           if(invoice_penalty_value == ''){
               invoice_penalty_value = 0;
           }

           if(invoice_other_charge_value == ''){
               invoice_other_charge_value = 0;
           }

           var invoice_total_value = parseFloat(invoice_cgst_value) + parseFloat(invoice_sgst_value) + parseFloat(invoice_igst_value) + parseFloat(invoice_cess_value) + parseFloat(invoice_interest_value) + parseFloat(invoice_late_fees_value) + parseFloat(invoice_penalty_value) + parseFloat(invoice_other_charge_value);

           $("#invoice_total_value").val(invoice_total_value);
        }

        function calculate_purchase_total() {
           var purchase_cgst_value = $("#purchase_cgst_value").val();
           var purchase_sgst_value = $("#purchase_sgst_value").val();
           var purchase_igst_value = $("#purchase_igst_value").val();
           var purchase_cess_value = $("#purchase_cess_value").val();
           var purchase_interest_value = $("#purchase_interest_value").val();
           var purchase_late_fees_value = $("#purchase_late_fees_value").val();
           var purchase_penalty_value = $("#purchase_penalty_value").val();
           var purchase_other_charge_value = $("#purchase_other_charge_value").val();

           if(purchase_cgst_value == ''){
               purchase_cgst_value = 0;
           }

           if(purchase_sgst_value == ''){
               purchase_sgst_value = 0;
           }

           if(purchase_igst_value == ''){
               purchase_igst_value = 0;
           }

           if(purchase_cess_value == ''){
               purchase_cess_value = 0;
           }
           
           if(purchase_interest_value == ''){
               purchase_interest_value = 0;
           }

           if(purchase_late_fees_value == ''){
               purchase_late_fees_value = 0;
           }

           if(purchase_penalty_value == ''){
               purchase_penalty_value = 0;
           }

           if(purchase_other_charge_value == ''){
               purchase_other_charge_value = 0;
           }

           var purchase_total_value = parseFloat(purchase_cgst_value) + parseFloat(purchase_sgst_value) + parseFloat(purchase_igst_value) + parseFloat(purchase_cess_value) + parseFloat(purchase_interest_value) + parseFloat(purchase_late_fees_value) + parseFloat(purchase_penalty_value) + parseFloat(purchase_other_charge_value);

           $("#purchase_total_value").val(purchase_total_value);
        }

        function calculate_gst_paid_in_cash_total() {
           var cash_gst_cgst_value = $("#cash_gst_cgst_value").val();
           var cash_gst_sgst_value = $("#cash_gst_sgst_value").val();
           var cash_gst_igst_value = $("#cash_gst_igst_value").val();
           var cash_gst_cess_value = $("#cash_gst_cess_value").val();
           var cash_gst_interest_value = $("#cash_gst_interest_value").val();
           var cash_gst_late_fees_value = $("#cash_gst_late_fees_value").val();
           var cash_gst_penalty_value = $("#cash_gst_penalty_value").val();
           var cash_gst_other_charge_value = $("#cash_gst_other_charge_value").val();

           if(cash_gst_cgst_value == ''){
               cash_gst_cgst_value = 0;
           }

           if(cash_gst_sgst_value == ''){
               cash_gst_sgst_value = 0;
           }

           if(cash_gst_igst_value == ''){
               cash_gst_igst_value = 0;
           }

           if(cash_gst_cess_value == ''){
               cash_gst_cess_value = 0;
           }
           
           if(cash_gst_interest_value == ''){
               cash_gst_interest_value = 0;
           }

           if(cash_gst_late_fees_value == ''){
               cash_gst_late_fees_value = 0;
           }

           if(cash_gst_penalty_value == ''){
               cash_gst_penalty_value = 0;
           }

           if(cash_gst_other_charge_value == ''){
               cash_gst_other_charge_value = 0;
           }

           var cash_gst_total_value = parseFloat(cash_gst_cgst_value) + parseFloat(cash_gst_sgst_value) + parseFloat(cash_gst_igst_value) + parseFloat(cash_gst_cess_value) + parseFloat(cash_gst_interest_value) + parseFloat(cash_gst_late_fees_value) + parseFloat(cash_gst_penalty_value) + parseFloat(cash_gst_other_charge_value);

           $("#cash_gst_total_value").val(cash_gst_total_value);
        }

        function calculate_gst_payable_total() {
           var payable_cgst_value = $("#payable_cgst_value").val();
           var payable_sgst_value = $("#payable_sgst_value").val();
           var payable_igst_value = $("#payable_igst_value").val();
           var payable_cess_value = $("#payable_cess_value").val();
           var payable_interest_value = $("#payable_interest_value").val();
           var payable_late_fees_value = $("#payable_late_fees_value").val();
           var payable_penalty_value = $("#payable_penalty_value").val();
           var payable_other_charge_value = $("#payable_other_charge_value").val();

           if(payable_cgst_value == ''){
               payable_cgst_value = 0;
           }

           if(payable_sgst_value == ''){
               payable_sgst_value = 0;
           }

           if(payable_igst_value == ''){
               payable_igst_value = 0;
           }

           if(payable_cess_value == ''){
               payable_cess_value = 0;
           }
           
           if(payable_interest_value == ''){
               payable_interest_value = 0;
           }

           if(payable_late_fees_value == ''){
               payable_late_fees_value = 0;
           }

           if(payable_penalty_value == ''){
               payable_penalty_value = 0;
           }

           if(payable_other_charge_value == ''){
               payable_other_charge_value = 0;
           }

           var payable_total_value = parseFloat(payable_cgst_value) + parseFloat(payable_sgst_value) + parseFloat(payable_igst_value) + parseFloat(payable_cess_value) + parseFloat(payable_interest_value) + parseFloat(payable_late_fees_value) + parseFloat(payable_penalty_value) + parseFloat(payable_other_charge_value);

           $("#payable_total_value").val(payable_total_value);
        }
    });
</script>
@endsection
