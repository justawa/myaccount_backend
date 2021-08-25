@extends('layouts.dashboard')

@section('content')
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
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Party Report</div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="10">
                                    {{ auth()->user()->profile->name }}
                                </th>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <th>Particulars</th>
                                <th>Vch Type</th>
                                <th>Vch No.</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($rows) > 0)
                                @foreach($rows as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                    <td>{{ $row['particulars'] }}</td>
                                    <td>{{ $row['voucher_type'] }}</td>
                                    <td>{{ $row['voucher_no'] }}</td>
                                    <td>{{ $row['value'] }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">No Data</td>
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

@section('scripts')
<script>
    $(document).ready(function () {

        

    });
</script>
@endsection