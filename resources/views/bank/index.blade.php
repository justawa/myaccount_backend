@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('bank') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">View Bank Details</div>

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
                                <th>Bank Name</th>
                                <th>Account Number</th>
                                <th>Branch</th>
                                <th>IFSC</th>
                                <th>Classification</td>
                                <th>Type</th>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            @if( count( $banks ) > 0 )
                                @php $count = 1; @endphp
                                @foreach( $banks as $bank )
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td><a href="{{ route('bank.edit', [$bank->id]) }}">{{ $bank->name }}</a></td>
                                        <td>{{ $bank->account_no }}</td>
                                        <td>{{ $bank->branch }}</td>
                                        <td>{{ $bank->ifsc }}</td>
                                        <td>{{ $bank->classification }}</td>
                                        <td>{{ $bank->type }}</td>
                                        <td><a href="{{ route('edit.bank.opening.balance', $bank->id) }}">Edit Opening Balance</a></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">No Data</td>
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
