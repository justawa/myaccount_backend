@extends('layouts.dashboard')

@section('content')

{{-- {!! Breadcrumbs::render('invoice-detail-debit-note', request()->segment(2)) !!} --}}

    <form method="post" action="#">
        {{ csrf_field() }}
        <div class="container" id="printable">
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
            <div class="row" style="margin-bottom: 20px;">
                {{-- <div class="col-md-6">
                    @if($invoice->amount_type == 'exclusive') <strong>Invoice is Excl of Taxes</strong> @endif
                    @if($invoice->amount_type == 'inclusive') <strong>Invoice is Incl of Taxes</strong> @endif
                </div> --}}
                <div class="col-md-6"><strong>Invoice no</strong> : @if($invoice->invoice_no != null) {{ $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix }} @else {{ $invoice->id }} @endif</div>
                <div class="col-md-6">
                    <a href="{{ route('sale.debit.note.edit', $note_no) }}">Edit Note</a>
                </div>
            </div>
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <strong>{{ $invoice->party->name }}</strong>
                </div>
                <div class="col-md-3">
                    <strong>Invoice Date</strong> : {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                </div>
                <div class="col-md-3">
                    <strong>Due Date</strong> : {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                </div>
            </div>
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <strong>Note No</strong> : {{ $note_no }}
                </div>
                {{-- <div class="col-md-6">
                    <strong>Note Date</strong> : <input type="text" class="form-control custom_date" name="note_date" placeholder="DD/MM/YYYY" />
                </div> --}}
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Revised Price</th>
                            {{-- <th>Calculated Price</th> --}}
                            <th>Revised GST</th>
                            {{-- <th>Calculated GST</th> --}}
                            <th colspan="2" @if(auth()->user()->profile->inventory_type == "without_inventory") style="visibility:hidden" @endif>Revised Qty</th>
                            {{-- <th>Calculated Qty</th> --}}
                            {{-- <th>Revised Discount</th> --}}
                            {{-- <th>Value</th> --}}
                            {{-- <th class="text-center">Action</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1; $items_price = 0; $items_gst = 0; $items_total = 0; $items_discount = 0;  @endphp
                        @if(count($debit_notes) > 0)
                            @foreach($debit_notes as $debit_note)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $debit_note->item_name }}</td>
                                
                                <td>{{ $debit_note->price ?? 0 }}</td>
                                {{-- <td>{{ $sale->price - $sale->price_difference }}</td> --}}
                                
                                
                                <td>{{ $debit_note->gst ?? 0 }}</td>
                                {{-- <td>{{ $sale->gst - $sale->gst_percent_difference }}</td> --}}
                                
                                
                                <td>@if(auth()->user()->profile->inventory_type != "without_inventory") {{ $debit_note->quantity ?? 0 }} @endif</td>
                                <td>{{ $credit_note->original_unit }}</td>
                                {{-- <td>{{ $sale->quantity - $sale->quantity_difference }}</td> --}}


                                {{-- <td>{{ $debit_note->discount ?? 0 }}</td> --}}
                                {{-- <td class="text-center">
                                    <form  method="post" action="{{ route('sale.delete.debit.note') }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="row_id" value="{{ $sale->id }}" />
                                        <button class="btn btn-link" style="color: red;">Delete</button>
                                    </form>
                                </td> --}}
                            </tr>
                                @php
                                    $price = $debit_note->price ?? 0;
                                    $gst = $debit_note->gst ?? 0;
                                    $qty = $debit_note->original_qty ?? 0;
                                    $discount = $debit_note->discount ?? 0;

                                    //$calculated_discount = ($price * $qty) * $discount/100;
                                    
                                    $items_price += $price * $qty;
                                    //$items_discount += $calculated_discount;
                                    $items_gst += $gst;
                                    // $items_total += ((($price * $qty) - $calculated_discount)  + $gst);
                                    $items_total += (($price * $qty)  + $gst);
                                @endphp
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Reason</th>
                            <th colspan="3">
                                {{ $debit_notes->first()->reason }}</th>
                        </tr>
                        <tr>
                            <th colspan="4">Item Value</th>
                            <th colspan="3">{{ $debit_notes->first()->taxable_value }}</th>
                        </tr>
                        {{-- <tr>
                            <th colspan="4">Discount</th>
                            <th colspan="3">{{ $items_discount }}</th>
                        </tr> --}}
                        <tr>
                            <th colspan="4">GST</th>
                            <th colspan="3">{{ $debit_notes->first()->gst_value }}</th>
                        </tr>
                        <tr>
                            <th colspan="4">Note Value</th>
                            <th colspan="3">{{ $debit_notes->first()->note_value }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- <button type="submit" class="btn btn-success">Update Note</button> --}}
        </div>

    </form>

    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-success btn-sm" id="print_section">Print</button>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            $(".edit-note").on('click', function (){
                open_modal_set_values( $(this) );
            });

            // ----------------------------------------------------------------------------------------------------

            $("#price_difference").on("keyup", function(){
                calculate_note_value();
            });

            $("#gst_percent_difference").on("keyup", function(){
                calculate_note_value();
            });

            $("#quantity_difference").on("keyup", function(){
                calculate_note_value();
            });

            $("#discount_difference").on("keyup", function(){
                calculate_note_value();
            });


            function open_modal_set_values( element ){
                
                var item_id = element.attr('data-item_id');
                var item_gst = element.attr('data-item_gst');
                var invoice_id = element.attr('data-invoice_id');
                var price = element.attr('data-price');
                var gst = element.attr('data-gst');
                var qty = element.attr('data-qty');
                var discount = element.attr('data-discount');

                if(item_gst.toLowerCase() == 'nil' || item_gst.toLowerCase() == 'exempt' || item_gst.toLowerCase() == 'export'){
                    item_gst = 0;
                }

                var original_price = element.attr('data-original_price');
                var original_gst = element.attr('data-original_gst');
                var original_qty = element.attr('data-original_qty');
                var original_discount = element.attr('data-original_discount');

                $("#item_id").val(item_id);
                $("#item_gst").val(item_gst);
                $("#invoice_id").val(invoice_id);
                $("#price_difference").val(price);
                $("#gst_percent_difference").val(gst);
                $("#quantity_difference").val(qty);
                $("#discount_difference").val(discount);
                
                $("#price").val(original_price);
                $("#gst").val(original_gst);
                $("#quantity").val(original_qty);
                $("#discount").val(original_discount);

                var taxable_value = qty*price;
                var discount_value = (qty*price) * discount/100;
                var gst_value = gst;
                var note_value = (parseFloat(taxable_value) - parseFloat(discount_value)) + parseFloat(gst_value);

                $("#taxable_value").val(taxable_value);
                $("#discount_value").val(discount_value);
                $("#gst_value").val(gst_value);
                $("#note_value").val(note_value);

                $("#edit-note-modal").modal('show');
            }


            function calculate_note_value()
            {
                var qty = $("#quantity_difference").val();
                var gst = $("#item_gst").val();
                var price = $("#price_difference").val();
                var discount = $("#discount_difference").val();

                if(gst == ''){
                    gst = 0;
                }

                if(price == ''){
                    price = 0;
                }

                if(qty == ''){
                    qty = 0;
                }

                if(discount == ''){
                    discount = 0;
                }

                var taxable_value = qty * price;
                var discount_value = (qty*price) * discount/100;
                var gst_value = taxable_value * gst / 100;
                var note_value = (parseFloat(taxable_value) - parseFloat(discount_value)) + parseFloat(gst_value);

                $("#taxable_value").val(taxable_value);
                $("#discount_value").val(discount_value);
                $("#gst_percent_difference").val(gst_value);
                $("#gst_value").val(gst_value);
                $("#note_value").val(note_value);
            }

            $("#reason_change").on("change", function(){
                var selected_reason = $(this).val();

                if(selected_reason == 'other'){
                    $("#reason_change_other").show();
                } else {
                    $("#reason_change_other").hide();
                }
            });

            $(".delete-row").on("click", function() {
                $(this).parent().parent().remove();

                calculateTFoot();
            });

            $('input').on("keyup", function() {
                var tr = $(this).closest('tr');

                const value = calculateValue(tr);
                tr.find('.row-value').text(value);

                calculateTFoot();
            });

            function calculateTFoot(){
                
                //const price = calculatePriceValue();
                const gst = calculateGstValue();
                //const qty = calculateQtyValue();
                const discount = calculateDiscountValue();

                const item_value = calculateItemValue();
                const note_value = parseFloat(item_value) - parseFloat(discount) + parseFloat(gst);

                $('input[name="taxable_value"]').val(item_value);
                $('input[name="discount_value"]').val(discount);
                $('input[name="gst_value"]').val(gst);
                $('input[name="note_value"]').val(note_value);
            }

            function calculateValue(tr) {

                var price = tr.find('input[name="price_difference[]"]').val();
                var gst = tr.find('input[name="gst_difference[]"]').val();
                var qty = tr.find('input[name="quantity_difference[]"]').val();
                var discount = tr.find('input[name="discount_difference[]"]').val();

                if(price == ''){
                    price = 0;
                }

                if(gst == ''){
                    gst = 0;
                }

                if(qty == ''){
                    qty = 0;
                }

                if(discount == ''){
                    discount = 0;
                }

                var item_amount = price * qty;

                var item_amount_with_gst = parseFloat(item_amount) + parseFloat(gst);

                var total_value = item_amount_with_gst - discount;

                return total_value;

            };

            function calculateItemValue(){
                let totalPrice = 0;
                let price = [];
                let qty = [];

                $('input[name="price_difference[]"]').each(function(i, v) {
                    price[i] = v.value;
                });

                $('input[name="quantity_difference[]"]').each(function(i, v) {
                    qty[i] = v.value;
                });

                for(i=0; i<price.length; i++){
                    totalPrice += (price[i] * qty[i]);
                }

                return totalPrice;
            };

            function calculateGstValue(){
                let totalGst = 0;

                $('input[name="gst_difference[]"]').each(function(i, v) {
                    totalGst += parseFloat(v.value);
                });

                return totalGst;
            };

            function calculateDiscountValue(){
                let totalDiscount = 0;

                $('input[name="discount_difference[]"]').each(function(i, v) {
                    totalDiscount += parseFloat(v.value);
                });

                return totalDiscount;
            };

            $('#note_reason_change').on("change", function() {
                console.log($(this).val());
                if( $(this).val() == "discount_on_sale" ){
                    localStorage.setItem("gst", $('input[name="gst_difference[]"]').val());
                    $('input[name="gst_difference[]"]').val(0).attr("disabled", true).trigger('keyup');
                } else {
                    if( localStorage.getItem("gst") !== null ){
                        $('input[name="gst_difference[]"]').val( localStorage.getItem("gst") ).attr("disabled", false);
                        localStorage.removeItem("gst");
                    }
                }
            });

            $('#print_section').on("click", function () {
                $('#printable').printThis();
            });

        });
    </script>
@endsection
