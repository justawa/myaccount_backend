@extends('layouts.dashboard')

<style>
    .list-group-horizontal .list-group-item {
        display: inline-block;
    }
    .list-group-horizontal .list-group-item {
        margin-bottom: 0;
        margin-left:-4px;
        margin-right: 0;
    }
    .list-group-horizontal .list-group-item:first-child {
        border-top-right-radius:0;
        border-bottom-left-radius:4px;
    }
    .list-group-horizontal .list-group-item:last-child {
        border-top-right-radius:4px;
        border-bottom-left-radius:0;
    }

    .draggable-hover {
        /* background-color: rgba(0,0,0,0.5); */
        height: inherit;
        width: inherit;
    }


</style>


@section('content')
<div id="mydraggable" style="width: 200px; height: 200px; background-repeat: no-repeat; background-size: 100% 100%; border: 1px solid #ccc; display: none; z-index: 99999; position: absolute;">
    <div id="mydrag-overlay" style="display: none;">
        <div class="row text-center" style="height: inherit;">
            <div class="col-md-6 text-center"><button type="button" id="change_row_status" data-row="" class="btn btn-success">Mark as done</button></div>
            <div class="col-md-6 text-center"><button type="button" id="close-draggable" class="btn btn-danger">Close</button></div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row" style="margin-bottom: 20px;">
        {{-- <div class="list-group list-group-horizontal text-center">
            <a href="{{ route('sale.document') }}" class="list-group-item">All</a>
            <a href="{{ route('sale.document', 1) }}" class="list-group-item">Jan</a>
            <a href="{{ route('sale.document', 2) }}" class="list-group-item">Feb</a>
            <a href="{{ route('sale.document', 3) }}" class="list-group-item">Mar</a>
            <a href="{{ route('sale.document', 4) }}" class="list-group-item">Apr</a>
            <a href="{{ route('sale.document', 5) }}" class="list-group-item">May</a>
            <a href="{{ route('sale.document', 6) }}" class="list-group-item">Jun</a>
            <a href="{{ route('sale.document', 7) }}" class="list-group-item">Jul</a>
            <a href="{{ route('sale.document', 8) }}" class="list-group-item">Aug</a>
            <a href="{{ route('sale.document', 9) }}" class="list-group-item">Sep</a>
            <a href="{{ route('sale.document', 10) }}" class="list-group-item">Oct</a>
            <a href="{{ route('sale.document', 11) }}" class="list-group-item">Nov</a>
            <a href="{{ route('sale.document', 12) }}" class="list-group-item">Dec</a>
        </div> --}}
        <form method="GET" action="{{ route('bank.statement.document') }}">
            <div class="col-md-5">
                <select class="form-control" name="month">
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "1" ) ) selected="selected" @endif @endif value="1">January</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "2" ) ) selected="selected" @endif @endif value="2">February</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "3" ) ) selected="selected" @endif @endif value="3">March</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "4" ) ) selected="selected" @endif @endif value="4">April</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "5" ) ) selected="selected" @endif @endif value="5">May</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "6" ) ) selected="selected" @endif @endif value="6">June</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "7" ) ) selected="selected" @endif @endif value="7">July</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "8" ) ) selected="selected" @endif @endif value="8">August</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "9" ) ) selected="selected" @endif @endif value="9">September</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "10" ) ) selected="selected" @endif @endif value="10">October</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "11" ) ) selected="selected" @endif @endif value="11">November</option>
                    <option @if( isset( $_GET['month'] ) ) @if( app('request')->input('month') == "12" ) ) selected="selected" @endif @endif value="12">December</option>
                </select>
            </div>
            <div class="col-md-5">
                <select class="form-control" name="year">
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2018" ) ) selected="selected" @endif @endif value="2018">2018</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2019" ) ) selected="selected" @endif @endif value="2019">2019</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2020" ) ) selected="selected" @endif @endif value="2020">2020</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2021" ) ) selected="selected" @endif @endif value="2021">2021</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2022" ) ) selected="selected" @endif @endif value="2022">2022</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2023" ) ) selected="selected" @endif @endif value="2023">2023</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2024" ) ) selected="selected" @endif @endif value="2024">2024</option>
                    <option @if( isset( $_GET['year'] ) ) @if( app('request')->input('year') == "2025" ) ) selected="selected" @endif @endif value="2025">2025</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success btn-block">Submit</button>
            </div>
        </form>
    </div>
    <h2 class="text-center">Showing results for "{{ $month }}"</h2>
    {{-- <div class="row">
        @if( count($uploaded_bills) > 0 )
            @foreach($uploaded_bills as $bill)
            <div class="col-md-2" style="margin-bottom: 10px;">
                <img src="{{ asset('storage/public/'.$bill->image_path) }}" class="img-responsive" />
            </div>
            @endforeach
        @else
            <h3 class="text-center">No Documents</h3>
        @endif
    </div> --}}

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Document</th>
                <th>Month</th>
                <th>Uploaded on</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if( count($uploaded_statements) > 0 )
                @php $count = 1; @endphp
                @foreach( $uploaded_statements as $bill )
                <tr>
                    @php $image_path = asset('storage/'.$bill->file_path) @endphp            
                    <td>{{ $count++ }}</td>
                    <td><img src="{{ $image_path }}" class="img-responsive" style="max-width: 250px" /></td>
                    <td>{{ $bill->month }}</td>
                    <td>{{ $bill->created_at }}</td>
                    <td><button type="button" class="btn btn-link view-full" data-row="{{ $bill->id }}" data-src="{{ $image_path }}">View Full</button></td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5"><span class="text-center">No Documents</span></td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection

@section('conflicting_scripts')
    <script>
        $( "#mydraggable" ).draggable().resizable();
    </script>
@endsection

@section('scripts')
    <script>

        $(document).ready(function () {
            $(".view-full").on("click", function () {
                var src = $(this).attr("data-src");
                var row = $(this).attr("data-row");

                $("#mydraggable").css("background-image", "url('"+ src +"')");
                $("#change_row_status").attr("data-row", row);

                $("#mydraggable").show();
            });

            $("#mydraggable").on("mouseover", function () {

                $("#mydrag-overlay").addClass("draggable-hover");
                $("#mydrag-overlay").show();

            });

            $("#mydraggable").on("mouseleave", function () {

                $("#mydrag-overlay").removeClass("draggable-hover");
                $("#mydrag-overlay").hide();
            });

            $("#close-draggable").on("click", function () {
                
                $("#mydraggable").css("background-image", "");
                $("#change_row_status").attr("data-row", "");

                $("#mydraggable").hide();

            });


            $("#change_row_status").on("click", function () {

                var row_id = $(this).attr("data-row");

                $.ajax({
                    type: 'post',
                    url: '{{ route("api.change.document.status") }}',
                    data: {
                        "row_id": row_id,
                        "_token": '{{ csrf_token() }}'
                    },
                    success: function(response){
                        console.log(response);
                        if (response == 'success') {
                            // $("#form-additional-info").trigger("reset");
                            $("#mydraggable").hide();
                            show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> Status updated successfully</span>`);

                            setTimeout(function(){ location.reload(); }, 3000);
                        } else {
                            show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> Failed to update status</span>`);
                        }
                    }
                });

            });

        });

    </script>
@endsection