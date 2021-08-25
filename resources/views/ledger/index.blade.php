@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">View Ledgers</div>

                <div class="panel-body">
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

                    @if(count($accounts) > 0)
                        @php $key = 0; @endphp
                        @foreach($accounts as $account)
                        
                        <h4 style="text-transform: uppercase; letter-spacing: 0.06em;">{{ $account->name }}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Particulars</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $count = 1 @endphp
                                @foreach($account as $ledger)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $ledger->particular_name }}</td>
                                    <td>{{ $ledger->amount }}</td>
                                    <td>{{ $ledger->type }}</td>
                                    <td>{{ $ledger->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endforeach
                    @else
                        <h3 class="text-center">No Ledgers</h3>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
