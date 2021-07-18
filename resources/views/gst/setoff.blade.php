@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-ledger') !!}

<div class="container">
    {{-- <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <form class="form-horizontal" action="{{ route('gst.setoff') }}" method="get">
                <div class="form-group">
                    <div class="col-md-5">
                        <select class="form-control" name="month">
                            <option selected disabled>Select Month</option>
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
                    <div class="col-md-4">
                        <select class="form-control" name="year">
                            <option selected disabled>Select Year</option>
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
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            GST SetOff
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
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
                    
                    {{-- <div class="row">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <button type="button" id="form1_make_payment" class="btn btn-success">Make Payment</button>
                            </div>
                        </div>
                    </div> --}}

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2">GST Setoff</th>
                                <th colspan="6"></th>
                            </tr>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th rowspan="2">Other than Reverse charge tax payable</th>
                                @if(auth()->user()->profile->registered != 3)
                                <th colspan="4">Paid through ITC(input)</th>
                                @endif
                                <th rowspan="2">Paid through GST Cash Ledger</th>
                                <th rowspan="2">Balance to be paid in cash</th>
                            </tr>
                            @if(auth()->user()->profile->registered != 3)
                            <tr>
                                <th>IGST</th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>CESS</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            <form id="other_than_reverse_charge_form">
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" id="ot_reverse_charge_igst" value="{{ $other_than_reverse_charge_igst }}" readonly /></td>
                                @if(auth()->user()->profile->registered != 3)
                                <td><input type="text" class="form-control" id="otr_input_igst_igst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_igst_cgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_igst_sgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_igst_cess" /></td>
                                @endif
                                <td><input type="text" class="form-control" id="otr_ptgcl_igst" /></td>
                                <td><input type="text" class="form-control" id="otr_btbpic_igst" readonly value="{{ $other_than_reverse_charge_igst }}" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" id="otreverse_charge_cgst" value="{{ $other_than_reverse_charge_cgst }}" readonly /></td>
                                @if(auth()->user()->profile->registered != 3)
                                <td><input type="text" class="form-control" id="otr_input_cgst_igst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_cgst_cgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_cgst_sgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_cgst_cess" /></td>
                                @endif
                                <td><input type="text" class="form-control" id="otr_ptgcl_cgst" /></td>
                                <td><input type="text" class="form-control" id="otr_btbpic_cgst" readonly value="{{ $other_than_reverse_charge_cgst }}" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" id="otreverse_charge_sgst" value="{{ $other_than_reverse_charge_sgst }}" readonly /></td>
                                @if(auth()->user()->profile->registered != 3)
                                <td><input type="text" class="form-control" id="otr_input_sgst_igst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_sgst_cgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_sgst_sgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_sgst_cess" /></td>
                                @endif
                                <td><input type="text" class="form-control" id="otr_ptgcl_sgst" /></td>
                                <td><input type="text" class="form-control" id="otr_btbpic_sgst" readonly  value="{{ $other_than_reverse_charge_sgst }}" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" id="otreverse_charge_cess" value="{{ $other_than_reverse_charge_cess }}" readonly /></td>
                                @if(auth()->user()->profile->registered != 3)
                                <td><input type="text" class="form-control" id="otr_input_cess_igst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_cess_cgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_cess_sgst" /></td>
                                <td><input type="text" class="form-control" id="otr_input_cess_cess" /></td>
                                @endif
                                <td><input type="text" class="form-control" id="otr_ptgcl_cess" /></td>
                                <td><input type="text" class="form-control" id="otr_btbpic_cess" readonly value="{{ $other_than_reverse_charge_cess }}" /></td>
                            </tr>
                            <tr style="display: none;" id="setoff_button_block">
                                <td colspan="6"></td>
                                <td colspan="1">
                                    <input type="text" class="form-control custom_date" id="otr_date" placeholder="DD/MM/YYYY" />
                                </td>
                                <td colspan="1">
                                    <button type="submit" class="btn btn-success btn-block">Setoff</button>
                                </td>
                            </tr>
                            <tr style="display: none;" id="make_payment_button_block">
                                <td colspan="6"></td>
                                <td colspan="2">
                                    <button type="button" id="form1_make_payment" class="btn btn-success btn-block">Make Payment</button>
                                </td>
                            </tr>
                            </form>
                        </tbody>
                    </table>

                    <hr/>

                    {{-- <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <button type="button" id="form2_make_payment" class="btn btn-success">Make Payment</button>
                            </div>
                        </div>
                    </div> --}}

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>GST Setoff</th>
                                <th>Reverse charge tax payable</th>
                                <th>Paid through GST Cash Ledger</th>
                                <th>Balance to be paid in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form id="reverse_charge_form">
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" id="reverse_charge_igst" value="{{ $reverse_charge_igst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_ptgcl_igst" /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_btbpic_igst" readonly value="{{ $reverse_charge_igst }}" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" id="reverse_charge_cgst" value="{{ $reverse_charge_cgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_ptgcl_cgst" /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_btbpic_cgst" readonly value="{{ $reverse_charge_cgst }}" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" id="reverse_charge_sgst" value="{{ $reverse_charge_sgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_ptgcl_sgst" /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_btbpic_sgst" readonly value="{{ $reverse_charge_sgst }}" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" id="reverse_charge_cess" value="{{ $reverse_charge_cess }}" readonly /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_ptgcl_cess" /></td>
                                <td><input type="text" class="form-control" id="reverse_charge_btbpic_cess" readonly value="{{ $reverse_charge_cess }}" /></td>
                            </tr>
                            <tr style="display: none;" id="reverse_charge_setoff_button_block">
                                <td colspan="2"></td>
                                <td colspan="1">
                                    <input type="text" class="form-control custom_date" id="r_date" placeholder="DD/MM/YYYY" />
                                </td>
                                <td colspan="1">
                                    <button type="submit" class="btn btn-success btn-block">Setoff</button>
                                </td>
                            </tr>
                            <tr style="display: none;" id="reverse_charge_make_payment_button_block">
                                <td colspan="3"></td>
                                <td colspan="1">
                                    <button type="button" id="form2_make_payment" class="btn btn-success btn-block">Make Payment</button>
                                </td>
                            </tr>
                            </form>
                        </tbody>
                    </table>

                    <hr/>
                    {{-- <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <button type="button" id="form3_make_payment" class="btn btn-success">Make Payment</button>
                            </div>
                        </div>
                    </div> --}}

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Late Fees</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form id="liability_latefees_form">
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" id="liability_igst_latefees" value="{{ $gst_liability_late_fees_igst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_latefees_igst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_latefees_igst" readonly value="{{ $gst_liability_late_fees_igst }}" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" id="liability_cgst_latefees" value="{{ $gst_liability_late_fees_cgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_latefees_cgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_latefees_cgst" readonly value="{{ $gst_liability_late_fees_cgst }}" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" id="liability_sgst_latefees" value="{{ $gst_liability_late_fees_sgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_latefees_sgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_latefees_sgst" readonly value="{{ $gst_liability_late_fees_sgst }}" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" id="liability_cess_latefees" value="{{ $gst_liability_late_fees_cess }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_latefees_cess" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_latefees_cess" readonly value="{{ $gst_liability_late_fees_cess }}" /></td>
                            </tr>
                            <tr style="display: none;" id="liability_setoff_latefees_button_block">
                                <td colspan="2"></td>
                                <td class="setoff-liability-latefees">
                                    <input type="text" class="form-control custom_date" id="liability_latefees_date" placeholder="DD/MM/YYYY" />
                                </td>
                                <td class="setoff-liability-latefees">
                                    <button type="submit" class="btn btn-success btn-block">Setoff</button>
                                </td>
                            </tr>
                            <tr style="display: none;" id="liability_make_payment_latefees_button_block">
                                <td colspan="3"></td>
                                <td class="liability-makepayment-latefees">
                                    <button type="button" id="form3_make_payment_latefees" class="btn btn-success btn-block">Make Payment</button>
                                </td>
                            </tr>
                            </form>
                        </tbody>
                    </table>
                    <hr/>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Interest</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form id="liability_interest_form">
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" id="liability_igst_interest" value="{{ $gst_liability_interest_igst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_interest_igst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_interest_igst" readonly value="{{ $gst_liability_interest_igst }}" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" id="liability_cgst_interest" value="{{ $gst_liability_interest_cgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_interest_cgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_interest_cgst" readonly value="{{ $gst_liability_interest_cgst }}" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" id="liability_sgst_interest" value="{{ $gst_liability_interest_sgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_interest_sgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_interest_sgst" readonly value="{{ $gst_liability_interest_sgst }}" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" id="liability_cess_interest" value="{{ $gst_liability_interest_cess }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_interest_cess" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_interest_cess" readonly value="{{ $gst_liability_interest_cess }}" /></td>
                            </tr>
                            <tr style="display: none;" id="liability_setoff_interest_button_block">
                                <td colspan="2"></td>
                                <td class="setoff-liability-interest">
                                    <input type="text" class="form-control custom_date" id="liability_interest_date" placeholder="DD/MM/YYYY" />
                                </td>
                                <td class="setoff-liability-interest">
                                    <button type="submit" class="btn btn-success btn-block">Setoff</button>
                                </td>
                            </tr>
                            <tr style="display: none;" id="liability_make_payment_interest_button_block">
                                <td colspan="3"></td>
                                <td class="liability-makepayment-interest">
                                    <button type="button" id="form3_make_payment_interest" class="btn btn-success btn-block">Make Payment</button>
                                </td>
                            </tr>
                            </form>
                        </tbody>
                    </table>
                    <hr/>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Penalty</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form id="liability_penalty_form">
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" id="liability_igst_penalty" value="{{ $gst_liability_penalty_igst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_penalty_igst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_penalty_igst" readonly value="{{ $gst_liability_penalty_igst }}" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" id="liability_cgst_penalty" value="{{ $gst_liability_penalty_cgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_penalty_cgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_penalty_cgst" readonly value="{{ $gst_liability_penalty_cgst }}" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" id="liability_sgst_penalty" value="{{ $gst_liability_penalty_sgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_penalty_sgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_penalty_sgst" readonly value="{{ $gst_liability_penalty_sgst }}" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" id="liability_cess_penalty" value="{{ $gst_liability_penalty_cess }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_penalty_cess" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_penalty_cess" readonly value="{{ $gst_liability_penalty_cess }}" /></td>
                            </tr>
                            <tr style="display: none;" id="liability_setoff_penalty_button_block">
                                <td colspan="2"></td>
                                <td class="setoff-liability-penalty">
                                    <input type="text" class="form-control custom_date" id="liability_penalty_date" placeholder="DD/MM/YYYY" />
                                </td>
                                <td class="setoff-liability-penalty">
                                    <button type="submit" class="btn btn-success btn-block">Setoff</button>
                                </td>
                            </tr>
                            <tr style="display: none;" id="liability_make_payment_penalty_button_block">
                                <td colspan="3"></td>
                                <td class="liability-makepayment-penalty">
                                    <button type="button" id="form3_make_payment_penalty" class="btn btn-success btn-block">Make Payment</button>
                                </td>
                            </tr>
                            </form>
                        </tbody>
                    </table>
                    <hr/>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Others</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form id="liability_others_form">
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" id="liability_igst_others" value="{{ $gst_liability_others_igst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_others_igst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_others_igst" readonly value="{{ $gst_liability_others_igst }}" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" id="liability_cgst_others" value="{{ $gst_liability_others_cgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_others_cgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_others_cgst" readonly value="{{ $gst_liability_others_cgst }}" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" id="liability_sgst_others" value="{{ $gst_liability_others_sgst }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_others_sgst" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_others_sgst" readonly value="{{ $gst_liability_others_sgst }}" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" id="liability_cess_others" value="{{ $gst_liability_others_cess }}" readonly /></td>
                                <td><input type="text" class="form-control" id="liability_ptgcl_others_cess" /></td>
                                <td><input type="text" class="form-control" id="liability_btbpic_others_cess" readonly value="{{ $gst_liability_others_cess }}" /></td>
                            </tr>
                            <tr style="display: none;" id="liability_setoff_others_button_block">
                                <td colspan="2"></td>
                                <td class="setoff-liablity-others">
                                    <input type="text" class="form-control custom_date" id="liability_others_date" placeholder="DD/MM/YYYY" />
                                </td>
                                <td class="setoff-liablity-others">
                                    <button type="submit" class="btn btn-success btn-block">Setoff</button>
                                </td>
                            </tr>
                            <tr style="display: none;" id="liability_make_payment_others_button_block">
                                <td colspan="3"></td>
                                <td class="liability-makepayment-others">
                                    <button type="button" id="form3_make_payment_others" class="btn btn-success btn-block">Make Payment</button>
                                </td>
                            </tr>
                            </form>
                        </tbody>
                    </table>
                    <hr/>

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="form1_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Make Payment</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('post.gst.setoff') }}" id="other_than_reverse_charge_form_make_payment">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="other_than_reverse_charge" id="form1_type" />
                    {{-- <div class="form-group">
                        <label for="form1_cgst" class="col-md-4 control-label">CGST</label>

                        <div class="col-md-6">
                            <input id="form1_cgst" type="text" class="form-control" name="cgst" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="form1_sgst" class="col-md-4 control-label">SGST</label>

                        <div class="col-md-6">
                            <input id="form1_sgst" type="text" class="form-control" name="sgst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_igst" class="col-md-4 control-label">IGST</label>

                        <div class="col-md-6">
                            <input id="form1_igst" type="text" class="form-control" name="igst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_cess" class="col-md-4 control-label">CESS</label>

                        <div class="col-md-6">
                            <input id="form1_cess" type="text" class="form-control" name="cess" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_total" class="col-md-4 control-label">Total</label>

                        <div class="col-md-6">
                            <input id="form1_total" type="text" class="form-control" name="total" readonly>
                        </div>
                    </div>
                    @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                    <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                        <label for="form1_voucherNo" class="col-md-4 control-label">Voucher No</label>

                        <div class="col-md-6">
                            <input id="form1_voucherNo" type="text" class="form-control" name="voucher_no" @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) @else @if(isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @endif @endif @endif>
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
                    </div>

                    <div class="form-group">
                        <label for="form1_date" class="col-md-4 control-label">Date</label>

                        <div class="col-md-6">
                            <input id="form1_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_cin" class="col-md-4 control-label">CIN</label>

                        <div class="col-md-6">
                            <input id="form1_cin" type="text" class="form-control" name="cin">
                        </div>
                    </div>   

                    <div class="col-md-8 col-md-offset-2">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form1_type_of_payment" value="cash" id="form1_cash_checkbox" /> <label for="form1_cash_checkbox">Cash</label>
                                </div>
    
                                <div class="col-md-9">
                                    <div class="form-group" id="form1_cash-list" style="display: none;">
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="form1_cashed_amount" class="form-control" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" class="form1_type_of_payment" value="bank" id="form1_bank_checkbox" /> <label for="form1_bank_checkbox">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form1_bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="form1_banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="form1_bank_cheque" name="bank_cheque" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="form1_bank">Bank List</label>
                                            <select class="form-control" name="bank" id="form1_bank">
                                                <option selected disabled>Select Bank</option>
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
                                    <input type="checkbox" name="type_of_payment[]" class="form1_type_of_payment" value="pos" id="form1_pos_checkbox" /> <label for="form1_pos_checkbox">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form1_pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="form1_posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="form1_pos">POS Bank List</label>
                                            <select class="form-control" name="pos_bank" id="form1_pos">
                                                <option selected disabled>Select Bank</option>
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
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Amount Paid</label>
                                        <input class="form-control" type="text" id="form1_amount_paid" name="amount_received" placeholder="Amount Received" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>CGST</th>
                                <td><input id="form1_cgst" type="text" class="form-control" name="cgst" readonly></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input id="form1_sgst" type="text" class="form-control" name="sgst" readonly></td>
                            </tr>
                            <tr>
                                <th>IGST</th>
                                <td><input id="form1_igst" type="text" class="form-control" name="igst" readonly></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input id="form1_cess" type="text" class="form-control" name="cess" readonly></td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td><input id="form1_total" type="text" class="form-control" name="total" readonly></td>
                            </tr>
                            <tr>
                                <th>Voucher No</th>
                                @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                                <td>
                                    <input id="form1_voucherNo" type="text" class="form-control" name="voucher_no" @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @endif @endif @endif>
                                    @if ($myerrors->has('voucher_no'))
                                        <span class="help-block">
                                            <ul>
                                                @foreach( $myerrors['voucher_no'] as $error )
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td><input id="form1_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY"></td>
                            </tr>
                            <tr>
                                <th>CIN</th>
                                <td><input id="form1_cin" type="text" class="form-control" name="cin"></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form1_type_of_payment" value="cash" id="form1_cash_checkbox" /> <label for="form1_cash_checkbox">Cash</label>
                                </div>
    
                                <div class="col-md-9">
                                    <div class="form-group" id="form1_cash-list" style="display: none;">
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="form1_cashed_amount" class="form-control" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" class="form1_type_of_payment" value="bank" id="form1_bank_checkbox" /> <label for="form1_bank_checkbox">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form1_bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="form1_banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="form1_bank_cheque" name="bank_cheque" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="form1_bank">Bank List</label>
                                            <select class="form-control" name="bank" id="form1_bank">
                                                <option selected disabled>Select Bank</option>
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form1_type_of_payment" value="pos" id="form1_pos_checkbox" /> <label for="form1_pos_checkbox">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form1_pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="form1_posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="form1_pos">POS Bank List</label>
                                            <select class="form-control" name="pos_bank" id="form1_pos">
                                                <option selected disabled>Select Bank</option>
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
                        </div>
                    </div>
                    {{-- <div class="row"> --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Amount Paid</label>
                                <input class="form-control" type="text" id="form1_amount_paid" name="amount_received" placeholder="Amount Received" readonly />
                            </div>
                        </div>
                    {{-- </div> --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="form2_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Make Payment</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('post.gst.setoff') }}" id="reverse_charge_form_make_payment">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="reverse_charge" id="form2_type" />
                    <div class="form-group">
                        <label for="form2_cgst" class="col-md-4 control-label">CGST</label>

                        <div class="col-md-6">
                            <input id="form2_cgst" type="text" class="form-control" name="cgst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form2_sgst" class="col-md-4 control-label">SGST</label>

                        <div class="col-md-6">
                            <input id="form2_sgst" type="text" class="form-control" name="sgst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form2_igst" class="col-md-4 control-label">IGST</label>

                        <div class="col-md-6">
                            <input id="form2_igst" type="text" class="form-control" name="igst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form2_cess" class="col-md-4 control-label">CESS</label>

                        <div class="col-md-6">
                            <input id="form2_cess" type="text" class="form-control" name="cess" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form2_total" class="col-md-4 control-label">Total</label>

                        <div class="col-md-6">
                            <input id="form2_total" type="text" class="form-control" name="total" readonly>
                        </div>
                    </div>
                    @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                    <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                        <label for="form2_voucherNo" class="col-md-4 control-label">Voucher No</label>

                        <div class="col-md-6">
                            <input id="form2_voucherNo" type="text" class="form-control" name="voucher_no" @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @endif @endif @endif>
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
                    </div>

                    <div class="form-group">
                        <label for="form2_date" class="col-md-4 control-label">Date</label>

                        <div class="col-md-6">
                            <input id="form2_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form2_cin" class="col-md-4 control-label">CIN</label>

                        <div class="col-md-6">
                            <input id="form2_cin" type="text" class="form-control" name="cin">
                        </div>
                    </div>

                    <div class="col-md-8 col-md-offset-2">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form2_type_of_payment" value="cash" id="form2_cash_checkbox" /> <label for="form2_cash_checkbox">Cash</label>
                                </div>
    
                                <div class="col-md-9">
                                    <div class="form-group" id="form2_cash-list" style="display: none;">
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="form2_cashed_amount" class="form-control" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form2_type_of_payment" value="bank" id="form2_bank_checkbox" /> <label for="form2_bank_checkbox">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form2_bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="form2_banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="form2_bank_cheque" name="bank_cheque" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>Bank List</label>
                                            <select class="form-control" name="bank" id="form2_bank">
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
                                    <input type="checkbox" name="type_of_payment[]" class="form2_type_of_payment" value="pos" id="form2_pos_checkbox" /> <label for="form2_pos_checkbox">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form2_pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="form2_posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank" id="form2_pos">
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
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Amount Paid</label>
                                        <input class="form-control" type="text" id="form2_amount_paid" name="amount_received" placeholder="Amount Received" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-4">
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="form3_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Make Payment</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('post.gst.setoff') }}" id="liability_form_make_payment">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="liability_charge" id="form3_type" />
                    <input type="hidden" name="type" value="" id="form3_setoff_type" />
                    <div class="form-group">
                        <label for="form3_cgst" class="col-md-4 control-label">CGST</label>

                        <div class="col-md-6">
                            <input id="form3_cgst" type="text" class="form-control" name="cgst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form3_sgst" class="col-md-4 control-label">SGST</label>

                        <div class="col-md-6">
                            <input id="form3_sgst" type="text" class="form-control" name="sgst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form3_igst" class="col-md-4 control-label">IGST</label>

                        <div class="col-md-6">
                            <input id="form3_igst" type="text" class="form-control" name="igst" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form3_cess" class="col-md-4 control-label">CESS</label>

                        <div class="col-md-6">
                            <input id="form3_cess" type="text" class="form-control" name="cess" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form3_total" class="col-md-4 control-label">Total</label>

                        <div class="col-md-6">
                            <input id="form3_total" type="text" class="form-control" name="total" readonly>
                        </div>
                    </div>
                    @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                    <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                        <label for="form3_voucherNo" class="col-md-4 control-label">Voucher No</label>

                        <div class="col-md-6">
                            <input id="form3_voucherNo" type="text" class="form-control" name="voucher_no" @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @endif @endif @endif>
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
                    </div>

                    <div class="form-group">
                        <label for="form3_date" class="col-md-4 control-label">Date</label>

                        <div class="col-md-6">
                            <input id="form3_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form3_cin" class="col-md-4 control-label">CIN</label>

                        <div class="col-md-6">
                            <input id="form3_cin" type="text" class="form-control" name="cin">
                        </div>
                    </div>

                    <div class="col-md-8 col-md-offset-2">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form3_type_of_payment" value="cash" id="form3_cash_checkbox" /> <label for="form3_cash_checkbox">Cash</label>
                                </div>
    
                                <div class="col-md-9">
                                    <div class="form-group" id="form3_cash-list" style="display: none;">
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="form3_cashed_amount" class="form-control" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" class="form3_type_of_payment" value="bank" id="form3_bank_checkbox" /> <label for="form3_bank_checkbox">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form3_bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="form3_banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="form3_bank_cheque" name="bank_cheque" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>Bank List</label>
                                            <select class="form-control" name="bank" id="form3_bank">
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
                                    <input type="checkbox" name="type_of_payment[]" class="form3_type_of_payment" value="pos" id="form3_pos_checkbox" /> <label for="form3_pos_checkbox">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="form3_pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="form3_posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank" id="form3_pos">
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
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Amount Paid</label>
                                        <input class="form-control" type="text" id="form3_amount_paid" name="amount_received" placeholder="Amount Paid" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-4">
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        Number.prototype.toFixedDown = function(digits) {
            var re = new RegExp("(\\d+\\.\\d{" + digits + "})(\\d)"),
                m = this.toString().match(re);
            return m ? parseFloat(m[1]) : this.valueOf();
        };
        
        $("#form1_make_payment").on("click", function () {

            var setoff_balance_igst = $("#otr_btbpic_igst").val();

            var setoff_balance_cgst = $("#otr_btbpic_cgst").val();

            var setoff_balance_sgst = $("#otr_btbpic_sgst").val();

            var setoff_balance_cess = $("#otr_btbpic_cess").val();

            if(setoff_balance_igst == ''){
                setoff_balance_igst = 0;
            }

            if(setoff_balance_cgst == ''){
                setoff_balance_cgst = 0;
            }

            if(setoff_balance_sgst == ''){
                setoff_balance_sgst = 0;
            }

            if(setoff_balance_cess == ''){
                setoff_balance_cess = 0;
            }

            var total_balance = parseFloat(setoff_balance_cess) + parseFloat(setoff_balance_cgst) + parseFloat(setoff_balance_igst) + parseFloat(setoff_balance_sgst);

            $("#other_than_reverse_charge_form_make_payment").trigger("reset");

            $("#form1_igst").val(setoff_balance_igst);
            $("#form1_cgst").val(setoff_balance_cgst);
            $("#form1_sgst").val(setoff_balance_sgst);
            $("#form1_cess").val(setoff_balance_cess);
            $("#form1_total").val(total_balance);

            $("#form1_modal").modal("show");
        });

        $("#form2_make_payment").on("click", function () {

            var setoff_balance_igst = $("#reverse_charge_btbpic_igst").val();

            var setoff_balance_cgst = $("#reverse_charge_btbpic_cgst").val();

            var setoff_balance_sgst = $("#reverse_charge_btbpic_sgst").val();

            var setoff_balance_cess = $("#reverse_charge_btbpic_cess").val();

            if(setoff_balance_igst == ''){
                setoff_balance_igst = 0;
            }

            if(setoff_balance_cgst == ''){
                setoff_balance_cgst = 0;
            }

            if(setoff_balance_sgst == ''){
                setoff_balance_sgst = 0;
            }

            if(setoff_balance_cess == ''){
                setoff_balance_cess = 0;
            }

            var total_balance = parseFloat(setoff_balance_cess) + parseFloat(setoff_balance_cgst) + parseFloat(setoff_balance_igst) + parseFloat(setoff_balance_sgst);

            $("#reverse_charge_form_make_payment").trigger("reset");

            $("#form2_igst").val(setoff_balance_igst);
            $("#form2_cgst").val(setoff_balance_cgst);
            $("#form2_sgst").val(setoff_balance_sgst);
            $("#form2_cess").val(setoff_balance_cess);
            $("#form2_total").val(total_balance);

            $("#form2_modal").modal("show");
        });

        $("#form3_make_payment_latefees").on("click", function () {

            var setoff_balance_latefees_igst = $("#liability_btbpic_latefees_igst").val();
            var setoff_balance_latefees_cgst = $("#liability_btbpic_latefees_cgst").val();
            var setoff_balance_latefees_sgst = $("#liability_btbpic_latefees_sgst").val();
            var setoff_balance_latefees_cess = $("#liability_btbpic_latefees_cess").val();
            

            if(setoff_balance_latefees_igst == ''){
                setoff_balance_latefees_igst = 0;
            }

            if(setoff_balance_latefees_cgst == ''){
                setoff_balance_latefees_cgst = 0;
            }

            if(setoff_balance_latefees_sgst == ''){
                setoff_balance_latefees_sgst = 0;
            }

            if(setoff_balance_latefees_cess == ''){
                setoff_balance_latefees_cess = 0;
            }

            var total_balance = parseFloat(setoff_balance_latefees_igst) + parseFloat(setoff_balance_latefees_cgst) + parseFloat(setoff_balance_latefees_sgst) + parseFloat(setoff_balance_latefees_cess);

            $("#liability_form_make_payment").trigger("reset");

            $("#form3_setoff_type").val('liability');
            $("#form3_igst").val(setoff_balance_latefees_igst);
            $("#form3_cgst").val(setoff_balance_latefees_cgst);
            $("#form3_sgst").val(setoff_balance_latefees_sgst);
            $("#form3_cess").val(setoff_balance_latefees_cess);
            $("#form3_total").val(total_balance);
            $("#form3_type").val("liability_latefees_charge");


            $("#form3_modal").modal("show");
        });

        $("#form3_make_payment_interest").on("click", function () {

            var setoff_balance_interest_igst = $("#liability_btbpic_interest_igst").val();
            var setoff_balance_interest_cgst = $("#liability_btbpic_interest_cgst").val();
            var setoff_balance_interest_sgst = $("#liability_btbpic_interest_sgst").val();
            var setoff_balance_interest_cess = $("#liability_btbpic_interest_cess").val();


            if(setoff_balance_interest_igst == ''){
                setoff_balance_interest_igst = 0;
            }

            if(setoff_balance_interest_cgst == ''){
                setoff_balance_interest_cgst = 0;
            }

            if(setoff_balance_interest_sgst == ''){
                setoff_balance_interest_sgst = 0;
            }

            if(setoff_balance_interest_cess == ''){
                setoff_balance_interest_cess = 0;
            }


            var total_balance = parseFloat(setoff_balance_interest_igst) + parseFloat(setoff_balance_interest_cgst) + parseFloat(setoff_balance_interest_sgst) + parseFloat(setoff_balance_interest_cess);

            $("#liability_form_make_payment").trigger("reset");

            $("#form3_setoff_type").val('interest');
            $("#form3_igst").val(setoff_balance_interest_igst);
            $("#form3_cgst").val(setoff_balance_interest_cgst);
            $("#form3_sgst").val(setoff_balance_interest_sgst);
            $("#form3_cess").val(setoff_balance_interest_cess);
            $("#form3_total").val(total_balance);
            $("#form3_type").val("liability_interest_charge");


            $("#form3_modal").modal("show");
        });

        $("#form3_make_payment_penalty").on("click", function () {

            var setoff_balance_penalty_igst = $("#liability_btbpic_penalty_igst").val();
            var setoff_balance_penalty_cgst = $("#liability_btbpic_penalty_cgst").val();
            var setoff_balance_penalty_sgst = $("#liability_btbpic_penalty_sgst").val();
            var setoff_balance_penalty_cess = $("#liability_btbpic_penalty_cess").val();

            if(setoff_balance_penalty_igst == ''){
                setoff_balance_penalty_igst = 0;
            }

            if(setoff_balance_penalty_cgst == ''){
                setoff_balance_penalty_cgst = 0;
            }

            if(setoff_balance_penalty_sgst == ''){
                setoff_balance_penalty_sgst = 0;
            }

            if(setoff_balance_penalty_cess == ''){
                setoff_balance_penalty_cess = 0;
            }

            var total_balance = parseFloat(setoff_balance_penalty_igst) + parseFloat(setoff_balance_penalty_cgst) + parseFloat(setoff_balance_penalty_sgst) + parseFloat(setoff_balance_penalty_cess);

            $("#liability_form_make_payment").trigger("reset");

            $("#form3_setoff_type").val('penalty');
            $("#form3_igst").val(setoff_balance_penalty_igst);
            $("#form3_cgst").val(setoff_balance_penalty_cgst);
            $("#form3_sgst").val(setoff_balance_penalty_sgst);
            $("#form3_cess").val(setoff_balance_penalty_cess);
            $("#form3_total").val(total_balance);
            $("#form3_type").val("liability_penalty_charge");


            $("#form3_modal").modal("show");
        });

        $("#form3_make_payment_others").on("click", function () {

            var setoff_balance_others_igst = $("#liability_btbpic_others_igst").val();
            var setoff_balance_others_cgst = $("#liability_btbpic_others_cgst").val();
            var setoff_balance_others_sgst = $("#liability_btbpic_others_sgst").val();
            var setoff_balance_others_cess = $("#liability_btbpic_others_cess").val();


            if(setoff_balance_others_igst == ''){
                setoff_balance_others_igst = 0;
            }

            if(setoff_balance_others_cgst == ''){
                setoff_balance_others_cgst = 0;
            }

            if(setoff_balance_others_sgst == ''){
                setoff_balance_others_sgst = 0;
            }

            if(setoff_balance_others_cess == ''){
                setoff_balance_others_cess = 0;
            }

            var total_balance = parseFloat(setoff_balance_others_igst) + parseFloat(setoff_balance_others_cgst) + parseFloat(setoff_balance_others_sgst) + parseFloat(setoff_balance_others_cess);

            $("#liability_form_make_payment").trigger("reset");

            $("#form3_setoff_type").val('others');
            $("#form3_igst").val(setoff_balance_others_igst);
            $("#form3_cgst").val(setoff_balance_others_cgst);
            $("#form3_sgst").val(setoff_balance_others_sgst);
            $("#form3_cess").val(setoff_balance_others_cess);
            $("#form3_total").val(total_balance);
            $("#form3_type").val("liability_others_charge");


            $("#form3_modal").modal("show");
        });

        $('.form1_type_of_payment').on("change", function(){

            var type_of_payment = $(this).val();

            // console.log("outside " + type_of_payment);

            if($(this).is(':checked')){
                if (type_of_payment == 'bank') {
                    $("#form1_bank-list").show();
                } else if(type_of_payment == 'pos') {
                    $("#form1_pos-bank-list").show();
                } else if(type_of_payment == 'cash'){
                    $("#form1_cash-list").show();
                }
            } else {
                // console.log("inside " + type_of_payment);
                if (type_of_payment == 'bank') {
                    $("#form1_bank-list").hide();
                } else if(type_of_payment == 'pos') {
                    $("#form1_pos-bank-list").hide();
                } else if(type_of_payment == 'cash'){
                    $("#form1_cash-list").hide();
                }
            }

        });

        $(document).on("keyup", "#form1_cashed_amount", function() {
            var cashed_amount = $(this).val();
            var banked_amount = $("#form1_banked_amount").val();
            var posed_amount = $("#form1_posed_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form1_amount_paid").val(amount_paid);
            $("#form1_amount_paid").trigger("keyup");
        });

        $(document).on("keyup", "#form1_banked_amount", function() {
            var banked_amount = $(this).val();
            var cashed_amount = $("#form1_cashed_amount").val();
            var posed_amount = $("#form1_posed_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form1_amount_paid").val(amount_paid);
            $("#form1_amount_paid").trigger("keyup");
        });

        $(document).on("keyup", "#form1_posed_amount", function() {
            var posed_amount = $(this).val();
            var cashed_amount = $("#form1_cashed_amount").val();
            var banked_amount = $("#form1_banked_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form1_amount_paid").val(amount_paid);
            $("#form1_amount_paid").trigger("keyup");
        });



        $('.form2_type_of_payment').on("change", function(){

            var type_of_payment = $(this).val();

            // console.log("outside " + type_of_payment);

            if($(this).is(':checked')){
                if (type_of_payment == 'bank') {
                    $("#form2_bank-list").show();
                } else if(type_of_payment == 'pos') {
                    $("#form2_pos-bank-list").show();
                } else if(type_of_payment == 'cash'){
                    $("#form2_cash-list").show();
                }
            } else {
                // console.log("inside " + type_of_payment);
                if (type_of_payment == 'bank') {
                    $("#form2_bank-list").hide();
                } else if(type_of_payment == 'pos') {
                    $("#form2_pos-bank-list").hide();
                } else if(type_of_payment == 'cash'){
                    $("#form2_cash-list").hide();
                }
            }

        });

        $(document).on("keyup", "#form2_cashed_amount", function() {
            var cashed_amount = $(this).val();
            var banked_amount = $("#form2_banked_amount").val();
            var posed_amount = $("#form2_posed_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form2_amount_paid").val(amount_paid);
            $("#form2_amount_paid").trigger("keyup");
        });

        $(document).on("keyup", "#form2_banked_amount", function() {
            var banked_amount = $(this).val();
            var cashed_amount = $("#form2_cashed_amount").val();
            var posed_amount = $("#form2_posed_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form2_amount_paid").val(amount_paid);
            $("#form2_amount_paid").trigger("keyup");
        });

        $(document).on("keyup", "#form2_posed_amount", function() {
            var posed_amount = $(this).val();
            var cashed_amount = $("#form2_cashed_amount").val();
            var banked_amount = $("#form2_banked_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form2_amount_paid").val(amount_paid);
            $("#form2_amount_paid").trigger("keyup");

        });

        $('.form3_type_of_payment').on("change", function(){

            var type_of_payment = $(this).val();

            // console.log("outside " + type_of_payment);

            if($(this).is(':checked')){
                if (type_of_payment == 'bank') {
                    $("#form3_bank-list").show();
                } else if(type_of_payment == 'pos') {
                    $("#form3_pos-bank-list").show();
                } else if(type_of_payment == 'cash'){
                    $("#form3_cash-list").show();
                }
            } else {
                // console.log("inside " + type_of_payment);
                if (type_of_payment == 'bank') {
                    $("#form3_bank-list").hide();
                } else if(type_of_payment == 'pos') {
                    $("#form3_pos-bank-list").hide();
                } else if(type_of_payment == 'cash'){
                    $("#form3_cash-list").hide();
                }
            }

        });

        $(document).on("keyup", "#form3_cashed_amount", function() {
            var cashed_amount = $(this).val();
            var banked_amount = $("#form3_banked_amount").val();
            var posed_amount = $("#form3_posed_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form3_amount_paid").val(amount_paid);
            $("#form3_amount_paid").trigger("keyup");
        });

        $(document).on("keyup", "#form3_banked_amount", function() {
            var banked_amount = $(this).val();
            var cashed_amount = $("#form3_cashed_amount").val();
            var posed_amount = $("#form3_posed_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form3_amount_paid").val(amount_paid);
            $("#form3_amount_paid").trigger("keyup");
        });

        $(document).on("keyup", "#form3_posed_amount", function() {
            var posed_amount = $(this).val();
            var cashed_amount = $("#form3_cashed_amount").val();
            var banked_amount = $("#form3_banked_amount").val();

            if( cashed_amount == '' ) {
                cashed_amount = 0;
            }

            if( banked_amount == '' ) {
                banked_amount = 0;
            }

            if( posed_amount == '' ) {
                posed_amount = 0;
            }

            var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

            $("#form3_amount_paid").val(amount_paid);
            $("#form3_amount_paid").trigger("keyup");
        });


        $("#otr_input_igst_igst").on("keyup", function(){
            partial_calculate_row1();
        });

        $("#otr_input_igst_cgst").on("keyup", function(){
            partial_calculate_row1();
        });

        $("#otr_input_igst_sgst").on("keyup", function(){
            partial_calculate_row1();
        });

        $("#otr_input_igst_cess").on("keyup", function(){
            partial_calculate_row1();
        });

        $("#otr_ptgcl_igst").on("keyup", function(){
            // partial2_calculate_row1();
            partial_calculate_row1();
        });

        //----

        $("#otr_input_cgst_igst").on("keyup", function(){
            partial_calculate_row2();
        });

        $("#otr_input_cgst_cgst").on("keyup", function(){
            partial_calculate_row2();
        });

        $("#otr_input_cgst_sgst").on("keyup", function(){
            partial_calculate_row2();
        });

        $("#otr_input_cgst_cess").on("keyup", function(){
            partial_calculate_row2();
        });

        $("#otr_ptgcl_cgst").on("keyup", function(){
            // partial2_calculate_row2();
            partial_calculate_row2();
        });

        //----

        $("#otr_input_sgst_igst").on("keyup", function(){
            partial_calculate_row3();
        });

        $("#otr_input_sgst_cgst").on("keyup", function(){
            partial_calculate_row3();
        });

        $("#otr_input_sgst_sgst").on("keyup", function(){
            partial_calculate_row3();
        });

        $("#otr_input_sgst_cess").on("keyup", function(){
            partial_calculate_row3();
        });

        $("#otr_ptgcl_sgst").on("keyup", function(){
            // partial2_calculate_row3();
            partial_calculate_row3();
        });

        //----

        $("#otr_input_cess_igst").on("keyup", function(){
            partial_calculate_row4();
        });

        $("#otr_input_cess_cgst").on("keyup", function(){
            partial_calculate_row4();
        });

        $("#otr_input_cess_sgst").on("keyup", function(){
            partial_calculate_row4();
        });

        $("#otr_input_cess_cess").on("keyup", function(){
            partial_calculate_row4();
        });

        $("#otr_ptgcl_cess").on("keyup", function(){
            // partial2_calculate_row4();
            partial_calculate_row4();
        });

        function partial_calculate_row1(){
            var ot_reverse_charge_igst = $("#ot_reverse_charge_igst").val();
            var otr_input_igst_igst = $("#otr_input_igst_igst").val();    
            var otr_input_igst_cgst = $("#otr_input_igst_cgst").val()
            var otr_input_igst_sgst = $("#otr_input_igst_sgst").val();
            var otr_input_igst_cess = $("#otr_input_igst_cess").val();
            var otr_ptgcl_igst = $("#otr_ptgcl_igst").val();

            if(ot_reverse_charge_igst == ''){
                ot_reverse_charge_igst = 0;
            }

            if(otr_input_igst_igst == ''){
                otr_input_igst_igst = 0;
            }

            if(otr_input_igst_cgst == ''){
               otr_input_igst_cgst = 0; 
            }

            if(otr_input_igst_sgst == ''){
                otr_input_igst_sgst = 0;
            }

            if(otr_input_igst_cess == ''){
                otr_input_igst_cess = 0;
            }

            if(otr_ptgcl_igst == ''){
                otr_ptgcl_igst = 0;
            }

            var added_value_row_1 = parseFloat(otr_input_igst_igst) + parseFloat(otr_input_igst_cgst) + parseFloat(otr_input_igst_sgst) + parseFloat(otr_input_igst_cess);

            var partial_total_row1 = ot_reverse_charge_igst - (parseFloat(added_value_row_1) + parseFloat(otr_ptgcl_igst));

            partial_total_row1 = partial_total_row1.toFixedDown(3);

            $("#otr_btbpic_igst").val(partial_total_row1);

            if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
                $("#make_payment_button_block").hide();
                $("#setoff_button_block").show();
            } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").show();
            } else {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").hide();
            }
        }

        // function partial2_calculate_row1(){
        //     var otr_ptgcl_igst = $("#otr_ptgcl_igst").val();
        //     var ot_reverse_charge_igst = $("#ot_reverse_charge_igst").val();
        //     var otr_input_igst_igst = $("#otr_input_igst_igst").val();    
        //     var otr_input_igst_cgst = $("#otr_input_igst_cgst").val()
        //     var otr_input_igst_sgst = $("#otr_input_igst_sgst").val();
        //     var otr_input_igst_cess = $("#otr_input_igst_cess").val(); 

        //     if(ot_reverse_charge_igst == ''){
        //         ot_reverse_charge_igst = 0;
        //     }

        //     if(otr_input_igst_igst == ''){
        //         otr_input_igst_igst = 0;
        //     }

        //     if(otr_input_igst_cgst == ''){
        //        otr_input_igst_cgst = 0; 
        //     }

        //     if(otr_input_igst_sgst == ''){
        //         otr_input_igst_sgst = 0;
        //     }

        //     if(otr_input_igst_cess == ''){
        //         otr_input_igst_cess = 0;
        //     }


        //     if(otr_ptgcl_igst == ''){
        //         otr_ptgcl_igst = 0;
        //     }
            
        //     var added_value_row_1 = parseFloat(otr_input_igst_igst) + parseFloat(otr_input_igst_cgst) + parseFloat(otr_input_igst_sgst) + parseFloat(otr_input_igst_cess);

        //     var partial_total_row1 = added_value_row_1 + ot_reverse_charge_igst;

        //     var total_partial2_row1 = parseFloat(partial_total_row1) - parseFloat(otr_ptgcl_igst);

        //     total_partial2_row1 = total_partial2_row1.toFixedDown(3);
            
        //     $("#otr_btbpic_igst").val(total_partial2_row1);

        //     if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
        //         $("#make_payment_button_block").hide();
        //         $("#setoff_button_block").show();
        //     } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").show();
        //     } else {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").hide();
        //     }
        // }


        function partial_calculate_row2(){
            var otreverse_charge_cgst = $("#otreverse_charge_cgst").val();
            var otr_input_cgst_igst = $("#otr_input_cgst_igst").val();    
            var otr_input_cgst_cgst = $("#otr_input_cgst_cgst").val()
            var otr_input_cgst_sgst = $("#otr_input_cgst_sgst").val();
            var otr_input_cgst_cess = $("#otr_input_cgst_cess").val();
            var otr_ptgcl_cgst = $("#otr_ptgcl_cgst").val();

            if(otreverse_charge_cgst == ''){
                otreverse_charge_cgst = 0;
            }

            if(otr_input_cgst_igst == ''){
                otr_input_cgst_igst = 0;
            }

            if(otr_input_cgst_cgst == ''){
               otr_input_cgst_cgst = 0; 
            }

            if(otr_input_cgst_sgst == ''){
                otr_input_cgst_sgst = 0;
            }

            if(otr_input_cgst_cess == ''){
                otr_input_cgst_cess = 0;
            }

            if(otr_ptgcl_cgst == ''){
                otr_ptgcl_cgst = 0;
            }

            var added_value_row_2 = parseFloat(otr_input_cgst_igst) + parseFloat(otr_input_cgst_cgst) + parseFloat(otr_input_cgst_sgst) + parseFloat(otr_input_cgst_cess);

            var partial_total_row2 = otreverse_charge_cgst - (parseFloat(added_value_row_2) + parseFloat(otr_ptgcl_cgst));

            partial_total_row2 = partial_total_row2.toFixedDown(3);

            $("#otr_btbpic_cgst").val(partial_total_row2);

            if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
                $("#make_payment_button_block").hide();
                $("#setoff_button_block").show();
            } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").show();
            } else {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").hide();
            }
        }

        // function partial2_calculate_row2(){
        //     var otr_ptgcl_cgst = $("#otr_ptgcl_cgst").val();
        //     var otreverse_charge_cgst = $("#otreverse_charge_cgst").val();
        //     var otr_input_cgst_igst = $("#otr_input_cgst_igst").val();    
        //     var otr_input_cgst_cgst = $("#otr_input_cgst_cgst").val()
        //     var otr_input_cgst_sgst = $("#otr_input_cgst_sgst").val();
        //     var otr_input_cgst_cess = $("#otr_input_cgst_cess").val();
        //     var otr_ptgcl_cgst = $("#otr_ptgcl_cgst").val();

        //     if(otreverse_charge_cgst == ''){
        //         otreverse_charge_cgst = 0;
        //     }

        //     if(otr_input_cgst_igst == ''){
        //         otr_input_cgst_igst = 0;
        //     }

        //     if(otr_input_cgst_cgst == ''){
        //        otr_input_cgst_cgst = 0; 
        //     }

        //     if(otr_input_cgst_sgst == ''){
        //         otr_input_cgst_sgst = 0;
        //     }

        //     if(otr_input_cgst_cess == ''){
        //         otr_input_cgst_cess = 0;
        //     }


        //     if(otr_ptgcl_cgst == ''){
        //         otr_ptgcl_cgst = 0;
        //     }
            
        //     var added_value_row_2 = parseFloat(otr_input_cgst_igst) + parseFloat(otr_input_cgst_cgst) + parseFloat(otr_input_cgst_sgst) + parseFloat(otr_input_cgst_cess);

        //     var partial_total_row2 = added_value_row_2 - otreverse_charge_cgst;

        //     var total_partial2_row2 = parseFloat(partial_total_row2) - parseFloat(otr_ptgcl_cgst);

        //     total_partial2_row2 = total_partial2_row2.toFixedDown(3);
            
        //     $("#otr_btbpic_cgst").val(total_partial2_row2);

        //     if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
        //         $("#make_payment_button_block").hide();
        //         $("#setoff_button_block").show();
        //     } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").show();
        //     } else {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").hide();
        //     }
        // }

        function partial_calculate_row3(){
            var otreverse_charge_sgst = $("#otreverse_charge_sgst").val();
            var otr_input_sgst_igst = $("#otr_input_sgst_igst").val();    
            var otr_input_sgst_cgst = $("#otr_input_sgst_cgst").val()
            var otr_input_sgst_sgst = $("#otr_input_sgst_sgst").val();
            var otr_input_sgst_cess = $("#otr_input_sgst_cess").val();
            var otr_ptgcl_sgst = $("#otr_ptgcl_sgst").val();

            if(otreverse_charge_sgst == ''){
                otreverse_charge_sgst = 0;
            }

            if(otr_input_sgst_igst == ''){
                otr_input_sgst_igst = 0;
            }

            if(otr_input_sgst_cgst == ''){
               otr_input_sgst_cgst = 0; 
            }

            if(otr_input_sgst_sgst == ''){
                otr_input_sgst_sgst = 0;
            }

            if(otr_input_sgst_cess == ''){
                otr_input_sgst_cess = 0;
            }

            if(otr_ptgcl_sgst == ''){
                otr_ptgcl_sgst = 0;
            }

            var added_value_row_3 = parseFloat(otr_input_sgst_igst) + parseFloat(otr_input_sgst_cgst) + parseFloat(otr_input_sgst_sgst) + parseFloat(otr_input_sgst_cess);

            var partial_total_row3 = otreverse_charge_sgst - (parseFloat(added_value_row_3) + parseFloat(otr_ptgcl_sgst));

            partial_total_row3 = partial_total_row3.toFixedDown(3);

            $("#otr_btbpic_sgst").val(partial_total_row3);

            if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
                $("#make_payment_button_block").hide();
                $("#setoff_button_block").show();
            } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").show();
            } else {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").hide();
            }
        }

        // function partial2_calculate_row3(){
        //     var otr_ptgcl_sgst = $("#otr_ptgcl_sgst").val();
        //     var otreverse_charge_sgst = $("#otreverse_charge_sgst").val();
        //     var otr_input_sgst_igst = $("#otr_input_sgst_igst").val();    
        //     var otr_input_sgst_cgst = $("#otr_input_sgst_cgst").val()
        //     var otr_input_sgst_sgst = $("#otr_input_sgst_sgst").val();
        //     var otr_input_sgst_cess = $("#otr_input_sgst_cess").val();
            

        //     if(otreverse_charge_sgst == ''){
        //         otreverse_charge_sgst = 0;
        //     }

        //     if(otr_input_sgst_igst == ''){
        //         otr_input_sgst_igst = 0;
        //     }

        //     if(otr_input_sgst_cgst == ''){
        //        otr_input_sgst_cgst = 0; 
        //     }

        //     if(otr_input_sgst_sgst == ''){
        //         otr_input_sgst_sgst = 0;
        //     }

        //     if(otr_input_sgst_cess == ''){
        //         otr_input_sgst_cess = 0;
        //     }
            
        //     if(otr_ptgcl_sgst == ''){
        //         otr_ptgcl_sgst = 0;
        //     }

        //     var added_value_row_3 = parseFloat(otr_input_sgst_igst) + parseFloat(otr_input_sgst_cgst) + parseFloat(otr_input_sgst_sgst) + parseFloat(otr_input_sgst_cess);

        //     var partial_total_row3 = added_value_row_3 - otreverse_charge_sgst;


        //     var total_partial2_row3 = parseFloat(partial_total_row3) - parseFloat(otr_ptgcl_sgst);

        //     total_partial2_row3 = total_partial2_row3.toFixedDown(3);
            
        //     $("#otr_btbpic_sgst").val(total_partial2_row3);

        //     if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
        //         $("#make_payment_button_block").hide();
        //         $("#setoff_button_block").show();
        //     } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").show();
        //     } else {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").hide();
        //     }
        // }

        function partial_calculate_row4(){
            var otreverse_charge_cess = $("#otreverse_charge_cess").val();
            var otr_input_cess_igst = $("#otr_input_cess_igst").val();    
            var otr_input_cess_cgst = $("#otr_input_cess_cgst").val()
            var otr_input_cess_sgst = $("#otr_input_cess_sgst").val();
            var otr_input_cess_cess = $("#otr_input_cess_cess").val();
            var otr_ptgcl_cess = $("#otr_ptgcl_cess").val();

            if(otreverse_charge_cess == ''){
                otreverse_charge_cess = 0;
            }

            if(otr_input_cess_igst == ''){
                otr_input_cess_igst = 0;
            }

            if(otr_input_cess_cgst == ''){
               otr_input_cess_cgst = 0; 
            }

            if(otr_input_cess_sgst == ''){
                otr_input_cess_sgst = 0;
            }

            if(otr_input_cess_cess == ''){
                otr_input_cess_cess = 0;
            }

            if(otr_ptgcl_cess == ''){
                otr_ptgcl_cess = 0;
            }

            var added_value_row_4 = parseFloat(otr_input_cess_igst) + parseFloat(otr_input_cess_cgst) + parseFloat(otr_input_cess_sgst) + parseFloat(otr_input_cess_cess);

            var partial_total_row4 = otreverse_charge_cess - (parseFloat(added_value_row_4) + parseFloat(otr_ptgcl_cess));

            partial_total_row4 = partial_total_row4.toFixedDown(3);

            $("#otr_btbpic_cess").val(partial_total_row4);

            if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
                $("#make_payment_button_block").hide();
                $("#setoff_button_block").show();
            } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").show();
            } else {
                $("#setoff_button_block").hide();
                $("#make_payment_button_block").hide();
            }
        }

        // function partial2_calculate_row4(){
        //     var otr_ptgcl_cess = $("#otr_ptgcl_cess").val();
        //     var otreverse_charge_cess = $("#otreverse_charge_cess").val();
        //     var otr_input_cess_igst = $("#otr_input_cess_igst").val();    
        //     var otr_input_cess_cgst = $("#otr_input_cess_cgst").val()
        //     var otr_input_cess_sgst = $("#otr_input_cess_sgst").val();
        //     var otr_input_cess_cess = $("#otr_input_cess_cess").val();
            

        //     if(otreverse_charge_cess == ''){
        //         otreverse_charge_cess = 0;
        //     }

        //     if(otr_input_cess_igst == ''){
        //         otr_input_cess_igst = 0;
        //     }

        //     if(otr_input_cess_cgst == ''){
        //        otr_input_cess_cgst = 0; 
        //     }

        //     if(otr_input_cess_sgst == ''){
        //         otr_input_cess_sgst = 0;
        //     }

        //     if(otr_input_cess_cess == ''){
        //         otr_input_cess_cess = 0;
        //     }

        //     if(otr_ptgcl_cess == ''){
        //         otr_ptgcl_cess = 0;
        //     }

        //     var added_value_row_4 = parseFloat(otr_input_cess_igst) + parseFloat(otr_input_cess_cgst) + parseFloat(otr_input_cess_sgst) + parseFloat(otr_input_cess_cess);

        //     var partial_total_row4 = added_value_row_4 - otreverse_charge_cess;


        //     var total_partial2_row4 = parseFloat(partial_total_row4) - parseFloat(otr_ptgcl_cess);

        //     total_partial2_row4 = total_partial2_row4.toFixedDown(3);
            
        //     $("#otr_btbpic_cess").val(total_partial2_row4);

        //     if( $("#otr_btbpic_igst").val() == 0 && $("#otr_btbpic_cgst").val() == 0 && $("#otr_btbpic_sgst").val() == 0 && $("#otr_btbpic_cess").val() == 0 ){
        //         $("#make_payment_button_block").hide();
        //         $("#setoff_button_block").show();
        //     } else if( $("#otr_btbpic_igst").val() > 0 || $("#otr_btbpic_cgst").val() > 0 || $("#otr_btbpic_sgst").val() > 0 || $("#otr_btbpic_cess").val() > 0 ) {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").show();
        //     } else {
        //         $("#setoff_button_block").hide();
        //         $("#make_payment_button_block").hide();
        //     }
        // }

        $("#other_than_reverse_charge_form").on("submit", function(e){
            e.preventDefault();

            var ot_reverse_charge_igst = $("#ot_reverse_charge_igst").val();
            var otr_input_igst_igst = $("#otr_input_igst_igst").val();
            var otr_input_igst_cgst = $("#otr_input_igst_cgst").val();
            var otr_input_igst_sgst = $("#otr_input_igst_sgst").val();
            var otr_input_igst_cess = $("#otr_input_igst_cess").val();
            var otr_ptgcl_igst = $("#otr_ptgcl_igst").val();
            var otr_btbpic_igst = $("#otr_btbpic_igst").val();

            var otreverse_charge_cgst = $("#otreverse_charge_cgst").val();
            var otr_input_cgst_igst = $("#otr_input_cgst_igst").val();
            var otr_input_cgst_cgst = $("#otr_input_cgst_cgst").val();
            var otr_input_cgst_sgst = $("#otr_input_cgst_sgst").val();
            var otr_input_cgst_cess = $("#otr_input_cgst_cess").val();
            var otr_ptgcl_cgst = $("#otr_ptgcl_cgst").val();
            var otr_btbpic_cgst = $("#otr_btbpic_cgst").val();

            var otreverse_charge_sgst = $("#otreverse_charge_sgst").val();
            var otr_input_sgst_igst = $("#otr_input_sgst_igst").val();
            var otr_input_sgst_cgst = $("#otr_input_sgst_cgst").val();
            var otr_input_sgst_sgst = $("#otr_input_sgst_sgst").val();
            var otr_input_sgst_cess = $("#otr_input_sgst_cess").val();
            var otr_ptgcl_sgst = $("#otr_ptgcl_sgst").val();
            var otr_btbpic_sgst = $("#otr_btbpic_sgst").val();

            var otreverse_charge_cess = $("#otreverse_charge_cess").val();
            var otr_input_cess_igst = $("#otr_input_cess_igst").val();
            var otr_input_cess_cgst = $("#otr_input_cess_cgst").val();
            var otr_input_cess_sgst = $("#otr_input_cess_sgst").val();
            var otr_input_cess_cess = $("#otr_input_cess_cess").val();
            var otr_ptgcl_cess = $("#otr_ptgcl_cess").val();
            var otr_btbpic_cess = $("#otr_btbpic_cess").val();

            var otr_date = $("#otr_date").val();
            var shouldContinue = true;


            if(otr_date == ''){
                show_custom_alert(`<span style=\"color: red\">Date is required field.</span>`);
                shouldContinue = false;
            }

            if(ot_reverse_charge_igst == ''){
                ot_reverse_charge_igst = 0;
            }

            if(otr_input_igst_igst == ''){
                otr_input_igst_igst = 0;
            }

            if(otr_input_igst_cgst == ''){
                otr_input_igst_cgst = 0;
            }

            if(otr_input_igst_sgst == ''){
                otr_input_igst_sgst = 0;
            }

            if(otr_input_igst_cess == ''){
                otr_input_igst_cess = 0;
            }

            if(otr_ptgcl_igst == ''){
                otr_ptgcl_igst = 0;
            }

            if(otr_btbpic_igst == ''){
                otr_btbpic_igst = 0;
            }

//-----------------------------------------------------
            if(otreverse_charge_cgst == ''){
                otreverse_charge_cgst = 0;
            }

            if(otr_input_cgst_igst == ''){
                otr_input_cgst_igst = 0;
            }

            if(otr_input_cgst_cgst == ''){
                otr_input_cgst_cgst = 0;
            }

            if(otr_input_cgst_sgst == ''){
                otr_input_cgst_sgst = 0;
            }

            if(otr_input_cgst_cess == ''){
                otr_input_cgst_cess = 0;
            }

            if(otr_ptgcl_cgst == ''){
                otr_ptgcl_cgst = 0;
            }

            if(otr_btbpic_cgst == ''){
                otr_btbpic_cgst = 0;
            }
//--------------------------------------------


            if(otreverse_charge_sgst == ''){
                otreverse_charge_sgst = 0;
            }

            if(otr_input_sgst_igst == ''){
                otr_input_sgst_igst = 0;
            }

            if(otr_input_sgst_cgst == ''){
                otr_input_sgst_cgst = 0;
            }

            if(otr_input_sgst_sgst == ''){
                otr_input_sgst_sgst = 0;
            }

            if(otr_input_sgst_cess == ''){
                otr_input_sgst_cess = 0;
            }

            if(otr_ptgcl_sgst == ''){
                otr_ptgcl_sgst = 0;
            }

            if(otr_btbpic_sgst == ''){
                otr_btbpic_sgst = 0;
            }

//-------------------------------

            if(otreverse_charge_cess == ''){
                otreverse_charge_cess = 0;
            }

            if(otr_input_cess_igst == ''){
                otr_input_cess_igst = 0;
            }

            if(otr_input_cess_cgst == ''){
                otr_input_cess_cgst = 0;
            }

            if(otr_input_cess_sgst == ''){
                otr_input_cess_sgst = 0;
            }

            if(otr_input_cess_cess == ''){
                otr_input_cess_cess = 0;
            }

            if(otr_ptgcl_cess == ''){
                otr_ptgcl_cess = 0;
            }

            if(otr_btbpic_cess == ''){
                otr_btbpic_cess = 0;
            }

            if(shouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.other.than.reverse.charge") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "ot_reverse_charge_igst": ot_reverse_charge_igst,
                        "otr_input_igst_igst": otr_input_igst_igst,
                        "otr_input_igst_cgst": otr_input_igst_cgst,
                        "otr_input_igst_sgst": otr_input_igst_sgst,
                        "otr_input_igst_cess": otr_input_igst_cess,
                        "otr_ptgcl_igst": otr_ptgcl_igst,
                        "otr_btbpic_igst": otr_btbpic_igst,

                        "ot_reverse_charge_cgst": otreverse_charge_cgst,
                        "otr_input_cgst_igst": otr_input_cgst_igst,
                        "otr_input_cgst_cgst": otr_input_cgst_cgst,
                        "otr_input_cgst_sgst": otr_input_cgst_sgst,
                        "otr_input_cgst_cess": otr_input_cgst_cess,
                        "otr_ptgcl_cgst": otr_ptgcl_cgst,
                        "otr_btbpic_cgst": otr_btbpic_cgst,

                        "ot_reverse_charge_sgst": otreverse_charge_sgst,
                        "otr_input_sgst_igst": otr_input_sgst_igst,
                        "otr_input_sgst_cgst": otr_input_sgst_cgst,
                        "otr_input_sgst_sgst": otr_input_sgst_sgst,
                        "otr_input_sgst_cess": otr_input_sgst_cess,
                        "otr_ptgcl_sgst": otr_ptgcl_sgst,
                        "otr_btbpic_sgst": otr_ptgcl_sgst,

                        "ot_reverse_charge_cess": otreverse_charge_cess,
                        "otr_input_cess_igst": otr_input_cess_igst,
                        "otr_input_cess_cgst": otr_input_cess_cgst,
                        "otr_input_cess_sgst": otr_input_cess_sgst,
                        "otr_input_cess_cess": otr_input_cess_cess,
                        "otr_ptgcl_cess": otr_ptgcl_cess,
                        "otr_btbpic_cess": otr_btbpic_cess,

                        "otr_date": otr_date
                    },
                    success: function(response){
                    
                        // console.log(response);

                        if(response == 'success'){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i>Data inserted successfully</span>`);
                            setTimeout(function(){location.reload(true);}, 5000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i>Some error occured while inserting data.</span>`);
                        }

                    }
                });
            }

        });


        $("#other_than_reverse_charge_form_make_payment").on("submit", function(e){
            e.preventDefault();
            
            var form1ShouldContinue = true;
            var form1_type = $("#form1_type").val();
            var form1_cgst = $("#form1_cgst").val();
            var form1_sgst = $("#form1_sgst").val();
            var form1_igst = $("#form1_igst").val();
            var form1_cess = $("#form1_cess").val();

            var form1_total = $("#form1_total").val();
            var form1_voucherNo = $("#form1_voucherNo").val();
            var form1_date = $("#form1_date").val();
            var form1_cin = $("#form1_cin").val();
            var form1_amount_paid = $("#form1_amount_paid").val();
            var form1_type_of_payment = Array();
            var form1_message = Array();

            var form1_cashed_amount = '';
            
            var form1_banked_amount = '';
            var form1_bank_cheque = '';
            var form1_bank = '';
            
            var form1_posed_amount = '';
            var form1_pos = '';

            if(form1_type == ''){
                form1ShouldContinue = false;
                form1_message.push("Type is required");
            }

            if(form1_cgst == ''){
                form1ShouldContinue = false;
                form1_message.push("CGST is required");
            }

            if(form1_sgst == ''){
                form1ShouldContinue = false;
                form1_message.push("SGST is required");
            }

            if(form1_igst == ''){
                form1ShouldContinue = false;
                form1_message.push("IGST is required");
            }

            if(form1_cess == ''){
                form1ShouldContinue = false;
                form1_message.push("CESS is required");
            }

            if(form1_total == ''){
                form1ShouldContinue = false;
                form1_message.push("Total is required");
            }

            if(form1_voucherNo == ''){
                form1ShouldContinue = false;
                form1_message.push("Voucher No. is required");
            }

            if(form1_date == ''){
                form1ShouldContinue = false;
                form1_message.push("Date is required");
            }

            if(form1_cin == ''){
                form1ShouldContinue = false;
                form1_message.push("CIN is required");
            }

            if($("#form1_cash_checkbox").is(":checked")){
                form1_type_of_payment.push("cash");
                form1_cashed_amount = $("#form1_cashed_amount").val();

                if(form1_cashed_amount == ''){
                    form1ShouldContinue = false;
                    form1_message.push("Cash Amount is required");
                }
            }

            if($("#form1_bank_checkbox").is(":checked")){
                form1_type_of_payment.push("bank");

                form1_banked_amount = $("#form1_banked_amount").val();
                form1_bank_cheque = $("#form1_bank_cheque").val();
                form1_bank = $("#form1_bank").val();

                if(form1_banked_amount == ''){
                    form1ShouldContinue = false;
                    form1_message.push("Bank Amount is required");
                }

                if(form1_bank_cheque == ''){
                    form1ShouldContinue = false;
                    form1_message.push("Bank Cheque is required");
                }

                if(form1_bank == '' || form1_bank == null){
                    form1ShouldContinue = false;
                    form1_message.push("Bank is required");
                }
            }

            if($("#form1_pos_checkbox").is(":checked")){
                form1_type_of_payment.push("pos");

                form1_posed_amount = $("#form1_posed_amount").val();
                form1_pos = $("#form1_pos").val();

                if(form1_posed_amount == ''){
                    form1ShouldContinue = false;
                    form1_message.push("POS Amount is required");
                }

                if(form1_pos == '' || form1_pos == null){
                    form1ShouldContinue = false;
                    form1_message.push("POS Bank is required");
                }
            }

            if(form1_amount_paid == ''){
                form1ShouldContinue = false;
                form1_message.push("Amount Received is required");
            }

            if(form1_total !== form1_amount_paid){
                form1ShouldContinue = false;
                form1_message.push("Amount Received should be equal to Total");
            }

            if(form1ShouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.gst.setoff") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "cgst": form1_cgst,
                        "sgst": form1_sgst,
                        "igst": form1_igst,
                        "cess": form1_cess,
                        "total": form1_total,
                        "voucher_no": form1_voucherNo,
                        "date": form1_date,
                        "cin": form1_cin,
                        "type_of_payment": form1_type_of_payment,
                        "cashed_amount": form1_cashed_amount,
                        "banked_amount": form1_banked_amount,
                        "posed_amount": form1_posed_amount,
                        "bank": form1_bank,
                        "bank_cheque": form1_bank_cheque,
                        "pos_bank": form1_pos,
                        "amount_received": form1_amount_paid,
                        "type": form1_type,
                    },
                    success: function(response){

                        // console.log(response);

                        if(response.success){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> ${response.message}</span>`);
                        }
                        else{
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> ${response.message}</span>`);
                        }
                        
                        $("#form1_modal").modal('hide');

                        //------------------------------------------------------------------

                        $("#otr_btbpic_igst").val(0);
                        $("#otr_btbpic_cgst").val(0);
                        $("#otr_btbpic_sgst").val(0);
                        $("#otr_btbpic_cess").val(0);

                        $("#make_payment_button_block").hide();
                        $("#setoff_button_block").show();
                    }
                });
            } else {
                show_custom_alert(form1_message);
            }
        });

        $("#reverse_charge_form").on("submit", function(e){
            e.preventDefault();

            // console.log("clicked");

            var reverse_charge_igst = $("#reverse_charge_igst").val();
            var reverse_charge_ptgcl_igst = $("#reverse_charge_ptgcl_igst").val();
            var reverse_charge_btbpic_igst = $("#reverse_charge_btbpic_igst").val();

            var reverse_charge_cgst = $("#reverse_charge_cgst").val();
            var reverse_charge_ptgcl_cgst = $("#reverse_charge_ptgcl_cgst").val();
            var reverse_charge_btbpic_cgst = $("#reverse_charge_btbpic_cgst").val()

            var reverse_charge_sgst = $("#reverse_charge_sgst").val();
            var reverse_charge_ptgcl_sgst = $("#reverse_charge_ptgcl_sgst").val();
            var reverse_charge_btbpic_sgst = $("#reverse_charge_btbpic_sgst").val();

            var reverse_charge_cess = $("#reverse_charge_cess").val();
            var reverse_charge_ptgcl_cess = $("#reverse_charge_ptgcl_cess").val();
            var reverse_charge_btbpic_cess = $("#reverse_charge_btbpic_cess").val();

            var r_date = $("#r_date").val();
            var shouldContinue = true;

            if(r_date == ''){
                show_custom_alert(`<span style=\"color: red\">Date is required field.</span>`);
                shouldContinue = false;
            }

            if(reverse_charge_igst == ''){
                reverse_charge_igst = 0;
            }

            if(reverse_charge_ptgcl_igst == ''){
                reverse_charge_ptgcl_igst = 0;
            }

            if(reverse_charge_btbpic_igst == ''){
                reverse_charge_btbpic_igst = 0;
            }

            //-----------------------------------------------------

            if(reverse_charge_cgst == ''){
                reverse_charge_cgst = 0;
            }

            if(reverse_charge_ptgcl_cgst == ''){
                reverse_charge_ptgcl_cgst = 0;
            }

            if(reverse_charge_btbpic_cgst == ''){
                reverse_charge_btbpic_cgst = 0;
            }

            //-----------------------------------------------------

            if(reverse_charge_sgst == ''){
                reverse_charge_sgst = 0;
            }

            if(reverse_charge_ptgcl_sgst == ''){
                reverse_charge_ptgcl_sgst = 0;
            }

            if(reverse_charge_btbpic_sgst == ''){
                reverse_charge_btbpic_sgst = 0;
            }

            //-----------------------------------------------------

            if(reverse_charge_cess == ''){
                reverse_charge_cess = 0;
            }

            if(reverse_charge_ptgcl_cess == ''){
                reverse_charge_ptgcl_cess = 0;
            }

            if(reverse_charge_btbpic_cess == ''){
                reverse_charge_btbpic_cess = 0;
            }

            //-----------------------------------------------------

            if(shouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.reverse.charge") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "reverse_charge_igst": reverse_charge_igst,
                        "reverse_charge_ptgcl_igst": reverse_charge_ptgcl_igst,
                        "reverse_charge_btbpic_igst": reverse_charge_btbpic_igst,

                        "reverse_charge_cgst": reverse_charge_cgst,
                        "reverse_charge_ptgcl_cgst": reverse_charge_ptgcl_cgst,
                        "reverse_charge_btbpic_cgst": reverse_charge_btbpic_cgst,

                        "reverse_charge_sgst": reverse_charge_sgst,
                        "reverse_charge_ptgcl_sgst": reverse_charge_ptgcl_sgst,
                        "reverse_charge_btbpic_sgst": reverse_charge_btbpic_sgst,

                        "reverse_charge_cess": reverse_charge_cess,
                        "reverse_charge_ptgcl_cess": reverse_charge_ptgcl_cess,
                        "reverse_charge_btbpic_cess": reverse_charge_btbpic_cess,

                        "r_date": r_date,
                    },
                    success: function(response){
                    
                        // console.log(response);

                        if(response == 'success'){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i>Data inserted successfully</span>`);

                            setTimeout(function(){location.reload();}, 5000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i>Some error occured while inserting data.</span>`);
                        }

                    }
                });
            }

        });

        $("#reverse_charge_form_make_payment").on("submit", function(e){
            e.preventDefault();
            
            var form2ShouldContinue = true;
            var form2_type = $("#form2_type").val();
            var form2_cgst = $("#form2_cgst").val();
            var form2_sgst = $("#form2_sgst").val();
            var form2_igst = $("#form2_igst").val();
            var form2_cess = $("#form2_cess").val();

            var form2_total = $("#form2_total").val();
            var form2_voucherNo = $("#form2_voucherNo").val();
            var form2_date = $("#form2_date").val();
            var form2_cin = $("#form2_cin").val();
            var form2_amount_paid = $("#form2_amount_paid").val();
            var form2_type_of_payment = Array();
            var form2_message = Array();

            var form2_cashed_amount = '';
            
            var form2_banked_amount = '';
            var form2_bank_cheque = '';
            var form2_bank = '';
            
            var form2_posed_amount = '';
            var form2_pos = '';

            if(form2_type == ''){
                form2ShouldContinue = false;
                form2_message.push("Type is required");
            }

            if(form2_cgst == ''){
                form2ShouldContinue = false;
                form2_message.push("CGST is required");
            }

            if(form2_sgst == ''){
                form2ShouldContinue = false;
                form2_message.push("SGST is required");
            }

            if(form2_igst == ''){
                form2ShouldContinue = false;
                form2_message.push("IGST is required");
            }

            if(form2_cess == ''){
                form2ShouldContinue = false;
                form2_message.push("CESS is required");
            }

            if(form2_total == ''){
                form2ShouldContinue = false;
                form2_message.push("Total is required");
            }

            if(form2_voucherNo == ''){
                form2ShouldContinue = false;
                form2_message.push("Voucher No. is required");
            }

            if(form2_date == ''){
                form2ShouldContinue = false;
                form2_message.push("Date is required");
            }

            if(form2_cin == ''){
                form2ShouldContinue = false;
                form2_message.push("CIN is required");
            }

            if($("#form2_cash_checkbox").is(":checked")){
                form2_type_of_payment.push("cash");
                form2_cashed_amount = $("#form2_cashed_amount").val();

                if(form2_cashed_amount == ''){
                    form2ShouldContinue = false;
                    form2_message.push("Cash Amount is required");
                }
            }

            if($("#form2_bank_checkbox").is(":checked")){
                form2_type_of_payment.push("bank");

                form2_banked_amount = $("#form2_banked_amount").val();
                form2_bank_cheque = $("#form2_bank_cheque").val();
                form2_bank = $("#form2_bank").val();

                if(form2_banked_amount == ''){
                    form2ShouldContinue = false;
                    form2_message.push("Bank Amount is required");
                }

                if(form2_bank_cheque == ''){
                    form2ShouldContinue = false;
                    form2_message.push("Bank Cheque is required");
                }

                if(form2_bank == '' || form2_bank == null){
                    form2ShouldContinue = false;
                    form2_message.push("Bank is required");
                }
            }

            if($("#form2_pos_checkbox").is(":checked")){
                form2_type_of_payment.push("pos");

                form2_posed_amount = $("#form2_posed_amount").val();
                form2_pos = $("#form2_pos").val();

                if(form2_posed_amount == ''){
                    form2ShouldContinue = false;
                    form2_message.push("POS Amount is required");
                }

                if(form2_pos == '' || form2_pos == null){
                    form2ShouldContinue = false;
                    form2_message.push("POS Bank is required");
                }
            }

            if(form2_amount_paid == ''){
                form2ShouldContinue = false;
                form2_message.push("Amount Received is required");
            }

            if(form2_total !== form2_amount_paid){
                form2ShouldContinue = false;
                form2_message.push("Amount Received should be equal to Total");
            }

            if(form2ShouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.gst.setoff") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "cgst": form2_cgst,
                        "sgst": form2_sgst,
                        "igst": form2_igst,
                        "cess": form2_cess,
                        "total": form2_total,
                        "voucher_no": form2_voucherNo,
                        "date": form2_date,
                        "cin": form2_cin,
                        "type_of_payment": form2_type_of_payment,
                        "cashed_amount": form2_cashed_amount,
                        "banked_amount": form2_banked_amount,
                        "posed_amount": form2_posed_amount,
                        "bank": form2_bank,
                        "bank_cheque": form2_bank_cheque,
                        "pos_bank": form2_pos,
                        "amount_received": form2_amount_paid,
                        "type": form2_type,
                    },
                    success: function(response){

                        console.log(response);

                        if(response.success){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> ${response.message}</span>`);
                        }
                        else{
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> ${response.message}</span>`);
                        }
                        
                        $("#form2_modal").modal('hide');

                        //------------------------------------------------------------------

                        $("#reverse_charge_btbpic_igst").val(0);
                        $("#reverse_charge_btbpic_cgst").val(0);
                        $("#reverse_charge_btbpic_sgst").val(0);
                        $("#reverse_charge_btbpic_cess").val(0);

                        $("#reverse_charge_make_payment_button_block").hide();
                        $("#reverse_charge_setoff_button_block").show();
                    }
                });
            } else {
                show_custom_alert(form2_message);
            }
        });

        $("#reverse_charge_ptgcl_igst").on("keyup", function() {
            var reverse_charge_igst = $("#reverse_charge_igst").val();
            var reverse_charge_ptgcl_igst = $(this).val();
            var reverse_charge_btbpic_igst = '';

            if(reverse_charge_igst == '')
            {
                reverse_charge_igst = 0;
            }

            if(reverse_charge_ptgcl_igst == '')
            {
                reverse_charge_ptgcl_igst = 0;
            }

            console.log(reverse_charge_ptgcl_igst);

            reverse_charge_btbpic_igst = reverse_charge_igst - reverse_charge_ptgcl_igst;

            reverse_charge_btbpic_igst = reverse_charge_btbpic_igst.toFixedDown(2);

            $("#reverse_charge_btbpic_igst").val(reverse_charge_btbpic_igst);

            show_reverse_charge_make_payment();
        });

        $("#reverse_charge_ptgcl_cgst").on("keyup", function() {
            var reverse_charge_cgst = $("#reverse_charge_cgst").val();
            var reverse_charge_ptgcl_cgst = $(this).val();
            var reverse_charge_btbpic_cgst = '';

            if(reverse_charge_cgst == '')
            {
                reverse_charge_cgst = 0;
            }

            if(reverse_charge_ptgcl_cgst == '')
            {
                reverse_charge_ptgcl_cgst = 0;
            }

            console.log(reverse_charge_ptgcl_cgst);

            reverse_charge_btbpic_cgst = reverse_charge_cgst - reverse_charge_ptgcl_cgst;

            reverse_charge_btbpic_cgst = reverse_charge_btbpic_cgst.toFixedDown(2);

            $("#reverse_charge_btbpic_cgst").val(reverse_charge_btbpic_cgst);

            show_reverse_charge_make_payment();
        });

        $("#reverse_charge_ptgcl_sgst").on("keyup", function() {
            var reverse_charge_sgst = $("#reverse_charge_sgst").val();
            var reverse_charge_ptgcl_sgst = $(this).val();
            var reverse_charge_btbpic_sgst = '';

            if(reverse_charge_sgst == '')
            {
                reverse_charge_sgst = 0;
            }

            if(reverse_charge_ptgcl_sgst == '')
            {
                reverse_charge_ptgcl_sgst = 0;
            }

            console.log(reverse_charge_ptgcl_sgst);

            reverse_charge_btbpic_sgst = reverse_charge_sgst - reverse_charge_ptgcl_sgst;

            reverse_charge_btbpic_sgst = reverse_charge_btbpic_sgst.toFixedDown(2);

            $("#reverse_charge_btbpic_sgst").val(reverse_charge_btbpic_sgst);

            show_reverse_charge_make_payment();
            
        });

        $("#reverse_charge_ptgcl_cess").on("keyup", function() {
            var reverse_charge_cess = $("#reverse_charge_cess").val();
            var reverse_charge_ptgcl_cess = $(this).val();
            var reverse_charge_btbpic_cess = '';

            if(reverse_charge_cess == '')
            {
                reverse_charge_cess = 0;
            }

            if(reverse_charge_ptgcl_cess == '')
            {
                reverse_charge_ptgcl_cess = 0;
            }

            console.log(reverse_charge_ptgcl_cess);

            reverse_charge_btbpic_cess = reverse_charge_cess - reverse_charge_ptgcl_cess;

            reverse_charge_btbpic_cess = reverse_charge_btbpic_cess.toFixedDown(2);

            $("#reverse_charge_btbpic_cess").val(reverse_charge_btbpic_cess);

            show_reverse_charge_make_payment();
        });

        function show_reverse_charge_make_payment(){
            if( $("#reverse_charge_btbpic_igst").val() == 0 && $("#reverse_charge_btbpic_sgst").val() == 0 && $("#reverse_charge_btbpic_cgst").val() == 0 && $("#reverse_charge_btbpic_cess").val() == 0 ){
                $("#reverse_charge_make_payment_button_block").hide();
                $("#reverse_charge_setoff_button_block").show();
            } else if( $("#reverse_charge_btbpic_igst").val() > 0 || $("#reverse_charge_btbpic_sgst").val() > 0 || $("#reverse_charge_btbpic_cgst").val() > 0 || $("#reverse_charge_btbpic_cess").val() > 0 ) {
                $("#reverse_charge_setoff_button_block").hide();
                $("#reverse_charge_make_payment_button_block").show();
            } else {
                $("#reverse_charge_setoff_button_block").hide();
                $("#reverse_charge_make_payment_button_block").hide();
            }
        }

        $("#liability_latefees_form").on("submit", function(e){
            e.preventDefault();

            var liability_igst_latefees = $("#liability_igst_latefees").val();
            var liability_ptgcl_latefees_igst = $("#liability_ptgcl_latefees_igst").val();
            var liability_btbpic_latefees_igst = $("#liability_btbpic_latefees_igst").val();
//----------------------------------------------------------------------------------------------
            var liability_cgst_latefees = $("#liability_cgst_latefees").val();
            var liability_ptgcl_latefees_cgst = $("#liability_ptgcl_latefees_cgst").val();
            var liability_btbpic_latefees_cgst = $("#liability_btbpic_latefees_cgst").val();
//----------------------------------------------------------------------------------------------
            var liability_sgst_latefees = $("#liability_sgst_latefees").val();
            var liability_ptgcl_latefees_sgst = $("#liability_ptgcl_latefees_sgst").val();
            var liability_btbpic_latefees_sgst = $("#liability_btbpic_latefees_sgst").val();
//----------------------------------------------------------------------------------------------

            var liability_cess_latefees = $("#liability_cess_latefees").val();
            var liability_ptgcl_latefees_cess = $("#liability_ptgcl_latefees_cess").val();
            var liability_btbpic_latefees_cess = $("#liability_btbpic_latefees_cess").val();

            var liability_date = $("#liability_latefees_date").val();
            var shouldContinue = true;

            if(liability_date == ''){
                show_custom_alert(`<span style=\"color: red\">Date is required field.</span>`);
                shouldContinue = false;
            }

            if(liability_igst_latefees == ''){
                liability_igst_latefees = 0;
            }

            if(liability_ptgcl_latefees_igst == ''){
               liability_ptgcl_latefees_igst = 0; 
            }

            if(liability_btbpic_latefees_igst == ''){
                liability_btbpic_latefees_igst = 0;
            }
//--------------------------------------------------

            if(liability_cgst_latefees == ''){
                liability_cgst_latefees = 0;
            }

            if(liability_ptgcl_latefees_cgst == ''){
                liability_ptgcl_latefees_cgst = 0;
            }

            if(liability_btbpic_latefees_cgst == ''){
                liability_btbpic_latefees_cgst = 0;
            }
//-------------------------------------------------

            if(liability_sgst_latefees == ''){
                liability_sgst_latefees = 0;
            }

            if(liability_ptgcl_latefees_sgst == ''){
                liability_ptgcl_latefees_sgst = 0;
            }

            if(liability_btbpic_latefees_sgst == ''){
                liability_btbpic_latefees_sgst = 0;
            }

//-----------------------------------------------------

            if(liability_cess_latefees == ''){
                liability_cess_latefees = 0;
            }

            if(liability_ptgcl_latefees_cess == ''){
                liability_ptgcl_latefees_cess = 0;
            }

            if(liability_btbpic_latefees_cess == ''){
                liability_btbpic_latefees_cess = 0;
            }

//-----------------------------------------------------

            if(shouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.liability.charge") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "liability_igst_latefees": liability_igst_latefees,
                        "liability_ptgcl_latefees_igst": liability_ptgcl_latefees_igst,
                        "liability_btbpic_latefees_igst": liability_btbpic_latefees_igst,

                        "liability_cgst_latefees": liability_cgst_latefees,
                        "liability_ptgcl_latefees_cgst": liability_ptgcl_latefees_cgst,
                        "liability_btbpic_latefees_cgst": liability_btbpic_latefees_cgst,

                        "liability_sgst_latefees": liability_sgst_latefees,
                        "liability_ptgcl_latefees_sgst": liability_ptgcl_latefees_sgst,
                        "liability_btbpic_latefees_sgst": liability_btbpic_latefees_sgst,

                        "liability_cess_latefees": liability_cess_latefees,
                        "liability_ptgcl_latefees_cess": liability_ptgcl_latefees_cess,
                        "liability_btbpic_latefees_cess": liability_btbpic_latefees_cess,

                        "liability_date": liability_date,
                        "liability_type": 'latefees',
                    },
                    success: function(response){
                    
                        console.log(response);

                        if(response == 'success'){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i>Data inserted successfully</span>`);

                            setTimeout(function(){location.reload();}, 5000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i>Some error occured while inserting data.</span>`);
                        }

                    }
                });
            }

        });

        $("#liability_interest_form").on("submit", function(e){
            e.preventDefault();

            var liability_igst_interest = $("#liability_igst_interest").val();
            var liability_ptgcl_interest_igst = $("#liability_ptgcl_interest_igst").val();
            var liability_btbpic_interest_igst = $("#liability_btbpic_interest_igst").val();

//----------------------------------------------------------------------------------------------

            var liability_cgst_interest = $("#liability_cgst_interest").val();
            var liability_ptgcl_interest_cgst = $("#liability_ptgcl_interest_cgst").val();
            var liability_btbpic_interest_cgst = $("#liability_btbpic_interest_cgst").val();
//----------------------------------------------------------------------------------------------
            
            var liability_sgst_interest = $("#liability_sgst_interest").val();
            var liability_ptgcl_interest_sgst = $("#liability_ptgcl_interest_sgst").val();
            var liability_btbpic_interest_sgst = $("#liability_btbpic_interest_sgst").val();

//----------------------------------------------------------------------------------------------

            var liability_cess_interest = $("#liability_cess_interest").val();
            var liability_ptgcl_interest_cess = $("#liability_ptgcl_interest_cess").val();
            var liability_btbpic_interest_cess = $("#liability_btbpic_interest_cess").val();

            var liability_date = $("#liability_interest_date").val();
            var shouldContinue = true;

            if(liability_date == ''){
                show_custom_alert(`<span style=\"color: red\">Date is required field.</span>`);
                shouldContinue = false;
            }
//----------------------------------------------
            if(liability_igst_interest == ''){
                liability_igst_interest = 0;
            }

            if(liability_ptgcl_interest_igst == ''){
                liability_ptgcl_interest_igst = 0;
            }

            if(liability_btbpic_interest_igst == ''){
                liability_btbpic_interest_igst = 0;
            }

//-------------------------------------------------

            if(liability_cgst_interest == ''){
                liability_cgst_interest = 0;
            }

            if(liability_ptgcl_interest_cgst == ''){
                liability_ptgcl_interest_cgst = 0;
            }

            if(liability_btbpic_interest_cgst == ''){
                liability_btbpic_interest_cgst = 0;
            }
//------------------------------------------------

            if(liability_sgst_interest == ''){
                liability_sgst_interest = 0;
            }

            if(liability_ptgcl_interest_sgst == ''){
                liability_ptgcl_interest_sgst = 0
            }

            if(liability_btbpic_interest_sgst == ''){
                liability_btbpic_interest_sgst = 0;
            }
//----------------------------------------------------

            if(liability_cess_interest == ''){
                liability_cess_interest = 0;
            }

            if(liability_ptgcl_interest_cess == ''){
                liability_ptgcl_interest_cess = 0;
            }

            if(liability_btbpic_interest_cess == ''){
                liability_btbpic_interest_cess = 0;
            }
//-----------------------------------------------------

            if(shouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.liability.charge") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',

                        
                        "liability_igst_interest": liability_igst_interest,
                        "liability_ptgcl_interest_igst": liability_ptgcl_interest_igst,
                        "liability_btbpic_interest_igst": liability_btbpic_interest_igst,

                        "liability_cgst_interest": liability_cgst_interest,
                        "liability_ptgcl_interest_cgst": liability_ptgcl_interest_cgst,
                        "liability_btbpic_interest_cgst": liability_btbpic_interest_cgst,

                        "liability_sgst_interest": liability_sgst_interest,
                        "liability_ptgcl_interest_sgst": liability_ptgcl_interest_sgst,
                        "liability_btbpic_interest_sgst": liability_btbpic_interest_sgst,

                        "liability_cess_interest": liability_cess_interest,
                        "liability_ptgcl_interest_cess": liability_ptgcl_interest_cess,
                        "liability_btbpic_interest_cess": liability_btbpic_interest_cess,

                        "liability_date": liability_date,
                        "liability_type": 'interest',
                    },
                    success: function(response){
                    
                        console.log(response);

                        if(response == 'success'){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i>Data inserted successfully</span>`);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i>Some error occured while inserting data.</span>`);
                        }

                    }
                });
            }

        });

        $("#liability_penalty_form").on("submit", function(e){
            e.preventDefault();
            
            var liability_igst_penalty = $("#liability_igst_penalty").val();
            var liability_ptgcl_penalty_igst = $("#liability_ptgcl_penalty_igst").val();
            var liability_btbpic_penalty_igst = $("#liability_btbpic_penalty_igst").val();

//----------------------------------------------------------------------------------------------
            
            var liability_cgst_penalty = $("#liability_cgst_penalty").val();
            var liability_ptgcl_penalty_cgst = $("#liability_ptgcl_penalty_cgst").val();
            var liability_btbpic_penalty_cgst = $("#liability_btbpic_penalty_cgst").val();
//----------------------------------------------------------------------------------------------
            
            var liability_sgst_penalty = $("#liability_sgst_penalty").val();
            var liability_ptgcl_penalty_sgst = $("#liability_ptgcl_penalty_sgst").val();
            var liability_btbpic_penalty_sgst = $("#liability_btbpic_penalty_sgst").val();

//----------------------------------------------------------------------------------------------

            var liability_cess_penalty = $("#liability_cess_penalty").val();
            var liability_ptgcl_penalty_cess = $("#liability_ptgcl_penalty_cess").val();
            var liability_btbpic_penalty_cess = $("#liability_btbpic_penalty_cess").val();

            var liability_date = $("#liability_penalty_date").val();
            var shouldContinue = true;

            if(liability_date == ''){
                show_custom_alert(`<span style=\"color: red\">Date is required field.</span>`);
                shouldContinue = false;
            }

            
//----------------------------------------------
            if(liability_igst_penalty == ''){
                liability_igst_penalty = 0;
            }

            if(liability_ptgcl_penalty_igst == ''){
                liability_ptgcl_penalty_igst = 0;
            }

            if(liability_btbpic_penalty_igst == ''){
                liability_btbpic_penalty_igst = 0;
            }
//------------------------------------------------

            if(liability_cgst_penalty == ''){
                liability_cgst_penalty = 0;
            }

            if(liability_ptgcl_penalty_cgst == ''){
                liability_ptgcl_penalty_cgst = 0;
            }

            if(liability_btbpic_penalty_cgst == ''){
                liability_btbpic_penalty_cgst = 0;
            }

//----------------------------------------------------

            if(liability_sgst_penalty == ''){
                liability_sgst_penalty = 0;
            }

            if(liability_ptgcl_penalty_sgst == ''){
                liability_ptgcl_penalty_sgst = 0;
            }

            if(liability_btbpic_penalty_sgst == ''){
                liability_btbpic_penalty_sgst = 0;
            }
//-----------------------------------------------------

            if(liability_cess_penalty == ''){
                liability_cess_penalty = 0;
            }

            if(liability_ptgcl_penalty_cess == ''){
                liability_ptgcl_penalty_cess = 0;
            }

            if(liability_btbpic_penalty_cess == ''){
                liability_btbpic_penalty_cess = 0;
            }
//-----------------------------------------------------

            if(shouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.liability.charge") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',


                        "liability_igst_penalty": liability_igst_penalty,
                        "liability_ptgcl_penalty_igst": liability_ptgcl_penalty_igst,
                        "liability_btbpic_penalty_igst": liability_btbpic_penalty_igst,

                        "liability_cgst_penalty": liability_cgst_penalty,
                        "liability_ptgcl_penalty_cgst": liability_ptgcl_penalty_cgst,
                        "liability_btbpic_penalty_cgst": liability_btbpic_penalty_cgst,

                        "liability_sgst_penalty": liability_sgst_penalty,
                        "liability_ptgcl_penalty_sgst": liability_ptgcl_penalty_sgst,
                        "liability_btbpic_penalty_sgst": liability_btbpic_penalty_sgst,

                        "liability_cess_penalty": liability_cess_penalty,
                        "liability_ptgcl_penalty_cess": liability_ptgcl_penalty_cess,
                        "liability_btbpic_penalty_cess": liability_btbpic_penalty_cess,

                        "liability_date": liability_date,
                        "liability_type": 'penalty',
                        
                    },
                    success: function(response){
                    
                        console.log(response);

                        if(response == 'success'){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i>Data inserted successfully</span>`);
                            setTimeout(function(){location.reload();}, 5000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i>Some error occured while inserting data.</span>`);
                        }

                    }
                });
            }

        });

        $("#liability_others_form").on("submit", function(e){
            e.preventDefault();
            
            var liability_igst_others = $("#liability_igst_others").val();
            var liability_ptgcl_others_igst = $("#liability_ptgcl_others_igst").val();
            var liability_btbpic_others_igst = $("#liability_btbpic_others_igst").val();
//----------------------------------------------------------------------------------------------
            
            var liability_cgst_others = $("#liability_cgst_others").val();
            var liability_ptgcl_others_cgst = $("#liability_ptgcl_others_cgst").val();
            var liability_btbpic_others_cgst = $("#liability_btbpic_others_cgst").val();
//----------------------------------------------------------------------------------------------
            
            var liability_sgst_others = $("#liability_sgst_others").val();
            var liability_ptgcl_others_sgst = $("#liability_ptgcl_others_sgst").val();
            var liability_btbpic_others_sgst = $("#liability_btbpic_others_sgst").val();

//----------------------------------------------------------------------------------------------

            var liability_cess_others = $("#liability_cess_others").val();
            var liability_ptgcl_others_cess = $("#liability_ptgcl_others_cess").val();
            var liability_btbpic_others_cess = $("#liability_btbpic_others_cess").val();

            var liability_date = $("#liability_others_date").val();
            var shouldContinue = true;

            if(liability_date == ''){
                show_custom_alert(`<span style=\"color: red\">Date is required field.</span>`);
                shouldContinue = false;
            }


            if(liability_igst_others == ''){
                liability_igst_others = 0;
            }

            if(liability_ptgcl_others_igst == ''){
                liability_ptgcl_others_igst = 0;
            }

            if(liability_btbpic_others_igst == ''){
                liability_btbpic_others_igst = 0;
            }
//----------------------------------------------------

            if(liability_cgst_others == ''){
                liability_cgst_others = 0;
            }

            if(liability_ptgcl_others_cgst == ''){
                liability_ptgcl_others_cgst = 0;
            }

            if(liability_btbpic_others_cgst == ''){
                liability_btbpic_others_cgst = 0;
            }
//-----------------------------------------------------

            if(liability_sgst_others == ''){
                liability_sgst_others = 0;
            }

            if(liability_ptgcl_others_sgst == ''){
                liability_ptgcl_others_sgst = 0;
            }

            if(liability_btbpic_others_sgst == ''){
                liability_btbpic_others_sgst = 0;
            }

//-----------------------------------------------------
            if(liability_cess_others == ''){
                liability_cess_others = 0;
            }

            if(liability_ptgcl_others_cess == ''){
                liability_ptgcl_others_cess = 0;
            }

            if(liability_btbpic_others_cess == ''){
                liability_btbpic_others_cess = 0;
            }
//-----------------------------------------------------

            if(shouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.liability.charge") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',

                        "liability_igst_others": liability_igst_others,
                        "liability_ptgcl_others_igst": liability_ptgcl_others_igst,
                        "liability_btbpic_others_igst": liability_btbpic_others_igst,

                        "liability_cgst_others": liability_cgst_others,
                        "liability_ptgcl_others_cgst": liability_ptgcl_others_cgst,
                        "liability_btbpic_others_cgst": liability_btbpic_others_cgst,

                        "liability_sgst_others": liability_sgst_others,
                        "liability_ptgcl_others_sgst": liability_ptgcl_others_sgst,
                        "liability_btbpic_others_sgst": liability_btbpic_others_sgst,

                        "liability_cess_others": liability_cess_others,
                        "liability_ptgcl_others_cess": liability_ptgcl_others_cess,
                        "liability_btbpic_others_cess": liability_btbpic_others_cess,

                        "liability_date": liability_date,
                        "liability_type": 'others',
                    },
                    success: function(response){
                    
                        console.log(response);

                        if(response == 'success'){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i>Data inserted successfully</span>`);

                            setTimeout(function(){location.reload();}, 5000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i>Some error occured while inserting data.</span>`);
                        }

                    }
                });
            }

        });

        $("#liability_form_make_payment").on("submit", function(e){
            e.preventDefault();
            
            var form3ShouldContinue = true;
            var form3_setoff_type = $("#form3_setoff_type").val();
            var form3_type = $("#form3_type").val();
            var form3_cgst = $("#form3_cgst").val();
            var form3_sgst = $("#form3_sgst").val();
            var form3_igst = $("#form3_igst").val();
            var form3_cess = $("#form3_cess").val();

            var form3_total = $("#form3_total").val();
            var form3_voucherNo = $("#form3_voucherNo").val();
            var form3_date = $("#form3_date").val();
            var form3_cin = $("#form3_cin").val();
            var form3_amount_paid = $("#form3_amount_paid").val();
            var form3_type_of_payment = Array();
            var form3_message = Array();

            var form3_cashed_amount = '';
            
            var form3_banked_amount = '';
            var form3_bank_cheque = '';
            var form3_bank = '';
            
            var form3_posed_amount = '';
            var form3_pos = '';

            if(form3_type == ''){
                form3ShouldContinue = false;
                form3_message.push("Type is required");
            }

            if(form3_cgst == ''){
                form3ShouldContinue = false;
                form3_message.push("CGST is required");
            }

            if(form3_sgst == ''){
                form3ShouldContinue = false;
                form3_message.push("SGST is required");
            }

            if(form3_igst == ''){
                form3ShouldContinue = false;
                form3_message.push("IGST is required");
            }

            if(form3_cess == ''){
                form3ShouldContinue = false;
                form3_message.push("CESS is required");
            }

            if(form3_total == ''){
                form3ShouldContinue = false;
                form3_message.push("Total is required");
            }

            if(form3_voucherNo == ''){
                form3ShouldContinue = false;
                form3_message.push("Voucher No. is required");
            }

            if(form3_date == ''){
                form3ShouldContinue = false;
                form3_message.push("Date is required");
            }

            if(form3_cin == ''){
                form3ShouldContinue = false;
                form3_message.push("CIN is required");
            }

            if($("#form3_cash_checkbox").is(":checked")){
                form3_type_of_payment.push("cash");
                form3_cashed_amount = $("#form3_cashed_amount").val();

                if(form3_cashed_amount == ''){
                    form3ShouldContinue = false;
                    form3_message.push("Cash Amount is required");
                }
            }

            if($("#form3_bank_checkbox").is(":checked")){
                form3_type_of_payment.push("bank");

                form3_banked_amount = $("#form3_banked_amount").val();
                form3_bank_cheque = $("#form3_bank_cheque").val();
                form3_bank = $("#form3_bank").val();

                if(form3_banked_amount == ''){
                    form3ShouldContinue = false;
                    form3_message.push("Bank Amount is required");
                }

                if(form3_bank_cheque == ''){
                    form3ShouldContinue = false;
                    form3_message.push("Bank Cheque is required");
                }

                if(form3_bank == '' || form3_bank == null){
                    form3ShouldContinue = false;
                    form3_message.push("Bank is required");
                }
            }

            if($("#form3_pos_checkbox").is(":checked")){
                form3_type_of_payment.push("pos");

                form3_posed_amount = $("#form3_posed_amount").val();
                form3_pos = $("#form3_pos").val();

                if(form3_posed_amount == ''){
                    form3ShouldContinue = false;
                    form3_message.push("POS Amount is required");
                }

                if(form3_pos == '' || form3_pos == null){
                    form3ShouldContinue = false;
                    form3_message.push("POS Bank is required");
                }
            }

            if(form3_amount_paid == ''){
                form3ShouldContinue = false;
                form3_message.push("Amount Received is required");
            }

            if(form3_total !== form3_amount_paid){
                form3ShouldContinue = false;
                form3_message.push("Amount Received should be equal to Total");
            }

            if(form3ShouldContinue){
                $.ajax({
                    type: 'POST',
                    url: '{{ route("post.gst.setoff") }}',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "cgst": form3_cgst,
                        "sgst": form3_sgst,
                        "igst": form3_igst,
                        "cess": form3_cess,
                        "total": form3_total,
                        "voucher_no": form3_voucherNo,
                        "date": form3_date,
                        "cin": form3_cin,
                        "type_of_payment": form3_type_of_payment,
                        "cashed_amount": form3_cashed_amount,
                        "banked_amount": form3_banked_amount,
                        "posed_amount": form3_posed_amount,
                        "bank": form3_bank,
                        "bank_cheque": form3_bank_cheque,
                        "pos_bank": form3_pos,
                        "amount_received": form3_amount_paid,
                        "type": form3_type,
                    },
                    success: function(response){

                        console.log(response);

                        if(response.success){
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> ${response.message}</span>`);
                        }
                        else{
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> ${response.message}</span>`);
                        }
                        
                        $("#form3_modal").modal('hide');

                        //-------------------------------------------------

                        if(form3_setoff_type == 'liability'){
                            $("#liability_btbpic_latefees_igst").val(0);
                            $("#liability_btbpic_latefees_cgst").val(0);
                            $("#liability_btbpic_latefees_sgst").val(0);
                            $("#liability_btbpic_latefees_cess").val(0);

                            $("#liability_make_payment_latefees_button_block").hide();
                            $("#liability_setoff_latefees_button_block").show();
                        }

                        if(form3_setoff_type == 'interest'){
                            $("#liability_btbpic_interest_igst").val(0);
                            $("#liability_btbpic_interest_cgst").val(0);
                            $("#liability_btbpic_interest_sgst").val(0);
                            $("#liability_btbpic_interest_cess").val(0);

                            $("#liability_make_payment_interest_button_block").hide();
                            $("#liability_setoff_interest_button_block").show();
                        }

                        if(form3_setoff_type == 'penalty'){
                            $("#liability_btbpic_penalty_igst").val(0);
                            $("#liability_btbpic_penalty_cgst").val(0);
                            $("#liability_btbpic_penalty_sgst").val(0);
                            $("#liability_btbpic_penalty_cess").val(0);

                            $("#liability_make_payment_penalty_button_block").hide();
                            $("#liability_setoff_penalty_button_block").show();
                        }

                        if(form3_setoff_type == 'others'){
                            $("#liability_btbpic_others_igst").val(0);
                            $("#liability_btbpic_others_cgst").val(0);
                            $("#liability_btbpic_others_sgst").val(0);
                            $("#liability_btbpic_others_cess").val(0);

                            $("#liability_make_payment_others_button_block").hide();
                            $("#liability_setoff_others_button_block").show();
                        }

                        
                    }
                });
            } else {
                show_custom_alert(form3_message);
            }
        });

        $("#liability_ptgcl_latefees_igst").on("keyup", function() {
            var liability_igst_latefees = $("#liability_igst_latefees").val();
            var liability_ptgcl_latefees_igst = $(this).val();
            var liability_btbpic_latefees_igst = '';

            if(liability_igst_latefees == '')
            {
                liability_igst_latefees = 0;
            }

            if(liability_ptgcl_latefees_igst == '')
            {
                liability_ptgcl_latefees_igst = 0;
            }

            if(liability_btbpic_latefees_igst == '')
            {
                liability_btbpic_latefees_igst = 0;
            }

            // console.log(reverse_charge_ptgcl_cess);

            liability_btbpic_latefees_igst = liability_igst_latefees - liability_ptgcl_latefees_igst;

            liability_btbpic_latefees_igst = liability_btbpic_latefees_igst.toFixedDown(2);

            $("#liability_btbpic_latefees_igst").val(liability_btbpic_latefees_igst);

            show_liability_latefees_make_payment();
        });

        $("#liability_ptgcl_latefees_cgst").on("keyup", function() {
            var liability_cgst_latefees = $("#liability_cgst_latefees").val();
            var liability_ptgcl_latefees_cgst = $(this).val();
            var liability_btbpic_latefees_cgst = '';

            if(liability_cgst_latefees == '')
            {
                liability_cgst_latefees = 0;
            }

            if(liability_ptgcl_latefees_cgst == '')
            {
                liability_ptgcl_latefees_cgst = 0;
            }

            if(liability_btbpic_latefees_cgst == '')
            {
                liability_btbpic_latefees_cgst = 0;
            }


            liability_btbpic_latefees_cgst = liability_cgst_latefees - liability_ptgcl_latefees_cgst;

            liability_btbpic_latefees_cgst = liability_btbpic_latefees_cgst.toFixedDown(2);

            $("#liability_btbpic_latefees_cgst").val(liability_btbpic_latefees_cgst);

            show_liability_latefees_make_payment();
        });

        $("#liability_ptgcl_latefees_sgst").on("keyup", function() {
            var liability_sgst_latefees = $("#liability_sgst_latefees").val();
            var liability_ptgcl_latefees_sgst = $(this).val();
            var liability_btbpic_latefees_sgst = '';

            if(liability_sgst_latefees == '')
            {
                liability_sgst_latefees = 0;
            }

            if(liability_ptgcl_latefees_sgst == '')
            {
                liability_ptgcl_latefees_sgst = 0;
            }

            if(liability_btbpic_latefees_sgst == '')
            {
                liability_btbpic_latefees_sgst = 0;
            }


            liability_btbpic_latefees_sgst = liability_sgst_latefees - liability_ptgcl_latefees_sgst;

            liability_btbpic_latefees_sgst = liability_btbpic_latefees_sgst.toFixedDown(2);

            $("#liability_btbpic_latefees_sgst").val(liability_btbpic_latefees_sgst);

            show_liability_latefees_make_payment();
        });

        $("#liability_ptgcl_latefees_cess").on("keyup", function() {
            var liability_cess_latefees = $("#liability_cess_latefees").val();
            var liability_ptgcl_latefees_cess = $(this).val();
            var liability_btbpic_latefees_cess = '';

            if(liability_cess_latefees == '')
            {
                liability_cess_latefees = 0;
            }

            if(liability_ptgcl_latefees_cess == '')
            {
                liability_ptgcl_latefees_cess = 0;
            }

            if(liability_btbpic_latefees_cess == '')
            {
                liability_btbpic_latefees_cess = 0;
            }


            liability_btbpic_latefees_cess = liability_cess_latefees - liability_ptgcl_latefees_cess;

            liability_btbpic_latefees_cess = liability_btbpic_latefees_cess.toFixedDown(2);

            $("#liability_btbpic_latefees_cess").val(liability_btbpic_latefees_cess);

            show_liability_latefees_make_payment();
        });

        $("#liability_ptgcl_interest_igst").on("keyup", function() {
            var liability_igst_interest = $("#liability_igst_interest").val();
            var liability_ptgcl_interest_igst = $(this).val();
            var liability_btbpic_interest_igst = '';

            if(liability_igst_interest == '')
            {
                liability_igst_interest = 0;
            }

            if(liability_ptgcl_interest_igst == '')
            {
                liability_ptgcl_interest_igst = 0;
            }

            if(liability_btbpic_interest_igst == '')
            {
                liability_btbpic_interest_igst = 0;
            }


            liability_btbpic_interest_igst = liability_igst_interest - liability_ptgcl_interest_igst;

            liability_btbpic_interest_igst = liability_btbpic_interest_igst.toFixedDown(2);

            $("#liability_btbpic_interest_igst").val(liability_btbpic_interest_igst);

            show_liability_interest_make_payment();
        });

        $("#liability_ptgcl_interest_cgst").on("keyup", function() {
            var liability_cgst_interest = $("#liability_cgst_interest").val();
            var liability_ptgcl_interest_cgst = $(this).val();
            var liability_btbpic_interest_cgst = '';

            if(liability_cgst_interest == '')
            {
                liability_cgst_interest = 0;
            }

            if(liability_ptgcl_interest_cgst == '')
            {
                liability_ptgcl_interest_cgst = 0;
            }

            if(liability_btbpic_interest_cgst == '')
            {
                liability_btbpic_interest_cgst = 0;
            }


            liability_btbpic_interest_cgst = liability_cgst_interest - liability_ptgcl_interest_cgst;

            liability_btbpic_interest_cgst = liability_btbpic_interest_cgst.toFixedDown(2);

            $("#liability_btbpic_interest_cgst").val(liability_btbpic_interest_cgst);

            show_liability_interest_make_payment();
        });

        $("#liability_ptgcl_interest_sgst").on("keyup", function() {
            var liability_sgst_interest = $("#liability_sgst_interest").val();
            var liability_ptgcl_interest_sgst = $(this).val();
            var liability_btbpic_interest_sgst = '';

            if(liability_sgst_interest == '')
            {
                liability_sgst_interest = 0;
            }

            if(liability_ptgcl_interest_sgst == '')
            {
                liability_ptgcl_interest_sgst = 0;
            }

            if(liability_btbpic_interest_sgst == '')
            {
                liability_btbpic_interest_sgst = 0;
            }


            liability_btbpic_interest_sgst = liability_sgst_interest - liability_ptgcl_interest_sgst;

            liability_btbpic_interest_sgst = liability_btbpic_interest_sgst.toFixedDown(2);

            $("#liability_btbpic_interest_sgst").val(liability_btbpic_interest_sgst);

            show_liability_interest_make_payment();
        });

        $("#liability_ptgcl_interest_cess").on("keyup", function() {
            var liability_cess_interest = $("#liability_cess_interest").val();
            var liability_ptgcl_interest_cess = $(this).val();
            var liability_btbpic_interest_cess = '';

            if(liability_cess_interest == '')
            {
                liability_cess_interest = 0;
            }

            if(liability_ptgcl_interest_cess == '')
            {
                liability_ptgcl_interest_cess = 0;
            }

            if(liability_btbpic_interest_cess == '')
            {
                liability_btbpic_interest_cess = 0;
            }


            liability_btbpic_interest_cess = liability_cess_interest - liability_ptgcl_interest_cess;

            liability_btbpic_interest_cess = liability_btbpic_interest_cess.toFixedDown(2);

            $("#liability_btbpic_interest_cess").val(liability_btbpic_interest_cess);

            show_liability_interest_make_payment();
        });

        $("#liability_ptgcl_penalty_igst").on("keyup", function() {
            var liability_igst_penalty = $("#liability_igst_penalty").val();
            var liability_ptgcl_penalty_igst = $(this).val();
            var liability_btbpic_penalty_igst = '';

            if(liability_igst_penalty == '')
            {
                liability_igst_penalty = 0;
            }

            if(liability_ptgcl_penalty_igst == '')
            {
                liability_ptgcl_penalty_igst = 0;
            }

            if(liability_btbpic_penalty_igst == '')
            {
                liability_btbpic_penalty_igst = 0;
            }


            liability_btbpic_penalty_igst = liability_igst_penalty - liability_ptgcl_penalty_igst;

            liability_btbpic_penalty_igst = liability_btbpic_penalty_igst.toFixedDown(2);

            $("#liability_btbpic_penalty_igst").val(liability_btbpic_penalty_igst);

            show_liability_penalty_make_payment();
        });

        $("#liability_ptgcl_penalty_cgst").on("keyup", function() {
            var liability_cgst_penalty = $("#liability_cgst_penalty").val();
            var liability_ptgcl_penalty_cgst = $(this).val();
            var liability_btbpic_penalty_cgst = '';

            if(liability_cgst_penalty == '')
            {
                liability_cgst_penalty = 0;
            }

            if(liability_ptgcl_penalty_cgst == '')
            {
                liability_ptgcl_penalty_cgst = 0;
            }

            if(liability_btbpic_penalty_cgst == '')
            {
                liability_btbpic_penalty_cgst = 0;
            }


            liability_btbpic_penalty_cgst = liability_cgst_penalty - liability_ptgcl_penalty_cgst;

            liability_btbpic_penalty_cgst = liability_btbpic_penalty_cgst.toFixedDown(2);

            $("#liability_btbpic_penalty_cgst").val(liability_btbpic_penalty_cgst);

            show_liability_penalty_make_payment();
        });

        $("#liability_ptgcl_penalty_sgst").on("keyup", function() {
            var liability_sgst_penalty = $("#liability_sgst_penalty").val();
            var liability_ptgcl_penalty_sgst = $(this).val();
            var liability_btbpic_penalty_sgst = '';

            if(liability_sgst_penalty == '')
            {
                liability_sgst_penalty = 0;
            }

            if(liability_ptgcl_penalty_sgst == '')
            {
                liability_ptgcl_penalty_sgst = 0;
            }

            if(liability_btbpic_penalty_sgst == '')
            {
                liability_btbpic_penalty_sgst = 0;
            }


            liability_btbpic_penalty_sgst = liability_sgst_penalty - liability_ptgcl_penalty_sgst;

            liability_btbpic_penalty_sgst = liability_btbpic_penalty_sgst.toFixedDown(2);

            $("#liability_btbpic_penalty_sgst").val(liability_btbpic_penalty_sgst);

            show_liability_penalty_make_payment();
        });

        $("#liability_ptgcl_penalty_cess").on("keyup", function() {
            var liability_cess_penalty = $("#liability_cess_penalty").val();
            var liability_ptgcl_penalty_cess = $(this).val();
            var liability_btbpic_penalty_cess = '';

            if(liability_cess_penalty == '')
            {
                liability_cess_penalty = 0;
            }

            if(liability_ptgcl_penalty_cess == '')
            {
                liability_ptgcl_penalty_cess = 0;
            }

            if(liability_btbpic_penalty_cess == '')
            {
                liability_btbpic_penalty_cess = 0;
            }


            liability_btbpic_penalty_cess = liability_cess_penalty - liability_ptgcl_penalty_cess;

            liability_btbpic_penalty_cess = liability_btbpic_penalty_cess.toFixedDown(2);

            $("#liability_btbpic_penalty_cess").val(liability_btbpic_penalty_cess);

            show_liability_penalty_make_payment();
        });

        $("#liability_ptgcl_others_igst").on("keyup", function() {
            var liability_igst_others = $("#liability_igst_others").val();
            var liability_ptgcl_others_igst = $(this).val();
            var liability_btbpic_others_igst = '';

            if(liability_igst_others == '')
            {
                liability_igst_others = 0;
            }

            if(liability_ptgcl_others_igst == '')
            {
                liability_ptgcl_others_igst = 0;
            }

            if(liability_btbpic_others_igst == '')
            {
                liability_btbpic_others_igst = 0;
            }


            liability_btbpic_others_igst = liability_igst_others - liability_ptgcl_others_igst;

            liability_btbpic_others_igst = liability_btbpic_others_igst.toFixedDown(2);

            $("#liability_btbpic_others_igst").val(liability_btbpic_others_igst);

            show_liability_others_make_payment();
        });

        $("#liability_ptgcl_others_cgst").on("keyup", function() {
            var liability_cgst_others = $("#liability_cgst_others").val();
            var liability_ptgcl_others_cgst = $(this).val();
            var liability_btbpic_others_cgst = '';

            if(liability_cgst_others == '')
            {
                liability_cgst_others = 0;
            }

            if(liability_ptgcl_others_cgst == '')
            {
                liability_ptgcl_others_cgst = 0;
            }

            if(liability_btbpic_others_cgst == '')
            {
                liability_btbpic_others_cgst = 0;
            }


            liability_btbpic_others_cgst = liability_cgst_others - liability_ptgcl_others_cgst;

            liability_btbpic_others_cgst = liability_btbpic_others_cgst.toFixedDown(2);

            $("#liability_btbpic_others_cgst").val(liability_btbpic_others_cgst);

            show_liability_others_make_payment();
        });

        $("#liability_ptgcl_others_sgst").on("keyup", function() {
            var liability_sgst_others = $("#liability_sgst_others").val();
            var liability_ptgcl_others_sgst = $(this).val();
            var liability_btbpic_others_sgst = '';

            if(liability_sgst_others == '')
            {
                liability_sgst_others = 0;
            }

            if(liability_ptgcl_others_sgst == '')
            {
                liability_ptgcl_others_sgst = 0;
            }

            if(liability_btbpic_others_sgst == '')
            {
                liability_btbpic_others_sgst = 0;
            }


            liability_btbpic_others_sgst = liability_sgst_others - liability_ptgcl_others_sgst;

            liability_btbpic_others_sgst = liability_btbpic_others_sgst.toFixedDown(2);

            $("#liability_btbpic_others_sgst").val(liability_btbpic_others_sgst);

            show_liability_others_make_payment();
        });

        $("#liability_ptgcl_others_cess").on("keyup", function() {
            var liability_cess_others = $("#liability_cess_others").val();
            var liability_ptgcl_others_cess = $(this).val();
            var liability_btbpic_others_cess = '';

            if(liability_cess_others == '')
            {
                liability_cess_others = 0;
            }

            if(liability_ptgcl_others_cess == '')
            {
                liability_ptgcl_others_cess = 0;
            }

            if(liability_btbpic_others_cess == '')
            {
                liability_btbpic_others_cess = 0;
            }


            liability_btbpic_others_cess = liability_cess_others - liability_ptgcl_others_cess;

            liability_btbpic_others_cess = liability_btbpic_others_cess.toFixedDown(2);

            $("#liability_btbpic_others_cess").val(liability_btbpic_others_cess);

            show_liability_others_make_payment();
        });

        function show_liability_latefees_make_payment(){
            if( $("#liability_btbpic_latefees_igst").val() == 0 && $("#liability_btbpic_latefees_cgst").val() == 0 && $("#liability_btbpic_latefees_sgst").val() == 0 && $("#liability_btbpic_latefees_cess").val() == 0 ){
                $("#liability_make_payment_latefees_button_block").hide();
                $("#liability_setoff_latefees_button_block").show();
                //--------------------------------------------------
                // $(".setoff-liability-latefees").css('visibility', 'visible');

            } else if( $("#liability_btbpic_latefees_igst").val() > 0 || $("#liability_btbpic_latefees_cgst").val() > 0 || $("#liability_btbpic_latefees_sgst").val() > 0 || $("#liability_btbpic_latefees_cess").val() > 0 ) {
                $("#liability_setoff_latefees_button_block").hide();
                $("#liability_make_payment_latefees_button_block").show();
                //--------------------------------------------------
                // $(".liability-makepayment-latefees").css('visibility', 'visible');
            } else {
                $("#liability_setoff_latefees_button_block").hide();
                $("#liability_make_payment_latefees_button_block").hide();
                //--------------------------------------------------
                // $(".setoff-liability-latefees").css('visibility', 'hidden');
                // $(".setoff-liablity-others").css('visibility', 'hidden');
                // $(".setoff-liability-penalty").css('visibility', 'hidden');
                // $(".setoff-liability-interest").css('visibility', 'hidden');

                // $(".liability-makepayment-latefees").css('visibility', 'hidden');
                // $(".liability-makepayment-interest").css('visibility', 'hidden');
                // $(".liability-makepayment-penalty").css('visibility', 'hidden');
                // $(".liability-makepayment-others").css('visibility', 'hidden');
            }
        }

        function show_liability_interest_make_payment(){
            if( $("#liability_btbpic_interest_igst").val() == 0 && $("#liability_btbpic_interest_cgst").val() == 0 && $("#liability_btbpic_interest_sgst").val() == 0 && $("#liability_btbpic_interest_cess").val() == 0 ){
                $("#liability_make_payment_interest_button_block").hide();
                $("#liability_setoff_interest_button_block").show();
                //--------------------------------------------------
                // $(".setoff-liability-interest").css('visibility', 'visible');

            } else if( $("#liability_btbpic_interest_igst").val() > 0 || $("#liability_btbpic_interest_cgst").val() > 0 || $("#liability_btbpic_interest_sgst").val() > 0 || $("#liability_btbpic_interest_cess").val() > 0 ) {
                $("#liability_setoff_interest_button_block").hide();
                $("#liability_make_payment_interest_button_block").show();
                //--------------------------------------------------
                // $(".liability-makepayment-interest").css('visibility', 'visible');
            } else {
                $("#liability_setoff_interest_button_block").hide();
                $("#liability_make_payment_interest_button_block").hide();
                //--------------------------------------------------
                // $(".setoff-liability-interest").css('visibility', 'hidden');
                // $(".setoff-liablity-others").css('visibility', 'hidden');
                // $(".setoff-liability-penalty").css('visibility', 'hidden');
                // $(".setoff-liability-interest").css('visibility', 'hidden');

                // $(".liability-makepayment-interest").css('visibility', 'hidden');
                // $(".liability-makepayment-latefees").css('visibility', 'hidden');
                // $(".liability-makepayment-penalty").css('visibility', 'hidden');
                // $(".liability-makepayment-others").css('visibility', 'hidden');
            }
        }

        function show_liability_penalty_make_payment(){
            if( $("#liability_btbpic_penalty_igst").val() == 0 && $("#liability_btbpic_penalty_cgst").val() == 0 && $("#liability_btbpic_penalty_sgst").val() == 0 && $("#liability_btbpic_penalty_cess").val() == 0 ){
                $("#liability_make_payment_penalty_button_block").hide();
                $("#liability_setoff_penalty_button_block").show();
                //--------------------------------------------------
                // $(".setoff-liability-penalty").css('visibility', 'visible');

            } else if( $("#liability_btbpic_penalty_igst").val() > 0 || $("#liability_btbpic_penalty_cgst").val() > 0 || $("#liability_btbpic_penalty_sgst").val() > 0 || $("#liability_btbpic_penalty_cess").val() > 0 ) {
                $("#liability_setoff_penalty_button_block").hide();
                $("#liability_make_payment_penalty_button_block").show();
                //--------------------------------------------------
                // $(".liability-makepayment-penalty").css('visibility', 'visible');
            } else {
                $("#liability_setoff_penalty_button_block").hide();
                $("#liability_make_payment_penalty_button_block").hide();
                //--------------------------------------------------
                // $(".setoff-liability-penalty").css('visibility', 'hidden');
                // $(".setoff-liablity-others").css('visibility', 'hidden');
                // $(".setoff-liability-interest").css('visibility', 'hidden');
                // $(".setoff-liability-latefees").css('visibility', 'hidden');

                // $(".liability-makepayment-penalty").css('visibility', 'hidden');
                // $(".liability-makepayment-others").css('visibility', 'hidden');
                // $(".liability-makepayment-interest").css('visibility', 'hidden');
                // $(".liability-makepayment-latefees").css('visibility', 'hidden');
            }
        }

        function show_liability_others_make_payment(){
            if( $("#liability_btbpic_others_igst").val() == 0 && $("#liability_btbpic_others_cgst").val() == 0 && $("#liability_btbpic_others_sgst").val() == 0 && $("#liability_btbpic_others_cess").val() == 0 ){
                $("#liability_make_payment_others_button_block").hide();
                $("#liability_setoff_others_button_block").show();
                //--------------------------------------------------
                // $(".setoff-liablity-others").css('visibility', 'visible');

            } else if( $("#liability_btbpic_others_igst").val() > 0 || $("#liability_btbpic_others_cgst").val() > 0 || $("#liability_btbpic_others_sgst").val() > 0 || $("#liability_btbpic_others_cess").val() > 0 ) {
                $("#liability_setoff_others_button_block").hide();
                $("#liability_make_payment_others_button_block").show();
                //--------------------------------------------------
                // $(".liability-makepayment-others").css('visibility', 'visible');
            } 
            else {
                $("#liability_setoff_others_button_block").hide();
                $("#liability_make_payment_others_button_block").hide();
                //--------------------------------------------------
            }
        }
    </script>
@endsection
