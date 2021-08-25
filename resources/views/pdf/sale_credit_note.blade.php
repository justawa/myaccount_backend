<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Note Cum Delivery</title>
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
               <h3 class="mr0">NOTE CUM DELIVERY</h3>
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
               <h4 style="font-size: 16px;">{{ $user_profile->name }}</h4>
               <h5>{{ $user_profile->billing_address . ' ' . $user_profile->billing_city }}</h5>
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
                    <td colspan="2">{{ \Carbon\Carbon::parse($credit_notes->first()->note_date)->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td colspan="4">Reference No and Date</td>
                    
                </tr>
                <tr>
                   <td colspan="4">{{ $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix . " --- " . \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</td>
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
                @if(count($credit_notes) > 0)
                @foreach($credit_notes as $credit_note)
                <tr>
                    <td colspan="1">{{ $loop->iteration }}</td>
                    <td colspan="5">{{ $credit_note->item_name }}</td>
                    <td colspan="2">{{ $credit_note->price }}</td>
                    <td colspan="2">{{ $credit_note->quantity }}</td>
                    <td colspan="2">{{ $credit_note->gst }}</td>
                </tr>
                @php
                  $price = $credit_note->price ?? 0;
                  $gst = $credit_note->gst ?? 0;
                  $qty = $credit_note->quantity ?? 0;
                  // $discount = $credit_note->discount ?? 0;
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
                    {{-- <td colspan="12"> --}}
                        <tr>
                            <td rowspan="2">HSN / SAC</td>
                            <td rowspan="2">Rate</td>
                            <td rowspan="2">Taxable Value</td>
                            @if ( $user_profile->place_of_business == $party->business_place ) 
                                <td colspan="2">CGST</td>
                                <td colspan="2">SGST</td>
                            @else
                                <td colspan="4" style="vertical-align: text-top;">IGST</td>
                            @endif
                            <td rowspan="2">Total</td>
                        </tr>
                        <tr>
                            @if ( $user_profile->place_of_business == $party->business_place ) 
                            <td >
                                    <p>%</p>
                            </td>
                            <td>
                                    <p>Amt</p>
                            </td>
                            <td >
                                    <p>%</p>
                            </td>
                            <td>
                                    <p>Amt</p>
                            </td>
                            @else
                                <td colspan="2">
                                    <p>%</p>
                                </td>
                                <td colspan="2">
                                    <p>Amt</p>
                                </td>
                            @endif
                        </tr>
                        @php
                            $total_taxable_amount = 0;
                            $total_cgst_amount = 0;
                            $total_sgst_amount = 0;
                            $total_taxed_amount = 0;
                            $total_igst_amount = 0;
                        @endphp

                        @foreach($hsn_data as $value)
                            @foreach($value as $hsn)
                            <tr>
                                <td>{{ $hsn['code'] }}</td>
                                <td>{{ $hsn['rate'] }}</td>
                                <td>{{ number_format((float)$hsn['taxable_value'], 2, '.', '') }}</td>
                                @if ( $user_profile->place_of_business == $party->business_place )
                                <td>{{ $hsn['rate']/2 }}</td>
                                <td>{{ number_format((float)$hsn['gst_amount']/2, 2, '.', '') }}</td>
                                <td>{{ $hsn['rate']/2 }}</td>
                                <td>{{ number_format((float)$hsn['gst_amount']/2, 2, '.', '') }}</td>
                                @else
                                <td colspan="2">{{ $hsn['rate'] }}</td>
                                <td colspan="2">{{ number_format((float)$hsn['gst_amount'], 2, '.', '') }}</td>
                                @endif
                                @php $total_taxable_amount = $hsn['taxable_value']+$hsn['gst_amount'] @endphp
                                <td>{{ number_format((float)$total_taxable_amount, 2, '.', '') }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    {{-- </td> --}}
                </tr>
                <tr>
                    <td colspan="6" rowspan="3" style="vertical-align: bottom;"></td>
                    <td colspan="6" rowspan="3" style="vertical-align: bottom;">
                        <p style="width:60%; float:left; text-align:right;"><strong>Reason:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $credit_notes->first()->reason }}</p>
                        <p style="width:60%; float:left; text-align:right;"><strong>Item Value:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $items_price }}</p>
                        <p style="width:60%; float:left; text-align:right;"><strong>GST:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $items_gst }}</p>
                        <p style="width:60%; float:left; text-align:right;"><strong>Note Value:</strong></p>
                        <p style="width:40%; float:left; text-align:right;">{{ $items_total }}</p>

                        <div style="clear:both;"></div>
                        <p style="text-align: right; margin-top: 50px;">For {{ $user_profile->name }}</p>
                        <p style="text-align: right;">Authorised Signature</p>
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