@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('view-purchase-order', request()->segment(4)) !!}

    <div class="container">
        <div class="row">
            <div class="col-md-4">
               <p><strong>Party Name :</strong> {{ $party_name }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Bill No. :</strong> {{ $purchase_order }}</p>
            </div>
            <div>
                <p>
                    <a href="{{ route('create.purchase.from.order', $purchase_order) }}" class="btn btn-link">Convert to Purchase</a>
                </p>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Item Qty</th>
                </tr>
            </thead>
            <tbody>
            @if(count($purchase_records) > 0)
                @php $count = 1 @endphp
                @foreach($purchase_records as $record)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $record->item_name }}</td>
                        <td>{{ $record->qty }}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
@endsection