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
                                <th width="13%" rowspan="2">Particulars</th>
                                <th width="25%" colspan="3">Receipts</th>
                                <th width="25%" colspan="3">Issued</th>
                                <th width="25%" colspan="3">Balance</th>
                            </tr>
                            <tr>
                                <th width="8%">Qty</th>
                                <th width="8%">Rate</th>
                                <th width="9%">Amount</th>
                                <th width="8%">Qty</th>
                                <th width="8%">Rate</th>
                                <th width="9%">Amount</th>
                                <th width="8%">Qty</th>
                                <th width="8%">Rate</th>
                                <th width="9%">Amount</th>
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
                                    <td width="13%">{{ $row['particulars'] }}</td>
                                    @if($row['transaction_type'] == 'receipt')
                                    <td width="8%">{{ $row['qty'] }}</td>
                                    <td width="8%">{{ $row['rate'] }}</td>
                                    <td width="9%">{{ $row['amount'] }}</td>
                                    <td width="8%"></td>
                                    <td width="8%"></td>
                                    <td width="9%"></td>
                                    @php
                                        $totalReceiptQty += $row['qty'];
                                        $totalReceiptAmt += $row['amount'];
                                    @endphp
                                    @endif
                                    @if($row['transaction_type'] == 'issued')
                                    <td width="8%"></td>
                                    <td width="8%"></td>
                                    <td width="9%"></td>
                                    <td width="8%">
                                        @php $issuedTotalQty = 0; @endphp
                                        @foreach($row['qty'] as $qty)
                                        @php $issuedTotalQty += $qty; @endphp
                                        <p>{{ $qty }}</p>
                                        @endforeach
                                        <hr/>
                                        <h5>{{ $issuedTotalQty }}</h5>
                                    </td>
                                    <td width="8%">
                                        @foreach($row['rate'] as $rate)
                                        <p>{{ $rate }}</p>
                                        @endforeach
                                    </td>
                                    <td width="9%">
                                        @php $issuedTotalAmt = 0; @endphp
                                        @foreach($row['amount'] as $amount)
                                        @php $issuedTotalAmt += $amount; @endphp
                                        <p>{{ $amount }}</p>
                                        @endforeach
                                        <hr/>
                                        <h5>{{ $issuedTotalAmt }}</h5>
                                    </td>
                                    @php
                                        $totalIssuedQty += $issuedTotalQty;
                                        $totalIssuedAmt += $issuedTotalAmt;
                                    @endphp
                                    @endif
                                    <td width="8%">
                                        @php $balanceTotalQty = 0; @endphp
                                        @foreach($row['balance']['qty'] as $qty)
                                        @php $balanceTotalQty += $qty; @endphp
                                        <p>{{ $qty }}</p>
                                        @endforeach
                                        <hr/>
                                        <h5>{{ $balanceTotalQty }}</h5>
                                    </td>
                                    <td width="8%">
                                        @foreach($row['balance']['rate'] as $rate)
                                        <p>{{ $rate }}</p>
                                        @endforeach
                                    </td>
                                    <td width="9%">
                                        @php $balanceTotalAmt = 0; @endphp
                                        @foreach($row['balance']['amount'] as $amount)
                                        @php $balanceTotalAmt += $amount; @endphp
                                        <p>{{ $amount }}</p>
                                        @endforeach
                                        <hr/>
                                        <h5>{{ $balanceTotalAmt }}</h5>
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
                                <th width="25%" colspan="2">Total</th>
                                <th width="8%">{{ $totalReceiptQty }}</th>
                                <th width="8%"></th>
                                <th width="9%">{{ $totalReceiptAmt }}</th>
                                <th width="8%">{{ $totalIssuedQty }}</th>
                                <th width="8%"></th>
                                <th width="9%">{{ $totalIssuedAmt }}</th>
                                <th width="8%">{{ $totalBalanceQty }}</th>
                                <th width="8%"></th>
                                <th width="9%">{{ $totalBalanceAmt }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection