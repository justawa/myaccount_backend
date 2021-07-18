@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('gst-ledger') !!}

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            GST Ledger
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">To Date</label>
                                            <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li><button class="btn btn-success btn-block">Search</button></li>
                                    </form>
                                </ul>
                            </div>
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
                    
                    {{-- @if(auth()->user()->profile->registered != 3) --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <button type="button" id="add_cash_advance_payment" class="btn btn-success">Add Advance Payment</button>
                                <button type="button" id="add_cash_balance" class="btn btn-success">Add Cash Opening Balance</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="4">Cash Ledger Balance</th>
                                <th rowspan="2">Total</th>
                            </tr>
                            <tr>
                                <th>IGST</th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>CESS</th>
                            </tr>
                            {{-- <tr>
                                <th>Balance</th>
                                <th>Current</th>
                                <th>Balance</th>
                                <th>Current</th>
                                <th>Balance</th>
                                <th>Current</th>
                                <th>Balance</th>
                                <th>Current</th>
                            </tr> --}}
                        </thead>
                        <tbody>
                            <tr>
                                <th>Tax</th>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_igst_tax'] + $cash['igst_tax'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cgst_tax'] + $cash['cgst_tax'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_sgst_tax'] + $cash['sgst_tax'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cess_tax'] + $cash['cess_tax'] }}</td>
                                <td>{{ $cash['balance_igst_tax'] + $cash['igst_tax'] + $cash['balance_cgst_tax'] + $cash['cgst_tax'] + $cash['balance_sgst_tax'] + $cash['sgst_tax'] + $cash['balance_cess_tax'] + $cash['cess_tax'] }}</td>
                            </tr>
                            <tr>
                                <th>Interest</th>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_igst_interest'] + $cash['igst_interest'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cgst_interest'] + $cash['cgst_interest'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_sgst_interest'] + $cash['sgst_interest'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cess_interest'] + $cash['cess_interest'] }}</td>
                                <td>{{ $cash['balance_igst_interest'] + $cash['igst_interest'] + $cash['balance_cgst_interest'] + $cash['cgst_interest'] + $cash['balance_sgst_interest'] + $cash['sgst_interest'] + $cash['balance_cess_interest'] + $cash['cess_interest'] }}</td>
                            </tr>
                            <tr>
                                <th>Late Fees</th>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_igst_late_fees'] + $cash['igst_late_fees'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cgst_late_fees'] + $cash['cgst_late_fees'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_sgst_late_fees'] + $cash['sgst_late_fees'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cess_late_fees'] + $cash['cess_late_fees'] }}</td>
                                <td>{{ $cash['balance_igst_late_fees'] + $cash['igst_late_fees'] + $cash['balance_cgst_late_fees'] + $cash['cgst_late_fees'] + $cash['balance_sgst_late_fees'] + $cash['sgst_late_fees'] + $cash['balance_cess_late_fees'] + $cash['cess_late_fees'] }}</td>
                            </tr>
                            <tr>
                                <th>Penalty</th>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_igst_penalty'] + $cash['igst_penalty'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cgst_penalty'] + $cash['cgst_penalty'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_sgst_penalty'] + $cash['sgst_penalty'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cess_penalty'] + $cash['cess_penalty'] }}</td>
                                <td>{{ $cash['balance_igst_penalty'] + $cash['igst_penalty'] + $cash['balance_cgst_penalty'] + $cash['cgst_penalty'] + $cash['balance_sgst_penalty'] + $cash['sgst_penalty'] + $cash['balance_cess_penalty'] + $cash['cess_penalty'] }}</td>
                            </tr>
                            <tr>
                                <th>Others</th>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_igst_others'] + $cash['igst_others'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cgst_others'] + $cash['cgst_others'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_sgst_others'] + $cash['sgst_others'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $cash['balance_cess_others'] + $cash['cess_others'] }}</td>
                                <td>{{ $cash['balance_igst_others'] + $cash['igst_others'] + $cash['balance_cgst_others'] + $cash['cgst_others'] + $cash['balance_sgst_others'] + $cash['sgst_others'] + $cash['balance_cess_others'] + $cash['cess_others'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>

                    {{-- @endif --}}

                    {{-- <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2"></th>
                                <th colspan="6">Cash Ledger Balance</th>
                            </tr>
                            <tr>
                                <th>Tax</th>
                                <th>Interest</th>
                                <th>Late Fees</th>
                                <th>Penalty</th>
                                <th>Other</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                                <td><input type="text" class="form-control" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" /></td>
                                <td colspan="4"></td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <button class="btn btn-success">Add Balance</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table> --}}

                    @if(auth()->user()->profile->registered != 3)
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                {{-- <button type="button" id="add_credit_advance_payment" class="btn btn-success">Add Advance Payment</button> --}}
                                <button type="button" id="add_credit_balance" class="btn btn-success">Add Credit Opening Balance</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="4">Credit Ledger Balance</th>
                                <th rowspan="2">Total</th>
                            </tr>
                            <tr>
                                <th>IGST</th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>CESS</th>
                            </tr>
                            {{-- <tr>
                                <th>Balance</th>
                                <th>Current</th>
                                <th>Balance</th>
                                <th>Current</th>
                                <th>Balance</th>
                                <th>Current</th>
                                <th>Balance</th>
                                <th>Current</th>
                            </tr> --}}
                        </thead>
                        <tbody>
                            <tr>
                                <th>Tax</th>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $credit['balance_igst'] + $credit['igst'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $credit['balance_cgst'] + $credit['cgst'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $credit['balance_sgst'] + $credit['sgst'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $credit['balance_cess'] + $credit['cess'] }}</td>
                                <td>{{ $credit['balance_igst'] + $credit['igst'] + $credit['balance_cgst'] + $credit['cgst'] + $credit['balance_sgst'] + $credit['sgst'] + $credit['balance_cess'] + $credit['cess'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>
                    @endif

                    @if(auth()->user()->profile->registered != 0)
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                {{-- <button type="button" id="add_liability_advance_payment" class="btn btn-success">Add Advance Payment</button> --}}
                                {{-- <button type="button" id="add_liability_balance" class="btn btn-success">Add Liability Opening Balance</button> --}}

                                <button type="button" id="add_fixed_liability_balance" class="btn btn-success">Opening Balance</button>
                                <button type="button" id="add_changing_liability_balance" class="btn btn-success">Add Liability</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2"></th>
                                <th colspan="6">Liability Ledger Balance</th>
                            </tr>
                            <tr>
                                <th>Tax payable under reverse charge</th>
                                <th>Other than reverse charge tax payable</th>
                                <th>Late Fees</th>
                                <th>Interest</th>
                                <th>Penalty</th>
                                <th>Others</th>
                            </tr>
                            {{-- <tr>
                                <th>Balance</th>
                                <th>Current</th>
                            </tr> --}}
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td>{{ $liability['balance_tax_reverse_charge_igst'] + $liability['reverse_charge_igst'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $liability['balance_other_than_reverse_charge_igst'] + $liability['other_than_reverse_charge_igst'] }}</td>
                                <td>{{ $liability['balance_igst_late_fees'] + $liability['igst_late_fees'] }}</td>
                                <td>{{ $liability['balance_igst_interest'] + $liability['igst_interest'] }}</td>
                                <td>{{ $liability['balance_igst_penalty'] + $liability['igst_penalty'] }}</td>
                                <td>{{ $liability['balance_igst_others'] + $liability['igst_others'] }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $liability['balance_tax_reverse_charge_cgst'] + $liability['reverse_charge_cgst'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $liability['balance_other_than_reverse_charge_cgst'] + $liability['other_than_reverse_charge_cgst'] }}</td>
                                <td>{{ $liability['balance_cgst_late_fees'] + $liability['cgst_late_fees'] }}</td>
                                <td>{{ $liability['balance_cgst_interest'] + $liability['cgst_interest'] }}</td>
                                <td>{{ $liability['balance_cgst_penalty'] + $liability['cgst_penalty'] }}</td>
                                <td>{{ $liability['balance_cgst_others'] + $liability['cgst_others'] }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $liability['balance_tax_reverse_charge_sgst'] + $liability['reverse_charge_sgst'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $liability['balance_other_than_reverse_charge_sgst'] + $liability['other_than_reverse_charge_sgst'] }}</td>
                                <td>{{ $liability['balance_sgst_late_fees'] + $liability['sgst_late_fees'] }}</td>
                                <td>{{ $liability['balance_sgst_interest'] + $liability['sgst_interest'] }}</td>
                                <td>{{ $liability['balance_sgst_penalty'] + $liability['sgst_penalty'] }}</td>
                                <td>{{ $liability['balance_sgst_others'] + $liability['sgst_others'] }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $liability['balance_tax_reverse_charge_cess'] + $liability['reverse_charge_cess'] }}</td>
                                {{-- <td>{{  }}</td> --}}
                                <td>{{ $liability['balance_other_than_reverse_charge_cess'] + $liability['other_than_reverse_charge_cess'] }}</td>
                                <td>{{ $liability['balance_cess_late_fees'] + $liability['cess_late_fees'] }}</td>
                                <td>{{ $liability['balance_cess_interest'] + $liability['cess_interest'] }}</td>
                                <td>{{ $liability['balance_cess_penalty'] + $liability['cess_penalty'] }}</td>
                                <td>{{ $liability['balance_cess_others'] + $liability['cess_others'] }}</td>
                            </tr>

                        </tbody>
                    </table>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="cash_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Opening Balance</h4>
            </div>
            <div class="modal-body">
                <form method="POST" @if($fixed_cash_ledger_balance) action="{{ route('edit.cash.ledger.balance') }}" @else action="{{ route('save.cash.ledger.balance') }}" @endif>
                    {{ csrf_field() }}
                    @if($fixed_cash_ledger_balance)
                        {{ method_field('PUT') }}
                        <input type="hidden" name="row_id" value="{{ $fixed_cash_ledger_balance->id }}" />
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Tax</th>
                                <th>Interest</th>
                                <th>Late Fees</th>
                                <th>Penalty</th>
                                <th>Other</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" name="igst_tax" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->igst_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_interest" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->igst_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_late_fees" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->igst_late_fees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_penalty" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->igst_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_others" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->igst_others }}" @endif /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" name="cgst_tax" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cgst_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_interest" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cgst_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_late_fees" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cgst_late_fees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_penalty" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cgst_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_others" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cgst_others }}" @endif /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" name="sgst_tax" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->sgst_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_interest" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->sgst_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_late_fees" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->sgst_late_fees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_penalty" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->sgst_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_others" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->sgst_others }}" @endif /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" name="cess_tax" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cess_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_interest" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cess_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_late_fees" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cess_late_fees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_penalty" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cess_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_others" @if($fixed_cash_ledger_balance) value="{{ $fixed_cash_ledger_balance->cess_others }}" @endif /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">
                                    <input id="cash_openinng_balance_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" @if($fixed_cash_ledger_balance) value="{{ \Carbon\Carbon::parse($fixed_cash_ledger_balance->date)->format('d/m/Y') }}" readonly @endif />

                                    <p id="cash_openinng_balance_date_validation_error" style="font-size: 12px; color: red;"></p>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="form-group">
                        <button id="btn_add_or_update_cash_openinng_balance" type="submit" class="btn btn-success">@if($fixed_cash_ledger_balance) Update @else Add @endif Balance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="add_advance_cash_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Advance Payment</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('save.cash.ledger.balance') }}">
                    {{ csrf_field() }}
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Tax</th>
                                <th>Interest</th>
                                <th>Late Fees</th>
                                <th>Penalty</th>
                                <th>Other</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" name="igst_tax" id="advance_payment_igst_tax" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->igst_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_interest" id="advance_payment_igst_interest" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->igst_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_late_fees" id="advance_payment_igst_late_fees" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->igst_latefees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_penalty" id="advance_payment_igst_penalty" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->igst_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="igst_others" id="advance_payment_igst_others" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->igst_others }}" @endif /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" name="cgst_tax" id="advance_payment_cgst_tax" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cgst_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_interest" id="advance_payment_cgst_interest" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cgst_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_late_fees" id="advance_payment_cgst_late_fees" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cgst_latefees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_penalty" id="advance_payment_cgst_penalty" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cgst_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cgst_others" id="advance_payment_cgst_others" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cgst_others }}" @endif /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" name="sgst_tax" id="advance_payment_sgst_tax" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->sgst_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_interest" id="advance_payment_sgst_interest" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->sgst_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_late_fees" id="advance_payment_sgst_late_fees" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->sgst_latefees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_penalty" id="advance_payment_sgst_penalty" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->sgst_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="sgst_others" id="advance_payment_sgst_others" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->sgst_others }}" @endif /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" name="cess_tax" id="advance_payment_cess_tax" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cess_tax }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_interest" id="advance_payment_cess_interest" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cess_interest }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_late_fees" id="advance_payment_cess_late_fees" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cess_latefees }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_penalty" id="advance_payment_cess_penalty" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cess_penalty }}" @endif /></td>
                                <td><input type="text" class="form-control" name="cess_others" id="advance_payment_cess_others" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cess_others }}" @endif /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">
                                    <label>Total</label>
                                    <input type="text" class="form-control" name="total" id="total_advance_payment_taxes" placeholder="Total" readonly />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    {{-- <input type="text" class="form-control" name="voucher_no" placeholder="Voucher No" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->voucher_no }}" @endif /> --}}
                                    <label>Voucher No</label>

                                    @php $showErrors = $myerrors->has('voucher_no') ? $myerrors->has('voucher_no') : $errors->has('voucher_no') @endphp
                                    <div class="{{ $showErrors ? ' has-error' : '' }}">
 

                                        <input id="voucher_no" placeholder="Voucher No" type="text" class="form-control" name="voucher_no" @if ( $myerrors->has('voucher_no') ) required @else @if($errors->has('voucher_no')) {{-- readonly value="{{ $invoice_no + 1 }}" --}} @else @if(isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') value="{{ $voucher_no + 1 }}" readonly @endif @endif @endif>
                                        @if ($myerrors->has('voucher_no'))
                                            <span class="help-block">
                                                <ul>
                                                    @foreach( $myerrors['voucher_no'] as $error )
                                                    <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </span>
                                        @endif
                                        <p id="bill_no_error_msg" style="color: red; font-size: 12px;"></p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label>CIN</label>
                                    <input type="text" class="form-control" name="cin" placeholder="CIN" @if($last_advance_cash_payment) value="{{ $last_advance_cash_payment->cin }}" @endif />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label>Date</label>
                                    <input type="text" class="form-control custom_date" name="date" id="advance_payment_date" placeholder="DD/MM/YYYY" @if($last_advance_cash_payment) value="{{ \Carbon\Carbon::parse($last_advance_cash_payment->date)->format('d/m/Y') }}" @endif />
                                    <p id="advance_payment_date_validation_error" style="font-size: 12px; color: red;"></p>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="radio" name="type_of_payment[]" value="cash" id="cash" /> <label for="cash">Cash</label>
                                </div>

                                <div class="col-md-9">
                                    <div class="form-group" id="cash-list" style="display: none;">
                                        <input type="hidden" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="radio" name="type_of_payment[]" value="bank" id="bank" /> <label for="bank">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="hidden" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" />
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
                                        <div class="form-group">
                                            <input type="hidden" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="radio" name="type_of_payment[]" value="pos" id="pos" /> <label for="pos">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="hidden" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Amount Paid</label>
                                        <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Paid" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button id="btn-add-gst-payment-modal" type="submit" class="btn btn-success">Add Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="credit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Opening Balance</h4>
            </div>
            <div class="modal-body">
                <form method="POST" @if($fixed_credit_ledger_balance) action="{{ route('edit.credit.ledger.balance') }}" @else action="{{ route('save.credit.ledger.balance') }}" @endif>
                    {{ csrf_field() }}
                    @if($fixed_credit_ledger_balance)
                        {{ method_field('PUT') }}
                        <input type="hidden" name="row_id" value="{{ $fixed_credit_ledger_balance->id }}" />
                    @endif
                    <div class="form-group">
                        <label>IGST</label>
                        <input type="text" class="form-control" placeholder="IGST" name="igst" @if($fixed_credit_ledger_balance) value="{{ $fixed_credit_ledger_balance->igst }}" @endif />
                    </div>
                    <div class="form-group">
                        <label>CGST</label>
                        <input type="text" class="form-control" placeholder="CGST" name="cgst" @if($fixed_credit_ledger_balance) value="{{ $fixed_credit_ledger_balance->cgst }}" @endif />
                    </div>
                    <div class="form-group">
                        <label>SGST</label>
                        <input type="text" class="form-control" placeholder="SGST" name="sgst" @if($fixed_credit_ledger_balance) value="{{ $fixed_credit_ledger_balance->sgst }}" @endif />
                    </div>
                    <div class="form-group">
                        <label>CESS</label>
                        <input type="text" class="form-control" placeholder="CESS" name="cess" @if($fixed_credit_ledger_balance) value="{{ $fixed_credit_ledger_balance->cess }}" @endif />
                    </div>
                    <hr/>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="date" @if($fixed_credit_ledger_balance) value="{{ \Carbon\Carbon::parse($fixed_credit_ledger_balance->date)->format('d/m/Y') }}" readonly @endif />
                    </div>

                    {{-- <div class="col-md-12">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" /> <label for="cash">Cash</label>
                                </div>

                                <div class="col-md-9">
                                    <div class="form-group" id="cash-list" style="display: none;">
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" /> <label for="bank">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" />
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
                                        <div class="form-group">
                                            <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" />
                                        </div>
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" /> <label for="pos">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" />
                                        </div>
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Amount Received</label>
                                        <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Received" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">@if($fixed_credit_ledger_balance) Update @else Add @endif Balance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="add_advance_credit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Advance Payment</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('save.credit.ledger.balance') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="IGST" name="igst" />
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="CGST" name="cgst" />
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="SGST" name="sgst" />
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="CESS" name="cess" />
                    </div>
                    <hr/>
                    <div class="form-group">
                        <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="date" />
                    </div>

                    {{-- <div class="col-md-12">
                        <div class="form-group">
                            <label>Mode of Payment</label><br />
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" /> <label for="cash">Cash</label>
                                </div>

                                <div class="col-md-9">
                                    <div class="form-group" id="cash-list" style="display: none;">
                                        <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" />
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" /> <label for="bank">Bank</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" />
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
                                        <div class="form-group">
                                            <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" />
                                        </div>
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" /> <label for="pos">POS</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group" id="pos-bank-list" style="display: none;">
                                        <div class="form-group">
                                            <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>POS Bank List</label>
                                            <select class="form-control" name="pos_bank">
                                                @if(count($banks) > 0)
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" />
                                        </div>
                                        <hr/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Amount Received</label>
                                        <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Received" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal" id="liability_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Opening Balance</h4>
            </div>
            <div class="modal-body">

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#changing">Changing</a></li>
                    <li><a data-toggle="tab" href="#fixed">Fixed</a></li>
                </ul>

                <div class="tab-content">
                    <div id="changing" class="tab-pane fade in active">
                        <form method="POST" action="{{ route('save.liability.ledger.balance') }}">
                            {{ csrf_field() }}
                            <input type="hidden" name="status" value="changing" />
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Interest</th>
                                        <th>Late Fees</th>
                                        <th>Penalty</th>
                                        <th>Other</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>IGST</th>
                                        <td><input type="text" class="form-control" name="igst_interest" /></td>
                                        <td><input type="text" class="form-control" name="igst_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="igst_penalty" /></td>
                                        <td><input type="text" class="form-control" name="igst_others" /></td>
                                    </tr>
                                    <tr>
                                        <th>CGST</th>
                                        <td><input type="text" class="form-control" name="cgst_interest" /></td>
                                        <td><input type="text" class="form-control" name="cgst_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="cgst_penalty" /></td>
                                        <td><input type="text" class="form-control" name="cgst_others" /></td>
                                    </tr>
                                    <tr>
                                        <th>SGST</th>
                                        <td><input type="text" class="form-control" name="sgst_interest" /></td>
                                        <td><input type="text" class="form-control" name="sgst_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="sgst_penalty" /></td>
                                        <td><input type="text" class="form-control" name="sgst_others" /></td>
                                    </tr>
                                    <tr>
                                        <th>CESS</th>
                                        <td><input type="text" class="form-control" name="cess_interest" /></td>
                                        <td><input type="text" class="form-control" name="cess_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="cess_penalty" /></td>
                                        <td><input type="text" class="form-control" name="cess_others" /></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7"><input type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" /></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Mode of Payment</label><br />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" /> <label for="cash">Cash</label>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="form-group" id="cash-list" style="display: none;">
                                                <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" />
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" /> <label for="bank">Bank</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group" id="bank-list" style="display: none;">
                                                <div class="form-group">
                                                    <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" />
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" />
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
                                                <div class="form-group">
                                                    <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" />
                                                </div>
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" /> <label for="pos">POS</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group" id="pos-bank-list" style="display: none;">
                                                <div class="form-group">
                                                    <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" />
                                                </div>
                                                <div class="form-group">
                                                    <label>POS Bank List</label>
                                                    <select class="form-control" name="pos_bank">
                                                        @if(count($banks) > 0)
                                                            @foreach($banks as $bank)
                                                                <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" />
                                                </div>
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Amount Received</label>
                                                <input class="form-control" type="text" id="amount_paid" name="amount_received" placeholder="Amount Received" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">Add Balance</button>
                            </div>
                        </form>
                    </div>
                    <div id="fixed" class="tab-pane fade">
                        <form method="POST" @if($fixed_liablility_balance) action="{{ route('update.liability.ledger.balance', $fixed_liablility_balance->id) }}" @else action="{{ route('save.liability.ledger.balance') }}" @endif >
                            {{ csrf_field() }}
                            <input type="hidden" name="status" value="fixed" />
                            @if($fixed_liablility_balance)
                           {{ method_field('PUT') }}
                            <input type="hidden" name="row_id" value="{{ $fixed_liablility_balance->id }}" />
                            @endif
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2"></th>
                                        <th colspan="2">Tax</th>
                                    </tr>
                                    <tr>
                                        <th>Reverse Charge</th>
                                        <th>Other than Reverse Charge</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>IGST</th>

                                        <td><input type="text" class="form-control" name="igst_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->igst_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="igst_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->igst_tax_other_than_reverse_charge }}" @endif /></td>
                                    </tr>
                                    <tr>
                                        <th>CGST</th>
                                        <td><input type="text" class="form-control" name="cgst_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->cgst_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="cgst_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->cgst_tax_other_than_reverse_charge }}" @endif /></td>
                                    </tr>
                                    <tr>
                                        <th>SGST</th>
                                        <td><input type="text" class="form-control" name="sgst_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->sgst_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="sgst_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->sgst_tax_other_than_reverse_charge }}" @endif /></td>
                                    </tr>
                                    <tr>
                                        <th>CESS</th>
                                        <td><input type="text" class="form-control" name="cess_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->cess_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="cess_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{  $fixed_liablility_balance->cess_tax_other_than_reverse_charge }}" @endif /></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                    <td colspan="3"><input type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" @if($fixed_liablility_balance) value="{{ \Carbon\Carbon::parse($fixed_liablility_balance->date)->format('d/m/Y') }}" readonly @endif /></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">@if($fixed_liablility_balance) Update @else Add @endif Balance</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div> --}}


