@extends('layouts.dashboard')

<style>
    /* .row{
        margin-top:40px;
        padding: 0 10px;
    } */

    .clickable{
        cursor: pointer;
    }

    .panel-heading span {
        margin-top: -20px;
        font-size: 15px;
    }

</style>

@section('sidebar-active')
class="active"
@endsection

@section('sidebar-autoopen')
active
@endsection

@section('content')

<div class="container">
    
    {{-- <div class="row">
        <div class="col-md-4 col-md-offset-8 text-right">
            <button type="button" id="btn_round_off_setting" class="btn btn-success">Round Off Settings</button>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-md-12">
            <form method="POST" action="{{ route('user.profile.store') }}" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Company</h3>
                        <span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
                    </div>

                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label for="name" class="control-label">Company Name</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="name" type="text" class="form-control" name="name" @if( isset($user_profile->name) ) value="{{ $user_profile->name }}" @else value="{{ old('name') }}" @endif required>

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                                    <label for="phone" class="control-label">Contact No</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="phone" type="text" class="form-control num-only" name="phone" @if( isset($user_profile->phone) ) value="{{ $user_profile->phone }}" @else value="{{ old('phone') }}" @endif required maxlength="10">

                                        @if ($errors->has('phone'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('phone') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="control-label">Email</label>
                                    <input id="email" type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" readonly required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_of_company" class="control-label">Status Of Company</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <select class="form-control" id="type_of_company" name="type_of_company">
                                            <option @if(isset($user_profile->type_of_company)) @if($user_profile->type_of_company == 'proprietor') selected="selected" @endif @else @if(old('type_of_company') == 'proprietor') selected="selected" @endif @endif value="proprietor">Proprietor</option>
                                            <option @if(isset($user_profile->type_of_company)) @if($user_profile->type_of_company == 'firm') selected="selected" @endif @else @if(old('type_of_company') == 'firm') selected="selected" @endif @endif value="firm">Firm</option>
                                            <option @if(isset($user_profile->type_of_company)) @if($user_profile->type_of_company == 'company') selected="selected" @endif @else @if(old('type_of_company') == 'company') selected="selected" @endif @endif value="company">Company - CIN NOP</option>
                                            <option @if(isset($user_profile->type_of_company)) @if($user_profile->type_of_company == 'ngo') selected="selected" @endif @else @if(old('type_of_company') == 'ngo') selected="selected" @endif @endif value="ngo">NGO</option>
                                            <option @if(isset($user_profile->type_of_company)) @if($user_profile->type_of_company == 'other') selected="selected" @endif @else @if(old('type_of_company') == 'other') selected="selected" @endif @endif value="other">Other</option>
                                        </select>
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                                    <label for="type" class="control-label">Type?</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="type" id="type1" value="manufacturer" 
                                            @if( isset($user_profile->type) ) 
                                                @if( $user_profile->type == "manufacturer" ) 
                                                    checked  
                                                @endif
                                            @else
                                                @if( old('type') == 'manufacturer' )
                                                    checked
                                                @endif
                                            @endif> Manufacturer
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="type" id="type2" value="retailer" 
                                            @if( isset($user_profile->type) ) 
                                                @if( $user_profile->type == "retailer" ) 
                                                    checked  
                                                @endif 
                                            @else
                                                @if( old('type') == 'retailer' )
                                                    checked
                                                @else 
                                                    checked 
                                                @endif
                                            @endif
                                            > Retailer
                                        </label>
                                    {{-- </div> --}}
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('logo') ? ' has-error' : '' }}">
                                    <label for="logo" class="control-label">Logo</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="logo" type="file" class="form-control" name="logo" style="height: auto;">

                                        @if ($errors->has('logo'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('logo') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            
                        </div>

                        @if($user_profile->logo != null)
                        <div class="row">
                            <div class="col-md-6">
                                <label>Last Logo uploaded</label>
                                <img src="{{ asset('storage/'.$user_profile->logo) }}" style="height: 80px; display: block;" />
                            </div>
                            <div class="form-group">
                                <button type="button" id="remove-logo" class="btn btn-sm btn-success">Remove Logo</button>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
{{-- -------------------------------------------------------------------------------------------------------------------------- --}}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Address</h3>
                        <span class="pull-right clickable panel-collapsed"><i class="glyphicon glyphicon-chevron-down"></i></span>
                    </div>

                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Business Place</label>
                                    <select type="text" class="form-control" placeholder="Place of Business" name="place_of_business">
                                        @foreach($states as $state)
                                        <option @if( isset($user_profile->place_of_business) ) @if($user_profile->place_of_business == $state->id) selected="selected" @endif @else @if(old('place_of_business') == $state->id) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                                    <label for="address" class="control-label">Business Address</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <textarea id="address" class="form-control" name="address" required> @if( isset($user_profile->communication_address) ) {{ $user_profile->communication_address }} @else {{ old('address') }} @endif</textarea>

                                        @if ($errors->has('address'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('address') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            {{-- <div class="col-md-4">
                                <div class="form-group{{ $errors->has('state') ? ' has-error' : '' }}">
                                    <label for="state" class="control-label">State</label>


                                        <select id="state" class="form-control" name="state" required>
                                            <option value="0">Select State</option>
                                            @foreach($states as $state)
                                            <option @if( isset($user_profile->communication_state) ) @if($user_profile->communication_state == $state->id) selected="selected" @endif @else @if(old('state') == $state->id) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }}</option>
                                            @endforeach
                                        </select>

                                        @if ($errors->has('state'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('state') }}</strong>
                                            </span>
                                        @endif

                                </div>
                            </div> --}}
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                                    <label for="city" class="control-label">City</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="city" type="text" class="form-control" name="city" @if( isset($user_profile->communication_city) ) value="{{ $user_profile->communication_city }}" @else value="{{ old('city') }}" @endif required />

                                        @if ($errors->has('city'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('city') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('pincode') ? ' has-error' : '' }}">
                                    <label for="pincode" class="control-label">Pincode</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="pincode" type="text" class="form-control num-only" name="pincode" @if( isset($user_profile->communication_pincode) ) value="{{ $user_profile->communication_pincode }}" @else value="{{ old('pincode') }}" @endif required />

                                        @if ($errors->has('pincode'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('pincode') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="checkbox" id="checkbox_shipping_address" name="checkbox_shipping_address" value="1" checked /> <label for="checkbox_shipping_address">Shipping same as Business address</label> <br/>
                                    <input type="checkbox" id="checkbox_billing_address" name="checkbox_billing_address" value="1" checked /> <label for="checkbox_billing_address">Billing same as Business address</label> <br/>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="shipping_address_section" style="display: none;">
                            <hr />
                            <div class="col-md-12">
                                <div class="form-group{{ $errors->has('shipping_address') ? ' has-error' : '' }}">
                                    <label for="shipping_address" class="control-label">Shipping Address</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <textarea id="shipping_address" class="form-control" name="shipping_address" >@if( isset($user_profile->shipping_address) ) {{ $user_profile->shipping_address }} @endif</textarea>

                                        @if ($errors->has('shipping_address'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('shipping_address') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('shipping_state') ? ' has-error' : '' }}">
                                    <label for="shipping_state" class="control-label">Shipping State</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <select id="shipping_state" class="form-control" name="shipping_state" >
                                            <option value="0">Select State</option>
                                            @foreach($states as $state)
                                            <option @if( isset($user_profile->shipping_state) ) @if($user_profile->shipping_state == $state->id) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }}</option>
                                            @endforeach
                                        </select>

                                        @if ($errors->has('shipping_state'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('shipping_state') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('shipping_city') ? ' has-error' : '' }}">
                                    <label for="shipping_city" class="control-label">Shipping City</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="shipping_city" type="text" class="form-control" name="shipping_city" @if( isset($user_profile->shipping_city) ) value="{{ $user_profile->shipping_city }}" @endif  />

                                        @if ($errors->has('shipping_city'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('shipping_city') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('shipping_pincode') ? ' has-error' : '' }}">
                                    <label for="shipping_pincode" class="control-label">Shipping Pincode</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="shipping_pincode" type="text" class="form-control" name="shipping_pincode" @if( isset($user_profile->shipping_pincode) ) value="{{ $user_profile->shipping_pincode }}" @endif  />

                                        @if ($errors->has('shipping_pincode'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('shipping_pincode') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                        </div>

                        <div class="row" id="billing_address_section" style="display: none;">
                            <hr />

                            <div class="col-md-12">
                                <div class="form-group{{ $errors->has('billing_address') ? ' has-error' : '' }}">
                                    <label for="billing_address" class="control-label">Billing Address</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <textarea id="billing_address" class="form-control" name="billing_address" > @if( isset($user_profile->billing_address) ) {{ $user_profile->billing_address }} @endif </textarea>

                                        @if ($errors->has('billing_address'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('billing_address') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('billing_state') ? ' has-error' : '' }}">
                                    <label for="billing_state" class="control-label">Billing State</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <select id="billing_state" class="form-control" name="billing_state" >
                                            <option value="0">Select State</option>
                                            @foreach($states as $state)
                                            <option @if( isset($user_profile->billing_state) ) @if($user_profile->billing_state == $state->id) selected="selected" @endif @endif value="{{ $state->id }}">{{ $state->name }}</option>
                                            @endforeach
                                        </select>

                                        @if ($errors->has('billing_state'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('billing_state') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('billing_city') ? ' has-error' : '' }}">
                                    <label for="billing_city" class="control-label">Billing City</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="billing_city" type="text" class="form-control" name="billing_city" @if( isset($user_profile->billing_city) ) value="{{ $user_profile->billing_city }}" @endif  />

                                        @if ($errors->has('billing_city'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('billing_city') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group{{ $errors->has('billing_pincode') ? ' has-error' : '' }}">
                                    <label for="billing_pincode" class="control-label">Billing Pincode</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="billing_pincode" type="text" class="form-control" name="billing_pincode" @if( isset($user_profile->billing_pincode) ) value="{{ $user_profile->billing_pincode }}" @endif  />

                                        @if ($errors->has('billing_pincode'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('billing_pincode') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
{{-- -------------------------------------------------------------------------------------------------------------------------- --}}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Accounting & GST Info</h3>
                        <span class="pull-right clickable panel-collapsed"><i class="glyphicon glyphicon-chevron-down"></i></span>
                    </div>

                    <div class="panel-body">


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('gst') ? ' has-error' : '' }}" style="display: inline-block;">

                                    <label for="gst" class="control-label">GST Status</label>
                                    {{-- <div class="col-md-6"> --}}
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="is_registered" id="is_registered1" value="0" @if(isset($user_profile->registered)) @if($user_profile->registered == 0) checked @endif @else checked @endif> Not Registered
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="is_registered" id="is_registered2" value="1"  type="radio" name="is_registered" id="is_registered2" @if(isset($user_profile->registered)) @if($user_profile->registered == 1) checked @endif @endif> Registered
                                        </label>
                                        {{-- <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="is_registered" id="is_registered3" value="2" @if(isset($user_profile->registered)) @if($user_profile->registered == 2) checked @endif @else checked @endif> Regular
                                        </label> --}}
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="is_registered" id="is_registered3" value="3" @if(isset($user_profile->registered)) @if($user_profile->registered == 3) checked @endif @else checked @endif> Composition
                                        </label>
                                        {{-- <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="is_registered" id="is_registered4" value="4" @if(isset($user_profile->registered)) @if($user_profile->registered == 4) checked @endif @else checked @endif> Ecommerce
                                        </label> --}}
                                        {{-- </div> --}}
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="is_registered" id="is_registered4" value="4" @if(isset($user_profile->registered)) @if($user_profile->registered == 4) checked @endif @else checked @endif> Ecommerce Operator
                                        </label>
                                        <p id="company_status_change_update"></p>
                                    {{-- <div class="col-md-3">
                                        <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                                            <label for="type" class="control-label"></label>


                                                <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                                    <input type="radio" name="is_operator" id="is_operator1" value="yes" > Yes
                                                </label>
                                                <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                                    <input type="radio" name="is_operator" id="is_operator2" value="no" checked> No
                                                </label>

                                        </div>
                                    </div> --}}
                                </div>
                                {{-- <div class="form-group">
                                    @if(isset($user_profile->registered))
                                        <input type="hidden" name="gst_status_type" value="updated" />
                                        <input type="hidden" name="gst_status_old" value="{{ $user_profile->registered }}" />
                                        <label>Applicable Date</label>
                                        <input type="text" name="gst_status_applicable_date" class="form-control" placeholder="GST Status Applicate date" />
                                    @else
                                        <input type="hidden" name="gst_status_type" value="new" />
                                    @endif
                                </div> --}}
                            </div>

                            <div class="col-md-6">
                                <div id="gst-block" class="{{ $errors->has('gst') ? ' has-error' : '' }}" @if(isset($user_profile->registered) ) @if($user_profile->registered == 0 || $user_profile->registered == 2) style="display: none;" @endif @else style="display: none;" @endif >
                                    <div class="form-group">
                                        <label for="gst" class="control-label">GST No.</label>

                                        {{-- <div class="col-md-6"> --}}
                                            <input id="gst" class="form-control" name="gst" @if( isset($user_profile->gst) ) value="{{ $user_profile->gst }}" @endif />

                                            @if ($errors->has('gst'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('gst') }}</strong>
                                                </span>
                                            @endif
                                        {{-- </div> --}}
                                    </div>
                                </div>

                                <div class="form-group" id="show_state_regular" @if(isset($user_profile->registered) ) @if($user_profile->registered == 0 || $user_profile->registered == 1 || $user_profile->registered == 3 || $user_profile->registered == 4) style="display: none;" @endif @else style="display: none;" @endif>
                                    <label>GST Invoice</label>
                                    <input type="text" name="gst_invoice" class="form-control" />
                                </div>
                                <div class="form-group" id="show_state_composition" @if(isset($user_profile->registered) ) @if($user_profile->registered == 0 || $user_profile->registered == 1 || $user_profile->registered == 2 || $user_profile->registered == 4) style="display: none;" @endif @else style="display: none;" @endif>
                                    
                                    <div class="form-group">
                                        <label>% on Sale of Invoice</label>
                                        <input type="text" name="percent_on_sale_of_invoice" class="form-control num-only" value="{{ $user_profile->percent_on_sale_of_invoice }}" />
                                    </div>

                                    <div class="form-group">
                                        <label>Composition Applicable Date</label>
                                        <input type="text" name="composition_applicable_date" class="form-control custom_date" value="{{ \Carbon\Carbon::parse($user_profile->composition_applicable_date)->format('d/m/Y') }}" placeholder="DD/MM/YYYY" />
                                    </div>

                                </div>

                            </div>
                        </div>

                        <hr/>

                        {{-- <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('add_lump_sump') ? ' has-error' : '' }}">
                                    <label for="add_lump_sump" class="control-label">Add Lumpsump</label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="add_lump_sump" id="add_lump_sump2" value="no"  type="radio" id="add_lump_sump2" @if(isset($user_profile->add_lump_sump)) @if($user_profile->add_lump_sump == "no") checked @endif @else checked @endif> No
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="add_lump_sump" id="add_lump_sump1" value="yes" @if(isset($user_profile->add_lump_sump)) @if($user_profile->add_lump_sump == "yes") checked @endif @endif> Yes
                                        </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div id="add_lump_sump_block" @if(isset($user_profile->add_lump_sump) ) @if($user_profile->add_lump_sump == "yes") style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif >
                                    <div class="form-group">
                                        <label for="gst" class="control-label">Gross Profit on Sale %</label>


                                            <input id="gross_profit" class="form-control" name="gross_profit" @if( isset($user_profile->gross_profit) ) value="{{ $user_profile->gross_profit }}" @endif />

                                            @if ($errors->has('gross_profit'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('gross_profit') }}</strong>
                                                </span>
                                            @endif

                                    </div>
                                </div>

                            </div>
                        </div> --}}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('inventory_type') ? ' has-error' : '' }}">
                                    <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                        <input type="radio" name="inventory_type" id="inventory_type1" value="with_inventory" @if(isset($user_profile->inventory_type)) @if($user_profile->inventory_type == "with_inventory") checked @endif @endif> Accounts with Inventory (Without Lump Sump)
                                    </label>
                                    <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                        <input type="radio" name="inventory_type" id="inventory_type2" value="without_inventory" @if(isset($user_profile->inventory_type)) @if($user_profile->inventory_type == "without_inventory") checked @endif @else checked @endif> Accounts without Inventory (Lump Sump)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="with_inventory_block" @if(isset($user_profile->inventory_type) ) @if($user_profile->inventory_type == "with_inventory") style="display: block;" @else style="display: none;" @endif @else style="display: none;" @endif>
                                    <div class="form-group">
                                        {{-- <label for="with_inventory_type" class="control-label">Method of Value</label>

                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="with_inventory_type" id="with_inventory_type1" value="lifo" @if(isset($user_profile->with_inventory_type)) @if($user_profile->with_inventory_type == "lifo") checked @endif @endif> LIFO
                                        </label>
                                        <label style="margin-bottom: 0; font-weight: normal; padding-left: 0;" class="col-md-12">
                                            <input type="radio" name="with_inventory_type" id="with_inventory_type2" value="fifo" @if(isset($user_profile->with_inventory_type)) @if($user_profile->with_inventory_type == "fifo") checked @endif @else checked @endif> FIFO
                                        </label>

                                        @if ($errors->has('with_inventory_type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('with_inventory_type') }}</strong>
                                            </span>
                                        @endif --}}

                                    </div>
                                </div>
                                <div id="without_inventory_block" @if(isset($user_profile->inventory_type) ) @if($user_profile->inventory_type == "without_inventory") style="display: block;" @else style="display: none;" @endif @endif>
                                    <div class="form-group">
                                        <label>G.P % on sale value</label>
                                        <input type="text" name="gp_percent_on_sale_value" class="form-control num-only" value="{{ $user_profile->gp_percent_on_sale_value }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="authorised_name" class="control-label">Authorised Name</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input type="text" class="form-control alpha-only" id="authorised_name" name="authorised_name" @if( isset($user_profile->authorised_name) ) value="{{ $user_profile->authorised_name }}" @else value="{{ old('authorised_name') }}" @endif />
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('authorised_signature') ? ' has-error' : '' }}">
                                    <label for="authorised_signature" class="control-label">Authorised Signature</label>

                                    {{-- <div class="col-md-6"> --}}
                                        <input id="authorised_signature" type="file" class="form-control" name="authorised_signature" style="height: auto;">

                                        @if ($errors->has('authorised_signature'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('authorised_signature') }}</strong>
                                            </span>
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-6">
                                <div class="form-group">
                                    <button type="button" id="remove-signature" class="btn btn-sm btn-success">Remove Signature</button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    {{-- {{ auth()->user()->invoices->count() . auth()->user()->purchases->count() . auth()->user()->purchaseRemainingAmounts->count() . auth()->user()->saleRemainingAmounts->count() . auth()->user()->partyRemainingAmounts->count() . auth()->user()->partyRemainingAmounts->count() }} --}}
                                    <div class="col-md-6">
                                        <label for="book_beginning_from" class="control-label">Book Beginning from</label>
                                        <input type="text" class="form-control custom_date" id="book_beginning_from" name="book_beginning_from" @if( isset($user_profile->book_beginning_from) ) value="{{ \Carbon\Carbon::parse($user_profile->book_beginning_from)->format('d/m/Y') }}" @else value="{{ old('book_beginning_from') }}" @endif placeholder="DD/MM/YYYY" @if( auth()->user()->invoices->count() > 0 || auth()->user()->purchases->count() > 0 || auth()->user()->purchaseRemainingAmounts->count() > 0 || auth()->user()->saleRemainingAmounts->count() > 0 || auth()->user()->partyRemainingAmounts->count() > 0 || auth()->user()->partyRemainingAmounts->count() > 0 ) readonly @endif />
                                    </div>

                                    {{-- <div class="col-md-6">
                                        <label for="book_ending_on" class="control-label">Book Ending on</label>
                                        <input type="text" class="form-control custom_date" id="book_ending_on" name="book_ending_on" @if( isset($user_profile->book_ending_on) ) value="{{ \Carbon\Carbon::parse($user_profile->book_ending_on)->format('d/m/Y') }}" @else value="{{ old('book_ending_on') }}" @endif placeholder="DD/MM/YYYY" />
                                    </div> --}}

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    
                                    <div class="col-md-6">
                                        <label for="financial_year_from" class="control-label">Financial Year from</label>
                                        <input type="text" class="form-control custom_date" id="financial_year_from" name="financial_year_from" @if( isset($user_profile->financial_year_from) ) value="{{ \Carbon\Carbon::parse($user_profile->financial_year_from)->format('d/m/Y') }}" @else value="{{ old('financial_year_from') }}" @endif placeholder="DD/MM/YYYY" />
                                    </div>

                                    <div class="col-md-6">
                                        <label for="financial_year_to" class="control-label">Financial Year to</label>
                                        <input type="text" class="form-control custom_date" id="financial_year_to" name="financial_year_to" @if( isset($user_profile->financial_year_to) ) value="{{ \Carbon\Carbon::parse($user_profile->financial_year_to)->format('d/m/Y') }}" @else value="{{ old('financial_year_to') }}" @endif placeholder="DD/MM/YYYY" />
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('opening_balance') ? ' has-error' : '' }}">
                                    <label for="opening_balance" class="control-label">Opening Balance</label>

                                        <input id="opening_balance" type="text" class="form-control" name="opening_balance" @if( isset($user_profile->opening_balance) ) value="{{ $user_profile->opening_balance }}" @endif required />

                                        @if ($errors->has('opening_balance'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('opening_balance') }}</strong>
                                            </span>
                                        @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="opening_balance_as_on" class="control-label">Opening Balance as on</label>

                                        <input type="date" id="opening_balance_as_on" class="form-control" name="opening_balance_as_on" style="line-height: 1;" @if( isset($user_profile->opening_balance_as_on) ) value="{{ $user_profile->opening_balance_as_on }}" @endif />

                                        @if ($errors->has('opening_balance_as_on'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('opening_balance_as_on') }}</strong>
                                            </span>
                                        @endif

                                </div>
                            </div>
                        </div> --}}

                        {{-- @if($user_profile->name != null)
                            <div class="col-md-offset-4 col-md-6">
                                <p><strong>Last Logo uploaded</strong></p>
                                <img src="{{ asset('storage/'.$user_profile->logo) }}" style="height: 80px;" />
                            </div>
                        @endif --}}


                </div>
            </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <textarea class="form-control" name="bank_information" placeholder="Bank Information" @if(isset($user_profile->bank_information)) readonly @endif>@if(isset($user_profile->bank_information)) {{ $user_profile->bank_information }} @endif</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <textarea class="form-control" name="terms_and_condition" placeholder="Terms and Conditions" @if(isset($user_profile->terms_and_condition)) readonly @endif>@if(isset($user_profile->terms_and_condition)) {{ $user_profile->terms_and_condition }} @endif</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success btn-mine">Update Profile</button>
                    </div>

                    <div class="col-md-6">
                        <button type="button" id="btn_opening_balance" class="btn btn-success">Update opening balance</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- <div class="modal" id="modal_round_off_setting">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Round Off Settings</h4>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('user.round.off.setting') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <button type="button" class="btn btn-success" id="add_account">Add Account +</button>
                    </div>
                    <div class="form-group" id="account_selection_block" style="display: none">
                        <select class="form-control" id="account_selection">
                            <option disabled selected>Select Account</option>
                            <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="radio-inline"><input type="radio" id="transaction_type_purchase" name="transaction_type" value="purchase">Purchase</label>
                        <label class="radio-inline"><input type="radio" id="transaction_type_sale" name="transaction_type" value="sale">Sale</label>
                    </div>
                    
                    <div class="purchase_block" style="display: none;">
                        
                        <div class="form-group">
                            <label class="radio-inline"><input type="radio" name="purchase_type" value="indirect_expense" @if(auth()->user()->roundOffSetting->purchase_type != null && auth()->user()->roundOffSetting->purchase_type == 'indirect_expense') checked @endif>Indirect Expense</label>
                            <label class="radio-inline"><input type="radio" name="purchase_type" value="indirect_income" @if(auth()->user()->roundOffSetting->purchase_type != null && auth()->user()->roundOffSetting->purchase_type == 'indirect_income') checked @endif >Indirect Income</label>
                        </div>
        
                        <div class="form-group">
                            <div class="checkbox">
                                <label><input type="checkbox" name="purchase_round_off_item[]" value="item_amount" @if(auth()->user()->roundOffSetting->purchase_item_amount != null && auth()->user()->roundOffSetting->purchase_item_amount == 'yes') checked @endif>Item Amount</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="purchase_round_off_item[]" value="gst_amount" @if(auth()->user()->roundOffSetting->purchase_gst_amount != null && auth()->user()->roundOffSetting->purchase_gst_amount == 'yes') checked @endif>GST Amount</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="purchase_round_off_item[]" value="total_amount" @if(auth()->user()->roundOffSetting->purchase_total_amount != null && auth()->user()->roundOffSetting->purchase_total_amount == 'yes') checked @endif>Total Amount</label>
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="manual" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'manual') checked @endif />Manual</label>
                            <label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="normal" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'normal') checked @endif>Normal</label>
                            <label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="upward" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'upward') checked @endif>Upward</label>
                            <label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="downward" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'downward') checked @endif>Downward</label>
                        </div>
        
                        <div class="form-group" id="purchase_upward_to_block" style="display: none;">
                            <label for="purchase_upward_to">To Place (upward)</label>
                            <input type="text" name="purchase_upward_to" id="purchase_upward_to" class="form-control" placeholder="To place" value="2" readonly />
                        </div>
                        <div class="form-group" id="purchase_downward_to_block" style="display: none;">
                            <label for="purchase_downward_to">To Place (downward)</label>
                            <input type="text" name="purchase_downward_to" id="purchase_downward_to" class="form-control" placeholder="To place" value="2" readonly />
                        </div>
                    </div>

                    <div class="sale_block" style="display: none;">
                        
                        <div class="form-group">
                            <label class="radio-inline"><input type="radio" name="sale_type" value="indirect_expense" @if(auth()->user()->roundOffSetting->sale_type != null && auth()->user()->roundOffSetting->sale_type == 'indirect_expense') checked @endif>Indirect Expense</label>
                            <label class="radio-inline"><input type="radio" name="sale_type" value="indirect_income" @if(auth()->user()->roundOffSetting->sale_type != null && auth()->user()->roundOffSetting->sale_type == 'indirect_income') checked @endif >Indirect Income</label>
                        </div>
        
                        <div class="form-group">
                            <div class="checkbox">
                                <label><input type="checkbox" name="sale_round_off_item[]" value="item_amount" @if(auth()->user()->roundOffSetting->sale_item_amount != null && auth()->user()->roundOffSetting->sale_item_amount == 'yes') checked @endif>Item Amount</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="sale_round_off_item[]" value="gst_amount" @if(auth()->user()->roundOffSetting->sale_gst_amount != null && auth()->user()->roundOffSetting->sale_gst_amount == 'yes') checked @endif>GST Amount</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="sale_round_off_item[]" value="total_amount" @if(auth()->user()->roundOffSetting->sale_total_amount != null && auth()->user()->roundOffSetting->sale_total_amount == 'yes') checked @endif>Total Amount</label>
                            </div>

                        </div>
        
                        <div class="form-group">
                            <label class="radio-inline"><input type="radio" name="sale_round_off_to" value="manual" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'manual') checked @endif />Manual</label>
                            <label class="radio-inline"><input type="radio" name="sale_round_off_to" value="normal" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'normal') checked @endif>Normal</label>
                            <label class="radio-inline"><input type="radio" name="sale_round_off_to" value="upward" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'upward') checked @endif>Upward</label>
                            <label class="radio-inline"><input type="radio" name="sale_round_off_to" value="downward" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'downward') checked @endif>Downward</label>
                        </div>
        
                        <div class="form-group" id="sale_upward_to_block" style="display: none;">
                            <label for="sale_upward_to">To Place (upward)</label>
                            <input type="text" name="sale_upward_to" id="sale_upward_to" class="form-control" placeholder="To place" value="2" readonly />
                        </div>
                        <div class="form-group" id="sale_downward_to_block" style="display: none;">
                            <label for="sale_downward_to">To Place (downward)</label>
                            <input type="text" name="sale_downward_to" id="sale_downward_to" class="form-control" placeholder="To place" value="2" readonly />
                        </div>
                    </div>

                    <div class="row form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <button type="submit" class="btn btn-success">Save Settings</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div> --}}

