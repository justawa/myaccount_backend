@extends('layouts.dashboard')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Invoices</div>

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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Invoice Date</th>
                                <th>Party Name</th>
                                <th>Invoice Value</th>
                                <th>GST Value</th>
                                <th>Taxable Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($invoices) > 0)
                            @php $count = 1 @endphp
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>
                                    <a href="{{ route('edit.invoice.form',$invoice->id) }}">
                                    @if($invoice->invoice_prefix != null)
                                    {{ $invoice->invoice_prefix }}
                                    @endif
                                    {{ $invoice->invoice_no }}
                                    @if($invoice->invoice_suffix != null)
                                    {{ $invoice->invoice_suffix }}
                                    @endif
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                                <td>{{ $invoice->party->name }}</td>
                                <td>{{ $invoice->total_amount - $invoice->gst }}</td>
                                <td>{{ $invoice->gst }}</td>
                                <td>{{ $invoice->total_amount }}</td>
                                <td>
                                    @if(!$invoice->transporterDetail)
                                    <button class="btn btn-link add-transporter-detail" data-invoice="{{ $invoice->id }}">Create E-way Bill</button>
                                    @elseif($ewaybill = $invoice->eWayBills()->where('status', 1)->orderBy('id', 'desc')->first())
                                    <a class="btn btn-link" href="{{ route('eway.bill.show', $ewaybill->id) }}">View E-way Bill</a>
                                    @else
                                    <button class="btn btn-link add-transporter-detail" data-invoice="{{ $invoice->id }}">Create E-way Bill</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Invoices</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- <div class="modal" id="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Details</h4>
            </div>
            <div class="modal-body">
                <form id="form-transporter-detail" method="post" action="{{ route('save.invoice.transport.detail') }}">
                    {{ csrf_field() }}
                    <input type="hidden" id="invoice_id" name="invoice_id" />
                    <div class="form-group">
                        <label>Select Transporter</label>
                        <select class="form-control" id="transporter" name="transporter">
                            <option value="0">Select Transporter</option>
                            @foreach($transporters as $transporter)
                                <option value="{{ $transporter->id }}">{{ $transporter->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transporter Name</label>
                        <input type="text" class="form-control" id="transporter_name" name="transporter_name" placeholder="Transporter Name" />
                    </div>
                    <div class="form-group">
                        <label>Transporter Doc No</label>
                        <input type="text" class="form-control" id="transporter_doc_no" name="transporter_doc_no" placeholder="Transporter Doc No" />
                    </div>
                    <div class="form-group">
                        <label>Transport Doc Date</label>
                        <input type="date" class="form-control" id="transport_doc_date" name="transport_doc_date" placeholder="Transport Doc Date" style="line-height: 1;" />
                    </div>
                    <div class="form-group">
                        <label>Transport Mode</label>
                        <input type="text" class="form-control" id="transport_mode" name="transport_mode" placeholder="Transport Mode" />
                    </div>
                    <div class="form-group">
                        <label>Transport Distance</label>
                        <input type="text" class="form-control" id="transport_distance" name="transport_distance" placeholder="Transport Distance" />
                    </div>
                    <div class="form-group">
                        <label>Vehicle Type</label>
                        <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" placeholder="Vehicle Type" />
                    </div>
                    <div class="form-group">
                        <label>Vehicle Number</label>
                        <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" placeholder="Vehicle Number" />
                    </div>
                    <div class="form-group">
                        <label>Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" placeholder="Delivery Date" style="line-height: 1;" />
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </form>
                <p id="transporter-detail-error"></p>
            </div>
        </div>
    </div>
</div> --}}


