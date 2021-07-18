@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('cash-deposit') !!}
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Cash Deposit in Bank Voucher</div>
                <div class="panel-body">
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
                                    <td>{{ $bank->contra }}</td>
                                    <td>{{ $bank->from_banks }}</td>
                                    <td>{{ $bank->to_banks }}</td>
                                    <td>{{ $bank->amount }}</td>
                                    <td>
                                        <a class="btn btn-default" href="{{ route('view.bank.to.bank.transfer', $bank->id) }}">Edit</a>
                                        <form style="display: inline-block;" method="POST" action="{{ route('update.bank.to.bank.transfer.status', $bank->id) }}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="status" value="{{ $bank->status == 1 ? 0 : 1 }}" />
                                            <button type="submit" class="btn btn-success">{{ $bank->status == 1 ? 'Cancel' : 'Activate' }}</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
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
