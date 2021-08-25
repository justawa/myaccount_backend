@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Export Excel</div>

                <div class="panel-body"> 

                    <form action="{{ route('export.excel') }}" method="GET">
                        <div class="row">
                            <div class="col-md-5">
                                <label>From Date</label>
                                <input type="text" class="form-control custom-date" placeholder="DD/MM/YYYY" name="from_date" />
                            </div>
                            <div class="col-md-5">
                                <label>To Date</label>
                                <input type="text" class="form-control custom-date" placeholder="DD/MM/YYYY" name="to_date" />
                            </div>
                            <div class="col-md-2">
                                <label style="visibility: hidden">Search</label>
                                <button class="btn btn-success">Search</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection