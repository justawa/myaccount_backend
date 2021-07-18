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
    <form method="POST" action="{{ route('store.purchase.order') }}" id="create-purchase">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <select class="form-control" id="party" name="party" required>
                    <option value="0">Choose a party</option>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('party.create') }}">Add Party</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Purchase Order Date</label>
                <input type="date" class="form-control" id="purchase_order_date" name="purchase_order_date" style="line-height: 1;" />
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Bill No.</label>
                <input id="bill_no" type="text" class="form-control" name="bill_no" value="{{ old('bill_no') }}" required>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-3">
            <a href="{{ route('group.create') }}" class="btn btn-primary">Add Group</a>
        </div>
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        {{-- <th>#</th> --}}
                        <th>Group</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="dynamic-body">
                    <tr>
                        {{-- <td>1</td> --}}
                        <td>
                            <select class="form-control group" required>
                                <option value="0">Select Group</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control item" name="item[]" required>        
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control quantity" name="quantity[]" required>
                        </td>
                        {{-- <td>
                            <input type="text" class="form-control price" name="price[]" id="price" readonly required>
                        </td>
                        <td>
                            <span class="amount"></span>
                        </td>
                        <td>
                            <span class="gst"></span>
                        </td>
                        <td>
                            <input type="hidden" name="calculated_gst[]" class="calculated-gst-input">
                            <span class="calculated-gst"></span>
                        </td> --}}
                        <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <button type="button" id="add-more-items" class="btn btn-success">+ Add More</button>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <textarea id="overall_remark" type="text" class="form-control" name="overall_remark" placeholder="Remarks"></textarea>
            </div>
        </div>
        <div class="col-md-4 col-md-offset-4">
            <div class="form-group">
                <label>Item Total Amount</label>
                <input type="text" class="form-control" name="item_total_amount" id="item_total_amount" readonly />
            </div>
            <div class="form-group">
                <label>Item Total GST</label>
                <input type="text" class="form-control" name="item_total_gst" id="item_total_gst" readonly />
            </div>
            <div class="form-group" id="additional-charges-outer">
                <div id="additional-charges-inner">
                    <label>Additional Charges <button type="button" id="btn-additional-charge" class="btn btn-link">Add Charges</button></label>
                    @php
                        if (Session::has("additional_charges.labour_charge")) {
                            $labour_charges = session('additional_charges.labour_charge');
                            session()->forget('additional_charges.labour_charge');
                        } else {
                            $labour_charges = 0;
                        }

                        if (Session::has('additional_charges.freight_charge')) {
                            $freight_charges = session('additional_charges.freight_charge');
                            session()->forget('additional_charges.freight_charge');
                        } else {
                            $freight_charges = 0;
                        }

                        if (Session::has('additional_charges.transport_charge')) {
                            $transport_charges = session('additional_charges.transport_charge');
                            session()->forget('additional_charges.transport_charge');
                        } else {
                            $transport_charges = 0;
                        }

                        if (Session::has('additional_charges.insurance_charge')) {
                            $insurance_charges = session('additional_charges.insurance_charge');
                            session()->forget('additional_charges.insurance_charge');
                        } else {
                            $insurance_charges = 0;
                        }
                    @endphp
                    <input type="hidden" name="labour_charges" value="{{ $labour_charges }}" />
                    <p style="color: #666;">Labour: Rs {{ $labour_charges }}</p>
                    <input type="hidden" name="freight_charges" value="{{ $freight_charges }}" />
                    <p style="color: #666;">Freight: Rs {{ $freight_charges }}</p>
                    <input type="hidden" name="transport_charges" value="{{ $transport_charges }}" />
                    <p style="color: #666;">Transport: Rs {{ $transport_charges }}</p>
                    <input type="hidden" name="insurance_charges" value="{{ $insurance_charges }}" />
                    <p style="color: #666;">Insurance: Rs {{ $insurance_charges }}</p>
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-link" id="btn-transporter-detail">Add Transporter Details</button>                
            </div>
            <div class="form-group">
                <label>Total Amount</label>
                <input type="text" id="total_amount" name="total_amount" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Amount Paid</label>
                <input id="amount_paid" type="text" class="form-control" name="amount_paid" required>
            </div>
            <div class="form-group">
                <label>Amount Remaining</label>
                <input id="amount_remaining" type="text" class="form-control" name="amount_remaining" readonly>
            </div>
            <div class="form-group">
                <label>Discount</label>
                <input id="overall_discount" type="text" class="form-control" name="overall_discount" >
            </div>
        </div>
    </div> --}}
    <div class="form-group">
        <div class="col-md-8 col-md-offset-4">
            <button type="submit" class="btn btn-primary">
                Create New Purchase Order
            </button>
        </div>
    </div>
    </form>
</div>


<div class="modal" id="add-transporter-detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Transporter Details</h4>
            </div>
            <div class="modal-body">
                <form id="form-transporter-detail">
                    {{-- <input type="hidden" name="item_id" id="price_item_id" value="" /> --}}
                    <div class="form-group">
                        <label>Vehicle Type</label>
                        <input type="text" class="form-control" id="vehicle_type" placeholder="Vehicle Type" />
                    </div>
                    <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" class="form-control" id="vehicle_number" placeholder="Vehicle Number" />
                    </div>
                    <div class="form-group">
                        <label>Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" placeholder="Delivery Date" style="line-height: 1;" />
                    </div>
                    {{-- <div class="form-group">
                        <label>Insurance Charge</label>
                        <input type="text" class="form-control" id="insurance_charge" placeholder="Insurance Charge" />
                    </div> --}}
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <p id="transporter-detail-error"></p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>

        $(document).ready(function (){

            $(document).on("submit", "#create-purchase", function(e){
                // e.preventDefault();
                var validation = true;

                var submit_quantities = $(".quantity");
                var submit_items = $(".item");

                var submit_party = $("#party option:selected").val();

                for(var j = 0; j < submit_quantities.length; j++){
                    if(submit_quantities[j].value == ""){
                        validation = false;
                    }
                }

                for(var k = 0; k < submit_items.length; k++){
                    if(submit_items[k].value == ""){
                        validation = false;
                    }
                }

                if(submit_party == 0){
                    validation = false;
                }


                if(validation) {
                    $("#create-purchase").trigger( "submit" );

                    // alert('submit');
                } else {
                    e.preventDefault();
                    alert("Please fill up all required fields.");
                }

            });

            $(document).on("change", ".group", function(){
                var group = $("option:selected", this).val();
                var tr = $(this).closest('tr');

                if(group > 0){
                    $.ajax({
                        type: "GET",
                        url: "{{ route('api.fetch.item') }}",
                        data: { group: group },
                        success: function(response){
                            
                            var arr = JSON.parse(response);
                            // console.log(response);
                            // var responseKey = Object.keys(response);
                            tr.find(".item").append(`<option value="0">Select Item</option>`);
                            
                            for($i=0; $i<arr.length; $i++){
                                tr.find(".item").append(`<option data-hsc="${arr[$i].hsc_code}" data-sac="${arr[$i].sac_code}" data-gst="${arr[$i].gst}" value="${arr[$i].id}">${arr[$i].name}</option>`);
                            }
                        }
                    });
                }else{
                    alert("Please select a valid group");
                    tr.find(".item").html('');
                    tr.find(".hsc").text('');
                    tr.find(".sac").text('');
                    tr.find(".gst").text('');
                }
            });

            $(document).on("click", "#add-more-items", function(){
                $("#dynamic-body").append(
                    `<tr>
                        <td>
                            <select class="form-control group" required>
                                <option value="0">Select Group</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control item" name="item[]" required>        
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control quantity" name="quantity[]" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger delete-row" data-item="" style="padding: 0; background: transparent; color: red; border: 0;"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>`
                );
            });

            $(document).on("click", ".delete-row", function(){

                $(this).parent().parent().remove();

            });

        });
    </script>
@endsection