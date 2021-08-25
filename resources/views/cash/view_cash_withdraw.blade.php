@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('cash-deposit') !!}
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Cash Withdraw in Bank Voucher</div>
                <div class="panel-body">
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
                            @if($cash_amounts->count())
                                @php $count=1; @endphp
                                @foreach($cash_amounts as $cash)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cash->date)->format('d/m/Y') }}</td>
                                    <td>{{ $cash->contra }}</td>
                                    <td>{{ $cash->banks }}</td>
                                    <td>{{ $cash->amount }}</td>
                                    <td>
                                        <a class="btn btn-default" href="{{ route('view.cash.withdraw', $cash->id) }}">Edit</a>
                                        <form style="display: inline-block;" method="POST" action="{{ route('update.cash.withdraw.status', $cash->id) }}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="status" value="{{ $cash->status == 1 ? 0 : 1 }}" />
                                            <button type="submit" class="btn btn-success">{{ $cash->status == 1 ? 'Cancel' : 'Activate' }}</button>
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
