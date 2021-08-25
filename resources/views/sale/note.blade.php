@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('credit-or-debit-sale-note') !!}
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
    {{-- <div class="row">
        <div class="col-md-4">
            <form id="form-search-by-invoice">
                <div class="form-group">
                    <input type="text" name="search_invoice" id="search_invoice" class="form-control" placeholder="Search Invoice" />
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Credit/Debit Sale Note
                        </div>
                        <div class="col-md-3 col-md-offset-3">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">To Date</label>
                                            <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li><button class="btn btn-success btn-block">Search</button></li>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body" id="printable">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    {{-- <th>
                        <input type="checkbox" name="select_all" id="checked_all" /> <label for="checked_all">Check All</label>
                    </th> --}}
                    <th>Company Name</th>
                    <th>Invoice Date</th>
                    <th>Invoice No</th>
                    {{-- <th>Amount</th> --}}
                    <th colspan="2">Note</th>
                </tr>
            </thead>
            <tbody id="dynamic-body">
                @if( count($sale_records) > 0)

                {{-- <form id="form-commision-to-all" method="post" action="{{ route('commision.to.all') }}"> --}}
                {{-- {{ csrf_field() }} --}}
                {{-- <input type="hidden" name="commission" id="this_commission" /> --}}
                @php $count = 1; @endphp
                @foreach($sale_records as $record)
                <tr>
                    {{-- <td>
                        <input type="hidden" name="multiple_record[]" value="{{ $record->id }}" />
                        <input type="checkbox" name="multiple_record_checked[]" />
                    </td> --}}
                    {{-- <td>{{ $count++ }}</td> --}}
                    <td>
                        {{ $record->party->name }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($record->invoice_date)->format('d/m/Y') }}
                    </td>
                    <td>
                        {{ $record->invoice_prefix . $record->invoice_no . $record->invoice_suffix }}
                    </td>
                    {{-- <td>{{ $record->total_amount }}</td> --}}
                    {{-- <td>
                        <button type="button" class="btn btn-link" id="btn_add_commission" data-record="{{ $record->id }}">Add Commission</button>
                    </td> --}}
                    <td>
                        <a href="{{ route('invoice.detail.credit.note', $record->id) }}">Credit Note</a>
                        @if($record->hasCreditNote)
                            {{-- <a href="{{ route('invoice.detail.credit.note', $record->id) }}">View Credit Note Details</a> --}}
                            {{-- <a href="{{ route('sale.credit.note.edit', $note_no) }}">{{ $record->credit_note_no }}</a> --}}
                            {{-- @php $note_no = $record->credit_note_no ?? 0 @endphp
                            <a href="{{ route('show.sale.credit.note', $note_no) }}">{{ $record->credit_note_no }}</a> --}}
                            <a href="{{ route('list.invoice.credit.note', $record->id) }}">View All</a>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('invoice.detail.debit.note', $record->id) }}">Debit Note</a>
                        @if($record->hasDebitNote)
                            {{-- <a href="{{ route('invoice.detail.debit.note', $record->id) }}">View Debit Note Details</a> --}}
                            {{-- @php $note_no = $record->debit_note_no ?? 0 @endphp
                            <a href="{{ route('show.sale.debit.note', $note_no) }}">{{ $record->debit_note_no }}</a> --}}
                            <a href="{{ route('list.invoice.debit.note', $record->id) }}">View All</a>
                        @endif
                    </td>

                    {{-- <td>
                        @if($record->hasBillNote)
                            <button type="button" id="btn_add_bill_note" class="btn btn-link" data-bill_no="{{ $record->id }}">Update Bill Note</a>
                        @else
                            <a href="{{ route('sale.bill.note', $record->id) }}">Create Bill Note</a>
                        @endif
                    </td> --}}
                </tr>
                @endforeach

                </form>
                @else
                    <tr>
                        <td colspan="6">No Data</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    </div>
                    </div>
                    </div>
                    </div>
</div>

