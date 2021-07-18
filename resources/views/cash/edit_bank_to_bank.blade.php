@extends('layouts.dashboard')

@section('content')
{{-- {!! Breadcrumbs::render('cash-withdraw') !!} --}}
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Bank to Bank transfer
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


                    <form method="POST" action="{{ route('update.bank.to.bank.transfer', $transfer->id) }}">

                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>From Bank</label>
                                <select class="form-control" name="from_bank">
                                    @if(count($banks) > 0)
                                        @foreach($banks as $bank)
                                            <option @if( $transfer->from_bank == $bank->id ) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                @if ($errors->has('from_bank'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('from_bank') }}</strong>
                                    </span>
                                @endif
                            </div>
                            {{-- <div class="col-md-6">
                                @php $showErrors = $myerrors->has('contra') ? $myerrors->has('contra') : $errors->has('contra') @endphp
                                <div class="form-group {{ $showErrors ? ' has-error' : '' }}">
                                    <input id="voucher_no" type="text" class="form-control" placeholder="Contra No" name="contra" @if ( $myerrors->has('contra') ) required @else @if($errors->has('contra')) @else @if(isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->bill_no_type == 'auto') value="{{ $contra + 1 }}" readonly @endif @endif @endif />
                                    @if ($myerrors->has('contra')) 
                                        <span class="help-block">
                                            <ul>
                                                @foreach( $myerrors['contra'] as $error )
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </span>
                                    @endif
                                    <p id="bill_no_error_msg" style="color: red; font-size: 12px;"></p>
                                </div>
                            </div> --}}
                            <div class="col-md-4">
                                <label>To Bank</label>
                                <select class="form-control" name="to_bank">
                                    @if(count($banks) > 0)
                                        @foreach($banks as $bank)
                                            <option @if( $transfer->to_bank == $bank->id ) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                @if ($errors->has('to_bank'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('to_bank') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label>Voucher</label>
                                <input type="text" class="form-control" name="voucher_no" value="{{ $transfer->contra }}" @if(isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->bill_no_type == 'auto') readonly @endif />
                                @if ($errors->has('voucher_no'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('voucher_no') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Amount" name="amount" value="{{ $transfer->amount }}" />
                                @if ($errors->has('amount'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <input id="date" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="date" value="{{ \Carbon\Carbon::parse($transfer->date)->format('d/m/Y') }}" />
                                @if ($errors->has('date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('date') }}</strong>
                                    </span>
                                @endif
                                <p id="date_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea class="form-control" placeholder="Narration" name="narration">{{ $transfer->narration }}</textarea>
                        </div>
                        <button id="btn-add-payment" type="submit" class="btn btn-success">Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $("#date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "date_validation_error", "#", "btn-add-payment", "#");
            });

            $("#voucher_no").on("keyup", function() {
                var bill_no = $("#voucher_no").val() ? $("#voucher_no").val() : undefined;
                var userId = '{{ auth()->user()->id }}';

                validateBillNo(bill_no, userId)
            });

            function validateBillNo(bill_no = undefined, userId = undefined) {
                console.log(bill_no, userId);
                if(bill_no && userId){
                    $.ajax({
                        type: 'post',
                        url: "{{ route('api.validate.cash.withdraw.voucherno') }}",
                        data: {
                            "token": bill_no,
                            "user": userId
                        },
                        success: function(response){
                            $('#btn-add-payment').attr('disabled', false);
                            $("#bill_no_error_msg").text('');
                        },
                        error: function(err){
                            // console.log(err);
                            // console.log(err.responseJSON.errors);
                            if(err.status == 400){
                                $("#bill_no_error_msg").text(err.responseJSON.errors);
                                $('#btn-add-payment').attr('disabled', true);
                            }
                        }
                    });
                }
            }

            $("#bank").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#bank-list").show();
                } else {
                    $("#bank-list").hide();
                }
            });

            $("#cash").on("change", function() {
                if( $(this).is(":checked") ){
                    $("#cash-box").show();
                } else {
                    $("#cash-box").hide();
                }
            });
        });
    </script>
@endsection