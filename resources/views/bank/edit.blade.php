@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('bank-edit', request()->segment(2)) !!}

<div class="container">

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">Edit Bank</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('bank.update', $bank->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Bank Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $bank->name }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('account_no') ? ' has-error' : '' }}">
                            <label for="account_no" class="col-md-4 control-label">Account No.</label>

                            <div class="col-md-6">
                                <input id="account_no" type="text" class="form-control" name="account_no" value="{{ $bank->account_no }}" required>

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
                                <input id="branch" type="text" class="form-control" name="branch" value="{{ $bank->branch }}" required>

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
                                <input id="ifsc" type="text" class="form-control" name="ifsc" value="{{ $bank->ifsc }}" required>

                                @if ($errors->has('ifsc'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('ifsc') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_balance') ? ' has-error' : '' }}">
                            <label for="opening_balance" class="col-md-4 control-label">Opening Balance</label>

                            <div class="col-md-6">
                                <input id="opening_balance" type="text" class="form-control" name="opening_balance" value="{{ $bank->opening_balance }}" required>

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
                                <input id="opening_balance_on_date" type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="opening_balance_on_date" value="{{ \Carbon\Carbon::parse($bank->opening_balance_on_date)->format('d/m/Y') }}" required>

                                @if ($errors->has('opening_balance_on_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_balance_on_date') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('balance_type') ? ' has-error' : '' }}">
                            <label for="balance_type" class="col-md-4 control-label">Balance Type</label>

                            <div class="col-md-6">
                                <select class="form-control" name="balance_type" >
                                    <option @if( isset( $bank->balance_type ) ) @if($bank->balance_type == 'creditor') selected="selected" @endif @endif value="creditor">Creditor</option>
                                    <option @if( isset( $bank->balance_type ) ) @if($bank->balance_type == 'debitor') selected="selected" @endif @endif value="debitor">Debtor</option>
                                </select>

                                @if ($errors->has('balance_type'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('balance_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('classification') ? ' has-error' : '' }}">
                            <label for="classification" class="col-md-4 control-label">Classification</label>

                            <div class="col-md-6">
                                <select class="form-control" id="classification" name="classification" required>
                                    <option selected="true" disabled>Select Classification</option>
                                    <option @if( isset( $bank->classification ) ) @if($bank->classification == 'current asset') selected="selected" @endif @endif value="current asset">Current Asset</option>
                                    <option @if( isset( $bank->classification ) ) @if($bank->classification == 'current liability') selected="selected" @endif @endif value="current liability">Current Liability</option>
                                    <option @if( isset( $bank->classification ) ) @if($bank->classification == 'fixed asset') selected="selected" @endif @endif value="fixed asset">Fixed Asset</option>
                                    <option @if( isset( $bank->classification ) ) @if($bank->classification == 'fixed liability') selected="selected" @endif @endif value="fixed liability">Fixed Liability</option>
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
                                <select @if( isset( $bank->classification ) ) @if( $bank->classification == 'current asset' || $bank->classification == 'fixed asset' ) disabled @endif @endif class="form-control" id="type" name="type" required>
                                    <option selected="true" disabled>Select Type</option>
                                    <option @if( isset( $bank->type ) ) @if($bank->type == 'secure') selected="selected" @endif @endif value="secure">Secure</option>
                                    <option @if( isset( $bank->type ) ) @if($bank->type == 'insecure') selected="selected" @endif @endif value="insecure">Unsecure</option>
                                </select>

                                @if ($errors->has('type'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
                                    Edit Bank
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
            $("#classification").on("change", function(){
                if( $(this).val() == 'current asset' || $(this).val() == 'fixed asset' ){
                    $("#type").attr("disabled", true);
                } else {
                    $("#type").attr("disabled", false);
                }
            });
        });
    </script>
@endsection