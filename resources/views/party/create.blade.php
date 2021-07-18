@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('create-party') !!}
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create New Party
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

                    <form class="form-horizontal" id="store-party" method="POST" action="{{ route('party.store') }}">
                        {{ csrf_field() }}


                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Business Name</label>

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

                        <div class="form-group{{ $errors->has('contact_person_name') ? ' has-error' : '' }}">
                            <label for="contact_person_name" class="col-md-4 control-label">Person Name</label>

                            <div class="col-md-6">
                                <input id="contact_person_name" type="text" class="form-control" name="contact_person_name" value="{{ old('contact_person_name') }}">

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
                                <input id="phone" type="tel" class="form-control" name="phone" value="{{ old('phone') }}" required>

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
                                <select class="form-control" name="business_place" id="business_place" required>
                                    <option selected disabled value="0">Select State</option>
                                    @foreach($states as $state)
                                    <option @if(old('business_place') == $state->id) selected="selected" @endif value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Contact Email</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('shipping_address') ? ' has-error' : '' }}">
                            <label for="shipping_address" class="col-md-4 control-label">Business Address</label>

                            <div class="col-md-6">
                                <textarea id="shipping_address" class="form-control" name="shipping_address" required>{{ old('shipping_address') }}</textarea>

                                @if ($errors->has('shipping_address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipping_address') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="form-group{{ $errors->has('shipping_state') ? ' has-error' : '' }}">
                            <label for="shipping_state" class="col-md-4 control-label">Business Place</label>

                            <div class="col-md-6">
                                <select id="shipping_state" class="form-control" name="shipping_state" required>
                                    <option value="0">Select State</option>
                                    @foreach($states as $state)
                                    <option @if(old('shipping_state') == $state->id) selected="selected" @endif value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('shipping_state'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipping_state') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group{{ $errors->has('shipping_city') ? ' has-error' : '' }}">
                            <label for="shipping_city" class="col-md-4 control-label">Business City</label>

                            <div class="col-md-6">
                                <input id="shipping_city" type="text" class="form-control" name="shipping_city" value="{{ old('shipping_city') }}" required />

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
                                <input id="shipping_pincode" type="text" class="form-control" name="shipping_pincode" value="{{ old('shipping_pincode') }}" required />

                                @if ($errors->has('shipping_pincode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipping_pincode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- ----------------------------------------------------------- --}}

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
                                    <textarea id="communication_address" class="form-control" name="communication_address"></textarea>

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
                                        <option value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
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
                                    <input id="communication_city" type="text" class="form-control" name="communication_city" />

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
                                    <input id="communication_pincode" type="text" class="form-control" name="communication_pincode" />

                                    @if ($errors->has('communication_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('communication_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>

                        {{-- <div id="shipping_address_section" style="display: none;">

                            <div class="form-group{{ $errors->has('shipping_address') ? ' has-error' : '' }}">
                                <label for="shipping_address" class="col-md-4 control-label">Shipping Address</label>

                                <div class="col-md-6">
                                    <textarea id="shipping_address" class="form-control" name="shipping_address"></textarea>

                                    @if ($errors->has('shipping_address'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('shipping_address') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('shipping_state') ? ' has-error' : '' }}">
                                <label for="shipping_state" class="col-md-4 control-label">Shipping State</label>

                                <div class="col-md-6">
                                    <select id="shipping_state" class="form-control" name="shipping_state">
                                        <option value="0">Select State</option>
                                        @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('shipping_state'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('shipping_state') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('shipping_city') ? ' has-error' : '' }}">
                                <label for="shipping_city" class="col-md-4 control-label">Shipping City</label>

                                <div class="col-md-6">
                                    <input id="shipping_city" type="text" class="form-control" name="shipping_city" />

                                    @if ($errors->has('shipping_city'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('shipping_city') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('shipping_pincode') ? ' has-error' : '' }}">
                                <label for="shipping_pincode" class="col-md-4 control-label">Shipping Pincode</label>

                                <div class="col-md-6">
                                    <input id="shipping_pincode" type="text" class="form-control" name="shipping_pincode" />

                                    @if ($errors->has('shipping_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('shipping_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div> --}}

                        <div id="billing_address_section" style="display: none;">

                            <div class="form-group{{ $errors->has('billing_address') ? ' has-error' : '' }}">
                                <label for="billing_address" class="col-md-4 control-label">Shipping Address</label>

                                <div class="col-md-6">
                                    <textarea id="billing_address" class="form-control" name="billing_address"></textarea>

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
                                        <option value="{{ $state->id }}">{{ $state->name }} ({{ $state->state_code }})</option>
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
                                    <input id="billing_city" type="text" class="form-control" name="billing_city" />

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
                                    <input id="billing_pincode" type="text" class="form-control" name="billing_pincode" />

                                    @if ($errors->has('billing_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <label for="status_of_registration" class="col-md-4 control-label">Status of Business</label>
                            <div class="col-md-6">
                                <select class="form-control" id="status_of_registration" name="status_of_registration">
                                    <option @if(old('status_of_registration') == 0) selected="selected" @endif value="0">Unregistered</option>
                                    <option @if(old('status_of_registration') == 1) selected="selected" @endif value="1">Registered/Regular</option>
                                    <option @if(old('status_of_registration') == 2) selected="selected" @endif value="2">Consumer</option>
                                    <option @if(old('status_of_registration') == 3) selected="selected" @endif value="3">Composition Dealer</option>
                                    <option @if(old('status_of_registration') == 4) selected="selected" @endif value="4">Ecommerce Operator</option>
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

                        <div id="gst-block" class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}" style="display: none;">
                            <label for="gst" class="col-md-4 control-label">GST/TAN No.</label>

                            <div class="col-md-6">
                                <input id="gst" class="form-control" name="gst" minlength="15" maxlength="15" value="{{ old('gst') }}" />

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif

                                <span class="help-block">
                                    <strong style="color: red" id="gst-error"></strong>
                                </span>
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
                                    <input @if(old('reverse_charge') == "yes") checked @endif type="radio" name="reverse_charge" id="reverse_charge1" value="yes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="reverse_charge" id="reverse_charge2" value="no" @if(old('reverse_charge')) @if(old('reverse_charge') == "no") checked @endif @else checked @endif> No
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
                                        <input type="checkbox" name="tds_income_tax" value="1" @if(old('tds_income_tax') == "1") checked @endif>
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
                                        <input type="checkbox" name="tds_gst" value="1" @if(old('tds_gst') == "1") checked @endif>
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
                                        <input type="checkbox" name="tcs_income_tax" value="1" @if(old('tcs_income_tax') == "1") checked @endif>
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
                                    <input type="checkbox" name="tcs_gst" value="1" @if(old('tcs_gst') == "1") checked @endif>
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

                        {{--  NO need to save party type as it is only required to set balance type automatically --}}
                        <div class="form-group{{ $errors->has('party_type') ? ' has-error' : '' }}">
                            <label for="party_type" class="col-md-4 control-label">Party Type</label>

                            <div class="col-md-6">        
                                <select id="party_type" class="form-control" name="party_type" required>
                                    <option @if(old('party_type') == 'creditor') selected="selected" @endif value="cr">Sundary Creditor</option>
                                    <option @if(old('party_type') == 'debitor') selected="selected" @endif value="dr">Sundary Debtor</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_balance') ? ' has-error' : '' }}">
                            <label for="opening_balance" class="col-md-4 control-label">Opening Balance</label>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-9" style="padding-right: 0;">
                                        <input id="opening_balance" type="text" class="form-control" name="opening_balance" value="{{ old('opening_balance') }}" required />
                                    </div>
                                    <div class="col-xs-3" style="padding-left: 0;">
                                        <select id="balance_type" class="form-control" name="balance_type" required>
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

                        {{-- <div class="form-group{{ $errors->has('balance_type') ? ' has-error' : '' }}">
                            <label for="balance_type" class="col-md-4 control-label">Balance Type</label>

                            <div class="col-md-6">
                                <select id="balance_type" type="text" class="form-control" name="balance_type" required>
                                    <option @if(old('balance_type') == 'creditor') selected="selected" @endif value="creditor">Creditor</option>
                                    <option @if(old('balance_type') == 'debitor') selected="selected" @endif value="debitor">Debtor</option>
                                </select>

                                @if ( $errors->has('balance_type') )
                                    <span class="help-block">
                                        <strong>{{ $errors->first('balance_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> --}}

                        <div class="form-group{{ $errors->has('opening_balance_as_on') ? ' has-error' : '' }}">
                            <label for="opening_balance_as_on" class="col-md-4 control-label">Opening Balance as on</label>

                            <div class="col-md-6">
                                <input type="text" id="opening_balance_as_on" class="form-control custom_date" placeholder="DD/MM/YYYY" name="opening_balance_as_on" value="{{ old('opening_balance_as_on') }}" />

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
                                <textarea class="form-control" name="terms_and_condition">{{ old('terms_and_condition') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine" id="submit-party">
                                    Add Party
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

        $(document).ready( function () {

            $("#opening_balance_as_on").on("keyup", function() {
                var date = $(this).val();

                validateDate(date, "opening_balance_as_on_validation_error", "#", "submit-party", "#");
            });


            $("#status_of_registration").on('change', function(){
                var status_of_registration = $(this).val();
                if (status_of_registration == 0 || status_of_registration == 2) {
                    $("#gst-block").hide();
                } else {
                    $("#gst-block").show();
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


            // $("#store-party").on("submit", function (e) {

            //     // e.preventDefault();

            //     if ( /^[a-z0-9]+$/i.test( $("#gst").val() ) ) {
            //         console.log("yes");
            //     } else {
            //         console.log("no");
            //     }

            // });

            $("#status_of_registration").on("change", function(){
                var status_of_registration = $("#status_of_registration").val();
                
                if(status_of_registration == 0 || status_of_registration == 2){
                    $("#gst").val("");
                    $("#gst-error").text("");
                }
            });

            $("#business_place").on("change", function(){
                var status_of_registration = $("#status_of_registration").val();
                if(status_of_registration == 1 || status_of_registration == 3 || status_of_registration == 4){
                    check_gst_validation();
                }
            });

            $("#gst").on("keyup", function(){
                check_gst_validation();
            });

            function check_gst_validation(){
                var gst = $("#gst").val();
                var business_place = $("#business_place").val();
                var shouldContinue = true;

                if(business_place == null){
                    $("#gst-error").text("Please select valid business place");
                    shouldContinue = false;
                } else{

                    if(gst.length > 15 || gst.length < 15){
                        $("#gst-error").text("GST no. should be 15 characters long");
                        shouldContinue = false;
                    }

                    if( business_place != null && gst.length == 15 ){
                        if(business_place == 1 || business_place == 2 || business_place == 3 || business_place == 4 || business_place == 5 || business_place == 6 || business_place == 7 || business_place == 8 || business_place == 9){
                            business_place = "0" + business_place;
                        }
                        if(business_place != gst.substring(0, 2)){
                            $("#gst-error").text("GST no. first 2 characters should match the state code");
                            shouldContinue = false;
                        }
                    }
                }


                if(shouldContinue){
                    $("#submit-party").attr("disabled", false);
                    $("#gst-error").text("");
                } else {
                    $("#submit-party").attr("disabled", true);
                }
            }


            $('input[name="additional_info"]').on("change", function() {
                if($(this).val() == "yes"){
                    $("#additional_info_section").show();
                } else {
                    $("#additional_info_section").hide();
                }
            });

            $("#name").on("keyup", function() {
                const url = "{{ route('validate.party.name') }}";
                let name = $(this).val();
                validateIfNameUnique(url, name, "#", "submit-party", "#", "name_validation_error");
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

        });
    </script>
@endsection
