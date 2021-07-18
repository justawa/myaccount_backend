@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-6 text-right">
                <form>
                    {{-- method="POST" action="{{ route('b2b.sale') }}" --}}
                    <div class="form-group">
                        <input type="hidden" name="export_to_excel" value="yes" />
                        <button class="btn btn-success">Export to Excel</button>
                    </div>
                </form>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Note/Refund Voucher Number</th>
                    <th>Note/Refund Voucher Date</th>
                    <th>Document Type</th>
                    <th>Place of Supply</th>
                    <th>Note/Refund Voucher Value</th>
                    <th>Rate</th>
                    <th>Applicable % of Tax Rate</th>
                    <th>Taxable Value</th>
                    <th>Cess Amount</th>
                    <th>Pre GST</th>
                </tr>
            </thead>
            <tbody>
                @if( count($notes) )
                @php $count = 1; @endphp
                @foreach( $notes as $note )
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $note['voucher_no'] }}</td>
                    <td>{{ $note['date'] }}</td>
                    <td>{{ $note['note_type'] }}</td>
                    <td>{{ $note['place_of_supply'] }}</td>
                    <td>{{ $note['price_difference'] }}</td>
                    <td>{{ $note['gst_rate'] }}</td>
                    <td></td>
                    <td>{{ $note['price'] }}</td>
                    <td>{{ $note['cess'] }}</td>
                    <td>{{ $note['pre_gst'] }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
@endsection