<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Sale Debit Note</title>
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
            <div class="colom-4 text-left">GST No: <span class=""><strong style="font-size: 16px">{{ $user_profile->gst }}</strong></span></div>
            <div class="colom-4 text-center">
               <h3 class="mr0">SALE DEBIT NOTE</h3>
            </div>
            <div class="colom-4 text-right">Contact No: <span class=""><strong>{{ $user_profile->phone }}</strong></span></div>
         </div>
         <div class="main">
            <div class="colom-4 text-left">
                @if($user_profile->logo)
                <img style="height: 80px;" src="{{ asset('storage/'.$user_profile->logo) }}" />
                @else
                <img style="height: 80px;" src="{{ asset('images/white.jpg') }}" />
                @endif
            </div>
            <div class="colom-4 text-center">
               <h4><span>{{ $user_profile->name }}</span></h4>
               <h5><span>{{ $user_profile->billing_address . ' ' . $user_profile->billing_city }}</span></h5>
            </div>
            <div class="colom-4 text-right"></div>
         </div>
        <!-- Simple Invoice - START -->
        <table class="table table-condensed" cellspacing="0">
            <tbody>
                <tr>
                    <th colspan="4"><strong>Bill To</strong></th>
                    <th colspan="4"><strong>Ship To</strong></th>
                    <th colspan="2"><strong>Note No</strong></th>
                    <th colspan="2"><strong>{{ $note_no }}</strong></th>
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
                    <td colspan="2">{{ \Carbon\Carbon::parse($debit_notes->first()->note_date)->format('d M, Y') }}</td>
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
                    <th colspan="1">#</th>
                    <th colspan="5">Item</th>
                    <th colspan="2">Price</th>
                    <th colspan="2">Qty</th>
                    <th colspan="2">GST</th>
                </tr>
                @php $count = 1; $items_price = 0; $items_gst = 0; $items_discount = 0; $items_total = 0;  @endphp
                @if(count($debit_notes) > 0)
                @foreach($debit_notes as $debit_note)
                <tr>
                    <td colspan="1">{{ $loop->iteration }}</td>
                    <td colspan="5">{{ $debit_note->item_name }}</td>
                    <td colspan="2">{{ $debit_note->price }}</td>
                    <td colspan="2">{{ $debit_note->quantity }}</td>
                    <td colspan="2">{{ $debit_note->gst }}</td>
                </tr>
                @php
                  $price = $debit_note->price ?? 0;
                  $gst = $debit_note->gst ?? 0;
                  $qty = $debit_note->quantity ?? 0;
                  // $discount = $debit_note->discount ?? 0;
                  if(auth()->user()->profile->inventory_type == "without_inventory"){
                      $items_price += $price;
                  }else{ 
                      $items_price += $price * $qty;
                  }
                  // $items_discount += $discount;
                  $items_gst += $gst;
                  // $items_total += ((($price * $qty) - $discount)  + $gst);
                  $items_total = ($items_price + $gst);
                @endphp
                @endforeach
                @endif
                <tr>
                    {{-- <td colspan="6" rowspan="3" style="vertical-align: bottom;"></td> --}}
                    <td colspan="12" rowspan="3" style="vertical-align: bottom;">
                        <p style="width:60%; float:left; text-align:right;"><strong>Reason:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $debit_notes->first()->reason }}</p>
                        <p style="width:60%; float:left; text-align:right;"><strong>Item Value:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $items_price }}</p>
                        <p style="width:60%; float:left; text-align:right;"><strong>GST:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $items_gst }}</p>
                        <p style="width:60%; float:left; text-align:right;"><strong>Note Value:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $items_total }}</p>
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