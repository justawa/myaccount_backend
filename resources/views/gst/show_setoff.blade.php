@extends('layouts.dashboard')

@section('content')

{{-- {!! Breadcrumbs::render('gst-setoff') !!} --}}

<div class="container">
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            GST Setoff
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period Table <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">Date</label>
                                            <input type="text" name="fix_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="2">GST Setoff</th>
                                <th colspan="6"></th>
                            </tr>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th rowspan="2">Other than Reverse charge tax payable</th>
                                @if(auth()->user()->profile->registered != 3)
                                <th colspan="4">Paid through ITC(input)</th>
                                @endif
                                <th rowspan="2">Paid through GST Cash Ledger</th>
                                <th rowspan="2">Balance to be paid in cash</th>
                            </tr>
                            @if(auth()->user()->profile->registered != 3)
                            <tr>
                                <th>IGST</th>
                                <th>CGST</th>
                                <th>SGST</th>
                                <th>CESS</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            
                            <tr>
                                <th>IGST</th>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->ot_reverse_charge_igst : 0 }}</td>
                                @if(auth()->user()->profile->registered != 3)
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_igst_igst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_igst_cgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_igst_sgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_igst_cess : 0 }}</td>
                                @endif
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_ptgcl_igst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_btbpic_igst : 0 }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->ot_reverse_charge_cgst : 0 }}</td>
                                @if(auth()->user()->profile->registered != 3)
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cgst_igst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cgst_cgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cgst_sgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cgst_cess : 0 }}</td>
                                @endif
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_ptgcl_cgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_btbpic_cgst : 0 }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->ot_reverse_charge_sgst : 0 }}</td>
                                @if(auth()->user()->profile->registered != 3)
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_sgst_igst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_sgst_cgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_sgst_sgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_sgst_cess : 0 }}</td>
                                @endif
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_ptgcl_sgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_btbpic_sgst : 0 }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->ot_reverse_charge_cess : 0 }}</td>
                                @if(auth()->user()->profile->registered != 3)
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cess_igst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cess_cgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cess_sgst : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_input_cess_cess : 0 }}</td>
                                @endif
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_ptgcl_cess : 0 }}</td>
                                <td>{{ $gst_set_off_other_than_reverse_charge ? $gst_set_off_other_than_reverse_charge->otr_btbpic_cess : 0 }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <hr/>

                    {{-- @if($gst_set_off_reverse_charge) --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>GST Setoff</th>
                                <th>Reverse charge tax payable</th>
                                <th>Paid through GST Cash Ledger</th>
                                <th>Balance to be paid in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_igst ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_ptgcl_igst ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_btbpic_igst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_cgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_ptgcl_cgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_btbpic_cgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_sgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_ptgcl_sgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_btbpic_sgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_cess ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_ptgcl_cess ?? 0 }}</td>
                                <td>{{ $gst_set_off_reverse_charge->reverse_charge_btbpic_cess ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>
                    {{-- @endif --}}

                    {{-- @if($gst_set_off_latefees) --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Late Fees</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td>{{ $gst_set_off_latefees->liability_igst_latefees ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_ptgcl_latefees_igst ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_btbpic_latefees_igst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $gst_set_off_latefees->liability_cgst_latefees ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_ptgcl_latefees_cgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_btbpic_latefees_cgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $gst_set_off_latefees->liability_sgst_latefees ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_ptgcl_latefees_sgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_btbpic_latefees_sgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $gst_set_off_latefees->liability_cess_latefees ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_ptgcl_latefees_cess ?? 0 }}</td>
                                <td>{{ $gst_set_off_latefees->liability_btbpic_latefees_cess ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>
                    {{-- @endif --}}

                    {{-- @if($gst_set_off_interest) --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Interest</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td>{{ $gst_set_off_interest->liability_igst_interest ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_ptgcl_interest_igst ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_btbpic_interest_igst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $gst_set_off_interest->liability_cgst_interest ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_ptgcl_interest_cgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_btbpic_interest_cgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $gst_set_off_interest->liability_sgst_interest ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_ptgcl_interest_sgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_btbpic_interest_sgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $gst_set_off_interest->liability_cess_interest ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_ptgcl_interest_cess ?? 0 }}</td>
                                <td>{{ $gst_set_off_interest->liability_btbpic_interest_cess ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>
                    {{-- @endif --}}

                    {{-- @if($gst_set_off_penalty) --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Penalty</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td>{{ $gst_set_off_penalty->liability_igst_penalty ?? 0  }}</td>
                                <td>{{ $gst_set_off_penalty->liability_ptgcl_penalty_igst ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_btbpic_penalty_igst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $gst_set_off_penalty->liability_cgst_penalty ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_ptgcl_penalty_cgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_btbpic_penalty_cgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $gst_set_off_penalty->liability_sgst_penalty ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_ptgcl_penalty_sgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_btbpic_penalty_sgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $gst_set_off_penalty->liability_cess_penalty ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_ptgcl_penalty_cess ?? 0 }}</td>
                                <td>{{ $gst_set_off_penalty->liability_btbpic_penalty_cess ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>
                    {{-- @endif --}}

                    {{-- @if($gst_set_off_others) --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2">Description</th>
                                <th colspan="3">Others</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Paid through GST cash ledger</th>
                                <th>Balance to be in cash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>IGST</th>
                                <td>{{ $gst_set_off_others->liability_igst_others ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_ptgcl_others_igst ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_btbpic_others_igst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CGST</th>
                                <td>{{ $gst_set_off_others->liability_cgst_others ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_ptgcl_others_cgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_btbpic_others_cgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>SGST</th>
                                <td>{{ $gst_set_off_others->liability_sgst_others ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_ptgcl_others_sgst ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_btbpic_others_sgst ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>CESS</th>
                                <td>{{ $gst_set_off_others->liability_cess_others ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_ptgcl_others_cess ?? 0 }}</td>
                                <td>{{ $gst_set_off_others->liability_btbpic_others_cess ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr/>
                    {{-- @endif --}}

                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
