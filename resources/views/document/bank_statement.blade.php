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

    #document a[aria-expanded="false"]::before, #document a[aria-expanded="true"]::before, #document a[aria-expanded="true"]::before {
        content: ''
    }


</style>

@section('count')
    ({{ $total_count }})
@endsection

@section('draggable')
    <div id="mydraggable" style="width: 95%; height: 90%; background-color: #333; position: absolute; z-index: 99999; padding-left: 20px; padding-right: 20px; border: 1px solid #ccc; box-sizing: border-box; margin: 0 auto; overflow: hidden; display: none;">
        
        {{-- <div class="main dragscroll" id="mydraggable-left" style="width: 28%; height: 90%; float: left; margin-right: 10px;">
            <img src="" id="mydraggable-left-img" style="width: 100%; height: 100%;" />
            <a target="_blank" href="" id="mydraggable-left-file">Preview not available</a>
        </div> --}}
        
        {{-- <div id="mydraggable-right" style="width: 70%; height: 90%; float: left; margin-left: 10px;">
            <iframe src="{{ route('purchase.create') }}" style="width: 100%; height: 100%;"></iframe>
        </div> --}}

        <div class="main dragscroll" id="mydraggable-left" style="width: 100%; height: 90%; float: left; margin-right: 10px;">
            <img src="" id="mydraggable-left-img" style="width: 100%; height: 100%;" />
            <a target="_blank" href="" id="mydraggable-left-file" style="display: flex; justify-content: center; align-items: center; height: 100%;">Preview not available</a>
        </div>

        <div style="width: 90%; margin: 0 auto; clear: both;">
            <div class="row text-left" style="position: relative;">
                {{-- <div class="col-md-3 text-center"> --}}
                    <button type="button" id="change_row_status" data-row="" class="btn btn-success">Mark as done</button>
                {{-- </div> --}}
                {{-- <div class="col-md-3 text-center"> --}}
                    <button type="button" id="rotate-draggable" class="btn btn-primary">Rotate Image</button>
                {{-- </div> --}}
                {{-- <div class="col-md-2 text-center"> --}}
                    {{-- <button type="button" onclick="zoomin()" class="btn btn-primary">Zoom In</button> --}}
                {{-- </div> --}}
                {{-- <div class="col-md-2 text-center"> --}}
                    {{-- <button type="button" onclick="zoomout()" class="btn btn-primary">Zoom Out</button> --}}
                {{-- </div> --}}
                {{-- <div class="col-md-2 text-center"> --}}
                    <button type="button" id="close-draggable" class="btn btn-danger">Close</button>
                {{-- </div> --}}
            </div>
        </div>
    </div>
@endsection

@section('content')

{!! Breadcrumbs::render('bank-statement-document') !!}

{{-- <div id="mydraggable" style="width: 200px; height: 200px; background-repeat: no-repeat; background-size: 100% 100%; border: 1px solid #ccc; display: none; z-index: 99999; position: absolute;">
    <div id="mydrag-overlay" style="display: none;">
        <div class="row text-center" style="height: inherit;">
            <div class="col-md-6 text-center"><button type="button" id="change_row_status" data-row="" class="btn btn-success">Mark as done</button></div>
            <div class="col-md-6 text-center"><button type="button" id="close-draggable" class="btn btn-danger">Close</button></div>
        </div>
    </div>
</div> --}}
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
                <th>Mark as done</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if( count($uploaded_statements) > 0 )
                @php $count = 1; $ext = ''; @endphp
                @foreach( $uploaded_statements as $bill )
                @php 
                    $pos = strpos($bill->file_path, '.');
                    if($pos){
                        $ext = substr($bill->file_path, $pos);
                    }
                @endphp
                @if($ext == '.png' || $ext == '.jpg' || $ext == '.jpeg') @php $isImage = true @endphp @else @php $isImage = false @endphp @endif
                <tr>
                    @php $file_path = asset('storage/'.$bill->file_path); @endphp
                    <td>{{ $count++ }}</td>
                    <td>
                        @if($isImage)
                            <img src="{{ $file_path }}" class="img-responsive" style="max-height: 100px" />
                        @else
                            <a href="{{ $file_path }}" target="_blank">
                                @php
                                    switch($bill->file_ext) {
                                        case "pdf":
                                            echo '<i class="fa fa-file-pdf-o fa-4x" aria-hidden="true"></i>';
                                        break;
                                        case "xls":
                                        case "xlsx":
                                            echo '<i class="fa fa-file-excel-o fa-4x" aria-hidden="true"></i>';
                                        break;
                                        default:
                                            echo '<i class="fa fa-file-text-o fa-4x" aria-hidden="true"></i>';
                                    }
                                @endphp
                            </a>
                        @endif
                    </td>
                    <td>{{ $bill->month }}</td>
                    <td>{{ $bill->created_at }}</td>
                    <td>{{ $bill->status == 1 ? 'Marked as done' : 'Pending' }}</td>
                    <td><button type="button" class="btn btn-link view-full" data-row="{{ $bill->id }}" data-src="{{ $file_path }}" data-is_image="{{ $isImage }}">View Full</button></td>
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
                var isImage = $(this).attr("data-is_image");

                // $("#mydraggable").css("background-image", "url('"+ src +"')");

                if(isImage){
                    $("#mydraggable-left-img").attr("src", src);
                    $("#mydraggable-left-file").hide();
                } else {
                    $("#mydraggable-left-file").attr("href", src);
                    $("#mydraggable-left-img").hide();
                }

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

            var degrees = 0;

            $("#rotate-draggable").on("click", function () {
                if(degrees == 360){
                    degrees = 0;
                }
                degrees = degrees + 90;

                $("#mydraggable-left-img")
                    .css({'-webkit-transform' : 'rotate('+ degrees +'deg)',
                        '-moz-transform' : 'rotate('+ degrees +'deg)',
                        '-ms-transform' : 'rotate('+ degrees +'deg)',
                        'transform' : 'rotate('+ degrees +'deg)'});
                                    
                // $("#mydrag-overlay")
                //     .css({'-webkit-transform' : 'rotate(-'+ degrees +'deg)',
                //         '-moz-transform' : 'rotate(-'+ degrees +'deg)',
                //         '-ms-transform' : 'rotate(-'+ degrees +'deg)',
                //         'transform' : 'rotate(-'+ degrees +'deg)'});
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

            function zoomin() {
                var myImg = document.getElementById("mydraggable-left-img");
                var currWidth = myImg.clientWidth;
                var currHeight = myImg.clientHeight;
                if (currWidth == 2500) return false;
                else {
                    myImg.style.width = (currWidth + 100) + "px";
                    myImg.style.height = (currHeight + 100) + "px";
                }
            }

            function zoomout() {
                var myImg = document.getElementById("mydraggable-left-img");
                var currWidth = myImg.clientWidth;
                var currHeight = myImg.clientHeight;
                if (currWidth < 450) return false;
                else {
                    myImg.style.width = (currWidth - 100) + "px";
                    myImg.style.height = (currHeight - 100) + "px";
                }
            }

        });

    </script>
@endsection