@endsection

@section('trigger_autoclose_sidebar')
    <script>
        $(document).ready(function() {
            console.log('side bar collapse hoja');
            $("#sidebarCollapse").trigger("click");
        });
    </script>
@endsection

@section('scripts')
    <script>

        $("#remove-logo").on("click", function() {
            $.ajax({
                method: "post",
                url: "{{ route('user.profile.remove.logo') }}",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    // console.log(response);
                    show_custom_alert(response.message);
                    if(response.success){
                        location.reload();
                    }
                }
            });
        });

        $("#remove-signature").on("click", function() {
            $.ajax({
                method: "post",
                url: "{{ route('user.profile.remove.authorized.signature') }}",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    // console.log(response);
                    show_custom_alert(response.message);
                    if(response.success){
                        location.reload();
                    }
                }
            });
        });

        $('input[name="percent_on_sale_of_invoice"]').on("keyup", function(){
            $('input[name="percent_on_sale_of_invoice"]').val($(this).val());
        })
        $('input[name="is_registered"]').on('change', function(){
            var is_registered = $(this).val();

            if( is_registered == 0 ) {
                $("#gst-block").hide();
                $("#show_state_regular").hide();
                $("#show_state_composition").hide();
            }

            if (is_registered == 1) {
                $("#gst-block").show();
                $("#show_state_regular").hide();
                $("#show_state_composition").hide();
            }

            else if(is_registered == 2){
                $("#gst-block").hide();
                $("#show_state_regular").show();
                $("#show_state_composition").hide();
            }

            else if(is_registered == 3){
                $("#gst-block").show();
                $("#show_state_composition").show();
                $("#show_state_regular").hide();
            }

            if (is_registered == 4) {
                $("#gst-block").show();
                $("#show_state_regular").hide();
                $("#show_state_composition").hide();
            }

        });

        $('input[name="add_lump_sump"]').on('change', function(){
            var is_registered = $(this).val();
            if (is_registered == "yes") {
                $("#add_lump_sump_block").show();
            }
            else {
                $("#add_lump_sump_block").hide();
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


        $(document).on('click', '.panel-heading span.clickable', function(e){
            var $this = $(this);
            if(!$this.hasClass('panel-collapsed')) {
                $this.parents('.panel').find('.panel-body').slideUp();
                $this.addClass('panel-collapsed');
                $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            } else {
                $this.parents('.panel').find('.panel-body').slideDown();
                $this.removeClass('panel-collapsed');
                $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            }
        });

/*
        // $(document).on('change', '#type_of_company_state', function(){
        //     // console.log($(this).val());

        //     if( $(this).val() == 'regular' ) {
        //         $("#show_state_regular").show();
        //         $("#show_state_composition").hide();
        //     }

        //     if( $(this).val() == 'composition' ) {
        //         $("#show_state_composition").show();
        //         $("#show_state_regular").hide();
        //     }
        // });
*/

        $('input[name="bill_no_type"]').on("change", function() {
            
            if($(this).val() == 'auto'){
                $("#invoice_auto_setting_block").show();
            } else {
                $("#invoice_auto_setting_block").hide();
            }

        });

        // $('input[name="want_round_off"]').on("change", function () {
        //     if( $(this).val() == "yes"){
        //         $("#round_off_block").show();
        //     } else {
        //         $("#round_off_block").hide();
        //     }
        // });

        // $('#btn_round_off_setting').on("click", function() {
        //     $('#modal_round_off_setting').modal("show");
        // });

        

        // $(document).ready(function(){

        //     $('#btn_round_off_setting').on("click", function() {
        //         $('#modal_round_off_setting').modal("show");
        //     });

        //     $("#add_account").on("click", function(){
        //         $("#account_selection_block").show();
        //     });

        //     $(document).on("change", "#account_selection", function(){
        //         console.log($(this).val());

        //         if( $(this).val() == 'sale' ){
        //             $(".sale_block").show();
        //             $("#transaction_type_sale").attr("checked", true);
        //         } else {
        //             $(".sale_block").hide();
        //             $("#transaction_type_sale").attr("checked", false);
        //         }

        //         if( $(this).val() == 'purchase' ){
        //             $(".purchase_block").show();
        //             $("#transaction_type_purchase").attr("checked", true);
        //         } else {
        //             $(".purchase_block").hide();
        //             $("#transaction_type_purchase").attr("checked", false);
        //         }
        //     });

        //     $('input[name="round_off_to"]').on("change", function(){
        //         if( $(this).is(":checked") ){

        //             if( $(this).val() == "upward" ){
        //                 $("#upward_to_block").show();
        //             } else {
        //                 $("#upward_to_block").hide();
        //             }

        //             if( $(this).val() == "downward" ){
        //                 $("#downward_to_block").show();
        //             } else {
        //                 $("#downward_to_block").hide();
        //             }

        //         }

        //     });

        //     $('input[name="transaction_type"]').on("change", function(){
        //         if($(this).val() == 'sale'){
        //             $(".sale_block").show();
        //         } else {
        //             $(".sale_block").hide();
        //         }

        //         if($(this).val() == 'purchase'){
        //             $(".purchase_block").show();
        //         } else {
        //            $(".purchase_block").hide(); 
        //         }
        //     });

            $('input[name="inventory_type"]').on("change", function() {
                if($(this).val() == 'with_inventory') {
                    // $("#with_inventory_block").show();
                    $("#without_inventory_block").hide();
                        
                } else {
                    // $("#with_inventory_block").hide();
                    $("#without_inventory_block").show();
                }
            });
            
        // });

        $('input[name="is_registered"]').on("change", function() {
            $("#company_status_change_update").html(`<span style="color: red;">UPDATE INVOICE SERIES</span>`);
        });

        $("#btn_opening_balance").on("click", function(){
            // $("#opening_balance_modal").modal("show");
            $("#opening_balance_modal").show();
            $(".modal-close").hide();
        });

    </script>
@endsection
