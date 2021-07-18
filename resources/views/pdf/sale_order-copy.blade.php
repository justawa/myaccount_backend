<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Order</title>

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

    <div id="page-wrap" class="information">
        <table width="100%">
            <tr>
                <td align="left" style="width: 40%;">
                        <h3>Sale Order</h3>
                </td>
                <td align="center">
                    <img src="{{ $user_profile->logo ? asset('storage/public/'.$user_profile->logo) : '' }}" alt="Logo" width="64" class="logo"/>
                </td>
                <td align="right" style="width: 40%;">
                    <h3>{{ $party_name }}</h3>
                </td>
            </tr>

        </table>
    </div>

    <br/>

    <div class="invoice">
        <h3>Sale Order Details</h3>
        <table width="100%">
            <thead>
            <tr align="center">
                <th>#</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
                @if(count($records) > 0)
                @php
                    $count = 1;
                    $amount = 0;
                @endphp
                @foreach($records as $record)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $record->item_name }}</td>
                    <td>{{ $record->qty }}</td>
                    <td>{{ $record->rate }}</td>
                    @php
                        $this_amount = $record->qty * $record->rate;
                        $amount += $this_amount;
                    @endphp
                    <td>{{ number_format((float)$this_amount, 2, '.', '') }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
            {{-- <tfoot>
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
            </tfoot> --}}
        </table>
    </div>
    <div class="text-center">
          <button id="print_section" type="button" class="btn btn-link">Print Invoice</button>
      </div>

    <script
src="https://code.jquery.com/jquery-3.4.1.min.js"
integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
crossorigin="anonymous"></script>
    <script src="{{ asset('js/printThis/printThis.js') }}"></script>
    <script>
        $('#print_section').on("click", function () {
            $('#page-wrap').printThis();
        });
    </script>
    
</body>
</html>