<div class="modal" id="add-transport-detail-and-charge-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Details</h4>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs">
                    <li class="active" id="transporter-details-li"><a data-toggle="tab" href="#transporter-details-tab">Transporter Details</a></li>
                    {{-- <li id="additional-charges-li"><a data-toggle="tab" href="#additional-charges-tab">Additional Charges</a></li> --}}
                </ul>
                <div class="tab-content" style="padding-top: 15px;">
                    <div id="transporter-details-tab" class="tab-pane fade in active">
                        <form id="form-transporter-detail" method="post" action="{{ route('save.invoice.transport.detail') }}">
                            {{ csrf_field() }}
                            <input type="hidden" class="invoice_id" name="invoice_id" />
                            {{-- <div class="form-group">
                                <label>Select Transporter</label>
                                <select class="form-control" id="transporter" name="transporter">
                                    <option value="0">Select Transporter</option>
                                    @foreach($transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{ $transporter->company_name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="form-group">
                                <label>Transporter Id</label>
                                <input type="text" class="form-control" id="transporter_id" name="transporter_id" placeholder="Transporter Id" required />
                            </div>
                            <div class="form-group">
                                <label>Transporter Name</label>
                                <input type="text" class="form-control" id="transporter_name" name="transporter_name" placeholder="Transporter Name" required />
                            </div>
                            <div class="form-group">
                                <label>Transporter Doc No</label>
                                <input type="text" class="form-control" id="transporter_doc_no" name="transporter_doc_no" placeholder="Transporter Doc No" required />
                            </div>
                            <div class="form-group">
                                <label>Transport Doc Date</label>
                                <input type="date" class="form-control" id="transport_doc_date" name="transport_doc_date" placeholder="Transport Doc Date" style="line-height: 1;" />
                            </div>
                            <div class="form-group">
                                <label>Transport Mode</label>
                                {{-- <input type="text" class="form-control" id="transport_mode" name="transport_mode" placeholder="Transport Mode" required /> --}}
                                <select class="form-control" id="transport_mode" name="transport_mode">
                                    <option value="1">Road</option>
                                    <option value="2">Air</option>
                                    <option value="3">Ship</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Transport Distance</label>
                                <input type="text" class="form-control" id="transport_distance" name="transport_distance" placeholder="Transport Distance" required />
                            </div>
                            <div class="form-group">
                                <label>Vehicle Type</label>
                                {{-- <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" placeholder="Vehicle Type" /> --}}
                                <select class="form-control" id="vehicle_type" name="vehicle_type" required>
                                    <option value="R">Regular</option>
                                    <option value="O">Over Dimension Cargo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Vehicle Number</label>
                                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" placeholder="Vehicle Number" required />
                            </div>
                            <div class="form-group">
                                <label>Delivery Date</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date" placeholder="Delivery Date" style="line-height: 1;" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required />
                            </div>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                        <p id="transporter-detail-error"></p>
                    </div>
                    {{-- <div id="additional-charges-tab" class="tab-pane">
                        <form id="form-additional-charge" method="post" action="{{ route('save.invoice.additional.charges') }}">
                            {{ csrf_field() }}
                            <input type="hidden" class="invoice_id" name="invoice_id" />
                            <div class="form-group">
                                <label>Labour Charge</label>
                                <input type="text" class="form-control" id="labour_charge" name="labour_charge" placeholder="Labour Charge" />
                            </div>
                            <div class="form-group">
                                <label>Transport Charge</label>
                                <input type="text" class="form-control" id="transport_charge" name="transport_charge" placeholder="Transport Charge" />
                            </div>
                            <div class="form-group">
                                <label>Insurance Charge</label>
                                <input type="text" class="form-control" id="insurance_charge" name="insurance_charge" placeholder="Insurance Charge" />
                            </div>
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <label>GST (%)</label>
                                    <input type="text" class="form-control" id="gst_percentage" name="gst_percentage" placeholder="GST" />
                                </div>
                                <div class="col-md-6">
                                    <label>Calculated GST Charge</label>
                                    <input type="text" class="form-control" id="calculated_gst_charge" name="calculated_gst_charge" placeholder="Calculated GST" readonly />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-mine">Submit</button>
                        </form>
                        <p id="additional-charge-error"></p>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
    <script>
        var status_of_registration = '{{ auth()->user()->profile->registered }}';
        
        $(document).ready(function() {

            $(".add-transporter-detail").on("click", function () {
                var invoice_id = $(this).data('invoice');
                $(".invoice_id").val(invoice_id);
                $("#add-transport-detail-and-charge-modal").modal('show');
                console.log(invoice_id);
            });

            $(document).on("keyup", "#labour_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#freight_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#transport_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#insurance_charge", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });

            $(document).on("keyup", "#gst_percentage", function(){
                calculated_gst_charge = calculate_additional_charges_gst();

                $("#calculated_gst_charge").val(calculated_gst_charge);
            });
        });

        function roundToSomeNumber(num) {
            
            num = parseFloat(num);
            
            // return num.toFixedDown(2);

            return num.toFixed(2);
            
        }

        function calculate_additional_charges_gst() {
            var labour_charge = $("#labour_charge").val();
            // var freight_charge = $("#freight_charge").val();
            var transport_charge = $("#transport_charge").val();
            var insurance_charge = $("#insurance_charge").val();
            var gst_percentage = $("#gst_percentage").val();

            if(labour_charge == ''){
                labour_charge = 0;
            }
            // if(freight_charge == ''){
            //     freight_charge = 0;
            // }
            if(transport_charge == ''){
                transport_charge = 0;
            }
            if(insurance_charge == ''){
                insurance_charge = 0;
            }
            
            if(gst_percentage == ''){
                gst_percentage = 0;
            }
            else if(gst_percentage.toLowerCase() == 'exempt' || gst_percentage.toLowerCase() == 'nil' || gst_percentage.toLowerCase() == 'export'){
                gst_percentage = 0;
            } else {
                if(status_of_registration == 0 || status_of_registration == 3){
                    gst_percentage = 0;
                }
            }

            var total_charge_amount = parseFloat(labour_charge) + parseFloat(transport_charge) + parseFloat(insurance_charge);

            var calculated_gst_charge = total_charge_amount * (gst_percentage / 100);

            calculated_gst_charge = roundToSomeNumber(calculated_gst_charge);

            return calculated_gst_charge;
        }
    </script>
@endsection
