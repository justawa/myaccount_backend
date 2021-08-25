@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Bank</th>
                    <th>Contra</th>
                    <th>Narration</th>
                </tr>
            </thead>
            <tbody>
                @if( count($cash_withdrawn) > 0 )
                    @php $count = 1; @endphp
                    @foreach($cash_withdrawn as $cash)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $cash->amount }}</td>
                        <td>{{ $cash->date }}</td>
                        <td>{{ $cash->bank }}</td>
                        <td>{{ $cash->contra }}</td>
                        <td>{{ $cash->narration }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">No Data</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection