@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-6 col-md-offset-4">
            <form action="{{ route('gst.purchase.report') }}">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" class="form-control" name="from_date" style="line-height: 1.8" @if( isset( $_GET['from_date'] ) ) value="{{ app('request')->input('from_date') }}" @endif />
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" class="form-control" name="to_date" style="line-height: 1.8" @if( isset( $_GET['to_date'] ) ) value="{{ app('request')->input('to_date') }}" @endif />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label style="visibility: hidden">button</label>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>GSTIN/UIN of Recipient</th>
                    <th>Receiver Name</th>
                    <th>Bill Number</th>
                    <th>Bill date</th>
                    <th>Place of supply</th>
                    <th>Reverse Charge</th>
                    <th>Bill Type</th>
                    <th>E-commerce GSTIN</th>
                    <th>Rate</th>
                    <th>Applicable % of Tax Rate</th>
                    <th>Taxable Value</th>
                    <th>Cess Amount</th>
                </tr>
            </thead>
            <tbody>
                @if( count($parties) > 0 && count($purchases) > 0 )
                    @php $count = 1; @endphp
                    @foreach($parties as $party)
                    @foreach($purchases[$party->id] as $purchase)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $purchase->party_gst }}</td>
                        <td>{{ $purchase->party_name }}</td>
                        <td>{{ $purchase->bill_no }}</td>
                        <td>{{ $purchase->bill_date }}</td>
                        <td>{{ $purchase->place_of_supply }}</td>
                        <td>No</td>
                        <td>{{ $purchase->type_of_bill }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $purchase->gst }}</td>
                        <td>{{ $purchase->total_amount }}</td>
                        <td>{{ $purchase->cess }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

@endsection
