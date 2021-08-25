@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Upload Inventory File</div>

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

                    @if ( $errors->any() )
                        <div class="alert alert-danger">
                            <ul>
                               @foreach( $errors->all() as $error ) 
                                    <li>{{ $error }}</li>
                               @endforeach
                            </ul>                            
                        </div>
                    @endif

                    <form method="POST" action="{{ route('post.import.inventory') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <label>Inventory File</label>
                        <div class="form-group">
                            <input type="file" class="form-control" name="inventory_file" style="height: auto;" />
                        </div>
                        <button class="btn btn-success">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
