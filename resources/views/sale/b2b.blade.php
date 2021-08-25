@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-return') !!}

    <div class="container">
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
        <div class="row">
            <div class="col-md-12">
                <form method="get">
                    <div class="col-md-5">
                        <div class="form-group">
                            <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="from" />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="to" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-6 text-right">
                <form >
                    {{-- method="POST" action="{{ route('b2b.sale') }}" --}}
                    <div class="form-group">
                        <input type="hidden" name="export_to_excel" value="yes" />
                        <button class="btn btn-success">Export to Excel</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Invoice Date</th>
                        <th>Invoice Value</th>
                        <th>Place of Supply</th>
                        <th>Reverse Charge</th>
                        <th>Invoice Type</th>
                        <th>Ecommerce GSTIN</th>
                        <th>Rate</th>
                        <th>Applicable % of Tax Rate</th>
                        <th>Taxable Value</th>
                        <th>CESS Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($invoices) > 0)
                        @php $count = 1; @endphp
                        @foreach($invoices as $invoice)
                        @foreach($invoice_items[$invoice->id] as $item)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $item->invoice_no }}</td>
                                <td>{{ $item->invoice_date }}</td>
                                <td>{{ $item->invoice_value }}</td>
                                <td>{{ $item->place_of_supply }}</td>
                                <td>{{ $item->reverse_charge }}</td>
                                <td>{{ $item->invoice_type }}</td>
                                <td>{{ $item->party_gst_no }}</td>
                                <td>{{ $item->gst_rate }}</td>
                                <td></td>
                                <td>{{ $item->taxable_value }}</td>
                                <td>
                                    @if( $item->total_cess == null )
                                        @php $item->total_cess = 0; @endphp
                                    @endif
                                    {{ $item->total_cess }}
                                </td>
                            </tr>
                        @endforeach
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5">No Data found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection