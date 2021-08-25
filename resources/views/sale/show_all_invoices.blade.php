@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('show-all-invoices') !!}
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View All Sales</div>

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
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Buyer Name</th>
                                <th>Amount Paid</th>
                                <th>Amount Remaining</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($invoices) > 0)
                            @php $count = 1 @endphp
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>
                                    @if($invoice->invoice_prefix != null)
                                        {{ $invoice->invoice_prefix }}
                                    @endif
                                        {{ $invoice->invoice_no }}
                                    @if($invoice->invoice_suffix != null)
                                        {{ $invoice->invoice_suffix }}
                                    @endif
                                </td>
                                <td>{{ $invoice->party->name }}</td>
                                <td>{{ $invoice->amount_paid }}</td>
                                <td>{{ $invoice->amount_remaining }}</td>
                                <td><a href="{{ route('edit.invoice.form', $invoice->id) }}">Edit</a></td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Invoices</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
