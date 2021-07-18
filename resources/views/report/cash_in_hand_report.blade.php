@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @if( count($cash_in_hand) > 0 )
                    @foreach($cash_in_hand as $cash)
                    <tr>
                        <td>{{ auth()->user()->name }}</td>
                        <td>{{ $cash->opening_balance }}</td>
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