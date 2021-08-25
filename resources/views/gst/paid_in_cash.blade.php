@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-paid-in-cash') !!}

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">GST Paid in Cash</div>
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

                    <form method="get" action="{{ route('gst.paid.in.cash') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <select class="form-control" name="month">
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "01" ) ) selected="selected" @endif @endif value="01">January</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "02" ) ) selected="selected" @endif @endif value="02">February</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "03" ) ) selected="selected" @endif @endif value="03">March</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "04" ) ) selected="selected" @endif @endif value="04">April</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "05" ) ) selected="selected" @endif @endif value="05">May</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "06" ) ) selected="selected" @endif @endif value="06">June</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "07" ) ) selected="selected" @endif @endif value="07">July</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "08" ) ) selected="selected" @endif @endif value="08">August</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "09" ) ) selected="selected" @endif @endif value="09">September</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "10" ) ) selected="selected" @endif @endif value="10">October</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "11" ) ) selected="selected" @endif @endif value="11">November</option>
                                        <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "12" ) ) selected="selected" @endif @endif value="12">December</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
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
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <button class="btn btn-success">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="{{ route('post.gst.paid.in.cash') }}">
                        {{ csrf_field() }}
                        @if( isset( $_GET['month'] ) )
                            <input type="hidden" name="month" value="{{ app('request')->input('month') }}" />
                        @endif
                        @if( isset( $_GET['year'] ) )
                            <input type="hidden" name="year" value="{{ app('request')->input('year') }}" />
                        @endif
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>GST Payable</th>
                                    <th>GST Payable (under reverse charge)</th>
                                    <th>GST Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>CGST</th>
                                    <td>{{ $cgst_payable }}</td>
                                    <td>0</td>
                                    <td><input placeholder="CGST" type="text" name="cgst" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>SGST</th>
                                    <td>{{ $sgst_payable }}</td>
                                    <td>0</td>
                                    <td><input placeholder="SGST" type="text" name="sgst" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>IGST</th>
                                    <td>{{ $igst_payable }}</td>
                                    <td>0</td>
                                    <td><input placeholder="IGST" type="text" name="igst" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>CESS</th>
                                    <td>{{ $cess_payable }}</td>
                                    <td>0</td>
                                    <td><input placeholder="CESS" type="text" name="cess" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>Interest</th>
                                    <td><input placeholder="Total" type="text" name="interest_payable" class="form-control" value="0" readonly /></td>
                                    <td>0</td>
                                    <td><input placeholder="Interest" type="text" name="interest" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>Late Fees</th>
                                    <td><input placeholder="Total" type="text" name="late_fees_payable" class="form-control" value="0" readonly /></td>
                                    <td>0</td>
                                    <td><input placeholder="Late Fees" type="text" name="late_fees" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>Others</th>
                                    <td><input placeholder="Total" type="text" name="others_payable" class="form-control" value="0" readonly /></td>
                                    <td>0</td>
                                    <td><input placeholder="Others" type="text" name="others" class="form-control" /></td>
                                </tr>
                                <tr>
                                    <th>Penalty</th>
                                    <td><input placeholder="Total" type="text" name="penalty_payable" class="form-control" value="0" readonly /></td>
                                    <td>0</td>
                                    <td><input placeholder="Penalty" type="text" name="penalty" class="form-control" /></td>
                                </tr>
                                @php $total_gst_payable = $cgst_payable + $sgst_payable + $igst_payable + $cess_payable; @endphp
                                <tr>
                                    <th>Total</th>
                                    <td><input placeholder="Total" type="text" name="total_gst_payable" class="form-control" value="{{ $total_gst_payable }}" readonly /></td>
                                    <td>0</td>
                                    <td><input placeholder="Total" type="text" name="total_gst_payment" class="form-control" readonly /></td>
                                </tr>
                            </tbody>
                        </table>
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
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="cin">CIN</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <input type="text" placeholder="CIN" id="cin" name="cin" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-9 col-md-offset-3">
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
    $(document).ready(function () {

        $('input[name="cgst"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="sgst"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="igst"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="cess"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="interest"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="late_fees"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="others"]').on("keyup", function () {
            add_gst_payment();
        });
        $('input[name="penalty"]').on("keyup", function () {
            add_gst_payment();
        });

        function add_gst_payment() {
            var cgst = $('input[name="cgst"]').val();
            var sgst = $('input[name="sgst"]').val();
            var igst = $('input[name="igst"]').val();
            var cess = $('input[name="cess"]').val();
            var interest = $('input[name="interest"]').val();
            var late_fees = $('input[name="late_fees"]').val();
            var others = $('input[name="others"]').val();
            var penalty = $('input[name="penalty"]').val();

            if( cgst == '' ){
                cgst = 0;
            }
            if( sgst == '' ){
                sgst = 0;
            }
            if( igst == '' ){
                igst = 0;
            }
            if( cess == '' ){
                cess = 0;
            }
            if( interest == '' ){
                interest = 0;
            }
            if( late_fees == '' ){
                late_fees = 0;
            }
            if( others == '' ){
                others = 0;
            }
            if( penalty == '' ){
                penalty = 0;
            }

            total = parseFloat(cgst) + parseFloat(sgst) + parseFloat(igst) + parseFloat(cess) + parseFloat(interest) + parseFloat(late_fees) + parseFloat(others) + parseFloat(penalty);

            $('input[name="total_gst_payment"]').val(total);

            console.log(total);
        }


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
                } else if(type_of_payment == 'pos') {
                    $("#pos-bank-list").hide();
                } else if(type_of_payment == 'cash'){
                    $("#cash-list").hide();
                }
            }


        });
    });
</script>
@endsection
