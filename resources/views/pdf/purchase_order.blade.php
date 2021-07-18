<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Purchase Order</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        p {
            margin: 5px 0;
        }

        .mr0 {
            margin: 0;
        }

        .mr-top {
            margin-top: 10px;
        }

        .mr-bottom {
            margin-bottom: 10px;
        }

        .main {
            width: 100%;
            float: left;
            font-family: sans-serif;
            font-size: 12px;
        }

        h3 {
            font-size: 20px;
        }

        .colom-4 {
            width: 33%;
            float: left;
        }

        .table {
            width: 100%;
            border: 1px solid #ddd;
            font-family: sans-serif;
            font-size: 12px;
            border-collapse: collapse;
        }

        .table-condensed>tbody>tr>td,
        .table-condensed>tbody>tr>th,
        .table-condensed>tfoot>tr>td,
        .table-condensed>tfoot>tr>th,
        .table-condensed>thead>tr>td,
        .table-condensed>thead>tr>th {
            padding: 5px 10px;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            border: 1px solid #ddd;
        }

        .table>thead>tr>th {
            padding: 10px;
        }

        #page-wrap {
            width: 800px;
            margin: 0 auto;
        }

        .panel-body {
            padding: 0px;
        }

        .height {
            min-height: 200px;
        }

        .icon {
            font-size: 47px;
            color: #5CB85C;
        }

        .iconbig {
            font-size: 77px;
            color: #5CB85C;
        }

        .table>tbody>tr>.emptyrow {
            border-top: none;
        }

        .table>thead>tr>.emptyrow {
            border-bottom: none;
        }

        .table>tbody>tr>.highrow {
            border-top: 3px solid;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .buyer-sign {
            text-align: right;
            margin-top: 50px;
        }

        .term {
            list-style: decimal;
            padding-left: 15px;
        }

        .term li {
            margin: 5px 0;
        }
    </style>

    <link rel="stylesheet" type="text/css" href="{{ asset('css/invoice-print.css') }}" />
</head>

<body>
    <div id="page-wrap">
        <div class="main mr-bottom">
            <div class="colom-4 text-left">GST No: <span class="">{{ $user_profile->gst }}</span></div>
            <div class="colom-4 text-center">
                <h3 class="mr0">PURCHASE ORDER</h3>
            </div>
            <div class="colom-4 text-right">Contact No: <span class="">{{ $user_profile->phone }}</span></div>
        </div>
        <div class="main">
            <div class="colom-4 text-left"><img style="height: 80px;" src="{{ asset('storage/'.$user_profile->logo) }}" /></div>
            <div class="colom-4 text-left">
                <p>Co. Name <span>{{ $user_profile->name }}</span></p>
                <p>Address <span>{{ $user_profile->communication_address . ' ' . $user_profile->communication_city }}</span></p>
            </div>
            <div class="colom-4 text-right">Email: <span class=""> {{ auth()->user()->email }}</span></div>
        </div>
        <!-- Simple Invoice - START -->
        <table class="table table-condensed" cellspacing="0">
            <tbody>
                <tr>
                    <th colspan="4"><strong>Bill To</strong></th>
                    <th colspan="4"><strong>Ship To</strong></th>
                    <th colspan="2"><strong>Quotation No</strong></th>
                    <th colspan="2"><strong>{{ $records->first()->token }}</strong></th>
                </tr>
                <tr>
                    <td rowspan="8" colspan="4" style="vertical-align: text-top;">
                        <p>Party Name {{ $party->name }}</p>
                        <p>GST NO. {{ $party->gst }}</p>
                        <p>Bill to {{ $party->billing_address . ' ' . $party->billing_city }}</p>
                    </td>
                    <td rowspan="8" colspan="4" style="vertical-align: text-top;">
                    {{ $party->shipping_address . ' ' . $party->shipping_city }}
                    </td>
                    <td colspan="2">Date </td>
                    <td colspan="2">{{ \Carbon\Carbon::parse($records->first()->date)->format('d M, Y') }}</td>
                </tr>
                <tr>
                    
                </tr>
                <tr>
                   
                </tr>
                <tr>
                    
                </tr>
                <tr>
                    
                </tr>
                <tr>
                    
                </tr>
                <tr>
                   
                </tr>
                <tr>
                    
                </tr>
                <tr rowspan="1">
                    <th colspan="2">#</th>
                    <th colspan="4">Item</th>
                    <th colspan="2">Quantity</th>
                    <th colspan="2">Rate</th>
                    <th colspan="2">Amount</th>
                </tr>
                @php
                    // $count = 1;
                    $total_amount = 0;
                    $total_discount = 0;
                    $total_cgst = 0;
                    $total_sgst = 0;
                    $total_igst = 0;
                    $total_cess = 0;
                @endphp
                @foreach($records as $record)
                <tr>
                    <td colspan="2">{{ $loop->iteration }}</td>
                    <td colspan="4">{{ $record->item_name }}</td>
                    <td colspan="2">{{ $record->qty }}</td>
                    <td colspan="2">{{ $record->rate }}</td>
                    @php
                        $this_amount = $record->qty * $record->rate;
                        //$amount += $this_amount;
                    @endphp
                    <td>{{ number_format((float)$this_amount, 2, '.', '') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="6" rowspan="3" style="vertical-align: bottom;"></td>
                    <td colspan="6" rowspan="3" style="vertical-align: bottom;">
                        <p style="width:80%; float:left; text-align:right;">Total Amount</p>
                        <p style="width:20%; float:left; text-align:right;">{{ $records->first()->total_amount }}</p>
                        <p style="width:80%; float:left; text-align:right;">Amount Received</p>
                        <p style="width:20%; float:left; text-align:right;">{{ $records->first()->amount_received }}</p>
                        <p style="width:80%; float:left; text-align:right;">Amount Remaining</p>
                        <p style="width:20%; float:left; text-align:right;">{{ $records->first()->amount_remaining }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Simple Invoice - END -->
    </div>

    <!--<div class="text-center">-->
    <!--    <button id="print_section" type="button" class="btn btn-link">Print Invoice</button>-->
    <!--</div>-->

  <!--  <script-->
  <!--src="https://code.jquery.com/jquery-3.4.1.min.js"-->
  <!--integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="-->
  <!--crossorigin="anonymous"></script>-->
  <!--  <script src="{{ asset('js/printThis/printThis.js') }}"></script>-->
  <!--  <script>-->
  <!--      $('#print_section').on("click", function () {-->
  <!--          $('#page-wrap').printThis();-->
  <!--      });-->
  <!--  </script>-->
</body>

</html>