@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('purchase.index') }}">View All Purchase</a>&nbsp;&nbsp;
            <a href="{{ route('purchase.create') }}">Create New Purchase</a>&nbsp;&nbsp;
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create New Purchase</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('purchase.store') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('party') ? ' has-error' : '' }}">
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
                        </div>

                        <hr>

                        <div id="dynamic-block">
                            <div class="form-group{{ $errors->has('item') ? ' has-error' : '' }}">
                                <label for="item" class="col-md-4 control-label">Item Name</label>

                                <div class="col-md-6">
                                    <select id="item" class="form-control" name="item[]" required>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('item'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('item') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('quantity') ? ' has-error' : '' }}">
                                <label for="quantity" class="col-md-4 control-label">Quantity</label>

                                <div class="col-md-6">
                                    <input id="quantity" type="text" class="form-control" name="quantity[]" required>

                                    @if ($errors->has('quantity'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('quantity') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">
                                <label for="price" class="col-md-4 control-label">Price</label>

                                <div class="col-md-6">
                                    <input id="price" type="text" class="form-control" name="price[]" required>

                                    @if ($errors->has('price'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('price') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('item_discount') ? ' has-error' : '' }}">
                                <label for="item_discount" class="col-md-4 control-label">Discount</label>

                                <div class="col-md-6">
                                    <input id="item_discount" type="text" class="form-control" name="item_discount[]" required>

                                    @if ($errors->has('item_discount'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('item_discount') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" id="add-more-items" class="btn btn-success">+ Add More</button>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group{{ $errors->has('bill_no') ? ' has-error' : '' }}">
                            <label for="bill_no" class="col-md-4 control-label">Bill NO.</label>

                            <div class="col-md-6">
                                <input id="bill_no" type="text" class="form-control" name="bill_no" value="{{ old('bill_no') }}" required>

                                @if ($errors->has('bill_no'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('bill_no') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('igst') ? ' has-error' : '' }}">
                            <label for="igst" class="col-md-4 control-label">IGST</label>

                            <div class="col-md-6">
                                <input id="igst" type="text" class="form-control" name="igst" value="{{ old('igst') }}" required>

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
                                <input id="cgst" type="text" class="form-control" name="cgst" value="{{ old('cgst') }}" required>

                                @if ($errors->has('cgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('cgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('sgst') ? ' has-error' : '' }}">
                            <label for="sgst" class="col-md-4 control-label">SGST</label>

                            <div class="col-md-6">
                                <input id="sgst" type="text" class="form-control" name="sgst" value="{{ old('sgst') }}" required>

                                @if ($errors->has('sgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST</label>

                            <div class="col-md-6">
                                <input id="gst" type="text" class="form-control" name="gst" value="{{ old('gst') }}" required>

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        {{-- <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">
                            <label for="price" class="col-md-4 control-label">Total Amount</label>

                            <div class="col-md-6">
                                <input id="price" type="text" class="form-control" name="price" value="{{ old('price') }}" required>

                                @if ($errors->has('price'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('price') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group{{ $errors->has('date') ? ' has-error' : '' }}">
                            <label for="date" class="col-md-4 control-label">Purchase Date</label>

                            <div class="col-md-6">
                                <input id="date" type="date" class="form-control" name="date" required value="{{ old('date') }}" style="line-height: 1.7;" >

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
                                <input id="amount_paid" type="text" class="form-control" name="amount_paid" required value="{{ old('amount_paid') }}" >

                                @if ($errors->has('amount_paid'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('amount_paid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('amount_remaining') ? ' has-error' : '' }}">
                            <label for="amount_remaining" class="col-md-4 control-label">Amount Remaining</label>

                            <div class="col-md-6">
                                <input id="amount_remaining" type="text" class="form-control" name="amount_remaining" required value="{{ old('amount_remaining') }}" >

                                @if ($errors->has('amount_remaining'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('amount_remaining') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('remark') ? ' has-error' : '' }}">
                            <label for="remark" class="col-md-4 control-label">Remark</label>

                            <div class="col-md-6">
                                <textarea id="remark" type="text" class="form-control" name="remark" ></textarea>

                                @if ($errors->has('remark'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('remark') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('barcode') ? ' has-error' : '' }}">
                            <label for="barcode" class="col-md-4 control-label">Barcode</label>

                            <div class="col-md-6">
                                <input id="barcode" type="text" class="form-control" name="barcode" required value="{{ old('barcode') }}">

                                @if ($errors->has('barcode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('barcode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('expiry') ? ' has-error' : '' }}">
                            <label for="expiry" class="col-md-4 control-label">Expiry</label>

                            <div class="col-md-6">
                                <input id="expiry" type="date" class="form-control" name="expiry" required value="{{ old('expiry') }}" style="line-height: 1.7;">

                                @if ($errors->has('expiry'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('expiry') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
                                    Create New Purchase!
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
        $(document).ready(function (){

            $(document).on("click", "#add-more-items", function(){
                $("#dynamic-block").append(`
                    <div class="form-group{{ $errors->has('item') ? ' has-error' : '' }}">
                        <label for="item" class="col-md-4 control-label">Item Name</label>

                        <div class="col-md-6">
                            <select id="item" class="form-control" name="item[]" required>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>

                            @if ($errors->has('item'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('item') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('quantity') ? ' has-error' : '' }}">
                        <label for="quantity" class="col-md-4 control-label">Quantity</label>

                        <div class="col-md-6">
                            <input id="quantity" type="text" class="form-control" name="quantity[]" required>

                            @if ($errors->has('quantity'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('quantity') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">
                        <label for="price" class="col-md-4 control-label">Price</label>

                        <div class="col-md-6">
                            <input id="price" type="text" class="form-control" name="price[]" required>

                            @if ($errors->has('price'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('price') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('item_discount') ? ' has-error' : '' }}">
                        <label for="item_discount" class="col-md-4 control-label">Discount</label>

                        <div class="col-md-6">
                            <input id="item_discount" type="text" class="form-control" name="item_discount[]" required>

                            @if ($errors->has('item_discount'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('item_discount') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                `);
            });
        });
    </script>
@endsection
