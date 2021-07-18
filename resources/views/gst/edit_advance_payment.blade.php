@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-ledger') !!}

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            GST SetOff
                        </div>
                    </div>
                </div>
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
                    
                    <form method="POST" action="{{ route('update.advance.payment', $advance_cash_payment->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tax</th>
                                    <th>Interest</th>
                                    <th>Late Fees</th>
                                    <th>Penalty</th>
                                    <th>Other</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>IGST</th>
                                    <td><input type="text" class="form-control" name="igst_tax" id="advance_payment_igst_tax" @if($advance_cash_payment) value="{{ $advance_cash_payment->igst_tax }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="igst_interest" id="advance_payment_igst_interest" @if($advance_cash_payment) value="{{ $advance_cash_payment->igst_interest }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="igst_late_fees" id="advance_payment_igst_late_fees" @if($advance_cash_payment) value="{{ $advance_cash_payment->igst_late_fees }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="igst_penalty" id="advance_payment_igst_penalty" @if($advance_cash_payment) value="{{ $advance_cash_payment->igst_penalty }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="igst_others" id="advance_payment_igst_others" @if($advance_cash_payment) value="{{ $advance_cash_payment->igst_others }}" @endif /></td>
                                </tr>
                                <tr>
                                    <th>CGST</th>
                                    <td><input type="text" class="form-control" name="cgst_tax" id="advance_payment_cgst_tax" @if($advance_cash_payment) value="{{ $advance_cash_payment->cgst_tax }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cgst_interest" id="advance_payment_cgst_interest" @if($advance_cash_payment) value="{{ $advance_cash_payment->cgst_interest }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cgst_late_fees" id="advance_payment_cgst_late_fees" @if($advance_cash_payment) value="{{ $advance_cash_payment->cgst_late_fees }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cgst_penalty" id="advance_payment_cgst_penalty" @if($advance_cash_payment) value="{{ $advance_cash_payment->cgst_penalty }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cgst_others" id="advance_payment_cgst_others" @if($advance_cash_payment) value="{{ $advance_cash_payment->cgst_others }}" @endif /></td>
                                </tr>
                                <tr>
                                    <th>SGST</th>
                                    <td><input type="text" class="form-control" name="sgst_tax" id="advance_payment_sgst_tax" @if($advance_cash_payment) value="{{ $advance_cash_payment->sgst_tax }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="sgst_interest" id="advance_payment_sgst_interest" @if($advance_cash_payment) value="{{ $advance_cash_payment->sgst_interest }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="sgst_late_fees" id="advance_payment_sgst_late_fees" @if($advance_cash_payment) value="{{ $advance_cash_payment->sgst_late_fees }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="sgst_penalty" id="advance_payment_sgst_penalty" @if($advance_cash_payment) value="{{ $advance_cash_payment->sgst_penalty }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="sgst_others" id="advance_payment_sgst_others" @if($advance_cash_payment) value="{{ $advance_cash_payment->sgst_others }}" @endif /></td>
                                </tr>
                                <tr>
                                    <th>CESS</th>
                                    <td><input type="text" class="form-control" name="cess_tax" id="advance_payment_cess_tax" @if($advance_cash_payment) value="{{ $advance_cash_payment->cess_tax }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cess_interest" id="advance_payment_cess_interest" @if($advance_cash_payment) value="{{ $advance_cash_payment->cess_interest }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cess_late_fees" id="advance_payment_cess_late_fees" @if($advance_cash_payment) value="{{ $advance_cash_payment->cess_late_fees }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cess_penalty" id="advance_payment_cess_penalty" @if($advance_cash_payment) value="{{ $advance_cash_payment->cess_penalty }}" @endif /></td>
                                    <td><input type="text" class="form-control" name="cess_others" id="advance_payment_cess_others" @if($advance_cash_payment) value="{{ $advance_cash_payment->cess_others }}" @endif /></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6">
                                        <label>Total</label>
                                        <input type="text" class="form-control" name="total" id="total_advance_payment_taxes" placeholder="Total" value="" readonly />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        {{-- <input type="text" class="form-control" name="voucher_no" placeholder="Voucher No" @if($advance_cash_payment) value="{{ $advance_cash_payment->voucher_no }}" @endif /> --}}
                                        <label>Voucher No</label>

                                        
                                        <div class="form-group">
                                            <input placeholder="Voucher No" type="text" class="form-control" name="voucher_no" value="{{ $advance_cash_payment->voucher_no }}" required>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        <label>CIN</label>
                                        <input type="text" class="form-control" name="cin" placeholder="CIN" @if($advance_cash_payment) value="{{ $advance_cash_payment->cin }}" @endif />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        <label>Date</label>
                                        <input type="text" class="form-control custom_date" name="date" id="advance_payment_date" placeholder="DD/MM/YYYY" @if($advance_cash_payment) value="{{ \Carbon\Carbon::parse($advance_cash_payment->date)->format('d/m/Y') }}" @endif />
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Mode of Payment</label><br />
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="radio" name="type_of_payment[]" value="cash" id="cash" @if($advance_cash_payment->cash_payment > 0) checked @endif /> <label for="cash">Cash</label>
                                    </div>

                                    <div class="col-md-9">
                                        <div class="form-group" id="cash-list" @if($advance_cash_payment->cash_payment > 0) style="display: none;" @endif>
                                            <input type="hidden" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" value="{{ $advance_cash_payment->cash_payment }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="radio" name="type_of_payment[]" value="bank" id="bank" @if($advance_cash_payment->bank_payment > 0) checked @endif /> <label for="bank">Bank</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group" id="bank-list" @if(!$advance_cash_payment->bank_payment > 0) style="display: none;" @endif>
                                            <div class="form-group">
                                                <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" value="{{ $advance_cash_payment->bank_payment }}" class="form-control" readonly />
                                            </div>
                                            <div class="form-group">
                                                <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" value="{{ $advance_cash_payment->bank_cheque }}" />
                                            </div>
                                            <div class="form-group">
                                                <label>Bank List</label>
                                                <select class="form-control" name="bank">
                                                    @if(count($banks) > 0)
                                                        @foreach($banks as $bank)
                                                            <option @if($advance_cash_payment->bank_id == $bank->id) selected @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" value="{{  \Carbon\Carbon::parse($advance_cash_payment->bank_payment_date)->format('d/m/Y') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="radio" name="type_of_payment[]" value="pos" id="pos" @if($advance_cash_payment->pos_payment > 0) checked @endif /> <label for="pos">POS</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group" id="pos-bank-list" @if(!$advance_cash_payment->pos_payment > 0) style="display: none;" @endif>
                                            <div class="form-group">
                                                <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" value="{{ $advance_cash_payment->pos_payment }}" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>POS Bank List</label>
                                                <select class="form-control" name="pos_bank">
                                                    @if(count($banks) > 0)
                                                        @foreach($banks as $bank)
                                                            <option @if($advance_cash_payment->pos_bank_id == $bank->id) selected @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" value="{{ \Carbon\Carbon::parse($advance_cash_payment->pos_payment_date)->format('d/m/Y') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Amount Paid</label>
                                            <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Paid" value="{{ $advance_cash_payment->cash_payment + $advance_cash_payment->bank_payment + $advance_cash_payment->pos_payment }}" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Update Payment</button>
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
        $(document).ready(function() {
            $('input[name="type_of_payment[]"]').on("change", function(){

                var type_of_payment = $(this).val();

                // console.log("outside " + type_of_payment);

                if($(this).is(':checked')){
                    if (type_of_payment == 'bank') {
                        $("#bank-list").show();
                        $("#pos-bank-list").hide();
                        $("#cash-list").hide();
                    } else if(type_of_payment == 'pos') {
                        $("#pos-bank-list").show();
                        $("#bank-list").hide();
                        $("#cash-list").hide();
                    } else if(type_of_payment == 'cash'){
                        $("#cash-list").show();
                        $("#bank-list").hide();
                        $("#pos-bank-list").hide();
                    }
                }

            });

            $('#advance_payment_igst_tax')
            .add('#advance_payment_igst_interest')
            .add('#advance_payment_igst_late_fees')
            .add('#advance_payment_igst_penalty')
            .add('#advance_payment_igst_others')
            
            .add('#advance_payment_cgst_tax')
            .add('#advance_payment_cgst_interest')
            .add('#advance_payment_cgst_late_fees')
            .add('#advance_payment_cgst_penalty')
            .add('#advance_payment_cgst_others')

            .add('#advance_payment_sgst_tax')
            .add('#advance_payment_sgst_interest')
            .add('#advance_payment_sgst_late_fees')
            .add('#advance_payment_sgst_penalty')
            .add('#advance_payment_sgst_others')

            .add('#advance_payment_cess_tax')
            .add('#advance_payment_cess_interest')
            .add('#advance_payment_cess_late_fees')
            .add('#advance_payment_cess_penalty')
            .add('#advance_payment_cess_others')

                .on("keyup", function() {
                add_taxes();
            });

            function add_taxes(){
                var igst_tax = $('#advance_payment_igst_tax').val();
                var igst_interest = $('#advance_payment_igst_interest').val();
                var igst_late_fees = $('#advance_payment_igst_late_fees').val();
                var igst_penalty = $('#advance_payment_igst_penalty').val();
                var igst_others = $('#advance_payment_igst_others').val();

                var cgst_tax = $('#advance_payment_cgst_tax').val();
                var cgst_interest = $('#advance_payment_cgst_interest').val();
                var cgst_late_fees = $('#advance_payment_cgst_late_fees').val();
                var cgst_penalty = $('#advance_payment_cgst_penalty').val();
                var cgst_others = $('#advance_payment_cgst_others').val();

                var sgst_tax = $('#advance_payment_sgst_tax').val();
                var sgst_interest = $('#advance_payment_sgst_interest').val();
                var sgst_late_fees = $('#advance_payment_sgst_late_fees').val();
                var sgst_penalty = $('#advance_payment_sgst_penalty').val();
                var sgst_others = $('#advance_payment_sgst_others').val();

                var cess_tax = $('#advance_payment_cess_tax').val();
                var cess_interest = $('#advance_payment_cess_interest').val();
                var cess_late_fees = $('#advance_payment_cess_late_fees').val();
                var cess_penalty = $('#advance_payment_cess_penalty').val();
                var cess_others = $('#advance_payment_cess_others').val();

                if(igst_tax == ''){
                    igst_tax = 0;
                }
                if(igst_interest == ''){
                    igst_interest = 0;
                }
                if(igst_late_fees == ''){
                    igst_late_fees = 0;
                }
                if(igst_penalty == ''){
                    igst_penalty = 0;
                }
                if(igst_others == ''){
                    igst_others = 0;
                }


                if(cgst_tax == ''){
                    cgst_tax = 0;
                }
                if(cgst_interest == ''){
                    cgst_interest = 0;
                }
                if(cgst_late_fees == ''){
                    cgst_late_fees = 0;
                }
                if(cgst_penalty == ''){
                    cgst_penalty = 0;
                }
                if(cgst_others == ''){
                    cgst_others = 0;
                }


                if(sgst_tax == ''){
                    sgst_tax = 0;
                }
                if(sgst_interest == ''){
                    sgst_interest = 0;
                }
                if(sgst_late_fees == ''){
                    sgst_late_fees = 0;
                }
                if(sgst_penalty == ''){
                    sgst_penalty = 0;
                }
                if(sgst_others == ''){
                    sgst_others = 0;
                }


                if(cess_tax == ''){
                    cess_tax = 0;
                }
                if(cess_interest == ''){
                    cess_interest = 0;
                }
                if(cess_late_fees == ''){
                    cess_late_fees = 0;
                }
                if(cess_penalty == ''){
                    cess_penalty = 0;
                }
                if(cess_others == ''){
                    cess_others = 0;
                }

                var total_tax_amount = parseFloat(igst_tax) + parseFloat(igst_interest) + parseFloat(igst_late_fees) + parseFloat(igst_penalty) + parseFloat(igst_others) + parseFloat(cgst_tax) + parseFloat(cgst_interest) + parseFloat(cgst_late_fees) + parseFloat(cgst_penalty) + parseFloat(cgst_others) + parseFloat(sgst_tax) + parseFloat(sgst_interest) + parseFloat(sgst_late_fees) + parseFloat(sgst_penalty) + parseFloat(sgst_others) + parseFloat(cess_tax) + parseFloat(cess_interest) + parseFloat(cess_late_fees) + parseFloat(cess_penalty) + parseFloat(cess_others);

                $("#total_advance_payment_taxes").val(total_tax_amount);
                $("#amount_paid").val(total_tax_amount);
                $("#cashed_amount").val(total_tax_amount);
                $("#banked_amount").val(total_tax_amount);
                $("#posed_amount").val(total_tax_amount);
            }

            $("#advance_payment_cess_others").trigger("keyup");
        });
    </script>
@endsection
