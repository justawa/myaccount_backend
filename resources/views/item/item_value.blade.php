@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <form>
                <div class="col-md-8">
                    <div class="form-group">
                        <input type="text" name="item" id="search_by_item_name" class="form-control" placeholder="Search by Item" autocomplete="off" />
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
                <div class="panel-heading">View Item Value</div>
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
                                <th>Name</th>
                                <th>Hsc Code</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( count($items) > 0 )
                            @php $count = 1; @endphp
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->hsc_code }}</td>
                                <td>{{ $item->value_amount }}</td>
                            </tr>
                            @endforeach
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

        $(document).on("keyup", "#search_by_item_name", function() {

            var key_to_search = $(this).val();

            auto_find_item( key_to_search );

        });

        function auto_find_item( key_to_search ) {
            if(key_to_search == ''){
                key_to_search = 0;
                $('.auto').removeClass('active');
            }
            $.ajax({
                "type": "POST",
                "url": "{{ route('api.search.item.by.name') }}",
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

            $("#search_by_item_name").val(searched_value);

        });

    });
</script>

@endsection