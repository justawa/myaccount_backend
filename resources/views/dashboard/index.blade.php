@extends('layouts.dashboard')

{{-- @section('company_logo')
    <img src="{{ asset('storage/public/'.$user_profile->logo) }}" style="display: block; margin: 0 auto; height: 80px;" />
@endsection --}}

@section('content')
{!! Breadcrumbs::render('home') !!}
<div class="container">

    <style>
        .styled-box {
            border: 1px solid;
            height: 100px;
            margin-bottom: 15px;
            color: #fff;
            padding: 10px;
            box-sizing: border-box;
            box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);
        }

        .styled-box p {
            color: #fff;
        }

        .my-row {
            height: 40px;
        }

        .material-icons.md-75 { font-size: 75px; }

        .mt-0{
            margin-top: 0;
        }

        .mb-0{
            margin-bottom: 0;
        }

        .ml-0{
            margin-left: 0;
        }

        .mr-0{
            margin-right: 0;
        }

        .mt-15{
            margin-top: 15px;
        }

        .mb-15{
            margin-bottom: 15px;
        }

        .ml-15{
            margin-left: 15px;
        }

        .mr-15{
            margin-right: 15px;
        }

        @media only screen and (max-width: 479px) {
            .ml-0{
                margin-left: -15px;
            }

            .mr-0{
                margin-right: -15px;
            }   
        }
    </style>
    <div class="row">
        {{-- <img src="{{ asset('storage/public/'.$user_profile->logo) }}" style="display: block; margin: 0 auto 20px; height: 150px;" /> --}}

        {{-- @section('header_style')
            style="background-color: transparent; box-shadow: none;"
        @endsection --}}

        {{-- @if( $user_profile->name != null )
            <h2 class="text-left text-uppercase" style="padding-bottom: 35px;">{{ $user_profile->name . "(". \Carbon\Carbon::parse($user_profile->financial_year_from)->format('Y') . "-" . \Carbon\Carbon::parse($user_profile->financial_year_to)->format('y') . ")" }}</h2>
        @endif --}}
    </div>
    <div class="row">
        {{-- <div class="col-md-6">

            <div class="col-md-12 styled-box" style="background-color: #1D6DC8; border-color: #1D6DC8;">
                <div class="col-md-2">
                    <i class="material-icons md-75">shopping_cart</i>
                </div>
                <div class="col-md-10">
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Purchases</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">{{ $purchase_data['total_purchase'] }}</div>
                    </div>
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Value</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">{{ $purchase_data['total_amount_purchase'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 styled-box" style="background-color: #7351A7; border-color: #7351A7;">
                <div class="col-md-2">
                    <i class="material-icons md-75">shopping_basket</i>
                </div>
                <div class="col-md-10">
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Sales</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">{{ $sale_data['total_sale'] }}</div>
                    </div>
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Value</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">{{ $sale_data['total_amount_sale'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 styled-box" style="background-color: #334062; border-color: #334062;">
                <div class="col-md-2">
                    <i class="material-icons md-75">reply</i>
                </div>
                <div class="col-md-10">
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Returns</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">0.00</div>
                    </div>
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Value</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">0.00</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-6">

            <div class="col-md-12 styled-box" style="background-color: #1A564C; border-color: #1A564C;">
                <div class="col-md-2">
                    <i class="fa fa-inr fa-5x" aria-hidden="true"></i>
                </div>
                <div class="col-md-10">
                    <div class="row my-row">
                        <div class="col-md-12 col-sm-12 col-xs-12">Purchases Taxes</div>
                    </div>
                    <div class="row my-row">
                        <div class="col-md-12 col-sm-12 col-xs-12 text-right">{{ $purchase_data['total_purchase_tax'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 styled-box" style="background-color: #336DB7; border-color: #336DB7;">
                <div class="col-md-2">
                    <i class="fa fa-inr fa-5x" aria-hidden="true"></i>
                </div>
                <div class="col-md-10">
                    <div class="row my-row">
                        <div class="col-md-12 col-sm-12 col-xs-12">Sales Taxes</div>
                    </div>
                    <div class="row my-row">
                        <div class="col-md-12 col-sm-12 col-xs-12 text-right">{{ $sale_data['total_sale_tax'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 styled-box" style="background-color: #4C4823; border-color: #4C4823;">
                <div class="col-md-2">
                    <i class="fa fa-money fa-5x" aria-hidden="true"></i>
                </div>
                <div class="col-md-10">
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Payments</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">{{ $payment_collection['total_amount'] }}</div>
                    </div>
                    <div class="row my-row">
                        <div class="col-md-8 col-sm-8 col-xs-8">Collection</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 text-right">{{ $payment_collection['amount_paid'] }}</div>
                    </div>
                </div>
            </div>

        </div> --}}

        <div class="row">
            <div class="col-md-8">
                {{-- <div class="row">
                    <a href="{{ route('sale.account') }}">
                        <div class="col-md-6" style="padding-left: 0; padding-right: 7px;">
                            <div class="col-md-10 col-sm-offset-1">
                                <div class="styled-box" style="color: #268ddd; border: none;">
                                    <div class="row my-row">
                                        <div class="col-md-12 col-sm-12 col-xs-12" style="color: #000;">Sales</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-2 col-sm-2 col-xs-2 text-left">
                                                    <i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i>
                                                </div>
                                                <div class="col-md-10 col-sm-10 col-xs-10 text-left" style="line-height: 4.5">{{ $sale_data['total_amount_sale'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('purchase.account') }}">
                        <div class="col-md-6" style="padding-left: 7px; padding-right: 0;">
                            <div class="col-md-10 col-sm-offset-1">
                                <div class="styled-box" style="color: #eb6100; border: none;">
                                    <div class="row my-row">
                                        <div class="col-md-8 col-sm-8 col-xs-8" style="color: #000;">Purchases</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-2 col-sm-2 col-xs-2 text-left">
                                                    <i class="fa fa-inr fa-3x" aria-hidden="true" style="color: #000;"></i>
                                                </div>
                                                <div class="col-md-10 col-sm-10 col-xs-10 text-left" style="line-height: 4.5">{{ $purchase_data['total_amount_purchase'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('creditor.report') }}">
                        <div class="col-md-6" style="padding-right: 7px; padding-left: 0;">
                            <div class="styled-box" style="color: #eb6100; border: none;">
                                <div class="row my-row">
                                    <div class="col-md-8 col-sm-8 col-xs-8" style="color: #000;">Payable</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-xs-2 text-left">
                                                <i class="fa fa-inr fa-3x" aria-hidden="true" style="color: #000;"></i>
                                            </div>
                                            <div class="col-md-10 col-sm-10 col-xs-10 text-left" style="line-height: 4.5">{{ $payment['amount_paid'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('debtor.report') }}">
                        <div class="col-md-6" style="padding-right: 0; padding-left: 7px;">
                            <div class="styled-box" style="color: #268ddd; border: none;">
                                <div class="row my-row">
                                    <div class="col-md-8 col-sm-8 col-xs-8" style="color: #000;">Receivable</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-xs-2 text-left">
                                                <i class="fa fa-inr fa-3x" aria-hidden="true" style="color: #000;"></i>
                                            </div>
                                            <div class="col-md-10 col-sm-10 col-xs-10 text-left" style="line-height: 4.5">{{ $receipt['amount_paid'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div> --}}
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8" style="padding: 10px 0; background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                                <div class="col-md-6">
                                    <p style="margin-bottom: 0;">Sales <i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> <span style="color: #268ddd;">{{ $sale_data['total_amount_sale'] }}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p style="margin-bottom: 0;">Purchases <i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> <span style="color: #eb6100;">{{ $purchase_data['total_amount_purchase'] }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8" style="padding: 10px 0; background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                                <div class="col-md-6">
                                    <p style="margin-bottom: 0;">Receivable <i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> <span style="color: #268ddd;">{{ $receipt['amount_paid'] }}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p style="margin-bottom: 0;">Payable <i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> <span style="color: #eb6100;">{{ $payment['amount_paid'] }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                    {{-- <div class="col-md-6">
                        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                        <script type="text/javascript">
                        google.charts.load('current', {'packages':['corechart']});
                        google.charts.setOnLoadCallback(drawChart);
                
                        function drawChart() {
                
                            var data = google.visualization.arrayToDataTable([
                            ['Sale', 'GST Chart'],
                            ['IGST',     "{{ $sale_data['total_igst'] }}"],
                            ['SGST',      "{{ $sale_data['total_sgst'] }}"],
                            ['CGST',  "{{ $sale_data['total_cgst'] }}"],
                            
                            ]);
                
                            var options = {
                            title: 'Sale GST',
                            is3D: true
                            };
                
                            var chart = new google.visualization.PieChart(document.getElementById('piechart3'));
                
                            chart.draw(data, options);
                        }
                        </script>
                        <div id="piechart3" style="width: 100%; height: 538px;"></div>
                    </div>
                    <div class="col-md-6">
                        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                        <script type="text/javascript">
                        google.charts.load('current', {'packages':['corechart']});
                        google.charts.setOnLoadCallback(drawChart);
                
                        function drawChart() {
                
                            var data = google.visualization.arrayToDataTable([
                            ['Purchase', 'GST Chart'],
                            ['IGST',     "{{ $purchase_data['total_igst'] }}"],
                            ['SGST',      "{{ $purchase_data['total_sgst'] }}"],
                            ['CGST',  "{{ $purchase_data['total_cgst'] }}"],
                            
                            ]);
                
                            var options = {
                            title: 'Purchase GST',
                            is3D: true
                            };
                
                            var chart = new google.visualization.PieChart(document.getElementById('piechart4'));
                
                            chart.draw(data, options);
                        }
                        </script>
                        <div id="piechart4" style="width: 100%; height: 538px;"></div>
                    </div>  --}}
                    <div class="col-md-12">
                        <h2>Stock Report (In Lakhs)</h2>
                        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                        <script>
                            google.charts.load('current', {packages: ['corechart', 'bar']});
                            google.charts.setOnLoadCallback(drawChart);

                            function drawChart() {
                                var data = google.visualization.arrayToDataTable([
                                    ['Month', 'Stock Out', 'Stock In'],
                                    ['Apr', {{ $sale_stock["apr"] }}, {{ $purchase_stock["apr"] }} ],
                                    ['May', {{ $sale_stock["may"] }}, {{ $purchase_stock["may"] }} ],
                                    ['Jun', {{ $sale_stock["jun"] }}, {{ $purchase_stock["jun"] }} ],
                                    ['Jul', {{ $sale_stock["jul"] }}, {{ $purchase_stock["jul"] }} ],
                                    ['Aug', {{ $sale_stock["aug"] }}, {{ $purchase_stock["aug"] }} ],
                                    ['Sep', {{ $sale_stock["sep"] }}, {{ $purchase_stock["sep"] }} ],
                                    ['Oct', {{ $sale_stock["oct"] }}, {{ $purchase_stock["oct"] }} ],
                                    ['Nov', {{ $sale_stock["nov"] }}, {{ $purchase_stock["nov"] }} ],
                                    ['Dec', {{ $sale_stock["dec"] }}, {{ $purchase_stock["dec"] }} ],
                                    ['Jan', {{ $sale_stock["jan"] }}, {{ $purchase_stock["jan"] }} ],
                                    ['Feb', {{ $sale_stock["feb"] }}, {{ $purchase_stock["feb"] }} ],
                                    ['Mar', {{ $sale_stock["mar"] }}, {{ $purchase_stock["mar"] }} ]
                                ]);

                                var materialOptions = {
                                    chart: {
                                        title: ''
                                    },
                                    colors: ['#eb6100','#268ddd'],
                                    
                                };
                                var materialChart = new google.charts.Bar(document.getElementById('chart_div'));
                                materialChart.draw(data, materialOptions);
                            }
                        </script>
                        <div id="chart_div" style="width: 100%; height: 538px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row ml-0" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                    <div class="col-md-12">
                        <a href="{{ route('cash.book') }}">
                            <h4 style="color: #636b6f"><i style="color: red" class="fa fa-university" aria-hidden="true"></i> Bank Balance</h4>
                            <p style="font-weight: 600; color: #000;"><i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> {{ $bank_balance }}</p>
                        </a>
                        <hr/>
                        <a href="{{ route('cash.book') }}">
                            <h4 style="color: #636b6f;"><i style="color: blue" class="fa fa-money" aria-hidden="true"></i> Cash Balance</h4>
                            <p style="font-weight: 600; color: #000;"><i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> {{ $cash_balance }}</p>
                        </a>
                    </div>
                </div>

                <div class="row ml-0" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12); margin-top: 15px;">
                    <div class="col-md-12">
                        <h4><i style="color: green" class="fa fa-money" aria-hidden="true"></i> Total Cash Deposit</h4>
                        <p style="font-weight: 600; color: #000;"><i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> {{ $cash_deposit }}</p>
                        <hr/>
                        <h4><i style="color: black" class="fa fa-money" aria-hidden="true"></i> Total Cash Withdrawn</h4>
                        <p style="font-weight: 600; color: #000;"><i class="fa fa-inr" aria-hidden="true" style="color: #000;"></i> {{ $cash_withdrawn }}</p>
                    </div>
                </div>

                {{-- <div class="row ml-0" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12); margin-top: 15px;">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total_qty = 0; @endphp
                                @if($itemsWithNegQty->count() > 0)
                                @foreach($itemsWithNegQty as $item)
                                @php $total_qty += $item->qty; @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->qty }}</td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="3">No Finished Items</td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td>{{ $total_qty }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3"><a href="{{ route('item.report') }}">More+</a></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div> --}}
            </div>
        </div>

        {{-- <div class="row" style="margin-top: 15px;">
            <div class="col-md-4" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                <h3 class="text-center">Latest Sales</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No.</th>
                            <th>Party</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($latest_invoices->count() > 0)
                            @foreach($latest_invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('edit.invoice.form', $invoice->id) }}">{{ $invoice->invoice_prefix . $invoice->invoice_no . $invoice->suffix }}</a></td>
                                <td>{{ $invoice->party->name }}</td>
                                <td>{{ 'Rs '.$invoice->item_total_amount }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="col-md-4" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                <h3 class="text-center">Latest Purchases</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bill No.</th>
                            <th>Party</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($latest_bills->count() > 0)
                            @foreach($latest_bills as $bill)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('edit.bill.form', $bill->id) }}">{{ $bill->bill_no }}</a></td>
                                <td>{{ $bill->party->name }}</td>
                                <td>{{ 'Rs '.$bill->item_total_amount }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="col-md-4" style="background-color: #fff; box-shadow: 0px 1px 3px 0px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 2px 1px -1px rgba(0,0,0,0.12);">
                <h3 class="text-center">Most Sold Items</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($most_active_items->count() > 0)
                            @foreach($most_active_items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('single.item.report', $item->item_id) }}">{{ $item->name }}</a></td>
                                <td>{{ $item->qty }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div> --}}

        

        {{-- <div class="row" style="background-color: #fff;">
            <div class="col-md-6">
                <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                <script type="text/javascript">
                    google.charts.load("current", {packages:["corechart"]});
                    google.charts.setOnLoadCallback(drawChart);
                    function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ["Element", "Density", { role: "style" } ],
                        ["Copper", 8.94, "#b87333"],
                        ["Silver", 10.49, "silver"],
                        ["Gold", 19.30, "gold"],
                        ["Platinum", 21.45, "color: #e5e4e2"]
                    ]);

                    var view = new google.visualization.DataView(data);
                    view.setColumns([0, 1,
                                    { calc: "stringify",
                                        sourceColumn: 1,
                                        type: "string",
                                        role: "annotation" },
                                    2]);

                    var options = {
                        title: "Density of Precious Metals, in g/cm^3",
                        width: 500,
                        height: 400,
                        bar: {groupWidth: "95%"},
                        legend: { position: "none" },
                    };
                    var chart = new google.visualization.BarChart(document.getElementById("barchart_values1"));
                    chart.draw(view, options);
                }
                </script>
                <div id="barchart_values1" style="width: 100%;"></div>
            </div>
            <div class="col-md-6">
                <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                <script type="text/javascript">
                    google.charts.load("current", {packages:["corechart"]});
                    google.charts.setOnLoadCallback(drawChart);
                    function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ["Element", "Density", { role: "style" } ],
                        ["Copper", 8.94, "#b87333"],
                        ["Silver", 10.49, "silver"],
                        ["Gold", 19.30, "gold"],
                        ["Platinum", 21.45, "color: #e5e4e2"]
                    ]);

                    var view = new google.visualization.DataView(data);
                    view.setColumns([0, 1,
                                    { calc: "stringify",
                                        sourceColumn: 1,
                                        type: "string",
                                        role: "annotation" },
                                    2]);

                    var options = {
                        title: "Density of Precious Metals, in g/cm^3",
                        width: 500,
                        height: 400,
                        bar: {groupWidth: "95%"},
                        legend: { position: "none" },
                    };
                    var chart = new google.visualization.BarChart(document.getElementById("barchart_values2"));
                    chart.draw(view, options);
                }
                </script>
                <div id="barchart_values2" style="width: 100%;"></div>
            </div> 
        </div> --}}

        
    </div>

</div>
@endsection