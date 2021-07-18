@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('party.index') }}">View All Parties</a>&nbsp;&nbsp;
            <a href="{{ route('party.create') }}">Create New Party</a>&nbsp;&nbsp;
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">View Party</div>
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
                                <th>Contact Person Name</th>
                                <th>Company Name</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>GST</td>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $party->contact_person_name }}</td>
                                <td>{{ $party->name }}</td>
                                <td>{{ $party->phone }}</td>
                                <td>{{ $party->address }}, {{ $party->city }}, {{ $party->state }}</td>
                                <td>{{ $party->gst }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
