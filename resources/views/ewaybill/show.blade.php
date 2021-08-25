@extends('layouts.dashboard')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Eway Bill</div>

                <div class="panel-body">
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Eway Bill No</th>
                                <th>Created On</th>
                                <th>Valid Upto</th>
                                <th>Invoice No</th>
                                <th>Invoice Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $ewaybill->bill_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($ewaybill->created_on)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($ewaybill->valid_upto)->format('d/m/Y') }}</td>
                                <td>{{ $ewaybill->invoice->invoice_prefix . $ewaybill->invoice->invoice_no . $ewaybill->invoice->invoice_suffix }}</td>
                                <td>{{ $ewaybill->invoice->total_amount }}</td>
                                <td>
                                    @if($ewaybill->status == 1)
                                    <form method="post" action="{{ route('eway.bill.update', $ewaybill->id) }}">
                                        {{ csrf_field() }}
                                        {{ method_field('PATCH') }}
                                        <input type="hidden" name="status" value="0" />
                                        <button type="submit" class="btn btn-danger">cancel</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2">Transporter Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>Transporter Name</th>
                                <td>{{ $ewaybill->invoice->transporterDetail->transporter_name }}</td>
                            </tr>
                            <tr>
                                <th>Transporter Doc No</th>
                                <td>{{ $ewaybill->invoice->transporterDetail->transporter_doc_no }}</td>
                            </tr>
                            <tr>
                                <th>Transporter Doc Date</th>
                                <td>{{ \Carbon\Carbon::parse($ewaybill->invoice->transporterDetail->transporter_doc_date)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Transport Mode</th>
                                <td>{{ $ewaybill->invoice->transporterDetail->transport_mode }}</td>
                            </tr>
                            <tr>
                                <th>Transport Distance</th>
                                <td>{{ $ewaybill->invoice->transporterDetail->transport_distance }}</td>
                            </tr>
                            <tr>
                                <th>Vehicle Type</th>
                                <td>{{ $ewaybill->invoice->transporterDetail->vehicle_type }}</td>
                            </tr>
                            <tr>
                                <th>Vehicle No</th>
                                <td>{{ $ewaybill->invoice->transporterDetail->vehicle_number }}</td>
                            </tr>
                            <tr>
                                <th>Delivery Date</th>
                                <td>{{ \Carbon\Carbon::parse($ewaybill->invoice->transporterDetail->delivery_date)->format('d/m/Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection