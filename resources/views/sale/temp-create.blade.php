@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('sale.index') }}">View All Invoices</a>&nbsp;&nbsp;
            <a href="{{ route('sale.create') }}">Create New Sale</a>&nbsp;&nbsp;
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create New Sale</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('sale.store') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('party') ? ' has-error' : '' }}">
                            <label for="party" class="col-md-4 control-label">Select Customer</label>

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

                        <div class="form-group">
                            <label for="group" class="col-md-4 control-label">Select Group</label>
                            <div class="col-md-6">
                                <select class="form-control" id="group">
                                    <option value="0">Select Group</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div id="dynamic-block">
                            <div class="form-group{{ $errors->has('item') ? ' has-error' : '' }}">
                                <label for="item" class="col-md-4 control-label">Item Name</label>

                                <div class="col-md-6">
                                    <select id="item" class="form-control" name="item[]" required>
                                        
                                    </select>

                                    @if ($errors->has('item'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('item') }}</strong>
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

                            <div class="form-group{{ $errors->has('item_discount') ? ' has-error' : '' }}">
                                <label for="item_discount" class="col-md-4 control-label">Discount</label>

                                <div class="col-md-6">
                                    <input id="item_discount" type="text" class="form-control" name="item_discount[]">

                                    @if ($errors->has('item_discount'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('item_discount') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('item_barcode') ? ' has-error' : '' }}">
                                <label for="item_barcode" class="col-md-4 control-label">Barcode</label>

                                <div class="col-md-6">
                                    <input id="item_barcode" type="text" class="form-control" name="item_barcode[]">

                                    @if ($errors->has('item_barcode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('item_barcode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('item_remark') ? ' has-error' : '' }}">
                                <label for="item_remark" class="col-md-4 control-label">Remark</label>

                                <div class="col-md-6">
                                    <textarea id="item_remark" type="text" class="form-control" name="item_remark[]"></textarea>

                                    @if ($errors->has('item_remark'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('item_remark') }}</strong>
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

                        <div class="form-group{{ $errors->has('overall_discount') ? ' has-error' : '' }}">
                            <label for="overall_discount" class="col-md-4 control-label">Discount</label>

                            <div class="col-md-6">
                                <input id="overall_discount" type="text" class="form-control" name="overall_discount" >

                                @if ($errors->has('overall_discount'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('overall_discount') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('discount_type') ? ' has-error' : '' }}">
                            <label for="discount_type" class="col-md-4 control-label">Discount Type</label>

                            <div class="col-md-6">
                                <select id="discount_type" class="form-control" name="discount_type">
                                    <option value="percent">Percentage</option>
                                    <option value="flat">Flat Amount</option>
                                </select>

                                @if ($errors->has('discount_type'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('discount_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group{{ $errors->has('overall_igst') ? ' has-error' : '' }}">
                            <label for="overall_igst" class="col-md-4 control-label">IGST</label>

                            <div class="col-md-6">
                                <input id="overall_igst" type="text" class="form-control" name="overall_igst" required>

                                @if ($errors->has('overall_igst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('overall_igst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('overall_cgst') ? ' has-error' : '' }}">
                            <label for="overall_cgst" class="col-md-4 control-label">CGST</label>

                            <div class="col-md-6">
                                <input id="overall_cgst" type="text" class="form-control" name="overall_cgst" required>

                                @if ($errors->has('overall_cgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('overall_cgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('overall_sgst') ? ' has-error' : '' }}">
                            <label for="overall_sgst" class="col-md-4 control-label">SGST</label>

                            <div class="col-md-6">
                                <input id="overall_sgst" type="text" class="form-control" name="overall_sgst" required>

                                @if ($errors->has('overall_sgst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('overall_sgst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('overall_gst') ? ' has-error' : '' }}">
                            <label for="overall_gst" class="col-md-4 control-label">GST</label>

                            <div class="col-md-6">
                                <input id="overall_gst" type="text" class="form-control" name="overall_gst" required>

                                @if ($errors->has('overall_gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('overall_gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Payment Type</label>
                            <div class="col-md-6">
                                <div class="radio">
                                    <label for="payment_type1">
                                        <input type="radio" name="payment_type" id="payment_type1" value="cash" checked="">
                                        Cash
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="payment_type2">
                                        <input type="radio" name="payment_type" id="payment_type2" value="credit">
                                        Credit
                                    </label>
                                </div>
                                <div class="radio">
                                    <label for="payment_type3">
                                        <input type="radio" name="payment_type" id="payment_type3" value="bank">
                                        Bank
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('amount_paid') ? ' has-error' : '' }}">
                            <label for="amount_paid" class="col-md-4 control-label">Amount Paid</label>

                            <div class="col-md-6">
                                <input id="amount_paid" type="text" class="form-control" name="amount_paid" >

                                @if ($errors->has('amount_paid'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('amount_paid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('overall_remark') ? ' has-error' : '' }}">
                            <label for="overall_remark" class="col-md-4 control-label">Remark</label>

                            <div class="col-md-6">
                                <textarea id="overall_remark" type="text" class="form-control" name="overall_remark[]"></textarea>

                                @if ($errors->has('overall_remark'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('overall_remark') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
                                    Create New Sale!
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

            $(document).on("change", "#group", function(){
                var group = $("#group option:selected").val();

                if(group > 0){
                    $.ajax({
                        method: "GET",
                        url: "{{ route('api.fetch.item') }}",
                        data: { group: group },
                        success: function(response){
                            
                            var arr = JSON.parse(response);
                            // var responseKey = Object.keys(response);
                            for($i=0; $i<arr.length; $i++){
                                $("#item").append(`<option value="${arr[$i].id}">${arr[$i].name}</option>`);
                            }
                        }
                    });
                }else{
                    alert("Please select a valid group");
                }
            });

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

                    <div class="form-group{{ $errors->has('item_discount') ? ' has-error' : '' }}">
                        <label for="item_discount" class="col-md-4 control-label">Discount</label>

                        <div class="col-md-6">
                            <input id="item_discount" type="text" class="form-control" name="item_discount[]">

                            @if ($errors->has('item_discount'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('item_discount') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('item_barcode') ? ' has-error' : '' }}">
                        <label for="item_barcode" class="col-md-4 control-label">Barcode</label>

                        <div class="col-md-6">
                            <input id="item_barcode" type="text" class="form-control" name="item_barcode[]">

                            @if ($errors->has('item_barcode'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('item_barcode') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('item_remark') ? ' has-error' : '' }}">
                        <label for="item_remark" class="col-md-4 control-label">Remark</label>

                        <div class="col-md-6">
                            <textarea id="item_remark" type="text" class="form-control" name="item_remark[]"></textarea>

                            @if ($errors->has('item_remark'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('item_remark') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                `);
            });
        });
    </script>
@endsection
