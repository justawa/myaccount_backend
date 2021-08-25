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
                <div class="panel-heading">Update Purchase</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('purchase.update', $purchase->id) }}">
                        {{ csrf_field() }}

                        {{ method_field('PUT') }}

                        {{-- <div class="form-group{{ $errors->has('party') ? ' has-error' : '' }}">
                            <label for="party" class="col-md-4 control-label">Select Party</label>

                            <div class="col-md-6">
                                <select class="form-control" name="party" required>
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
                        </div> --}}

                        {{-- <hr> --}}

                        <div id="dynamic-block">
                            {{-- <div class="form-group{{ $errors->has('item') ? ' has-error' : '' }}">
                                <label for="item" class="col-md-4 control-label">Item Name</label>

                                <div class="col-md-6">
                                    {{ $purchase->items() }}
                                </div>
                            </div> --}}

                            <div class="form-group{{ $errors->has('quantity') ? ' has-error' : '' }}">
                                <label for="quantity" class="col-md-4 control-label">Quantity</label>

                                <div class="col-md-6">
                                    <input id="quantity" type="text" class="form-control" name="quantity" required value="{{ $purchase->qty }}" required>

                                    @if ($errors->has('quantity'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('quantity') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group{{ $errors->has('bill_no') ? ' has-error' : '' }}">
                            <label for="bill_no" class="col-md-4 control-label">Bill NO.</label>

                            <div class="col-md-6">
                                <input id="bill_no" type="text" class="form-control" name="bill_no" value="{{ $purchase->bill_no }}" required>

                                @if ($errors->has('bill_no'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('bill_no') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('igst') ? ' has-error' : '' }}">
                            <label for="igst" class="col-md-4 control-label">IGST</label>

                            <div class="col-md-6">
                                <input id="igst" type="text" class="form-control" name="igst" value="{{ $purchase->igst }}" required>

                                @if ($errors->has('igst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('igst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('cgst') ? ' has-error' : '' }}">
                            <label for="cgst" class="col-md-4 control-label">CGST</label>

                            <div class="col-md-6">
                                <input id="cgst" type="text" class="form-control" name="cgst" value="{{ $purchase->cgst }}" required>

                                @if ($errors->has('cgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('cgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST</label>

                            <div class="col-md-6">
                                <input id="gst" type="text" class="form-control" name="gst" value="{{ $purchase->gst }}" required>

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">
                            <label for="price" class="col-md-4 control-label">Price</label>

                            <div class="col-md-6">
                                <input id="price" type="text" class="form-control" name="price" value="{{ $purchase->price }}" required>

                                @if ($errors->has('price'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('price') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('date') ? ' has-error' : '' }}">
                            <label for="date" class="col-md-4 control-label">Purchase Date</label>

                            <div class="col-md-6">
                                <input id="date" type="date" class="form-control" name="date" required value="{{ $purchase->bought_on }}" style="line-height: 1.7;" >

                                @if ($errors->has('date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('date') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('amount_paid') ? ' has-error' : '' }}">
                            <label for="amount_paid" class="col-md-4 control-label">Amount Paid</label>

                            <div class="col-md-6">
                                <input id="amount_paid" type="text" class="form-control" name="amount_paid" required value="{{ $purchase->amount_paid }}" >

                                @if ($errors->has('amount_paid'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('amount_paid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
                                    Update Purchase!
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
