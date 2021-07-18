@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('cash-in-hand') !!}
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Cash in Hand</div>

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


                    <form method="POST" action="{{ route('post.cash.in.hand') }}">

                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Opening Balance </label>
                                    <input type="text" class="form-control" placeholder="Opening Balance" name="opening_balance"  @if( isset( $cash_in_hand->opening_balance ) ) value="{{ $cash_in_hand->opening_balance }}" @endif />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Opening Balance Type</label>
                                    <select class="form-control" name="balance_type" >
                                        <option @if( isset( $cash_in_hand->balance_type ) ) @if($cash_in_hand->balance_type == 'creditor') selected="selected" @endif @endif value="creditor">Creditor</option>
                                        <option @if( isset( $cash_in_hand->balance_type ) ) @if($cash_in_hand->balance_type == 'debitor') selected="selected" @endif @endif value="debitor">Debtor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Opening Balance Date</label>
                                    <input type="text" name="balance_date" id="balance_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($cash_in_hand->balance_date) ) value="{{ \Carbon\Carbon::parse($cash_in_hand->balance_date)->format('d/m/Y') }}" @endif @if( \Carbon\Carbon::parse($cash_in_hand->balance_date) >= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from) && \Carbon\Carbon::parse($cash_in_hand->balance_date) <= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to) ) disabled @endif />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Narration</label>
                        <textarea class="form-control" placeholder="Narration" name="narration"> @if( isset($cash_in_hand->narration) ) {{ $cash_in_hand->narration }} @endif</textarea>
                        </div>
                        <button type="submit" class="btn btn-success" >Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
