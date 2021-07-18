<?php

namespace App\Http\Controllers;

use App\CashDepositSetting;
use App\CashWithdraw;
use App\CashWithdrawSetting;
use App\GstPaymentSetting;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PaymentSetting;
use App\PurchaseOrderSetting;
use App\PurchaseSetting;
use App\ReceiptSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


use App\CashInHand;
use App\State;
use App\User;
use App\UserProfile;
use App\RoundOffSetting;
use App\SaleOrderSetting;
use App\SelectOption;
use App\PurchaseSelectOption;
use App\UserProfileGstStatus;
use App\NoteSetting;

use DB;
use Carbon\Carbon;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('active', ['except' => ['validate_invoice_starting_no', 'validate_purchase_order_starting_no', 'validate_sale_order_starting_no', 'validate_payment_starting_no', 'validate_receipt_starting_no', 'validate_contra_starting_no', 'validate_gst_payment_starting_no', 'validate_note_starting_no']]);
    }

    public function profile() {

        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
        $states = State::all();
        $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();

        // dd($user_profile);

        return view('user.profile', compact('user_profile', 'states', 'cash_in_hand'));
    }

    public function store_profile(Request $request){
        // return $request->all();

        // $profile = new UserProfile;

        $customMessages = [
            'book_beginning_from.required' => '"Book Beginning from" date cannot be empty',
            'financial_year_from.required' => '"Financial Year from" date cannot be empty',
            'financial_year_to.required' => '"Financial Year to" date cannot be empty'
        ];

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'phone' => 'required|integer|digits_between:10,12',
            'logo' => 'image|nullable',
            'gst' => 'alpha_num|nullable',
            'percent_on_sale_of_invoice' => 'integer|nullable',
            'composition_applicable_date' => 'date_format:d/m/Y|nullable',
            'authorised_name' => 'string|nullable',
            'authorised_signature' => 'image|nullable',
            'book_beginning_from' => 'required|date_format:d/m/Y',
            'financial_year_from' => 'required|date_format:d/m/Y',
            'financial_year_to' => 'required|date_format:d/m/Y',
        ], $customMessages);


        // if($request->gst_status_type == 'updated'){

        //     if(isset($request->gst_status_applicable_date)){
        //         if(Carbon::now() < Carbon::parse($request->gst_status_applicable_date)){
                    
        //             $user_profile_gst_status = new UserProfileGstStatus;
        //             $user_profile_gst_status->status = $request->is_registered;
        //             $user_profile_gst_status->applicable_date = $request->gst_status_applicable_date;
        //             $user_profile_gst_status->save();
    
        //             $request->is_registered = $request->gst_status_old;
        //         }
        //     }
        // }

        $request->state = $request->place_of_business;

        if (isset($request->checkbox_shipping_address) && $request->checkbox_shipping_address == "1") {
            $shipping_address = $request->address;
            $shipping_state = $request->state;
            $shipping_city = $request->city;
            $shipping_pincode = $request->pincode;
        } else {
            $shipping_address = $request->shipping_address;
            $shipping_state = $request->shipping_state;
            $shipping_city = $request->shipping_city;
            $shipping_pincode = $request->shipping_pincode;
        }

        if (isset($request->checkbox_billing_address) && $request->checkbox_billing_address == "1") {
            $billing_address = $request->address;
            $billing_state = $request->state;
            $billing_city = $request->city;
            $billing_pincode = $request->pincode;
        } else {
            $billing_address = $request->billing_address;
            $billing_state = $request->billing_state;
            $billing_city = $request->billing_city;
            $billing_pincode = $request->billing_pincode;
        }

        if( $request->is_registered == '4' ){
            $is_operator = 'yes';
        } else {
            $is_operator = 'no';
        }

        
        if ($request->has('book_beginning_from')) {
            $request->book_beginning_from = date('Y-m-d', strtotime(str_replace('/', '-', $request->book_beginning_from)));
        }

        if ($request->has('book_ending_on')) {
            $request->book_ending_on = date('Y-m-d', strtotime(str_replace('/', '-', $request->book_ending_on)));
        }   

        
        if ($request->has('financial_year_from')) {
            $request->financial_year_from = date('Y-m-d', strtotime(str_replace('/', '-', $request->financial_year_from)));
        }

        if ($request->has('financial_year_to')) {
            $request->financial_year_to = date('Y-m-d', strtotime(str_replace('/', '-', $request->financial_year_to)));
        }

        // if( $request->financial_year_from > $request->book_beginning_from || $request->financial_year_to < $request->book_beginning_from ){
        //     return redirect()->back()->with('failure', 'Booking Beginning date should be within financial year');
        // }

        // if ( $request->financial_year_from > $request->book_ending_on || $request->financial_year_to < $request->book_ending_on ) {
        //     return redirect()->back()->with('failure', 'Booking Ending date should be within financial year');
        // }

        // if( Carbon::parse($request->book_beginning_from) >= Carbon::parse($request->book_ending_on) ){
        //     return redirect()->back()->with('failure', 'Please provide valid date range for Books');
        // }

        if( Carbon::parse($request->financial_year_from) >= Carbon::parse($request->financial_year_to) ){
            return redirect()->back()->with('failure', 'Please provide valid date range for Financial Year');
        }

        $request->starting_no = 1;
        
        $request->width_of_numerical = 9;
        
        $start_no_applicable_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->financial_year_from)));

        $request->period = 'year';

        $suffix_applicable_date = isset( $request->suffix_applicable_date ) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->suffix_applicable_date))) : null;

        $prefix_applicable_date = isset( $request->prefix_applicable_date ) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->prefix_applicable_date))) : null;

        
        
        $composition_applicable_date = isset( $request->composition_applicable_date ) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->composition_applicable_date))) : null;

        if($composition_applicable_date){
            if (Carbon::parse($request->financial_year_from) > Carbon::parse($composition_applicable_date) ) {
                return redirect()->back()->with('failure', 'Please provide composition applicable date within the financial year dates');
            }

            if (Carbon::parse($request->financial_year_to) < Carbon::parse($composition_applicable_date)) {
                return redirect()->back()->with('failure', 'Please provide composition applicable date within the financial year dates');
            }
        }

        if($request->has('inventory_type')) {
            $inventory_type = $request->inventory_type;
            if($request->inventory_type == 'without_inventory'){
                $add_lump_sump = 'yes';
            } else if($request->inventory_type == 'with_inventory') {
                $add_lump_sump = 'no';
            }
        } else {
            $inventory_type = 'without_inventory';
            $add_lump_sump = 'yes';
        }

        if($request->has('inventory_type') && $request->inventory_type == 'with_inventory'){
            if( $request->has('with_inventory_type') ){
                $with_inventory_type = $request->with_inventory_type;
            } else {
                $with_inventory_type = 'fifo';
            }
        } else {
            $with_inventory_type = null;
        }


        if( $request->hasFile('logo') && $request->hasFile('authorised_signature') ) {
            $path = Storage::disk('public')->putFile('logos', $request->file('logo'));
            $authorised_signature = Storage::disk('public')->putFile('authorised_signature', $request->file('authorised_signature'));

            $user_profile = UserProfile::updateOrCreate(
                ['user_id' => Auth::user()->id],
                ['name' => $request->name, 'logo' => $path, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'place_of_business' => $request->place_of_business , 'communication_address' => $request->address, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'authorised_signature' => $authorised_signature, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bank_information' => $request->bank_information, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
            );

        } else if ( $request->hasFile('logo') ) {
            $path = Storage::disk('public')->putFile('logos', $request->file('logo'));

            $user_profile = UserProfile::updateOrCreate(
                ['user_id' => Auth::user()->id],
                ['name' => $request->name, 'logo' => $path, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'place_of_business' => $request->place_of_business, 'communication_address' => $request->address, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bank_information' => $request->bank_information, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
            );

        } else if ( $request->hasFile('authorised_signature') ) {
            $authorised_signature = Storage::disk('public')->putFile('authorised_signature', $request->file('authorised_signature'));

            $user_profile = UserProfile::updateOrCreate(
                ['user_id' => Auth::user()->id],
                ['name' => $request->name, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'place_of_business' => $request->place_of_business, 'communication_address' => $request->address, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'authorised_signature' => $authorised_signature, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bank_information' => $request->bank_information, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
            );

        } else {
            $user_profile = UserProfile::updateOrCreate(
                ['user_id' => Auth::user()->id],
                ['name' => $request->name, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'communication_address' => $request->address, 'place_of_business' => $request->place_of_business, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bank_information' => $request->bank_information, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
            );
        }

        if ($user_profile) {
            return redirect()->back()->with('success', 'Profile Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update profile');
        }
    }

    public function store_round_off_setting(Request $request)
    {

        // return $request->all();
        $setting = User::find(Auth::user()->id)->roundOffSetting;

        if (!$setting) {

            $setting = new RoundOffSetting;

            $setting->user_id = Auth::user()->id;
        }

        if( $request->transaction_type == "sale" ){

            $setting->sale_type = $request->sale_type;

            if($request->has('sale_round_off_item')){
                if( in_array("item_amount", $request->sale_round_off_item) ){
                    $setting->sale_item_amount = "yes";
                } else {
                    $setting->sale_item_amount = "no";
                }

                if( in_array("gst_amount", $request->sale_round_off_item) ){
                    $setting->sale_gst_amount = "yes";
                } else {
                    $setting->sale_gst_amount = "no";
                }

                if( in_array("total_amount", $request->sale_round_off_item) ){
                    $setting->sale_total_amount = "yes";
                } else {
                    $setting->sale_total_amount = "no";
                }
            } else {
                $setting->sale_item_amount = "no";
                $setting->sale_gst_amount = "no";
                $setting->sale_total_amount = "no";
            }

            $setting->sale_round_off_to = $request->sale_round_off_to;

            $setting->sale_upward_to = $request->sale_upward_to;
            $setting->sale_downward_to = $request->sale_downward_to;
        }

        else if( $request->transaction_type == "purchase" ){
            $setting->purchase_type = $request->purchase_type;

            if($request->has('purchase_round_off_item')) {
                if (in_array("item_amount", $request->purchase_round_off_item)) {
                    $setting->purchase_item_amount = "yes";
                } else {
                    $setting->purchase_item_amount = "no";
                }
    
                if (in_array("gst_amount", $request->purchase_round_off_item)) {
                    $setting->purchase_gst_amount = "yes";
                } else {
                    $setting->purchase_gst_amount = "no";
                }
    
                if (in_array("total_amount", $request->purchase_round_off_item)) {
                    $setting->purchase_total_amount = "yes";
                } else {
                    $setting->purchase_total_amount = "no";
                }
            } else {
                $setting->purchase_item_amount = "no";
                $setting->purchase_gst_amount = "no";
                $setting->purchase_total_amount = "no";
            }
            
            $setting->purchase_round_off_to = $request->purchase_round_off_to;

            $setting->purchase_upward_to = $request->purchase_upward_to;
            $setting->purchase_downward_to = $request->purchase_downward_to;
        }

        // $setting->round_off = $request->want_round_off;
        // $setting->round_off_to = $request->round_off_to;

        // if ($request->has('price_to_round')) {

        //     $price_to_round = $request->price_to_round;

        //     $sale_total = array_search('sale_total', $price_to_round);
        //     $sale_gst_total = array_search('sale_gst_total', $price_to_round);
        //     $sale_cess_total = array_search('sale_cess_total', $price_to_round);

        //     $item_total = array_search('item_total', $price_to_round);

        //     $purchase_total = array_search('purchase_total', $price_to_round);
        //     $purchase_gst_total = array_search('purchase_gst_total', $price_to_round);
        //     $purchase_cess_total = array_search('purchase_cess_total', $price_to_round);

        //     if (!is_bool($sale_total)) {
        //         $sale_total += 1;
        //     }

        //     if (!is_bool($sale_gst_total)) {
        //         $sale_gst_total += 1;
        //     }

        //     if (!is_bool($sale_cess_total)) {
        //         $sale_cess_total += 1;
        //     }


        //     if (!is_bool($item_total)) {
        //         $item_total += 1;
        //     }


        //     if (!is_bool($purchase_total)) {
        //         $purchase_total += 1;
        //     }

        //     if (!is_bool($purchase_gst_total)) {
        //         $purchase_gst_total += 1;
        //     }

        //     if (!is_bool($purchase_cess_total)) {
        //         $purchase_cess_total += 1;
        //     }
            
    
        //     if( $sale_total ) {
        //         $setting->sale_total = "yes";
        //     } else {
        //         $setting->sale_total = "no";
        //     }
            
        //     if( $sale_gst_total ) {
        //         $setting->sale_gst_total = "yes";
        //     } else {
        //         $setting->sale_gst_total = "no";
        //     }
    
        //     if( $sale_cess_total ) {
        //         $setting->sale_cess_total = "yes";
        //     } else {
        //         $setting->sale_cess_total = "no";
        //     }
    
        //     if( $item_total ) {
        //         $setting->item_total = "yes";
        //     } else {
        //         $setting->item_total = "no";
        //     }
    
        //     if( $purchase_total ) {
        //         $setting->purchase_total = "yes";
        //     } else {
        //         $setting->purchase_total = "no";
        //     }
    
        //     if( $purchase_gst_total ) {
        //         $setting->purchase_gst_total = "yes";
        //     } else {
        //         $setting->purchase_gst_total = "no";
        //     }
    
        //     if( $purchase_cess_total ) {
        //         $setting->purchase_cess_total = "yes";
        //     } else {
        //         $setting->purchase_cess_total = "no";
        //     }
        // }

        if($setting->save()) {
            return redirect()->back()->with('success', 'Settings saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save settings');
        }
    }

    public function invoice_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.invoice_setting', compact('user_profile'));
    }

    public function save_invoice_setting(Request $request)
    {

//             'width_of_numerical' => 'integer|nullable',
//             'starting_no' => 'integer|nullable',
//             'start_no_applicable_date' => 'date_format:d/m/Y|nullable',
//             'suffix_applicable_date' => 'date_format:d/m/Y|nullable',
//             'prefix_applicable_date' => 'date_format:d/m/Y|nullable',
//             'name_of_suffix' => 'string|nullable',
//             'name_of_prefix' => 'string|nullable',

        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $suffix_applicable_date = isset($request->suffix_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->suffix_applicable_date))) : null;

        $prefix_applicable_date = isset($request->prefix_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->prefix_applicable_date))) : null;



        $composition_applicable_date = isset($request->composition_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->composition_applicable_date))) : null;


        if ($composition_applicable_date) {
            if (Carbon::parse($request->financial_year_from) > Carbon::parse($composition_applicable_date)) {
                return redirect()->back()->with('failure', 'Please provide valid composition applicable date');
            }

            if (Carbon::parse($request->financial_year_to) < Carbon::parse($composition_applicable_date)) {
                return redirect()->back()->with('failure', 'Please provide valid composition applicable date');
            }
        }

        $user_profile = UserProfile::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'invoice_template' => $request->invoice_template, 'show_terms' => $request->show_terms, 'show_bank_info' => $request->show_bank_info]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function purchase_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.purchase_setting', compact('user_profile'));
    }

    public function save_purchase_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = PurchaseSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function purchase_order_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.purchase_order_setting', compact('user_profile'));
    }

    public function save_purchase_order_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = PurchaseOrderSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function sale_order_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.sale_order_setting', compact('user_profile'));
    }

    public function save_sale_order_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = SaleOrderSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function payment_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.payment_setting', compact('user_profile'));
    }

    public function save_payment_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = PaymentSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function receipt_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.receipt_setting', compact('user_profile'));
    }

    public function save_receipt_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = ReceiptSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function cash_withdraw_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.cash_withdraw_setting', compact('user_profile'));
    }

    public function save_cash_withdraw_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = CashWithdrawSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function cash_deposit_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.cash_deposit_setting', compact('user_profile'));
    }

    public function save_cash_deposit_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = CashDepositSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function gst_payment_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.gst_payment_setting', compact('user_profile'));
    }

    public function save_gst_payment_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = GstPaymentSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function note_setting()
    {
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        return view('user.note_setting', compact('user_profile'));
    }

    public function save_note_setting(Request $request)
    {
        $start_no_applicable_date = isset($request->start_no_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->start_no_applicable_date))) : null;

        $user_profile = NoteSetting::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function select_option_setting()
    {
        return view('user.select_option_setting');
    }

    public function save_select_option_setting(Request $request)
    {

        $show_buyer_name = $request->show_buyer_name ?? 0;
        $show_order = $request->show_order ?? 0;
        $show_reference_name = $request->show_reference_name ?? 0;
        $show_gst_classification = $request->show_gst_classification ?? 0;
        $show_cess_charge = $request->show_cess_charge ?? 0;
        $show_tcs = $request->show_tcs ?? 0;
        $show_consign_info = $request->show_consign_info ?? 0;
        $show_import_export_info = $request->show_import_export_info ?? 0;

        $user_profile = SelectOption::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['show_buyer_name' => $show_buyer_name, 'show_order' => $show_order, 'show_reference_name' => $show_reference_name, 'show_gst_classification' => $show_gst_classification, 'show_cess_charge' => $show_cess_charge, 'show_tcs' => $show_tcs, 'show_consign_info' => $show_consign_info, 'show_import_export_info' => $show_import_export_info]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function purchase_select_option_setting()
    {
        return view('user.purchase_select_option_setting');
    }

    public function save_purchase_select_option_setting(Request $request)
    {
        $show_buyer_name = $request->show_buyer_name ?? 0;
        $show_order = $request->show_order ?? 0;
        $show_reference_name = $request->show_reference_name ?? 0;
        $show_gst_classification = $request->show_gst_classification ?? 0;
        $show_cess_charge = $request->show_cess_charge ?? 0;
        $show_tcs = $request->show_tcs ?? 0;
        $show_consign_info = $request->show_consign_info ?? 0;
        $show_import_export_info = $request->show_import_export_info ?? 0;

        $user_profile = PurchaseSelectOption::updateOrCreate(
            ['user_id' => Auth::user()->id],
            ['show_buyer_name' => $show_buyer_name, 'show_order' => $show_order, 'show_reference_name' => $show_reference_name, 'show_gst_classification' => $show_gst_classification, 'show_cess_charge' => $show_cess_charge, 'show_tcs' => $show_tcs, 'show_consign_info' => $show_consign_info, 'show_import_export_info' => $show_import_export_info]
        );

        if ($user_profile) {
            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update settings');
        }
    }

    public function remove_logo(Request $request)
    {
        $profile = UserProfile::where('user_id', auth()->user()->id)->first();

        $profile->logo = null;
        if($profile->save()){
            return response()->json(['success' => true, 'message' => 'Logo removed successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to remove logo']);
        }
    }

    public function remove_signature(Request $request)
    {
        $profile = UserProfile::where('user_id', auth()->user()->id)->first();

        $profile->authorised_signature = null;
        if($profile->save()){
            return response()->json(['success' => true, 'message' => 'Signature removed successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to remove logo']);
        }
    }

    public function validate_invoice_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }

        $rows = DB::table('invoices')
            ->where('invoice_no', $request->starting_no)
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('invoice_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        if( $rows->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    ///---

    public function validate_purchase_order_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }

        $rows = DB::table('purchase_order')
            ->where('token', $request->starting_no)
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_sale_order_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }        

        $rows = DB::table('sale_order')
            ->where('token', $request->starting_no)
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_payment_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        } 

        $rows1 = DB::table('party_pending_payment_account')
            ->where('voucher_no', $request->starting_no)
            ->where('type', 'purchase')
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('payment_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows2 = DB::table('purchase_remaining_amounts')
            ->where('voucher_no', $request->starting_no)
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('payment_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows3 = DB::table('gst_set_off')
            ->where('voucher_no', $request->starting_no)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows4 = DB::table('cash_ledger_balance')
            ->where('voucher_no', $request->starting_no)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows1->count() > 0 || $rows2->count() > 0 || $rows3->count() > 0 || $rows4->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_receipt_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }

        $rows1 = DB::table('party_pending_payment_account')
            ->where('voucher_no', $request->starting_no)
            ->where('type', 'sale')
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('payment_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows2 = DB::table('sale_remaining_amounts')
            ->where('voucher_no', $request->starting_no)
            ->whereIn('party_id', $user->getPartyIdsAttribute())
            ->whereBetween('payment_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows1->count() > 0 || $rows2->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_contra_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }

        $rows1 = DB::table('cash_deposit')
            ->where('contra', $request->starting_no)
            ->whereIn('user_id', $user->id)
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows2 = DB::table('cash_withdraw')
            ->where('contra', $request->starting_no)
            ->whereIn('user_id', $user->id)
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows1->count() > 0 || $rows2->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_gst_payment_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }

        $rows1 = DB::table('gst_set_off')
            ->where('voucher_no', $request->starting_no)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows2 = DB::table('cash_ledger_balance')
            ->where('voucher_no', $request->starting_no)
            ->where('user_id', $user->id)
            ->whereBetween('date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows1->count() > 0 || $rows2->count() ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_note_starting_no(Request $request)
    {
        $isValidated = true;

        $user = User::find($request->user_id);

        if(!$user) {
            return response()->json(
                array(
                    'success' => false,
                    'errors' => 'Not a valid user, Please select valid user and then try to update the settings. If problem continues, logout and then login again'
                ), 400);
        }

        $rows1 = DB::table('credit_notes')
            ->where('note_no', $request->starting_no)
            ->whereIn('item_id', $user->getItemIdsAttribute())
            ->whereBetween('note_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        $rows2 = DB::table('debit_notes')
            ->where('note_no', $request->starting_no)
            ->whereIn('item_id', $user->getItemIdsAttribute())
            ->whereBetween('note_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])
            ->get();

        
        if( $rows1->count() > 0 || $rows2->count() > 0 ) {
            $isValidated = false;
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide different starting no for the series, If you want to update'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

}
