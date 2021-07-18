@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('edit-sale-party-pending-payment', request()->segment(3)) !!}

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Pending Payment</div>
                <div class="panel-body">
                    <div><strong>Amount:</strong> {{ $party_pending_payment->pending_balance }}</div>
                    <form method="POST" action="{{ route('update.sale.party.pending.payment', $party_pending_payment->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div style="padding: 10px 0; width: 100%; display: inline-block;">
                            <div class="col-md-6">
                                <strong>Party Name : </strong>{{ $associated_party->name }}
                            </div>
                            <div class="col-md-6">
                                <label>Voucher No.</label>
                                <input type="text" class="form-control" name="voucher_no" placeholder="Voucher No." value="{{ $party_pending_payment->voucher_no }}" @if(isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->bill_no_type == 'auto') readonly @endif>
                            </div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th colspan="2">Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2"><input type="text" class="form-control custom_date" name="payment_date" value="{{ \Carbon\Carbon::parse($party_pending_payment->payment_date)->format('d/m/Y') }}" placeholder="dd/mm/yyyy" required /></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                @if($party_pending_payment->tds_income_tax_checked)
                                <tr>
                                    <th>TDS Income Tax</th>
                                    <td><input type="text" class="form-control" name="tds_income_tax_amount" value="{{ $party_pending_payment->tds_income_tax_amount }}" /></td>
                                </tr>
                                @endif

                                @if($party_pending_payment->tds_gst_checked)
                                <tr>
                                    <th>TDS GST</th>
                                    <td><input type="text" class="form-control" name="tds_gst_amount" value="{{ $party_pending_payment->tds_gst_amount }}" /></td>
                                </tr>
                                @endif

                                @if($party_pending_payment->tcs_income_tax_checked)
                                <tr>
                                    <th>TCS Income Tax</th>
                                    <td><input type="text" class="form-control" name="tcs_income_tax_amount" value="{{ $party_pending_payment->tcs_income_tax_amount }}" /></td>
                                </tr>
                                @endif

                                @if($party_pending_payment->tcs_gst_checked)
                                <tr>
                                    <th>TCS GST</th>
                                    <td><input type="text" class="form-control" name="tcs_gst_amount" value="{{ $party_pending_payment->tcs_gst_amount }}" /></td>
                                </tr>
                                @endif


                                {{-- <tr>
                                    <th>Type of Payment</th>
                                    <td>
                                        <div class="form-group">
                                            <label>Type of Payment</label><br />
                                            <input type="radio" name="type_of_payment" value="cash" id="cash" @if ($party_pending_payment->type_of_payment == 'cash') checked @endif /> <label for="cash">Cash</label>
                                            <input type="radio" name="type_of_payment" value="bank" id="bank" @if ($party_pending_payment->type_of_payment == 'bank') checked @endif /> <label for="bank">Bank</label>
                                        </div>

                                        <div class="form-group" id="bank-list" @if ($party_pending_payment->type_of_payment == 'cash') style="display: none;" @endif>
                                            <div class="form-group">
                                            <input type="text" name="bank_cheque" id="bank_cheque" class="form-control" placeholder="Bank Cheque No." value="{{ $party_pending_payment->bank_cheque }}" />
                                            </div>
                                            <div class="form-group">
                                                <select class="form-control" name="bank" id="bank-select-list">
                                                    @if(count($banks) > 0)
                                                        @foreach($banks as $bank)
                                                            <option @if($party_pending_payment->type_of_payment == 'bank' && $party_pending_payment->bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                </tr> --}}
                            </tfoot>
                        </table>
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'cash+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+pos' || 
                                    $party_pending_payment->type_of_payment == 'bank+cash' || 
                                    $party_pending_payment->type_of_payment == 'pos+cash' || 
                                    $party_pending_payment->type_of_payment == 'cash' ) checked="checked" @endif @endif /> <label for="cash">Cash</label>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group" id="cash-list" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'cash+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+pos' || 
                                    $party_pending_payment->type_of_payment == 'bank+cash' || 
                                    $party_pending_payment->type_of_payment == 'pos+cash' || 
                                    $party_pending_payment->type_of_payment == 'cash' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" value="{{ $party_pending_payment->cash_payment }}" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'bank+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+pos' || 
                                    $party_pending_payment->type_of_payment == 'bank+cash' || 
                                    $party_pending_payment->type_of_payment == 'pos+bank' || 
                                    $party_pending_payment->type_of_payment == 'bank' ) checked="checked" @endif @endif /> <label for="bank">Bank</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group" id="bank-list" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'bank+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+pos' || 
                                    $party_pending_payment->type_of_payment == 'bank+cash' || 
                                    $party_pending_payment->type_of_payment == 'pos+bank' || 
                                    $party_pending_payment->type_of_payment == 'bank' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" value="{{ $party_pending_payment->bank_payment }}" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" value="{{ $party_pending_payment->bank_cheque }}" />
                                        </div>
                                        <div class="form-group">
                                            <label>Bank List</label>
                                            <select class="form-control" name="bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option @if($party_pending_payment->bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
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
                                    <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'bank+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+pos' || 
                                    $party_pending_payment->type_of_payment == 'pos+cash' || 
                                    $party_pending_payment->type_of_payment == 'pos+bank' || 
                                    $party_pending_payment->type_of_payment == 'pos' ) checked="checked" @endif @endif /> <label for="pos">POS</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group" id="pos-bank-list" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'bank+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+pos' || 
                                    $party_pending_payment->type_of_payment == 'pos+cash' || 
                                    $party_pending_payment->type_of_payment == 'pos+bank' || 
                                    $party_pending_payment->type_of_payment == 'pos' ) style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" value="{{ $party_pending_payment->pos_payment }}" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option @if($party_pending_payment->pos_bank_id == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
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
                                    <input type="checkbox" name="type_of_payment[]" value="discount" id="discount" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'bank+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+discount' || 
                                    $party_pending_payment->type_of_payment == 'pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'discount' ) checked="checked" @endif @endif /> <label for="discount">Cash Discount</label>
                                </div>

                                <div class="col-md-8">
                                    <div id="discount-list" class="form-group" @if(isset($party_pending_payment->type_of_payment) && $party_pending_payment->type_of_payment != "no_payment") @if( 
                                    $party_pending_payment->type_of_payment == 'combined' || 
                                    $party_pending_payment->type_of_payment == 'bank+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'bank+discount' || 
                                    $party_pending_payment->type_of_payment == 'cash+discount' || 
                                    $party_pending_payment->type_of_payment == 'pos+discount' || 
                                    $party_pending_payment->type_of_payment == 'discount' ) style="display:block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                        <div class="form-group">
                                            <label>Discount Type</label>
                                            <select class="form-control" name="discount_type" id="discount_type">
                                                {{-- <option disabled selected>Discount Type</option> --}}
                                                <option @if($party_pending_payment->discount_type == "fixed") selected="selected" @endif value="fixed">Rs</option>
                                                <option @if($party_pending_payment->discount_type == "percent") selected="selected" @endif value="percent">%</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Discount Figure" name="discount_figure" id="discount_figure" class="form-control" value="{{ $party_pending_payment->discount_figure }}" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Discount Amount" name="discounted_amount" id="discounted_amount" class="form-control" value="{{ $party_pending_payment->discount_payment }}" readonly />
                                            <hr/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Amount Paid</label>
                            <input type="text" class="form-control" id="amount" name="amount" value="{{ $party_pending_payment->amount }}" required />
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks">{{ $party_pending_payment->remarks }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success">Update Payment</button>
                            <button type="button" class="btn btn-default" @if($party_pending_payment->status == 1) onclick="event.preventDefault(); document.getElementById('cancel-payment-form').submit();" @endif>{{ $party_pending_payment->status == 1 ? 'Cancel' : 'Cancelled' }}</button>
                        </div>
                    </form>
                    <form id="cancel-payment-form" method="POST" action="{{ route('cancel.sale.party.payment', $party_pending_payment->id) }}">
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

            function calculate_amount() {
                var cash_amount = $("#cashed_amount").val() || 0;
                var bank_amount = $("#banked_amount").val() || 0;
                var pos_amount = $("#posed_amount").val() || 0;
                var discount_amount = $("#discounted_amount").val() || 0;

                var amount = parseFloat(cash_amount) + parseFloat(bank_amount) + parseFloat(pos_amount) + parseFloat(discount_amount);

                $("#amount").val(amount);
            }

            function calculate_discount() {
                var discount_type = $("#discount_type option:selected").val();

                var discount_figure = $("#discount_figure").val() || 0;
                // pending balance
                var total_amount = '{{ $party_pending_payment->pending_balance }}';

                if(discount_type == 'percent'){
                    discount_figure = Number((discount_figure * total_amount) / 100).toFixed(2);
                }

                $("#discounted_amount").val(discount_figure);
            }

        });
    </script>
@endsection