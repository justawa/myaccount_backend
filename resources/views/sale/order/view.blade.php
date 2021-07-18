@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('view-sale-order', request()->segment(4)) !!}
    <div class="container">
        <div class="row">
            <div class="col-md-4">
               <p><strong>Party Name :</strong> {{ $party_name }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Sale Order No. :</strong> {{ $sale_order }}</p>
            </div>
            <div>
                <p>
                    <a href="{{ route('create.sale.from.order', $sale_order) }}" class="btn btn-link">Convert to Sale</a>
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
            @if(count($sale_records) > 0)
                @php $count = 1 @endphp
                @foreach($sale_records as $record)
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
