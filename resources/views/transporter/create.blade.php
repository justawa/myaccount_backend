@extends('layouts.dashboard')

@section('content')
<div class="container">
    {{-- <div class="row">
        <div class="col-md-12">
            <a href="{{ route('group.index') }}">View All Groups</a>&nbsp;&nbsp;
            <a href="{{ route('group.create') }}">Create New Group</a>&nbsp;&nbsp;
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Add New Transporter</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('transporter.store') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('company_name') ? ' has-error' : '' }}">
                            <label for="company_name" class="col-md-4 control-label">Company Name</label>

                            <div class="col-md-6">
                                <input id="company_name" type="text" class="form-control" name="company_name" value="{{ old('company_name') }}" required>

                                @if ($errors->has('company_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('company_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('contact_person') ? ' has-error' : '' }}">
                            <label for="contact_person" class="col-md-4 control-label">Contact Person</label>

                            <div class="col-md-6">
                                <input id="contact_person" type="text" class="form-control" name="contact_person" value="{{ old('contact_person') }}" required>

                                @if ($errors->has('contact_person'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('contact_person') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST Registered?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="is_registered" id="is_registered1" value="1"> Registered
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="is_registered" id="is_registered2" value="0" checked> Not Registered
                                </label>
                            </div>
                        </div>

                        <div id="gst-block" class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}" style="display: none;">
                            <label for="gst" class="col-md-4 control-label">GST No.</label>

                            <div class="col-md-6">
                                <input id="gst" class="form-control" name="gst" />

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="phone" class="col-md-4 control-label">Phone</label>

                            <div class="col-md-6">
                                <input id="phone" type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>

                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                            <label for="address" class="col-md-4 control-label">Address</label>

                            <div class="col-md-6">
                                <textarea id="address" type="text" class="form-control" name="address" required></textarea>

                                @if ($errors->has('address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
                                    Add Transporter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script>
    $('input[name="is_registered"]').on('change', function(){
        var is_registered = $(this).val();
        if (is_registered == 1) {
            $("#gst-block").show();
        } else {
            $("#gst-block").hide();
        }
    });
</script>
@endsection
