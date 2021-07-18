@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('party') !!}
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="{{ route('post.import.party') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Choose Excel File</label>
                        <input type="file" class="form-control" name="party_file" style="height: auto;" />
                    </div>
                </div>
                <div class="col-md-2">
                    <label style="visibility: hidden">Button</label>
                    <button class="btn btn-success">Import Parties</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <form>
                <div class="col-md-6 col-md-offset-4">
                    <div class="form-group">
                        <label style="visibility: hidden">Party</label>
                        <input type="text" name="party" id="search_by_party_name" class="form-control" placeholder="Company Name" autocomplete="off" />
                        {{-- <div class="auto"></div> --}}
                    </div>
                </div>
                <div class="col-md-2">
                    <label style="visibility: hidden">Button</label>
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
                            View All Party
                        </div>
                        <div class="col-md-4 col-md-offset-4">
                            <a href="{{ route('export.party.to.excel') }}" class="btn btn-success btn-sm">Export</a>
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
                                <th>Contact Person Name</th>
                                <th>Company Name</th>
                                <th>Phone</th>
                                <th>Registered?</th>
                                <th>GST</td>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($parties) > 0)
                                @php $count = 1 @endphp
                                @foreach($parties as $party)
                                <tr>
                                    <td>{{ $count++ }}</td>
                                    <td>{{ $party->contact_person_name }}</td>
                                    <td>{{ $party->name }}</td>
                                    <td>{{ $party->phone }}</td>
                                    @if( $party->status_of_registration == 1 || $party->status_of_registration == 3 || $party->status_of_registration == 4 )
                                        @php $registered = "Yes"; @endphp
                                    @else
                                        @php $registered = "No"; @endphp
                                    @endif
                                    <td>{{ $registered }}</td>
                                    @if( $registered == "Yes" )
                                        <td>{{ $party->gst }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                    <td>
                                        <a href="{{ route('party.edit', $party->id) }}">Edit Party</a>
                                        {{-- <form class="form-horizontal" method="POST" action="{{ route('party.destroy', $party->id) }}">
                                            {{ csrf_field() }}

                                            {{ method_field('DELETE') }}
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form> --}}
                                    </td>
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="5">No Party Added</td>
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
