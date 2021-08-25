@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('edit-sale-pending-payment', request()->segment(3)) !!}

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Pending Payment</div>
                <div class="panel-body">
                    <div style="padding: 10px 0; width: 100%; display: inline-block;">
                        <div class="col-md-4">
                            <strong>Party Name : </strong>{{ $associated_party->name }}
                        </div>
                        <div class="col-md-4">
                            <strong>Invoice No. : </strong>{{ $invoice_id }}
                        </div>
                        <div class="col-md-4">
                            <strong>Total Amount : </strong>Rs <span id="total_amount">{{ $sale_remaining_amount->total_amount }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('update.sale.pending.payment', $sale_remaining_amount->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <table class="table">
                            <tbody>
                                @if($associated_party->tds_income_tax)
                                <tr>
                                    <th colspan="2">TDS Income Tax</th>
                                    <td colspan="2"><input type="text" class="form-control" name="tds_income_tax" value="{{ $sale_remaining_amount->tds_income_tax }}" /></td>
                                </tr>
                                @endif

                                @if($associated_party->tds_gst)
                                <tr>
                                    <th colspan="2">TDS GST</th>
                                    <td colspan="2"><input type="text" class="form-control" name="tds_gst" value="{{ $sale_remaining_amount->tds_gst }}" /></td>
                                </tr>
                                @endif
                                
                                @if($associated_party->tcs_income_tax)
                                <tr>
                                    <th colspan="2">TCS Income Tax</th>
                                    <td colspan="2"><input type="text" class="form-control" name="tcs_income_tax" value="{{ $sale_remaining_amount->tcs_income_tax }}" /></td>
                                </tr>
                                @endif
                                
                                @if($associated_party->tcs_gst)
                                <tr>
                                    <th colspan="2">TCS GST</th>
                                    <td colspan="2"><input type="text" class="form-control" name="tcs_gst" value="{{ $sale_remaining_amount->tcs_gst }}" /></td>
                                </tr>
                                @endif

                                <tr>
                                    <th colspan="2">Type of Payment</th>
                                    <td colspan="2">
                                        <div class="form-group">
                                            <label>Mode of Payment</label><br>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+pos' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash' ) checked="checked" @endif @endif /> <label for="cash">Cash</label>
                                                </div>

                                                <div class="col-md-9">
                                                    <div class="form-group" id="cash-list" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+pos' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" value="{{ $sale_remaining_amount->cash_payment }}">
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+pos' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+bank' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank' ) checked="checked" @endif @endif> <label for="bank">Bank</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="form-group" id="bank-list" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+pos' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+bank' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                                        <div class="form-group">
                                                            <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" value="{{ $sale_remaining_amount->bank_payment }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" value="{{ $sale_remaining_amount->bank_cheque }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Bank List</label>
                                                            <select class="form-control" name="bank">
                                                                @if(count($banks) > 0)
                                                                    @foreach($banks as $bank)
                                                                        <option @if($sale_remaining_amount->bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" value="{{ \Carbon\Carbon::parse($sale_remaining_amount->bank_payment_date)->format('d/m/Y') }}" />
                                                        </div>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+pos' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+bank' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos' ) checked="checked" @endif @endif> <label for="pos">POS</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="form-group" id="pos-bank-list" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+pos' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+cash' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+bank' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                                        <div class="form-group">
                                                            <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" value="{{ $sale_remaining_amount->pos_payment }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>POS Bank List</label>
                                                            <select class="form-control" name="pos_bank">
                                                                @if(count($banks) > 0)
                                                                    @foreach($banks as $bank)
                                                                        <option @if($sale_remaining_amount->pos_bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" value="{{ \Carbon\Carbon::parse($sale_remaining_amount->pos_payment_date)->format('d/m/Y') }}" />
                                                        </div>
                                                        <hr/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-5">
                                                    <input type="checkbox" name="type_of_payment[]" value="cash_discount" id="cash_discount" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'discount' ) checked="checked" @endif @endif /> <label for="cash_discount">Cash Discount</label>
                                                </div>
                                                <div class="col-md-7">
                                                    {{-- <label>Discount Type</label> --}}
                                                    <div id="discount-list" class="row" @if(isset($sale_remaining_amount->type_of_payment) && $sale_remaining_amount->type_of_payment != "no_payment") @if( 
                                                    $sale_remaining_amount->type_of_payment == 'combined' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'bank+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'cash+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'pos+discount' || 
                                                    $sale_remaining_amount->type_of_payment == 'discount' ) style="display:block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                                        <div class="col-md-6" style="padding-right: 0;">
                                                            <select class="form-control" name="discount_type" id="discount_type">
                                                                {{-- <option value="" disabled selected>None</option> --}}
                                                                <option @if($sale_remaining_amount->discount_type == 'fixed') selected="selected" @endif value="fixed">Fixed (Rs)</option>
                                                                <option @if($sale_remaining_amount->discount_type == 'percent') selected="selected" @endif value="percent">Percent (%)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6" style="padding-left: 0;">
                                                            <input type="text" placeholder="Disc. Figure" name="discount_figure" id="discount_figure" class="form-control" value="{{ $sale_remaining_amount->discount_figure }}" />
                                                        </div>
                                                        <div class="col-md-12">
                                                            <input type="text" placeholder="Discount" name="discount_amount" id="discount_holder" class="form-control" value="{{ $sale_remaining_amount->total_discount }}" readonly /> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Amount Paid</th>
                                    <th>Amount Remaining</th>
                                    <th>Payment Date</th>
                                    <th>Voucher</th>
                                </tr>
                                <tr>
                                    <td><input type="text" class="form-control" id="amount_paid" name="amount_paid" value="{{ $sale_remaining_amount->amount_paid }}" required /></td>
                                    <td><input type="text" class="form-control" id="amount_remaining" name="amount_remaining" value="{{ $sale_remaining_amount->amount_remaining }}" readonly /></td>
                                    <td><input type="text" class="form-control custom_date" name="payment_date" value="{{ \Carbon\Carbon::parse($sale_remaining_amount->payment_date)->format('d/m/Y') }}" placeholder="dd/mm/yyyy" required /></td>
                                    <td><input type="text" class="form-control" name="voucher_no" value="{{ $sale_remaining_amount->voucher_no }}" @if(isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->bill_no_type == 'auto') readonly @endif /></td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="form-group">
                            <button class="btn btn-success">Update Payment</button>
                            <button type="button" class="btn btn-default" @if($sale_remaining_amount->status == 1) onclick="event.preventDefault(); document.getElementById('cancel-payment-form').submit();" @endif>{{ $sale_remaining_amount->status == 1 ? 'Cancel' : 'Cancelled' }}</button>
                        </div>
                    </form>
                    <form id="cancel-payment-form" method="POST" action="{{ route('cancel.sale.payment', $sale_remaining_amount->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                    </form>
                </div>
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
        $(document).ready(function(){
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
                    } else if(type_of_payment == 'cash_discount'){
                        $("#discount-list").show();
                    }
                } else {
                    // console.log("inside " + type_of_payment);
                    if (type_of_payment == 'bank') {
                        $("#bank-list").hide();
                        $("#banked_amount").val(0).trigger("keyup");
                    } else if(type_of_payment == 'pos') {
                        $("#pos-bank-list").hide();
                        $("#posed_amount").val(0).trigger("keyup");
                    } else if(type_of_payment == 'cash'){
                        $("#cash-list").hide();
                        $("#cashed_amount").val(0).trigger("keyup");
                    } else if(type_of_payment == 'cash_discount'){
                        $("#discount-list").hide();
                        $("#discount_figure").val(0).trigger("keyup");
                        $("#discount_holder").val(0).trigger("keyup");
                    }
                }

            });

            $("#amount_paid").on("keyup", function(){
                let total_amount = '{{ $sale_remaining_amount->total_amount }}';
                let amount_paid = $(this).val();

                if(typeof total_amount == NaN){
                    total_amount = 0;
                }

                if(amount_paid == ''){
                    amount_paid = 0;
                }

                let amount_remaining = roundToTwo(total_amount - amount_paid);

                $("#amount_remaining").val(amount_remaining);
            });

            $("#cashed_amount").on("keyup", function() {
                calculate_amount();
            });

            $("#banked_amount").on("keyup", function() {
                calculate_amount();
            });

            $("#posed_amount").on("keyup", function() {
                calculate_amount();
            });

            $("#discount_figure").on("keyup", function() {
                calculate_discount();
                calculate_amount();
            });

            $("#discount_type").on("change", function () {
                calculate_discount();
                calculate_amount();
            });

            $("#discount_holder").on("keyup", function () {
                calculate_discount();
                calculate_amount();
            });

            function calculate_amount() {
                var cash_amount = $("#cashed_amount").val() || 0;
                var bank_amount = $("#banked_amount").val() || 0;
                var pos_amount = $("#posed_amount").val() || 0;
                var discount_amount = $("#discount_holder").val() || 0;

                var amount = parseFloat(cash_amount) + parseFloat(bank_amount) + parseFloat(pos_amount) + parseFloat(discount_amount);

                $("#amount_paid").val(amount).trigger("keyup");;
            }

            function calculate_discount() {
                var discount_type = $("#discount_type option:selected").val();

                var discount_figure = $("#discount_figure").val() || 0;
                var total_amount = '{{ $sale_remaining_amount->amount_paid + $sale_remaining_amount->amount_remaining }}' || 0;

                if(discount_type == 'percent'){
                    discount_figure = Number((discount_figure * total_amount) / 100).toFixed(2);
                }

                $("#discount_holder").val(discount_figure);
            }
        });
    </script>
@endsection
