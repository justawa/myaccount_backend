@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('debtor-report') !!}

    <div class="container">
        {{-- <div class="row">
            <form>
                <div class="col-md-5">
                    <div class="form-group">
                        <select class="form-control" name="query_by">
                            <option value="name">Name</option>
                            <option value="invoice">Invoice</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="q" class="form-control" placeholder="Query Term" />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </div>
            </form>
        </div> --}}
        {{-- <form method="GET">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success btn-block" type="submit">Submit</button>
                </div>
            </div>
        </form> --}}
        <div class="row">
            {{-- <div class="col-md-2 col-md-offset-10 text-right"><button type="button" id="btn_configuration" class="btn btn-success">Configuration</button></div> --}}
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-8">
                                Debtor Report
                            </div>
                            <div class="col-md-4">
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
                        </div>
                    </div>
                    <div class="panel-body">
                        @php $debitTotal = 0; $creditTotal = 0; $closingTotal = 0; @endphp
                        <table class="table table-bordered table-hover" style="margin-bottom:0">
                            <thead>
                                <tr>
                                    <th width="60%" rowspan="3">Debtor Report</th>
                                    <th width="40%" colspan="3" class="text-center">
                                        <p>{{ auth()->user()->profile->name }}</p>
                                        @if( $from_date && $to_date )
                                            {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
                                        @endif
                                    </th>
                                    
                                </tr>
                                <tr>
                                    <th width="20%" colspan="2" class="text-center">Transactions</th>
                                    <th width="20%" rowspan="2" class="text-center">Closing Balance</th>
                                </tr>
                                <tr>
                                    <th width="10%" class="debit-side">Debit</th>
                                    <th width="10%" class="credit-side">Credit</th>   
                                </tr>
                            </thead>
                        </table>
                        <div class="scrollable-table" style="max-height: 45vh; overflow-x: hidden; overflow-y: scroll;">
                        <table class="table table-bordered table-hover" style="margin-bottom:0">
                            <tbody>
                                <tr></tr>
                                @if( count($parties) )
                                    @php $count = 1; @endphp
                                    
                                    {{-- @foreach($parties as $party)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $party->name }}</td>
                                        @if( $party->status_of_registration == 1 || $party->status_of_registration == 3 || $party->status_of_registration == 4 )
                                            @php $registered = "Yes"; @endphp
                                        @else
                                            @php $registered = "No"; @endphp
                                        @endif
                                        <td>{{ $registered }}</td>
                                        @if( $registered == "Yes" )
                                            <td>{{ $party->gst }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{ $party->total_amount }}</td>
                                    </tr>
                                    @endforeach --}}
                                    @foreach($parties as $party)
                                    @php $moving_amount = $party->opening_balance; $prev_amount = $party->opening_balance; @endphp
                                    @php $showHead = true; $debitTotal += $party->combined_array['debit_total']; $creditTotal += $party->combined_array['credit_total']; $closingTotal += $party->combined_array['closing_total']; @endphp
                                    <tbody>
                                        <tr style="background-color: #f1f2fa">
                                            <td width="60%" style="padding: 0;">
                                                <label for="{{ $party->id }}" style="text-transform: uppercase; display: block; margin: 0; padding: 8px;">{{ $party->name }}
                                                    <input type="checkbox" name="accounting" id="{{ $party->id }}" data-toggle="toggle" style="visibility: hidden;">
                                                </label>
                                            </td>
                                            <td width="10%" class="debit-side">
                                                {{ $party->combined_array['debit_total'] }}
                                            </td>
                                            <td width="10%" class="credit-side">
                                                {{ $party->combined_array['credit_total'] }}
                                            </td>
                                            <td width="20%">
                                                {{ $party->combined_array['closing_total'] }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody style="display: none;">
                                        <tr>
                                            <td width="60%">Opening Balance</td>
                                            <td width="10%" class="debit-side"></td>
                                            <td width="10%" class="credit-side"></td>
                                            <td width="20%">{{ $party->opening_balance }}</td>
                                        </tr>
                                    @foreach($party->combined_array as $data)
                                    @if( $data['type'] == 'showable' )
                                        <tr>
                                            <td>
                                                @if($data['loop'] == 'sale')
                                                    @php    
                                                        $route = 'edit.invoice.form';
                                                    @endphp
                                                @elseif($data['loop'] == 'sale_credit_note')
                                                    @php
                                                        $route = 'show.sale.credit.note';
                                                    @endphp
                                                @elseif($data['loop'] == 'sale_debit_note')
                                                    @php
                                                        $route = 'show.sale.debit.note';
                                                    @endphp
                                                @elseif($data['loop'] == 'purchase_credit_note')
                                                    @php
                                                        $route = 'show.purchase.credit.note';
                                                    @endphp
                                                @elseif($data['loop'] == 'purchase_debit_note')
                                                    @php
                                                        $route = 'show.purchase.debit.note';
                                                    @endphp
                                                @elseif($data['loop'] == 'receipt')
                                                    @php
                                                        $route = 'edit.sale.pending.payment';
                                                    @endphp
                                                @elseif($data['loop'] == 'sale_party_payment')
                                                    @php
                                                        $route = 'edit.sale.party.pending.payment';
                                                    @endphp
                                                @elseif($data['loop'] == 'sale_order')
                                                    @php
                                                        $route = 'edit.sale.order';
                                                    @endphp
                                                @endif
                                                <table class="table table-condensed" style="background-color: transparent;">
                                                    @if($showHead)
                                                    <thead>
                                                        <tr>
                                                            <th width="25%" class="text-center">Date</th>
                                                            <th width="25%" class="text-center">Particular</th>
                                                            <th width="25%" class="text-center">Voucher Type</th>
                                                            <th width="25%" class="text-center">Voucher No.</th>
                                                        </tr>
                                                    </thead>
                                                    @php $showHead = false @endphp
                                                    @endif
                                                    <tbody>
                                                        <tr>
                                                            <td width="25%" class="text-center" style="border-top: 0">{{ \Carbon\Carbon::parse($data['date'])->format('d/m/Y') }}</td>
                                                            <td width="25%" class="text-center" style="border-top: 0">{{ $data['particulars'] }}</td>
                                                            <td width="25%" class="text-center" style="border-top: 0">{{ $data['voucher_type'] }}</td>
                                                            <td width="25%" class="text-center" style="border-top: 0">
                                                                @if($route != null)
                                                                <a href="{{ route($route, $data['routable']) }}">
                                                                    {{ $data['voucher_no'] }}
                                                                </a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            @if($data['transaction_type'] == 'debit')
                                                @php $moving_amount += $data['amount']; @endphp
                                                <td class="debit-side">{{ $data['amount'] }}</td>
                                                <td class="credit-side"></td>
                                            @endif

                                            @if($data['transaction_type'] == 'credit')
                                                <td class="debit-side"></td>
                                                @php $moving_amount -= $data['amount']; @endphp
                                                <td class="credit-side">{{ $data['amount'] }}</td>
                                            @endif

                                            <td>{{ $moving_amount > 0 ? $moving_amount . 'DR' : $moving_amount . 'CR' }}</td>
                                        </tr>
                                    @endif
                                    @endforeach
                                    </tbody>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">No Data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        </div>
                        <table class="table table-bordered table-hover" style="margin-bottom:0">
                            <tfoot>
                                <tr>
                                    <th width="60%">Grand Total</th>
                                    <td width="10%">{{ $debitTotal }}</td>
                                    <td width="10%">{{ $creditTotal }}</td>
                                    <td width="20%">{{ $closingTotal > 0 ? $closingTotal . 'DR' : $closingTotal . 'CR' }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>


    <div class="modal" id="configuration_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Configuration</h4>
                </div>
                <div class="modal-body">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_only_debit"> Show Only Debit balance
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="show_only_credit"> Show Only Credit balance
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            $("#btn_configuration").on("click", function(){
                $("#configuration_modal").modal("show");
            });
            
            $("#show_only_debit").on("change", function(){
                if( $(this).is(":checked") ){
                    $(".credit-side").css("visibility", "hidden");
                }else{
                    $(".credit-side").css("visibility", "visible");
                }
            });

            $("#show_only_credit").on("change", function(){
                if( $(this).is(":checked") ){
                    $(".debit-side").css("visibility", "hidden");
                } else {
                    $(".debit-side").css("visibility", "visible");
                }
            });

            $('[data-toggle="toggle"]').change(function(){
                $(this).parent().parent().parent().parent().next('tbody').toggle();
            });


        });
    </script>
@endsection