<div class="modal" id="liability_modal_changing">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Changing Balance</h4>
            </div>
            <div class="modal-body">

                {{-- <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#changing">Changing</a></li>
                    <li><a data-toggle="tab" href="#fixed">Fixed</a></li>
                </ul> --}}

                {{-- <div class="tab-content"> --}}
                    {{-- <div id="changing" class="tab-pane fade in active"> --}}
                        <form method="POST" action="{{ route('save.liability.ledger.balance') }}">
                            {{ csrf_field() }}
                            <input type="hidden" name="status" value="changing" />
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Interest</th>
                                        <th>Late Fees</th>
                                        <th>Penalty</th>
                                        <th>Other</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>IGST</th>
                                        <td><input type="text" class="form-control" name="igst_interest" id="changing_balance_igst_interest" /></td>
                                        <td><input type="text" class="form-control" name="igst_late_fees" id="changing_balance_igst_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="igst_penalty" id="changing_balance_igst_penalty" /></td>
                                        <td><input type="text" class="form-control" name="igst_others" id="changing_balance_igst_others" /></td>
                                    </tr>
                                    <tr>
                                        <th>CGST</th>
                                        <td><input type="text" class="form-control" name="cgst_interest" id="changing_balance_cgst_interest" /></td>
                                        <td><input type="text" class="form-control" name="cgst_late_fees" id="changing_balance_cgst_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="cgst_penalty" id="changing_balance_cgst_penalty" /></td>
                                        <td><input type="text" class="form-control" name="cgst_others" id="changing_balance_cgst_others" /></td>
                                    </tr>
                                    <tr>
                                        <th>SGST</th>
                                        <td><input type="text" class="form-control" name="sgst_interest" id="changing_balance_sgst_interest" /></td>
                                        <td><input type="text" class="form-control" name="sgst_late_fees" id="changing_balance_sgst_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="sgst_penalty" id="changing_balance_sgst_penalty" /></td>
                                        <td><input type="text" class="form-control" name="sgst_others" id="changing_balance_sgst_others" /></td>
                                    </tr>
                                    <tr>
                                        <th>CESS</th>
                                        <td><input type="text" class="form-control" name="cess_interest" id="changing_balance_cess_interest" /></td>
                                        <td><input type="text" class="form-control" name="cess_late_fees" id="changing_balance_cess_late_fees" /></td>
                                        <td><input type="text" class="form-control" name="cess_penalty" id="changing_balance_cess_penalty" /></td>
                                        <td><input type="text" class="form-control" name="cess_others" id="changing_balance_cess_others" /></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <input id="changing_balance_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" />
                                            <p id="changing_balance_date_validation_error" style="font-size: 12px; color: red;"></p>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- <div class="col-md-12">
                                <div class="form-group">
                                    <label>Mode of Payment</label><br />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="checkbox" name="type_of_payment[]" value="cash" id="cash" /> <label for="cash">Cash</label>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="form-group" id="cash-list" style="display: none;">
                                                <input type="text" placeholder="Cash Amount" name="cashed_amount" id="cashed_amount" class="form-control" />
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="checkbox" name="type_of_payment[]" value="bank" id="bank" /> <label for="bank">Bank</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group" id="bank-list" style="display: none;">
                                                <div class="form-group">
                                                    <input type="text" placeholder="Bank Amount" id="banked_amount" name="banked_amount" class="form-control" />
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" placeholder="Bank Cheque" id="bank_cheque" name="bank_cheque" class="form-control" />
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
                                                <div class="form-group">
                                                    <input type="text" placeholder="DD/MM/YYYY" id="bank_payment_date" name="bank_payment_date" class="form-control custom_date" />
                                                </div>
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="checkbox" name="type_of_payment[]" value="pos" id="pos" /> <label for="pos">POS</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group" id="pos-bank-list" style="display: none;">
                                                <div class="form-group">
                                                    <input type="text" placeholder="POS Amount" id="posed_amount" name="posed_amount" class="form-control" />
                                                </div>
                                                <div class="form-group">
                                                    <label>POS Bank List</label>
                                                    <select class="form-control" name="pos_bank">
                                                        @if(count($banks) > 0)
                                                            @foreach($banks as $bank)
                                                                <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->branch }})</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" placeholder="DD/MM/YYYY" id="pos_payment_date" name="pos_payment_date" class="form-control custom_date" />
                                                </div>
                                                <hr/>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div> --}}

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Total Amount</label>
                                        <input class="form-control" type="text" id="changing_balance_amount_paid" name="amount_received" placeholder="Total Amount" readonly />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Narration</label>
                                        <textarea class="form-control" name="narration" placeholder="Narration"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button id="btn_changing_balance" type="submit" class="btn btn-success">Add Balance</button>
                            </div>
                        </form>
                    {{-- </div> --}}
                    
                {{-- </div> --}}

            </div>
        </div>
    </div>
