@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('credit-or-debit-purchase-note') !!}
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
            <form id="form-search-by-bill">
                <div class="form-group">
                    <input type="text" name="search_bill" id="search_bill" class="form-control" placeholder="Search Bill" />
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
                            Credit/Debit Purchase Note
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
                    <th>Company Name</th>
                    <th>Bill Date</th>
                    <th>Bill No</th>
                    {{-- <th>Amount</th> --}}
                    <th colspan="2">Note</th>
                </tr>
            </thead>
            <tbody id="dynamic-body">
                @if( count($purchase_records)>0 )
                    @foreach($purchase_records as $record)
                    <tr>
                        <td>{{ $record->party->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->bill_date)->format('d/m/Y') }}</td>
                        <td>{{ $record->bill_no }}</td>
                        {{-- <td>{{ $record->total_amount }}</td> --}}
                        {{-- <td>
                            <button type="button" class="btn btn-link" id="btn_add_commission" data-record="{{ $record->id }}">Add Commission</button>
                        </td> --}}
                        <td>
                            <a href="{{ route('bill.detail.debit.note', $record->id) }}">Debit Note</a>
                            @if($record->hasDebitNote)
                                {{-- <a href="{{ route('bill.detail.debit.note', $record->id) }}">View Debit Note Details</a> --}}
                                {{-- <a href="{{ route('purchase.debit.note.edit', $record->debit_note_no) }}">{{ $record->debit_note_no }}</a> --}}
                                {{-- @php $note_no = $record->debit_note_no ?? 0 @endphp
                                <a href="{{ route('show.purchase.debit.note', $note_no) }}">{{ $record->debit_note_no }}</a> --}}
                                <a href="{{ route('list.bill.debit.note', $record->id) }}">View All</a>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('bill.detail.credit.note', $record->id) }}">Credit Note</a>
                            @if($record->hasCreditNote)
                                {{-- <a href="{{ route('bill.detail.credit.note', $record->id) }}">View Credit Note Details</a> --}}
                                {{-- <a href="{{ route('purchase.credit.note.edit', $record->credit_note_no) }}">{{ $record->credit_note_no }}</a> --}}
                                {{-- @php $note_no = $record->credit_note_no ?? 0 @endphp
                                <a href="{{ route('show.purchase.credit.note', $note_no) }}">{{ $record->debit_note_no }}</a> --}}
                                <a href="{{ route('list.bill.credit.note', $record->id) }}">View All</a>
                            @endif
                        </td>

                        {{-- <td>
                            @if($record->hasBillNote)
                                <button id="btn_add_bill_note" class="btn btn-link" data-bill_no="{{ $record->id }}">Update Bill Note</a>
                            @else
                                <a href="{{ route('purchase.bill.note', $record->id) }}">Create Bill Note</a>
                            @endif
                        </td> --}}
                    </tr>
                    @endforeach
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
                <form id="form-add-commission" method="post" action="{{ route('add.commission.to.bill') }}">
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
</div>

<div class="modal" id="add-note-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Bill Note</h4>
            </div>
            <div class="modal-body">
                <form id="form-add-commission" method="post" action="{{ route('update.purchase.bill.note') }}">

                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put" />
                    <input type="hidden" name="bill_no" id="bill_no" value="" />
                    <input type="hidden" name="type" value="purchase" />
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
            $(document).on('click', "#btn_add_commission", function (){
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

            $("#form-search-by-bill").on("submit", function(e){
                e.preventDefault();
                var search_bill = $("#search_bill").val();

                $.ajax({
                    type: 'post',
                    url: "{{ route('get.row.by.bill') }}",
                    data: {
                        "search_bill": search_bill,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response){
                        
                        $("#dynamic-body").html('');

                        if(response.length > 0){
                            for(var i=0; i<response.length; i++){
                                $("#dynamic-body").append(`
                                    <tr>
                                        <td>${response[i].id}</td>
                                        <td>${response[i].bill_no}</td>
                                        <td>${response[i].total_amount}</td>
                                        <td>
                                            <button type="button" class="btn btn-link" id="btn_add_commission" data-record="${response[i].id}">Add Commission</button>
                                            <a href="bill-detail-note/${response[i].id}">View Details</a>
                                        </td>
                                    </tr>
                                `);
                            }
                        }

                    }
                });
            });
        });
    </script>
@endsection