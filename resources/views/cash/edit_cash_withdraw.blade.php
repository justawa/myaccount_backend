@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('edit-cash-withdraw', request()->segment(3)) !!}

<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Edit Cash withdrawn</div>

                <div class="panel-body">


                    <form method="POST" action="{{ route('update.cash.withdraw', $cash_withdraw->id) }}">

                        {{ csrf_field() }}
                        {{ method_field('PUT') }}

                        <div class="form-group row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Amount" name="amount" value="{{ $cash_withdraw->amount }}" />
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="date" value="{{ \Carbon\Carbon::parse($cash_withdraw->date)->format('d/m/Y') }}" />
                            </div>
                        </div>

                        {{-- <div class="form-group row">
                            <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" checked /> <label for="cash">Cash</label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group" id="cash-box">
                                    <div class="form-group">
                                        <input type="text" placeholder="Cash Amount" id="cash_amount" name="cash_amount" class="form-control" />
                                    </div>
                                    <hr/>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-3">
                                <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" checked /> <label for="bank">Bank</label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group" id="bank-list">
                                    <div class="form-group">
                                        <input type="text" placeholder="Bank Amount" id="bank_amount" name="bank_amount" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <input type="text" placeholder="Bank Cheque No." id="bank_cheque" name="bank_cheque" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label>Bank List</label>
                                        <select class="form-control" name="bank">
                                            @if(count($banks) > 0)
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <hr/>
                                </div>
                            </div>
                        </div> --}}

                        <div class="form-group row">
                            <div class="col-md-6">
                                <select class="form-control" name="bank">
                                    @if(count($banks) > 0)
                                        @foreach($banks as $bank)
                                            <option @if($cash_withdraw->bank == $bank->id) selected="selected" @endif value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Contra No" name="contra" value="{{ $cash_withdraw->contra }}" @if(isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->bill_no_type == 'auto') readonly @endif />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea class="form-control" placeholder="Narration" name="narration">{{ $cash_withdraw->narration }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Submit</button>
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
