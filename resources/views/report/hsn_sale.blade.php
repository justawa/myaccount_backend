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
                    <th>HSN</th>
                    <th>Description</th>
                    <th>UQC</th>
                    <th>Total Quantity</th>
                    <th>Total Value</th>
                    <th>Taxable Value</th>
                    <th>Integrated Tax Amount</th>
                    <th>Central Tax Amount</th>
                    <th>State/UT Tax Amount</th>
                    <th>CESS Amount</th>
                    <th>GST</th>
                </tr>
            </thead>
            <tbody>
                @if( count($items) > 0 )
                    @php $count = 1; $total_gst = 0; @endphp
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $item->hsc_code }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->measuring_unit }}</td>
                        <td>{{ $item->total_qty_per_item }}</td>
                        <td>{{ $item->total_value_per_item }}</td>
                        <td>{{ $item->taxable_value_per_item }}</td>
                        <td>{{ $item->integrated_tax_value_per_item }}</td>
                        <td>{{ $item->central_tax_value_per_item }}</td>
                        <td>{{ $item->state_tax_value_per_item }}</td>
                        <td>{{ $item->cess_amount_per_item }}</td>
                        @php $total_gst += $item->gst_per_item @endphp
                        <td>{{ $item->gst_per_item }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            {{-- <tfoot>
                <tr>
                    <th colspan="11">Total</th>
                    <th>{{ $total_gst }}</th>
                </tr>
            </tfoot> --}}
        </table>
    </div>
@endsection