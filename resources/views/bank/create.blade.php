@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('create-bank') !!}
<div class="container">
    {{-- <div class="row">
        <div class="col-md-12">
            <a href="{{ route('party.index') }}">View All Parties</a>&nbsp;&nbsp;
            <a href="{{ route('party.create') }}">Create New Party</a>&nbsp;&nbsp;
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">Add New Bank</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('bank.store') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Bank Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                                <p id="name_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('account_no') ? ' has-error' : '' }}">
                            <label for="account_no" class="col-md-4 control-label">Account No.</label>

                            <div class="col-md-6">
                                <input id="account_no" type="text" class="form-control" name="account_no" value="{{ old('account_no') }}" required>

                                @if ($errors->has('account_no'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('account_no') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('branch') ? ' has-error' : '' }}">
                            <label for="branch" class="col-md-4 control-label">Bank Branch</label>

                            <div class="col-md-6">
                                <input id="branch" type="text" class="form-control" name="branch" value="{{ old('branch') }}" required>

                                @if ($errors->has('branch'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('branch') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('ifsc') ? ' has-error' : '' }}">
                            <label for="ifsc" class="col-md-4 control-label">IFSC</label>

                            <div class="col-md-6">
                                <input id="ifsc" type="text" class="form-control" name="ifsc" value="{{ old('ifsc') }}" required>

                                @if ($errors->has('ifsc'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('ifsc') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('classification') ? ' has-error' : '' }}">
                            <label for="classification" class="col-md-4 control-label">Classification</label>

                            <div class="col-md-6">
                                <select class="form-control" id="classification" name="classification" required>
                                    <option selected disabled>Select Classification</option>
                                    <option @if(old('classification') == 'current asset') selected="selected" @endif value="current asset">Current Asset</option>
                                    <option @if(old('classification') == 'current liability') selected="selected" @endif value="current liability">Current Liability</option>
                                    <option  @if(old('classification') == 'fixed asset') selected="selected" @endif value="fixed asset">Fixed Asset</option>
                                    <option @if(old('classification') == 'fixed liability') selected="selected" @endif value="fixed liability">Fixed Liability</option>
                                </select>

                                @if ($errors->has('classification'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('classification') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                            <label for="type" class="col-md-4 control-label">Type</label>

                            <div class="col-md-6">
                                <select class="form-control" id="type" name="type">
                                    <option selected disabled>Select Type</option>
                                    <option @if( old('type') == 'secure' ) selected="selected" @endif value="secure">Secure</option>
                                    <option @if( old('type') == 'insecure' ) selected="selected" @endif value="insecure">Unsecure</option>
                                </select>

                                @if ($errors->has('type'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_balance') ? ' has-error' : '' }}">
                            <label for="opening_balance" class="col-md-4 control-label">Opening Balance</label>

                            <div class="col-md-6">

                                <div class="row">
                                    <div class="col-xs-9" style="padding-right: 0;">
                                        <input id="opening_balance" type="text" class="form-control" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                                    </div>
                                    <div class="col-xs-3" style="padding-left: 0;">
                                        <select class="form-control" id="balance_type" name="balance_type" >
                                            <option @if(old('balance_type') == 'creditor') selected="selected" @endif value="creditor">CR</option>
                                            <option @if(old('balance_type') == 'debitor') selected="selected" @endif value="debitor">DR</option>
                                        </select>
                                    </div>
                                </div>

                                @if ($errors->has('opening_balance'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_balance') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_balance_on_date') ? ' has-error' : '' }}">
                            <label for="opening_balance_on_date" class="col-md-4 control-label">Opening Balance on Date</label>

                            <div class="col-md-6">
                                <input id="opening_balance_on_date" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="opening_balance_on_date" value="{{ old('opening_balance_on_date', \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)->format('d/m/Y')) }}">

                                @if ($errors->has('opening_balance_on_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_balance_on_date') }}</strong>
                                    </span>
                                @endif
                                <p id="opening_balance_on_date_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('balance_type') ? ' has-error' : '' }}">
                            <label for="balance_type" class="col-md-4 control-label">Balance Type</label>

                            <div class="col-md-6">
                                <select class="form-control" name="balance_type" >
                                    <option @if(old('balance_type') == 'creditor') selected="selected" @endif value="creditor">Creditor</option>
                                    <option @if(old('balance_type') == 'debitor') selected="selected" @endif value="debitor">Debtor</option>
                                </select>

                                @if ($errors->has('balance_type'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('balance_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button id="add_bank" type="submit" class="btn btn-success btn-mine">
                                    Add Bank
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
    <script>
        $(document).ready(function(){

            $("#name").on("keyup", function() {
                const url = "{{ route('validate.bank.name') }}";
                let name = $(this).val();
                validateIfNameUnique(url, name, "#", "add_bank", "#", "name_validation_error");
            });

            $("#opening_balance_on_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "opening_balance_on_date_validation_error", "#", "add_bank", "#");
            });

            $("#classification").on("change", function(){
                $("#balance_type option:selected").removeAttr("selected");
                if( $(this).val() == 'current asset' || $(this).val() == 'fixed asset' ){
                    $("#type").attr("disabled", true);
                    console.log('dr');
                   
                    
                    $("#balance_type option[value='debitor']").attr("selected", "selected");

                } else {
                    $("#type").attr("disabled", false);
                    console.log('cr');

                    $("#balance_type option[value='creditor']").attr("selected", "selected");

                }
            });
        });
    </script>
@endsection