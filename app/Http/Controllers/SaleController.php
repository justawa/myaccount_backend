<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Mail\SendInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use DB;
use Auth;
use Excel;

use App\Bank;
use App\BillNote;
use App\CreditNote;
use App\DebitNote;
use App\Group;
use App\Insurance;
use App\Invoice;
use App\Item;
use App\Invoice_Item;
use App\Ledger;
use App\Party;
use App\PartyPendingPaymentAccount;
use App\Purchase;
use App\sale;
use App\sale_Item;
use App\SaleLog;
use App\SaleRemainingAmount;
use App\SaleOrder;
use App\State;
use App\Transporter;
use App\User;
use App\UserProfile;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (isset($request->from_date) && isset($request->to_date)) {
            $invoices = User::find(Auth::user()->id)->invoices()->whereBetween('invoice_date', [date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date))), date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)))])->orderBy('id', 'desc')->get();
        } else if (isset($request->query_by) && isset($request->q)) {
            if ($request->query_by == 'invoice_no') {
                $invoices = User::find(Auth::user()->id)->invoices()->where('invoice_no', $request->q)->orderBy('id', 'desc')->get();
            } else if ($request->query_by == 'name') {
                $party = Party::where('user_id', Auth::user()->id)->where('name', $request->q)->first();

                $invoices = Invoice::where('party_id', $party->id)->orderBy('id', 'desc')->get();
            }
        } else {
            $invoices = User::findOrFail(Auth::user()->id)->invoices()->orderBy('id', 'desc')->get();
        }


        foreach ($invoices as $invoice) {
            $party = Party::find($invoice->party_id);

            $invoice->party_name = $party->name;
            $invoice->party_city = $party->city;
        }


        /** ---------------------------------------------------------------------------------------------- */

        // $invoices = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->get();

        // $invoices = User::findOrFail(Auth::user()->id)->invoices()->get();

        // foreach($invoices as $invoice){
        //     $party = Party::find($invoice->party_id);

        //     $invoice->party_name = $party->name;
        // }

        return view('sale.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = Group::all();
        $insurances = Insurance::where('user_id', Auth::user()->id)->get();
        $items = Item::where('user_id', Auth::user()->id)->get();
        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'debitor')->get();
        $transporters = Transporter::where('user_id', Auth::user()->id)->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $invoice = User::findOrFail(Auth::user()->id)->invoices()->orderBy('id', 'desc')->first();

        $invoice_no = null;
        $invoice_prefix = null;
        $invoice_suffix = null;
        $myerrors = array();

        if (isset(auth()->user()->profile) && auth()->user()->profile->bill_no_type == 'auto') {

            if (isset($invoice->invoice_no)) {
                switch ($user_profile->width_of_numerical) {
                    case 0:
                        $myerrors['invoice_no'][] = 'Invalid Max-length provided. Please update your invoice settings';
                        break;
                    case 1:
                        if ($invoice->invoice_no > 9) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 2:
                        if ($invoice->invoice_no > 99) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 3:
                        if ($invoice->invoice_no > 999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 4:
                        if ($invoice->invoice_no > 9999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 5:
                        if ($invoice->invoice_no > 99999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 6:
                        if ($invoice->invoice_no > 999999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 7:
                        if ($invoice->invoice_no > 9999999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 8:
                        if ($invoice->invoice_no > 99999999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                    case 9:
                        if ($invoice->invoice_no > 999999999) {
                            $myerrors['invoice_no'][] = 'Max-length exceeded for invoice no. Please update your invoice settings';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if ($user_profile->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse($user_profile->start_no_applicable_date)->addWeek();
            }

            if ($user_profile->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse($user_profile->start_no_applicable_date)->addMonth();
            }

            if ($user_profile->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse($user_profile->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['invoice_no'][] = 'Applicable date expired for your invoice series. Please update your invoice settings';
            }

            if ($invoice) {
                if (isset($invoice->invoice_no_type) && $invoice->invoice_no_type == 'auto') {
                    $invoice_no = ($invoice->invoice_no == '' || $invoice->invoice_no == null) ? 0 : $invoice->invoice_no;
                } else {
                    $invoice_no = isset($user_profile->starting_no) ? $user_profile->starting_no - 1 : 0;
                }
            } else {
                $invoice_no = isset($user_profile->starting_no) ? $user_profile->starting_no - 1 : 0;
            }


            $invoice_suffix = null;
            $invoice_prefix = null;
            if (\Carbon\Carbon::now() >= \Carbon\Carbon::parse($user_profile->prefix_applicable_date)) {
                if ($user_profile->name_of_prefix != null) {
                    $invoice_prefix = $user_profile->name_of_prefix;
                } else {
                    $invoice_prefix = null;
                }
            }

            if (\Carbon\Carbon::now() >= \Carbon\Carbon::parse($user_profile->suffix_applicable_date)) {
                if ($user_profile->name_of_suffix != null) {
                    $invoice_suffix = $user_profile->name_of_suffix;
                } else {
                    $invoice_suffix = null;
                }
            }
        }

        $myerrors = collect($myerrors);

        // dd($myerrors);

        return view('sale.create', compact('groups', 'insurances', 'items', 'parties', 'transporters', 'banks', 'user_profile', 'invoice_no', 'invoice_prefix', 'invoice_suffix'))->with('myerrors', $myerrors);
        // ->withErrors($errors)
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();

        $this->validate(
            $request,
            [
                'invoice_no' => 'required|uniqueInvoice:party',
                'party' => 'required',
                'invoice_date' => 'required|date_format:d/m/Y',
                'due_date' => 'required|date_format:d/m/Y',
                'item_discount' => 'array',
                'item_discount.*' => 'numeric|nullable',
                'quantity' => 'array',
                'quantity.*' => 'numeric|nullable',
                'price' => 'array',
                'price.*' => 'numeric|nullable',
                'free_quantity' => 'array',
                'free_quantity.*' => 'numeric|nullable',
                'amount' => 'array',
                'amount.*' => 'numeric|nullable',
                'calculated_gst' => 'array',
                'calculated_gst.*' => 'numeric|nullable',
                'cashed_amount' => 'numeric|nullable',
                'banked_amount' => 'numeric|nullable',
                'posed_amount' => 'numeric|nullable',
                'total_discount' => 'numeric|nullable',
                'sale_order_no' => 'alpha_num|nullable',
                'reference_name' => 'string|nullable',
                'shipping_bill_no' => 'alpha_num|nullable',
                'date_of_shipping' => 'date_format:d/m/Y|nullable',
                'code_of_shipping_port' => 'alpha_num|nullable',
                'conversion_rate' => 'numeric|nullable',
                'currency_symbol' => 'string|nullable',
                'consignee_info' => 'string|nullable',
                'consignor_info' => 'string|nullable',
                'tcs' => 'numeric|nullable',
                'item_total_amount' => 'required'
            ],
            [
                'invoice_no.required' => 'Invoice No is required',
                'invoice_no.uniqueInvoice' => 'Please provide unique Invoice no for current financial year',
                'item_discount.*.numeric' => 'Discount must be a number',
                'quantity.*.numeric' => 'Quantity must be a number',
                'price.*.numeric' => 'Price/Rate must be a number',
                'free_quantity.*.numeric' => 'Free Qty must be a number',
                'amount.*.numeric' => 'Amount must be a number',
                'calculated_gst.*.numeric' => 'Some error occured in data. Please try again'
            ]
        );

        $invoice = new Invoice;
        $insertable_items = array();
        $amount = 0;

        $invoice->party_id = $request->party;

        $party = Party::findOrFail($request->party);


        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->bill_no_type)) {
            $invoice->invoice_no_type = auth()->user()->profile->bill_no_type;
        } else {
            $invoice->invoice_no_type = 'manual';
        }

        $invoice->billing_address = isset($request->billing_address) ? $request->billing_address : $party->billing_address . ', ' .
            $party->billing_city . ', ' .
            $party->billing_state . ', ' .
            $party->billing_pincode;

        if ($request->has('buyer_name')) {
            $invoice->buyer_name = $request->buyer_name;
        }

        $i_date = $request->invoice_date;
        $idate = str_replace('/', '-', $i_date);
        $invoice_date = date('Y-m-d', strtotime($idate));


        if (auth()->user()->profile->financial_year_from > $invoice_date) {
            return redirect()->back()->with('failure', 'Please select valid invoice date for current financial year', $request->invoice_date);
        }

        if (auth()->user()->profile->financial_year_to < $invoice_date) {
            return redirect()->back()->with('failure', 'Please select valid invoice date for current financial year');
        }

        $d_date = $request->due_date;
        $ddate = str_replace('/', '-', $d_date);
        $due_date = date('Y-m-d', strtotime($ddate));


        if (auth()->user()->profile->financial_year_from > $due_date) {
            return redirect()->back()->with('failure', 'Please select valid due date for current financial year');
        }

        if (auth()->user()->profile->financial_year_to < $due_date) {
            return redirect()->back()->with('failure', 'Please select valid due date for current financial year');
        }

        $invoice->invoice_date = $invoice_date;

        $invoice->due_date = $due_date;

        $invoice->reference_name = $request->reference_name;

        $invoice->order_no = $request->sale_order_no ?? null;

        if ($request->tax_inclusive == 'inclusive_of_tax') {
            $invoice->amount_type = 'inclusive';
        } else if ($request->tax_inclusive == 'exclusive_of_tax') {
            $invoice->amount_type = 'exclusive';
        }

        if (Session::has("transporter_details")) {
            $transporter_id = session("transporter_details.transporter_id");
            $vehicle_type = session("transporter_details.vehicle_type");
            $vehicle_number = session("transporter_details.vehicle_number");
            $delivery_date = session("transporter_details.delivery_date");

            $dtime = strtotime($delivery_date);

            $delivery_date = date('Y-m-d', $dtime);

            $invoice->transporter_id = $transporter_id;
            $invoice->vehicle_type = $vehicle_type;
            $invoice->vehicle_number = $vehicle_number;
            $invoice->delivery_date = $delivery_date;
        }

        // $invoice->igst = $request->overall_igst;
        // $invoice->cgst = $request->overall_cgst;
        // $invoice->sgst = $request->overall_sgst;
        // $invoice->gst = $request->overall_gst;

        $invoice->labour_charge = $request->labour_charges;
        $invoice->freight_charge = $request->freight_charges;
        $invoice->transport_charge = $request->transport_charges;
        $invoice->insurance_charge = $request->insurance_charges;
        $invoice->gst_charged_on_additional_charge = $request->gst_charged;

        $invoice->insurance_id = $request->insurance_company;

        $invoice->item_total_amount = $request->item_total_amount;
        $invoice->gst = $request->total_gst_amounted;
        $invoice->item_total_rcm_gst = $request->item_total_rcm_gst;
        $invoice->cess = $request->total_cess_amounted;
        $invoice->amount_paid = $request->amount_paid;
        $invoice->amount_remaining = $request->amount_remaining;
        $invoice->total_discount = $request->total_discount;
        $invoice->amount_before_round_off = $request->total_amount;
        $invoice->round_off_operation = $request->round_off_operation;
        $invoice->round_offed = $request->round_offed;
        $invoice->total_amount = $request->amount_to_pay;
        
        $invoice->remark = $request->overall_remark;

        $invoice->tcs = $request->tcs;

        //----------------------------------------------------------------------------------

        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        // $party = Party::where('id', $request->party)->first();

        if ($party->reverse_charge == "yes") {
            $invoice->gst_classification = 'rcm';
        }

        if ($user_profile->place_of_business == $party->business_place) {
            $invoice->cgst = $request->total_gst_amounted / 2;
            $invoice->sgst = $request->total_gst_amounted / 2;
        }
        // else if( $user_profile->place_of_business != $party->business_place && ( $party->business_place == 4 || $party->business_place == 7 || $party->business_place == 25 || $party->business_place == 26 || $party->business_place == 31 || $party->business_place == 34 || $party->business_place == 35 ) ) {
        //     $invoice->ugst = $request->item_total_gst;
        // }
        else {
            $invoice->igst = $request->total_gst_amounted;
        }

        //-----------------------------------------------------------------------------------

        // $invoice->type_of_payment = $request->type_of_payment;

        // if($request->type_of_payment == 'bank'){
        //     $invoice->bank_id = $request->bank;
        // }

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('cash_discount', $type_of_payment);

            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            // if( $cash && $bank && $pos ){
            // 	$invoice->type_of_payment = 'combined';

            // 	$invoice->cash_payment = $request->cashed_amount;
            // 	$invoice->bank_payment = $request->banked_amount;
            // 	$invoice->pos_payment = $request->posed_amount;

            // 	$invoice->bank_id = $request->bank;
            // 	$invoice->bank_cheque = $request->bank_cheque;
            //     $invoice->pos_bank_id = $request->pos_bank;

            //     $invoice->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            //     $invoice->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // } else if ( $pos && $bank ) {
            // 	$invoice->type_of_payment = 'pos+bank';

            // 	$invoice->bank_payment = $request->banked_amount;
            // 	$invoice->pos_payment = $request->posed_amount;

            // 	$invoice->bank_id = $request->bank;
            // 	$invoice->bank_cheque = $request->bank_cheque;
            //     $invoice->pos_bank_id = $request->pos_bank;

            //     $invoice->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            //     $invoice->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // } else if ( $pos && $cash ) {
            // 	$invoice->type_of_payment = 'pos+cash';

            // 	$invoice->cash_payment = $request->cashed_amount;
            // 	$invoice->pos_payment = $request->posed_amount;

            //     $invoice->pos_bank_id = $request->pos_bank;

            //     $invoice->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // } else if ( $bank && $cash ) {
            // 	$invoice->type_of_payment = 'bank+cash';

            // 	$invoice->cash_payment = $request->cashed_amount;
            // 	$invoice->bank_payment = $request->banked_amount;

            // 	$invoice->bank_id = $request->bank;
            //     $invoice->bank_cheque = $request->bank_cheque;

            //     $invoice->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            // } else if ( $bank ) {
            // 	$invoice->type_of_payment = 'bank';

            // 	$invoice->bank_payment = $request->banked_amount;

            // 	$invoice->bank_id = $request->bank;
            //     $invoice->bank_cheque = $request->bank_cheque;

            //     $invoice->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            // } else if ( $cash ) {
            // 	$invoice->type_of_payment = 'cash';

            // 	$invoice->cash_payment = $request->cashed_amount;
            // } else if ( $pos ) {
            // 	$invoice->type_of_payment = 'pos';

            // 	$invoice->pos_payment = $request->posed_amount;

            //     $invoice->pos_bank_id = $request->pos_bank;

            //     $invoice->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // }

            if ($cash && $bank && $pos && $discount) {

                $invoice->type_of_payment = 'combined';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->pos_bank_id = $request->pos_bank;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {

                $invoice->type_of_payment = 'cash+bank+pos';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank && $discount) {
                $invoice->type_of_payment = 'cash+bank+discount';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            } else if ($cash && $discount && $pos) {
                $invoice->type_of_payment = 'cash+pos+discount';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->pos_bank_id = $request->pos_bank;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            } else if ($discount && $bank && $pos) {
                $invoice->type_of_payment = 'bank+pos+discount';

                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->pos_bank_id = $request->pos_bank;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            } else if ($cash && $bank) {

                $invoice->type_of_payment = 'bank+cash';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $invoice->type_of_payment = 'pos+cash';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->pos_payment = $request->posed_amount;

                $invoice->pos_bank_id = $request->pos_bank;
            } else if ($cash && $discount) {

                $invoice->type_of_payment = 'cash+discount';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {
                $invoice->type_of_payment = 'pos+bank';

                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;
                $invoice->pos_bank_id = $request->pos_bank;
            } else if ($bank && $discount) {

                $invoice->type_of_payment = 'bank+discount';

                $invoice->bank_payment = $request->banked_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $invoice->type_of_payment = 'pos+discount';

                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $invoice->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $invoice->type_of_payment = 'cash';

                $invoice->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $invoice->type_of_payment = 'bank';

                $invoice->bank_payment = $request->banked_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $invoice->type_of_payment = 'pos';

                $invoice->pos_payment = $request->posed_amount;

                $invoice->pos_bank_id = $request->pos_bank;
            } else if ($discount) {
                $invoice->type_of_payment = 'discount';

                $invoice->discount_payment = $request->discount_amount;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;
            }
        } else {
            $invoice->type_of_payment = 'no_payment';
        }

        $invoice->shipping_bill_no = $request->shipping_bill_no;
        $invoice->date_of_shipping = $request->filled('date_of_shipping') ? date('Y-m-d', strtotime(str_replace('/', '-', $request->date_of_shipping))) : null;
        $invoice->code_of_shipping_port = $request->code_of_shipping_port;
        $invoice->conversion_rate = $request->conversion_rate;
        $invoice->currency_symbol = $request->currency_symbol;
        $invoice->export_type = $request->export_type;

        $invoice->consignee_info = $request->consignee_info;
        $invoice->consignor_info = $request->consignor_info;

        if ($request->invoice_no != null) {
            $invoice->invoice_no = $request->invoice_no;
        }

        if ($request->has('invoice_prefix')) {
            $invoice->invoice_prefix = $request->invoice_prefix;
        }

        if ($request->has('invoice_suffix')) {
            $invoice->invoice_suffix = $request->invoice_suffix;
        }

        if ($request->has('gst_classification')) {
            for ($i = 0; $i < count($request->gst_classification); $i++) {
                if ($request->gst_classification[$i] == 'rcm') {
                    $invoice->gst_classification = 'rcm';
                }
            }
        } else {
            for ($i = 0; $i < count($request->item); $i++) {
                $item = Item::find($request->item[$i]);

                if (isset($item) && $item->item_under_rcm == "yes") {
                    $invoice->gst_classification = 'rcm';
                }
            }
        }

        if ($request->has("add_lump_sump") && $request->add_lump_sump == "yes") {
            $invoice->is_add_lump_sump = 1;
        } else {
            $invoice->is_add_lump_sump = 0;
        }

        $invoice->save();

        $sale_remaining_amount = new SaleRemainingAmount;

        $sale_remaining_amount->party_id = $request->party;
        $sale_remaining_amount->invoice_id = $invoice->id;
        $sale_remaining_amount->total_amount = $request->total_amount;
        $sale_remaining_amount->amount_paid = $request->amount_paid;
        $sale_remaining_amount->amount_remaining = $request->amount_remaining;
        $sale_remaining_amount->payment_date = $invoice_date;
        // $sale_remaining_amount->type_of_payment = $request->type_of_payment;
        // if($request->type_of_payment == 'bank'){
        //     $sale_remaining_amount->bank_id;
        // }


        if ($request->has('type_of_payment')) {
            // if( $cash && $bank && $pos ) {
            // 	$sale_remaining_amount->type_of_payment = 'combined';

            // 	$sale_remaining_amount->cash_payment = $request->cashed_amount;
            // 	$sale_remaining_amount->bank_payment = $request->banked_amount;
            // 	$sale_remaining_amount->pos_payment = $request->posed_amount;

            // 	$sale_remaining_amount->bank_id = $request->bank;
            // 	$sale_remaining_amount->pos_bank_id = $request->pos_bank;
            // } else if ( $pos && $bank ) {
            // 	$sale_remaining_amount->type_of_payment = 'pos+bank';

            // 	$sale_remaining_amount->bank_payment = $request->banked_amount;
            // 	$sale_remaining_amount->pos_payment = $request->posed_amount;

            // 	$sale_remaining_amount->bank_id = $request->bank;
            // 	$sale_remaining_amount->pos_bank_id = $request->pos_bank;
            // } else if ( $pos && $cash ) {
            // 	$sale_remaining_amount->type_of_payment = 'pos+cash';

            // 	$sale_remaining_amount->cash_payment = $request->cashed_amount;
            // 	$sale_remaining_amount->pos_payment = $request->posed_amount;

            // 	$sale_remaining_amount->pos_bank_id = $request->pos_bank;
            // } else if ( $bank && $cash ) {
            // 	$sale_remaining_amount->type_of_payment = 'bank+cash';

            // 	$sale_remaining_amount->cash_payment = $request->cashed_amount;
            // 	$sale_remaining_amount->bank_payment = $request->banked_amount;

            // 	$sale_remaining_amount->bank_id = $request->bank;
            // } else if ( $bank ) {
            // 	$sale_remaining_amount->type_of_payment = 'bank';

            // 	$sale_remaining_amount->bank_payment = $request->banked_amount;

            // 	$sale_remaining_amount->bank_id = $request->bank;
            // } else if ( $cash ) {
            // 	$sale_remaining_amount->type_of_payment = 'cash';

            // 	$sale_remaining_amount->cash_payment = $request->cashed_amount;
            // } else if ( $pos ) {
            // 	$sale_remaining_amount->type_of_payment = 'pos';

            // 	$sale_remaining_amount->pos_payment = $request->posed_amount;

            // 	$sale_remaining_amount->pos_bank_id = $request->pos_bank;
            // }

            if ($cash && $bank && $pos && $discount) {

                $sale_remaining_amount->type_of_payment = 'combined';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {

                $sale_remaining_amount->type_of_payment = 'cash+bank+pos';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank && $discount) {

                $sale_remaining_amount->type_of_payment = 'cash+bank+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount && $pos) {

                $sale_remaining_amount->type_of_payment = 'cash+pos+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount && $bank && $pos) {

                $sale_remaining_amount->type_of_payment = 'bank+pos+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank) {

                $sale_remaining_amount->type_of_payment = 'bank+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {

                $sale_remaining_amount->type_of_payment = 'pos+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $discount) {

                $sale_remaining_amount->type_of_payment = 'cash+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {

                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $discount) {

                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {

                $sale_remaining_amount->type_of_payment = 'pos+discount';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash) {

                $sale_remaining_amount->type_of_payment = 'cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $sale_remaining_amount->type_of_payment = 'bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {

                $sale_remaining_amount->type_of_payment = 'pos';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount) {

                $sale_remaining_amount->type_of_payment = 'discount';
                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            }
        } else {
            $sale_remaining_amount->type_of_payment = 'no_payment';
        }

        $sale_remaining_amount->is_original_payment = 1;

        $sale_remaining_amount->save();

        $hasLumpSump = false;

        $items = $request->item;
        $quantities = $request->quantity;

        if ($request->has('add_lump_sump') && $request->add_lump_sump == 'yes') {
            $prices = $request->amount;
            $amounts = $request->amount;
            $hasLumpSump = true;
        } else if ($request->has('price')) {
            $prices = $request->price;
            $amounts = $request->amount;
        }

        $discount_types = $request->item_discount_type;
        $discounts = $request->item_discount;
        $remarks = $request->item_remark;
        $calculated_gst = $request->calculated_gst;
        $gst_tax_types = $request->gst_tax_type;
        $calculated_rcm_gst = $request->calculated_gst_rcm;
        $measuring_unit = $request->measuring_unit;
        $free_qty = $request->free_quantity;
        $gst_classification = $request->gst_classification;
        $cesses = $request->cess_amount;

        for ($i = 0; $i < count($items); $i++) {
            $insertable_items[$i]['id'] = $items[$i];
            $insertable_items[$i]['qty'] = $quantities[$i];
            $insertable_items[$i]['price'] = $prices[$i];
            $insertable_items[$i]['amount'] = $amounts[$i];
            $insertable_items[$i]['discount_type'] = $discount_types[$i] ?? null;
            $insertable_items[$i]['discount'] = $discounts[$i];
            $insertable_items[$i]['remark'] = isset($remarks[$i]) ? $remarks[$i] : null;
            $insertable_items[$i]['gst'] = $calculated_gst[$i] ?? 0;
            $insertable_items[$i]['gst_tax_type'] = $gst_tax_types[$i];
            $insertable_items[$i]['rcm_gst'] = $calculated_rcm_gst[$i] ?? 0;
            $insertable_items[$i]['measuring_unit'] = $measuring_unit[$i];
            $insertable_items[$i]['free_qty'] = isset($free_qty[$i]) ? $free_qty[$i] : 0;
            $insertable_items[$i]['gst_classification'] = isset($gst_classification[$i]) ? $gst_classification[$i] : null;
            $insertable_items[$i]['cess'] = isset($cesses[$i]) ? $cesses[$i] : null;
        }

        foreach ($insertable_items as $item) {
            $invoice_item = new Invoice_Item;

            $sold_item = Item::find($item['id']);

            $invoice_item->invoice_id = $invoice->id;

            // checking if user is composition then use percent_on_sale_of_invoice instead of item gst
            $gst_perent_corrected = (auth()->user()->profile->registered == 3) ? 0 : $sold_item->gst;

            $invoice_item->item_id = $item['id'];
            $invoice_item->sold_on = date('Y-m-d', strtotime(str_replace('/', '-', $request->invoice_date)));
            $invoice_item->gst = $item['gst'];
            $invoice_item->rcm_gst = $item['rcm_gst'];
            $invoice_item->gst_rate = $gst_perent_corrected;
            $invoice_item->item_price = $item['price'];
            $invoice_item->item_total = $item['amount'];
            $invoice_item->item_tax_type = $item['gst_tax_type'];
            $invoice_item->discount_type = $item['discount_type'];
            $invoice_item->discount = $item['discount'];
            $invoice_item->party_id = $request->party;
            $invoice_item->gst_classification = $item['gst_classification'];
            $invoice_item->remark = $item['remark'];
            $invoice_item->cess = $item['cess'];
            $invoice_item->has_lump_sump = ($hasLumpSump) ? 1 : 0;

            // if(Session::has("item_cess" . $item['id'])) {
            //     $invoice_item->cess = session("item_cess.".$item['id']);
            // }

            if (isset($item['measuring_unit'])) {

                if ($item['free_qty'] > 0) {
                    $qty = $item['qty'] + $item['free_qty'];
                } else {
                    $qty = $item['qty'];
                }

                if ($item['measuring_unit'] == $sold_item->measuring_unit) {
                    $sold_item->qty = $sold_item->qty - $qty;

                    $invoice_item->item_qty = $item['qty'];
                    $invoice_item->item_alt_qty = 0;
                    $invoice_item->item_comp_qty = 0;
                    $invoice_item->qty_type = 'base';
                }

                if ($item['measuring_unit'] == $sold_item->alternate_measuring_unit) {

                    $alternate_to_base = $sold_item->conversion_of_alternate_to_base_unit_value;

                    $original_qty = $qty * $alternate_to_base;

                    $sold_item->qty = $sold_item->qty - $original_qty;

                    $invoice_item->item_qty = $original_qty;
                    $invoice_item->item_alt_qty = $item['qty'];
                    $invoice_item->item_comp_qty = 0;
                    $invoice_item->qty_type = 'alternate';
                }

                if ($item['measuring_unit'] == $sold_item->compound_measuring_unit) {

                    $alternate_to_base = $sold_item->conversion_of_alternate_to_base_unit_value;
                    $compound_to_alternate = $sold_item->conversion_of_compound_to_alternate_unit_value;

                    $original_qty = $alternate_to_base * $compound_to_alternate * $qty;

                    $sold_item->qty = $sold_item->qty - $original_qty;

                    $invoice_item->item_qty = $original_qty;
                    $invoice_item->item_alt_qty = $compound_to_alternate * $qty;
                    $invoice_item->item_comp_qty = $item['qty'];
                    $invoice_item->qty_type = 'compound';
                }
                $invoice_item->item_measuring_unit = $item['measuring_unit'];
            } else {

                if ($item['free_qty'] > 0) {
                    $qty = $item['qty'] + $item['free_qty'];
                } else {
                    $qty = $item['qty'];
                }

                $sold_item->qty = $sold_item->qty - $qty;

                $invoice_item->item_qty = $item['qty'];
                $invoice_item->item_alt_qty = 0;
                $invoice_item->item_comp_qty = 0;
                $invoice_item->qty_type = 'base';
            }

            $invoice_item->free_qty = $item['free_qty'];

            $sold_item->save();

            $invoice_item->save();
        }

        // $sale_ledger = new SaleLedger;

        // $party = Party::find($request->party);

        // $sale_ledger->particulars = $party->name;
        // $sale_ledger->amount = $amount;
        // $sale_ledger->type = 'debit';

        // $sale_ledger->save();

        // $tax_ledger = new TaxLedger;

        // $tax_ledger->particulars = 'GST';
        // $tax_ledger->amount = $request->gst * $amount / 100;
        // $tax_ledger->type = 'debit';

        // $tax_ledger->save();

        // foreach($foramatted_rule as $rule){


        /*
        if ($request->payment_type == 'cash') {
            $ledger_sale_cr = new Ledger;
            $ledger_sale_dr = new Ledger;
            $ledger_tax_cr = new Ledger;
            $ledger_tax_dr = new Ledger;

            $ledger_sale_cr->account = 24;
            $ledger_sale_cr->particular = 6;
            $ledger_sale_cr->type = 'cr';
            $ledger_sale_cr->amount = $amount;
            $ledger_sale_cr->invoice_id = $invoice->id;

            $ledger_sale_dr->account = 6;
            $ledger_sale_dr->particular = 24;
            $ledger_sale_dr->type = 'dr';
            $ledger_sale_dr->amount = $amount;
            $ledger_sale_dr->invoice_id = $invoice->id;


            $ledger_tax_cr->account = 12;
            $ledger_tax_cr->particular = 6;
            $ledger_tax_cr->type = 'cr';
            $ledger_tax_cr->amount = $gst_amount;
            $ledger_tax_cr->invoice_id = $invoice->id;

            $ledger_tax_dr->account = 6;
            $ledger_tax_dr->particular = 12;
            $ledger_tax_dr->type = 'dr';
            $ledger_tax_dr->amount = $gst_amount;
            $ledger_tax_dr->invoice_id = $invoice->id;

            $ledger_sale_cr->save();
            $ledger_sale_dr->save();
            $ledger_tax_cr->save();
            $ledger_tax_dr->save();
        }   */


        // if($rule == 'party'){
        //     $ledger_party_cr->account = 6;
        //     $ledger_party_cr->particular = 1001;
        //     $ledger_party_cr->type = 'dr';
        //     $ledger_party_cr->amount = $amount;
        //     $ledger_party_cr->invoice_id = $invoice->id;

        //     $ledger_party_dr->account = 1001;
        //     $ledger_party_dr->particular = 6;
        //     $ledger_party_dr->type = 'dr';
        //     $ledger_party_dr->amount = $amount;
        //     $ledger_party_dr->invoice_id = $invoice->id;
        // }



        // if($rule == 'stock'){
        //     $ledger->account = 24;
        //     $ledger->particular = 6;
        //     $ledger->type = 'cr';
        //     $ledger->amount = $amount;
        //     $ledger->invoice_id = $invoice->id;
        // }

        // if($rule == 'credit'){
        //     $ledger->account = 24;
        //     $ledger->particular = 6;
        //     $ledger->type = 'cr';
        //     $ledger->amount = $amount;
        //     $ledger->invoice_id = $invoice->id;
        // }
        // }

        if ($request->submit_type == "save") {
            return redirect()->back()->with('success', 'Invoice created successfully');
        } else if ($request->submit_type == "print") {
            return redirect(route('print.invoice', $invoice->id));
        } else if ($request->submit_type == "email") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendInvoice($invoice->id));
            }
            return redirect()->back()->with('success', 'Invoice created successfully');
        } else if ($request->submit_type == "eway") {
            return redirect(route('eway.bill.create', $invoice->id));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);
        $party = Invoice::findorFail($id)->party;

        // foreach($invoice->items as $item){
        //     echo "id " . $item->id . "<br>";
        //     echo "qty " . $item->pivot->item_qty . "<br>";
        // }

        return view('sale.show', compact('invoice', 'party'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //$invoices = Invoice::findOrFail($id);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function find_invoice_by_party()
    {
        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'debitor')->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        $last_receipt_sale = User::find(Auth::user()->id)->saleRemainingAmounts()->orderBy('id', 'desc')->first();
        $last_receipt_party = User::find(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->orderBy('id', 'desc')->first();

        if ($last_receipt_sale && $last_receipt_party) {
            if (\Carbon\Carbon::parse($last_receipt_sale->created_at) > \Carbon\Carbon::parse($last_receipt_party->created_at)) {
                $last_receipt = $last_receipt_sale;
            } else {
                $last_receipt = $last_receipt_party;
            }
        }
        else if($last_receipt_sale) {
            $last_receipt = $last_receipt_sale;
        }
        else if($last_receipt_party) {
            $last_receipt = $last_receipt_party;
        }
        else {
            $last_receipt = null;
        }

        $voucher_no = null;
        $myerrors = array();

        if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->bill_no_type == 'auto') {

            if ($last_receipt && isset($last_receipt->voucher_no)) {
                $width = isset(auth()->user()->receiptSetting) ? auth()->user()->receiptSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['voucher_no'][] = 'Invalid Max-length provided. Please update your receipt settings.';
                        break;
                    case 1:
                        if ($last_receipt->token > 9) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 2:
                        if ($last_receipt->token > 99) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 3:
                        if ($last_receipt->token > 999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 4:
                        if ($last_receipt->token > 9999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 5:
                        if ($last_receipt->token > 99999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 6:
                        if ($last_receipt->token > 999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 7:
                        if ($last_receipt->token > 9999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 8:
                        if ($last_receipt->token > 99999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 9:
                        if ($last_receipt->token > 999999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->receiptSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->receiptSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->receiptSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['voucher_no'][] = 'Applicable date expired for your receipt voucher no. Please update your receipt settings.';
            }

            if ($last_receipt) {

                if (isset($last_receipt->voucher_no_type) && $last_receipt->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->receiptSetting->updated_at) > \Carbon\Carbon::parse($last_receipt->created_at)) {
                        $voucher_no = isset(auth()->user()->receiptSetting->starting_no) ? auth()->user()->receiptSetting->starting_no - 1 : 0;
                    } else {
                        $voucher_no = ($last_receipt->voucher_no == '' || $last_receipt->voucher_no == null) ? 0 : $last_receipt->voucher_no;
                    }
                } else {
                    $voucher_no = isset(auth()->user()->receiptSetting->starting_no) ? auth()->user()->receiptSetting->starting_no - 1 : 0;
                }
            } else {
                $voucher_no = isset(auth()->user()->receiptSetting->starting_no) ? auth()->user()->receiptSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('sale.find_invoice_by_party', compact('banks', 'parties', 'voucher_no'))->with('myerrors', $myerrors);;
    }

    public function post_find_invoice_by_party(Request $request)
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $closing_balance_from_date = auth()->user()->profile->book_beginning_from;
        if( \Carbon\Carbon::parse($from_date)->lt(\Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)) ){
            $closing_balance_to_date = \Carbon\Carbon::parse($from_date);
        } else {
            $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        }

        $party = Party::find($request->selected_party);
        $total = 0;
        $invoices = Invoice::where('party_id', $party->id)
            ->where('type_of_bill', 'regular')
            ->whereBetween('invoice_date', [$from_date, $to_date])
            ->get();


        $paid_amounts = SaleRemainingAmount::where('party_id', $party->id)
            ->where('is_original_payment', 0)
            ->where('status', 1)
            ->whereBetween('payment_date', [$from_date, $to_date])
            ->get();


        $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
            ->where('type', 'sale')
            ->where('status', 1)
            ->whereBetween('payment_date', [$from_date, $to_date])
            ->get();

        $sale_orders = SaleOrder::where('party_id', $party->id)
            ->where('status', 1)
            ->whereBetween('date', [$from_date, $to_date])
            ->groupBy('token')
            ->get();

        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'sale')
                ->where(function ($query) {
                    $query->where('credit_notes.reason', 'sale_return')->orWhere('credit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('credit_notes.reason', 'discount_on_sale');
                })
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();

        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'sale')->where('debit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();
        
        $a = [];

        foreach( $invoices as $invoice ) {
            $total += $invoice->total_amount;
            array_push($a, $invoice->total_amount);
            $total -= $invoice->bank_payment ?? 0;
            array_push($a, -1*$invoice->bank_payment);
            $total -= $invoice->pos_payment ?? 0;
            array_push($a, -1*$invoice->pos_payment);
            $total -= $invoice->cash_payment ?? 0;
            array_push($a, -1*$invoice->cash_payment);
            $total -= $invoice->discount_payment ?? 0;
            array_push($a, -1*$invoice->discount_payment);

        }    

        foreach( $paid_amounts as $amount ){
            $total -= $amount->amount_paid;
            array_push($a, -1*$amount->amount_paid);
        }

        foreach( $party_paid_amounts as $amount ){
            $total -= $amount->amount;
            array_push($a, -1*$amount->amount);
        }

        foreach ($sale_orders as $order) {
            $total -= $order->amount_received;
            array_push($a, -1*$order->amount_received);
        }

        foreach ($creditNotes as $creditNote) {
            $total -= $creditNote->note_value;
            array_push($a, -1*$creditNote->note_value);
        }

        foreach ($debitNotes as $debitNote) {
            $total += $debitNote->note_value;
            array_push($a, -1*$debitNote->note_value);
        }

        $total += $party->opening_balance;
        $total += $this->calculate_debtor_party_closing_balance($party, $closing_balance_from_date, $closing_balance_to_date);
        array_push($a, $party->opening_balance);

        foreach ($invoices as $record) {
            $remaining_amount_data = SaleRemainingAmount::where('invoice_id', $record->id)->orderBy('id', 'desc')->first();

            $record->remaining_amount = $remaining_amount_data;
        }

        return response()->json(['sale' => $invoices, 'total_pending' => $total, 'from_date' => $from_date, 'to_date' => $to_date, 'array' => $a, 'sale_orders' => $sale_orders]);
    }

    private function calculate_debtor_party_closing_balance($party, $from_date, $to_date)
    {
        $from_date = \Carbon\Carbon::parse($from_date)->format('Y-m-d');
        $to_date = \Carbon\Carbon::parse($to_date)->format('Y-m-d');
        
        // $isDatesSame = false;
        // if($from_date->eq($to_date)){
        //     $isDatesSame = true;
        // }

        $closing_balance = 0;

        $invoices = Invoice::where('party_id', $party->id)
            ->where('type_of_bill', 'regular')
            ->whereBetween('invoice_date', [$from_date, $to_date])
            ->get();


        $paid_amounts = SaleRemainingAmount::where('party_id', $party->id)
            ->where('is_original_payment', 0)
            ->where('status', 1)
            ->whereBetween('payment_date', [$from_date, $to_date])
            ->get();


        $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
            ->where('type', 'sale')
            ->where('status', 1)
            ->whereBetween('payment_date', [$from_date, $to_date])
            ->get();

        $sale_orders = SaleOrder::where('party_id', $party->id)
            ->where('status', 1)
            ->whereBetween('date', [$from_date, $to_date])
            ->get();

        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'sale')
                ->where(function ($query) {
                    $query->where('credit_notes.reason', 'sale_return')->orWhere('credit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('credit_notes.reason', 'discount_on_sale');
                })
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();

        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'sale')->where('debit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();

        foreach( $invoices as $invoice ) {
            if($invoice->gst_classification == 'rcm'){
                // $item_total_amount = $invoice->item_total_amount;
                // $total_discount = $invoice->total_discount ? $invoice->total_discount : 0;
                // $opening_balance += ($item_total_amount - $total_discount);
                $closing_balance += $invoice->total_amount;
            } else{
                $closing_balance += $invoice->total_amount;
            }

            $closing_balance -= $invoice->bank_payment ?? 0;
            $closing_balance -= $invoice->pos_payment ?? 0;
            $closing_balance -= $invoice->cash_payment ?? 0;
            $closing_balance -= $invoice->discount_payment ?? 0;
        }    

        foreach( $paid_amounts as $amount ){
            $closing_balance -= $amount->amount_paid;
        }

        foreach( $party_paid_amounts as $amount ){
            $closing_balance -= $amount->amount;
        }

        foreach ($sale_orders as $order) {
            $closing_balance -= $order->amount_received;
        }

        foreach ($creditNotes as $creditNote) {
            $closing_balance -= $creditNote->note_value;
        }

        foreach ($debitNotes as $debitNote) {
            $closing_balance += $debitNote->note_value;
        }

        return $closing_balance;

    }

    public function get_sale_invoice($invoice, $party)
    {
        $party_id = $party;
        $invoice_id = $invoice;

        $associated_party = Party::find($party_id);

        $sale_amounts = SaleRemainingAmount::where(['invoice_id' => $invoice_id, 'party_id' => $party_id])->get();

        // $credit_notes = CreditNote::where('invoice_id', $invoice_id)->where('type', 'sale')
        // ->where(function ($query) {
        //     $query->where('reason', 'sale_return')->orWhere('reason', 'new_rate_or_discount_value_with_gst')->orWhere('reason', 'discount_on_sale');
        // })
        // ->get();

        // $debit_notes = DebitNote::where('bill_no', $invoice_id)->where('type', 'sale')->where('reason', 'new_rate_or_discount_value_with_gst')->get();

        // return $credit_notes;
        // return $debit_notes;

        $sale_amount = Invoice::where(['id' => $invoice_id, 'party_id' => $party_id])->first();

        $total_amount = $sale_amount->total_amount;

        $invoice_no = $sale_amount->invoice_prefix . $sale_amount->invoice_no . $sale_amount->invoice_suffix;
        $invoice_date = $sale_amount->invoice_date;

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        foreach ($sale_amounts as $sale) {
            if ($sale->type_of_payment == 'bank') {
                $bank = Bank::find($sale->bank_id);

                $sale->bank_name = $bank->name;
                $sale->bank_branch = $bank->branch;
            }
        }

        $last_receipt_sale = User::find(Auth::user()->id)->saleRemainingAmounts()->orderBy('id', 'desc')->first();

        $last_receipt_party = User::find(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->orderBy('id', 'desc')->first();

        if ($last_receipt_sale && $last_receipt_party) {
            if (\Carbon\Carbon::parse($last_receipt_sale->created_at) > \Carbon\Carbon::parse($last_receipt_party->created_at)) {
                $last_receipt = $last_receipt_sale;
            } else {
                $last_receipt = $last_receipt_party;
            }
        }
        else if($last_receipt_sale) {
            $last_receipt = $last_receipt_sale;
        }
        else if($last_receipt_party) {
            $last_receipt = $last_receipt_party;
        }
        else {
            $last_receipt = null;
        }


        $voucher_no = null;
        $myerrors = array();

        if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->bill_no_type == 'auto') {

            if ($last_receipt && isset($last_receipt->voucher_no)) {
                $width = isset(auth()->user()->receiptSetting) ? auth()->user()->receiptSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['voucher_no'][] = 'Invalid Max-length provided. Please update your receipt settings.';
                        break;
                    case 1:
                        if ($last_receipt->token > 9) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 2:
                        if ($last_receipt->token > 99) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 3:
                        if ($last_receipt->token > 999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 4:
                        if ($last_receipt->token > 9999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 5:
                        if ($last_receipt->token > 99999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 6:
                        if ($last_receipt->token > 999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 7:
                        if ($last_receipt->token > 9999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 8:
                        if ($last_receipt->token > 99999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                    case 9:
                        if ($last_receipt->token > 999999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your receipt settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->receiptSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->receiptSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->receiptSetting) && auth()->user()->receiptSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->receiptSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['voucher_no'][] = 'Applicable date expired for your receipt voucher no. Please update your receipt settings.';
            }

            if ($last_receipt) {

                if (isset($last_receipt->voucher_no_type) && $last_receipt->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->receiptSetting->updated_at) > \Carbon\Carbon::parse($last_receipt->created_at)) {
                        $voucher_no = isset(auth()->user()->receiptSetting->starting_no) ? auth()->user()->receiptSetting->starting_no - 1 : 0;
                    } else {
                        $voucher_no = ($last_receipt->voucher_no == '' || $last_receipt->voucher_no == null) ? 0 : $last_receipt->voucher_no;
                    }
                } else {
                    $voucher_no = isset(auth()->user()->receiptSetting->starting_no) ? auth()->user()->receiptSetting->starting_no - 1 : 0;
                }
            } else {
                $voucher_no = isset(auth()->user()->receiptSetting->starting_no) ? auth()->user()->receiptSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('sale.get_sale_invoice', compact('sale_amounts', 'associated_party', 'invoice_id', 'total_amount', 'banks', 'invoice_no', 'invoice_date', 'voucher_no'))->with('myerrors', $myerrors);
    }

    public function post_pending_payment(Request $request)
    {
        $sale_remaining_amount = new SaleRemainingAmount;

        $sale_remaining_amount->invoice_id = $request->invoice_id;
        $sale_remaining_amount->party_id = $request->party;
        $sale_remaining_amount->total_amount = $request->total_amount;
        $sale_remaining_amount->amount_paid = $request->amount_paid;

        $is_voucher_valid = $this->validate_payment_voucher_no($request->voucher_no, auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to);

        if(!$is_voucher_valid) {
            return redirect()->back()->with('failure', "Please provide unique voucher no");
        }

        if ($request->has('voucher_no')) {
            $sale_remaining_amount->voucher_no = $request->voucher_no;
        }


        if (isset(auth()->user()->receiptSetting) && isset(auth()->user()->receiptSetting->bill_no_type)) {
            $sale_remaining_amount->voucher_no_type = auth()->user()->receiptSetting->bill_no_type;
        } else {
            $sale_remaining_amount->voucher_no_type = 'manual';
        }

        if ($request->has('tds_income_tax')) {
            $sale_remaining_amount->tds_income_tax = $request->tds_income_tax;
        }

        if ($request->has('tds_gst')) {
            $sale_remaining_amount->tds_gst = $request->tds_gst;
        }

        if ($request->has('tcs_income_tax')) {
            $sale_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
        }

        if ($request->has('tcs_gst')) {
            $sale_remaining_amount->tcs_gst = $request->tcs_gst;
        }

        if ($request->has('payment_date')) {
            $sale_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));
        }

        $sale_remaining_amount->amount_remaining = $request->amount_remaining;
        $sale_remaining_amount->type_of_payment = $request->type_of_payment;
        // if ($request->type_of_payment == 'bank') {
        //     $sale_remaining_amount->bank_id = $request->bank_id;
        //     $sale_remaining_amount->bank_payment = $request->amount_paid;
        //     $sale_remaining_amount->bank_cheque = $request->bank_cheque;
        // }
        // if($request->type_of_payment == 'cash'){
        //     $sale_remaining_amount->cash_payment = $request->amount_paid;
        // }
        // if ($request->type_of_payment == 'pos') {
        //     $sale_remaining_amount->pos_payment = $request->amount_paid;
        //     $sale_remaining_amount->pos_bank_id = $request->pos_bank_id;
        // }

        //-----------------------------

        // if($request->has('type_of_payment')){

        //     $type_of_payment = $request->type_of_payment;

        //     $cash = array_search('cash', $type_of_payment);
        //     $bank = array_search('bank', $type_of_payment);
        //     $pos = array_search('pos', $type_of_payment);

        //     if (!is_bool($cash)) {
        //         $cash += 1;
        //     }

        //     if (!is_bool($bank)) {
        //         $bank += 1;
        //     }

        //     if (!is_bool($pos)) {
        //         $pos += 1;
        //     }
        //     if ($cash && $bank && $pos) {
        //         $sale_remaining_amount->type_of_payment = 'combined';

        //         $sale_remaining_amount->cash_payment = $request->cashed_amount;
        //         $sale_remaining_amount->bank_payment = $request->banked_amount;
        //         $sale_remaining_amount->pos_payment = $request->posed_amount;

        //         $sale_remaining_amount->bank_id = $request->bank;
        //         $sale_remaining_amount->bank_cheque = $request->bank_cheque;
        //         $sale_remaining_amount->pos_bank_id = $request->pos_bank;


        //     } else if ($bank && $pos) {
        //         $sale_remaining_amount->type_of_payment = 'pos+bank';

        //         $sale_remaining_amount->bank_payment = $request->banked_amount;
        //         $sale_remaining_amount->pos_payment = $request->posed_amount;

        //         $sale_remaining_amount->bank_id = $request->bank;
        //         $sale_remaining_amount->bank_cheque = $request->bank_cheque;
        //         $sale_remaining_amount->pos_bank_id = $request->pos_bank;

        //     } else if ($cash && $pos) {
        //         $sale_remaining_amount->type_of_payment = 'pos+cash';

        //         $sale_remaining_amount->cash_payment = $request->cashed_amount;
        //         $sale_remaining_amount->pos_payment = $request->posed_amount;

        //         $sale_remaining_amount->pos_bank_id = $request->pos_bank;


        //     } else if ($cash && $bank) {
        //         $sale_remaining_amount->type_of_payment = 'bank+cash';

        //         $sale_remaining_amount->cash_payment = $request->cashed_amount;
        //         $sale_remaining_amount->bank_payment = $request->banked_amount;

        //         $sale_remaining_amount->bank_id = $request->bank;
        //         $sale_remaining_amount->bank_cheque = $request->bank_cheque;


        //     } else if ($bank) {
        //         $sale_remaining_amount->type_of_payment = 'bank';

        //         $sale_remaining_amount->bank_payment = $request->banked_amount;

        //         $sale_remaining_amount->bank_id = $request->bank;
        //         $sale_remaining_amount->bank_cheque = $request->bank_cheque;

        //     } else if ($cash) {
        //         $sale_remaining_amount->type_of_payment = 'cash';

        //         $sale_remaining_amount->cash_payment = $request->cashed_amount;

        //     } else if ($pos) {
        //         $sale_remaining_amount->type_of_payment = 'pos';

        //         $sale_remaining_amount->pos_payment = $request->posed_amount;

        //         $sale_remaining_amount->pos_bank_id = $request->pos_bank;

        //     }
        // } else {
        //     $sale_remaining_amount->type_of_payment = 'no_payment';
        // }

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('discount', $type_of_payment);

            //array_search returns false or array index (if found). 0 can be an array index and it also acts as false in programming so adding 1 to it, so that it becomes 0+1 ie 1 (0 is always false and value more than 0 is always true) which is true
            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'combined';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'bank+pos+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'cash+pos+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $sale_remaining_amount->type_of_payment = 'bank+cash+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $sale_remaining_amount->type_of_payment = 'bank+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->banked_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $sale_remaining_amount->type_of_payment = 'cash+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->discounted_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'pos+discount';

                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $sale_remaining_amount->type_of_payment = 'discount';

                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->discounted_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $sale_remaining_amount->type_of_payment = 'cash+bank+pos';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $sale_remaining_amount->type_of_payment = 'bank+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->cashed_amount + $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $sale_remaining_amount->type_of_payment = 'pos+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->banked_amount + $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $sale_remaining_amount->type_of_payment = 'bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $sale_remaining_amount->type_of_payment = 'pos';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $sale_remaining_amount->type_of_payment = 'cash';

                $sale_remaining_amount->pos_payment = $request->cashed_amount;

                // $amount = $request->cashed_amount;
            }
        } else {
            $sale_remaining_amount->type_of_payment = 'no_payment';
        }

        if ($sale_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Data uploaded successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to upload data');
        }
    }

    public function view_pending_receivable(Request $request)
    {
        // if ($request->has('from_date') && $request->has('to_date')) {
        //     $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
        //     $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        // } else {
        //     $from_date = auth()->user()->profile->financial_year_from;
        //     $to_date = auth()->user()->profile->financial_year_to;
        // }

        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'debitor')->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();
        
        return view('sale.view_pending_receivable', compact('parties', 'banks'));
    }

    public function get_pending_receivable(Request $request)
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        // return response()->json([$from_date, $to_date]);

        $sale_amounts = auth()->user()->saleRemainingAmounts()->where('sale_remaining_amounts.party_id', $request->selected_party)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->get();
        
        $account_amounts = auth()->user()->partyRemainingAmounts()->where('party_pending_payment_account.party_id', $request->selected_party)->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->get();

        foreach($sale_amounts as $amount) {
            if($amount->is_original_payment == 1) {
                $invoice = Invoice::find($amount->invoice_id);
                if($invoice) {
                    $amount->voucher_no = $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix;
                    $amount->voucher_url = route('edit.invoice.form', $invoice->id);
                }
            }
        }

        return response()->json(['bill' => $sale_amounts, 'account' => $account_amounts]);
    }

    public function update_sale_pending_receivable_status(Request $request, $id)
    {
        $payment = SaleRemainingAmount::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();
        return redirect()->back();
    }

    public function update_party_sale_pending_receivable_status(Request $request, $id)
    {
        $payment = PartyPendingPaymentAccount::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();
        return redirect()->back();
    }

    public function view_pending_receivable_detail($id) {
        $payment = SaleRemainingAmount::findOrFail($id);
        $banks = Bank::all();
        $type="sale";
        return view('payments.pending_payments', compact('payment', 'banks', 'type'));
    }

    public function view_party_pending_receivable_detail($id) {
        $payment = PartyPendingPaymentAccount::findOrFail($id);
        $banks = Bank::all();
        $type="sale";
        return view('payments.party_pending_payments', compact('payment', 'banks', 'type'));
    }

    public function update_pending_receivable_detail(Request $request, $id)
    {
        $sale_remaining_amount = SaleRemainingAmount::findOrFail($id);

        $sale_remaining_amount->invoice_id = $request->invoice_id;
        $sale_remaining_amount->party_id = $request->party;
        $sale_remaining_amount->total_amount = $request->total_amount;
        $sale_remaining_amount->amount_paid = $request->amount_paid;

        if ($request->has('voucher_no')) {
            $sale_remaining_amount->voucher_no = $request->voucher_no;
        }

        if ($request->has('tds_income_tax')) {
            $sale_remaining_amount->tds_income_tax = $request->tds_income_tax;
        }

        if ($request->has('tds_gst')) {
            $sale_remaining_amount->tds_gst = $request->tds_gst;
        }

        if ($request->has('tcs_income_tax')) {
            $sale_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
        }

        if ($request->has('tcs_gst')) {
            $sale_remaining_amount->tcs_gst = $request->tcs_gst;
        }

        if ($request->has('payment_date')) {
            $sale_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));
        }

        $sale_remaining_amount->amount_remaining = $request->amount_remaining;
        $sale_remaining_amount->type_of_payment = $request->type_of_payment;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('discount', $type_of_payment);

            //array_search returns false or array index (if found). 0 can be an array index and it also acts as false in programming so adding 1 to it, so that it becomes 0+1 ie 1 (0 is always false and value more than 0 is always true) which is true
            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'combined';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'bank+pos+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'cash+pos+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $sale_remaining_amount->type_of_payment = 'cash+bank+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $sale_remaining_amount->type_of_payment = 'bank+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->banked_amount + $request->discounted_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $sale_remaining_amount->type_of_payment = 'cash+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount + $request->discounted_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'pos+discount';

                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->posed_amount + $request->discounted_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $sale_remaining_amount->type_of_payment = 'discount';

                $sale_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->discounted_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $sale_remaining_amount->type_of_payment = 'cash+bank+pos';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $sale_remaining_amount->type_of_payment = 'bank+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->cashed_amount + $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $sale_remaining_amount->type_of_payment = 'pos+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->banked_amount + $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $sale_remaining_amount->type_of_payment = 'bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $sale_remaining_amount->type_of_payment = 'pos';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $sale_remaining_amount->type_of_payment = 'cash';

                $sale_remaining_amount->pos_payment = $request->cashed_amount;

                // $amount = $request->cashed_amount;
            }
        } else {
            $sale_remaining_amount->type_of_payment = 'no_payment';
        }

        if ($sale_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function update_party_pending_receivable_detail(Request $request, $id)
    {
        $pending_payment = PartyPendingPaymentAccount::findOrFail($id);

        $amount = 0;

        $pending_payment->party_id = $request->party_id;

        $pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

        if ($request->has('voucher_no')) {
            $pending_payment->voucher_no = $request->voucher_no;
        }

        $pending_payment->remarks = $request->remarks;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('discount', $type_of_payment);

            //array_search returns false or array index (if found). 0 can be an array index and it also acts as false in programming so adding 1 to it, so that it becomes 0+1 ie 1 which is true
            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {
                $pending_payment->type_of_payment = 'combined';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $pending_payment->type_of_payment = 'bank+pos+discount';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $pending_payment->type_of_payment = 'cash+pos+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->posed_amount + $request->discounted_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $pending_payment->type_of_payment = 'cash+bank+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $pending_payment->type_of_payment = 'bank+discount';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $pending_payment->type_of_payment = 'cash+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->discounted_amount;

                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $pending_payment->type_of_payment = 'pos+discount';

                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->posed_amount + $request->discounted_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $pending_payment->type_of_payment = 'discount';

                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->discounted_amount;

                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $pending_payment->type_of_payment = 'cash+bank+pos';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $pending_payment->type_of_payment = 'bank+cash';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;

                $amount = $request->cashed_amount + $request->banked_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $pending_payment->type_of_payment = 'pos+cash';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->cashed_amount + $request->posed_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $pending_payment->type_of_payment = 'pos+bank';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->banked_amount + $request->posed_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $pending_payment->type_of_payment = 'bank';

                $pending_payment->bank_payment = $request->banked_amount;

                $amount = $request->banked_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $pending_payment->type_of_payment = 'pos';

                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->posed_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $pending_payment->type_of_payment = 'cash';

                $pending_payment->pos_payment = $request->cashed_amount;

                $amount = $request->cashed_amount;
            }
        } else {
            $pending_payment->type_of_payment = 'no_payment';
        }


        $pending_payment->amount = $amount;

        if ($request->has('tds_income_tax_amount') && !empty($request->tds_income_tax_amount)) {
            $pending_payment->amount += $request->tds_income_tax_amount;
        }

        if ($request->has('tds_gst') && !empty($request->tds_gst)) {
            $pending_payment->amount += $request->tds_gst;
        }

        if ($request->has('tcs_income_tax') && !empty($request->tcs_income_tax)) {
            $pending_payment->amount += $request->tcs_income_tax;
        }

        if ($request->has('tcs_gst') && !empty($request->tcs_gst)) {
            $pending_payment->amount += $request->tcs_gst;
        }

        // $pending_payment->tds_income_tax_amount = $request->tds_income_tax_amount;
        // $pending_payment->tds_gst_amount = $request->tds_gst;
        // $pending_payment->tcs_income_tax_amount = $request->tcs_income_tax;
        // $pending_payment->tcs_gst_amount = $request->tcs_gst;

        // $pending_payment->tds_income_tax_checked = $request->tds_income_tax_checked;
        // $pending_payment->tds_gst_checked = $request->tds_gst_checked;
        // $pending_payment->tcs_income_tax_checked = $request->tcs_income_tax_checked;
        // $pending_payment->tcs_gst_checked = $request->tcs_gst_checked;

        $pending_payment->type = "sale";

        if ($pending_payment->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function post_fetch_item_by_barcode(Request $request)
    {
        $barcode = $request->barcode ?? null;
        $item = null;

        if ($barcode) {
            $purchase = Purchase::where('barcode', $barcode)->first();
            if ($purchase)
                $item = Item::find($purchase->item_id);
        }

        return response()->json(['item' => $item]);
    }

    public function get_note_view(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sale_records = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->OrderBy('id', 'desc')->get();

        // return $sale_records;

        foreach ($sale_records as $record) {
            $credit_notes = CreditNote::where('invoice_id', $record->id)->where('type', 'sale')->first();
            $debit_notes = DebitNote::where('bill_no', $record->id)->where('type', 'sale')->first();
            $record->bill_note = BillNote::where('bill_no', $record->id)->where('type', 'sale')->get();

            if ($credit_notes) {
                $record->hasCreditNote = true;
                $record->credit_note_no = $credit_notes->note_no;
            } else {
                $record->hasCreditNote = false;
            }

            if ($debit_notes) {
                $record->hasDebitNote = true;
                $record->debit_note_no = $debit_notes->note_no;
            } else {
                $record->hasDebitNote = false;
            }

            if (count($record->bill_note) > 0) {
                $record->hasBillNote = true;
            } else {
                $record->hasBillNote = false;
            }
        }

        return view('sale.note', compact('sale_records'));
    }

    public function invoice_detail_credit_note($invoice_id)
    {

        // $creditNote = CreditNote::where('invoice_id', $invoice_id)->where('type', 'sale')->first();

        // $note_no = $creditNote->note_no;
        // $note_date = Carbon::parse($creditNote->created_at)->format('d/m/Y');

        // $creditNotes = CreditNote::where ('invoice_id', $invoice_id)->where('type', 'sale')->get();

        // return $debitNotes;

        $invoice = Invoice::findOrFail($invoice_id);

        // $invoice_no = $invoice_id;

        // foreach ($creditNotes as $note) {
        //     $item = Item::findOrFail($note->item_id);

        //     $note->item_name = $item->name;
        //     $note->item_gst = $item->gst;
        // }

        $credit_note = User::find(Auth::user()->id)->creditNotes()->orderBy('id', 'desc')->first();
        $debit_note = User::find(Auth::user()->id)->debitNotes()->orderBy('id', 'desc')->first();

        if ($credit_note && $debit_note) {
            if (\Carbon\Carbon::parse($credit_note->created_at) > \Carbon\Carbon::parse($debit_note->created_at)) {
                $last_note = $credit_note;
            } else {
                $last_note = $debit_note;
            }
        }
        else if($credit_note) {
            $last_note = $credit_note;
        }
        else if($debit_note) {
            $last_note = $debit_note;
        }
        else {
            $last_note = null;
        }

        $note_no = null;
        $myerrors = array();

        if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->bill_no_type == 'auto') {
            if (isset($last_note->note_no)) {
                $width = isset(auth()->user()->noteSetting) ? auth()->user()->noteSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['note_no'][] = 'Invalid Max-length provided. Please update your note settings.';
                        break;
                    case 1:
                        if ($last_note->note_no > 9) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 2:
                        if ($last_note->note_no > 99) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 3:
                        if ($last_note->note_no > 999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 4:
                        if ($last_note->note_no > 9999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 5:
                        if ($last_note->note_no > 99999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 6:
                        if ($last_note->note_no > 999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 7:
                        if ($last_note->note_no > 9999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 8:
                        if ($last_note->note_no > 99999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 9:
                        if ($last_note->note_no > 999999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->noteSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->noteSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->noteSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['note_no'][] = 'Applicable date expired for note_no. Please update your note settings.';
            }

            if ($last_note) {

                if (isset($last_note->voucher_no_type) && $last_note->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->noteSetting->updated_at) > \Carbon\Carbon::parse($last_note->created_at)) {
                        $note_no = isset(auth()->user()->noteSetting->starting_no) ? auth()->user()->noteSetting->starting_no - 1 : 0;
                    } else {
                        $note_no = ($last_note->note_no == '' || $last_note->note_no == null) ? 0 : $last_note->note_no;
                    }
                } else {
                    $note_no = isset(auth()->user()->noteSetting->starting_no) ? auth()->user()->noteSetting->starting_no - 1 : 0;
                }
            } else {
                $note_no = isset(auth()->user()->noteSetting->starting_no) ? auth()->user()->noteSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('sale.detail_note', compact('invoice', 'note_no'))->with('myerrors', $myerrors);
    }

    public function invoice_detail_debit_note($invoice_id)
    {
        // $debitNote = DebitNote::where('bill_no', $invoice_id)->where('type', 'sale')->first();

        // $note_no = $debitNote->note_no;
        // $note_date = Carbon::parse($debitNote->created_at)->format('d/m/Y');

        // $debitNotes = DebitNote::where('bill_no', $invoice_id)->where('type', 'sale')->get();

        $invoice = Invoice::findOrFail($invoice_id);

        // $invoice_no = $invoice_id;

        // foreach ($debitNotes as $note) {
        //     $item = Item::find($note->item_id);

        //     $note->item_name = $item->name;
        //     $note->item_gst = $item->gst;
        // }

        $credit_note = User::find(Auth::user()->id)->creditNotes()->orderBy('id', 'desc')->first();
        $debit_note = User::find(Auth::user()->id)->debitNotes()->orderBy('id', 'desc')->first();

        if ($credit_note && $debit_note) {
            if (\Carbon\Carbon::parse($credit_note->created_at) > \Carbon\Carbon::parse($debit_note->created_at)) {
                $last_note = $credit_note;
            } else {
                $last_note = $debit_note;
            }
        }
        else if($credit_note) {
            $last_note = $credit_note;
        }
        else if($debit_note) {
            $last_note = $debit_note;
        }
        else {
            $last_note = null;
        }

        $note_no = null;
        $myerrors = array();

        if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->bill_no_type == 'auto') {
            if (isset($last_note->note_no)) {
                $width = isset(auth()->user()->noteSetting) ? auth()->user()->noteSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['note_no'][] = 'Invalid Max-length provided. Please update your note settings.';
                        break;
                    case 1:
                        if ($last_note->note_no > 9) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 2:
                        if ($last_note->note_no > 99) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 3:
                        if ($last_note->note_no > 999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 4:
                        if ($last_note->note_no > 9999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 5:
                        if ($last_note->note_no > 99999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 6:
                        if ($last_note->note_no > 999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 7:
                        if ($last_note->note_no > 9999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 8:
                        if ($last_note->note_no > 99999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                    case 9:
                        if ($last_note->note_no > 999999999) {
                            $myerrors['note_no'][] = 'Max-length exceeded for note no. Please update your note settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->noteSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->noteSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->noteSetting) && auth()->user()->noteSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->noteSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['note_no'][] = 'Applicable date expired for note_no. Please update your note settings.';
            }

            if ($last_note) {

                if (isset($last_note->voucher_no_type) && $last_note->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->noteSetting->updated_at) > \Carbon\Carbon::parse($last_note->created_at)) {
                        $note_no = isset(auth()->user()->noteSetting->starting_no) ? auth()->user()->noteSetting->starting_no - 1 : 0;
                    } else {
                        $note_no = ($last_note->note_no == '' || $last_note->note_no == null) ? 0 : $last_note->note_no;
                    }
                } else {
                    $note_no = isset(auth()->user()->noteSetting->starting_no) ? auth()->user()->noteSetting->starting_no - 1 : 0;
                }
            } else {
                $note_no = isset(auth()->user()->noteSetting->starting_no) ? auth()->user()->noteSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('sale.detail_debit_note', compact('invoice', 'note_no'))->with('myerrors', $myerrors);
    }

    public function invoice_create_credit_note(Request $request, $invoice_id)
    {

        $item_id = $request->item_id;
        $price_difference = $request->price_difference;
        $gst_difference = $request->gst_difference;
        $quantity_difference = $request->quantity_difference;
        $measuring_unit = $request->measuring_unit;
        $discount_difference = $request->discount_difference;

        $loopCount = count($item_id);
        for ($i = 0; $i < $loopCount; $i++) {
            $found_item = Item::find($item_id[$i]);
            $creditNote = new CreditNote;
            $creditNote->note_no = $request->note_no;

            if (isset(auth()->user()->noteSetting) && isset(auth()->user()->noteSetting->bill_no_type)) {
                $creditNote->voucher_no_type = auth()->user()->noteSetting->bill_no_type;
            } else {
                $creditNote->voucher_no_type = 'manual';
            }

            $qty = $quantity_difference[$i];
            $original_qty = $qty;
            if (isset($measuring_unit[$i])) {
                if ($measuring_unit[$i] == $found_item->alternate_measuring_unit) {
                    $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                    $original_qty = $qty * $alternate_to_base;
                    $qty = $original_qty;
                }

                if ($measuring_unit[$i] == $found_item->compound_measuring_unit) {
                    $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                    $compound_to_alternate = $found_item->conversion_of_compound_to_alternate_unit_value;
                    $original_qty = $alternate_to_base * $compound_to_alternate * $qty;
                    $qty = $original_qty;
                }

            }

            $creditNote->note_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->note_date)));
            $creditNote->item_id = $item_id[$i];
            $creditNote->invoice_id = $invoice_id;
            $creditNote->price = $price_difference[$i];
            $creditNote->gst = $gst_difference[$i];
            $creditNote->quantity = $qty;
            $creditNote->original_qty = $original_qty;
            $creditNote->original_unit = $measuring_unit[$i];
            $creditNote->discount = $discount_difference[$i];
            $creditNote->reason = $request->reason_change;
            $creditNote->taxable_value = $request->taxable_value;
            $creditNote->discount_value = $request->discount_value;
            $creditNote->gst_value = $request->gst_value;
            $creditNote->note_value = $request->note_value;

            $creditNote->type = 'sale';

            $creditNote->save();
        }

        if ($request->submit_type == "print") {
            // return redirect(route('show.sale.credit.note', $request->note_no));
        } else if ($request->submit_type == "email") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendSaleCreditNote($request->note_no));
            }
            // return redirect()->back()->with('success', 'Note created successfully');
        } else if ($request->submit_type == "eway") {
            // return redirect(route('list.invoice.credit.note', $invoice_id));
        }

        return redirect(route('list.invoice.credit.note', $invoice_id))->with('success', 'Note created successfully!');
        // return redirect()->back()->with('success', 'Note created successfully!');
        // return redirect()->route('invoice.detail.credit.note', $invoice_id)->with('success', 'Note created successfully!');
    }

    public function unique_invoice_credit_note_no(Request $request)
    {
        $is_note_no_valid = true;
        $note = CreditNote::where('note_no', $request->note_no)->where('type', 'sale')->first();

        if ($note) {
            $is_note_no_valid = false;
        }

        return response()->json($is_note_no_valid);
    }

    public function invoice_create_debit_note(Request $request, $invoice_id)
    {

        $item_id = $request->item_id;
        $price_difference = $request->price_difference;
        $gst_difference = $request->gst_difference;
        $quantity_difference = $request->quantity_difference;
        $measuring_unit = $request->measuring_unit;
        $discount_difference = $request->discount_difference;

        $loopCount = count($item_id);
        for ($i = 0; $i < $loopCount; $i++) {
            $found_item = Item::find($item_id[$i]);
            $debitNote = new DebitNote;
            $debitNote->note_no = $request->note_no;

            if (isset(auth()->user()->noteSetting) && isset(auth()->user()->noteSetting->bill_no_type)) {
                $debitNote->voucher_no_type = auth()->user()->noteSetting->bill_no_type;
            } else {
                $debitNote->voucher_no_type = 'manual';
            }

            $qty = $quantity_difference[$i];
            $original_qty = $qty;
            if (isset($measuring_unit[$i])) {
                if ($measuring_unit[$i] == $found_item->alternate_measuring_unit) {
                    $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                    $original_qty = $qty * $alternate_to_base;
                    $qty = $original_qty;
                }

                if ($measuring_unit[$i] == $found_item->compound_measuring_unit) {
                    $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                    $compound_to_alternate = $found_item->conversion_of_compound_to_alternate_unit_value;
                    $original_qty = $alternate_to_base * $compound_to_alternate * $qty;
                    $qty = $original_qty;
                }

            }

            $debitNote->note_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->note_date)));
            $debitNote->item_id = $item_id[$i];
            $debitNote->bill_no = $invoice_id;
            $debitNote->price = $price_difference[$i];
            $debitNote->gst = $gst_difference[$i];
            $debitNote->quantity = $qty;
            $debitNote->original_qty = $original_qty;
            $creditNote->original_unit = $measuring_unit[$i];
            $debitNote->discount = $discount_difference[$i];
            $debitNote->reason = $request->reason_change;
            $debitNote->taxable_value = $request->taxable_value;
            $debitNote->discount_value = $request->discount_value;
            $debitNote->gst_value = $request->gst_value;
            $debitNote->note_value = $request->note_value;

            $debitNote->type = 'sale';

            $debitNote->save();
        }

        if ($request->submit_type == "print") {
            // return redirect(route('show.sale.debit.note', $request->note_no));
        } else if ($request->submit_type == "email") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendSaleDebitNote($request->note_no));
            }
            // return redirect()->back()->with('success', 'Note created successfully');
        } else if ($request->submit_type == "eway") {
            // return redirect(route('eway.bill.create'));
        }

        return redirect(route('list.invoice.debit.note', $invoice_id))->with('success', 'Note created successfully!');
        // return redirect()->back()->with('success', 'Note created successfully!');
        //return redirect()->route('invoice.detail.debit.note', $invoice_id)->with('success', 'Note created successfully!');
    }

    public function unique_invoice_debit_note_no(Request $request)
    {
        $is_note_no_valid = true;
        $note = DebitNote::where('note_no', $request->note_no)->where('type', 'sale')->first();

        if ($note) {
            $is_note_no_valid = false;
        }

        return response()->json($is_note_no_valid);
    }

    public function edit_sale_qty(Request $request)
    {
        $sale = Invoice::find($request->row_id);
        $item = Item::find($sale->item_id);
        $item->qty = $item->qty + ($request->old_quantity - $request->new_quantity);
        $item->save();

        $sale->qty = $request->new_quantity;

        // return $request->all();
        if ($sale->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('success', 'Failed to update data');
        }
    }

    public function list_invoice_credit_note($invoice_id)
    {
        $credit_notes = CreditNote::where('invoice_id', $invoice_id)->where('type', 'sale')->groupBy('note_no')->get();
        return view('sale.list-invoice-credit-note', compact('credit_notes'));
    }

    public function list_invoice_debit_note($invoice_id)
    {
        $debit_notes = DebitNote::where('bill_no', $invoice_id)->where('type', 'sale')->groupBy('note_no')->get();
        return view('sale.list-invoice-debit-note', compact('debit_notes'));
    }

    public function get_row_by_invoice(Request $request)
    {

        $sale_record = Invoice::Where('id', $request->search_invoice)->get();

        // return view('sale.note', compact('sale_records'));

        return response()->json($sale_record);
    }

    public function add_commission_to_invoice(Request $request)
    {

        $invoice = Invoice::find($request->row_id);

        $invoice->commission = $invoice->total_amount * $request->commission / 100;

        $invoice->save();

        return response()->json($invoice);
    }

    public function create_or_update_credit_note(Request $request)
    {
        // return $request->all();

        if ($request->price != $request->price_difference) {
            $param['price_difference'] = $request->price_difference;
        }

        if ($request->quantity != $request->quantity_difference) {
            $param['quantity_difference'] = $request->quantity_difference;
        }

        if ($request->gst != $request->gst_percent_difference) {
            $param['gst_percent_difference'] = $request->gst_percent_difference;
        }

        if ($request->discount != $request->discount_difference) {
            $param['discount_difference'] = $request->discount_difference;
        }

        $param['reason'] = $request->reason_change;

        $item = Item::find($request->item_id);

        if ($request->reason_change == 'sale_return') {
            $item->qty += $request->quantity_difference;
            $item->save();
        }

        if ($request->reason_change == 'other') {
            $param['reason'] .= " (" . $request->reason_change_other . ")";
        }

        $param['taxable_value'] = $request->taxable_value;
        $param['discount_value'] = $request->discount_value;
        $param['gst_value'] = $request->gst_value;
        $param['note_value'] = $request->note_value;


        $creditNote = CreditNote::updateOrCreate(
            [
                'invoice_id' => $request->invoice_id,
                'item_id' => $request->item_id,
                'type' => $request->note_type,
            ],
            $param
        );

        // dd( $creditNote);

        if ($creditNote) {
            return redirect()->back()->with('success', 'Note saved/updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save/update note');
        }
    }

    public function create_or_update_debit_note(Request $request)
    {
        // return $request->all();

        // if($request->has('price')){
        //     $param = [
        //         'price' => $request->price,
        //         'price_difference' => $request->price_difference,
        //         'reason_price_change' => $request->reason_price_change
        //     ];

        //     if($request->reason_price_change == 'other') {
        //         $param['reason_price_change_other'] = $request->reason_price_change_other;
        //     }
        // }

        // else if ($request->has('gst')) {
        //     $param = [
        //         'gst' => $request->gst,
        //         'gst_percent_difference' => $request->gst_percent_difference,
        //         'reason_gst_change' => $request->reason_price_change
        //     ];

        //     if($request->reason_gst_change == 'other'){
        //         $param['reason_gst_change_other'] = $request->reason_gst_change_other;
        //     }
        // }

        // else if ($request->has('quantity')) {
        //     $param = [
        //         'quantity' => $request->quantity,
        //         'quantity_difference' => $request->quantity_difference,
        //         'reason_quantity_change' => $request->reason_quantity_change
        //     ];

        //     if($request->reason_quantity_change == 'other') {
        //         $param['reason_quantity_change_other'] = $request->reason_quantity_change_reason;
        //     }
        // }

        if ($request->price != $request->price_difference) {
            $param['price_difference'] = $request->price_difference;
        }

        if ($request->quantity != $request->quantity_difference) {
            $param['quantity_difference'] = $request->quantity_difference;
        }

        if ($request->gst != $request->gst_percent_difference) {
            $param['gst_percent_difference'] = $request->gst_percent_difference;
        }

        if ($request->discount != $request->discount_difference) {
            $param['discount_difference'] = $request->discount_difference;
        }


        $param['reason'] = $request->reason_change;

        if ($request->reason_change == 'other') {
            $param['reason'] .= " (" . $request->reason_change_other . ")";
        }

        $param['taxable_value'] = $request->taxable_value;
        $param['discount_value'] = $request->discount_value;
        $param['gst_value'] = $request->gst_value;
        $param['note_value'] = $request->note_value;

        // else {
        //     $param = [
        //         'price' => $request->price,
        //         'price_difference' => $request->price_difference,
        //         'gst' => $request->gst,
        //         'gst_percent_difference' => $request->gst_percent_difference,
        //         'quantity' => $request->quantity,
        //         'quantity_difference' => $request->quantity_difference,
        //         'remain' => $request->remain,
        //         'remarks' => $request->remarks
        //     ];
        // }

        $debitNote = DebitNote::updateOrCreate(
            [
                'item_id' => $request->item_id,
                'bill_no' => $request->invoice_id,
                'type' => $request->note_type
            ],
            $param
        );

        $item = Item::find($request->item_id);

        if ($request->note_type == 'sale') {
            $item->qty -= $request->price_difference;
        } else if ($request->note_type == 'sale') {
            $item->qty += $request->price_difference;
        }

        $item->save();

        if ($debitNote) {
            return redirect()->back()->with('success', 'Note saved/updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save/update note');
        }
    }

    public function delete_credit_note(Request $request)
    {

        // return $request->all();

        if ($request->has('row_id') && $request->row_id != null) {

            $credit_note = CreditNote::find($request->row_id);

            // return $credit_note;

            if ($credit_note) {
                $credit_note_count = CreditNote::where('note_no', $credit_note->note_no)->where('type', 'sale')->whereBetween('note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->count();

                $credit_note->delete();

                if ($credit_note_count > 1) {
                    return redirect()->back()->with('success', 'Item deleted successfully!');
                } else {
                    return redirect()->route('sale.note')->with('success', 'Credit Note deleted successfully!');
                }
            }
        }

        return redirect()->back()->with('failure', 'No such item exist in the note');
    }

    public function delete_debit_note(Request $request)
    {
        if ($request->has('row_id') && $request->row_id != null) {
            $debit_note = DebitNote::find($request->row_id);

            if ($debit_note) {
                $debit_note_count = DebitNote::where('note_no', $debit_note->note_no)->where('type', 'sale')->whereBetween('note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->count();

                $debit_note->delete();

                if ($debit_note_count > 1) {
                    return redirect()->back()->with('success', 'Item deleted successfully!');
                } else {
                    return redirect()->route('sale.note')->with('success', 'Debit Note deleted successfully!');
                }
            }
        }

        return redirect()->back()->with('failure', 'No such item exist in the note');
    }

    public function bill_type_regular($id)
    {
        $sale_record = Invoice::find($id);

        $sale_record->type_of_bill = 'regular';

        $invoice_items = Invoice_Item::where('invoice_id', $id)->get();

        foreach ($invoice_items as $record) {
            $item = Item::find($record->item_id);

            $item->qty = $item->qty - $record->item_qty;

            $item->save();
        }

        $debit_notes = DebitNote::where('bill_no', $id)->where('type', 'sale')->whereBetween('note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();
        $credit_notes = CreditNote::where('invoice_id', $id)->where('type', 'sale')->whereBetween('note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

        foreach($debit_notes as $note) {
            $found_note = DebitNote::find($note->id);
            $found_note->status = 1;
            $found_note->save();
        }

        foreach($credit_notes as $note) {
            $found_note = CreditNote::find($note->id);
            $found_note->status = 1;
            $found_note->save();
        }

        $sale_remaining_amount = SaleRemainingAmount::where('invoice_id', $id)->get();

        foreach($sale_remaining_amount as $amount) {
            $foundAmount = SaleRemainingAmount::find($amount->id);
            $foundAmount->status = 1;
            $foundAmount->save();
        }

        if ($sale_record->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function bill_type_cancel($id)
    {
        $sale_record = Invoice::find($id);

        $sale_record->type_of_bill = 'cancel';

        $invoice_items = Invoice_Item::where('invoice_id', $id)->get();

        foreach ($invoice_items as $record) {
            $item = Item::find($record->item_id);

            $item->qty = $item->qty + $record->item_qty;

            $item->save();
        }

        $debit_notes = DebitNote::where('bill_no', $id)->where('type', 'sale')->whereBetween('note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();
        $credit_notes = CreditNote::where('invoice_id', $id)->where('type', 'sale')->whereBetween('note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

        foreach($debit_notes as $note) {
            $found_note = DebitNote::find($note->id);
            $found_note->status = 0;
            $found_note->save();
        }

        foreach($credit_notes as $note) {
            $found_note = CreditNote::find($note->id);
            $found_note->status = 0;
            $found_note->save();
        }

        $sale_remaining_amount = SaleRemainingAmount::where('invoice_id', $id)->get();

        foreach($sale_remaining_amount as $amount) {
            $foundAmount = SaleRemainingAmount::find($amount->id);
            $foundAmount->status = 0;
            $foundAmount->save();
        }

        if ($sale_record->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function sale_report()
    {

        $parties = Party::where('user_id', Auth::user()->id)->get();

        foreach ($parties as $party) {
            $records = Invoice::where('party_id', $party->id)->where('type_of_bill', 'regular')->get();
            $count = 0;
            foreach ($records as $record) {
                $sale_remaining = SaleRemainingAmount::where('party_id', $party->id)->where('invoice_id', $record->id)->orderBy('id', 'desc')->first();

                if ($sale_remaining != null) {
                    $sale_records[$count] = $sale_remaining;
                    $sale_records[$count]->party_name = $party->name;
                }
                $count++;
            }
        }

        // return $sale_records;

        return view('sale.creditor', compact('sale_records'));
    }

    public function b2b_sale(Request $request)
    {
        // SELECT invoice_id, gst_rate, SUM(gst), SUM(cess) FROM invoice_item GROUP BY gst_rate, invoice_id

        if (isset($request->from) && isset($request->to)) {
            $from = date('Y-m-d', strtotime(str_replace('/', '-', $request->from)));
            $to = date('Y-m-d', strtotime(str_replace('/', '-', $request->to)));

            $invoices = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from, $to])->get();
        } else {
            $invoices = User::findOrFail(Auth::user()->id)->invoices()->get();
        }

        foreach ($invoices as $invoice) {

            // $items = Invoice_Item::where( 'invoice_id', $invoice->id )->get();

            $invoice_items[$invoice->id] = DB::table('invoice_item')
                ->select('invoice_id', 'gst_rate', DB::raw('SUM(gst) as taxable_value'), DB::raw('SUM(cess) as total_cess'))
                ->groupBy('invoice_id')
                ->groupBy('gst_rate')
                ->where('invoice_id', $invoice->id)
                ->get();


            // $party = Party::find($invoice->party_id);
            // $state = State::find($party->business_place);

            // $invoice->invoice_no = $invoice->invoice_no ? $invoice->invoice_no : $invoice->id;
            // $invoice->place_of_supply = $state->state_code."-".$state->name;
            // $invoice->reverse_charge = ($party->reverse_charge) ? $party->reverse_charge : 'No';
            // $invoice->party_gst_no = $party->gst;

            foreach ($invoice_items[$invoice->id] as $item) {
                $invoice = Invoice::find($item->invoice_id);

                $party = Party::find($invoice->party_id);
                $state = State::find($party->business_place);

                $item->invoice_no = $invoice->invoice_no ? $invoice->invoice_no : $invoice->id;
                $item->invoice_date = $invoice->invoice_date;
                $item->invoice_value = $invoice->total_amount;
                $item->place_of_supply = $state->state_code . "-" . $state->name;
                $item->reverse_charge = ($party->reverse_charge) ? $party->reverse_charge : 'No';
                $item->invoice_type = $invoice->type_of_bill;
                $item->party_gst_no = $party->gst;
            }
        }



        // return $invoice_items;


        if (isset($request->export_to_excel) && $request->export_to_excel == "yes") {
            $invoicesArray = [];

            // Define the Excel spreadsheet headers
            $invoicesArray[] = ['Invoice No', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Reverse Charge', 'Invoice Type', 'Ecommerce GSTIN', 'Rate', 'Applicable % of Tax Rate', 'Taxable Value', 'CESS Amount'];

            foreach ($invoices as  $invoice) {
                foreach ($invoice_items[$invoice->id] as $item) {
                    $invoicesArray[] = [$item->invoice_no, $item->invoice_date, $item->invoice_value, $item->place_of_supply, $item->reverse_charge, $item->invoice_type, $item->party_gst_no, $item->gst_rate, '', $item->taxable_value, $item->total_cess];
                }
            }

            Excel::create('B2B', function ($excel) use ($invoicesArray) {

                // Set the spreadsheet title, creator, and description
                $excel->setTitle('B2B Sale');
                $excel->setCreator('Admin')->setCompany('admin@test.com');
                $excel->setDescription('B2B Sale Sheet');

                // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function ($sheet) use ($invoicesArray) {
                    $sheet->fromArray($invoicesArray,  null, 'A1', false, false);
                });
            })->download('xlsx');
        } else {

            return view('sale.b2b', compact('invoices', 'invoice_items'));
        }
    }

    // public function b2b_sale(Request $request){
    //     $registered_parties = Party::where('user_id', Auth::user()->id)->where('registered', 1)->get();

    //     foreach ($registered_parties as $party) {

    //         if (isset($request->from) && isset($request->to)) {
    //             $from = strtotime($request->from);
    //             $to = strtotime($request->to);

    //             $from = date('Y-m-d', $from);
    //             $to = date('Y-m-d', $to);

    //             $invoices[$party->id] = Invoice::where('party_id', $party->id)->whereBetween('invoice_date', [$from, $to])->get();
    //             // $invoices = Invoice::where('party_id', $party->id)->whereBetween('invoice_date', [$from, $to])->get();
    //         } else {
    //             $invoices[$party->id] = Invoice::where('party_id', $party->id)->get();
    //             // $invoices = Invoice::where('party_id', $party->id)->get();
    //         }
    //         $gst_rates = array();
    //         $invoice_nos = array();
    //         $invoice_gsts = array();

    //         foreach ($invoices[$party->id] as $record) {

    //             $invoice_items = Invoice_Item::where( 'invoice_id', $record->id )->get();

    //             // $invoice_items = Invoice_Item::all();

    //             foreach( $invoice_items as $this_item ){
    //                 // echo $this_item->gst_rate . "\n";

    //                 $this_item->total = 0;

    //                 if( $this_item->gst_rate == null ){
    //                     $this_item->gst_rate = 0;
    //                 }

    //                 // array_push( $invoice_nos, $this_item->invoice_id );
    //                 // array_push( $gst_rates, $this_item->gst_rate );

    //                 // $invoice_gsts = array_combine( $invoice_nos, $gst_rates );

    //                 $invoice_gsts[ $this_item->invoice_id ][ $this_item->gst_rate ][] = $this_item->toArray();


    //                 foreach( $invoice_gsts[ $this_item->invoice_id ][ $this_item->gst_rate ] as $key => $value ){
    //                     $this_item->total += $value['gst'];
    //                 }

    //                 $gst_rate = $this_item->gst_rate;

    //                 echo $gst_rate;

    //                 die();

    //                 $record->items[ $gst_rate ] = $this_item->toArray();
    //             }

    //             $record->invoice_no = $record->invoice_no ? $record->invoice_no : $record->id;

    //             $record->invoice_date = $record->invoice_date;

    //             $record->type_of_bill = $record->type_of_bill;

    //             // $item = Item::find( $record->item_id );

    //             // $record->rate = (int) $item->gst;

    //             // $record->rate = $record->gst_rate;

    //             $record->gst_no = $party->gst;
    //             $state = State::find( $party->business_place );
    //             $record->place_of_supply = $state->state_code."-".$state->name;
    //             $record->reverse_charge = ( $party->reverse_charge ) ? $party->reverse_charge : 'No';
    //         }

    //         // for( $i = 0; $i < count( $invoice_nos ); $i++ ){
    //         //     $invoice_gsts[$invoice_nos[$i]][] = $gst_rates[$i];
    //         // }

    //         // echo "<pre>";
    //         // echo "invoice NOs ";
    //         // print_r( $invoice_nos );
    //         // echo "<br>";
    //         // echo "gst rates ";
    //         // print_r( $gst_rates );
    //         // echo "<br>";

    //         // print_r( $invoice_gsts );

    //         // return $invoice_items;

    //     }

    //     return $invoices;

    //     // die();

    //     if( isset($request->export_to_excel) && $request->export_to_excel == "yes" ) {
    //         $invoicesArray = [];

    //         // Define the Excel spreadsheet headers
    //         $invoicesArray[] = [ 'Invoice No', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Reverse Charge', 'Invoice Type', 'Ecommerce GSTIN', 'Rate', 'Applicable % of Tax Rate', 'Taxable Value', 'CESS Amount'] ;

    //         foreach($invoices as  $records){
    //             foreach( $records as $invoice ) {
    //                 $invoicesArray[] = [ $invoice->invoice_no, $invoice->invoice_date, $invoice->total_amount, $invoice->place_of_supply, $invoice->reverse_charge, $invoice->type_of_bill, $invoice->gst_no, $invoice->rate, '', $invoice->total_amount, $invoice->cess ];
    //             }
    //         }

    //         Excel::create('B2B', function($excel) use ( $invoicesArray ) {

    //             // Set the spreadsheet title, creator, and description
    //             $excel->setTitle('B2B Sale');
    //             $excel->setCreator('Admin')->setCompany('admin@test.com');
    //             $excel->setDescription('B2B Sale Sheet');

    //             // Build the spreadsheet, passing in the payments array
    //             $excel->sheet('sheet1', function($sheet) use ($invoicesArray) {
    //                 $sheet->fromArray( $invoicesArray,  null, 'A1', false, false);
    //             });
    //         })->download('xlsx');
    //     } else {

    //         return view('sale.b2b', compact('invoices'));
    //     }

    // }

    public function add_commission_to_all(Request $request)
    {
        $multiple_records = $request->multiple_record;
        $multiple_records_checked = $request->multiple_record_checked;

        // print_r($multiple_records_checked);

        $record_count = count($multiple_records);

        for ($i = 0; $i < $record_count; $i++) {

            if (isset($multiple_records_checked[$i])) {
                $invoice = Invoice::find($multiple_records[$i]);

                $invoice->commission = $invoice->total_amount * $request->commission / 100;

                $invoice->save();
            }
        }

        return redirect()->back()->with("success", "Commission added to all successfully");
    }


    public function create_sale_order()
    {
        $items = Item::where('user_id', Auth::user()->id)->get();
        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'debitor')->get();
        $groups = Group::where('user_id', Auth::user()->id)->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();


        $last_sale_order = User::find(Auth::user()->id)->saleOrder()->orderBy('id', 'desc')->first();

        $order_no = null;
        $myerrors = array();

        if (isset(auth()->user()->saleOrderSetting) && auth()->user()->saleOrderSetting->bill_no_type == 'auto') {

            if (isset($last_sale_order->token)) {
                $width = isset(auth()->user()->saleOrderSetting) ? auth()->user()->saleOrderSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['token_no'][] = 'Invalid Max-length provided. Please update your sale order settings.';
                        break;
                    case 1:
                        if ($last_sale_order->token > 9) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 2:
                        if ($last_sale_order->token > 99) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 3:
                        if ($last_sale_order->token > 999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 4:
                        if ($last_sale_order->token > 9999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 5:
                        if ($last_sale_order->token > 99999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 6:
                        if ($last_sale_order->token > 999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 7:
                        if ($last_sale_order->token > 9999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 8:
                        if ($last_sale_order->token > 99999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                    case 9:
                        if ($last_sale_order->token > 999999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for sale order no. Please update your sale order settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->saleOrderSetting) && auth()->user()->saleOrderSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->saleOrderSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->saleOrderSetting) && auth()->user()->saleOrderSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->saleOrderSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->saleOrderSetting) && auth()->user()->saleOrderSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->saleOrderSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['token_no'][] = 'Applicable date expired for your sale order series. Please update your sale order settings.';
            }

            // return $last_sale_order;

            if ($last_sale_order) {
                if (isset($last_sale_order->voucher_no_type) && $last_sale_order->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->saleOrderSetting->updated_at) > \Carbon\Carbon::parse($last_sale_order->created_at)) {
                        $order_no = isset(auth()->user()->saleOrderSetting->starting_no) ? auth()->user()->saleOrderSetting->starting_no - 1 : 0;
                    } else {
                        $order_no = ($last_sale_order->token == '' || $last_sale_order->token == null) ? 0 : $last_sale_order->token;
                    }
                } else {
                    $order_no = isset(auth()->user()->saleOrderSetting->starting_no) ? auth()->user()->saleOrderSetting->starting_no - 1 : 0;
                }
            } else {
                $order_no = isset(auth()->user()->saleOrderSetting->starting_no) ? auth()->user()->saleOrderSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('sale.order.create', compact('items', 'parties', 'groups', 'banks', 'user_profile', 'order_no'))->with('myerrors', $myerrors);
    }

    public function store_sale_order(Request $request)
    {
        $insertable_items = array();

        $party = Party::findOrFail($request->party);

        $items = $request->item;
        $quantities = $request->quantity;
        $units = $request->measuring_unit;
        $reference_name = $request->reference_name;
        $rates = $request->rate;
        $values = $request->value;
        //

        if ($request->sale_token != '') {
            $sale_token = $request->sale_token;
        } else {
            $sale_token = md5(rand(0, 99) . rand(0, 99) . rand(0, 99) . rand(0, 99) . rand(0, 99) . time());
        }

        $stime = strtotime(str_replace('/', '-', $request->sale_order_date));

        $sale_date = date('Y-m-d', $stime);

        for ($i = 0; $i < count($items); $i++) {
            $insertable_items[$i]['id'] = $items[$i];
            $insertable_items[$i]['qty'] = $quantities[$i];
            $insertable_items[$i]['units'] = $units[$i];
            $insertable_items[$i]['rate'] = $rates[$i];
            $insertable_items[$i]['value'] = $values[$i];
        }

        foreach ($insertable_items as $item) {
            $sale_order = new SaleOrder;

            if (isset(auth()->user()->saleOrderSetting) && isset(auth()->user()->saleOrderSetting->bill_no_type)) {
                $sale_order->voucher_no_type = auth()->user()->saleOrderSetting->bill_no_type;
            } else {
                $sale_order->voucher_no_type = 'manual';
            }

            $sale_order->qty = $item['qty'];
            $sale_order->unit = isset($item['unit']) ? $item['unit'] : '';
            $sale_order->rate = $item['rate'];
            $sale_order->value = $item['value'];
            $sale_order->party_id = $request->party;
            $sale_order->item_id = $item['id'];
            $sale_order->date = date('Y-m-d', strtotime(str_replace('/', '-', $sale_date)));
            $sale_order->token = $sale_token;
            $sale_order->reference_name = $reference_name;
            if ($request->filled('cashed_amount')) {
                $sale_order->cash_amount = $request->cashed_amount;
            }
            if ($request->filled('banked_amount')) {
                $sale_order->bank_id = $request->bank;
                $sale_order->bank_amount = $request->banked_amount;
                $sale_order->bank_cheque = $request->bank_cheque;

                //$sale_order->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            }
            if ($request->filled('posed_amount')) {
                $sale_order->pos_bank_id = $request->pos_bank;
                $sale_order->pos_amount = $request->posed_amount;

                //$sale_order->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            }

            if ($request->filled('total_amount')) {
                $sale_order->total_amount = $request->total_amount;
            } else {
                $sale_order->total_amount = 0;
            }

            if ($request->filled('amount_received')) {
                $sale_order->amount_received = $request->amount_received;
            } else {
                $sale_order->amount_received = 0;
            }

            if ($request->filled('amount_remaining')) {
                $sale_order->amount_remaining = $request->amount_remaining;
            } else {
                $sale_order->amount_remaining = 0;
            }

            $sale_order->narration = $request->narration;

            $sale_order->user_id = Auth::user()->id;

            $sale_order->save();
        }

        if ($request->has("submit_type") && $request->submit_type == "save") {
            return redirect()->back()->with('success', 'Sale order created successfully');
        }

        if ($request->has("submit_type") && $request->submit_type == "save_and_print") {
            return redirect(route('print.sale.order', $sale_order->token));
        }

        if ($request->has("submit_type") && $request->submit_type == "save_and_send") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendSaleOrder($purchase_order->token));
            }
            return redirect()->back()->with('success', 'Sale order created successfully');
        }

        //return redirect()->back()->with('success', 'Sale Order Saved with ' . $sale_order->token . '. Please save this number for future reference.');
    }

    public function view_sale_order($token)
    {

        $sale_records = SaleOrder::where('token', $token)->where('user_id', Auth::user()->id)->get();
        $party_name = '';
        $sale_order = '';

        foreach ($sale_records as $record) {
            $party = Party::find($record->party_id);
            $item = Item::find($record->item_id);

            $party_name = $party->name;
            $record->item_name = $item->name;

            $sale_order = $record->token;
        }

        return view('sale.order.view', compact('sale_records', 'party_name', 'sale_order'));
    }

    public function create_sale_from_order($sale_order_no)
    {
        $sale_orders = SaleOrder::where('token', $sale_order_no)->where('user_id', Auth::user()->id)->get();

        $party_id = SaleOrder::where('token', $sale_order_no)->where('user_id', Auth::user()->id)->value('party_id');

        $reference_name = SaleOrder::where('token', $sale_order_no)->where('user_id', Auth::user()->id)->value('reference_name');

        $involved_party = Party::find($party_id);

        $state = State::find($involved_party->billing_state);

        $involved_party->billing_state = $state->name ?? '';

        $parties = Party::where('user_id', Auth::user()->id)->get();
        $transporters = Transporter::where('user_id', Auth::user()->id)->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();
        $groups = Group::all();
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $invoice = User::findOrFail(Auth::user()->id)->invoices()->orderBy('id', 'desc')->first();

        $myerrors = collect();

        // return $invoice;
        if ($invoice) {
            $invoice_no = $invoice->invoice_no;
        } else {
            $invoice_no = "0";
        }

        foreach ($sale_orders as $order) {
            $item = Item::find($order->item_id);

            $order->item_name = $item->name;
            $order->hsc_code = $item->hsc_code;
            $order->sac_code = $item->sac_code;
            $order->gst_percent = $item->gst;
            $order->measuring_unit = $item->measuring_unit ?? null;
            $order->alternate_unit = $item->alternate_measuring_unit ?? null;
            $order->compound_unit = $item->compound_measuring_unit ?? null;
        }

        $type_of_payment = 'no_payment';
        $cash_payment = 0;
        $bank_payment = 0;
        $pos_payment = 0;
        $discount_payment = 0;


        $cash = $sale_orders->first()->cash_amount ?? false;
        $bank = $sale_orders->first()->bank_amount ?? false;
        $pos = $sale_orders->first()->pos_amount ?? false;
        $discount = false;

        // return $cash;

        //here
        if ($cash && $bank && $pos && $discount) {
            $type_of_payment = 'combined';
        } else if ($cash && $bank && $pos) {
            $type_of_payment = 'cash+bank+pos';
        } else if ($cash && $bank && $discount) {
            $type_of_payment = 'cash+bank+discount';
        } else if ($cash && $discount && $pos) {
            $type_of_payment = 'cash+pos+discount';
        } else if ($discount && $bank && $pos) {
            $type_of_payment = 'bank+pos+discount';
        } else if ($cash && $bank) {
            $type_of_payment = 'bank+cash';
        } else if ($cash && $pos) {
            $type_of_payment = 'pos+cash';
        } else if ($cash && $discount) {
            $type_of_payment = 'cash+discount';
        } else if ($bank && $pos) {
            $type_of_payment = 'pos+bank';
        } else if ($bank && $discount) {
            $type_of_payment = 'discount';
        } else if ($pos && $discount) {
            $type_of_payment = 'pos+discount';
        } else if ($cash) {
            $type_of_payment = 'cash';
        } else if ($bank) {
            $type_of_payment = 'bank';
        } else if ($pos) {
            $type_of_payment = 'pos';
        } else if ($discount) {
            $type_of_payment = 'discount';
        }

        // return $reference_name;

        // return $type_of_payment;

        return view('sale.create', compact('sale_orders', 'groups', 'parties', 'sale_order_no', 'transporters', 'banks', 'involved_party', 'reference_name', 'user_profile', 'invoice_no', 'type_of_payment', 'myerrors'));
    }

    public function view_all_sale_order()
    {

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->groupBy('token')->get();
        // $sale_orders = SaleOrder::select('*')->where('user_id', Auth::user()->id)->distinct()->get();

        foreach ($sale_orders as $order) {
            $party = Party::find($order->party_id);
            $order->party_name = $party->name;

            $item = Item::find($order->item_id);
            $order->item_name = isset($item) ? $item->name : '';
        }

        // return $sale_orders;

        return view('sale.order.index', compact('sale_orders'));
    }


    public function update_sale_order_status(Request $request, $id)
    {

        $sale_order = SaleOrder::findOrFail($id);

        if (strtolower($request->type) == 'cancel') {
            $sale_order->status = 0;
        } else if (strtolower($request->type) == 'activate') {
            $sale_order->status = 1;
        }

        $sale_order->save();

        return redirect()->back()->with('success', 'Status updated successfully');
    }


    public function edit_sale_order($sale_order_no)
    {
        $sale_orders = SaleOrder::where('token', $sale_order_no)->where('user_id', Auth::user()->id)->get();
        // $sale_order = SaleOrder::find($sale_order_no);
        $items = Item::where('user_id', Auth::user()->id)->get();
        $parties = Party::where('user_id', Auth::user()->id)->get();
        $groups = Group::all();

        $banks = Bank::where('user_id', Auth::user()->id)->get();
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        // return $sale_order;


        $party_id = '';
        $date = '';
        $reference_name = '';
        $total_amount = 0;
        $amount_received = 0;
        $amount_remaining = 0;

        foreach ($sale_orders as $sale_order) {
            $currentItem = Item::findOrFail($sale_order->item_id);

            $sale_order->item_name = $currentItem->name;
            $sale_order->base_unit = $currentItem->measuring_unit ?? null;
            $sale_order->alternate_unit = $currentItem->alternate_measuring_unit ?? null;
            $sale_order->compound_unit = $currentItem->compound_measuring_unit ?? null;

            $total_amount = $sale_order->total_amount;
            $amount_received = $sale_order->amount_received;
            $amount_remaining = $sale_order->amount_remaining;

            $party_id = $sale_order->party_id;
            $date = $sale_order->date;
            $reference_name = $sale_order->reference_name;
        }

        return view('sale.order.edit', compact('items', 'parties', 'groups', 'sale_orders', 'party_id', 'date', 'reference_name', 'banks', 'user_profile', 'sale_order_no', 'total_amount', 'amount_received', 'amount_remaining'));
    }

    public function update_sale_order(Request $request)
    {

        // return $request->all();

        $sale_order = SaleOrder::find($request->id);

        $sale_order->item_id = $request->item_id;
        $sale_order->qty = $request->qty;
        $sale_order->unit = $request->unit;
        $sale_order->rate = $request->rate;
        $sale_order->value = $request->value;

        if ($sale_order->save()) {
            echo "success";
        } else {
            echo "fail";
        }
    }

    public function update_sale_order_remains(Request $request, $sale_order_no)
    {

        $sale_orders = SaleOrder::where('token', $sale_order_no)->get();

        foreach ($sale_orders as $sale_order) {

            $order = SaleOrder::find($sale_order->id);

            if ($request->has('selected_party_id')) {
                $order->party_id = $request->selected_party_id;
            }

            if ($request->has('selected_order_date')) {
                $order->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->selected_order_date)));
            }

            if ($request->has('selected_reference')) {
                $order->reference_name = $request->selected_reference;
            }

            if ($request->has('cashed_amount') && $request->cashed_amount > 0) {
                $order->cash_amount = $request->cashed_amount;
            } else {
                $order->cash_amount = 0;
            }

            if ($request->has('banked_amount') && $request->banked_amount > 0) {
                $order->bank_id = $request->bank;
                $order->bank_amount = $request->banked_amount;
                $order->bank_cheque = $request->bank_cheque;

                $order->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else {
                $order->bank_id = null;
                $order->bank_amount = 0;
                $order->bank_cheque = null;

                $order->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            }

            if ($request->has('posed_amount') && $request->posed_amount > 0) {
                $order->pos_amount = $request->posed_amount;

                $order->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else {
                $order->pos_amount = 0;

                $order->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            }

            if ($request->has('amount_received') && $request->amount_received > 0) {
                $order->amount_received = $request->amount_received;
            } else {
                $order->amount_received = 0;
            }

            if ($request->has('total_amount') && $request->total_amount > 0) {
                $order->total_amount = $request->total_amount;
            } else {
                $order->total_amount = 0;
            }

            if ($request->has('amount_remaining') && $request->amount_remaining > 0) {
                $order->amount_remaining = $request->amount_remaining;
            } else {
                $order->amount_remaining = 0;
            }

            if ($request->has('narration')) {
                $order->narration = $request->narration;
            }

            $order->save();
        }

        return redirect()->back()->with('success', 'Sale Order updated successfully!');
    }

    public function store_sale_order_single_row(Request $request)
    {


        // return $request->all();


        $stime = strtotime($request->date);

        $sale_date = date('Y-m-d', $stime);


        $sale_order = new SaleOrder;

        $sale_order->party_id = $request->party_id;

        $sale_order->item_id = $request->item_id;

        $sale_order->qty = $request->qty;

        $sale_order->date = $sale_date;

        $sale_order->token = $request->token;

        $sale_order->reference_name = $request->reference;

        $sale_order->user_id = Auth::user()->id;

        if ($sale_order->save()) {
            echo "success";
        } else {
            echo "fail";
        }
    }

    private function validate_payment_voucher_no($voucher, $from_date, $to_date) {
        $is_valid = true;
        $party_payment = User::find(auth()->user()->id)->partyRemainingAmounts()->where('voucher_no', $voucher)->whereBetween('payment_date', [$from_date, $to_date])->get();
        $sale_payment = User::find(auth()->user()->id)->saleRemainingAmounts()->where('voucher_no', $voucher)->whereBetween('payment_date', [$from_date, $to_date])->get();

        if(count($party_payment) > 0 || count($sale_payment) > 0) {
            $is_valid = false;
        }

        return $is_valid;
    }

    public function add_pending_payment_to_party(Request $request)
    {

        $pending_payment = new PartyPendingPaymentAccount;

        $amount = 0;

        $pending_payment->party_id = $request->party_id;
        $pending_payment->pending_balance = $request->pending_balance;
        $pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

        $is_voucher_valid = $this->validate_payment_voucher_no($request->voucher_no, auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to);

        if(!$is_voucher_valid) {
            return redirect()->back()->with('failure', "Please provide unique voucher no");
        }

        if ($request->has('voucher_no')) {
            $pending_payment->voucher_no = $request->voucher_no;
        }

        if (isset(auth()->user()->receiptSetting) && isset(auth()->user()->receiptSetting->bill_no_type)) {
            $pending_payment->voucher_no_type = auth()->user()->receiptSetting->bill_no_type;
        } else {
            $pending_payment->voucher_no_type = 'manual';
        }

        $pending_payment->remarks = $request->remarks;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('discount', $type_of_payment);

            //array_search returns false or array index (if found). 0 can be an array index and it also acts as false in programming so adding 1 to it, so that it becomes 0+1 ie 1 which is true
            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {
                $pending_payment->type_of_payment = 'combined';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $pending_payment->type_of_payment = 'bank+pos+discount';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $pending_payment->type_of_payment = 'cash+pos+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->posed_amount + $request->discounted_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $pending_payment->type_of_payment = 'bank+cash+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $pending_payment->type_of_payment = 'bank+discount';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount + $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $pending_payment->type_of_payment = 'cash+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->discounted_amount;

                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $pending_payment->type_of_payment = 'pos+discount';

                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->posed_amount + $request->discounted_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $pending_payment->type_of_payment = 'discount';

                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->discounted_amount;

                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $pending_payment->type_of_payment = 'cash+bank+pos';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $pending_payment->type_of_payment = 'bank+cash';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;

                $amount = $request->cashed_amount + $request->banked_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $pending_payment->type_of_payment = 'pos+cash';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->cashed_amount + $request->posed_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $pending_payment->type_of_payment = 'pos+bank';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->banked_amount + $request->posed_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $pending_payment->type_of_payment = 'bank';

                $pending_payment->bank_payment = $request->banked_amount;

                $amount = $request->banked_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $pending_payment->type_of_payment = 'pos';

                $pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->posed_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $pending_payment->type_of_payment = 'cash';

                $pending_payment->cash_payment = $request->cashed_amount;

                $amount = $request->cashed_amount;
            }
        } else {
            $pending_payment->type_of_payment = 'no_payment';
        }


        $pending_payment->amount = $amount;

        if ($request->has('tds_income_tax_amount') && !empty($request->tds_income_tax_amount)) {
            $pending_payment->amount += $request->tds_income_tax_amount;
        }

        if ($request->has('tds_gst') && !empty($request->tds_gst)) {
            $pending_payment->amount += $request->tds_gst;
        }

        if ($request->has('tcs_income_tax') && !empty($request->tcs_income_tax)) {
            $pending_payment->amount += $request->tcs_income_tax;
        }

        if ($request->has('tcs_gst') && !empty($request->tcs_gst)) {
            $pending_payment->amount += $request->tcs_gst;
        }

        $pending_payment->tds_income_tax_amount = $request->tds_income_tax_amount;
        $pending_payment->tds_gst_amount = $request->tds_gst;
        $pending_payment->tcs_income_tax_amount = $request->tcs_income_tax;
        $pending_payment->tcs_gst_amount = $request->tcs_gst;

        $pending_payment->tds_income_tax_checked = $request->tds_income_tax_checked;
        $pending_payment->tds_gst_checked = $request->tds_gst_checked;
        $pending_payment->tcs_income_tax_checked = $request->tcs_income_tax_checked;
        $pending_payment->tcs_gst_checked = $request->tcs_gst_checked;

        $pending_payment->type = "sale";

        if ($pending_payment->save()) {
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function suggest_sale_order_no(Request $request)
    {

        $suggestion = array(
            md5(strtotime("+1 minutes") . rand() . rand() . rand() . rand()),
            md5(strtotime("+2 minutes") . rand() . rand() . rand() . rand()),
            md5(strtotime("+3 minutes") . rand() . rand() . rand() . rand()),
            md5(strtotime("+4 minutes") . rand() . rand() . rand() . rand()),
            md5(strtotime("+5 minutes") . rand() . rand() . rand() . rand())
        );

        return response()->json($suggestion);
    }

    public function find_sale_order_no(Request $request)
    {

        $sale_orders = SaleOrder::where('token', 'like', $request->key_to_search . '%')->where('user_id', auth()->user()->id)->get();

        return response()->json($sale_orders);
    }

    public function update_sale_bill_note(Request $request)
    {

        $billNote = BillNote::updateOrCreate(
            [
                'bill_no' => $request->bill_no,
                'type' => 'sale'
            ],
            [
                'taxable_value_difference' => $request->taxable_value_difference,
                'gst_value_difference' => $request->gst_value_difference,
                'reason' => $request->reason
            ]
        );

        if ($billNote) {
            return redirect()->back()->with('success', 'Note saved/updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save/update note');
        }
    }


    public function sale_bill_note($bill_no)
    {

        $bill_note = new BillNote;

        $bill_note->bill_no = $bill_no;

        $bill_note->taxable_value_difference = 0;

        $bill_note->gst_value_difference = 0;

        $bill_note->type = 'sale';

        if ($bill_note->save()) {
            return redirect()->back()->with('success', 'Note created successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to create note');
        }
    }

    public function pending_payment_report(Request $request)
    {
        // return $request->all();

        if (isset($request->from_date) && isset($request->to_date)) {

            $invoices = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$request->from_date, $request->to_date])->get();

            foreach ($invoices as $invoice) {
                $party = Party::find($invoice->party_id);

                $invoice->party_name = $party->name;

                $remaining_amount_data = SaleRemainingAmount::where('invoice_id', $invoice->id)->orderBy('id', 'desc')->first();

                $invoice->remaining_amount = $remaining_amount_data;
            }

            return view('report.pending_payment', compact('invoices'));
        } else {

            if (isset($request->party)) {
                $parties = Party::where('user_id', Auth::user()->id)->where('name', $request->party)->get();
            } else {
                $parties = Party::where('user_id', Auth::user()->id)->get();
            }

            foreach ($parties as $party) {
                $sale_record[$party->id] = Invoice::where('party_id', $party->id)->where('type_of_bill', 'regular')->get();

                foreach ($sale_record[$party->id] as $record) {
                    $remaining_amount_data = SaleRemainingAmount::where('invoice_id', $record->id)->orderBy('id', 'desc')->first();

                    $record->remaining_amount = $remaining_amount_data;
                }
            }

            return view('report.pending_payment', compact('sale_record', 'parties'));
        }


        // echo json_encode($sale_record);

        // return response()->json($sale_record);

        // return $sale_record;


    }

    public function show_all_invoices()
    {
        $invoices = User::find(Auth::user()->id)->invoices()->get();

        return view('sale.show_all_invoices', compact('invoices'));
    }

    public function edit_invoice_form($id)
    {
        $invoice = Invoice::findOrFail($id);

        $banks = User::find(Auth::user()->id)->banks()->get();

        $payment = SaleRemainingAmount::where('invoice_id', $id)->orderBy('id', 'desc')->first();

        // return $invoice->items;
        return view('sale.edit_invoice_form', compact('invoice', 'banks', 'payment'));
    }

    public function update_invoice_item(Request $request)
    {
        $invoice_item = Invoice_Item::find($request->source);

        $item_prev_qty = $invoice_item->item_qty;
        $item_prev_free_qty = $invoice_item->free_qty;
        $item_prev_price = $invoice_item->item_price;
        $item_prev_gst = $invoice_item->gst;
        $item_prev_cess = $invoice_item->cess;
        $item_prev_discount = $invoice_item->discount;
        $item_prev_measuring_unit = $invoice_item->item_measuring_unit;

        if ($request->lump_sump == 1) {
            $invoice = Invoice::find($invoice_item->invoice_id);
            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::find($invoice->party_id);

            $invoice->item_total_amount = $invoice->item_total_amount - $item_prev_price + $request->amount;

            $invoice->gst = $invoice->gst - $item_prev_gst + $request->calculated_tax;

            $invoice->cess = $invoice->cess - $item_prev_cess + $request->cess;

            // $invoice->amount_before_round_off = ($invoice->amount_before_round_off - ($item_prev_price + $item_prev_gst + $item_prev_cess) + ($request->amount + $request->calculated_tax + $request->cess));

            $invoice->amount_before_round_off = $invoice->item_total_amount + $invoice->gst + $invoice->cess + $invoice->tcs;

            // $invoice->total_amount = $invoice->total_amount - $item_prev_price + $request->amount;

            // $invoice->amount_remaining = ($invoice->amount_remaining - ($item_prev_price + $item_prev_gst + $item_prev_cess) + ($request->amount + $request->calculated_tax + $request->cess));

            if(auth()->user()->roundOffSetting->sale_round_off_to == "upward") {
                $invoice->total_amount = ceil($invoice->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->sale_round_off_to == "downward") {
                $invoice->total_amount = floor($invoice->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->sale_round_off_to == "normal") {
                $invoice->total_amount = round($invoice->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->sale_round_off_to == "manual") {
                $invoice->total_amount = $invoice->amount_before_round_off;
            }

            $round_off_difference = $invoice->total_amount - $invoice->amount_before_round_off;

            if($round_off_difference >= 0) {
                $invoice->round_off_operation = '+';
            } else {
                $invoice->round_off_operation = '-';
            }

            $invoice->round_offed = abs($round_off_difference);

            $invoice_item->item_price = $request->rate;
            $invoice_item->item_total = $request->amount;
            $invoice_item->gst = $request->calculated_tax;
            $invoice_item->cess = $request->cess;

            /** ------------updating invoice cgst/sgst or ugst or igst------------ */
            if ($user_profile->place_of_business == $party->business_place) {
                $invoice->cgst = $invoice->gst / 2;
                $invoice->sgst = $invoice->gst / 2;
            } else {
                $invoice->igst = $invoice->gst;
            }

            $invoice->save();
            $invoice_item->save();
        } else {

            $item_prev_discount = $item_prev_discount ? $item_prev_discount : 0;
            $request->discount = $request->discount ? $request->discount : 0;

            // $item_prev_amount = ($item_prev_qty * $item_prev_price) - ($item_prev_qty * $item_prev_price * $item_prev_discount / 100);
            $item_prev_amount = $invoice_item->item_total;
            $item_prev_total_amount = ($item_prev_amount + $item_prev_gst);


            $item_new_amount = $request->amount;

            $item_new_total_amount = $item_new_amount + $request->calculated_tax;

            $invoice = Invoice::find($invoice_item->invoice_id);
            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::find($invoice->party_id);
            
            $invoice_discount = $invoice->total_discount ?? 0;
            $invoice_round_offed = $invoice->round_offed ?? 0;

            // $invoice->invoice_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->invoice_date)));
            // $invoice->due_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->due_date)));

            
            
            $invoice->gst = $invoice->gst - $item_prev_gst + $request->calculated_tax;
            $invoice->cess = $invoice->cess - $item_prev_cess + $request->cess;
            $invoice->item_total_amount = $invoice->item_total_amount - $item_prev_amount + $item_new_amount;
            $invoice->amount_before_round_off = $invoice->item_total_amount + $invoice->gst + $invoice->cess + $invoice->tcs;

            // $invoice->total_amount = $invoice->total_amount - $item_prev_total_amount - $item_prev_gst - $item_prev_cess + $item_new_total_amount - $invoice_discount + $request->calculated_tax + $request->cess;

            if(auth()->user()->roundOffSetting->sale_round_off_to == "upward") {
                $invoice->total_amount = ceil($invoice->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->sale_round_off_to == "downward") {
                $invoice->total_amount = floor($invoice->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->sale_round_off_to == "normal") {
                $invoice->total_amount = round($invoice->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->sale_round_off_to == "manual") {
                $invoice->total_amount = $invoice->amount_before_round_off;
            }

            $round_off_difference = $invoice->total_amount - $invoice->amount_before_round_off;

            if($round_off_difference >= 0) {
                $invoice->round_off_operation = '+';
            } else {
                $invoice->round_off_operation = '-';
            }

            $invoice->round_offed = abs($round_off_difference);

            $invoice->amount_remaining = $invoice->total_amount - $invoice->amount_paid;

            /** ------------updating invoice cgst/sgst or ugst or igst------------ */
            if ($user_profile->place_of_business == $party->business_place) {
                $invoice->cgst = $invoice->gst / 2;
                $invoice->sgst = $invoice->gst / 2;
            }
            // else if ($user_profile->place_of_business != $party->business_place && ($party->business_place == 4 || $party->business_place == 7 || $party->business_place == 25 || $party->business_place == 26 || $party->business_place == 31 || $party->business_place == 34 || $party->business_place == 35)) {
            //     $invoice->ugst = $invoice->gst;
            // } 
            else {
                $invoice->igst = $invoice->gst;
            }

            $invoice->save();

            $sale_remaining_amounts = SaleRemainingAmount::where('invoice_id', $invoice->id)->get();

            foreach ($sale_remaining_amounts as $remaining_amount) {
                $remaining = SaleRemainingAmount::find($remaining_amount->id);
                $remaining->total_amount = $invoice->total_amount;
                $remaining->amount_remaining = $remaining->total_amount - $remaining->amount_paid;
                $remaining->save();
            }

            $item = Item::find($invoice_item->item_id);

            if ($item_prev_measuring_unit == $item->measuring_unit) {
                $item_prev_qty = $item_prev_qty;
            } else if ($item_prev_measuring_unit == $item->alternate_measuring_unit) {
                $item_prev_qty = $item_prev_qty * $item->conversion_of_alternate_to_base_unit_value;
            } else if ($item_prev_measuring_unit == $item->compound_measuring_unit) {
                $item_prev_qty = $item_prev_qty * $item->conversion_of_alternate_to_base_unit_value * $item->conversion_of_compound_to_alternate_unit_value;
            }

            
            $request_qty = $request->qty;
            if ($item->alternate_measuring_unit == $request->measuring_unit) {
                $request_qty = $request->qty * $item->conversion_of_alternate_to_base_unit_value;
            } else if ($item->compound_measuring_unit == $request->measuring_unit) {
                $request_qty = $request->qty * $item->conversion_of_alternate_to_base_unit_value * $item->conversion_of_compound_to_alternate_unit_value;
            }

            $item->qty = $item->qty - $item_prev_qty - $item_prev_free_qty + $request_qty + $request->free_qty;

            $item->save();

            $invoice_item->item_qty = $request->qty;
            $invoice_item->free_qty = $request->free_qty;
            $invoice_item->item_measuring_unit = $request->measuring_unit;
            $invoice_item->item_price = $request->rate;
            $invoice_item->item_total = $request->amount;
            $invoice_item->item_tax_type = $request->item_tax_inclusive;
            $invoice_item->gst = $request->calculated_tax;
            $invoice_item->cess = $request->cess;
            $invoice_item->discount_type = $request->item_discount_type;
            $invoice_item->discount = $request->discount;

            $invoice_item->save();
        }

        return redirect()->back()->with('success', 'Item updated successfully');
    }

    public function update_invoice(Request $request, Invoice $invoice)
    {

        // $this->validate($request,[
        //     'amount_paid' => 'required',
        //     'amount_remaining' => 'required'
        // ]);


        // $invoice = Invoice::findOrFail($request->invoice_id);

        $sale_remaining_amount = SaleRemainingAmount::where('invoice_id', $invoice->id)->first();

        // if( $request->amount_type == 'inclusive_of_tax' ){
        //     $amount_type = 'inclusive';
        // }
        // else if( $request->amount_type == 'exclusive_of_tax' ) {
        //     $amount_type = 'exclusive';
        // }

        // $invoice->amount_type = $amount_type;

        if ($request->filled('invoice_no')) {
            $invoice->invoice_no = $request->invoice_no;
        }

        $invoice->invoice_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->invoice_date)));
        $invoice->due_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->due_date)));

        if ($request->filled('buyer_name')) {
            $invoice->buyer_name = $request->buyer_name;
        }

        if ($request->filled('reference_name')) {
            $invoice->reference_name = $request->reference_name;
        }

        if ($request->filled('shipping_bill_no')) {
            $invoice->shipping_bill_no = $request->shipping_bill_no;
        } else {
            $invoice->shipping_bill_no = null;
        }

        if ($request->filled('date_of_shipping')) {
            $invoice->date_of_shipping = date('Y-m-d', strtotime(str_replace('/', '-', $request->date_of_shipping)));
        }

        if ($request->filled('code_of_shipping_port')) {
            $invoice->code_of_shipping_port = $request->code_of_shipping_port;
        }

        if ($request->filled('conversion_rate')) {
            $invoice->conversion_rate = $request->conversion_rate;
        }

        if ($request->filled('currency_symbol')) {
            $invoice->currency_symbol = $request->currency_symbol;
        }

        if ($request->filled('export_type')) {
            $invoice->export_type = $request->export_type;
        }

        if ($request->filled('consignee_info')) {
            $invoice->consignee_info = $request->consignee_info;
        }

        if ($request->filled('consignor_info')) {
            $invoice->consignor_info = $request->consignor_info;
        }

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('cash_discount', $type_of_payment);

            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {

                $invoice->type_of_payment = 'combined';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->pos_bank_id = $request->pos_bank;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'combined';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {

                $invoice->type_of_payment = 'cash+bank+pos';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->type_of_payment = 'cash+bank+pos';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank && $discount) {
                $invoice->type_of_payment = 'cash+bank+discount';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'cash+bank+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount && $pos) {
                $invoice->type_of_payment = 'cash+pos+discount';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->pos_bank_id = $request->pos_bank;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'cash+pos+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount && $bank && $pos) {
                $invoice->type_of_payment = 'bank+pos+discount';

                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->pos_bank_id = $request->pos_bank;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'bank+pos+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank) {

                $invoice->type_of_payment = 'bank+cash';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->bank_payment = $request->banked_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->type_of_payment = 'bank+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $invoice->type_of_payment = 'pos+cash';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->pos_payment = $request->posed_amount;

                $invoice->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->type_of_payment = 'pos+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $discount) {

                $invoice->type_of_payment = 'cash+discount';

                $invoice->cash_payment = $request->cashed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'cash+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {
                $invoice->type_of_payment = 'pos+bank';

                $invoice->bank_payment = $request->banked_amount;
                $invoice->pos_payment = $request->posed_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;
                $invoice->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $discount) {

                $invoice->type_of_payment = 'discount';

                $invoice->bank_payment = $request->banked_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $invoice->type_of_payment = 'pos+discount';

                $invoice->pos_payment = $request->posed_amount;
                $invoice->discount_payment = $request->discount_amount;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $invoice->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->type_of_payment = 'pos+discount';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash) {
                $invoice->type_of_payment = 'cash';

                $invoice->cash_payment = $request->cashed_amount;

                $sale_remaining_amount->type_of_payment = 'cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $invoice->type_of_payment = 'bank';

                $invoice->bank_payment = $request->banked_amount;

                $invoice->bank_id = $request->bank;
                $invoice->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->type_of_payment = 'bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $invoice->type_of_payment = 'pos';

                $invoice->pos_payment = $request->posed_amount;

                $invoice->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->type_of_payment = 'pos';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount) {
                $invoice->type_of_payment = 'discount';

                $invoice->discount_payment = $request->discount_amount;

                $invoice->discount_type = $request->discount_type;
                $invoice->discount_figure = $request->discount_figure;

                $sale_remaining_amount->type_of_payment = 'discount';
                $sale_remaining_amount->discount_payment = $request->discount_amount;
                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            }
        } else {
            $invoice->type_of_payment = 'no_payment';
            $sale_remaining_amount->type_of_payment = 'no_payment';
        }


        if ($request->filled('tcs')) {
            $invoice->tcs = $request->tcs;
        }

        $invoice->item_total_amount = $request->item_total_amount;
        $invoice->order_no = $request->sale_order_no;
        $invoice->gst = $request->total_gst_amounted;
        $invoice->cess = $request->total_cess_amounted;
        $invoice->amount_before_round_off = $request->total_amount_before_discount;
        $invoice->total_discount = $request->total_discount;
        $invoice->round_off_operation = $request->round_off_operation;
        $invoice->round_offed = $request->round_offed;
        $invoice->total_amount = $request->total_amount;
        $invoice->amount_paid = $request->amount_paid;
        $invoice->amount_remaining = $request->amount_remaining;
        $invoice->remark = $request->overall_remark;

        $sale_remaining_amount->amount_paid = $request->amount_paid;
        $sale_remaining_amount->amount_remaining = $request->amount_remaining;


        $invoice->save();
        $sale_remaining_amount->save();

        return redirect()->back()->with('success', 'Invoice updated successfully!');
    }

    public function update_invoice_individual_column(Request $request)
    {
        $invoice = Invoice::find($request->id);
        if($request->type == 'date_of_shipping') {
            $invoice->date_of_shipping = date('Y-m-d', strtotime(str_replace('/', '-', $request->value)));
        } else {
            $invoice->{$request->type} = $request->value;
        }
        $invoice->save();
    }

    public function edit_sale_pending_payment_form($id)
    {
        $sale_remaining_amount = SaleRemainingAmount::find($id);

        $associated_party = Party::find($sale_remaining_amount->party_id);

        $invoice = Invoice::find($sale_remaining_amount->invoice_id);

        $invoice_id = $invoice->invoice_no ? $invoice->invoice_no : $invoice->id;

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('sale.edit_sale_pending_payment', compact('sale_remaining_amount', 'associated_party', 'invoice_id', 'banks'));
    }

    public function update_sale_pending_payment(Request $request, $id)
    {
        $sale_remaining_amount = SaleRemainingAmount::find($id);

        $sale_remaining_amount->amount_paid = $request->amount_paid;
        $sale_remaining_amount->amount_remaining = $request->amount_remaining;
        $sale_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));
        $sale_remaining_amount->voucher_no = $request->voucher_no;

        if ($request->has('tds_income_tax')) {
            $sale_remaining_amount->tds_income_tax = $request->tds_income_tax;
        }
        if ($request->has('tds_gst')) {
            $sale_remaining_amount->tds_gst = $request->tds_gst;
        }
        if ($request->has('tcs_income_tax')) {
            $sale_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
        }
        if ($request->has('tcs_gst')) {
            $sale_remaining_amount->tcs_gst = $request->tcs_gst;
        }


        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('cash_discount', $type_of_payment);

            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {

                $sale_remaining_amount->type_of_payment = 'combined';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {

                $sale_remaining_amount->type_of_payment = 'cash+bank+pos';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank && $discount) {
                $sale_remaining_amount->type_of_payment = 'cash+bank+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount && $pos) {
                $sale_remaining_amount->type_of_payment = 'cash+pos+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($discount && $bank && $pos) {
                $sale_remaining_amount->type_of_payment = 'bank+pos+discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank) {

                $sale_remaining_amount->type_of_payment = 'bank+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $sale_remaining_amount->type_of_payment = 'pos+cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $discount) {

                $sale_remaining_amount->type_of_payment = 'cash+discount';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {
                $sale_remaining_amount->type_of_payment = 'pos+bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $discount) {

                $sale_remaining_amount->type_of_payment = 'discount';

                $sale_remaining_amount->bank_payment = $request->banked_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $sale_remaining_amount->type_of_payment = 'pos+discount';

                $sale_remaining_amount->pos_payment = $request->posed_amount;
                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $sale_remaining_amount->type_of_payment = 'cash';

                $sale_remaining_amount->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $sale_remaining_amount->type_of_payment = 'bank';

                $sale_remaining_amount->bank_payment = $request->banked_amount;

                $sale_remaining_amount->bank_id = $request->bank;
                $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $sale_remaining_amount->type_of_payment = 'pos';

                $sale_remaining_amount->pos_payment = $request->posed_amount;

                $sale_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount) {
                $sale_remaining_amount->type_of_payment = 'discount';

                $sale_remaining_amount->discount_payment = $request->discount_amount;

                $sale_remaining_amount->discount_type = $request->discount_type;
                $sale_remaining_amount->discount_figure = $request->discount_figure;
            }
        } else {
            $sale_remaining_amount->type_of_payment = 'no_payment';
        }

        if ($sale_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Payment updated successfully!!');
        } else {
            return redirect()->back()->with('failure', 'Failed to update payment');
        }
    }

    public function edit_sale_party_pending_payment_form($id)
    {
        $party_pending_payment = PartyPendingPaymentAccount::find($id);

        $associated_party = Party::find($party_pending_payment->party_id);

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('sale.edit_sale_party_pending_payment', compact('party_pending_payment', 'associated_party', 'banks'));
    }

    public function update_sale_party_pending_payment(Request $request, $id)
    {
        // return $request->all();

        $party_pending_payment = PartyPendingPaymentAccount::find($id);

        // $party_pending_payment->amount = $request->amount;

        $party_pending_payment->voucher_no = $request->voucher_no;
        $party_pending_payment->remarks = $request->remarks;
        $party_pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

        if ($request->has('tds_income_tax_amount')) {
            $party_pending_payment->tds_income_tax_amount = $request->tds_income_tax_amount;
        }
        if ($request->has('tds_gst_amount')) {
            $party_pending_payment->tds_gst_amount = $request->tds_gst_amount;
        }
        if ($request->has('tcs_income_tax_amount')) {
            $party_pending_payment->tcs_income_tax_amount = $request->tcs_income_tax_amount;
        }
        if ($request->has('tcs_gst_amount')) {
            $party_pending_payment->tcs_gst_amount = $request->tcs_gst_amount;
        }

        // $party_pending_payment->type_of_payment = $request->type_of_payment;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);
            $discount = array_search('discount', $type_of_payment);

            //array_search returns false or array index (if found). 0 can be an array index and it also acts as false in programming so adding 1 to it, so that it becomes 0+1 ie 1 which is true
            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if (!is_bool($discount)) {
                $discount += 1;
            }

            if ($cash && $bank && $pos && $discount) {
                $party_pending_payment->type_of_payment = 'combined';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->bank_payment = $request->banked_amount;
                $party_pending_payment->pos_payment = $request->posed_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
                $party_pending_payment->pos_bank_id = $request->pos_bank;
                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $party_pending_payment->type_of_payment = 'bank+pos+discount';

                $party_pending_payment->bank_payment = $request->banked_amount;
                $party_pending_payment->pos_payment = $request->posed_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount + $request->posed_amount + $request->discounted_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
                $party_pending_payment->pos_bank_id = $request->pos_bank;
                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $party_pending_payment->type_of_payment = 'cash+pos+discount';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->pos_payment = $request->posed_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->posed_amount + $request->discounted_amount;

                $party_pending_payment->pos_bank_id = $request->pos_bank;
                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $party_pending_payment->type_of_payment = 'cash+bank+discount';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->bank_payment = $request->banked_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->discounted_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $party_pending_payment->type_of_payment = 'bank+discount';

                $party_pending_payment->bank_payment = $request->banked_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount + $request->discounted_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $party_pending_payment->type_of_payment = 'cash+discount';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount + $request->discounted_amount;

                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $party_pending_payment->type_of_payment = 'pos+discount';

                $party_pending_payment->pos_payment = $request->posed_amount;
                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->posed_amount + $request->discounted_amount;

                $party_pending_payment->pos_bank_id = $request->pos_bank;
                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $party_pending_payment->type_of_payment = 'discount';

                $party_pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->discounted_amount;

                $party_pending_payment->discount_type = $request->discount_type;
                $party_pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $party_pending_payment->type_of_payment = 'cash+bank+pos';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->bank_payment = $request->banked_amount;
                $party_pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
                $party_pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $party_pending_payment->type_of_payment = 'bank+cash';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->bank_payment = $request->banked_amount;

                $amount = $request->cashed_amount + $request->banked_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $party_pending_payment->type_of_payment = 'pos+cash';

                $party_pending_payment->cash_payment = $request->cashed_amount;
                $party_pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->cashed_amount + $request->posed_amount;

                $party_pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $party_pending_payment->type_of_payment = 'pos+bank';

                $party_pending_payment->bank_payment = $request->banked_amount;
                $party_pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->banked_amount + $request->posed_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
                $party_pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $party_pending_payment->type_of_payment = 'bank';

                $party_pending_payment->bank_payment = $request->banked_amount;

                $amount = $request->banked_amount;

                $party_pending_payment->bank_id = $request->bank;
                $party_pending_payment->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $party_pending_payment->type_of_payment = 'pos';

                $party_pending_payment->pos_payment = $request->posed_amount;

                $amount = $request->posed_amount;

                $party_pending_payment->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $party_pending_payment->type_of_payment = 'cash';

                $party_pending_payment->cash_payment = $request->cashed_amount;

                $amount = $request->cashed_amount;
            }
        } else {
            $party_pending_payment->type_of_payment = 'no_payment';
        }

        $party_pending_payment->amount = $amount;

        // if (in_array("cash", $request->type_of_payment)) {
        //     $party_pending_payment->bank_payment = $request->cashed_amount;
        // }

        // if (in_array("bank", $request->type_of_payment)) {
        //     $party_pending_payment->bank_id = $request->bank;
        //     $party_pending_payment->bank_cheque = $request->bank_cheque;
        //     $party_pending_payment->bank_payment = $request->banked_amount;
        // }

        // if(in_array("pos", $request->type_of_payment)) {
        //     $party_pending_payment->pos_bank_id = $request->pos_bank;
        //     $party_pending_payment->pos_payment = $request->posed_amount;
        // }

        // if(in_array("discount", $request->type_of_payment)) {
        //     $party_pending_payment->discount_type = $request->discount_type;
        //     $party_pending_payment->discount_figure = $request->discount_figure;
        //     $party_pending_payment->discount_payment = $request->discounted_amount;
        // }

        if ($party_pending_payment->save()) {
            return redirect()->back()->with('success', 'Payment updated successfully!!');
        } else {
            return redirect()->back()->with('success', 'Failed to update payment');
        }
    }

    public function cancel_sale_party_payment(Request $request, $id)
    {
        $party_pending_payment = PartyPendingPaymentAccount::find($id);

        $party_pending_payment->status = 0;

        if ($party_pending_payment->save()) {
            return redirect()->back()->with('success', 'Payment cancelled successfully!!');
        } else {
            return redirect()->back()->with('success', 'Failed to cancel payment');
        }
    }

    public function cancel_sale_payment(Request $request, $id)
    {
        $sale_remaining_amount = SaleRemainingAmount::find($id);

        $sale_remaining_amount->status = 0;

        if ($sale_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Payment cancelled successfully!!');
        } else {
            return redirect()->back()->with('failure', 'Failed to cancel payment');
        }
    }

    public function generate_sales_register(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;

            $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->orderBy('invoice_date')->get();
        } else {
            $sales = User::findOrFail(Auth::user()->id)->invoices()->orderBy('invoice_date')->get();
        }

        return $sales;
    }

    private function calculate_item_total($price, $qty, $calculated_gst, $gst_tax_type, $hasLumpSump = false)
    {
        if ($hasLumpSump) {
            $qty = 1;
        }

        if ($gst_tax_type == "inclusive_of_tax") {
            return ($price * $qty) - $calculated_gst; // as inclusive of tax price would be price - gst;
        }

        return $price * $qty;
    }

    public function sales_account(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sale_array = array();
        $credit_note_array = array();
        $debit_note_array = array();

        $opening_balance = $this->calculate_sales_account_opening_balance($from_date);

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->orderBy('invoice_date')->get();

        // return $sales;

        foreach ($sales as $invoice) {

            // return $invoice;

            $amount_paid = $invoice->amount_paid ?? 0;
            $discount_payment = $invoice->discount_payment ?? 0;

            // as discount is not getting added to amount paid as of now
            $total_amount_paid = $amount_paid + $discount_payment;


            $party = Party::findOrFail($invoice->party_id);

            $sale_array[] = [
                'routable' => $invoice->id,
                'particulars' => $party->name,
                'voucher_type' => 'Sale',
                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                'amount' => $invoice->item_total_amount,
                'amount_paid' => $total_amount_paid,
                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                'transaction_type' => 'credit',
                'loop' => 'sale',
                'type' => 'showable',
                'reference_name' => $invoice->reference_name,
                'party_gst_no' => $invoice->party->gst,
                'party_shipping_address' => $invoice->party->shipping_address . ', ' . $invoice->party->shipping_city . ', ' . $invoice->party->shipping_state . ', ' . $invoice->party->shipping_pincode,
                'gross_profit_percent' => $invoice->percent_on_sale_of_invoice,
                'order_detail' => '',
                'shipping_detail' => $invoice->shipping_bill_no . ', ' . $invoice->date_of_shipping,
                'import_export' => $invoice->export_type,
                'port_code' => $invoice->code_of_shipping_port,
                'item_name' => '',
                'quantity_detail' => '',
                'rates' => '',
                'show_taxable_detail' => '',
                'gross_total' => $invoice->total_amount,
            ];
        }

        // foreach ($sales as $invoice) {
        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->groupBy('credit_notes.note_no')->get();

        foreach ($creditNotes as $creditNote) {
            if($creditNote->taxable_value > 0){
                $credit_note_array[] = [
                    'routable' => $creditNote->note_no ?? 0,
                    'particulars' => 'Credit Note',
                    'voucher_type' => 'Note',
                    'voucher_no' => $creditNote->note_no,
                    'amount' => $creditNote->taxable_value,
                    'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                    'month' => Carbon::parse($creditNote->created_at)->format('m'),
                    'transaction_type' => 'debit',
                    'loop' => 'credit_note',
                    'type' => 'showable',
                    'reference_name' => '',
                    'party_gst_no' => '',
                    'party_shipping_address' => '',
                    'gross_profit_percent' => '',
                    'order_detail' => '',
                    'shipping_detail' => '',
                    'import_export' => '',
                    'port_code' => '',
                    'item_name' => '',
                    'quantity_detail' => '',
                    'rates' => '',
                    'show_taxable_detail' => '',
                    'gross_total' => '',
                ];
            }
        }
        // }

        // foreach ($sales as $invoice) {
        // $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'sale')->where('debit_notes.status', 1)->whereBetween('debit_notes.created_at', [$from_date, $to_date])->groupBy('debit_notes.note_no')->get();

        // foreach ($debitNotes as $debitNote) {
        //     $debit_note_array[] = [
        //         'routable' => $debitNote->note_no ?? 0,
        //         'particulars' => 'Debit Note',
        //         'voucher_type' => 'Note',
        //         'voucher_no' => $debitNote->note_no,
        //         'amount' => $debitNote->taxable_value,
        //         'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
        //         'month' => Carbon::parse($debitNote->created_at)->format('m'),
        //         'transaction_type' => 'credit',
        //         'loop' => 'debit_note',
        //         'type' => 'showable',
        //         'reference_name' => '',
        //         'party_gst_no' => '',
        //         'party_shipping_address' => '',
        //         'gross_profit_percent' => '',
        //         'order_detail' => '',
        //         'shipping_detail' => '',
        //         'import_export' => '',
        //         'port_code' => '',
        //         'item_name' => '',
        //         'quantity_detail' => '',
        //         'rates' => '',
        //         'show_taxable_detail' => '',
        //         'gross_total' => '',
        //     ];
        // }
        // }   

        $combined_array = array_merge(
            $sale_array,
            $credit_note_array
        );

        $this->array_sort_by_column($combined_array, 'date');

        // echo "<pre>";
        // print_r($combined_array);
        // die();

        $group = [];
        foreach ($combined_array as $item) {
            $count = 0;
            $month = Carbon::parse($item['date'])->format('F');

            if (isset($group[$month])) {
                foreach ($group[$month] as $key => $value) {
                    $count = $key;
                }
            }
            $count++;
            // echo "<pre>";
            // print_r($item);
            // print_r( $group[$item['month']][$count] );
            foreach ($item as $key => $value) {
                // if ($key == 'month') continue;
                $group[$month][$count][$key] = $value;
            }
        }

        $combined_array = $group;

        // echo "<pre>";
        // print_r( $combined_array);
        // die();

        // return $combined_array;


        return view('report.sales_account', compact('opening_balance', 'combined_array', 'from_date', 'to_date'));
    }

    public function export_sales_account(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sale_array = array();
        $credit_note_array = array();
        $debit_note_array = array();

        $opening_balance = $this->calculate_sales_account_opening_balance($from_date);

        $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->orderBy('invoice_date')->get();

        // return $sales;

        foreach ($sales as $invoice) {

            // return $invoice;

            $amount_paid = $invoice->amount_paid ?? 0;
            $discount_payment = $invoice->discount_payment ?? 0;

            // as discount is not getting added to amount paid as of now
            $total_amount_paid = $amount_paid + $discount_payment;

            $sale_array[] = [
                'routable' => $invoice->id,
                'particulars' => 'Sale',
                'voucher_type' => 'Sale',
                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                'amount' => $invoice->item_total_amount,
                'amount_paid' => $total_amount_paid,
                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                'transaction_type' => 'credit',
                'loop' => 'sale',
                'type' => 'showable',
                'reference_name' => $invoice->reference_name,
                'party_gst_no' => $invoice->party->gst,
                'party_shipping_address' => $invoice->party->shipping_address . ', ' . $invoice->party->shipping_city . ', ' . $invoice->party->shipping_state . ', ' . $invoice->party->shipping_pincode,
                'gross_profit_percent' => $invoice->percent_on_sale_of_invoice,
                'order_detail' => '',
                'shipping_detail' => $invoice->shipping_bill_no . ', ' . $invoice->date_of_shipping,
                'import_export' => $invoice->export_type,
                'port_code' => $invoice->code_of_shipping_port,
                'item_name' => '',
                'quantity_detail' => '',
                'rates' => '',
                'show_taxable_detail' => '',
                'gross_total' => $invoice->total_amount,
            ];
        }

        // foreach ($sales as $invoice) {
        /*
             only show debit note against sale in the credit side so commenting below code
            */
        // $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'sale')->where('reason', 'sale_return')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

        // foreach ($creditNotes as $creditNote) {
        //     if($creditNote->taxable_value > 0){
        //         $credit_note_array[] = [
        //             'routable' => $creditNote->note_no ?? 0,
        //             'particulars' => 'Credit Note',
        //             'voucher_type' => 'Note',
        //             'voucher_no' => $creditNote->note_no,
        //             'amount' => $creditNote->taxable_value,
        //             'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
        //             'month' => Carbon::parse($creditNote->created_at)->format('m'),
        //             'transaction_type' => 'debit',
        //             'loop' => 'credit_note',
        //             'type' => 'showable',
        //             'reference_name' => '',
        //             'party_gst_no' => '',
        //             'party_shipping_address' => '',
        //             'gross_profit_percent' => '',
        //             'order_detail' => '',
        //             'shipping_detail' => '',
        //             'import_export' => '',
        //             'port_code' => '',
        //             'item_name' => '',
        //             'quantity_detail' => '',
        //             'rates' => '',
        //             'show_taxable_detail' => '',
        //             'gross_total' => '',
        //         ];
        //     }
        // }
        // }

        // foreach ($sales as $invoice) {
        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

        foreach ($debitNotes as $debitNote) {
            $debit_note_array[] = [
                'routable' => $debitNote->note_no ?? 0,
                'particulars' => 'Debit Note',
                'voucher_type' => 'Note',
                'voucher_no' => $debitNote->note_no,
                'amount' => $debitNote->taxable_value,
                'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
                'month' => Carbon::parse($debitNote->created_at)->format('m'),
                'transaction_type' => 'credit',
                'loop' => 'debit_note',
                'type' => 'showable',
                'reference_name' => '',
                'party_gst_no' => '',
                'party_shipping_address' => '',
                'gross_profit_percent' => '',
                'order_detail' => '',
                'shipping_detail' => '',
                'import_export' => '',
                'port_code' => '',
                'item_name' => '',
                'quantity_detail' => '',
                'rates' => '',
                'show_taxable_detail' => '',
                'gross_total' => '',
            ];
        }
        // }   

        $combined_array = array_merge(
            $sale_array,
            $debit_note_array
        );

        $this->array_sort_by_column($combined_array, 'date');

        // echo "<pre>";
        // print_r($combined_array);
        // die();

        /*$group = [];
        foreach ($combined_array as $item) {
            $count = 0;
            $month = Carbon::parse($item['date'])->format('F');

            if (isset($group[$month])) {
                foreach ($group[$month] as $key => $value) {
                    $count = $key;
                }
            }
            $count++;
            // echo "<pre>";
            // print_r($item);
            // print_r( $group[$item['month']][$count] );
            foreach ($item as $key => $value) {
                // if ($key == 'month') continue;
                $group[$month][$count][$key] = $value;
            }
        }*/

        // $combined_array = $group;

        // echo "<pre>";
        // print_r( $combined_array);
        // die();

        // return $combined_array;

        $sale_accounts = array();
        $moving_cash = $opening_balance;
        // $purchase_accounts[]['opening_balance'] = 'opening balance';
        // $purchase_accounts[]['closing balance'] = $moving_cash;
        foreach ($combined_array as $arr) {
            $id = $arr['routable'];
            $sale_accounts[$id]['Date'] = $arr['date'];
            $sale_accounts[$id]['Particulars'] = $arr['particulars'];
            $sale_accounts[$id]['Voucher type'] = $arr['voucher_type'];
            $sale_accounts[$id]['Voucher no'] = $arr['voucher_no'];
            if ($arr['transaction_type'] == 'debit') {
                $moving_cash += $arr['amount'];
                $sale_accounts[$id]['Debit'] = $arr['amount'] ?? 0;
                $sale_accounts[$id]['Credit'] = 0;
            }

            if ($arr['transaction_type'] == 'credit') {
                $moving_cash -= $arr['amount'];
                $sale_accounts[$id]['Debit'] = 0;
                $sale_accounts[$id]['Credit'] = $arr['amount'] ?? 0;
            }
            $sale_accounts[$id]['Closing balance'] = $moving_cash;
        }

        // echo "<pre>";
        // print_r($sale_accounts);

        $sale_accounts[]['OPENING BALANCE'] = 'OPENING BALANCE';
        $sale_accounts[]['closing balance'] = $opening_balance;

        Excel::create('sales_account', function ($excel) use ($sale_accounts) {
            $excel->sheet('FirstSheet', function ($sheet) use ($sale_accounts) {
                $sheet->fromArray($sale_accounts);
            });
        })->export('xlsx');
    }

    private function calculate_sales_account_opening_balance($till_date)
    {
        $opening_balance = 0;

        $sales = User::findOrFail(Auth::user()->id)->invoices()
            ->where('invoice_date', '<', $till_date)
            ->orderBy('invoice_date')
            ->get();


        $creditNotes = User::find(auth()->user()->id)->creditNotes()
            ->where('credit_notes.type', 'sale')
            ->where('reason', 'sale_return')
            ->where('credit_notes.created_at', '<', $till_date)
            ->get();


        // $debitNotes = User::find(auth()->user()->id)->debitNotes()
        // ->where('debit_notes.type', 'sale')
        // ->where('debit_notes.created_at', '<', $till_date)
        // ->get();

        foreach ($sales as $invoice) {
            $opening_balance -= $invoice->item_total_amount;
        }


        foreach ($creditNotes as $creditNote) {
            $opening_balance += $creditNote->taxable_value;
        }

        // foreach( $debitNotes as $debitNote ){
        //     $opening_balance -= $debitNote->taxable_value;
        // }

        return $opening_balance;
    }

    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }


    public function sale_gst_report(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->orderBy('invoice_date')->get();

        foreach ($sales as $sale) {
            if (Auth::user()->profile->place_of_business == $sale->party->business_place) {
                $sale->has_igst = false;
            } else {
                $sale->has_igst = true;
            }

            $sale->fivePercentTotal = 0;
            $sale->twelvePercentTotal = 0;
            $sale->eighteenPercentTotal = 0;
            $sale->twentyEightPercentTotal = 0;
            $sale->exemptPercentTotal = 0;
            $sale->nilPercentTotal = 0;
            $sale->exportPercentTotal = 0;

            $sale->fivePercentItemTotal = 0;
            $sale->twelvePercentItemTotal = 0;
            $sale->eighteenPercentItemTotal = 0;
            $sale->twentyEightPercentItemTotal = 0;
            $sale->exemptPercentItemTotal = 0;
            $sale->nilPercentItemTotal = 0;
            $sale->exportPercentItemTotal = 0;
            foreach ($sale->invoice_items->groupBy('gst_rate') as $data) {
                foreach ($data as $item) {
                    // Credit/debit note value must not be shown in SALE  GST AND PURCHASE GST report (16 oct 2020) point date
                    // $q = CreditNote::where('invoice_id', $sale->id)->where('type', 'sale')->whereBetween('created_at', [$from_date, $to_date])->where('reason', ['discount_on_sale', 'sale_return']);
                    switch ($item->gst_rate) {
                        case 5:
                            $sale->fivePercentTotal += $item->gst;
                            $sale->fivePercentItemTotal += $item->item_total;
                            // $credit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($credit_notes as $note) {
                            //     $sale->fivePercentTotal += $note->gst;
                            //     $sale->fivePercentItemTotal += $note->price;
                            // }
                            break;
                        case 12:
                            $sale->twelvePercentTotal += $item->gst;
                            $sale->twelvePercentItemTotal += $item->item_total;
                            // $credit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($credit_notes as $note) {
                            //     $sale->twelvePercentTotal += $note->gst;
                            //     $sale->twelvePercentItemTotal += $note->price;
                            // }
                            break;
                        case 18:
                            $sale->eighteenPercentTotal += $item->gst;
                            $sale->eighteenPercentItemTotal += $item->item_total;
                            // $credit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($credit_notes as $note) {
                            //     $sale->eighteenPercentTotal += $note->gst;
                            //     $sale->eighteenPercentItemTotal += $note->price;
                            // }
                            break;
                        case 28:
                            $sale->twentyEightPercentTotal += $item->gst;
                            $sale->twentyEightPercentItemTotal += $item->item_total;
                            // $credit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($credit_notes as $note) {
                            //     $sale->twentyEightPercentTotal += $note->gst;
                            //     $sale->twentyEightPercentItemTotal += $note->price;
                            // }
                            break;
                        case 'exempt':
                            $sale->exemptPercentTotal += $item->gst;
                            $sale->exemptPercentItemTotal += $item->item_total;
                            break;
                        case 'nil':
                            $sale->nilPercentTotal += $item->gst;
                            $sale->nilPercentItemTotal += $item->item_total;
                            break;
                        case 'export':
                            $sale->exportPercentTotal += $item->gst;
                            $sale->exportPercentItemTotal += $item->item_total;
                            break;
                    }
                }
            }
        }

        // return $sales;

        return view('report.sale_gst_report', compact('sales'));
    }

    public function export_sale_gst_report(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->get();

        foreach ($sales as $sale) {
            $sale->fivePercentTotal = 0;
            $sale->twelvePercentTotal = 0;
            $sale->eighteenPercentTotal = 0;
            $sale->twentyEightPercentTotal = 0;
            foreach ($sale->invoice_items->groupBy('gst_rate') as $data) {
                foreach ($data as $item) {
                    $q = CreditNote::where('invoice_id', $sale->id)->where('type', 'sale')->whereBetween('created_at', [$from_date, $to_date])->where('reason', ['discount_on_sale', 'sale_return']);
                    switch ($item->gst_rate) {
                        case 5:
                            $sale->fivePercentTotal += $item->gst;
                            $credit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($credit_notes as $note) {
                                $sale->fivePercentTotal += $note->gst;
                            }
                            break;
                        case 12:
                            $sale->twelvePercentTotal += $item->gst;
                            $credit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($credit_notes as $note) {
                                $sale->twelvePercentTotal += $note->gst;
                            }
                            break;
                        case 18:
                            $sale->eighteenPercentTotal += $item->gst;
                            $credit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($credit_notes as $note) {
                                $sale->eighteenPercentTotal += $note->gst;
                            }
                            break;
                        case 28:
                            $sale->twentyEightPercentTotal += $item->gst;
                            $credit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($credit_notes as $note) {
                                $sale->twentyEightPercentTotal += $note->gst;
                            }
                            break;
                    }
                }
            }
        }

        $saleArray = array();

        foreach ($sales as $sale) {
            $saleArray[$sale->id]['Date'] = Carbon::parse($sale->invoice_date)->format('d/m/Y');
            $saleArray[$sale->id]['Invoice No'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $saleArray[$sale->id]['Buyer Name'] = $sale->party->name;
            $saleArray[$sale->id]['5%'] = $sale->fivePercentTotal;
            $saleArray[$sale->id]['sgst @2.5%'] = $sale->fivePercentTotal / 2;
            $saleArray[$sale->id]['cgst/utgst @2.5%'] = $sale->fivePercentTotal / 2;
            $saleArray[$sale->id]['12%'] = $sale->twelvePercentTotal;
            $saleArray[$sale->id]['sgst @6%'] = $sale->twelvePercentTotal / 2;
            $saleArray[$sale->id]['cgst/utgst @6%'] = $sale->twelvePercentTotal / 2;
            $saleArray[$sale->id]['18%'] = $sale->eighteenPercentTotal;
            $saleArray[$sale->id]['sgst @9%'] = $sale->eighteenPercentTotal / 2;
            $saleArray[$sale->id]['cgst/utgst @9%'] = $sale->eighteenPercentTotal / 2;
            $saleArray[$sale->id]['28%'] = $sale->twentyEightPercentTotal;
            $saleArray[$sale->id]['sgst @14%'] = $sale->twentyEightPercentTotal / 2;
            $saleArray[$sale->id]['cgst/utgst @14%'] = $sale->twentyEightPercentTotal / 2;
        }

        // $saleArray[]['5%'] = '5';
        // $saleArray[]['12%'] = '12';
        // $saleArray[]['18%'] = '18';
        // $saleArray[]['28%'] = '28';

        Excel::create('sale_gst_report', function ($excel) use ($saleArray) {
            $excel->sheet('FirstSheet', function ($sheet) use ($saleArray) {
                $sheet->fromArray($saleArray);
            });
        })->export('xlsx');
    }

    public function export_b2b_sale(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->get();

        $saleArray = array();

        foreach ($sales as $sale) {
            $saleArray[$sale->id]['GSTIN/UIN of Receipt'] = $sale->party->gst;
            $saleArray[$sale->id]['Invoice No'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $saleArray[$sale->id]['Invoice date'] = $sale->invoice_date;
            $saleArray[$sale->id]['Invoice Value'] = $sale->total_amount;
            $saleArray[$sale->id]['Place Of Supply'] = $sale->party->business_place;
            $saleArray[$sale->id]['Reverse Charge'] = $sale->party->reverse_charge;
            $saleArray[$sale->id]['Invoice Type'] = $sale->type_of_bill;
            $saleArray[$sale->id]['E-commerce GST IN'] = '';
            $saleArray[$sale->id]['Rate'] = $sale->gst;
            $saleArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;
            $saleArray[$sale->id]['Cess Amount'] = $sale->cess;
        }

        Excel::create('b2b_sale_report', function ($excel) use ($saleArray) {
            $excel->sheet('FirstSheet', function ($sheet) use ($saleArray) {
                $sheet->fromArray($saleArray);
            });
        })->export('xlsx');
    }


    public function export_b2cl_sale(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->get();

        $saleArray = array();

        foreach ($sales as $sale) {
            $saleArray[$sale->id]['Invoice No'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $saleArray[$sale->id]['Invoice date'] = $sale->invoice_date;
            $saleArray[$sale->id]['Invoice Value'] = $sale->total_amount;
            $saleArray[$sale->id]['Place Of Supply'] = $sale->party->business_place;
            $saleArray[$sale->id]['Rate'] = $sale->gst;
            $saleArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;
            $saleArray[$sale->id]['Cess Amount'] = $sale->cess;
            $saleArray[$sale->id]['E-commerce GST IN'] = '';
        }

        Excel::create('b2b_sale_report', function ($excel) use ($saleArray) {
            $excel->sheet('FirstSheet', function ($sheet) use ($saleArray) {
                $sheet->fromArray($saleArray);
            });
        })->export('xlsx');
    }

    public function edit_debit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $note_no)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->get();

        $note_date = '';
        foreach ($debit_notes as $note) {
            $item = Item::findOrFail($note->item_id);
            $note->item_name = $item->name ?? '';
            $note->item_gst = $item->gst ?? '';
            $note_date = $note->note_date;
            $note->base_unit = $item->measuring_unit;
            $note->alternate_unit = $item->alternate_measuring_unit;
            $note->compound_unit = $item->compound_measuring_unit;
        }

        $invoice = Invoice::findOrFail($debit_notes->first()->bill_no);

        return view('sale.edit_debit_note', compact('note_no', 'debit_notes', 'invoice', 'note_date'));
    }

    public function edit_credit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $note_no)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->get();
        $note_date = '';
        foreach ($credit_notes as $note) {
            $item = Item::findOrFail($note->item_id);
            $note->item_name = $item->name ?? '';
            $note->item_gst = $item->gst ?? '';
            $note_date = $note->note_date;
            $note->base_unit = $item->measuring_unit;
            $note->alternate_unit = $item->alternate_measuring_unit;
            $note->compound_unit = $item->compound_measuring_unit;
        }

        $invoice = Invoice::findOrFail($credit_notes->first()->invoice_id);

        return view('sale.edit_credit_note', compact('note_no', 'credit_notes', 'invoice', 'note_date'));
    }

    public function update_credit_note_item(Request $request)
    {
        $id = $request->modal_row_id;
        $credit_note = CreditNote::findOrFail($id);

        $price = $request->modal_revised_price ?? 0;
        $gst = $request->modal_revised_gst ?? 0;
        $qty = $request->modal_revised_qty ?? 0;
        
        $found_item = Item::find($credit_note->item_id);
        $measuring_unit = $request->modal_measuring_unit;
        $original_qty = $qty;
        if (isset($measuring_unit)) {
            if ($measuring_unit == $found_item->alternate_measuring_unit) {
                $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                $original_qty = $qty * $alternate_to_base;
                $qty = $original_qty;
            }

            if ($measuring_unit == $found_item->compound_measuring_unit) {
                $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                $compound_to_alternate = $found_item->conversion_of_compound_to_alternate_unit_value;
                $original_qty = $alternate_to_base * $compound_to_alternate * $qty;
                $qty = $original_qty;
            }

        }

        $discount = $request->modal_revised_discount ?? 0;

        

        $credit_note->price = $price;
        $credit_note->gst = $gst;
        $credit_note->quantity = $qty;
        $credit_note->original_qty = $original_qty;
        $credit_note->original_unit = $measuring_unit;
        $credit_note->discount = $discount;

        $credit_note->save();

        return redirect()->back()->with('success', 'Credit Note Item updated successfully');
    }

    public function update_debit_note_item(Request $request)
    {

        // return $request->all();

        $id = $request->modal_row_id;
        $debit_note = DebitNote::findOrFail($id);
        
        $price = $request->modal_revised_price ?? 0;
        $gst = $request->modal_revised_gst ?? 0;
        $qty = $request->modal_revised_qty ?? 0;

        $found_item = Item::find($credit_note->item_id);
        $measuring_unit = $request->modal_measuring_unit;
        $original_qty = $qty;
        if (isset($measuring_unit)) {
            if ($measuring_unit == $found_item->alternate_measuring_unit) {
                $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                $original_qty = $qty * $alternate_to_base;
                $qty = $original_qty;
            }

            if ($measuring_unit == $found_item->compound_measuring_unit) {
                $alternate_to_base = $found_item->conversion_of_alternate_to_base_unit_value;
                $compound_to_alternate = $found_item->conversion_of_compound_to_alternate_unit_value;
                $original_qty = $alternate_to_base * $compound_to_alternate * $qty;
                $qty = $original_qty;
            }

        }

        $discount = $request->modal_revised_discount ?? 0;


        $debit_note->price = $price;
        $debit_note->gst = $gst;
        $debit_note->quantity = $qty;
        $debit_note->original_qty = $original_qty;
        $debit_note->original_unit = $measuring_unit;
        $debit_note->discount = $discount;

        $debit_note->save();

        return redirect()->back()->with('success', 'Debit Note Item updated successfully');
    }

    public function update_credit_note(Request $request)
    {
        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $request->search_by_note_no)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

        foreach ($credit_notes as $credit_note) {
            $credit_note->reason = $request->reason_change;
            $credit_note->taxable_value = $request->taxable_value;
            $credit_note->discount_value = $request->discount_value;
            $credit_note->gst_value = $request->gst_value;
            $credit_note->note_value = $request->note_value;
            $credit_note->note_no = $request->note_no;
            $credit_note->note_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->note_date)));

            $credit_note->save();
        }

        return redirect()->route('sale.credit.note.edit', $request->note_no)->with('success', 'Credit Note Updated successfully');
    }

    public function update_debit_note(Request $request)
    {
        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $request->search_by_note_no)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

        foreach ($debit_notes as $debit_note) {
            $debit_note->reason = $request->reason_change;
            $debit_note->taxable_value = $request->taxable_value;
            $debit_note->discount_value = $request->discount_value;
            $debit_note->gst_value = $request->gst_value;
            $debit_note->note_value = $request->note_value;
            $debit_note->note_no = $request->note_no;
            $debit_note->note_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->note_date)));

            $debit_note->save();
        }

        return redirect()->route('sale.debit.note.edit', $request->note_no)->with('success', 'Debit Note Updated successfully');
    }

    public function show_debit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $note_no)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->get();

        foreach ($debit_notes as $note) {
            $note->item_name = Item::findOrFail($note->item_id)->name ?? '';
        }

        $invoice = Invoice::findOrFail($debit_notes->first()->bill_no);

        return view('sale.show_debit_note', compact('note_no', 'debit_notes', 'invoice'));
    }

    public function show_credit_note($note_no)
    {
        
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $note_no)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->get();

        foreach ($credit_notes as $note) {
            $note->item_name = Item::findOrFail($note->item_id)->name ?? '';
        }

        $invoice = Invoice::findOrFail($credit_notes->first()->invoice_id);

        return view('sale.show_credit_note', compact('note_no', 'credit_notes', 'invoice'));
    }

    public function send_mail_to_invoice_holder(Invoice $invoice)
    {
        if ($invoice->party->email) {
            Mail::to($invoice->party->email)->send(new SendInvoice($invoice->id));
        }
        return redirect()->back()->with('success', 'Invoice created successfully');
    }

    public function validate_sale_invoice_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('invoices')
            ->where('invoice_no', $request->invoice_no)
            ->get();

        foreach ($rows as $row) {
            if ($row->party_id == $party_id && $user->profile->financial_year_from <= $row->invoice_date && $user->profile->financial_year_to >= $row->invoice_date) {
                $isValidated = false;
                break;
            }
        }

        if (!$isValidated) {
            return response()->json(array(
                'success' => false,
                'errors' => 'Please provide unique order no for selected party in the current financial year'
            ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_sale_order_voucher_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('sale_order')
            ->where('token', $request->token)
            ->get();

        foreach ($rows as $row) {
            if ($row->party_id == $party_id && $user->profile->financial_year_from <= $row->date && $user->profile->financial_year_to >= $row->date) {
                $isValidated = false;
                break;
            }
        }

        if (!$isValidated) {
            return response()->json(array(
                'success' => false,
                'errors' => 'Please provide unique order no for selected party in the current financial year'
            ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_sale_party_payment_voucher_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('party_pending_payment_account')
            ->where('type', 'sale')
            ->where('voucher_no', $request->token)
            ->get();

        foreach ($rows as $row) {
            if ($row->party_id == $party_id && $user->profile->financial_year_from <= $row->created_at && $user->profile->financial_year_to >= $row->created_at) {
                $isValidated = false;
                break;
            }
        }

        if (!$isValidated) {
            return response()->json(array(
                'success' => false,
                'errors' => 'Please provide unique order no for selected party in the current financial year'
            ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_sale_payment_voucher_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('sale_remaining_amounts')
            ->where('voucher_no', $request->token)
            ->get();

        foreach ($rows as $row) {
            if ($row->party_id == $party_id && $user->profile->financial_year_from <= $row->created_at && $user->profile->financial_year_to >= $row->created_at) {
                $isValidated = false;
                break;
            }
        }

        if (!$isValidated) {
            return response()->json(array(
                'success' => false,
                'errors' => 'Please provide unique order no for selected party in the current financial year'
            ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function find_invoice_no(Request $request)
    {
        $invoices = User::find(Auth::user()->id)->invoices()->where('invoice_no', 'like', $request->q . '%')->get();
        return response()->json($invoices);
    }
}
