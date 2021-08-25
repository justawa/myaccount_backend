@extends('layouts.dashboard')

@section('content')

{!! Breadcrumbs::render('party-edit', request()->segment(2)) !!}

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Update Party</div>

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

                    <form class="form-horizontal" method="POST" action="{{ route('party.update', $party->id) }}">
                        {{ csrf_field() }}

                        {{ method_field('PUT') }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Business Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $party->name }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('contact_person_name') ? ' has-error' : '' }}">
                            <label for="contact_person_name" class="col-md-4 control-label">Person Name</label>

                            <div class="col-md-6">
                                <input id="contact_person_name" type="text" class="form-control" name="contact_person_name" value="{{ $party->contact_person_name }}">

                                @if ($errors->has('contact_person_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('contact_person_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="phone" class="col-md-4 control-label">Phone</label>

                            <div class="col-md-6">
                                <input id="phone" type="tel" class="form-control" name="phone" @if( isset( $party->phone ) ) value="{{ $party->phone }}" @endif required>

                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="col-md-4 control-label">Business Place</label>

                            <div class="col-md-6">
                                <select class="form-control" name="business_place">
                                    <option value="0">Select State</option>
                                    @foreach($states as $state)
                                    <option @if( isset( $party->business_place ) ) @if( $party->business_place == $state->id ) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Contact Email</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ $party->email }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('shipping_address') ? ' has-error' : '' }}">
                            <label for="shipping_address" class="col-md-4 control-label">Billing Address</label>

                            <div class="col-md-6">
                                <textarea id="shipping_address" class="form-control" name="shipping_address">@if( isset($party->shipping_address) ) {{ $party->shipping_address }}  @endif</textarea>

                                @if ($errors->has('shipping_address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipping_address') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('shipping_city') ? ' has-error' : '' }}">
                            <label for="shipping_city" class="col-md-4 control-label">Billing City</label>

                            <div class="col-md-6">
                                <input id="shipping_city" type="text" class="form-control" name="shipping_city" @if( isset( $party->shipping_city ) ) value="{{ $party->shipping_city }}" @endif />

                                @if ($errors->has('shipping_city'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipping_city') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('shipping_pincode') ? ' has-error' : '' }}">
                            <label for="shipping_pincode" class="col-md-4 control-label">Pincode</label>

                            <div class="col-md-6">
                                <input id="shipping_pincode" type="text" class="form-control" name="shipping_pincode" @if( isset( $party->shipping_pincode ) ) value="{{ $party->shipping_pincode }}" @endif />

                                @if ($errors->has('shipping_pincode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipping_pincode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 col-md-offset-4">
                            <div class="form-group">
                                <input type="checkbox" id="checkbox_communication_address" name="checkbox_communication_address" value="1" checked /> <label for="checkbox_communication_address">Billing same as Business address</label> <br/>

                                {{-- <input type="checkbox" id="checkbox_shipping_address" name="checkbox_shipping_address" value="1" checked /> <label for="checkbox_shipping_address">Shipping same as Communication address</label> <br/> --}}

                                <input type="checkbox" id="checkbox_billing_address" name="checkbox_billing_address" value="1" checked /> <label for="checkbox_billing_address">Shipping same as Business address</label> <br/>
                            </div>
                        </div>

                        <div id="communication_address_section" style="display: none;">
                    
                            <div class="form-group{{ $errors->has('communication_address') ? ' has-error' : '' }}">
                                <label for="communication_address" class="col-md-4 control-label">Communication Address</label>

                                <div class="col-md-6">
                                    <textarea id="communication_address" class="form-control" name="communication_address">@if( isset($party->communication_address) ) {{ $party->communication_address }}  @endif</textarea>

                                    @if ($errors->has('communication_address'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('communication_address') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('communication_state') ? ' has-error' : '' }}">
                                <label for="communication_state" class="col-md-4 control-label">Communication State</label>

                                <div class="col-md-6">
                                    <select id="communication_state" class="form-control" name="communication_state">
                                        <option value="0">Select State</option>
                                        @foreach($states as $state)
                                        <option @if( isset( $party->communication_state ) ) @if( $party->communication_state == $state->id ) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('communication_state'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('communication_state') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('communication_city') ? ' has-error' : '' }}">
                                <label for="communication_city" class="col-md-4 control-label">Communication City</label>

                                <div class="col-md-6">
                                    <input id="communication_city" type="text" class="form-control" name="communication_city" @if( isset( $party->communication_city ) ) value="{{ $party->communication_city }}" @endif />

                                    @if ($errors->has('communication_city'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('communication_city') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('communication_pincode') ? ' has-error' : '' }}">
                                <label for="communication_pincode" class="col-md-4 control-label">Communication Pincode</label>

                                <div class="col-md-6">
                                    <input id="communication_pincode" type="text" class="form-control" name="communication_pincode"
                                    @if( isset( $party->communication_pincode ) ) value="{{ $party->communication_pincode }}" @endif />

                                    @if ($errors->has('communication_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('communication_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <div id="billing_address_section" style="display: none;">

                            <div class="form-group{{ $errors->has('billing_address') ? ' has-error' : '' }}">
                                <label for="billing_address" class="col-md-4 control-label">Shipping Address</label>

                                <div class="col-md-6">
                                    <textarea id="billing_address" class="form-control" name="billing_address">>@if( isset($party->billing_address) ) {{ $party->billing_address }}  @endif</textarea>

                                    @if ($errors->has('billing_address'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_address') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('billing_state') ? ' has-error' : '' }}">
                                <label for="billing_state" class="col-md-4 control-label">Shipping State</label>

                                <div class="col-md-6">
                                    <select id="billing_state" class="form-control" name="billing_state">
                                        <option value="0">Select State</option>
                                        @foreach($states as $state)
                                        <option @if( isset( $party->billing_state ) ) @if( $party->billing_state == $state->id ) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('billing_state'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_state') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('billing_city') ? ' has-error' : '' }}">
                                <label for="billing_city" class="col-md-4 control-label">Shipping City</label>

                                <div class="col-md-6">
                                    <input id="billing_city" type="text" class="form-control" name="billing_city" @if( isset( $party->billing_city ) ) value="{{ $party->billing_city }}" @endif />

                                    @if ($errors->has('billing_city'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_city') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('billing_pincode') ? ' has-error' : '' }}">
                                <label for="billing_pincode" class="col-md-4 control-label">Shipping Pincode</label>

                                <div class="col-md-6">
                                    <input id="billing_pincode" type="text" class="form-control" name="billing_pincode" @if( isset( $party->billing_pincode ) ) value="{{ $party->billing_pincode }}" @endif />

                                    @if ($errors->has('billing_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <label for="status_of_registration" class="col-md-4 control-label">Status of Company</label>
                            <div class="col-md-6">
                                <select class="form-control" id="status_of_registration" name="status_of_registration">
                                    <option @if( isset( $party->status_of_registration ) ) @if( $party->status_of_registration == 0 ) selected="selected" @endif @endif value="0">Unregistered</option>
                                    <option @if( isset( $party->status_of_registration ) ) @if( $party->status_of_registration == 1 ) selected="selected" @endif @endif value="1">Registered/Regular</option>
                                    <option @if( isset( $party->status_of_registration ) ) @if( $party->status_of_registration == 2 ) selected="selected" @endif @endif value="2">Consumer</option>
                                    <option @if( isset( $party->status_of_registration ) ) @if( $party->status_of_registration == 3 ) selected="selected" @endif @endif value="3">Composition Dealer</option>
                                    <option @if( isset( $party->status_of_registration ) ) @if( $party->status_of_registration == 4 ) selected="selected" @endif @endif value="4">Ecommerce Operator</option>
                                </select>
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('is_registered') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST Registered?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="is_registered" id="is_registered1" value="1"> Registered
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="is_registered" id="is_registered2" value="0" checked> Not Registered
                                </label>
                            </div>
                        </div> --}}

                        <div id="gst-block" class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}" @if( $party->status_of_registration == 0 || $party->status_of_registration == 3 ) style="display: none;" @endif >
                            <label for="gst" class="col-md-4 control-label">GST/TAN No.</label>

                            <div class="col-md-6">
                            <input id="gst" class="form-control" name="gst" value="{{ $party->gst }}" id="gst" minlength="15" maxlength="15" />

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('is_operator') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Tax Type?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="tax_type" id="tax_type1" value="tcs"> TCS
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="tax_type" id="tax_type2" value="tds" checked> TDS
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="tax_type" id="tax_type3" value="gst" checked> GST
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="tax_type" id="tax_type4" value="income tax" checked> Income Tax
                                </label>
                            </div>
                        </div> --}}

                        {{-- <div class="form-group{{ $errors->has('reverse_charge') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Reverse Charge?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="reverse_charge" id="reverse_charge1" @if( isset( $party->reverse_charge ) ) @if( $party->reverse_charge == "yes" ) checked="checked" @endif @endif value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="reverse_charge" id="reverse_charge2" @if( isset( $party->reverse_charge ) ) @if( $party->reverse_charge == "no" ) checked="checked" @endif @else  checked="checked" @endif value="no"> No
                                </label>
                            </div>
                        </div> --}}

                        <input type="hidden" name="reverse_charge" value="no" />

                        <div class="form-group">
                            <label class="col-md-4 control-label">Additional Info?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="additional_info" id="additional_info1" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="additional_info" id="additional_info2" value="no" checked> No
                                </label>
                            </div>
                        </div>

                        <div id="additional_info_section" style="display: none;">

                            <div class="form-group{{ $errors->has('tds_income_tax') ? ' has-error' : '' }}">
                                <label for="tds_income_tax" class="col-md-4 control-label">TDS Income Tax</label>
    
                                <div class="col-md-6">
                                    {{-- <input id="tds_income_tax" type="text" class="form-control" name="tds_income_tax" > --}}
                                    <label class="radio-inline">
                                        <input type="checkbox" name="tds_income_tax" value="1" @if( isset( $party->tds_income_tax ) ) @if( $party->tds_income_tax == 1 ) checked @endif @endif>
                                    </label>
    
                                    @if ($errors->has('tds_income_tax'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tds_income_tax') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('tds_gst') ? ' has-error' : '' }}">
                                <label for="tds_gst" class="col-md-4 control-label">TDS GST</label>
    
                                <div class="col-md-6">
                                    {{-- <input id="tds_gst" type="text" class="form-control" name="tds_gst" > --}}
                                    <label class="radio-inline">
                                        <input type="checkbox" name="tds_gst" value="1" @if( isset( $party->tds_gst ) ) @if( $party->tds_gst == 1 ) checked @endif @endif>
                                    </label>
    
                                    @if ($errors->has('tds_gst'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tds_gst') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('tcs_income_tax') ? ' has-error' : '' }}">
                                <label for="tcs_income_tax" class="col-md-4 control-label">TCS Income Tax</label>
    
                                <div class="col-md-6">
                                    {{-- <input id="tcs_income_tax" type="text" class="form-control" name="tcs_income_tax" > --}}
    
                                    <label class="radio-inline">
                                        <input type="checkbox" name="tcs_income_tax" value="1" @if( isset( $party->tcs_income_tax ) ) @if( $party->tcs_income_tax == 1 ) checked @endif @endif>
                                    </label>
    
                                    @if ($errors->has('tcs_income_tax'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tcs_income_tax') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('tcs_gst') ? ' has-error' : '' }}">
                                <label for="tcs_gst" class="col-md-4 control-label">TCS GST</label>
    
                                <div class="col-md-6">
                                    {{-- <input id="tcs_gst" type="text" class="form-control" name="tcs_gst" > --}}
    
                                    <label class="radio-inline">
                                    <input type="checkbox" name="tcs_gst" value="1" @if( isset( $party->tcs_gst ) ) @if( $party->tcs_gst == 1 ) checked @endif @endif>
                                </label>
    
                                    @if ($errors->has('tcs_gst'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tcs_gst') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('away_distance') ? ' has-error' : '' }}">
                            <label for="away_distance" class="col-md-4 control-label">Away Distance from us</label>

                            <div class="col-md-6">
                                <input id="away_distance" type="text" class="form-control" name="away_distance" required />

                                @if ($errors->has('away_distance'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('away_distance') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group{{ $errors->has('party_type') ? ' has-error' : '' }}">
                            <label for="party_type" class="col-md-4 control-label">Party Type</label>

                            <div class="col-md-6">        
                                <select id="party_type" class="form-control" name="party_type" required>
                                    <option @if( isset( $party->balance_type ) ) @if($party->balance_type == "creditor") selected="selected" @endif @endif value="cr">Sundary Creditor</option>
                                    <option @if( isset( $party->balance_type ) ) @if($party->balance_type == "debitor") selected="selected" @endif @endif value="dr">Sundary Debtor</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_balance') ? ' has-error' : '' }}">
                            <label for="opening_balance" class="col-md-4 control-label">Opening Balance</label>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-9" style="padding-right: 0;">
                                        <input id="opening_balance" type="text" class="form-control" name="opening_balance" @if( isset( $party->opening_balance ) ) value="{{ $party->opening_balance }}" @endif required />
                                    </div>
                                    <div class="col-xs-3" style="padding-left: 0;">
                                        <select id="balance_type" type="text" class="form-control" name="balance_type" required>
                                            <option @if( isset( $party->balance_type ) ) @if($party->balance_type == "creditor") selected="selected" @endif @endif value="creditor">CR</option>
                                            <option @if( isset( $party->balance_type ) ) @if($party->balance_type == "debitor") selected="selected" @endif @endif value="debitor">DR</option>
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

                        {{-- <div class="form-group{{ $errors->has('balance_type') ? ' has-error' : '' }}">
                            <label for="balance_type" class="col-md-4 control-label">Balance Type</label>

                            <div class="col-md-6">
                                <select id="balance_type" type="text" class="form-control" name="balance_type" required>
                                    <option @if( isset( $party->balance_type ) ) @if($party->balance_type == "creditor") selected="selected" @endif @endif value="creditor">Creditor</option>
                                    <option @if( isset( $party->balance_type ) ) @if($party->balance_type == "debitor") selected="selected" @endif @endif value="debitor">Debtor</option>
                                </select>

                                @if ( $errors->has('balance_type') )
                                    <span class="help-block">
                                        <strong>{{ $errors->first('balance_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group">
                            <label for="opening_balance_as_on" class="col-md-4 control-label">Opening Balance as on</label>

                            <div class="col-md-6">
                                <input type="text" id="opening_balance_as_on" class="form-control custom_date" name="opening_balance_as_on" @if( $party->opening_balance_as_on ) value="{{ \Carbon\Carbon::parse($party->opening_balance_as_on)->format('d/m/Y') }}" @endif readonly />

                                @if ($errors->has('opening_balance_as_on'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_balance_as_on') }}</strong>
                                    </span>
                                @endif
                                <p id="opening_balance_as_on_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="terms_and_condition" class="col-md-4 control-label">Terms and Conditions</label>
                            <div class="col-md-6">
                                <textarea class="form-control" name="terms_and_condition"> @if( isset( $party->terms_and_condition ) ) {{ $party->terms_and_condition }} @endif </textarea>
                            </div>
                        </div>

                        {{-- <div class="form-group" id="edit_applicable_date_block">
                            <label for="edit_applicable_date" class="col-md-4 control-label">Applicable Date</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control custom_date" id="edit_applicable_date" name="edit_applicable_date" placeholder="DD/MM/YYYY" value="{{ old('edit_applicable_date') }}" autocomplete="off" maxlength="10" required>
                                <p id="edit_applicable_date_validation_error" style="font-size: 12px; color: red;"></p>
                            </div>
                        </div> --}}

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine" id="update-party">
                                    Edit Party
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

        $("#opening_balance_as_on").on("keyup", function() {
            var date = $(this).val();

            validateDate(date, "opening_balance_as_on_validation_error", "#", "update-party", "#");
        });

        $("#edit_applicable_date").on("keyup", function() {
            var date = $(this).val();

            validateDate(date, "edit_applicable_date_validation_error", "#", "update-party", "#");
        });

        $("#status_of_registration").on('change', function(){
            var status_of_registration = $(this).val();

            // $("#registeration_applicable_date_block").show();

            if (status_of_registration == 0 || status_of_registration == 2) {
                $("#gst-block").hide();
            } else {
                $("#gst-block").show();
            }
        });

        $('input[name="is_registered"]').on('change', function(){
            var is_registered = $(this).val();
            if (is_registered == 1) {
                $("#gst-block").show();
            } else {
                $("#gst-block").hide();
            }
        });

        $("#checkbox_communication_address").on("change", function () {
            if($(this).is(":checked")){
                $("#communication_address_section").hide();
            } else {
                $("#communication_address_section").show();
            }
        });

        $("#checkbox_shipping_address").on("change", function () {
            if($(this).is(":checked")){
                $("#shipping_address_section").hide();
            } else {
                $("#shipping_address_section").show();
            }
        });

        $("#checkbox_billing_address").on("change", function () {
            if($(this).is(":checked")){
                $("#billing_address_section").hide();
            } else {
                $("#billing_address_section").show();
            }
        });

        $('input[name="additional_info"]').on("change", function() {
            if($(this).val() == "yes"){
                $("#additional_info_section").show();
            } else {
                $("#additional_info_section").hide();
            }
        });

        $("#party_type").on("change", function() {
            let val = $(this).find(":selected").val();

            if(val === 'cr') {
                $("#balance_type").val("creditor").trigger("change");
            }
            else if(val === 'dr') {
                $("#balance_type").val("debitor").trigger("change");
            }
        });
    </script>
@endsection
