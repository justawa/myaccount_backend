@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Add New Unit</div>

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

                    <form method="post" action="{{ route('measuringunit.store') }}">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label>Measuring Unit</label>
                            <input type="text" class="form-control" id="measuring_unit" name="name" placeholder="Measuring Unit" />
                        </div>
                        <button type="submit" class="btn btn-success btn-mine">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection