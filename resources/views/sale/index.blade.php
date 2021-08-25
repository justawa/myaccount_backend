@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('sale') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6">
            {{-- <form>
                <div class="form-group">
                    <label>From Date</label>
                    <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                </div>
                <div class="form-group">
                    <button class="btn btn-success" >Search</button>
                </div>
            </form> --}}
        </div>

        <div class="col-md-6">
            <form>
                <div class="form-group">
                    <label>Search By</label>
                    <select class="form-control" name="query_by">
                        <option value="name">Name</option>
                        <option value="invoice_no">Invoice</option>
                        {{-- <option value="state">State</option> --}}
                    </select>
                </div>
                <div class="form-group">
                    <label>Search Here</label>
                    <input type="text" class="form-control item_search" name="q" />
                    <div class="auto"></div>
                </div>
                <div class="form-group">
                    <button class="btn btn-success" >Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            View All Sales
                        </div>
                        <div class="col-md-4">
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
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Buyer Name</th>
                                    <th>Amount Paid</th>
                                    <th>Amount Remaining</th>
                                    <th>Gross Total</th>
                                    <th>Value</th>
                                    <th>SGST</th>
                                    <th>CGST</th>
                                    <th>IGST</th>
                                    <th>UGST</th>
                                    <th>Type</th>
                                    {{-- <th>Change Type</th> --}}
                                    <th colspan="2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($invoices) > 0)
                                @php $count = 1 @endphp
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('edit.invoice.form', $invoice->id) }}">
                                        @if($invoice->invoice_prefix != null)
                                            {{ $invoice->invoice_prefix }}
                                        @endif
                                            {{ $invoice->invoice_no }}
                                        @if($invoice->invoice_suffix != null)
                                            {{ $invoice->invoice_suffix }}
                                        @endif
                                        </a>
                                    </td>
                                    <td>{{ $invoice->party_name }}</td>
                                    <td>{{ $invoice->amount_paid }}</td>
                                    <td>{{ $invoice->amount_remaining }}</td>
                                    <td>{{ $invoice->total_amount }}</td>
                                    <td>{{ $invoice->item_total_amount }}</td>
                                    <td>{{ $invoice->sgst ? $invoice->sgst : 0 }}</td>
                                    <td>{{ $invoice->cgst ? $invoice->cgst : 0 }}</td>
                                    <td>{{ $invoice->igst ? $invoice->igst : 0 }}</td>
                                    <td>{{ $invoice->ugst ? $invoice->ugst : 0 }}</td>
                                    {{-- <td>{{ ucwords($invoice->type_of_bill) }}</td> --}}
                                    <td>
                                        @if($invoice->type_of_bill == 'regular')
                                            <a style="color: red;" href="{{ route('sale.bill.type.cancel', $invoice->id) }}">Deactive</a>
                                        @else
                                            <a href="{{ route('sale.bill.type.regular', $invoice->id) }}">Active</a>
                                        @endif
                                    </td>
                                    {{-- <td>
                                        <a href="{{ route('sale.show', $invoice->id) }}">View Invoice</a>
                                    </td> --}}
                                    <td>
                                        @if($invoice->type_of_bill == 'regular')
                                            <a target="_blank" href="{{ route('print.invoice', $invoice->id) }}"><i class="fa fa-print" aria-hidden="true"></i></a>
                                        @else
                                            <i class="fa fa-print" aria-hidden="true"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->type_of_bill == 'regular')
                                        <form method="post" action="{{ route('send.mail.invoice', $invoice) }}">
                                            {{ csrf_field() }}
                                            <button title="Send Mail" type="submit" class="btn btn-primary"><i class="fa fa-envelope" aria-hidden="true"></i></button>
                                        </form>
                                        @else
                                            <button title="Send Mail" type="submit" class="btn btn-primary" disabled><i class="fa fa-envelope" aria-hidden="true"></i></button>
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
</div>
@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            $(document).on("keyup", ".item_search", function() {
                var query_by = $('select[name="query_by"] option:selected').val();
                var key_to_search = $(this).val();


                if(query_by == 'name')
                    auto_find_name( key_to_search );
                else
                    auto_find_invoice_no( key_to_search );

            });

            $(document).on('click', '.auto div', function(){
                var searched_value = $(this).text();

                $('.auto').html('');
                $('.auto').removeClass('active');

                $(".item_search").val(searched_value);

            });


            function auto_find_name( key_to_search ) {
                const url = "{{ route('api.search.party.name') }}";
                autosuggest(url, key_to_search, 'name');
            }

            function auto_find_invoice_no( key_to_search ){
                const url = "{{ route('api.search.invoice.no') }}";
                autosuggest(url, key_to_search, 'invoice_no')
            }

            function autosuggest(url, key_to_search, type){
                if(key_to_search == ''){
                    key_to_search = '-';
                    $('.auto').removeClass('active');
                }
                
                $.ajax({
                    "type": "POST",
                    "url": url,
                    "data": {
                        "q": key_to_search,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(data){

                        console.log(data);
                        var outWords = data;
                        if(outWords.length > 0) {
                            $('.auto').html('');
                            for(x = 0; x < outWords.length; x++){
                                //Fills the .auto div with the options
                                $('.auto').append(`<div>${outWords[x][type]}</div>`);
                            }

                            $('.auto').addClass('active');

                        }
                    }
                });
            }
        });
    </script>

@endsection
