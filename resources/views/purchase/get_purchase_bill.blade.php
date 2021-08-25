@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('get-bill-info', request()->segment(2), request()->segment(4)) !!}

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View Bill Amounts</div>
                <div class="panel-body">
                    <div style="padding: 10px 0; width: 100%; display: inline-block;">
                        <div class="col-md-3">
                            <strong>Party Name : </strong>{{ $associated_party->name }}
                            <input type="hidden" id="party" value="{{ $associated_party->id }}" />
                        </div>
                        <div class="col-md-3">
                            <strong>Bill NO. : </strong>{{ $bill_no }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Amount : </strong>Rs {{ $total_amount }}
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="add-pending-payment" class="btn btn-success">Add Payment to Bill</button>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                {{-- <th>#</th> --}}
                                <th>Amount Paid</th>
                                <th>Amount Remaining</th>
                                <th>Type of payment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1; $total_paid = 0; $amount_remaining = 0; @endphp
                            @foreach($purchase_amounts as $purchase)
                            @php $total_paid += $purchase->amount_paid @endphp
                            @if ($loop->first) @continue @endif
                            <tr>
                                {{-- <td>{{ $count++ }}</td> --}}
                                <td>{{ $purchase->amount_paid }}</td>
                                <td>{{ $purchase->amount_remaining }}</td>
                                <td>{{ $purchase->type_of_payment }} @if($purchase->type_of_payment == 'bank') ({{ $purchase->bank_name . " - " . $purchase->bank_branch }}) @endif</td>
                                <td><a href="{{ route('edit.purchase.pending.payment', $purchase->id) }}">Edit</a></td>
                            </tr>
                            @endforeach

                            {{-- @foreach($credit_notes as $note)
                            @php $total_paid += $note->note_value; $amount_remaining += $note->note_value; @endphp
                            <tr>
                                <td>credit note</td>
                                <td>{{ $note->note_value }}</td>
                                <td>{{ $amount_remaining }}</td>
                                <td><a href="{{ route('invoice.detail.credit.note', $note->id) }}">View Note</a></td>
                            </tr>
                            @endforeach --}}
                            {{-- @foreach($debit_notes as $note)
                            @php $total_paid -= $note->note_value; $amount_remaining -= $note->note_value; @endphp
                            <tr>
                                <td>debit note</td>
                                <td>{{ $note->note_value }}</td>
                                <td>{{ $amount_remaining }}</td>
                                <td><a href="{{ route('invoice.detail.debit.note', $note->id) }}">View Note</a></td>
                            </tr>
                            @endforeach --}}

                            @php $total_remaining = $total_amount - $total_paid @endphp
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td>{{ $total_paid }}</td>
                                <td>{{ $total_remaining }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal" id="modal-add-pending-payment">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Pending Payment</h4>
            </div>
            <div class="modal-body">
                <form id="form-add-pending-payment" method="POST" action="{{ route('post.pending.payment') }}">
                    {{ csrf_field() }}
                    <input type="hidden" class="form-control" name="bill_id" id="bill_id" value="{{ $bill }}" />
                    {{-- <div class="form-group">
                        <label>Select Party</label>
                        <select class="form-control" name="party" id="party_id">
                            <option value="{{ $associated_party->id }}">{{ $associated_party->name }}</option>
                        </select>
                    </div> --}}
                    <input type="hidden" name="party" id="party_id" value="{{ $associated_party->id }}" />
                    <input type="hidden" class="amount_remaining" id="amount_remaining" name="amount_remaining" />
                    <input type="hidden" id="amount_to_pay" value="{{ $total_remaining }}" />
                    <input type="hidden" name="total_amount" id="total_amount" value="{{ $total_amount }}" />
                    <p><strong>Balance: <span>{{ $total_remaining }}</span></strong></p>

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

                    {{-- <div class="form-group">
                        <label>Amount To Be Paid</label>
                        <input type="text" class="form-control" id="amount_to_pay" value="{{ $total_remaining }}" placedholder="Amount To Be Paid" readonly />
                    </div> --}}
                    
                     
                    {{-- <div class="form-group">
                        <label>Amount Paid</label>
                        <input type="text" class="form-control" name="amount_paid" id="amount_paid" placeholder="Amount Paid" required />
                    </div> --}}

                    @if( $associated_party->tds_income_tax == 1 )
                    <div class="form-group">
                        <label>TDS Income Tax Amount</label>
                        <input type="text" class="form-control" id="tds_income_tax_amount" name="tds_income_tax" placeholder="TDS Income Tax Amount" required>
                    </div>
                    @else
                       <input type="hidden" id="tds_income_tax_amount" name="tds_income_tax" value="0">
                    @endif

                    @if( $associated_party->tds_gst == 1 )
                    <div class="form-group">
                        <label>TDS GST Amount</label>
                        <input type="text" class="form-control" id="tds_gst" name="tds_gst" placeholder="TDS GST Amount" required>
                    </div>
                    @else
                        <input type="hidden" id="tds_gst" name="tds_gst" value="0">
                    @endif

                    @if( $associated_party->tcs_income_tax == 1 )
                    <div class="form-group">
                        <label>TCS Income Tax Amount</label>
                        <input type="text" class="form-control" id="tcs_income_tax" name="tcs_income_tax" placeholder="TCS Income Tax Amount" required>
                    </div>
                    @else
                        <input type="hidden" id="tcs_income_tax" name="tcs_income_tax" value="0">
                    @endif

                    @if( $associated_party->tcs_gst == 1 )
                    <div class="form-group">
                        <label>TCS GST Amount</label>
                        <input type="text" class="form-control" id="tcs_gst" name="tcs_gst" placeholder="TCS GST Amount" required>
                    </div>
                    @else
                        <input type="hidden" id="tcs_gst" name="tcs_gst" value="0">
                    @endif

                    {{-- <div class="form-group">
                        <label>Amount Remaining</label>
                        <input type="text" class="form-control" name="amount_remaining" id="amount_remaining" placeholder="Amount Remaining" readonly required />
                    </div> --}}

                    <div class="form-group">
                        <label>Total Amount</label>
                        <input type="text" class="form-control" id="amount_paid" name="amount_paid" placeholder="Total Amount" readonly />
                    </div>

                    @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                    <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                        <label>Voucher No.</label>
                        <input id="voucher_no" type="text" class="form-control" name="voucher_no" placeholder="Voucher No." @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @else required @endif @endif @endif />
                        @if ($myerrors->has('voucher_no'))
                        <span class="help-block">
                            <ul>
                                @foreach( $myerrors['voucher_no'] as $error )
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="text" class="form-control custom_date" id="payment_date" name="payment_date" placeholder="DD/MM/YYYY" autocomplete="off" required>
                        <p id="payment_date_validation_error" style="font-size: 12px; color: red;"></p>
                    </div>

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
                <p id="pending-amount-message"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        function calculate_amount_remaining()
        {
            var amount_to_pay = $("#amount_to_pay").val();
            var amount_paid = $("#amount_paid").val();

            var tds_income_tax = $("#tds_income_tax_amount").val();
            if(tds_income_tax == ''){
                tds_income_tax = 0;
            }
            
            var tds_gst = $("#tds_gst").val();
            if(tds_gst == ''){
                tds_gst = 0;
            }

            var tcs_income_tax = $("#tcs_income_tax").val();
            if(tcs_income_tax == ''){
                tcs_income_tax = 0;
            }

            var tcs_gst = $("#tcs_gst").val();
            if(tcs_gst == ''){
                tcs_gst = 0;
            }

            amount_paid = parseFloat(amount_paid) + parseFloat(tds_income_tax) + parseFloat(tds_gst) + parseFloat(tcs_income_tax) + parseFloat(tcs_gst);

            let amount_remaining = roundToTwo(amount_to_pay - amount_paid);

            $(".amount_remaining").val(amount_remaining);
            $(".amount_remaining").text(amount_remaining);
        }

        function roundToTwo(num) {
            return +(Math.round(num + "e+2")  + "e-2");
        }

        $(document).ready(function(){
            // $('input[name="type_of_payment"]').on("change", function(){
            //     var type_of_payment = $(this).val();

            //     if(type_of_payment == 'bank'){
            //         $("#bank-list").show();
            //     } else {
            //         $("#bank-list").hide();
            //     }
            // });

            $("#payment_date").on("keyup", function() {
                var date = $(this).val();
                var validate_against = '{{ \Carbon\Carbon::parse($bill_date)->format("d/m/Y") }}';

                validateDate(date, "payment_date_validation_error", "#", "btn-add-payment-modal", "#");

                validateTwoDates(validate_against, date, "#", "btn-add-payment-modal", "#", "payment_date_validation_error");
            });

            $("#voucher_no").on("keyup", function() {
                var bill_no = $("#voucher_no").val() ? $("#voucher_no").val() : undefined;
                var party = $("#party").val() ? $("#party").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            $("#party").on("change", function() {
                var bill_no = $("#voucher_no").val() ? $("#voucher_no").val() : undefined;
                var party = $("#party").val() ? $("#party").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(party, bill_no, userId)
            });

            function validateBillNo(party = undefined, bill_no = undefined, userId = undefined) {
                console.log(party, bill_no, userId);
                if(party && bill_no && userId){
                    $.ajax({
                        type: 'post',
                        url: "{{ route('api.validate.purchase.payment.voucherno') }}",
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

            $(document).on("click", "#add-pending-payment", function () {
                $('#modal-add-pending-payment').modal('show');
            });

            $(document).on("keyup", "#amount_paid", function () {
                calculate_amount_remaining();
            });

            $("#tds_income_tax_amount").on("keyup", function(){
                calculate_amount_remaining();
            });

            $("#tds_gst").on("keyup", function(){
                calculate_amount_remaining();
            });

            $("#tcs_income_tax").on("keyup", function(){
                calculate_amount_remaining();
            });

            $("#tcs_gst").on("keyup", function(){
                calculate_amount_remaining();
            });

            // $(document).on("submit", "#form-add-pending-payment", function (e) {
            //     e.preventDefault();
            //     let continueProcess = true;
            //     var bill_no = $("#bill_no").val();
            //     var party_id = $("#party_id option:selected").val();
            //     var total_amount = $("#total_amount").val();
            //     var amount_paid = $("#amount_paid").val();

            //     var tds_income_tax = $("#tds_income_tax_amount").val();
            //     if(tds_income_tax == ''){
            //         tds_income_tax = 0;
            //     }
                
            //     var tds_gst = $("#tds_gst").val();
            //     if(tds_gst == ''){
            //         tds_gst = 0;
            //     }

            //     var tcs_income_tax = $("#tcs_income_tax").val();
            //     if(tcs_income_tax == ''){
            //         tcs_income_tax = 0;
            //     }

            //     var tcs_gst = $("#tcs_gst").val();
            //     if(tcs_gst == ''){
            //         tcs_gst = 0;
            //     }

            //     var payment_date = $("#payment_date").val();

            //     if(payment_date == ''){
            //         alert('Date is required!');
            //         continueProcess = false;
            //     }

            //     var amount_remaining = $("#amount_remaining").val();
            //     var type_of_payment = $("input[name='type_of_payment']:checked").val();
            //     if (type_of_payment == 'bank') {
            //         var bank_select_list = $("#bank-select-list option:selected").val();
            //         var bank_cheque = $("#bank_cheque").val();
            //     } else {
            //         var bank_select_list = 0;
            //         var bank_cheque = null;
            //     }

            //     if(continueProcess){
            //         $.ajax({
            //             type: 'post',
            //             url: "{{ route('post.pending.payment') }}",
            //             data: {
            //                 "party_id": party_id,
            //                 "bill_no": bill_no,
            //                 "total_amount": total_amount,
            //                 "amount_paid": amount_paid,
            //                 "tds_income_tax": tds_income_tax,
            //                 "tds_gst": tds_gst,
            //                 "tcs_income_tax": tcs_income_tax,
            //                 "tcs_gst": tcs_gst,
            //                 "amount_remaining": amount_remaining,
            //                 "type_of_payment": type_of_payment,
            //                 "bank_id": bank_select_list,
            //                 "bank_cheque": bank_cheque,
            //                 "payment_date": payment_date,
            //                 "_token": '{{ csrf_token() }}'
            //             },
            //             success: function (response) {
            //                 console.log(response);

            //                 if (response == 'success') {
            //                     $("#pending-amount-message").html("Data saved successfully");
            //                     $("#amount_paid").val('');
            //                     $("#amount_remaining").val('');
            //                     location.reload();
            //                 } else {
            //                     $("#pending-amount-message").html("Some occured while saving data.");
            //                 }
            //             }
            //         });
            //     }
            // });

            $(document).on('click', '.modal-close', function() {
                $("#amount_paid").val('');
                $("#amount_remaining").val('');
                $(".amount_remaining").text('');
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
                        $("#banked_amount").val(0);
                        $("#banked_amount").trigger('keyup');
                        $("#bank-list").hide();
                    } else if(type_of_payment == 'pos') {
                        $("#posed_amount").val(0);
                        $("#posed_amount").trigger('keyup');
                        $("#pos-bank-list").hide();
                    } else if(type_of_payment == 'cash'){
                        $("#cashed_amount").val(0);
                        $("#cashed_amount").trigger('keyup');
                        $("#cash-list").hide();
                    } else if(type_of_payment == 'discount'){
                        $("#discount_figure").val(0);
                        $("#discount_figure").trigger('keyup');
                        $("#discount-list").hide();
                    }
                }

                // $("#amount_paid").trigger("keyup");
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

                var total_pending_payment_amount_in_modal = $("#amount_to_pay").val();
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
                $("#discounted_amount").trigger("keyup");
            }


            $("#cashed_amount").on("keyup", function() {
                const amount_paid = calculate_amount_paid();
                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            $("#banked_amount").on("keyup", function() {
                const amount_paid = calculate_amount_paid();
                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            $("#posed_amount").on("keyup", function() {
                const amount_paid = calculate_amount_paid();
                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            }); 
            
            $("#discounted_amount").on("keyup", function() {
                const amount_paid = calculate_amount_paid();
                $("#amount_paid").val(amount_paid);
                $("#amount_paid").trigger("keyup");
            });

            function calculate_amount_paid() {
                let cash_amount = $("#cashed_amount").val();
                let bank_amount = $("#banked_amount").val();
                let pos_amount = $("#posed_amount").val();
                let discount_amount = $("#discounted_amount").val();

                if(cash_amount == ''){
                    cash_amount = 0;
                }

                if(bank_amount == ''){
                    bank_amount = 0;
                }

                if(pos_amount == ''){
                    pos_amount = 0;
                }

                if(discount_amount == ''){
                    discount_amount = 0;
                }

                const amount_paid = parseFloat(cash_amount) + parseFloat(bank_amount) + parseFloat(pos_amount) + parseFloat(discount_amount);

                return amount_paid;
            }
        });
    </script>
@endsection
