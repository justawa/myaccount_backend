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
            <form method="POST" action="{{ route('save.select.option.setting') }}">
                {{ csrf_field() }}
                    
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Select Option Settings</h3>
                        <span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
                    </div>

                    <div class="panel-body">

                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_buyer_name" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_buyer_name) checked @endif value="1">Show Buyer Name</label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_order" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_order) checked @endif value="1">Show Sale Order</label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_reference_name" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_reference_name) checked @endif value="1">Show Reference Name</label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_gst_classification" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_gst_classification) checked @endif value="1">Show GST Classification</label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_cess_charge" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_cess_charge) checked @endif value="1">Show CESS Charge</label>
                        </div>
                        {{-- <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" id="show_additional_charge">Show Additional Charge</label>
                        </div> --}}
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_tcs" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_tcs) checked @endif value="1">Show TCS - Income tax</label>
                        </div>
                        {{-- <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" id="search_using_barcode" @if(auth()->user()->selectOption->show_using_barcode) checked @endif>Search using Barcode</label>
                        </div> --}}
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_consign_info" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_consign_info) checked @endif value="1">Show Consigner &amp; Consignee Name &amp; Address</label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline"><input type="checkbox" name="show_import_export_info" @if(isset(auth()->user()->selectOption) && auth()->user()->selectOption->show_import_export_info) checked @endif value="1">Show Export/Import Info</label>
                        </div>

                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-success">Save Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@section('scripts')
    <script>
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

        $('input[name="bill_no_type"]').on("change", function() {
            
            if($(this).val() == 'auto'){
                $("#invoice_auto_setting_block").show();
                $("#starting_no").attr('required', true);
                $("#width_of_numerical").attr('required', true);
                $("#start_no_applicable_date").attr('required', true);
                $("#period").attr('required', true);
            } else {
                $("#invoice_auto_setting_block").hide();
                $("#starting_no").attr('required', false);
                $("#width_of_numerical").attr('required', false);
                $("#start_no_applicable_date").attr('required', false);
                $("#period").attr('required', false);
            }

        });

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