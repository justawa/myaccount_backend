@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('item') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="{{ route('post.import.inventory') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Choose Excel File</label>
                        <input type="file" class="form-control" name="inventory_file" style="height: auto;" />
                    </div>
                </div>
                <div class="col-md-2">
                    <label style="visibility:hidden">Button</label>
                    <button class="btn btn-success">Import Items</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <form>
                <div class="col-md-6 col-md-offset-4">
                    <div class="form-group">
                        <label style="visibility:hidden">Item</label>
                        <input type="text" name="item" id="search_by_item_name" class="form-control" placeholder="Search by Item" autocomplete="off" />
                        <div class="auto"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label style="visibility:hidden">Button</label>
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-4">
                            View All Item
                        </div>
                        <div class="col-md-4 col-md-offset-4">
                            <a href="{{ route('export.item.to.excel') }}" class="btn btn-success btn-sm">Export</a>
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <th>Quantity</th>
                                @endif
                                <th>Group</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($items) > 0)
                            @php $count = 1 @endphp
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $item->name }}</td>
                                @if(auth()->user()->profile->inventory_type != "without_inventory")
                                <td>{{ $item->qty }}</td>
                                @endif
                                <td>{{ $item->group_name }}</td>
                                <td>
                                    <a href="{{ route('item.edit', $item->id) }}">Edit Item</a>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Item Added</td>
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
