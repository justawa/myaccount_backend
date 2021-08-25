@extends('layouts.dashboard')

@section('content')

<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Upload Document</div>

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


                    <form method="POST" id="additional_document_form" action="{{ route('post.additional.document') }}" enctype="multipart/form-data">

                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Month</label>
                                    <select class="form-control" name="month">
                                        <option value="1">Jan</option>
                                        <option value="2">Feb</option>
                                        <option value="3">Mar</option>
                                        <option value="4">Apr</option>
                                        <option value="5">May</option>
                                        <option value="6">Jun</option>
                                        <option value="7">Jul</option>
                                        <option value="8">Aug</option>
                                        <option value="9">Sep</option>
                                        <option value="10">Oct</option>
                                        <option value="11">Nov</option>
                                        <option value="12">Dec</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Year</label>
                                    <select class="form-control" name="year">
                                        <option value="2020">2020</option>
                                        <option value="2021">2021</option>
                                        <option value="2022">2022</option>
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                        <option value="2029">2029</option>
                                        <option value="2030">2030</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" name="type">
                                        <option value="Income Tax Returns">Income Tax Returns</option>
                                        <option value="GST Returns">GST Returns</option>
                                        <option value="Balance Sheet">Balance Sheet</option>
                                        <option value="Stock Statement">Stock Statement</option>
                                        <option value="TDS Returns">TDS Returns</option>
                                        <option value="Form 26-AS">Form 26-AS</option>
                                        <option value="Audit Report(Inc. Tax)">Audit Report(Inc. Tax)</option>
                                        <option value="GST Challan">GST Challan</option>
                                        <option value="Inc-Tax Challan">Inc-Tax Challan</option>
                                        <option value="GST Certificates">GST Certificates</option>
                                        <option value="PF/EST Returns">PF/EST Returns</option>
                                        <option value="GST Audit Report">GST Audit Report</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Upload Statement</label>
                            <input type="file" class="form-control" name="document" style="height: auto;" />
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </form>
                    
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-bar-fill">
                            <span class="progress-bar-text">0%</span>
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
            $("#additional_document_form").on('submit', function(e) {
                e.preventDefault();    
                var formData = new FormData($(this)[0]);

                $("#progressBar").show();
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener("progress", function(evt) {
                            // console.log(evt);
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);

                                $('.progress-bar-fill').css('width', `${percentComplete}%`);
                                $('.progress-bar-text').text(`${percentComplete}%`);
                            }
                        }, false);

                        return xhr;
                    },
                    url: "{{ route('post.additional.document') }}",
                    type: 'POST',
                    data: formData,
                    contentType: "application/json",
                    dataType: "json",
                    success: function (data) {
                        // console.log(data);
                        alert(data.success);
                        $("#additional_document_form").trigger('reset');
                        $('.progress-bar-fill').css('width', `0%`);
                        $('.progress-bar-text').text('0%');
                    },
                    error: function(res){
                        // console.log(res.responseJSON.errors.document[0]);
                        $('.progress-bar-fill').css('width', `0%`);
                        $('.progress-bar-text').text('Error');
                        alert(res.responseJSON.errors.document[0]);
                    },
                    processData: false,
                    contentType: false
                });
            });
        })
    </script>
@endsection