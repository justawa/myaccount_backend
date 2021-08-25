@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('purchase-order') !!}
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Party Name</th>
                    <th>Voucher No.</th>
                    <th colspan="3"></th>
                </tr>
            </thead>
            <tbody>
            @if(count($purchase_orders) > 0)
                @php $count = 1 @endphp
                @foreach($purchase_orders as $record)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                        <td>{{ $record->party_name }}</td>
                        <td>{{ $record->token }}</td>
                        <td><a class="btn btn-link" href="{{ route('edit.purchase.order', $record->token) }}">Edit/Convert to Purchase</a></td>
                        <td><a class="btn btn-link" href="{{ route('print.purchase.order', $record->token) }}">Print</a></td>
                        <td>
                            @if($record->status == 1)    
                                <form method="post" action="{{ route('update.purchase.order.status', $record->id) }}">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="PATCH" />
                                    <input type="hidden" name="type" value="CANCEL" />
                                    <button type="submit" class="btn btn-link">Cancel</button>
                                </form>
                            @else
                                <form method="post" action="{{ route('update.purchase.order.status', $record->id) }}">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="PATCH" />
                                    <input type="hidden" name="type" value="ACTIVATE" />
                                    <button type="submit" class="btn btn-link">Activate</button>
                                </form>
                            @endif
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
@endsection