@extends('admin.layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Edit GST</div>
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
                    <form method="post" action="{{ route('update.gst', $gst->id) }}">
                        {{ csrf_field() }}
                        <input name="_method" type="hidden" value="PUT">
                        <div class="form-group">
                            <input type="text" name="gst" class="form-control" value="{{ $gst->name }}" placeholder="GST" readonly />
                        </div>
                        <div class="form-group">
                            <input type="text" name="updated_gst" class="form-control" placeholder="Updated GST" />
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
