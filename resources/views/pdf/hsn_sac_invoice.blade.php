<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>Invoice </title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <style>
         p { margin: 5px 0;font-family: sans-serif;font-size: 12px;}
         .mr0{margin:0;}
         .mr-top{margin-top:10px;}
         .mr-bottom{margin-bottom:10px;}
         .main{width:100%;float:left; font-family: sans-serif;font-size: 12px;}
         h3{font-size: 20px;}
         .colom-4{
         width: 33%;
         float:left;
         }
         .table{width: 100%;
         border: 1px solid #ddd;font-family: sans-serif;font-size: 12px; border-collapse: collapse;
         }
         .table-condensed>tbody>tr>td, .table-condensed>tbody>tr>th, .table-condensed>tfoot>tr>td, .table-condensed>tfoot>tr>th, .table-condensed>thead>tr>td, .table-condensed>thead>tr>th {
         padding:5px 10px;
         }
         .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
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
         .table > tbody > tr > .emptyrow {
         border-top: none;
         }
         .table > thead > tr > .emptyrow {
         border-bottom: none;
         }
         .table > tbody > tr > .highrow {
         border-top: 3px solid;
         }
         .text-center{text-align:center;}
         .text-right{text-align:right;}
         .buyer-sign {
         text-align: right;
         margin-top: 50px;
         }
         .term{list-style: decimal;
         padding-left: 15px;}
         .term li{    margin: 5px 0;}
      </style>

      <link rel="stylesheet" type="text/css" href="{{ asset('css/invoice-print.css') }}" />
   </head>
   <body>
      <div id="page-wrap">
         <div class="main mr-bottom">
            <div class="colom-4 text-left"><span style="font-size: 16px;">GST No:</span> <span class=""><strong style="font-size: 16px">{{ $user_profile->gst }}</strong></span></div>
            <div class="colom-4 text-center">
               <h3 class="mr0">
                   {{ $user_profile->registered == 3 ? 'BILL OF SUPPLY' : 'TAX INVOICE' }}
                   @if($user_profile->registered == 3)
                    <strong>composition taxable person not eligible to collect tax on supplies</strong>
                   @endif
                </h3>
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
            <thead>
                <tr>
                    <th colspan="4">Bill To</th>
                    <th colspan="4">Ship To</th>
                    <th colspan="2">
                        Invoice No
                        <p>{{ $invoice->invoice_prefix }} {{ $invoice->invoice_no }} {{ $invoice->invoice_suffix }}</p>
                    </th>
                    <th colspan="2">
                        Invoice Date
                        <p>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</p>
                    </th>
                </tr>
                <tr>
                    <td rowspan="2" colspan="4" style="vertical-align: text-top;">
                        <p>Party Name {{ $party->name }}</p>
                        <p>GST NO. {{ $party->gst }}</p>
                        <p>Bill to {{ $party->billing_address . ' ' . $party->billing_city }}</p>
                    </td>
                    <td rowspan="2" colspan="4" style="vertical-align: text-top;">
                        {{ $party->shipping_address . ' ' . $party->shipping_city }}
                    </td>
                    <td colspan="2">
                        Buyer Name
                        <p>{{ $invoice->buyer_name ?? '-' }}</p>
                    </td>
                    <td colspan="2">
                        Reference Name
                        <p>{{ $invoice->reference_name ?? '-' }}</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        E-way Bill No
                        <p>{{ $invoice->regularEWayBill() ? $invoice->regularEWayBill()->bill_no : '-' }}</p>
                    </td>
                    <td colspan="2">
                        Order No
                        <p>{{ $invoice->order_no ?? '-' }}</p>
                    </td>
                </tr>
            </thead>
            <tbody>
               <tr rowspan="1">
                  <td >S No</td>
                  <td colspan="2">Description</td>
                  <td colspan="2">HSN / SAC</td>
                  <td colspan="2">QTY</td>
                  <td colspan="2">Rate</td>
                  <td>Discount</td>
                  <td>Amount</td>
               </tr>
                @php
                    // $count = 1;
                    $total_amount = 0;
                    $total_discount = 0;
                    $total_cgst = 0;
                    $total_sgst = 0;
                    $total_igst = 0;
                    $total_cess = 0;
                    $total_qty_bought = 0;
                @endphp
               @foreach($invoice_items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td colspan="2">{{ $item->info->name }}</td>
                    <td colspan="2">
                        @if( $item->info->hsc_code != null )
                            {{ $item->info->hsc_code ?? "-" }}
                        @endif

                        @if( $item->info->sac_code != null )
                            {{ $item->info->sac_code ?? "-" }}
                        @endif
                    </td>
                    <td>
                        @if($item->qty_type == "base")
                            {{ $item->item_qty }}
                            @php $total_qty_bought += $item->item_qty @endphp
                        @elseif($item->qty_type == "alternate")
                            {{ $item->item_alt_qty }}
                            @php $total_qty_bought += $item->item_alt_qty @endphp
                        @else
                            {{ $item->item_comp_qty }}
                            @php $total_qty_bought += $item->item_comp_qty @endphp
                        @endif
                    </td>
                    <td>{{ $item->item_measuring_unit }}</td>

                    @php //$total_amount += $item->item_price;
                    @endphp
                    <td colspan="2">{{ $item->item_price }}</td>
                    
                    @php //$total_discount += $item->discount;
                    @endphp
                    <td>{{ $item->discount }}</td>
                    @php $gst = $item->gst + $item->rcm_gst; @endphp
                    @if ( $user_profile->place_of_business == $party->business_place )
                        @php
                            $total_cgst += $gst/2;
                            $total_sgst += $gst/2;
                        @endphp
                    @else
                        @php
                            $total_igst += $gst;
                        @endphp
                    @endif

                    @php $total_cess += $item->cess; @endphp
                    @if( $item->item_tax_type == 'inclusive_of_tax' )
                        @php $first_part = $item->item_price; @endphp
                        @php $second_part = $item->item_price * ( 100 / ( 100 + $item->info->gst ) ); @endphp

                        @php $thisCalculatedGstAmount = ($first_part - $second_part) * $item->item_qty; @endphp

                        @php $this_amount = (($item->item_price * $item->item_qty) - $thisCalculatedGstAmount) - ($item->discount * $item->item_qty);  @endphp
                    @endif

                    @if( $item->item_tax_type == 'exclusive_of_tax' )
                        @php 
                            $this_amount = ($item->item_price * $item->item_qty) - ($item->discount * $item->item_qty); 

                            $thisCalculatedGstAmount = 0;
                        @endphp
                    @endif

                    @php 
                        $total_amount += number_format((float)$item->item_total, 2, '.', '');
                    @endphp

                    <td>{{ number_format((float)$item->item_total, 2, '.', '') }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="5"></td>
                    <td>{{ $total_qty_bought }}</td>
                    <td colspan="3"></td>
                </tr>
               <tr>
                  <td colspan="8" style="vertical-align: text-top;">
                     {{-- <p>Total in Words:</p> --}}
                     <p>Payment Mode: {{ ucwords(str_replace('+', ' ', str_replace('_', ' ', $invoice->type_of_payment))) }}</p>
                     <p>Cash: {{ $invoice->cash_payment }}</p>
                     <p>Bank: {{ $invoice->bank_payment }}</p>
                     <p>POS: {{ $invoice->pos_payment }}</p>
                     <p>Cash Discount: {{ $invoice->discount_payment }}</p>
                     <p>Paid: {{ $invoice->amount_paid }}</p>
                  </td>
                  <td colspan="6" rowspan="6" style="vertical-align: top;">
                    <p style="width:80%; float:left; text-align:right;">Total Amount Before Tax</p>
                        <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$total_amount, 2, '.', '') }}</p>
                        <p style="width:80%; float:left; text-align:right;"><strong>Add:</strong></p>
                        <p style="width:20%; float:left; text-align:right;"></p>
                        @if ( $user_profile->place_of_business == $party->business_place )
                            <p style="width:80%; float:left; text-align:right;">CGST</p>
                            <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$total_cgst, 2, '.', '') }}</p>
                            <p style="width:80%; float:left; text-align:right;">UTGST/SGST</p>
                            <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$total_sgst, 2, '.', '') }}</p>
                        @else
                            @if($user_profile->registered != 3)
                            <p style="width:80%; float:left; text-align:right;">IGST</p>
                            <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$total_igst, 2, '.', '') }}</p>
                            @endif
                        @endif
                        @if($user_profile->registered != 3)
                        <p style="width:80%; float:left; text-align:right;">CESS</p>
                        <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$invoice->cess, 2, '.', '') }}</p>
                        @endif
                        <p style="width:80%; float:left; text-align:right;">TCS</p>
                        <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$invoice->tcs, 2, '.', '') }}</p>
                        <p style="width:80%; float:left; text-align:right;">Total</p>
                        
                        <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$invoice->amount_before_round_off, 2, '.', '') }}</p>
                        <p style="width:80%; float:left; text-align:right;">Round Off</p>
                        
                        <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$invoice->round_offed, 2, '.', '') }}</p>
                
                        <p style="width:80%; float:left; text-align:right;">Grand Total</p>
                        <p style="width:20%; float:left; text-align:right;">{{ number_format((float)$invoice->total_amount, 2, '.', '') }}</p>

                        <p style="width:80%; float:left; text-align:left;"><strong>Amount in words:</strong></p>
                        <p style="width:80%; float:left; text-align:left;"><strong>{{ ucwords(getCurrencyInWords(number_format((float)$invoice->total_amount, 2, '.', ''))) }}</strong></p>
                  </td>
               </tr>
               <tr>
                    <td colspan="3" style="vertical-align: text-top;">
                        Bank Detail:
                    </td>
               {{-- </tr>
               <tr> --}}
                    <td colspan="5" style="vertical-align: text-top;">
                        {{ $user_profile->bank_information ?? "NA" }}
                    </td>
               </tr>
               <tr>
                  <td rowspan="2">HSN / SAC</td>
                  <td rowspan="2">Rate</td>
                  <td rowspan="2">Taxable Value</td>
                  @if ( $user_profile->place_of_business == $party->business_place ) 
                    <td colspan="2">CGST</td>
                    <td colspan="2">SGST</td>
                  @elseif($user_profile->registered != 3)
                    <td colspan="4" style="vertical-align: text-top;">IGST</td>
                  @endif
                  <td rowspan="2" @if($user_profile->registered == 3) colspan="5" @endif>Total</td>
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
                  @elseif($user_profile->registered != 3)
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
                        @elseif($user_profile->registered != 3)
                        <td colspan="2">{{ $hsn['rate'] }}</td>
                        <td colspan="2">{{ number_format((float)$hsn['gst_amount'], 2, '.', '') }}</td>
                        @endif
                        @php $total_taxable_amount = $hsn['taxable_value']+$hsn['gst_amount'] @endphp
                        <td @if($user_profile->registered == 3) colspan="5" @endif>{{ number_format((float)$total_taxable_amount, 2, '.', '') }}</td>
                    </tr>
                    @endforeach
                @endforeach
                {{-- @foreach($invoice_items as $item)
                @php $gst = $item->gst + $item->rcm_gst; @endphp
                <tr>
                    <td>
                        @if( $item->info->hsc_code != null )
                            {{ $item->info->hsc_code }}
                        @endif

                        @if( $item->info->sac_code != null )
                            {{ $item->info->sac_code }}
                        @endif
                    </td>

                    @php $this_item_amount = $item->item_total; @endphp

                    @php $total_taxable_amount += $this_item_amount @endphp

                    <td>{{ $item->info->gst }}</td>
                    <td>
                        {{ number_format((float)$this_item_amount, 2, '.', '') }}
                    </td>
                    
                    @if ( $user_profile->place_of_business == $party->business_place ) 
                        <td>
                            {{ $item->info->gst / 2 }}%
                        </td>

                        @php $this_cgst = $gst / 2; @endphp
                        
                        @php $total_cgst_amount += $this_cgst; @endphp
                        <td>
                            {{ number_format((float)$this_cgst, 2, '.', '') }}
                        </td>
                        <td>
                            {{ $item->info->gst / 2 }}%
                        </td>

                        @php $this_sgst = $gst / 2; @endphp

                        @php $total_sgst_amount += $this_sgst; @endphp
                        <td>
                            {{ number_format((float)$this_sgst, 2, '.', '') }}
                        </td>

                        @php $this_gst = $this_cgst + $this_sgst; $total_taxed_amount += $this_gst; @endphp
                    @elseif($user_profile->registered != 3)

                        <td colspan="2">
                            {{ $item->info->gst }}%
                        </td>

                        @php $this_igst = $gst @endphp

                        @php $total_igst_amount += $this_igst; @endphp
                        <td colspan="2">
                            {{ number_format((float)$this_igst, 2, '.', '') }}
                        </td>
                        @php $this_gst = $this_igst; $total_taxed_amount += $this_gst; @endphp

                    @endif

                    <td @if($user_profile->registered == 3) colspan="5" @endif>
                        {{ number_format((float)$this_gst, 2, '.', '') }}
                    </td>
                </tr>
                @endforeach --}}
               {{-- </tr> --}}
               <tr>
                  <td colspan="7">
                     <h4>Terms & Conditions:</h4>
                     <ul class="term">
                        @if($user_profile->show_terms && $user_profile->terms_and_condition)
                            <li>{{ $user_profile->terms_and_condition }}</li>
                        @else
                            <li>If the bill is not paid with in 30 days interest 24% will be charged from the date of bill.</li>
                            <li>In the event of any dispute of whatever nature Kathua court only will have jurisdiction.</li>
                            <li>Good once sold cannot be taken back.</li>
                        @endif
                     </ul>
                     <p class="buyer-sign" style="vertical-align: bottom;">Buyer Sign</p>
                  </td>
                  <td colspan="4" class="text-right" style="vertical-align: bottom;">
                    <p>For {{ $user_profile->name }}</p>
                    {{-- @if($user_profile->show_bank_info && $user_profile->bank_information)
                        <p>{{ $user_profile->bank_information }}</p>
                    @endif --}}
                    @if($user_profile->authorised_signature)
                        <img style="height: 120px;" src="{{ asset('storage/'.$user_profile->authorised_signature) }}" />
                    @endif
                    <p class="mr-top">Authorised Signature</p>
                  </td>
               </tr>
            </tbody>
         </table>
         <!-- Simple Invoice - END -->
      </div>
      <!--<div class="text-center">-->
      <!--    <button id="print_section" type="button" class="btn btn-link">Print Invoice</button>-->
      <!--</div>-->

  <!--    <script-->
  <!--src="https://code.jquery.com/jquery-3.4.1.min.js"-->
  <!--integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="-->
  <!--crossorigin="anonymous"></script>-->
  <!--    <script src="{{ asset('js/printThis/printThis.js') }}"></script>-->
  <!--    <script>-->
  <!--          $('#print_section').on("click", function () {-->
  <!--              $('#page-wrap').printThis();-->
  <!--          });-->
  <!--    </script>-->
   </body>
</html>