{{-- <div class="modal" id="add-commission-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Commission</h4>
            </div>
            <div class="modal-body">
                <form id="form-add-commission" method="post" action="{{ route('add.commission.to.invoice') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put" />
                    <input type="hidden" id="row_id" name="row_id" value="" />
                    <div class="form-group">
                        <label>Commission</label>
                        <input type="text" class="form-control" id="commission_amount" name="commission" placeholder="Commission Amount" />
                    </div>
                    <button type="submit" class="btn btn-success btn-mine">Submit</button>
                </form>
                <p id="note-error"></p>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal" id="add-commission-modal-to-all">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Commission to all</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Commission</label>
                    <input type="text" class="form-control" id="commission_amount_for_all" placeholder="Commission Amount" />
                </div>
                <button id="commision_to_all" type="button" class="btn btn-success btn-mine">Submit</button>
                <p id="commision-message"></p>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal" id="add-note-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Bill Note</h4>
            </div>
            <div class="modal-body">
                <form id="form-add-commission" method="post" action="{{ route('update.sale.bill.note') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put" />
                    <input type="hidden" name="bill_no" id="bill_no" value="" />
                    <input type="hidden" name="type" value="sale" />
                    <div class="form-group">
                        <label>Taxable Value Difference</label>
                        <input type="text" class="form-control" id="taxable_value_difference" name="taxable_value_difference" placeholder="Taxable Value Difference" />
                    </div>
                    <div class="form-group">
                        <label>GST Value Difference</label>
                        <input type="text" class="form-control" id="gst_value_difference" name="gst_value_difference" placeholder="GST Value Difference" />
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <select class="form-control" id="reason" name="reason">
                            <option value="discount">Discount</option>
                            <option value="goods returns">Good Returns</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-mine">Submit</button>
                </form>
                <p id="note-error"></p>
            </div>
        </div>
    </div>
</div> --}}

@endsection

@section('scripts')
    <script>

        $(document).ready(function (){
            $(document).on('click', '#btn_add_commission', function (){
                var record = $(this).attr('data-record');
                $("#row_id").val(record);
                $("#add-commission-modal").modal('show');
            });

            $(document).on('click', "#btn_add_bill_note", function (){
                var record = $(this).attr('data-bill_no');
                $("#bill_no").val(record);
                $("#add-note-modal").modal('show');
            });

            $("#form-add-commission").on("submit", function(e){
                e.preventDefault();
                var row_id = $("#row_id").val();
                var commission = $("#commission_amount").val();

                $.ajax({
                    type: 'post',
                    url: "{{ route('add.commission.to.bill') }}",
                    data: {
                        "row_id": row_id,
                        "commission": commission,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response){
                        console.log(response);
                        $("#form-add-commission").trigger('reset');
                        $("#add-commission-modal").modal('hide');
                    }
                });

            });


            $("#form-search-by-invoice").on("submit", function(e){
                e.preventDefault();
                var search_invoice = $("#search_invoice").val();

                $.ajax({
                    type: 'post',
                    url: "{{ route('get.row.by.invoice') }}",
                    data: {
                        "search_invoice": search_invoice,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response){
                        $("#dynamic-body").html('');

                        if(response.length > 0){
                            for(var i=0; i<response.length; i++){
                                $("#dynamic-body").append(`
                                    <tr>
                                        <td>${i+1}</td>
                                        <td>${response[i].id}</td>
                                        <td>${response[i].total_amount}</td>
                                        <td>
                                            <button type="button" class="btn btn-link" id="btn_add_commission" data-record="${response[i].id}">Add Commission</button>
                                            <a href="invoice-detail-note/${response[i].id}">View Details</a>
                                        </td>
                                    </tr>
                                `);
                            }
                        }
                    }
                });
            });
        });

        $('input[name="select_all"]').on("change", function() {
            if( $(this).is(":checked") ){
                $('input[name="multiple_record_checked[]"]').attr("checked", true);
                $('#add-commission-modal-to-all').modal('show');
            } else {
                $('input[name="multiple_record_checked[]"]').attr("checked", false);
                $('#add-commission-modal-to-all').modal('hide');
            }
        });

        $(document).on('hidden.bs.modal', '#add-commission-modal-to-all', function() {
            $('input[type="checkbox"]').prop("checked", false);
        });


        $("#commision_to_all").on("click", function() {
            var commission_amount = $("#commission_amount_for_all").val();

            $("#this_commission").val(commission_amount);

            $("#form-commision-to-all").trigger("submit");
        });
    </script>
@endsection