</div>



{{--  --}}

<div class="modal" id="liability_modal_fixed">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Fixed Balance</h4>
            </div>
            <div class="modal-body">

                {{-- <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#changing">Changing</a></li>
                    <li><a data-toggle="tab" href="#fixed">Fixed</a></li>
                </ul> --}}

                {{-- <div class="tab-content"> --}}
                    {{-- <div id="fixed" class="tab-pane fade"> --}}
                        <form method="POST" @if($fixed_liablility_balance) action="{{ route('update.liability.ledger.balance', $fixed_liablility_balance->id) }}" @else action="{{ route('save.liability.ledger.balance') }}" @endif >
                            {{ csrf_field() }}
                            <input type="hidden" name="status" value="fixed" />
                            @if($fixed_liablility_balance)
                           {{ method_field('PUT') }}
                            <input type="hidden" name="row_id" value="{{ $fixed_liablility_balance->id }}" />
                            @endif
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2"></th>
                                        <th colspan="2">Tax</th>
                                    </tr>
                                    <tr>
                                        <th>Reverse Charge</th>
                                        <th>Other than Reverse Charge</th>
                                        {{-- <th>Interest</th>
                                        <th>Late Fees</th>
                                        <th>Penalty</th>
                                        <th>Other</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>IGST</th>
                                        {{-- <td><input type="text" class="form-control" name="igst_tax" /></td> --}}
                                        <td><input type="text" class="form-control" name="igst_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->igst_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="igst_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->igst_tax_other_than_reverse_charge }}" @endif /></td>
                                        {{-- <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td> --}}
                                    </tr>
                                    <tr>
                                        <th>CGST</th>
                                        {{-- <td><input type="text" class="form-control" name="cgst_tax" /></td> --}}
                                        <td><input type="text" class="form-control" name="cgst_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->cgst_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="cgst_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->cgst_tax_other_than_reverse_charge }}" @endif /></td>
                                        {{-- <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td> --}}
                                    </tr>
                                    <tr>
                                        <th>SGST</th>
                                        {{-- <td><input type="text" class="form-control" name="sgst_tax" /></td> --}}
                                        <td><input type="text" class="form-control" name="sgst_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->sgst_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="sgst_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->sgst_tax_other_than_reverse_charge }}" @endif /></td>
                                        {{-- <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td> --}}
                                    </tr>
                                    <tr>
                                        <th>CESS</th>
                                        {{-- <td><input type="text" class="form-control" name="cess_tax" /></td> --}}
                                        <td><input type="text" class="form-control" name="cess_tax_reverse_charge" @if($fixed_liablility_balance) value="{{ $fixed_liablility_balance->cess_tax_reverse_charge }}" @endif /></td>
                                        <td><input type="text" class="form-control" name="cess_tax_other_than_reverse_charge" @if($fixed_liablility_balance) value="{{  $fixed_liablility_balance->cess_tax_other_than_reverse_charge }}" @endif /></td>
                                        {{-- <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td>
                                        <td><input type="text" class="form-control" /></td> --}}
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                    <td colspan="3">
                                        <input id="fixed_balance_date" type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" @if($fixed_liablility_balance) value="{{ \Carbon\Carbon::parse($fixed_liablility_balance->date)->format('d/m/Y') }}" readonly @endif />
                                        <p id="fixed_balance_date_validation_error" style="font-size: 12px; color: red;"></p>
                                    </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="form-group">
                                <button id="btn_add_update_fixed_balance" type="submit" class="btn btn-success">@if($fixed_liablility_balance) Update @else Add @endif Balance</button>
                            </div>
                        </form>
                    {{-- </div> --}}
                {{-- </div> --}}

            </div>
        </div>
    </div>
