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
    <div class="row">
        <div class="col-md-6">
            <form>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="date" name="from_date" class="form-control" style="line-height: 1.8" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="date" name="to_date" class="form-control" style="line-height: 1.8" />
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <form>
                <div class="col-md-8">
                    <div class="form-group">
                        <input type="text" name="party" id="search_by_party_name" class="form-control" placeholder="Search by Party" autocomplete="off" />
                        <div class="auto"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Pending Payments</div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Invoice Date</th>
                                <th>Party</th>
                                <th>Total Amount</th>
                                <th>Remaining Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( isset($parties) && count($parties) > 0 )
                                @php $count = 1; @endphp
                                @foreach( $parties as $party )
                                @foreach( $sale_record[$party->id] as $record )
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>
                                            @if( $record->invoice_no != null ) 
                                                {{ $record->invoice_no }} 
                                            @else
                                                {{ $record->id }} 
                                            @endif
                                        </td>
                                        <td>{{ $record->invoice_date }}</td>
                                        <td>{{ $party->name }}</td>
                                        <td>{{ $record->total_amount }}</td>
                                        <td>
                                            @if( $record->remaining_amount == null )
                                                0
                                            @else
                                                {{ $record->remaining_amount->amount_remaining }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @endforeach
                            @elseif( isset($invoices) && count($invoices) > 0 )
                                @php $count = 1; @endphp
                                @foreach( $invoices as $record )
                                    <tr>
                                        <td>{{ $count++ }}</td>
                                        <td>
                                            @if( $record->invoice_no != null ) 
                                                {{ $record->invoice_no }} 
                                            @else
                                                {{ $record->id }} 
                                            @endif
                                        </td>
                                        <td>{{ $record->invoice_date }}</td>
                                        <td>{{ $record->party_name }}</td>
                                        <td>{{ $record->total_amount }}</td>
                                        <td>
                                            @if( $record->remaining_amount == null )
                                                0
                                            @else
                                                {{ $record->remaining_amount->amount_remaining }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No Data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')

<script>
    $(document).ready(function () {

        $(document).on("keyup", "#search_by_party_name", function() {

            var key_to_search = $(this).val();

            auto_find_party( key_to_search );

        });

        function auto_find_party( key_to_search ) {
            if(key_to_search == ''){
                key_to_search = 0;
                $('.auto').removeClass('active');
            }
            $.ajax({
                "type": "POST",
                "url": "{{ route('api.search.party.by.name') }}",
                "data": {
                    "key_to_search": key_to_search,
                    "_token": '{{ csrf_token() }}'
                },
                success: function(data){

                    console.log(data);
                    var outWords = data;
                    if(outWords.length > 0) {

                        for(x = 0; x < outWords.length; x++){
                            $('.auto').append(`<div data-value="${outWords[x].name}" >${outWords[x].name}</div>`); //Fills the .auto div with the options
                        }

                        $('.auto').addClass('active');

                    }
                }
            });
        }

        $(document).on('click', '.auto div', function(){
            var searched_value = $(this).attr('data-value');

            $('.auto').html('');
            $('.auto').removeClass('active');

            $("#search_by_party_name").val(searched_value);

        });

    });
</script>

@endsection
