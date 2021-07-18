@extends('layouts.dashboard')

@section('content')
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
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Item Report</div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="12">
                                    {{ auth()->user()->profile->name }}
                                </th>
                            </tr>
                            <tr>
                                <th rowspan="2">Date</th>
                                <th rowspan="2">Particulars</th>
                                <th rowspan="2">Vch Type</th>
                                <th rowspan="2">Vch No.</th>
                                <th @if(auth()->user()->profile->inventory_type != "without_inventory") colspan="3" @endif>Inwards</th>
                                <th @if(auth()->user()->profile->inventory_type != "without_inventory") colspan="3" @endif>Outwards</th>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <th colspan="2">Closing</th>
                                @endif
                            </tr>
                            <tr>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <th>Rate</th>
                                <th>Quantity <span style="font-size: 11px;">({{ $item->measuring_unit }})</span></th>
                                @endif
                                <th>Value</th>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <th>Rate</th>
                                <th>Quantity <span style="font-size: 11px;">({{ $item->measuring_unit }})</span></th>
                                @endif
                                <th>Value</th>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                {{-- <th>Rate</th> --}}
                                <th>Quantity <span style="font-size: 11px;">({{ $item->measuring_unit }})</span></th>
                                <th>Value</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $inwards_total_qty = 0;
                                $inwards_total_val = 0;

                                $outwards_total_qty = 0;
                                $outwards_total_val = 0;

                                $closing_total_qty = 0;
                                $closing_total_val = 0;
                            @endphp
                            @if(count($rows) > 0)
                                @php
                                    $closing_qty = 0;
                                    $closing_value = 0;
                                @endphp
                                @foreach($rows as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                    <td>{{ $row['particulars'] }}</td>
                                    <td>{{ $row['voucher_type'] }}</td>
                                    <td>{{ $row['voucher_no'] }}</td>
                                    
                                    @if($row['type'] == 'purchase' || $row['type'] == 'opening_balance')
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td>{{ $row['rate'] }}</td>
                                        <td>{{ $row['quantity'] }}</td>
                                        @endif
                                        <td>{{ $row['value'] }}</td>
                                        
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td></td>
                                        <td></td>
                                        @endif
                                        <td></td>
                                        
                                        @php
                                            $closing_qty += $row['quantity'];
                                            $closing_value += $row['value'];

                                            $inwards_total_qty += $row['quantity'];
                                            $inwards_total_val += $row['value'];
                                        @endphp
                                    @endif

                                    @if($row['type'] == 'sale')
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td></td>
                                        <td></td>
                                        @endif
                                        <td></td>
                                        
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td>{{ $row['rate'] }}</td>
                                        <td>{{ $row['quantity'] }}</td>
                                        @endif
                                        <td>{{ $row['value'] }}</td>
                                        
                                        @php
                                            $closing_qty -= $row['quantity'];
                                            $closing_value -= $row['value'];

                                            $outwards_total_qty += $row['quantity'];
                                            $outwards_total_val += $row['value'];
                                        @endphp
                                    @endif
                                    @if($row['type'] == 'credit_note')
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td>{{ $row['rate'] }}</td>
                                        <td>{{ $row['quantity'] }}</td>
                                        @endif
                                        <td>{{ $row['value'] }}</td>
                                        
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td></td>
                                        <td></td>
                                        @endif
                                        <td></td>
                                        
                                        @php
                                            $closing_qty += $row['quantity'];
                                            $closing_value += $row['value'];

                                            $inwards_total_qty += $row['quantity'];
                                            $inwards_total_val += $row['value'];
                                        @endphp
                                    @endif
                                    @if($row['type'] == 'debit_note')
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td></td>
                                        <td></td>
                                        @endif
                                        <td></td>
                                        
                                        @if(auth()->user()->profile->inventory_type != "without_inventory")
                                        <td>{{ $row['rate'] }}</td>
                                        <td>{{ $row['quantity'] }}</td>
                                        @endif
                                        <td>{{ $row['value'] }}</td>
                                        
                                        @php
                                            $closing_qty -= $row['quantity'];
                                            $closing_value -= $row['value'];

                                            $outwards_total_qty += $row['quantity'];
                                            $outwards_total_val += $row['value'];
                                        @endphp
                                    @endif
                                    @if($row['type'] == 'managed_inventory')
                                        @if($closing_qty < $row['quantity'])
                                            @php
                                                $managed_qty = $row['quantity'] - $closing_qty;
                                            @endphp
                                            @if(auth()->user()->profile->inventory_type != "without_inventory")
                                            <td>{{ $managed_qty }}</td>
                                            @endif
                                            <td>{{ $row['value'] }}</td>
                                            @if(auth()->user()->profile->inventory_type != "without_inventory")
                                            <td></td>
                                            @endif
                                            <td></td>

                                            @php
                                                $closing_qty += $managed_qty;
                                                $closing_value += $row['value'];

                                                $inwards_total_qty += $row['quantity'];
                                                $inwards_total_val += $row['value'];
                                            @endphp  
                                        @else
                                            @php
                                                $managed_qty = $closing_qty - $row['quantity'];
                                            @endphp
                                            <td></td>
                                            @if(auth()->user()->profile->inventory_type != "without_inventory")
                                            <td></td>
                                            <td>{{ $managed_qty }}</td>
                                            @endif
                                            <td>{{ $row['value'] }}</td>

                                            @php
                                                $closing_qty -= $managed_qty;
                                                $closing_value -= $row['value'];

                                                $outwards_total_qty += $row['quantity'];
                                                $outwards_total_val += $row['value'];
                                            @endphp
                                        @endif
                                    @endif
                                    @if(auth()->user()->profile->inventory_type != "without_inventory")
                                    <td>{{ $closing_qty }}</td>
                                    <td>{{ $closing_value }}</td>
                                    @endif
                                    @php
                                        $closing_total_qty += $row['quantity'];
                                        $closing_total_val += $row['value'];
                                    @endphp
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">No Data</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <th></th>
                                <th>{{ $inwards_total_qty > 0 ? $inwards_total_qty : '' }}</th>
                                @endif
                                <th>{{ $inwards_total_val > 0 ? $inwards_total_val : '' }}</th>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <th></th>
                                <th>{{ $outwards_total_qty > 0 ? $outwards_total_qty : '' }}</th>
                                @endif
                                <th>{{ $outwards_total_val > 0 ? $outwards_total_val : '' }}</th>
                                
                                
                                <th>
                                    {{-- {{ $closing_total_qty }} --}}
                                </th>
                                <th>
                                    {{-- {{ $closing_total_val }} --}}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        

    });
</script>
@endsection