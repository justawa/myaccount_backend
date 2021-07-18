@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-ledger') !!}

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <form class="form-horizontal" action="{{ route('gst.ledger') }}" method="get">
                <div class="form-group">
                    <div class="col-md-5">
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
                <div class="panel-heading">GST Payable</div>
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
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <button type="button" id="form1_make_payment" class="btn btn-success">Make Payment</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="3">GST Payable (CMP)</th>
                                <th rowspan="2">Paid Through GST Cash Ledger</th>
                                <th rowspan="2">Balance to paid in Cash</th>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <th>Under Reverse Charge</th>
                                <th>Other than Reverse Charge</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>CGST</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>IGST</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <button type="button" id="form2_make_payment" class="btn btn-success">Make Payment</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="7">GST to be Paid in Cash</th>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <th>Late Fees</th>
                                <th>Interest</th>
                                <th>Penalty</th>
                                <th>Others</th>
                                <th>Paid through GST Cash Ledger</th>
                                <th>Balance to be paid in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

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
                <form class="form-horizontal" method="POST" action="{{ route('gst.payable') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="form1" />
                    <div class="form-group">
                        <label for="form1_cgst" class="col-md-4 control-label">CGST</label>

                        <div class="col-md-6">
                            <input id="form1_cgst" type="text" class="form-control" name="cgst">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="form1_sgst" class="col-md-4 control-label">SGST</label>

                        <div class="col-md-6">
                            <input id="form1_sgst" type="text" class="form-control" name="sgst">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_igst" class="col-md-4 control-label">IGST</label>

                        <div class="col-md-6">
                            <input id="form1_igst" type="text" class="form-control" name="igst">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_cess" class="col-md-4 control-label">CESS</label>

                        <div class="col-md-6">
                            <input id="form1_cess" type="text" class="form-control" name="cess">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form1_date" class="col-md-4 control-label">Date</label>

                        <div class="col-md-6">
                            <input id="form1_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" required>
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

<div class="modal" id="form2_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Make Payment</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('gst.to.be.paid.in.cash') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="form2" />
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>IGST</th>
                                <th>CESS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>Late Fees</th>
                                <td><input type="text" class="form-control" name="cgst_late_fees"/></td>
                                <td><input type="text" class="form-control" name="sgst_late_fees"/></td>
                                <td><input type="text" class="form-control" name="igst_late_fees"/></td>
                                <td><input type="text" class="form-control" name="cess_late_fees"/></td>
                            </tr>
                            <tr>
                                <th>Interest</th>
                                <td><input type="text" class="form-control" name="cgst_interest"/></td>
                                <td><input type="text" class="form-control" name="sgst_interest"/></td>
                                <td><input type="text" class="form-control" name="igst_interest"/></td>
                                <td><input type="text" class="form-control" name="cess_interest"/></td>
                            </tr>
                            <tr>
                                <th>Penalty</th>
                                <td><input type="text" class="form-control" name="cgst_penalty"/></td>
                                <td><input type="text" class="form-control" name="sgst_penalty"/></td>
                                <td><input type="text" class="form-control" name="igst_penalty"/></td>
                                <td><input type="text" class="form-control" name="cess_penalty"/></td>
                            </tr>
                            <tr>
                                <th>Others</th>
                                <td><input type="text" class="form-control" name="cgst_others"/></td>
                                <td><input type="text" class="form-control" name="sgst_others"/></td>
                                <td><input type="text" class="form-control" name="igst_others"/></td>
                                <td><input type="text" class="form-control" name="cess_others"/></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="form-group">
                        <label for="form2_date" class="col-md-4 control-label">Date</label>

                        <div class="col-md-6">
                            <input id="form2_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" required>
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
        $("#form1_make_payment").on("click", function () {
            $("#form1_modal").modal("show");
        });

        $("#form2_make_payment").on("click", function () {
            $("#form2_modal").modal("show");
        });
    </script>
@endsection
