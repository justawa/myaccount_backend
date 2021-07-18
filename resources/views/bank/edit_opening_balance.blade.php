@extends('layouts.dashboard')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Opening Balance of {{ $bank->name . ' - ' . $bank->branch }}</div>

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


                    <form method="POST" action="{{ route('update.bank.opening.balance', $bank->id) }}">

                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Opening Balance </label>
                                    <input type="text" class="form-control" placeholder="Opening Balance" name="opening_balance"  @if( isset( $bank->opening_balance ) ) value="{{ $bank->opening_balance }}" @endif />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Opening Balance Type</label>
                                    <select class="form-control" name="balance_type" >
                                        <option @if( isset( $bank->balance_type ) ) @if($bank->balance_type == 'creditor') selected="selected" @endif @endif value="creditor">Creditor</option>
                                        <option @if( isset( $bank->balance_type ) ) @if($bank->balance_type == 'debitor') selected="selected" @endif @endif value="debitor">Debtor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Opening Balance Date</label>
                                <input type="text" name="opening_balance_on_date" id="opening_balance_on_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($bank->opening_balance_on_date) ) value="{{ \Carbon\Carbon::parse($bank->opening_balance_on_date)->format('d/m/Y') }}" @endif />
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success" >Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection