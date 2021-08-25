@extends('layouts.dashboard')

@section('content')
{{-- {!! Breadcrumbs::render('cash-book') !!} --}}
<div class="container">
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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            Sale GST Report
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            @if(app('request')->input('from_date') && app('request')->input('to_date'))
                                @php
                                    $from_date = app('request')->input('from_date');
                                    $to_date = app('request')->input('to_date');
                                @endphp
                            @else
                                @php
                                    $from_date = auth()->user()->profile->financial_year_from;
                                    $to_date = auth()->user()->profile->financial_year_to;
                                @endphp
                            @endif
                            <a href="{{ route('export.sale.gst.report', ['from_date' => $from_date, 'to_date' => $to_date]) }}" style="border: 1px solid #000; color: #000; padding: 5px; background-color: #fff">Export</a>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">

                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                   <th>#</th>
                                   <th>Date</th> 
                                   <th>Party</th>
                                   <th>Voucher No.</th>
                                   {{-- <th>Total GST</th> --}}
                                   <th>Invoice Value</th>
                                   <th>Taxable Value</th>
                                   <th>CESS</th>
                                   <th>Item Total @5%</th>
                                   <th>sgst @2.5%</th>
                                   <th>cgst/utgst @2.5%</th>
                                   <th>igst @5%</th>
                                   <th>Item Total @12%</th>
                                   <th>sgst @6%</th>
                                   <th>cgst/utgst @6%</th>
                                   <th>igst @12%</th>
                                   <th>Item Total @18%</th>
                                   <th>sgst @9%</th>
                                   <th>cgst/utgst @9%</th>
                                   <th>igst @18%</th>
                                   <th>Item Total @28%</th>
                                   <th>sgst @14%</th>
                                   <th>cgst/utgst @14%</th>
                                   <th>igst @28%</th>
                                   <th>Item Total @EXEMPT</th>
                                   {{-- <th>EXEMPT</th> --}}
                                   <th>Item @NIL</th>
                                   {{-- <th>NIL</th> --}}
                                   <th>Item @EXPORT</th>
                                   {{-- <th>EXPORT</th> --}}
                                   {{-- <th>Total Value</th> --}}
                                </tr>   
                            </thead>
                            <tbody>
                                @if( $sales->count() > 0 )
                                    @php
                                        $fivePercentGrandTotal = 0;
                                        $twelvePercentGrandTotal = 0;
                                        $eighteenPercentGrandTotal = 0;
                                        $twentyEightPercentGrandTotal = 0;
                                        $exemptPercentGrandTotal = 0;
                                        $nilPercentGrandTotal = 0;
                                        $exportPercentGrandTotal = 0;

                                        $fivePercentItemGrandTotal = 0;
                                        $twelvePercentItemGrandTotal = 0;
                                        $eighteenPercentItemGrandTotal = 0;
                                        $twentyEightPercentItemGrandTotal = 0;
                                        $exemptPercentItemGrandTotal = 0;
                                        $nilPercentItemGrandTotal = 0;
                                        $exportPercentItemGrandTotal = 0;

                                        $cess = 0;

                                        $invoice_value_total = 0;
                                        $taxable_value_total = 0;
                                    @endphp
                                    @foreach( $sales as $sale )
                                        {{-- <tbody> --}}
                                            <tr>
                                                <td style="padding: 0;">
                                                    <label for="{{ $sale->id }}" style="text-transform: uppercase; display: block; margin: 0; padding: 8px;">{{ $loop->iteration }}
                                                        <input type="checkbox" name="accounting" id="{{ $sale->id }}" data-toggle="toggle" style="visibility: hidden;">
                                                    </label>
                                                </td>
                                                <td>{{ Carbon\Carbon::parse($sale->invoice_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    {{ $sale->party->name }}
                                                </td>
                                                <td>
                                                    {{ $sale->invoice_prefix }}{{ $sale->invoice_no }}{{ $sale->invoice_suffix }}
                                                </td>
                                                <td>
                                                    {{ $sale->total_amount - $sale->tcs }}
                                                    @php $invoice_value_total += ($sale->total_amount - $sale->tcs) @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->item_total_amount }}
                                                    @php $taxable_value_total += $sale->item_total_amount @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->cess ?? 0 }}
                                                    @php $cess += $sale->cess @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->fivePercentItemTotal }}
                                                    @php $fivePercentItemGrandTotal += $sale->fivePercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->fivePercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->fivePercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ ($sale->has_igst) ? $sale->fivePercentTotal : '0' }}
                                                    @php $fivePercentGrandTotal += $sale->fivePercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->twelvePercentItemTotal }}
                                                    @php $twelvePercentItemGrandTotal += $sale->twelvePercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->twelvePercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->twelvePercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ ($sale->has_igst) ? $sale->twelvePercentTotal : '0' }}
                                                    @php $twelvePercentGrandTotal += $sale->twelvePercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->eighteenPercentItemTotal }}
                                                    @php $eighteenPercentItemGrandTotal += $sale->eighteenPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->eighteenPercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->eighteenPercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ ($sale->has_igst) ? $sale->eighteenPercentTotal : '0' }}
                                                    @php $eighteenPercentGrandTotal += $sale->eighteenPercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->twentyEightPercentItemTotal }}
                                                    @php $twentyEightPercentItemGrandTotal += $sale->twentyEightPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->twentyEightPercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ (!$sale->has_igst) ? $sale->twentyEightPercentTotal/2 : '0' }}
                                                </td>
                                                <td>
                                                    {{ ($sale->has_igst) ? $sale->twentyEightPercentTotal : '0' }}
                                                    @php $twentyEightPercentGrandTotal += $sale->twentyEightPercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $sale->exportPercentItemTotal }}
                                                    @php $exportPercentItemGrandTotal += $sale->exportPercentItemTotal @endphp
                                                </td>
                                                {{-- <td>
                                                    {{ $sale->exemptPercentTotal }}
                                                    @php $exemptPercentGrandTotal += $sale->exemptPercentTotal @endphp
                                                </td> --}}

                                                <td>
                                                    {{ $sale->nilPercentItemTotal }}
                                                    @php $nilPercentItemGrandTotal += $sale->nilPercentItemTotal @endphp
                                                </td>
                                                {{-- <td>
                                                    {{ $sale->nilPercentTotal }}
                                                    @php $nilPercentGrandTotal += $sale->nilPercentTotal @endphp
                                                </td> --}}

                                                <td>
                                                    {{ $sale->exportPercentItemTotal }}
                                                    @php $exportPercentItemGrandTotal += $sale->exportPercentItemTotal @endphp
                                                </td>
                                                {{-- <td>
                                                    {{ $sale->exportPercentTotal }}
                                                    @php $exportPercentGrandTotal += $sale->exportPercentTotal @endphp
                                                </td> --}}
                                                {{-- <td>
                                                    {{ $sale->total_amount }}
                                                </td> --}}
                                            </tr>
                                        {{-- </tbody> --}}
                                        {{-- <tbody style="display: none;">
                                        @if( auth()->user()->profile->registered != 3 )
                                        @foreach( $sale->invoice_items->groupBy('gst_rate') as $data )
                                            @php $totalGSTAmount = 0; $key = null; $gst_value = array(); @endphp
                                            @foreach($data as $item)
                                                @php 
                                                    $totalGSTAmount += $item->gst;
                                                    $key = $item->gst_rate;
                                                @endphp
                                            @endforeach
                                            <tr>
                                                <td colspan="5">
                                                    <table class="table table-condensed" style="background-color: transparent;">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">GST %</th>
                                                                <th class="text-center">Total GST</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-center" style="border-top: 0">{{ $key }}</td>
                                                                <td class="text-center" style="border-top: 0">{{ $totalGSTAmount }}</td>
                                                                @if($key == 5)
                                                                    @php $fivePercentTotal += $totalGSTAmount; @endphp
                                                                @endif
    
                                                                @if($key == 12)
                                                                    @php $twelvePercentTotal += $totalGSTAmount; @endphp
                                                                @endif
    
                                                                @if($key == 18)
                                                                    @php $eighteenPercentTotal += $totalGSTAmount; @endphp
                                                                @endif
    
                                                                @if($key == 28)
                                                                    @php $twentyEightPercentTotal += $totalGSTAmount; @endphp
                                                                @endif
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
    
                                        @endforeach
    
                                        @endif
                                        </tbody> --}}
    
                                    @endforeach
                                    <tr>
                                        <th colspan="6">Grand Total</th>
                                        <th>{{ $invoice_value_total }}</th>
                                        <th>{{ $taxable_value_total }}</th>
                                        <th>{{ $cess }}</th>
                                        <th>{{ $fivePercentItemGrandTotal }}</th>
                                        <th>{{ (!$sale->has_igst) ? $fivePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$sale->has_igst) ? $fivePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($sale->has_igst) ? $fivePercentGrandTotal : 0 }}</th>
                                        <th>{{ $twelvePercentItemGrandTotal }}</th>
                                        <th>{{ (!$sale->has_igst) ? $twelvePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$sale->has_igst) ? $twelvePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($sale->has_igst) ? $twelvePercentGrandTotal : 0 }}</th>
                                        <th>{{ $eighteenPercentItemGrandTotal }}</th>
                                        <th>{{ (!$sale->has_igst) ? $eighteenPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$sale->has_igst) ? $eighteenPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($sale->has_igst) ? $eighteenPercentGrandTotal : 0 }}</th>
                                        <th>{{ $twentyEightPercentItemGrandTotal }}</th>
                                        <th>{{ (!$sale->has_igst) ? $twentyEightPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$sale->has_igst) ? $twentyEightPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($sale->has_igst) ? $twentyEightPercentGrandTotal : 0 }}</th>
                                        <th>{{ $exemptPercentItemGrandTotal }}</th>
                                        {{-- <th>{{ $exemptPercentGrandTotal }}</th> --}}
                                        <th>{{ $nilPercentItemGrandTotal }}</th>
                                        {{-- <th>{{ $nilPercentGrandTotal }}</th> --}}
                                        <th>{{ $exportPercentItemGrandTotal }}</th>
                                        {{-- <th>{{ $exportPercentGrandTotal }}</th> --}}
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">No Data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- <table class="table table-bordered">
                        <thead>
                            <tr style="background-color: #f1f2fa;">
                                <th>GST Rates</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>5%</th>
                                <td><i class="fa fa-inr" aria-hidden="true"></i> {{ $fivePercentTotal }}</td>
                            </tr>
                            <tr>
                                <th>12%</th>
                                <td><i class="fa fa-inr" aria-hidden="true"></i> {{ $twelvePercentTotal }}</td>
                            </tr>
                            <tr>
                                <th>18%</th>
                                <td><i class="fa fa-inr" aria-hidden="true"></i> {{ $eighteenPercentTotal }}</td>
                            </tr>
                            <tr>
                                <th>28%</th>
                                <td><i class="fa fa-inr" aria-hidden="true"></i> {{ $twentyEightPercentTotal }}</td>
                            </tr>
                        </tbody>
                    </table> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- @section('scripts')
    <script>
        $(document).ready(function(){
            $('[data-toggle="toggle"]').change(function(){
                console.log("toggled");
                $(this).parent().parent().parent().parent().next('tbody').toggle();
            });
        });
    </script>
@endsection --}}