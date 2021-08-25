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
                            Purchase GST Report
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
                            <a href="{{ route('export.purchase.gst.report', ['from_date' => $from_date, 'to_date' => $to_date]) }}" style="border: 1px solid #000; color: #000; padding: 5px; background-color: #fff">Export</a>
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
                                   <th>Bill Value</th>
                                   <th>Taxable Value</th>
                                   <th>CESS</th>
                                   <th>Item Total @5%</th>
                                   <th>sgst @2.5%</th>
                                   <th>cgst/utgst @2.5%</th>
                                   <th>igst 5%</th>
                                   <th>Item Total @12%</th>
                                   <th>sgst @6%</th>
                                   <th>cgst/utgst @6%</th>
                                   <th>igst 12%</th>
                                   <th>Item Total @18%</th>
                                   <th>sgst @9%</th>
                                   <th>cgst/utgst @9%</th>
                                   <th>igst 18%</th>
                                   <th>Item Total @28%</th>
                                   <th>sgst @14%</th>
                                   <th>cgst/utgst @14%</th>
                                   <th>igst 28%</th>
                                   <th>Item Total @EXEMPT</th>
                                   <th>EXEMPT</th>
                                   <th>Item @NIL</th>
                                   <th>NIL</th>
                                   <th>Item @EXPORT</th>
                                   <th>EXPORT</th>
                                </tr>   
                            </thead>
                            <tbody>
                                @if( $purchases->count() > 0 )
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

                                        $purchase_value_total = 0;
                                        $taxable_value_total = 0;
                                    @endphp
                                    @foreach( $purchases as $purchase )
    
                                        {{-- <tbody> --}}
                                            <tr>
                                                <td style="padding: 0;">
                                                    <label for="{{ $purchase->id }}" style="text-transform: uppercase; display: block; margin: 0; padding: 8px;">{{ $loop->iteration }}
                                                        <input type="checkbox" name="accounting" id="{{ $purchase->id }}" data-toggle="toggle" style="visibility: hidden;">
                                                    </label>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    {{ $purchase->party->name }}
                                                </td>
                                                <td>
                                                    {{ $purchase->bill_no }}
                                                </td>
                                                <td>
                                                    {{ $purchase->total_amount - $purchase->tcs }}
                                                    @php $purchase_value_total += ($purchase->total_amount - $purchase->tcs) @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->item_total_amount }}
                                                    @php $taxable_value_total += $purchase->item_total_amount @endphp
                                                </td>
                                                <td>{{ $purchase->item_total_cess ?? 0 }}</td>
                                                @php $cess += $purchase->item_total_cess @endphp
                                                <td>
                                                    {{ $purchase->fivePercentItemTotal }}
                                                    @php $fivePercentItemGrandTotal += $purchase->fivePercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->fivePercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->fivePercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ ($purchase->has_igst) ? $purchase->fivePercentTotal : 0 }}
                                                    @php $fivePercentGrandTotal += $purchase->fivePercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->twelvePercentItemTotal }}
                                                    @php $twelvePercentItemGrandTotal += $purchase->twelvePercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->twelvePercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->twelvePercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ ($purchase->has_igst) ? $purchase->twelvePercentTotal : 0 }}
                                                    @php $twelvePercentGrandTotal += $purchase->twelvePercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->eighteenPercentItemTotal }}
                                                    @php $eighteenPercentItemGrandTotal += $purchase->eighteenPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->eighteenPercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->eighteenPercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ ($purchase->has_igst) ? $purchase->eighteenPercentTotal : 0 }}
                                                    @php $eighteenPercentGrandTotal += $purchase->eighteenPercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->twentyEightPercentItemTotal }}
                                                    @php $twentyEightPercentItemGrandTotal += $purchase->twentyEightPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->twentyEightPercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ (!$purchase->has_igst) ? $purchase->twentyEightPercentTotal/2 : 0 }}
                                                </td>
                                                <td>
                                                    {{ ($purchase->has_igst) ? $purchase->twentyEightPercentTotal : 0 }}
                                                    @php $twentyEightPercentGrandTotal += $purchase->twentyEightPercentTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->exportPercentItemTotal }}
                                                    @php $exportPercentItemGrandTotal += $purchase->exportPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->exemptPercentTotal }}
                                                    @php $exemptPercentGrandTotal += $purchase->exemptPercentTotal @endphp
                                                </td>

                                                <td>
                                                    {{ $purchase->nilPercentItemTotal }}
                                                    @php $nilPercentItemGrandTotal += $purchase->nilPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->nilPercentTotal }}
                                                    @php $nilPercentGrandTotal += $purchase->nilPercentTotal @endphp
                                                </td>

                                                <td>
                                                    {{ $purchase->exportPercentItemTotal }}
                                                    @php $exportPercentItemGrandTotal += $purchase->exportPercentItemTotal @endphp
                                                </td>
                                                <td>
                                                    {{ $purchase->exportPercentTotal }}
                                                    @php $exportPercentGrandTotal += $purchase->exportPercentTotal @endphp
                                                </td>
                                                {{-- <td>
                                                    {{ $purchase->total_amount }}
                                                </td> --}}
                                            </tr>
                                        {{-- </tbody> --}}
                                        {{-- <tbody style="display: none;">
                                        @if( auth()->user()->profile->registered != 3 )
                                        
                                        @foreach( $purchase->purchase_items->groupBy('gst_rate') as $data )
                                            @php $totalGSTAmount = 0; $key = null @endphp
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
                                        <th>{{ $purchase_value_total }}</th>
                                        <th>{{ $taxable_value_total }}</th>
                                        <th>{{ $cess }}</th>
                                        <th>{{ $fivePercentItemGrandTotal }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $fivePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $fivePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($purchase->has_igst) ? $fivePercentGrandTotal : 0 }}</th>
                                        <th>{{ $twelvePercentItemGrandTotal }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $twelvePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $twelvePercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($purchase->has_igst) ? $twelvePercentGrandTotal : 0 }}</th>
                                        <th>{{ $eighteenPercentItemGrandTotal }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $eighteenPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $eighteenPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($purchase->has_igst) ? $eighteenPercentGrandTotal : 0 }}</th>
                                        <th>{{ $twentyEightPercentItemGrandTotal }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $twentyEightPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ (!$purchase->has_igst) ? $twentyEightPercentGrandTotal/2 : 0 }}</th>
                                        <th>{{ ($purchase->has_igst) ? $twentyEightPercentGrandTotal : 0 }}</th>
                                        <th>{{ $exemptPercentItemGrandTotal }}</th>
                                        <th>{{ $exemptPercentGrandTotal }}</th>
                                        <th>{{ $nilPercentItemGrandTotal }}</th>
                                        <th>{{ $nilPercentGrandTotal }}</th>
                                        <th>{{ $exportPercentItemGrandTotal }}</th>
                                        <th>{{ $exportPercentGrandTotal }}</th>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">No Data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('[data-toggle="toggle"]').change(function(){
                console.log("toggled");
                $(this).parent().parent().parent().parent().next('tbody').toggle();
            });
        });
    </script>
@endsection