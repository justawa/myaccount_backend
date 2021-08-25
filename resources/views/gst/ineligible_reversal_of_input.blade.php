@extends('layouts.dashboard')

@section('content')

<div class="container">
    {{-- <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <form class="form-horizontal" method="get">
                <div class="form-group">
                    <div class="col-md-5">
                        @if( app('request')->input('month') && app('request')->input('year') )
                            @php
                                $checkmonth = \Carbon\Carbon::createFromFormat('m', app('request')->input('month'))->format('m');
                                $checkyear = \Carbon\Carbon::createFromFormat('Y', app('request')->input('year'))->format('Y');
                            @endphp
                        @else
                            @php
                                $checkmonth = \Carbon\Carbon::now()->format('m');
                                $checkyear = \Carbon\Carbon::now()->format('Y');
                            @endphp
                        @endif
                        <select class="form-control" name="month">
                            <option @if( $checkmonth == "01" ) selected="selected" @endif value="01">January</option>
                            <option @if( $checkmonth == "02" ) selected="selected" @endif value="02">February</option>
                            <option @if( $checkmonth == "03" ) selected="selected" @endif value="03">March</option>
                            <option @if( $checkmonth == "04" ) selected="selected" @endif value="04">April</option>
                            <option @if( $checkmonth == "05" ) selected="selected" @endif value="05">May</option>
                            <option @if( $checkmonth == "06" ) selected="selected" @endif value="06">June</option>
                            <option @if( $checkmonth == "07" ) selected="selected" @endif value="07">July</option>
                            <option @if( $checkmonth == "08" ) selected="selected" @endif value="08">August</option>
                            <option @if( $checkmonth == "09" ) selected="selected" @endif value="09">September</option>
                            <option @if( $checkmonth == "10" ) selected="selected" @endif value="10">October</option>
                            <option @if( $checkmonth == "11" ) selected="selected" @endif value="11">November</option>
                            <option @if( $checkmonth == "12" ) selected="selected" @endif value="12">December</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" name="year">
                            <option @if( $checkyear == "2017" ) selected="selected" @endif value="2017">2017</option>
                            <option @if( $checkyear == "2018" ) selected="selected" @endif value="2018">2018</option>
                            <option @if( $checkyear == "2019" ) selected="selected" @endif value="2019">2019</option>
                            <option @if( $checkyear == "2020" ) selected="selected" @endif value="2020">2020</option>
                            <option @if( $checkyear == "2021" ) selected="selected" @endif value="2021">2021</option>
                            <option @if( $checkyear == "2022" ) selected="selected" @endif value="2022">2022</option>
                            <option @if( $checkyear == "2023" ) selected="selected" @endif value="2023">2023</option>
                            <option @if( $checkyear == "2024" ) selected="selected" @endif value="2024">2024</option>
                            <option @if( $checkyear == "2025" ) selected="selected" @endif value="2025">2025</option>
                            <option @if( $checkyear == "2026" ) selected="selected" @endif value="2026">2026</option>
                            <option @if( $checkyear == "2027" ) selected="selected" @endif value="2027">2027</option>
                            <option @if( $checkyear == "2028" ) selected="selected" @endif value="2028">2028</option>
                            <option @if( $checkyear == "2029" ) selected="selected" @endif value="2029">2029</option>
                            <option @if( $checkyear == "2030" ) selected="selected" @endif value="2030">2030</option>
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
                            Ineligible/Reversal of Input
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ineligible/Reversal of Input</th>
                                <th colspan="2">Timeframe</th>
                                <th>
                                    @if( app('request')->input('from_date') && app('request')->input('to_date') )
                                        @php
                                            $from_date = app('request')->input('from_date');
                                            $to_date = app('request')->input('to_date');
                                        @endphp
                                    @else
                                        @php
                                            $from_date = \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)->format('d/m/Y');
                                            $to_date = \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to)->format('d/m/Y');
                                        @endphp
                                    @endif

                                    <span id="data_from_date">{{ $from_date }}</span> - <span id="data_to_date">{{ $to_date }}</span>
                                </th>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <th>ITC(Input Ledger Bal)</th>
                                <th>To be(-) Reverse</th>
                                <th>To be(-) Ineligible</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form method="POST" action="{{ route('post.gst.reversal.of.input') }}">
                                {{ csrf_field() }}
                                <tr>
                                    <th>CGST</th>
                                    <td><input type="text" class="form-control" name="itc_cgst" readonly value="{{ old('itc_cgst') ?? $cgst }}" /></td>
                                    <td><input type="text" class="form-control" name="reverse_cgst" id="reverse_cgst" value="{{ old('reverse_cgst') ?? '0' }}" ></td>
                                    <td><input type="text" class="form-control" name="ineligible_cgst" id="ineligible_cgst" value="{{ old('ineligible_cgst') ?? '0' }}" ></td>
                                </tr>
                                <tr>
                                    <th>SGST</th>
                                    <td><input type="text" class="form-control" name="itc_sgst" readonly value="{{ old('itc_sgst') ?? $sgst }}" /></td>
                                    <td><input type="text" class="form-control" name="reverse_sgst" id="reverse_sgst" value="{{ old('reverse_sgst') ?? '0' }}" ></td>
                                    <td><input type="text" class="form-control" name="ineligible_sgst" id="ineligible_sgst" value="{{ old('ineligible_sgst') ?? '0' }}"></td>
                                </tr>
                                <tr>
                                    <th>IGST</th>
                                    <td><input type="text" class="form-control" name="itc_igst" readonly value="{{ old('itc_igst') ?? $igst }}" /></td>
                                    <td><input type="text" class="form-control" name="reverse_igst" id="reverse_igst" value="{{ old('reverse_igst') ?? '0' }}" ></td>
                                    <td><input type="text" class="form-control" name="ineligible_igst" id="ineligible_igst" value="{{ old('ineligible_igst') ?? '0' }}" ></td>
                                </tr>
                                <tr>
                                    <th>CESS</th>
                                    <td><input type="text" class="form-control" name="itc_cess" readonly value="{{ old('itc_cess') ?? $cess }}" /></td>
                                    <td><input type="text" class="form-control" name="reverse_cess" id="reverse_cess" value="{{ old('reverse_cess') ?? '0' }}" ></td>
                                    <td><input type="text" class="form-control" name="ineligible_cess" id="ineligible_cess" value="{{ old('ineligible_cess') ?? '0' }}" ></td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td><input type="text" class="form-control" name="itc_total" readonly value="{{ old('itc_total') ?? $cgst + $sgst + $igst + $cess }}" ></td>
                                    <td><input type="text" class="form-control" name="reverse_total" id="reverse_total" readonly value="{{ old('reverse_total') ?? '0' }}" ></td>
                                    <td><input type="text" class="form-control" name="ineligible_total" id="ineligible_total" readonly value="{{ old('ineligible_total') ?? '0' }}" ></td>
                                </tr>
                                {{-- <tr>
                                    <th>Narration</th>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr> --}}
                                <tr>
                                    <td colspan="2">
                                        <textarea class="form-control" rows="1" name="narration" placeholder="Narration"></textarea>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="submit_date" />
                                        <p style="color: red; font-size: 11px" id="date_error"></p>
                                    </td>
                                    <td><button type="submit" class="btn btn-success btn-block" id="save_ineligible">Save</button></td>
                                </tr>
                            </form>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#reverse_cgst").on("keyup", function() {
                calculate_reverse_side();
            });

            $("#reverse_sgst").on("keyup", function() {
                calculate_reverse_side();
            });

            $("#reverse_igst").on("keyup", function() {
                calculate_reverse_side();
            });

            $("#reverse_cess").on("keyup", function() {
                calculate_reverse_side();
            });

            //----------------------------------------

            $("#ineligible_cgst").on("keyup", function() {
                calculate_ineligible_side();
            });

            $("#ineligible_sgst").on("keyup", function() {
                calculate_ineligible_side();
            });

            $("#ineligible_igst").on("keyup", function() {
                calculate_ineligible_side();
            });

            $("#ineligible_cess").on("keyup", function() {
                calculate_ineligible_side();
            });
        });

        function calculate_reverse_side()
        {
            var reverse_cgst = $("#reverse_cgst").val();
            var reverse_sgst = $("#reverse_sgst").val();
            var reverse_igst = $("#reverse_igst").val();
            var reverse_cess = $("#reverse_cess").val();

            if(reverse_cgst == ''){
                reverse_cgst = 0;
            }

            if(reverse_sgst == ''){
                reverse_sgst = 0;
            }

            if(reverse_igst == ''){
                reverse_igst = 0;
            }

            if(reverse_cess == ''){
                reverse_cess = 0;
            }

            var reverse_total = parseFloat(reverse_cgst) + parseFloat(reverse_sgst) + parseFloat(reverse_igst) + parseFloat(reverse_cess);

            $("#reverse_total").val(reverse_total);
        }

        function calculate_ineligible_side()
        {
            var ineligible_cgst = $("#ineligible_cgst").val();
            var ineligible_sgst = $("#ineligible_sgst").val();
            var ineligible_igst = $("#ineligible_igst").val();
            var ineligible_cess = $("#ineligible_cess").val();

            if(ineligible_cgst == ''){
                ineligible_cgst = 0;
            }

            if(ineligible_sgst == ''){
                ineligible_sgst = 0;
            }

            if(ineligible_igst == ''){
                ineligible_igst = 0;
            }

            if(ineligible_cess == ''){
                ineligible_cess = 0;
            }

            var ineligible_total = parseFloat(ineligible_cgst) + parseFloat(ineligible_sgst) + parseFloat(ineligible_igst) + parseFloat(ineligible_cess);

            $("#ineligible_total").val(ineligible_total);
        }

        $('input[name="submit_date"]').on("keyup", function() {
            const from_date = $("#data_from_date").text();
            const to_date = $("#data_to_date").text();
            const date_to_check = $(this).val();
            $("#save_ineligible").attr('disabled', false);
            $("#date_error").html("");

            if(date_to_check.length > 6){
                const isValidDate = checkIFDateIsBetweenTwoProvidedDates(from_date, to_date, date_to_check);
                if(!isValidDate){
                    $("#save_ineligible").attr('disabled', true);
                    $("#date_error").html("Please provide date between selected range");
                }
            }
        });

        function checkIFDateIsBetweenTwoProvidedDates(from_date, to_date, date_to_check) { 
            const D_1 = from_date.split("/"); 
            const D_2 = to_date.split("/"); 
            const D_3 = date_to_check.split("/");

            var d1 = new Date(D_1[2], parseInt(D_1[1]) - 1, D_1[0]); 
            var d2 = new Date(D_2[2], parseInt(D_2[1]) - 1, D_2[0]); 
            var d3 = new Date(D_3[2], parseInt(D_3[1]) - 1, D_3[0]); 

            // console.log("1", d1)
            // console.log("2", d2)
            // console.log("3", d3);
              
            if (d3 >= d1 && d3 <= d2) { 
                return true;
            }

            return false;
        }
    </script>
@endsection
