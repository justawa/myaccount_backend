@extends('layouts.dashboard')

<style>
    #document a[aria-expanded="false"]::before, #document a[aria-expanded="true"]::before, #document a[aria-expanded="true"]::before {
        content: ''
    }

</style>

@section('content')

{{-- {!! Breadcrumbs::render('gst-setoff') !!} --}}

<div class="container" id="document">
    <br/>
    <form action="{{ route('post.find.purchase.by.party') }}">
        <div class="form-group">
            <select class="form-control" name="party" id="party">
                <option value="0">Select Party</option>
                @foreach($parties as $party)
                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            Pending Receivable
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form id="search_pending_receivable_form">
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" value="{{ \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)->format('d/m/Y') }}" />
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">To Date</label>
                                            <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" value="{{ \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to)->format('d/m/Y') }}" />
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

                    
                <div class="panel-body">
                  <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#invoice">Invoice Wise</a></li>
            <li><a data-toggle="tab" href="#account">Account Wise</a></li>
        </ul>
        <div class="tab-content">
            <div id="invoice" class="tab-pane fade in active">
                <table class="table table-bordered" id="document_data_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Voucher no</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody  id="dynamic-body-bill">
                        {{-- @if( count($sale_amounts) > 0 )
                            @php $count = 1; @endphp
                            @foreach( $sale_amounts as $amount )
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td><a href="{{ route('view.pending.receivable.detail', $amount->id) }}">{{ $amount->voucher_no }}</a></td>
                                <td>{{ $amount->payment_date }}</td>
                                <td>{{ $amount->amount_paid }}</td>
                                <td>
                                    <form method="POST" action="{{ route('update.sale.pending.receivable.status', $amount->id) }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ $amount->status == 1 ? 0 : 1 }}" />
                                        <button type="submit" class="btn btn-success">{{ $amount->status == 1 ? 'Cancel' : 'Activate' }}</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5"><span class="text-center">No Receivables</span></td>
                            </tr>
                        @endif --}}
                    </tbody>
                    <tfoot id="dynamic-foot-bill">
                    </tfoot>
                </table>
            </div>
            <div id="account" class="tab-pane fade">
                <table class="table table-bordered" id="document_complete_data_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Voucher no</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="dynamic-body-account">
                        {{-- @if( count($account_amounts) > 0 )
                            @php $count = 1; @endphp
                            @foreach( $account_amounts as $amount )
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td><a href="{{ route('view.party.pending.receivable.detail', $amount->id) }}">{{ $amount->voucher_no }}</a></td>
                                <td>{{ $amount->payment_date }}</td>
                                <td>{{ $amount->amount }}</td>
                                <td>
                                    <form method="POST" action="{{ route('update.party.sale.pending.receivable.status', $amount->id) }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ $amount->status == 1 ? 0 : 1 }}" />
                                        <button type="submit" class="btn btn-success">{{ $amount->status == 1 ? 'Cancel' : 'Activate' }}</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5"><span class="text-center">No Receivables</span></td>
                            </tr>
                        @endif --}}
                    </tbody>
                    <tfoot id="dynamic-foot-account">
                    </tfoot>
                </table>
            </div>
        </div>
                </div>
                    

                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $(document).on("change", "#party", function() {
                get_receivable();
            });

            // $(document).on("keyup", "input[name='from_date']", function() {
            //     if($(this).val().length > 9)
            //         get_receivable();
            // });

            // $(document).on("keyup", "input[name='to_date']", function() {
            //     if($(this).val().length > 9)
            //         get_receivable();
            // });

            $("#search_pending_receivable_form").on("submit", function(e) {
                e.preventDefault();
                get_receivable();
            });

            $(document).on("submit", ".status_update_form", function(e) {
                e.preventDefault();
                var action = $(this).attr("action");
                var method = $(this).attr("method");
                var data = $(this).serialize();
                $.ajax({
                    type: method,
                    url: action,
                    data: data,
                }).done(function(res){
                    get_receivable();
                });
            });

            function get_receivable() {
                var selected_one = $("#party").val();
                var from_date = $('input[name="from_date"]').val();
                var to_date = $('input[name="to_date"]').val();
                if (selected_one > 0) {
                    // $("#btn_payment_against_party").show();
                    // $("#total_pending_payment_block").show();
                    // $("#btn_payment_against_party").attr("data-party", selected_one);
                    $.ajax({
                        type: "get",
                        url: "{{ route('get.pending.receivable') }}",
                        data: {
                            "selected_party": selected_one,
                            "from_date": from_date,
                            "to_date": to_date,
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response){

                            console.log(response);
                            // $("#total_pending_payment_amount").text(roundToTwo(response.total_pending));
                            // $("#total_pending_payment_amount_in_modal").text(roundToTwo(response.total_pending));

                            $("#dynamic-body-bill").html('');
                            $("#dynamic-body-account").html('');

                            if (response.bill.length > 0) {

                                let total_amount_bill = 0;
                                for(var i = 0; i < response.bill.length; i++){
                                    var html_row = '';
                                    if (response.bill[i].remaining_amount == null) {
                                        html_row = 'NA';
                                    } else {
                                        html_row = response.purchase[i].remaining_amount.amount_remaining;
                                    }
                                    const form_csrf = '{{ csrf_field() }}';
                                    total_amount_bill+=response.bill[i].amount_paid;
                                    let voucher_url = response.bill[i].voucher_url ? response.bill[i].voucher_url : `edit/sale-pending-payment/${response.bill[i].id}`;
                                    $("#dynamic-body-bill").append(`
                                        <tr>
                                            <td>${i+1}</td>
                                            <td><a target="_blank" href="${voucher_url}">${response.bill[i].voucher_no}</a></td>
                                            <td>${response.bill[i].payment_date || ''}</td>
                                            <td>${response.bill[i].amount_paid}</td>
                                            <td>
                                                <form method="POST" class="status_update_form" action="update-sale-pending-receivable-status/${response.bill[i].id}">
                                                    ${form_csrf}
                                                    <input type="hidden" name="status" value="${response.bill[i].status == 1 ? 0 : 1 }" />
                                                    <button type="submit" class="btn btn-success">${response.bill[i].status == 1 ? 'Cancel' : 'Activate' }</button>
                                                </form>
                                            </td>
                                        </tr>
                                    `);
                                }
                                $("#dynamic-foot-bill").append(`<tr>
                                    <td colspan="3">Total</td>
                                    <td>${total_amount_bill}</td>
                                    <td></td>
                                </tr>`);
                            } else {
                                $("#dynamic-body-bill").append(`
                                    <tr>
                                        <td colspan="6" class="text-center">No bill data</td>
                                    </tr>
                                `);
                            }

                            if (response.account.length > 0) {
                                let total_amount_account = 0;
                                for(var i = 0; i < response.account.length; i++){
                                    var html_row = '';
                                    if (response.account[i].remaining_amount == null) {
                                        html_row = 'NA';
                                    } else {
                                        html_row = response.account[i].remaining_amount.amount_remaining;
                                    }
                                    const form_csrf = '{{ csrf_field() }}';
                                    total_amount_account+=response.account[i].amount;
                                    $("#dynamic-body-account").append(`
                                        <tr>
                                            <td>${i+1}</td>
                                            <td><a target="_blank" href="edit/sale-party-pending-payment/${response.account[i].id}">${response.account[i].voucher_no}</a></td>
                                            <td>${response.account[i].payment_date || ''}</td>
                                            <td>${response.account[i].amount}</td>
                                            <td>
                                                <form method="POST" class="status_update_form" action="update-party-sale-pending-receivable-status/${response.account[i].id}">
                                                    ${form_csrf}
                                                    <input type="hidden" name="status" value="${response.account[i].status == 1 ? 0 : 1 }" />
                                                    <button type="submit" class="btn btn-success">${response.account[i].status == 1 ? 'Cancel' : 'Activate' }</button>
                                                </form>
                                            </td>
                                        </tr>
                                    `);
                                }
                                $("#dynamic-foot-account").append(`<tr>
                                    <td colspan="3">Total</td>
                                    <td>${total_amount_account}</td>
                                    <td></td>
                                </tr>`);
                            } else {
                                $("#dynamic-body-account").append(`
                                    <tr>
                                        <td colspan="6" class="text-center">No Account data</td>
                                    </tr>
                                `);
                            }
                        }
                    });
                } else {
                    alert('Please select a valid party');
                    // $("#btn_payment_against_party").hide();
                    // $("#dynamic-body").html('');
                    $("#dynamic-body-bill").append(`
                        <tr>
                            <td colspan="6" class="text-center">Select party to get data</td>
                        </tr>
                    `);
                    $("#dynamic-body-account").append(`
                        <tr>
                            <td colspan="6" class="text-center">Select party to get data</td>
                        </tr>
                    `);
                }
            }
        });
    </script>
@endsection
