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
        <div class="col-md-4 col-md-offset-8 mt-5">
            <form>
                <div class="form-group">
                    <label>Stock Type</label>
                    <select class="form-control" name="type">
                        <option value="fifo">FIFO</option>
                        <option value="lifo">LIFO</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-block">Submit</button>
                </div>
            </form>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Item Report</div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover" style="margin-bottom:0">
						<thead>
                            <tr>
                                <th width="12%" rowspan="2">Date</th>
                                <th width="28%" rowspan="2">Particulars</th>
                                <th width="20%" colspan="2">Receipts</th>
                                <th width="20%" colspan="2">Issued</th>
                                <th width="20%" colspan="2">Balance</th>
                            </tr>
                            <tr>
                                <th width="10%">Qty</th>
                                {{-- <th>Rate</th> --}}
                                <th width="10%">Amount</th>
                                <th width="10%">Qty</th>
                                {{-- <th>Rate</th> --}}
                                <th width="10%">Amount</th>
                                <th width="10%">Qty</th>
                                {{-- <th>Rate</th> --}}
                                <th width="10%">Amount</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="scrollable-table" style="max-height: 85vh; overflow-x: hidden; overflow-y: scroll;">
                    <table class="table table-bordered" style="margin-bottom:0">
                        <tbody>
                            @if(count($data) > 0)
                                @php
                                    $totalReceiptQty = 0;
                                    $totalReceiptAmt = 0;
                                    $totalIssuedQty = 0;
                                    $totalIssuedAmt = 0;
                                    $totalBalanceQty = 0;
                                    $totalBalanceAmt = 0;
                                @endphp
                                @foreach($data as $row)
                                <tr>
                                    <td width="12%">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                    <td width="28%">{{ $row['particulars'] }}</td>
                                    @if($row['transaction_type'] == 'receipt')
                                    <td width="10%">{{ $row['qty'] }}</td>
                                    {{-- <td>{{ $row['rate'] }}</td> --}}
                                    <td width="10%">{{ $row['amount'] }}</td>
                                    <td width="10%"></td>
                                    <td width="10%"></td>
                                    {{-- <td></td> --}}
                                    @php
                                        $totalReceiptQty += $row['qty'];
                                        $totalReceiptAmt += $row['amount'];
                                    @endphp
                                    @endif
                                    @if($row['transaction_type'] == 'issued')
                                    <td width="10%"></td>
                                    <td width="10%"></td>
                                    {{-- <td></td> --}}
                                    <td width="10%">
                                        @php $issuedTotalQty = 0; @endphp
                                        @foreach($row['qty'] as $qty)
                                        @php $issuedTotalQty += $qty; @endphp
                                        {{-- <p>{{ $qty }}</p> --}}
                                        @endforeach
                                        {{-- <hr/> --}}
                                        <p>{{ $issuedTotalQty }}</p>
                                    </td>
                                    {{-- <td>
                                        @foreach($row['rate'] as $rate)
                                        <p>{{ $rate }}</p>
                                        @endforeach
                                    </td> --}}
                                    <td width="10%">
                                        @php $issuedTotalAmt = 0; @endphp
                                        @foreach($row['amount'] as $amount)
                                        @php $issuedTotalAmt += $amount; @endphp
                                        {{-- <p>{{ $amount }}</p> --}}
                                        @endforeach
                                        {{-- <hr/> --}}
                                        <p>{{ $issuedTotalAmt }}</p>
                                    </td>
                                    @php
                                        $totalIssuedQty += $issuedTotalQty;
                                        $totalIssuedAmt += $issuedTotalAmt;
                                    @endphp
                                    @endif
                                    <td width="10%">
                                        @php $balanceTotalQty = 0; @endphp
                                        @foreach($row['balance']['qty'] as $qty)
                                        @php $balanceTotalQty += $qty; @endphp
                                        {{-- <p>{{ $qty }}</p> --}}
                                        @endforeach
                                        {{-- <hr/> --}}
                                        <p>{{ $balanceTotalQty }}</p>
                                    </td>
                                    {{-- <td>
                                        @foreach($row['balance']['rate'] as $rate)
                                        <p>{{ $rate }}</p>
                                        @endforeach
                                    </td> --}}
                                    <td width="10%">
                                        @php $balanceTotalAmt = 0; @endphp
                                        @foreach($row['balance']['amount'] as $amount)
                                        @php $balanceTotalAmt += $amount; @endphp
                                        {{-- <p>{{ $amount }}</p> --}}
                                        @endforeach
                                        {{-- <hr/> --}}
                                        <p>{{ $balanceTotalAmt }}</p>
                                    </td>
                                    @php
                                        $totalBalanceQty = $balanceTotalQty;
                                        $totalBalanceAmt = $balanceTotalAmt;
                                    @endphp
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">No Data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    </div>
                    <table class="table table-bordered">
                        <tfoot>
                            <tr>
                                <th width="40%" colspan="2">Total</th>
                                <th width="10%">{{ $totalReceiptQty }}</th>
                                <th width="10%">{{ $totalReceiptAmt }}</th>
                                <th width="10%">{{ $totalIssuedQty }}</th>
                                <th width="10%">{{ $totalIssuedAmt }}</th>
                                <th width="10%">{{ $totalBalanceQty }}</th>
                                <th width="10%">{{ $totalBalanceAmt }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection