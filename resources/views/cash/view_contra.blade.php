@extends('layouts.dashboard')

<style>


    #document a[aria-expanded="false"]::before, #document a[aria-expanded="true"]::before, #document a[aria-expanded="true"]::before {
        content: ''
    }

</style>

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Contra
                        </div>
                        <div class="col-md-3 col-md-offset-3">
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
                <div class="panel-body" id="document">
                  <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#deposit">Cash Deposit</a></li>
                    <li><a data-toggle="tab" href="#withdraw">Cash Withdraw</a></li>
                    <li><a data-toggle="tab" href="#bank_to_bank">Bank to Bank Transfer</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="deposit" class="tab-pane fade in active">
                      <table class="table table-bordered">
                        <thead>
                            <th>SNo</th>
                            <th>Date</th>
                            <th>Contra</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @if($deposit_amounts->count())
                                @php $count=1; @endphp
                                @foreach($deposit_amounts as $cash)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cash->date)->format('d/m/Y') }}</td>
                                    <td><a href="{{ route('edit.cash.deposit', $cash->id) }}">{{ $cash->contra }}</a></td>
                                    <td>{{ $cash->banks }}</td>
                                    <td>{{ $cash->amount }}</td>
                                    <td>
                                        <a class="btn btn-default" href="{{ route('edit.cash.deposit', $cash->id) }}">Edit</a>
                                        <form style="display: inline-block" method="POST" action="{{ route('update.cash.deposit.status', $cash->id) }}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="status" value="{{ $cash->status == 1 ? 0 : 1 }}" />
                                            <button type="submit" class="btn btn-success">{{ $cash->status == 1 ? 'Cancel' : 'Activate' }}</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6">No Data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    </div>
                    <div id="withdraw" class="tab-pane fade">
                        <table class="table table-bordered">
                            <thead>
                                <th>SNo</th>
                                <th>Date</th>
                                <th>Contra</th>
                                <th>Bank</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </thead>
                            <tbody>
                                @if($withdraw_amounts->count())
                                    @php $count=1; @endphp
                                    @foreach($withdraw_amounts as $cash)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ \Carbon\Carbon::parse($cash->date)->format('d/m/Y') }}</td>
                                        <td><a href="{{ route('edit.cash.withdraw', $cash->id) }}">{{ $cash->contra }}</a></td>
                                        <td>{{ $cash->banks }}</td>
                                        <td>{{ $cash->amount }}</td>
                                        <td>
                                            <a class="btn btn-default" href="{{ route('edit.cash.withdraw', $cash->id) }}">Edit</a>
                                            <form style="display: inline-block;" method="POST" action="{{ route('update.cash.withdraw.status', $cash->id) }}">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="status" value="{{ $cash->status == 1 ? 0 : 1 }}" />
                                                <button type="submit" class="btn btn-success">{{ $cash->status == 1 ? 'Cancel' : 'Activate' }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">No Data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div id="bank_to_bank" class="tab-pane fade">
                        <table class="table table-bordered">
                            <thead>
                                <th>SNo</th>
                                <th>Date</th>
                                <th>Contra</th>
                                <th>From Bank</th>
                                <th>To Bank</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </thead>
                            <tbody>
                                @if($bank_amounts->count())
                                    @php $count=1; @endphp
                                    @foreach($bank_amounts as $bank)
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bank->date)->format('d/m/Y') }}</td>
                                        <td><a href="{{ route('edit.bank.to.bank.transfer', $bank->id) }}">{{ $bank->contra }}</a></td>
                                        <td>{{ $bank->from_banks }}</td>
                                        <td>{{ $bank->to_banks }}</td>
                                        <td>{{ $bank->amount }}</td>
                                        <td>
                                            <a class="btn btn-default" href="{{ route('edit.bank.to.bank.transfer', $bank->id) }}">Edit</a>
                                            <form style="display: inline-block;" method="POST" action="{{ route('update.bank.to.bank.transfer.status', $bank->id) }}">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="status" value="{{ $bank->status == 1 ? 0 : 1 }}" />
                                                <button type="submit" class="btn btn-success">{{ $bank->status == 1 ? 'Cancel' : 'Activate' }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7">No Data</td>
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
</div>
@endsection

{{-- @section('scripts')
    <script>
        $(document).ready(function () {

        });
    </script>
@endsection --}}
