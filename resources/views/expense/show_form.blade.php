@extends('layouts.dashboard')

@section('content')
<div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Add New Expense</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('store.expense') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('party') ? ' has-error' : '' }}">
                            <label for="party" class="col-md-4 control-label">Party</label>

                            <div class="col-md-6">
                                <select class="form-control" name="party" id="party" required>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}">{{ $party->name }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('party'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('party') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('bill') ? ' has-error' : '' }}">
                            <label for="bill" class="col-md-4 control-label">Bill No.</label>

                            <div class="col-md-6">
                                <input id="bill" type="text" class="form-control" name="bill" required>

                                @if ($errors->has('bill'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('bill') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('tax_info') ? ' has-error' : '' }}">
                            <label for="tax_info" class="col-md-4 control-label">Tax Info</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="taxed" id="taxed1" value="tds"> TDS
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="taxed" id="taxed2" value="gst" checked> GST
                                </label>
                            </div>
                        </div>

                        <div id="tds-block" style="display: none;">
                            <div class="form-group">
                                <label for="tds" class="col-md-4 control-label">TDS (%)</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="tds_percentage" id="tds" />
                                </div>
                            </div>
                        </div>

                        <div id="gst-block">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Is it eligible for input in GST?</label>
                                <div class="col-md-6">
                                    <label class="radio-inline">
                                        <input type="radio" name="gst_eligible" id="gst_eligible1" value="yes"> Yes
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="gst_eligible" id="gst_eligible2" value="no" checked> No
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="gst" class="col-md-4 control-label">GST (%)</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="gst_percentage" id="gst" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Add Expense
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('input[name="taxed"]').on('change', function(){
            var taxed = $(this).val();
            if (taxed == 'gst') {
                $("#gst-block").show();
                $("#tds-block").hide();
            } else if(taxed == 'tds') {
                $("#gst-block").hide();
                $("#tds-block").show();
            } else {
                $("#gst-block").hide();
                $("#tds-block").hide();
            }
        });
    </script>
@endsection