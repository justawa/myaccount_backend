<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        body {
            margin: 0px;
        }
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        a {
            color: #fff;
            text-decoration: none;
        }
        table {
            font-size: x-small;
        }
        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        .invoice table {
            margin: 15px;
        }
        .invoice h3 {
            margin-left: 15px;
        }
        .information {
            background-color: #F5F8FA;
            color: #3097D1;
        }
        .information .logo {
            margin: 5px;
        }
        .information table {
            padding: 10px;
        }

        .invoice table{
            border: 1px solid #ddd;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .invoice > table > thead > tr > th, .invoice > table > tbody > tr > td, .invoice > table > tfoot > tr > th, .invoice > table > tfoot > tr > td {
            border: 1px solid #ddd;
            vertical-align: bottom;
            padding: 8px;
            line-height: 1.6;
        }

        #terms_and_condition {
            font-size: 8px;
            white-space: pre-line;
        }
    </style>
</head>
<body>

    <div class="information">
        <table width="100%">
            <tr>
                <td align="left" style="width: 40%;">
                    @if($user_profile->format_of_invoice == 'gst invoice')
                        <h3>GST Invoice</h3>
                    @else
                        <h3>Bill of Supply</h3>
                    @endif
                    <pre>
Invoice Number: @if($invoice->invoice_prefix) {{ $invoice->invoice_prefix }} @endif {{ $invoice->invoice_no }} @if($invoice->invoice_suffix) {{ $invoice->invoice_suffix }} @endif
<br /><br />
Date: {{ Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
<br />
Billing Address: {{ $party->billing_address }}, {{ $party->billing_city }}, {{ $party->billing_state }} {{ $party->billing_pincode }}
</pre>
                </td>
                <td align="center">
                    <img src="{{ asset('storage/'.$user_profile->logo) }}" alt="Logo" width="64" class="logo"/>
                </td>
                <td align="right" style="width: 40%;">

                    <h3>{{ $party->name }}</h3>
                    @if($user_profile->invoice_heading == 'yes')
                    <p>Composition Dealer<p>
                    @endif
                    <pre>
                        Amount Paid: {{ $invoice->amount_paid }}
                        Amount Remaining: {{ $invoice->amount_remaining }}
                    </pre>
                    Shipping Address: {{ $party->shipping_address }}, {{ $party->shipping_city }}, {{ $party->shipping_state }} {{ $party->shipping_pincode }}
                </td>
            </tr>

        </table>
    </div>

    <br/>
    @if(count($invoice_items) > 0)
    <div class="invoice">
        <h3>Invoice Details</h3>
        <table width="100%">
            <thead>
            <tr align="center">
                <th>#</th>
                <th>Description</th>
                <th>HSN/SAC</th>
                @if( $user_profile->registered != 0 && $user_profile->registered != 3 )
                    <th>GST Rate</th>
                @endif
                <th>Quantity</th>
                <th>Rate</th>
                <th>Per</th>
                <th>Discount</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
                @php
                    $count = 1;
                    $amount = 0;
                    $total_gst = 0;
                @endphp
                @foreach($invoice_items as $item)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $item->info->name }}</td>
                    <td>
                        @if( $item->info->hsc_code != null )
                            {{ $item->info->hsc_code }}
                        @endif

                        @if( $item->info->sac_code != null )
                            {{ $item->info->sac_code }}
                        @endif
                    </td>
                    @if( $user_profile->registered != 0 && $user_profile->registered != 3 )
                        <td>{{ $item->info->gst }}</td>
                    @endif
                    <td>{{ $item->item_qty }}</td>
                    <td>{{ $item->item_price }}</td>
                    <td>{{ $item->info->measuring_unit }}</td>
                    <td>{{ $item->discount }}</td>

                    @if( $invoice->amount_type == 'inclusive' )
                        @php $first_part = $item->item_price; @endphp
                        @php $second_part = $item->item_price * ( 100 / ( 100 + $item->info->gst ) ); @endphp

                        @php $thisCalculatedGstAmount = ($first_part - $second_part) * $item->item_qty; @endphp

                        @php $this_amount = (($item->item_price * $item->item_qty) - $thisCalculatedGstAmount) - ($item->discount * $item->item_qty);  @endphp
                    @endif

                    @if( $invoice->amount_type == 'exclusive' )
                        @php 
                            $this_amount = ($item->item_price * $item->item_qty) - ($item->discount * $item->item_qty); 

                            $thisCalculatedGstAmount = 0; //no used in exclusive
                        @endphp
                    @endif

                    @php 
                        $amount += $this_amount;
                        $total_gst += $thisCalculatedGstAmount;
                    @endphp


                    <td>{{ number_format((float)$this_amount, 2, '.', '') }}</td>
                </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td @if($user_profile->registered == 0 || $user_profile->registered == 3) colspan="7" @else colspan="8" @endif>Total Taxable Value</td>
                    <td>{{ number_format((float)$amount, 2, '.', '') }}</td>
                </tr>

                @if( $user_profile->registered == 0 || $user_profile->registered == 3 )
                    @php $gst = 0; @endphp
                @else

                    @if ( $user_profile->billing_state == $party->billing_state )
                        <tr>
                            <td colspan="8">CGST</td>
                            
                            @php $cgst = $invoice->cgst @endphp
                            
                            <td>{{ number_format((float)$cgst, 2, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td colspan="8">SGST</td>
                            
                            @php $sgst = $invoice->sgst @endphp
                            
                            <td>{{ number_format((float)$sgst, 2, '.', '') }}</td>
                        </tr>
                        @php $gst = $cgst + $sgst; @endphp
                    {{-- @elseif( ( $party->billing_state == '4' || $party->billing_state == '7' || $party->billing_state == '25' || $party->billing_state == '26' || $party->billing_state == '31' || $party->billing_state == '34' || $party->billing_state == '35' ) && $user_profile->billing_state != $party->billing_state )
                        <tr>
                            <td colspan="8">UGST</td>
                            
                            @php $ugst = $invoice->ugst; @endphp
                            
                            <td>{{ number_format((float)$ugst, 2, '.', '') }}</td>
                        </tr>

                        @php $gst = $ugst @endphp --}}
                    @else
                        <tr>
                            <td colspan="8">IGST</td>
                            
                            @php $igst = $invoice->igst; @endphp
                            
                            <td>{{ number_format((float)$igst, 2, '.', '') }}</td>
                        </tr>

                        @php $gst = $igst @endphp
                    @endif
                    
                @endif

                <tr>
                    <td @if($user_profile->registered == 0 || $user_profile->registered == 3) colspan="7" @else colspan="8" @endif>Total Invoice Value</td>
                    <td>{{ number_format((float)$amount + $gst, 2, '.', '') }}</td>
                </tr>
                @if($invoice->gst_classification == 'rcm')
                <tr>
                    <td colspan="8">Invoice under RCM</td>
                </tr>
                @endif
            </tfoot>
        </table>

        @if( $user_profile->registered != 0 && $user_profile->registered != 3 )
            <table width="100%">
                @if ( $user_profile->billing_state == $party->billing_state )
                    <thead>
                        <tr align="center">
                            <th rowspan="2">HSN/SAC</th>
                            <th rowspan="2">Taxable Value</th>
                            <th colspan="2">Central Tax</th>
                            <th colspan="2">State Tax</th>
                            <th rowspan="2">Total Tax Amount</th>
                        </tr>
                        <tr align="center">
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_taxable_amount = 0;
                            $total_cgst_amount = 0;
                            $total_sgst_amount = 0;
                            $total_taxed_amount = 0;
                        @endphp
                        @foreach($invoice_items as $item)
                        <tr>
                            <td>
                                @if( $item->info->hsc_code != null )
                                    {{ $item->info->hsc_code }}
                                @endif

                                @if( $item->info->sac_code != null )
                                    {{ $item->info->sac_code }}
                                @endif
                            </td>
                            {{-- @if( $invoice->amount_type == 'inclusive' )
                                @php $first_part = $item->item_price; @endphp
                                @php $second_part = $item->item_price * ( 100 / ( 100 + $item->info->gst ) ); @endphp

                                @php $thisCalculatedGstAmount = ($first_part - $second_part) * $item->item_qty; @endphp

                                @php 
                                    $this_item_amount = (($item->item_price * $item->item_qty) - $thisCalculatedGstAmount) - ($item->discount * $item->item_qty); 

                                @endphp
                            @endif

                            @if( $invoice->amount_type == 'exclusive' )
                                @php $this_item_amount = ($item->item_price * $item->item_qty) - ($item->discount * $item->item_qty); @endphp
                            @endif --}}

                            @php $this_item_amount = $item->item_total; @endphp

                            @php $total_taxable_amount += $this_item_amount @endphp

                            <td>
                                {{ number_format((float)$this_item_amount, 2, '.', '') }}
                            </td>
                            <td>
                                {{ $item->info->gst / 2 }}%
                            </td>
                            {{-- @if( $invoice->amount_type == 'inclusive' )
                                @php $this_cgst = $thisCalculatedGstAmount; @endphp
                            @endif
                            @if( $invoice->amount_type == 'exclusive' )
                                @php $this_cgst = ((($item->info->gst / 2) * $this_item_amount) / 100); @endphp
                            @endif --}}

                            @php $this_cgst = $item->gst / 2; @endphp
                            
                            @php $total_cgst_amount += $this_cgst; @endphp
                            <td>
                                {{ number_format((float)$this_cgst, 2, '.', '') }}
                            </td>
                            <td>
                                {{ $item->info->gst / 2 }}%
                            </td>
                            {{-- @if( $invoice->amount_type == 'inclusive' )
                                @php $this_sgst = $thisCalculatedGstAmount; @endphp
                            @endif
                            @if( $invoice->amount_type == 'exclusive' )
                                @php $this_sgst = ((($item->info->gst / 2) * $this_item_amount) / 100); @endphp
                            @endif --}}

                            @php $this_sgst = $item->gst / 2; @endphp

                            @php $total_sgst_amount += $this_sgst; @endphp
                            <td>
                                {{ number_format((float)$this_sgst, 2, '.', '') }}
                            </td>
                            @php $this_gst = $this_cgst + $this_sgst; $total_taxed_amount += $this_gst; @endphp
                            <td>
                                {{ number_format((float)$this_gst, 2, '.', '') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format((float)$total_taxable_amount, 2, '.', '') }}</td>
                            <td></td>
                            <td>{{ number_format((float)$total_cgst_amount, 2, '.', '') }}</td>
                            <td></td>
                            <td>{{ number_format((float)$total_sgst_amount, 2, '.', '') }}</td>
                            <td>{{ number_format((float)$total_taxed_amount, 2, '.', '') }}</td>
                        </tr>
                        @if($invoice->gst_classification == 'rcm')
                        <tr>
                            <td colspan="8">Invoice under RCM</td>
                        </tr>
                        @endif
                    </tfoot>
                {{-- @elseif( ( $party->billing_state == '4' || $party->billing_state == '7' || $party->billing_state == '25' || $party->billing_state == '26' || $party->billing_state == '31' || $party->billing_state == '34' || $party->billing_state == '35' ) && $user_profile->billing_state != $party->billing_state )
                    <thead>
                        <tr align="center">
                            <th rowspan="2">HSN/SAC</th>
                            <th rowspan="2">Taxable Value</th>
                            <th colspan="2">UGST</th>
                            <th rowspan="2">Total Tax Amount</th>
                        </tr>
                        <tr align="center">
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_taxable_amount = 0;
                            $total_ugst_amount = 0;
                            $total_taxed_amount = 0;
                        @endphp
                        @foreach($invoice_items as $item)
                        <tr>
                            <td>
                                @if( $item->info->hsc_code != null )
                                    {{ $item->info->hsc_code }}
                                @endif

                                @if( $item->info->sac_code != null )
                                    {{ $item->info->sac_code }}
                                @endif
                            </td>

                            @php $this_item_amount = $item->item_total @endphp

                            @php $total_taxable_amount += $this_item_amount @endphp
                            <td>
                                {{ number_format((float)$this_item_amount, 2, '.', '') }}
                            </td>
                            <td>
                                {{ $item->info->gst }}%
                            </td>

                            @php $this_ugst = $item->gst @endphp

                            @php $total_ugst_amount += $this_ugst; @endphp
                            <td>
                                {{ number_format((float)$this_ugst, 2, '.', '') }}
                            </td>
                            @php $this_gst = $this_ugst; $total_taxed_amount += $this_gst; @endphp
                            <td>
                                {{ number_format((float)$this_gst, 2, '.', '') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format((float)$total_taxable_amount, 2, '.', '') }}</td>
                            <td></td>
                            <td>{{ number_format((float)$total_ugst_amount, 2, '.', '') }}</td>
                            <td>{{ number_format((float)$total_taxed_amount, 2, '.', '') }}</td>
                        </tr>
                    </tfoot> --}}
                @else
                    <thead>
                        <tr align="center">
                            <th rowspan="2">HSN/SAC</th>
                            <th rowspan="2">Taxable Value</th>
                            <th colspan="2">IGST</th>
                            <th rowspan="2">Total Tax Amount</th>
                        </tr>
                        <tr align="center">
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_taxable_amount = 0;
                            $total_igst_amount = 0;
                            $total_taxed_amount = 0;
                        @endphp
                        @foreach($invoice_items as $item)
                        <tr>
                            <td>
                                @if( $item->info->hsc_code != null )
                                    {{ $item->info->hsc_code }}
                                @endif

                                @if( $item->info->sac_code != null )
                                    {{ $item->info->sac_code }}
                                @endif
                            </td>
                            {{-- @if( $invoice->amount_type == 'inclusive' )
                                @php $first_part = $item->item_price; @endphp
                                @php $second_part = $item->item_price * ( 100 / ( 100 + $item->info->gst ) ); @endphp

                                @php $thisCalculatedGstAmount = ($first_part - $second_part) * $item->item_qty; @endphp

                                @php 
                                    $this_item_amount = (($item->item_price * $item->item_qty) - $thisCalculatedGstAmount) - ($item->discount * $item->item_qty); 

                                @endphp
                            @endif

                            @if( $invoice->amount_type == 'exclusive' )
                                @php 
                                    $this_item_amount = ($item->item_price * $item->item_qty) - ($item->discount * $item->item_qty);
                                 @endphp
                            @endif --}}

                            @php $this_item_amount = $item->item_total @endphp

                            @php $total_taxable_amount += $this_item_amount; @endphp
                            <td>
                                {{ number_format((float)$this_item_amount, 2, '.', '') }}
                            </td>
                            <td>
                                {{ $item->info->gst }}%
                            </td>
                            {{-- @if( $invoice->amount_type == 'inclusive' )
                                @php $this_igst = $thisCalculatedGstAmount; @endphp
                            @endif
                            @if( $invoice->amount_type == 'exclusive' )
                                @php $this_igst = ((($item->info->gst) * $this_item_amount) / 100); @endphp
                            @endif --}}

                            @php $this_igst = $item->gst @endphp

                            @php $total_igst_amount += $this_igst; @endphp
                            <td>
                                {{ number_format((float)$this_igst, 2, '.', '') }}
                            </td>
                            @php $this_gst = $this_igst; $total_taxed_amount += $this_gst; @endphp
                            <td>
                                {{ number_format((float)$this_gst, 2, '.', '') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format((float)$total_taxable_amount, 2, '.', '') }}</td>
                            <td></td>
                            <td>{{ number_format((float)$total_igst_amount, 2, '.', '') }}</td>
                            <td>{{ number_format((float)$total_taxed_amount, 2, '.', '') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @endif

        @if( $user_profile->terms_and_condition != null )
            <table width="100%">
                <thead>
                    <tr>
                        <th>Terms and Conditions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="terms_and_condition">{{ $user_profile->terms_and_condition }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
    @endif

    {{-- <div class="information" style="position: absolute; bottom: 0;">
        <table width="100%">
            <tr>
                <td align="left" style="width: 50%;">
                    &copy; {{ date('Y') }} {{ config('app.url') }} - All rights reserved.
                </td>
                <td align="right" style="width: 50%;">
                    Company Slogan
                </td>
            </tr>
        </table>
    </div> --}}
</body>
</html>
