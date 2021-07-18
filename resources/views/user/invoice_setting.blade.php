@extends('layouts.dashboard')

<style>
    /* .row{
        margin-top:40px;
        padding: 0 10px;
    } */

    .clickable{
        cursor: pointer;
    }

    .panel-heading span {
        margin-top: -20px;
        font-size: 15px;
    }

</style>

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <form method="POST" action="{{ route('save.invoice.setting') }}">
                {{ csrf_field() }}
                    
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Invoice Settings</h3>
                        <span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
                    </div>

                    <div class="panel-body">

                        <div class="row" style="margin-bottom: 10px;">

                            <div class="col-md-3">
                                <div class="form-group{{ $errors->has('bill_no_type') ? ' has-error' : '' }}">
                                    <label for="bill_type" class="control-label">Invoice No Type?</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input @if(auth()->user()->profile->bill_no_type == 'auto') checked @endif type="radio" name="bill_no_type" id="bill_no_type1" value="auto" checked> Auto
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input @if(auth()->user()->profile->bill_no_type == 'manually') checked @endif type="radio" name="bill_no_type" id="bill_no_type2" value="manually" > Manually
                                        </label>
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group{{ $errors->has('format_of_invoice') ? ' has-error' : '' }}">
                                    <label for="type" class="control-label">Format of Invoice?</label>


                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="format_of_invoice" id="format_of_invoice1" value="bill of supply" > Bill of Supply
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="format_of_invoice" id="format_of_invoice1" value="gst invoice" checked> GST Invoice
                                        </label>

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('invoice_heading') ? ' has-error' : '' }}">
                                    <label for="type" class="control-label">Invoice Heading always prints:- Composition Dealer?</label>

                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="invoice_heading" id="invoice_heading1" value="yes" > Yes
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="invoice_heading" id="invoice_heading2" value="no" checked> No
                                        </label>

                                </div>
                            </div>

                        </div>

                        <div id="invoice_auto_setting_block" @if(auth()->user()->profile->bill_no_type == 'manually') style="display: none;" @endif>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('starting_no') ? ' has-error' : '' }}">
                                        <label for="starting_no" class="control-label">Starting No.</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <input id="starting_no" type="text" class="form-control" name="starting_no" @if( isset($user_profile->starting_no) ) value="{{ $user_profile->starting_no }}" @endif>

                                            @if ($errors->has('starting_no'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('starting_no') }}</strong>
                                                </span>
                                            @endif
                                        {{-- </div> --}}
                                        <p id="starting_no_error_msg" style="color: red; font-size: 12px;"></p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('width_of_numerical') ? ' has-error' : '' }}">
                                        <label for="width_of_numerical" class="control-label">Width of Numerical</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <input id="width_of_numerical" type="text" class="form-control" name="width_of_numerical" @if( isset($user_profile->width_of_numerical) ) value="{{ $user_profile->width_of_numerical }}" @endif>

                                            @if ($errors->has('width_of_numerical'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('width_of_numerical') }}</strong>
                                                </span>
                                            @endif
                                        {{-- </div> --}}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('start_no_applicable_date') ? ' has-error' : '' }}">
                                        <label for="start_no_applicable_date" class="control-label">Applicable Date</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <input id="start_no_applicable_date" type="text" name="start_no_applicable_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($user_profile->start_no_applicable_date) ) value="{{ \Carbon\Carbon::parse($user_profile->start_no_applicable_date)->format('d/m/Y') }}" @endif />
                                        {{-- </div> --}}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="period" class="control-label">Period</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <select id="period" type="text" name="period" class="form-control" >
                                                <option @if( isset($user_profile->period) && $user_profile->period == 'week' ) selected="selected" @endif value="week">Week</option>
                                                <option @if( isset($user_profile->period) && $user_profile->period == 'month' ) selected="selected" @endif value="month">Month</option>
                                                <option @if( isset($user_profile->period) && $user_profile->period == 'year' ) selected="selected" @endif value="year">Year</option>
                                            </select>
                                        {{-- </div> --}}
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="suffix_applicable_date" class="control-label">Applicable Date</label>

                                        {{-- <div class="col-md-6"> --}}
                                        <input id="suffix_applicable_date" type="text" name="suffix_applicable_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($user_profile->suffix_applicable_date) ) value="{{ Carbon\Carbon::parse($user_profile->suffix_applicable_date)->format('d/m/Y') }}" @endif />
                                        {{-- </div> --}}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_of_suffix" class="control-label">Name of Suffix</label>

                                        {{-- <div class="col-md-6"> --}}
                                        <input id="name_of_suffix" type="text" name="name_of_suffix" data-allowDash="true" class="form-control" @if( isset($user_profile->name_of_suffix) ) value="{{ $user_profile->name_of_suffix }}" @endif />
                                        {{-- </div> --}}
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prefix_applicable_date" class="control-label">Applicable Date</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <input id="prefix_applicable_date" type="text" name="prefix_applicable_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($user_profile->prefix_applicable_date) ) value="{{ \Carbon\Carbon::parse($user_profile->prefix_applicable_date)->format('d/m/Y') }}" @endif />
                                        {{-- </div> --}}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_of_prefix" class="control-label">Name of Prefix</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <input id="name_of_prefix" type="text" name="name_of_prefix" class="form-control" @if( isset($user_profile->name_of_prefix) ) value="{{ $user_profile->name_of_prefix }}" @endif />
                                        {{-- </div> --}}
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="type_of_company" class="control-label">Invoice Template</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <select class="form-control" id="invoice_template" name="invoice_template">
                                                {{-- <option @if(isset($user_profile->invoice_template)) @if($user_profile->invoice_template == '1') selected="selected" @endif @else @if(old('invoice_template') == '1') selected="selected" @endif @endif value="1">Template 1 (without HSN/SAC)</option> --}}
                                                <option @if(isset($user_profile->invoice_template)) @if($user_profile->invoice_template == '2') selected="selected" @endif @else @if(old('invoice_template') == '2') selected="selected" @endif @endif value="2">Template 2 (with HSN/SAC)</option>
                                            </select>
                                        {{-- </div> --}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Show Terms and Condition?</label>

                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input @if(auth()->user()->profile->show_terms == 1) checked @endif type="radio" name="show_terms" id="show_terms1" value="1" checked> YES
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input @if(auth()->user()->profile->show_terms == 0) checked @endif type="radio" name="show_terms" id="show_terms2" value="0" > NO
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Show Bank Info?</label>

                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input @if(auth()->user()->profile->show_bank_info == 1) checked @endif type="radio" name="show_bank_info" id="show_bank_info1" value="1" checked> YES
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input @if(auth()->user()->profile->show_bank_info == 0) checked @endif type="radio" name="show_bank_info" id="show_bank_info2" value="0" > NO
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-success save-settings">Save Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@section('scripts')
    <script>

        let validatingRoute = "{{ route('api.validate.invoice.startingno') }}";

        let starting_no = $("#starting_no").val();
        if($('input[name="bill_no_type"]:checked').val() == 'auto' && starting_no != ''){
            checkStartingNo(starting_no, validatingRoute);
        }
        else {
            $(".save-settings").attr('disabled', false);
			$("#starting_no_error_msg").text('');
        }

        $("#starting_no").on("keyup", function() {
            let starting_no = $(this).val();

            // console.log(starting_no);

            if(starting_no != ''){
                console.log(starting_no);
                checkStartingNo(starting_no, validatingRoute);
            }
        });

        $('input[name="is_registered"]').on('change', function(){
            var is_registered = $(this).val();

            if( is_registered == 0 ) {
                $("#gst-block").hide();
                $("#show_state_regular").hide();
                $("#show_state_composition").hide();
            }

            if (is_registered == 1) {
                $("#gst-block").show();
                $("#show_state_regular").hide();
                $("#show_state_composition").hide();
            }

            else if(is_registered == 2){
                $("#gst-block").hide();
                $("#show_state_regular").show();
                $("#show_state_composition").hide();
            }

            else if(is_registered == 3){
                $("#gst-block").show();
                $("#show_state_composition").show();
                $("#show_state_regular").hide();
            }

            if (is_registered == 4) {
                $("#gst-block").show();
                $("#show_state_regular").hide();
                $("#show_state_composition").hide();
            }

        });

        $('input[name="add_lump_sump"]').on('change', function(){
            var is_registered = $(this).val();
            if (is_registered == "yes") {
                $("#add_lump_sump_block").show();
            }
            else {
                $("#add_lump_sump_block").hide();
            }
        });

        $("#checkbox_shipping_address").on("change", function () {
            if($(this).is(":checked")){
                $("#shipping_address_section").hide();
            } else {
                $("#shipping_address_section").show();
            }
        });

        $("#checkbox_billing_address").on("change", function () {
            if($(this).is(":checked")){
                $("#billing_address_section").hide();
            } else {
                $("#billing_address_section").show();
            }
        });


        $(document).on('click', '.panel-heading span.clickable', function(e){
            var $this = $(this);
            if(!$this.hasClass('panel-collapsed')) {
                $this.parents('.panel').find('.panel-body').slideUp();
                $this.addClass('panel-collapsed');
                $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            } else {
                $this.parents('.panel').find('.panel-body').slideDown();
                $this.removeClass('panel-collapsed');
                $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            }
        });

/*
        // $(document).on('change', '#type_of_company_state', function(){
        //     // console.log($(this).val());

        //     if( $(this).val() == 'regular' ) {
        //         $("#show_state_regular").show();
        //         $("#show_state_composition").hide();
        //     }

        //     if( $(this).val() == 'composition' ) {
        //         $("#show_state_composition").show();
        //         $("#show_state_regular").hide();
        //     }
        // });
*/

        $('input[name="bill_no_type"]').on("change", function() {
            
            if($(this).val() == 'auto'){
                $("#invoice_auto_setting_block").show();

                if($('input[name="bill_no_type"]:checked').val() == 'auto' && starting_no != ''){
                    console.log($('input[name="bill_no_type"]').val());
                    checkStartingNo(starting_no, validatingRoute);
                }
            } else {
                $("#invoice_auto_setting_block").hide();
                $(".save-settings").attr('disabled', false);
			    $("#starting_no_error_msg").text('');
            }

        });

        // $('input[name="want_round_off"]').on("change", function () {
        //     if( $(this).val() == "yes"){
        //         $("#round_off_block").show();
        //     } else {
        //         $("#round_off_block").hide();
        //     }
        // });

        // $('#btn_round_off_setting').on("click", function() {
        //     $('#modal_round_off_setting').modal("show");
        // });

        

        $(document).ready(function(){

            $('#btn_round_off_setting').on("click", function() {
                $('#modal_round_off_setting').modal("show");
            });

            $("#add_account").on("click", function(){
                $("#account_selection_block").show();
            });

            $(document).on("change", "#account_selection", function(){
                console.log($(this).val());

                if( $(this).val() == 'sale' ){
                    $(".sale_block").show();
                    $("#transaction_type_sale").attr("checked", true);
                } else {
                    $(".sale_block").hide();
                    $("#transaction_type_sale").attr("checked", false);
                }

                if( $(this).val() == 'purchase' ){
                    $(".purchase_block").show();
                    $("#transaction_type_purchase").attr("checked", true);
                } else {
                    $(".purchase_block").hide();
                    $("#transaction_type_purchase").attr("checked", false);
                }
            });

            $('input[name="round_off_to"]').on("change", function(){
                if( $(this).is(":checked") ){

                    if( $(this).val() == "upward" ){
                        $("#upward_to_block").show();
                    } else {
                        $("#upward_to_block").hide();
                    }

                    if( $(this).val() == "downward" ){
                        $("#downward_to_block").show();
                    } else {
                        $("#downward_to_block").hide();
                    }

                }

            });

            $('input[name="transaction_type"]').on("change", function(){
                if($(this).val() == 'sale'){
                    $(".sale_block").show();
                } else {
                    $(".sale_block").hide();
                }

                if($(this).val() == 'purchase'){
                    $(".purchase_block").show();
                } else {
                   $(".purchase_block").hide(); 
                }
            });
        });

    </script>
@endsection