</div>

{{--  --}}

<div class="modal" id="add_advance_liability_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Advance Payment</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('save.cash.ledger.balance') }}">
                    {{ csrf_field() }}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Under Reverse Charge</th>
                                <th>Other than Reverse Charge</th>
                                <th>Late Fees</th>
                                <th>Interest</th>
                                <th>Penalty</th>
                                <th>Other</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td><input type="text" class="form-control" name="igst_under_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="igst_other_than_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="igst_latefees" /></td>
                                <td><input type="text" class="form-control" name="igst_interest" /></td>
                                <td><input type="text" class="form-control" name="igst_penalty" /></td>
                                <td><input type="text" class="form-control" name="igst_others" /></td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td><input type="text" class="form-control" name="cgst_under_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="cgst_other_than_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="cgst_latefees" /></td>
                                <td><input type="text" class="form-control" name="cgst_interest" /></td>
                                <td><input type="text" class="form-control" name="cgst_penalty" /></td>
                                <td><input type="text" class="form-control" name="cgst_others" /></td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td><input type="text" class="form-control" name="sgst_under_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="sgst_other_than_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="sgst_latefees" /></td>
                                <td><input type="text" class="form-control" name="sgst_interest" /></td>
                                <td><input type="text" class="form-control" name="sgst_penalty" /></td>
                                <td><input type="text" class="form-control" name="sgst_others" /></td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td><input type="text" class="form-control" name="cess_under_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="cess_other_than_reverse_charge" /></td>
                                <td><input type="text" class="form-control" name="cess_latefees" /></td>
                                <td><input type="text" class="form-control" name="cess_interest" /></td>
                                <td><input type="text" class="form-control" name="cess_penalty" /></td>
                                <td><input type="text" class="form-control" name="cess_others" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7"><input type="text" class="form-control custom_date" name="date" placeholder="DD/MM/YYYY" /></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $("#advance_payment_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "advance_payment_date_validation_error", "#", "btn-add-gst-payment-modal", "#");
            });

            $("#cash_openinng_balance_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "cash_openinng_balance_date_validation_error", "#", "btn_add_or_update_cash_openinng_balance", "#");
            });
            

            $("#fixed_balance_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "fixed_balance_date_validation_error", "#", "btn_add_update_fixed_balance", "#");
            });

            $("#changing_balance_date").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "changing_balance_date_validation_error", "#", "btn_changing_balance", "#");
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
                        url: "{{ route('api.validate.gst.payment.voucherno') }}",
                        data: {
                            "token": bill_no,
                            "user": userId
                        },
                        success: function(response){
                            $('#btn-add-gst-payment-modal').attr('disabled', false);
                            $("#bill_no_error_msg").text('');
                        },
                        error: function(err){
                            // console.log(err);
                            // console.log(err.responseJSON.errors);
                            if(err.status == 400){
                                $("#bill_no_error_msg").text(err.responseJSON.errors);
                                $('#btn-add-gst-payment-modal').attr('disabled', true);
                            }
                        }
                    });
                }
            }
        });
        $("#add_cash_balance").on("click", function () {
            $("#cash_modal").modal("show");
        });

        $("#add_credit_balance").on("click", function () {
            $("#credit_modal").modal("show");
        });

        // $("#add_liability_balance").on("click", function () {
        //     $("#liability_modal").modal("show");
        // });

        $("#add_fixed_liability_balance").on("click", function () {
            $("#liability_modal_fixed").modal("show");
        });

        $("#add_changing_liability_balance").on("click", function () {
            $("#liability_modal_changing").modal("show");
        });


        $("#add_cash_advance_payment").on("click", function () {
            $("#add_advance_cash_modal").modal("show");
        });

        $("#add_credit_advance_payment").on("click", function () {
            $("#add_advance_credit_modal").modal("show");
        });

        $("#add_liability_advance_payment").on("click", function () {
            $("#add_advance_liability_modal").modal("show");
        });

        $('input[name="type_of_payment[]"]').on("change", function(){

            var type_of_payment = $(this).val();

            // console.log("outside " + type_of_payment);

            if($(this).is(':checked')){
                if (type_of_payment == 'bank') {
                    $("#bank-list").show();
                    $("#pos-bank-list").hide();
                    $("#cash-list").hide();
                } else if(type_of_payment == 'pos') {
                    $("#pos-bank-list").show();
                    $("#bank-list").hide();
                    $("#cash-list").hide();
                } else if(type_of_payment == 'cash'){
                    $("#cash-list").show();
                    $("#bank-list").hide();
                    $("#pos-bank-list").hide();
                }
            }

        });

        // $(document).on("keyup", "#cashed_amount", function() {
        //     var cashed_amount = $(this).val();
        //     var banked_amount = $("#banked_amount").val();
        //     var posed_amount = $("#posed_amount").val();

        //     if( cashed_amount == '' ) {
        //         cashed_amount = 0;
        //     }

        //     if( banked_amount == '' ) {
        //         banked_amount = 0;
        //     }

        //     if( posed_amount == '' ) {
        //         posed_amount = 0;
        //     }

        //     var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

        //     $("#amount_paid").val(amount_paid);
        //     $("#amount_paid").trigger("keyup");
        // });

        // $(document).on("keyup", "#banked_amount", function() {
        //     var banked_amount = $(this).val();
        //     var cashed_amount = $("#cashed_amount").val();
        //     var posed_amount = $("#posed_amount").val();

        //     if( cashed_amount == '' ) {
        //         cashed_amount = 0;
        //     }

        //     if( banked_amount == '' ) {
        //         banked_amount = 0;
        //     }

        //     if( posed_amount == '' ) {
        //         posed_amount = 0;
        //     }

        //     var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

        //     $("#amount_paid").val(amount_paid);
        //     $("#amount_paid").trigger("keyup");
        // });

        // $(document).on("keyup", "#posed_amount", function() {
        //     var posed_amount = $(this).val();
        //     var cashed_amount = $("#cashed_amount").val();
        //     var banked_amount = $("#banked_amount").val();

        //     if( cashed_amount == '' ) {
        //         cashed_amount = 0;
        //     }

        //     if( banked_amount == '' ) {
        //         banked_amount = 0;
        //     }

        //     if( posed_amount == '' ) {
        //         posed_amount = 0;
        //     }

        //     var amount_paid = parseFloat(cashed_amount) + parseFloat(banked_amount) + parseFloat(posed_amount);

        //     $("#amount_paid").val(amount_paid);
        //     $("#amount_paid").trigger("keyup");
        // });

        $('#changing_balance_igst_interest')
        .add('#changing_balance_igst_late_fees')
        .add('#changing_balance_igst_penalty')
        .add('#changing_balance_igst_others')

        .add('#changing_balance_cgst_interest')
        .add('#changing_balance_cgst_late_fees')
        .add('#changing_balance_cgst_penalty')
        .add('#changing_balance_cgst_others')

        .add('#changing_balance_sgst_interest')
        .add('#changing_balance_sgst_late_fees')
        .add('#changing_balance_sgst_penalty')
        .add('#changing_balance_sgst_others')

        .add('#changing_balance_cess_interest')
        .add('#changing_balance_cess_late_fees')
        .add('#changing_balance_cess_penalty')
        .add('#changing_balance_cess_others')
            .on("keyup", function() {
            add_values();
        });


        $('#advance_payment_igst_tax')
        .add('#advance_payment_igst_interest')
        .add('#advance_payment_igst_late_fees')
        .add('#advance_payment_igst_penalty')
        .add('#advance_payment_igst_others')
        
        .add('#advance_payment_cgst_tax')
        .add('#advance_payment_cgst_interest')
        .add('#advance_payment_cgst_late_fees')
        .add('#advance_payment_cgst_penalty')
        .add('#advance_payment_cgst_others')

        .add('#advance_payment_sgst_tax')
        .add('#advance_payment_sgst_interest')
        .add('#advance_payment_sgst_late_fees')
        .add('#advance_payment_sgst_penalty')
        .add('#advance_payment_sgst_others')

        .add('#advance_payment_cess_tax')
        .add('#advance_payment_cess_interest')
        .add('#advance_payment_cess_late_fees')
        .add('#advance_payment_cess_penalty')
        .add('#advance_payment_cess_others')

            .on("keyup", function() {
            add_taxes();
        });

        $("#advance_payment_date").on("keyup", function() {
            $("#bank_payment_date").val($(this).val());
            $("#pos_payment_date").val($(this).val());
        });

        function add_values(){

            var igst_interest = $('#changing_balance_igst_interest').val() ? $('#changing_balance_igst_interest').val() : 0;
            var igst_late_fees = $('#changing_balance_igst_late_fees').val() ? $('#changing_balance_igst_late_fees').val() : 0;
            var igst_penalty = $('#changing_balance_igst_penalty').val() ? $('#changing_balance_igst_penalty').val() : 0;
            var igst_others = $('#changing_balance_igst_others').val() ? $('#changing_balance_igst_others').val() : 0;

            var cgst_interest = $('#changing_balance_cgst_interest').val() ? $('#changing_balance_cgst_interest').val() : 0;
            var cgst_late_fees = $('#changing_balance_cgst_late_fees').val() ? $('#changing_balance_cgst_late_fees').val() : 0;
            var cgst_penalty = $('#changing_balance_cgst_penalty').val() ? $('#changing_balance_cgst_penalty').val() : 0;
            var cgst_others = $('#changing_balance_cgst_others').val() ? $('#changing_balance_cgst_others').val() : 0;

            var sgst_interest = $('#changing_balance_sgst_interest').val() ? $('#changing_balance_sgst_interest').val() : 0;
            var sgst_late_fees = $('#changing_balance_sgst_late_fees').val() ? $('#changing_balance_sgst_late_fees').val() : 0;
            var sgst_penalty = $('#changing_balance_sgst_penalty').val() ? $('#changing_balance_sgst_penalty').val() : 0;
            var sgst_others = $('#changing_balance_sgst_others').val() ? $('#changing_balance_sgst_others').val() : 0;

            var cess_interest = $('#changing_balance_cess_interest').val() ? $('#changing_balance_cess_interest').val() : 0;
            var cess_late_fees = $('#changing_balance_cess_late_fees').val() ? $('#changing_balance_cess_late_fees').val() : 0;
            var cess_penalty = $('#changing_balance_cess_penalty').val() ? $('#changing_balance_cess_penalty').val() : 0;
            var cess_others = $('#changing_balance_cess_others').val() ? $('#changing_balance_cess_others').val() : 0;

            var total_amount = parseFloat(igst_interest) + parseFloat(igst_late_fees) + parseFloat(igst_penalty) + parseFloat(igst_others) + parseFloat(cgst_interest) + parseFloat(cgst_late_fees) + parseFloat(cgst_penalty) + parseFloat(cgst_others) + parseFloat(sgst_interest) + parseFloat(sgst_late_fees) + parseFloat(sgst_penalty) + parseFloat(sgst_others) + parseFloat(cess_interest) + parseFloat(cess_late_fees) + parseFloat(cess_penalty) + parseFloat(cess_others);

            $("#changing_balance_amount_paid").val(total_amount);
            
        }

        function add_taxes(){
            var igst_tax = $('#advance_payment_igst_tax').val();
            var igst_interest = $('#advance_payment_igst_interest').val();
            var igst_late_fees = $('#advance_payment_igst_late_fees').val();
            var igst_penalty = $('#advance_payment_igst_penalty').val();
            var igst_others = $('#advance_payment_igst_others').val();

            var cgst_tax = $('#advance_payment_cgst_tax').val();
            var cgst_interest = $('#advance_payment_cgst_interest').val();
            var cgst_late_fees = $('#advance_payment_cgst_late_fees').val();
            var cgst_penalty = $('#advance_payment_cgst_penalty').val();
            var cgst_others = $('#advance_payment_cgst_others').val();

            var sgst_tax = $('#advance_payment_sgst_tax').val();
            var sgst_interest = $('#advance_payment_sgst_interest').val();
            var sgst_late_fees = $('#advance_payment_sgst_late_fees').val();
            var sgst_penalty = $('#advance_payment_sgst_penalty').val();
            var sgst_others = $('#advance_payment_sgst_others').val();

            var cess_tax = $('#advance_payment_cess_tax').val();
            var cess_interest = $('#advance_payment_cess_interest').val();
            var cess_late_fees = $('#advance_payment_cess_late_fees').val();
            var cess_penalty = $('#advance_payment_cess_penalty').val();
            var cess_others = $('#advance_payment_cess_others').val();

            if(igst_tax == ''){
                igst_tax = 0;
            }
            if(igst_interest == ''){
                igst_interest = 0;
            }
            if(igst_late_fees == ''){
                igst_late_fees = 0;
            }
            if(igst_penalty == ''){
                igst_penalty = 0;
            }
            if(igst_others == ''){
                igst_others = 0;
            }


            if(cgst_tax == ''){
                cgst_tax = 0;
            }
            if(cgst_interest == ''){
                cgst_interest = 0;
            }
            if(cgst_late_fees == ''){
                cgst_late_fees = 0;
            }
            if(cgst_penalty == ''){
                cgst_penalty = 0;
            }
            if(cgst_others == ''){
                cgst_others = 0;
            }


            if(sgst_tax == ''){
                sgst_tax = 0;
            }
            if(sgst_interest == ''){
                sgst_interest = 0;
            }
            if(sgst_late_fees == ''){
                sgst_late_fees = 0;
            }
            if(sgst_penalty == ''){
                sgst_penalty = 0;
            }
            if(sgst_others == ''){
                sgst_others = 0;
            }


            if(cess_tax == ''){
                cess_tax = 0;
            }
            if(cess_interest == ''){
                cess_interest = 0;
            }
            if(cess_late_fees == ''){
                cess_late_fees = 0;
            }
            if(cess_penalty == ''){
                cess_penalty = 0;
            }
            if(cess_others == ''){
                cess_others = 0;
            }

            var total_tax_amount = parseFloat(igst_tax) + parseFloat(igst_interest) + parseFloat(igst_late_fees) + parseFloat(igst_penalty) + parseFloat(igst_others) + parseFloat(cgst_tax) + parseFloat(cgst_interest) + parseFloat(cgst_late_fees) + parseFloat(cgst_penalty) + parseFloat(cgst_others) + parseFloat(sgst_tax) + parseFloat(sgst_interest) + parseFloat(sgst_late_fees) + parseFloat(sgst_penalty) + parseFloat(sgst_others) + parseFloat(cess_tax) + parseFloat(cess_interest) + parseFloat(cess_late_fees) + parseFloat(cess_penalty) + parseFloat(cess_others);

            $("#total_advance_payment_taxes").val(total_tax_amount);
            $("#amount_paid").val(total_tax_amount);
            $("#cashed_amount").val(total_tax_amount);
            $("#banked_amount").val(total_tax_amount);
            $("#posed_amount").val(total_tax_amount);
        }

    </script>
@endsection
