@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('party.index') }}">View All Parties</a>&nbsp;&nbsp;
            <a href="{{ route('party.create') }}">Create New Party</a>&nbsp;&nbsp;
        </div>
    </div>
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

                        <div class="form-group{{ $errors->has('contact_person_name') ? ' has-error' : '' }}">
                            <label for="contact_person_name" class="col-md-4 control-label">Contact Person Name</label>

                            <div class="col-md-6">
                                <input id="contact_person_name" type="text" class="form-control" name="contact_person_name" value="{{ $party->contact_person_name }}" required>

                                @if ($errors->has('contact_person_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('contact_person_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Contact Email</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ $party->email }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Company Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $party->name }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}">
                            <label for="gst" class="col-md-4 control-label">GST Registered?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="is_registered" id="is_registered1" value="1" @if( $party->is_operator == 1 ) checked @endif> Registered
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="is_registered" id="is_registered2" value="0" @if( $party->is_operator == 0 ) checked @endif> Not Registered
                                </label>
                            </div>
                        </div>

                        <div id="gst-block" class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}" style="display: none;">
                            <label for="gst" class="col-md-4 control-label">GST / TAN No.</label>

                            <div class="col-md-6">
                                <input id="gst" class="form-control" name="gst" value="{{ $party->gst }}" />

                                @if ($errors->has('gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('is_operator') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Ecommerce Operator?</label>

                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="is_operator" id="is_operator1" value="1" @if( $party->is_operator == 1 ) checked @endif > Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="is_operator" id="is_operator2" value="0" @if( $party->is_operator == 0 ) checked @endif> No
                                </label>
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('tds_income_tax') ? ' has-error' : '' }}">
                            <label for="tds_income_tax" class="col-md-4 control-label">TDS Income Tax</label>

                            <div class="col-md-6">
                                <input id="tds_income_tax" type="text" class="form-control" name="tds_income_tax" value="{{ $party->tds_income_tax }}" >

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
                                <input id="tds_gst" type="text" class="form-control" name="tds_gst" value="{{ $party->tds_gst }}" >

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
                                <input id="tcs_income_tax" type="text" class="form-control" name="tcs_income_tax" value="{{ $party->tcs_income_tax }}" >

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
                                <input id="tcs_gst" type="text" class="form-control" name="tcs_gst" value="{{ $party->tcs_gst }}" >

                                @if ($errors->has('tcs_gst'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('tcs_gst') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="phone" class="col-md-4 control-label">Phone</label>

                            <div class="col-md-6">
                                <input id="phone" type="tel" class="form-control" name="phone" value="{{ $party->phone }}" required>

                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                            <label for="address" class="col-md-4 control-label">Communication Address</label>

                            <div class="col-md-6">
                                <textarea id="address" class="form-control" name="address" required>{{ $party->communication_address }}</textarea>

                                @if ($errors->has('address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('state') ? ' has-error' : '' }}">
                            <label for="state" class="col-md-4 control-label">Communication State</label>

                            <div class="col-md-6">
                                <select id="state" class="form-control" name="state" required>
                                    <option value="0">Select State</option>
                                    @foreach($states as $state)
                                    <option @if( $party->communication_state == $state->id ) selected="selected" @endif value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>

                                @if ($errors->has('state'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('state') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                            <label for="city" class="col-md-4 control-label">Communication City</label>

                            <div class="col-md-6">
                                <input id="city" type="text" class="form-control" name="city" value="{{ $party->communication_city }}" required />

                                @if ($errors->has('city'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('city') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('pincode') ? ' has-error' : '' }}">
                            <label for="pincode" class="col-md-4 control-label">Communication Pincode</label>

                            <div class="col-md-6">
                                <input id="pincode" type="text" class="form-control" name="pincode" value="{{ $party->communication_pincode }}" required />

                                @if ($errors->has('pincode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('pincode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 col-md-offset-4">
                            <div class="form-group">
                                <input type="checkbox" id="checkbox_shipping_address" name="checkbox_shipping_address" value="1" checked /> <label for="checkbox_shipping_address">Shipping same as Communication address</label> <br/>
                                <input type="checkbox" id="checkbox_billing_address" name="checkbox_billing_address" value="1" checked /> <label for="checkbox_billing_address">Billing same as Communication address</label> <br/>
                            </div>
                        </div>

                        <div id="shipping_address_section" style="display: none;">
                            <div class="form-group{{ $errors->has('shipping_address') ? ' has-error' : '' }}" >
                                <label for="shipping_address" class="col-md-4 control-label">Shipping Address</label>
    
                                <div class="col-md-6">
                                    <textarea id="shipping_address" class="form-control" name="shipping_address">{{ $party->shipping_address }}</textarea>
    
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
                                        <option @if( $party->shipping_state == $state->id ) selected="selected" @endif  value="{{ $state->id }}">{{ $state->name }}</option>
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
                                    <input id="shipping_city" type="text" class="form-control" name="shipping_city" value="{{ $party->shipping_city }}" />
    
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
                                    <input id="shipping_pincode" type="text" class="form-control" name="shipping_pincode" value="{{ $party->shipping_pincode }}" />
    
                                    @if ($errors->has('shipping_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('shipping_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div id="billing_address_section" style="display: none;">
                            <div class="form-group{{ $errors->has('billing_address') ? ' has-error' : '' }}">
                                <label for="billing_address" class="col-md-4 control-label">Billing Address</label>
    
                                <div class="col-md-6">
                                    <textarea id="billing_address" class="form-control" name="billing_address">{{ $party->billing_address }}</textarea>
    
                                    @if ($errors->has('billing_address'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_address') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('billing_state') ? ' has-error' : '' }}">
                                <label for="billing_state" class="col-md-4 control-label">Billing State</label>
    
                                <div class="col-md-6">
                                    <select id="billing_state" class="form-control" name="billing_state">
                                        <option value="0">Select State</option>
                                        @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
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
                                <label for="billing_city" class="col-md-4 control-label">Billing City</label>
    
                                <div class="col-md-6">
                                    <input id="billing_city" type="text" class="form-control" name="billing_city" value="{{ $party->billing_city }}" />
    
                                    @if ($errors->has('billing_city'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_city') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('billing_pincode') ? ' has-error' : '' }}">
                                <label for="billing_pincode" class="col-md-4 control-label">Billing Pincode</label>
    
                                <div class="col-md-6">
                                    <input id="billing_pincode" type="text" class="form-control" name="billing_pincode" value="{{ $party->billing_pincode }}" />
    
                                    @if ($errors->has('billing_pincode'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('billing_pincode') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>


                        <div class="form-group{{ $errors->has('away_distance') ? ' has-error' : '' }}">
                            <label for="away_distance" class="col-md-4 control-label">Away Distance from us</label>

                            <div class="col-md-6">
                                <input id="away_distance" type="text" class="form-control" name="away_distance" value="{{ $party->away_from_us_distance }}" required />

                                @if ($errors->has('away_distance'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('away_distance') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('opening_balance') ? ' has-error' : '' }}">
                            <label for="opening_balance" class="col-md-4 control-label">Opening Balance</label>

                            <div class="col-md-6">
                                <input id="opening_balance" type="text" class="form-control" name="opening_balance" value="{{ $party->opening_balance }}" required />

                                @if ($errors->has('opening_balance'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_balance') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('balance_type') ? ' has-error' : '' }}">
                            <label for="balance_type" class="col-md-4 control-label">Balance Type</label>

                            <div class="col-md-6">
                                <select id="balance_type" type="text" class="form-control" name="balance_type" required>
                                    <option @if( $party->balance_type == "credit" ) selected="selected" @endif value="credit">Credit</option>
                                    <option @if( $party->balance_type == "debit" ) selected="selected" @endif value="debit">Debit</option>
                                </select>

                                @if ( $errors->has('balance_type') )
                                    <span class="help-block">
                                        <strong>{{ $errors->first('balance_type') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="opening_balance_as_on" class="col-md-4 control-label">Opening Balance as on</label>

                            <div class="col-md-6">
                                <input type="date" id="opening_balance_as_on" class="form-control" name="opening_balance_as_on" style="line-height: 1;" value="{{ $party->opening_balance_as_on }}" />

                                @if ($errors->has('opening_balance_as_on'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_balance_as_on') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-success btn-mine">
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
        $('input[name="is_registered"]').on('change', function(){
            var is_registered = $(this).val();
            if (is_registered == 1) {
                $("#gst-block").show();
            } else {
                $("#gst-block").hide();
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
    </script>
@endsection