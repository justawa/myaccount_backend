<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Excel;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBill;


use App\Bank;
use App\BillNote;
use App\CreditNote;
use App\DebitNote;
use App\DutiesAndTaxes;
use App\Group;
use App\Item;
use App\Party;
use App\PartyPendingPaymentAccount;
use App\Purchase;
use App\PurchaseRecord;
use App\Purchase_Item;
use App\PurchaseLog;
use App\PurchaseOrder;
use App\PurchaseRemainingAmount;
use App\State;
use App\Transporter;
use App\UploadedBill;
use App\User;
use App\UserProfile;
use App\Insurance;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (isset($request->from_date) && isset($request->to_date)) {
            $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date))), date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)))])->orderBy('id', 'desc')->get();
        } else if (isset($request->query_by) && isset($request->q)) {
            if ($request->query_by == 'bill_no') {
                $purchases = User::find(Auth::user()->id)->purchases()->where($request->query_by, $request->q)->orderBy('id', 'desc')->get();
            } else if ($request->query_by == 'name') {
                $party = Party::where('user_id', Auth::user()->id)->where('name', $request->q)->first();

                $purchases = PurchaseRecord::where('party_id', $party->id)->orderBy('id', 'desc')->get();
            }
            // else if( $request->query_by == 'state' ) {
            //     $state = State::where('name', strtoupper($request->q))->first();

            //     $parties = Party::where('user_id', Auth::user()->id)->where('business_place', $state->id)->get();

            //     foreach( $parties as $party ) {
            //         $data = PurchaseRecord::where('party_id', $party->id)->get();

            //         $purchases[$party->id] = $data;
            //     }

            //     // $purchases = $data;

            //     $purchases['type'] = 'state';

            //     foreach( $purchases[$party->id] as $purchase ){ 
            //         $party = Party::find($purchase->party_id);

            //         $purchase->party_name = $party->name;
            //         $purchase->party_city = $party->city;
            //     }
            // }
        } else {
            $purchases = User::find(Auth::user()->id)->purchases()->orderBy('id', 'desc')->get();
        }

        // $purchases = PurchaseRecord::where('user_id', Auth::user()->id)->where('type_of_bill', 'regular')->get();

        if ($request->query_by != 'state') {
            foreach ($purchases as $purchase) {
                $party = Party::find($purchase->party_id);

                $purchase->party_name = $party->name;
                $purchase->party_city = $party->city;
            }
        }

        return view('purchase.index', compact('purchases'));
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
        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'creditor')->get();
        $transporters = Transporter::where('user_id', Auth::user()->id)->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('status', 0)->orderBy('created_at', 'desc')->take(5)->get();

        $bill = User::findOrFail(Auth::user()->id)->purchases()->orderBy('id', 'desc')->first();

        // return $bill;

        $bill_no = null;
        $myerrors = array();

        if (auth()->user()->purchaseSetting->bill_no_type == 'auto') {
            if (isset($bill->bill_no)) {
                $width = auth()->user()->purchaseSetting ? auth()->user()->purchaseSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['bill_no'][] = 'Invalid Max-length provided. Please update your purchase settings';
                        break;
                    case 1:
                        if ($bill->bill_no > 9) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 2:
                        if ($bill->bill_no > 99) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 3:
                        if ($bill->bill_no > 999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 4:
                        if ($bill->bill_no > 9999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 5:
                        if ($bill->bill_no > 99999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 6:
                        if ($bill->bill_no > 999999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 7:
                        if ($bill->bill_no > 9999999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 8:
                        if ($bill->bill_no > 99999999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                    case 9:
                        if ($bill->bill_no > 999999999) {
                            $myerrors['bill_no'][] = 'Max-length exceeded for bill no. Please update your purchase settings';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->purchaseSetting) && auth()->user()->purchaseSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->purchaseSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->purchaseSetting) && auth()->user()->purchaseSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->purchaseSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->purchaseSetting) && auth()->user()->purchaseSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->purchaseSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['bill_no'][] = 'Applicable date expired for your bill no series. Please update your purchase settings';
            }

            if ($bill) {
                if (isset($bill->bill_no_type) && $bill->bill_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->purchaseSetting->updated_at) > \Carbon\Carbon::parse($bill->created_at)) {
                        $bill_no = (isset(auth()->user()->purchaseSetting->starting_no) && auth()->user()->purchaseSetting->starting_no > 0) ? auth()->user()->purchaseSetting->starting_no - 1 : 0;
                    } else {
                        $bill_no = ($bill->bill_no == '' || $bill->bill_no == null) ? 0 : $bill->bill_no;
                    }
                } else {
                    $bill_no = (isset(auth()->user()->purchaseSetting->starting_no) && auth()->user()->purchaseSetting->starting_no > 0) ? auth()->user()->purchaseSetting->starting_no - 1 : 0;
                }
            } else {
                $bill_no = (isset(auth()->user()->purchaseSetting->starting_no) && auth()->user()->purchaseSetting->starting_no > 0) ? auth()->user()->purchaseSetting->starting_no - 1 : 0;
            }
        }

        foreach ($uploaded_bills as $bill) {
            switch ($bill->month) {
                case 1:
                    $bill->month = "Jan";
                    break;
                case 2:
                    $bill->month = "Feb";
                    break;
                case 3:
                    $bill->month = "Mar";
                    break;
                case 4:
                    $bill->month = "Apr";
                    break;
                case 5:
                    $bill->month = "May";
                    break;
                case 6:
                    $bill->month = "Jun";
                    break;
                case 7:
                    $bill->month = "Jul";
                    break;
                case 8:
                    $bill->month = "Aug";
                    break;
                case 9:
                    $bill->month = "Sep";
                    break;
                case 10:
                    $bill->month = "Oct";
                    break;
                case 11:
                    $bill->month = "Nov";
                    break;
                case 12:
                    $bill->month = "Dec";
                    break;
            }
        }

        $myerrors = collect($myerrors);

        return view('purchase.create', compact('insurances', 'items', 'parties', 'groups', 'transporters', 'banks', 'uploaded_bills', 'user_profile', 'bill_no'))->with('myerrors', $myerrors);
    }

    public function validateBillNo(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('purchase_records')
            ->where('bill_no', $request->bill_no)
            ->get();

        foreach ($rows as $row) {
            if ($row->party_id == $party_id && $user->profile->financial_year_from <= $row->bill_date && $user->profile->financial_year_to >= $row->bill_date) {
                $isValidated = false;
            }
        }

        if (!$isValidated) {
            return response()->json(array(
                'success' => false,
                'errors' => 'Please provide unique Bill no for selected party in the current financial year'
            ), 400);
        }

        return response()->json(array('success' => true), 200);

        // $rules = array('bill_no' => 'required|uniqueBillForParty:party');
        // $validator = Validator::make($request, $rules);

        // if ($validator->fails())
        // {
        //     return Response::json(array(
        //         'success' => false,
        //         'errors' => 'Please provide unique Bill no for selected party in the current financial year'

        //     ), 400);
        // }
        // return Response::json(array('success' => true), 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate(
            $request,
            [
                'bill_no' => 'required|uniqueBillForParty:party',
                'bill_date' => 'required|date_format:d/m/Y',
                'party' => 'required',
                'item_discount' => 'array',
                'item_discount.*' => 'numeric|nullable',
                'quantity' => 'array',
                'quantity.*' => 'numeric|nullable',
                'price' => 'array',
                'price.*' => 'numeric|nullable',
                'item_barcode' => 'array',
                'item_barcode.*' => 'required|string',
                'calculated_gst' => 'array',
                'calculated_gst.*' => 'numeric|nullable',
                'cashed_amount' => 'numeric|nullable',
                'banked_amount' => 'numeric|nullable',
                'posed_amount' => 'numeric|nullable',
                'total_discount' => 'numeric|nullable',
                'purchase_order_no' => 'alpha_num|nullable',
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
                'bill_no.required' => 'Bill No is required',
                'bill_no.uniqueBillForParty' => 'Please provide unique Bill no for selected party in the current financial year',
                'item_discount.*.numeric' => 'Discount must be a number',
                'quantity.*.numeric' => 'Quantity must be a number',
                'price.*.numeric' => 'Price/Rate must be a number',
                'item_barcode.*.required' => 'Bar/Marka is required for all items',
                'calculated_gst.*.numeric' => 'Some error occured in data. Please try again'
            ]
        );

        if (auth()->user()->profile->financial_year_from > date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)))) {
            return redirect()->back()->with('failure', 'Please select valid bill date for current financial year');
        }

        if (auth()->user()->profile->financial_year_to < date('Y-m-d', strtotime(
            str_replace('/', '-', $request->bill_date)
        ))) {
            return redirect()->back()->with('failure', 'Please select valid bill date for current financial year');
        }

        $purchase_record = new PurchaseRecord;

        $purchase_record->party_id = $request->party;

        if ($request->has('buyer_name')) {
            $purchase_record->buyer_name = $request->buyer_name;
        }

        $purchase_record->bill_no = $request->bill_no;

        if (isset(auth()->user()->purchaseSetting) && isset(auth()->user()->purchaseSetting->bill_no_type)) {
            $purchase_record->bill_no_type = auth()->user()->purchaseSetting->bill_no_type;
        } else {
            $purchase_record->bill_no_type = 'manual';
        }

        if ($request->has('bill_date')) {
            $purchase_record->bill_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
        } else {
            $purchase_record->bill_date = date('Y-m-d', time());
        }

        $purchase_record->item_total_amount = $request->item_total_amount;
        $purchase_record->item_total_gst = $request->total_gst_amounted;
        $purchase_record->item_total_rcm_gst = $request->item_total_rcm_gst;
        $purchase_record->item_total_cess = $request->total_cess_amounted;
        $purchase_record->total_discount = $request->total_discount;
        // $purchase_record->total_amount = $request->total_amount;

        $purchase_record->amount_before_round_off = $request->total_amount;
        $purchase_record->round_off_operation = $request->round_off_operation;
        $purchase_record->round_offed = $request->round_offed;
        $purchase_record->total_amount = $request->amount_to_pay;

        $purchase_record->amount_paid = $request->amount_paid;
        $purchase_record->amount_remaining = $request->amount_remaining;
        $purchase_record->purchase_order_no = $request->purchase_order_no;
        $purchase_record->discount = $request->overall_discount;


        if ($request->tax_inclusive == 'inclusive_of_tax') {
            $purchase_record->amount_type = 'inclusive';
        } else if ($request->tax_inclusive == 'exclusive_of_tax') {
            $purchase_record->amount_type = 'exclusive';
        }

        /*----------------------------------------------------------------------------------*/

        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $party = Party::where('id', $request->party)->first();

        if ($party->reverse_charge == "yes") {
            $purchase_record->gst_classification = 'rcm';
        }

        if ($user_profile->place_of_business == $party->business_place) {
            $purchase_record->cgst = $request->total_gst_amounted / 2;
            $purchase_record->sgst = $request->total_gst_amounted / 2;
        }
        // else if( $user_profile->place_of_business != $party->business_place && ( $party->business_place == 4 || $party->business_place == 7 || $party->business_place == 25 || $party->business_place == 26 || $party->business_place == 31 || $party->business_place == 34 || $party->business_place == 35 ) ) {
        //     $purchase_record->ugst = $request->item_total_gst;
        // }
        else {
            $purchase_record->igst = $request->total_gst_amounted;
        }

        //-----------------------------------------------------------------------------------

        $purchase_record->tcs = $request->tcs;

        $purchase_record->remark = $request->overall_remark;

        $purchase_record->reference_name = $request->reference_name;

        $purchase_record->labour_charge = $request->labour_charges;
        $purchase_record->freight_charge  = $request->freight_charges;
        $purchase_record->transport_charge  = $request->transport_charges;
        $purchase_record->insurance_charge  = $request->insurance_charges;
        $purchase_record->gst_charged_on_additional_charge = $request->gst_charged;

        $purchase_record->insurance_id = $request->insurance_company;

        if (Session::has("transporter_details")) {
            $transporter_id = session("transporter_details.transporter_id");
            $vehicle_type = session("transporter_details.vehicle_type");
            $vehicle_number = session("transporter_details.vehicle_number");
            $delivery_date = session("transporter_details.delivery_date");

            $dtime = strtotime($delivery_date);

            $delivery_date = date('Y-m-d', $dtime);

            $purchase_record->transporter_id = $transporter_id;
            $purchase_record->vehicle_type = $vehicle_type;
            $purchase_record->vehicle_number = $vehicle_number;
            $purchase_record->delivery_date = $delivery_date;
        }

        // $purchase_record->type_of_payment = $request->type_of_payment;

        // if ($request->type_of_payment == 'bank') {
        //     $purchase_record->bank_id = $request->bank;
        // }

        if ($request->has('type_of_payment')) {
            $type_of_payment = $request->type_of_payment;
            // $count_top = count($type_of_payment);

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
            //     $purchase_record->type_of_payment = 'combined';

            //     $purchase_record->cash_payment = $request->cashed_amount;
            //     $purchase_record->bank_payment = $request->banked_amount;
            //     $purchase_record->pos_payment = $request->posed_amount;

            //     $purchase_record->bank_id = $request->bank;
            //     $purchase_record->bank_cheque = $request->bank_cheque;
            //     $purchase_record->pos_bank_id = $request->pos_bank;

            //     $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            //     $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // } else if ( $pos && $bank ) {
            //     $purchase_record->type_of_payment = 'pos+bank';

            //     $purchase_record->bank_payment = $request->banked_amount;
            //     $purchase_record->pos_payment = $request->posed_amount;

            //     $purchase_record->bank_id = $request->bank;
            //     $purchase_record->bank_cheque = $request->bank_cheque;
            //     $purchase_record->pos_bank_id = $request->pos_bank;

            //     $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            //     $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // } else if ( $pos && $cash ) {
            //     $purchase_record->type_of_payment = 'pos+cash';

            //     $purchase_record->cash_payment = $request->cashed_amount;
            //     $purchase_record->pos_payment = $request->posed_amount;

            //     $purchase_record->pos_bank_id = $request->pos_bank;

            //     $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // } else if ( $bank && $cash ) {
            //     $purchase_record->type_of_payment = 'bank+cash';

            //     $purchase_record->cash_payment = $request->cashed_amount;
            //     $purchase_record->bank_payment = $request->banked_amount;

            //     $purchase_record->bank_id = $request->bank;
            //     $purchase_record->bank_cheque = $request->bank_cheque;

            //     $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            // } else if ( $bank ) {
            //     $purchase_record->type_of_payment = 'bank';

            //     $purchase_record->bank_payment = $request->banked_amount;

            //     $purchase_record->bank_id = $request->bank;
            //     $purchase_record->bank_cheque = $request->bank_cheque;

            //     $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            // } else if ( $cash ) {
            //     $purchase_record->type_of_payment = 'cash';

            //     $purchase_record->cash_payment = $request->cashed_amount;
            // } else if ( $pos ) {
            //     $purchase_record->type_of_payment = 'pos';

            //     $purchase_record->pos_payment = $request->posed_amount;

            //     $purchase_record->pos_bank_id = $request->pos_bank;

            //     $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            // }

            if ($cash && $bank && $pos && $discount) {

                $purchase_record->type_of_payment = 'combined';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->bank_payment = $request->banked_amount;
                $purchase_record->pos_payment = $request->posed_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;
                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($cash && $bank && $pos) {

                $purchase_record->type_of_payment = 'cash+bank+pos';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->bank_payment = $request->banked_amount;
                $purchase_record->pos_payment = $request->posed_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($cash && $bank && $discount) {
                $purchase_record->type_of_payment = 'cash+bank+discount';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->bank_payment = $request->banked_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($cash && $discount && $pos) {
                $purchase_record->type_of_payment = 'cash+pos+discount';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->pos_payment = $request->posed_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;
            } else if ($discount && $bank && $pos) {
                $purchase_record->type_of_payment = 'bank+pos+discount';

                $purchase_record->bank_payment = $request->banked_amount;
                $purchase_record->pos_payment = $request->posed_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($cash && $bank) {

                $purchase_record->type_of_payment = 'bank+cash';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->bank_payment = $request->banked_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($cash && $pos) {
                $purchase_record->type_of_payment = 'pos+cash';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->pos_payment = $request->posed_amount;

                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($cash && $discount) {

                $purchase_record->type_of_payment = 'cash+discount';

                $purchase_record->cash_payment = $request->cashed_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {
                $purchase_record->type_of_payment = 'pos+bank';

                $purchase_record->bank_payment = $request->banked_amount;
                $purchase_record->pos_payment = $request->posed_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;
                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($bank && $discount) {

                $purchase_record->type_of_payment = 'bank+discount';

                $purchase_record->bank_payment = $request->banked_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($pos && $discount) {
                $purchase_record->type_of_payment = 'pos+discount';

                $purchase_record->pos_payment = $request->posed_amount;
                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;

                $invoice->pos_bank_id = $request->pos_bank;

                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($cash) {
                $purchase_record->type_of_payment = 'cash';

                $purchase_record->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $purchase_record->type_of_payment = 'bank';

                $purchase_record->bank_payment = $request->banked_amount;

                $purchase_record->bank_id = $request->bank;
                $purchase_record->bank_cheque = $request->bank_cheque;

                $purchase_record->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($pos) {
                $purchase_record->type_of_payment = 'pos';

                $purchase_record->pos_payment = $request->posed_amount;

                $purchase_record->pos_bank_id = $request->pos_bank;

                $purchase_record->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($discount) {
                $purchase_record->type_of_payment = 'discount';

                $purchase_record->discount_payment = $request->discount_amount;

                $purchase_record->discount_type = $request->discount_type;
                $purchase_record->discount_figure = $request->discount_figure;
            }
        } else {
            $purchase_record->type_of_payment = 'no_payment';
        }

        $purchase_record->shipping_bill_no = $request->shipping_bill_no;
        $purchase_record->date_of_shipping = $request->filled('date_of_shipping') ? date('Y-m-d', strtotime(str_replace('/', '-', $request->date_of_shipping))) : null;
        $purchase_record->code_of_shipping_port = $request->code_of_shipping_port;
        $purchase_record->conversion_rate = $request->conversion_rate;
        $purchase_record->currency_symbol = $request->currency_symbol;
        $purchase_record->export_type = $request->export_type;

        $purchase_record->consignee_info = $request->consignee_info;
        $purchase_record->consignor_info = $request->consignor_info;

        if ($request->has('gst_classification')) {
            for ($i = 0; $i < count($request->gst_classification); $i++) {
                if ($request->gst_classification[$i] == 'rcm') {
                    $purchase_record->gst_classification = 'rcm';
                }
            }
        } else {
            for ($i = 0; $i < count($request->item); $i++) {
                $item = Item::find($request->item[$i]);

                if (isset($item) && $item->item_under_rcm == "yes") {
                    $purchase_record->gst_classification = 'rcm';
                }
            }
        }

        if ($request->has("add_lump_sump") && $request->add_lump_sump == "yes") {
            $purchase_record->is_add_lump_sump = 1;
        } else {
            $purchase_record->is_add_lump_sump = 0;
        }

        if ($purchase_record->save()) {

            /**-------Purchase pending payments------*/
            $purchase_remaining_amount = new PurchaseRemainingAmount;

            $purchase_remaining_amount->party_id = $request->party;
            // $purchase_remaining_amount->bill_no = $request->bill_no;
            $purchase_remaining_amount->purchase_id = $purchase_record->id;
            $purchase_remaining_amount->total_amount = $request->total_amount;
            $purchase_remaining_amount->amount_paid = $request->amount_paid;
            $purchase_remaining_amount->amount_remaining = $request->amount_remaining;


            if ($request->has('type_of_payment')) {
                // if ($cash && $bank && $pos) {
                //     $purchase_remaining_amount->type_of_payment = 'combined';

                //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                //     $purchase_remaining_amount->bank_payment = $request->banked_amount;
                //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

                //     $purchase_remaining_amount->bank_id = $request->bank;
                //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                // } else if ($pos && $bank) {
                //     $purchase_remaining_amount->type_of_payment = 'pos+bank';

                //     $purchase_remaining_amount->bank_payment = $request->banked_amount;
                //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

                //     $purchase_remaining_amount->bank_id = $request->bank;
                //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                // } else if ($pos && $cash) {
                //     $purchase_remaining_amount->type_of_payment = 'pos+cash';

                //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

                //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                // } else if ($bank && $cash) {
                //     $purchase_remaining_amount->type_of_payment = 'bank+cash';

                //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                //     $purchase_remaining_amount->bank_payment = $request->banked_amount;

                //     $purchase_remaining_amount->bank_id = $request->bank;
                // } else if ($bank) {
                //     $purchase_remaining_amount->type_of_payment = 'bank';

                //     $purchase_remaining_amount->bank_payment = $request->banked_amount;

                //     $purchase_remaining_amount->bank_id = $request->bank;
                // } else if ($cash) {
                //     $purchase_remaining_amount->type_of_payment = 'cash';

                //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                // } else if ($pos) {
                //     $purchase_remaining_amount->type_of_payment = 'pos';

                //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

                //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                // }

                if ($cash && $bank && $pos && $discount) {

                    $purchase_remaining_amount->type_of_payment = 'combined';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->bank_payment = $request->banked_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;
                    $purchase_remaining_amount->discount_payment = $request->discount_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash && $bank && $pos) {

                    $purchase_remaining_amount->type_of_payment = 'cash+bank+pos';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->bank_payment = $request->banked_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash && $bank && $discount) {

                    $purchase_remaining_amount->type_of_payment = 'cash+bank+discount';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->bank_payment = $request->banked_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                    $purchase_remaining_amount->discount_payment = $request->discount_amount;
                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash && $discount && $pos) {

                    $purchase_remaining_amount->type_of_payment = 'cash+pos+discount';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($discount && $bank && $pos) {

                    $purchase_remaining_amount->type_of_payment = 'bank+pos+discount';

                    $purchase_remaining_amount->bank_payment = $request->banked_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;
                    $purchase_remaining_amount->discount_payment = $request->discount_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash && $bank) {

                    $purchase_remaining_amount->type_of_payment = 'bank+cash';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->bank_payment = $request->banked_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash && $pos) {

                    $purchase_remaining_amount->type_of_payment = 'pos+cash';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash && $discount) {

                    $purchase_remaining_amount->type_of_payment = 'cash+discount';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;

                    $purchase_remaining_amount->discount_payment = $request->discount_amount;
                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($bank && $pos) {

                    $purchase_remaining_amount->type_of_payment = 'pos+bank';

                    $purchase_remaining_amount->bank_payment = $request->banked_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($bank && $discount) {

                    $purchase_remaining_amount->type_of_payment = 'pos+bank';

                    $purchase_remaining_amount->bank_payment = $request->banked_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                    $purchase_remaining_amount->discount_payment = $request->discount_amount;
                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($pos && $discount) {

                    $purchase_remaining_amount->type_of_payment = 'pos+discount';

                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->discount_payment = $request->discount_amount;
                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($cash) {

                    $purchase_remaining_amount->type_of_payment = 'cash';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($bank) {

                    $purchase_remaining_amount->type_of_payment = 'bank';

                    $purchase_remaining_amount->bank_payment = $request->banked_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($pos) {

                    $purchase_remaining_amount->type_of_payment = 'pos';

                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                } else if ($discount) {

                    $purchase_remaining_amount->type_of_payment = 'discount';
                    $purchase_remaining_amount->discount_payment = $request->discount_amount;
                    $purchase_remaining_amount->discount_type = $request->discount_type;
                    $purchase_remaining_amount->discount_figure = $request->discount_figure;

                    $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                }
            } else {
                $purchase_remaining_amount->type_of_payment = 'no_payment';
            }

            $purchase_remaining_amount->is_original_payment = 1;

            $purchase_remaining_amount->save();

            /**-------purchase Items------- */

            $insertable_items = array();

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
            $barcodes = $request->item_barcode;
            $calculated_gsts = $request->calculated_gst;
            $gst_tax_types = $request->gst_tax_type;
            $measuring_unit = $request->measuring_unit;
            $free_qty = $request->free_quantity;
            $gst_classification = $request->gst_classification;
            $cesses = $request->cess_amount;

            for ($i = 0; $i < count($items); $i++) {
                $insertable_items[$i]['id'] = $items[$i];

                if (isset($quantities[$i])) {
                    $insertable_items[$i]['qty'] = $quantities[$i];
                } else {
                    $insertable_items[$i]['qty'] = 0;
                }

                if (isset($prices[$i])) {
                    $insertable_items[$i]['price'] = $prices[$i];
                } else {
                    $insertable_items[$i]['price'] = 0;
                }

                if (isset($amounts[$i])) {
                    $insertable_items[$i]['amount'] = $amounts[$i];
                } else {
                    $insertable_items[$i]['amount'] = 0;
                }

                if (isset($discount_types[$i])) {
                    $insertable_items[$i]['discount_type'] = $discount_types[$i];
                } else {
                    $insertable_items[$i]['discount_type'] = null;
                }

                if (isset($discounts[$i])) {
                    $insertable_items[$i]['discount'] = $discounts[$i];
                } else {
                    $insertable_items[$i]['discount'] = 0;
                }

                $insertable_items[$i]['barcode'] = $barcodes[$i];

                if (isset($calculated_gsts[$i])) {
                    $insertable_items[$i]['calculated_gst'] = $calculated_gsts[$i];
                } else {
                    $insertable_items[$i]['calculated_gst'] = 0;
                }

                $insertable_items[$i]['gst_tax_type'] = $gst_tax_types[$i];

                $insertable_items[$i]['measuring_unit'] = $measuring_unit[$i];

                if (isset($free_qty[$i])) {
                    $insertable_items[$i]['free_qty'] = $free_qty[$i];
                } else {
                    $insertable_items[$i]['free_qty'] = 0;
                }

                $insertable_items[$i]['gst_classification'] = isset($gst_classification[$i]) ? $gst_classification[$i] : null;

                $insertable_items[$i]['cess'] = isset($cesses[$i]) ? $cesses[$i] : null;
            }

            foreach ($insertable_items as $item) {
                $purchase = new Purchase;

                $purchased_item = Item::find($item['id']);

                $purchase->price = $item['price'];
                $purchase->item_total = $item['amount'];
                $purchase->item_tax_type = $item['gst_tax_type'];
                // $purchase->bill_no = $request->bill_no;
                $purchase->purchase_id = $purchase_record->id;
                $purchase->gst = $item['calculated_gst'];
                $purchase->gst_rate = $purchased_item->gst;
                $purchase->barcode = $item['barcode'];
                $purchase->bought_on = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
                $purchase->remark = $request->remark;
                $purchase->item_id = $item['id'];
                $purchase->party_id = $request->party;
                $purchase->discount_type = $item['discount_type'];
                $purchase->discount = $item['discount'];
                $purchase->gst_classification = $item['gst_classification'];
                $purchase->cess = $item['cess'];


                if (Session::has("item_extra" . $item['id'])) {

                    $manufacture = session("item_extra" . $item['id'] . '.manufacture');
                    $expiry = session("item_extra" . $item['id'] . '.expiry');
                    $batch = session("item_extra" . $item['id'] . '.batch');
                    $size = session("item_extra" . $item['id'] . '.size');
                    $pieces = session("item_extra" . $item['id'] . '.pieces');

                    $mtime = strtotime($manufacture);
                    $etime = strtotime($expiry);

                    $manufacture1 = date('Y-m-d', $mtime);
                    $expiry1 = date('Y-m-d', $etime);

                    $purchase->manufacture = $manufacture1;
                    $purchase->expiry = $expiry1;
                    $purchase->batch = $batch;
                    $purchase->size = $size;
                    $purchase->pieces = $pieces;
                }

                if (Session::has("short_rate" . $item['id'])) {

                    // $gross_rate = session("short_rate" . $item['id'] . '.gross_rate');
                    $short_rate = session("short_rate" . $item['id'] . '.short_rate');
                    $net_rate = session("short_rate" . $item['id'] . '.net_rate');

                    $purchase->short_rate = $short_rate;
                    $purchase->net_rate = $net_rate;
                }

                // if (Session::has("item_cess" . $item['id'])) {
                //     $purchase->cess = session("item_cess." . $item['id']);
                // }

                // return $purchase;

                if (isset($item['measuring_unit'])) {
                    if ($item['free_qty'] > 0) {
                        $qty = $item['qty'] + $item['free_qty'];
                    } else {
                        $qty = $item['qty'];
                    }


                    if ($item['measuring_unit'] == $purchased_item->measuring_unit) {
                        $purchased_item->qty = $purchased_item->qty + $qty;

                        $purchase->qty = $item['qty'];
                        $purchase->alt_qty = 0;
                        $purchase->comp_qty = 0;
                        $purchase->qty_type = 'base';
                    }

                    if ($item['measuring_unit'] == $purchased_item->alternate_measuring_unit) {

                        $alternate_to_base = $purchased_item->conversion_of_alternate_to_base_unit_value;

                        $original_qty = $qty * $alternate_to_base;

                        $purchased_item->qty = $purchased_item->qty + $original_qty;

                        $purchase->qty = $original_qty;
                        $purchase->alt_qty = $item['qty'];
                        $purchase->comp_qty = 0;
                        $purchase->qty_type = 'alternate';
                    }

                    if ($item['measuring_unit'] == $purchased_item->compound_measuring_unit) {

                        $alternate_to_base = $purchased_item->conversion_of_alternate_to_base_unit_value;
                        $compound_to_alternate = $purchased_item->conversion_of_alternate_to_base_unit_value;

                        $original_qty = $alternate_to_base * $compound_to_alternate * $qty;

                        $purchased_item->qty = $purchased_item->qty + $original_qty;

                        $purchase->qty = $original_qty;
                        $purchase->alt_qty = $compound_to_alternate * $qty;
                        $purchase->comp_qty = $item['qty'];
                        $purchase->qty_type = 'compound';
                    }
                    $purchase->item_measuring_unit = $item['measuring_unit'];
                } else {
                    if ($item['free_qty'] > 0) {
                        $qty = $item['qty'] + $item['free_qty'];
                    } else {
                        $qty = $item['qty'];
                    }

                    $purchased_item->qty = $purchased_item->qty + $qty;

                    $purchase->qty = $item['qty'];
                    $purchase->alt_qty = 0;
                    $purchase->comp_qty = 0;
                    $purchase->qty_type = 'base';
                }

                $purchase->free_qty = $item['free_qty'];

                $purchased_item->save();

                // $purchase->amount_paid = $request->amount_paid;
                // $purchase->amount_remaining = ($request->quantity * $request->price) - $request->amount_paid;
                // $purchase->amount_remaining = $request->amount_remaining;

                $purchase->save();

                // $duties_and_taxes = new DutiesAndTaxes;
                // $duties_and_taxes->igst = $purchased_item->igst;
                // $duties_and_taxes->cgst = $purchased_item->cgst;
                // $duties_and_taxes->sgst = $purchased_item->sgst;
                // $duties_and_taxes->gst = $purchased_item->gst;
                // $duties_and_taxes->purchase_id = $purchase->id;
                // $duties_and_taxes->type = 'purchase';
                // $duties_and_taxes->save();
            }

            // return $request->all();

            if ($request->submit_type == "save") {
                return redirect()->back()->with('success', 'Bill created successfully');
            } else if ($request->submit_type == "print") {
                return redirect(route('print.bill', $purchase_record->id));
            } else if ($request->submit_type == "email") {
                if ($party->email) {
                    Mail::to($party->email)->send(new SendBill($purchase_record->id));
                }
                return redirect()->back()->with('success', 'Bill created successfully');
            }
        } else {
            return redirect()->back()->with('failure', 'Failed to create Purchase');
        }

        // shipping_bill_no

        // date_of_shipping

        // code_of_shipping_port

        // conversion_rate

        // currency_symbol

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase = Purchase::findOrFail($id);

        // foreach($purchase->items as $item){
        //     $purchase->item_name = $item->name;
        // }

        return view('purchase.edit', compact('purchase'));
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
        $purchase = Purchase::findOrFail($id);

        $purchase_log = new PurchaseLog;

        $rest_qty = $request->qty - $purchase->qty;

        $purchase_log->qty = $purchase->qty;
        $purchase_log->price = $purchase->price;
        $purchase_log->bill_no = $purchase->bill_no;
        $purchase_log->igst = $purchase->igst;
        $purchase_log->cgst = $purchase->cgst;
        $purchase_log->gst = $purchase->gst;
        $purchase_log->bought_on = $purchase->bought_on;
        $purchase_log->amount_paid = $purchase->amount_paid;
        $purchase_log->amount_remaining = $purchase->amount_remaining;
        $purchase_log->item_id = $purchase->item_id;
        $purchase_log->party_id = $purchase->party_id;
        $purchase_log->purchase_id = $purchase->id;

        $purchase_log->save();

        $purchase->qty = $request->quantity;
        $purchase->bill_no = $request->bill_no;
        $purchase->price = $request->price;
        $purchase->igst = $request->igst;
        $purchase->cgst = $request->cgst;
        $purchase->sgst = $request->sgst;
        $purchase->gst = $request->gst;
        $purchase->bought_on = $request->date;
        $purchase->amount_paid = $request->amount_paid;
        // $purchase->amount_remaining = ($request->price * $request->qty) - $request->amount_paid;
        $purchase->amount_remaining = $request->amount_remaining;
        $purchase->remark = $request->remark;

        if ($purchase->save()) {
            // $duties_and_taxes = DutiesAndTaxes::where('purchase_id', $id)->first();
            // $duties_and_taxes->igst = $request->igst;
            // $duties_and_taxes->cgst = $request->cgst;
            // $duties_and_taxes->gst = $request->gst;
            // $duties_and_taxes->save();

            $purchase_item = Purchase_Item::where('item_id', $purchase->item_id)->first();
            $purchase_item->item_qty = $purchase_item->item_qty + $rest_qty;
            $purchase_item->save();
            return redirect()->back()->with('success', 'Purchase updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update purchase');
        }
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

    public function filter_by_date(Request $request)
    {
        $parties = Party::where('user_id', Auth::user()->id)->get();

        foreach ($parties as $party) {

            if (isset($request->from) && isset($request->to)) {
                $from = strtotime($request->from);
                $to = strtotime($request->to);

                $from = date('Y-m-d', $from);
                $to = date('Y-m-d', $to);

                $purchase_records[$party->id] = PurchaseRecord::where('party_id', $party->id)->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from, $to])->get();
            } else {
                $purchase_records[$party->id] = PurchaseRecord::where('party_id', $party->id)->where('type_of_bill', 'regular')->orderBy('bill_date', 'desc')->get();
            }

            foreach ($purchase_records[$party->id] as $record) {
                $record->party_name = $party->name;
            }
        }

        // return $purchase_records;

        return view('purchase.filter_by_date', compact('purchase_records'));
    }

    public function filter_by_party(Request $request)
    {
        if (!empty($request->party)) {
            $parties = Party::where('user_id', Auth::user()->id)->where('name', 'like', '%' . $request->party . '%')->get();

            foreach ($parties as $party) {
                $purchase = PurchaseRecord::where('party_id', $party->id)->get();

                $purchases = (object)$purchase;
            }
        } else {
            $purchases = User::find(Auth::user()->id)->purchases()->orderBy('created_at', 'desc')->get();
        }

        foreach ($purchases as $key => $purchase) {
            $party = Party::find($purchase->party_id);
            $purchase->party_name = $party->name;
        }

        return view('purchase.filter_by_party', compact('purchases'));
    }

    public function filter_by_bill(Request $request)
    {
        if (!empty($request->bill)) {
            $purchases = User::find(Auth::user()->id)->purchases()->where('purchase_records.bill_no', $request->bill)->get();
        } else {
            $purchases = User::find(Auth::user()->id)->purchases()->orderBy('created_at', 'desc')->get();
        }

        foreach ($purchases as $key => $purchase) {
            $party = Party::find($purchase->party_id);
            $purchase->party_name = $party->name;
        }

        return view('purchase.filter_by_party', compact('purchases'));
    }

    public function show_purchase_bill($bill_no)
    {

        $purchase = Purchase::find($bill_no);

        $item = Item::find($purchase->item_id);

        // return $purchase;
        return view('purchase.show_purchase_bill', compact('purchase', 'item'));
    }

    public function create_purchase_order()
    {
        $items = Item::where('user_id', Auth::user()->id)->get();
        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'creditor')->get();
        $groups = Group::where('user_id', Auth::user()->id)->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        $last_purchase_order = User::find(Auth::user()->id)->purchaseOrder()->orderBy('id', 'desc')->first();

        $order_no = null;
        $myerrors = array();

        if (isset(auth()->user()->purchaseOrderSetting) && auth()->user()->purchaseOrderSetting->bill_no_type == 'auto') {

            if (isset($last_purchase_order->token)) {
                $width = isset(auth()->user()->purchaseOrderSetting) ? auth()->user()->purchaseOrderSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['token_no'][] = 'Invalid Max-length provided. Please update your purchase order settings';
                        break;
                    case 1:
                        if ($last_purchase_order->token > 9) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 2:
                        if ($last_purchase_order->token > 99) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 3:
                        if ($last_purchase_order->token > 999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 4:
                        if ($last_purchase_order->token > 9999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 5:
                        if ($last_purchase_order->token > 99999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 6:
                        if ($last_purchase_order->token > 999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 7:
                        if ($last_purchase_order->token > 9999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 8:
                        if ($last_purchase_order->token > 99999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                    case 9:
                        if ($last_purchase_order->token > 999999999) {
                            $myerrors['token_no'][] = 'Max-length exceeded for purchase order no. Please update your purchase order settings';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->purchaseOrderSetting) && auth()->user()->purchaseOrderSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->purchaseOrderSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->purchaseOrderSetting) && auth()->user()->purchaseOrderSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->purchaseOrderSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->purchaseOrderSetting) && auth()->user()->purchaseOrderSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->purchaseOrderSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['token_no'][] = 'Applicable date expired for your purchase order series. Please update your purchase order settings';
            }

            if ($last_purchase_order) {
                if (isset($last_purchase_order->voucher_no_type) && $last_purchase_order->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->purchaseOrderSetting->updated_at) > \Carbon\Carbon::parse($last_purchase_order->created_at)) {
                        $order_no = isset(auth()->user()->purchaseOrderSetting->starting_no) ? auth()->user()->purchaseOrderSetting->starting_no - 1 : 0;
                    } else {
                        $order_no = ($last_purchase_order->token == '' || $last_purchase_order->token == null) ? 0 : $last_purchase_order->token;
                    }
                } else {
                    $order_no = isset(auth()->user()->purchaseOrderSetting->starting_no) ? auth()->user()->purchaseOrderSetting->starting_no - 1 : 0;
                }
            } else {
                $order_no = isset(auth()->user()->purchaseOrderSetting->starting_no) ? auth()->user()->purchaseOrderSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('purchase.order.create', compact('items', 'parties', 'groups', 'banks', 'order_no'))->with('myerrors', $myerrors);
    }

    public function store_purchase_order(Request $request)
    {
        $insertable_items = array();

        $party = Party::findOrFail($request->party);

        $items = $request->item;
        $quantities = $request->quantity;
        $units = $request->measuring_unit;
        $reference_name = $request->reference_name;
        $rates = $request->rate;
        $values = $request->value;
        // $purchase_token = rand(0, 99) . rand(0, 99) . rand(0, 99) . rand(0, 99) . rand(0, 99);

        if ($request->purchase_token != '') {
            $purchase_token = $request->purchase_token;
        } else {
            $purchase_token = md5(rand(0, 99) . rand(0, 99) . rand(0, 99) . rand(0, 99) . rand(0, 99) . time());
        }

        $ptime = strtotime(str_replace('/', '-', $request->purchase_order_date));

        $purchase_date = date('Y-m-d', $ptime);

        for ($i = 0; $i < count($items); $i++) {
            $insertable_items[$i]['id'] = $items[$i];
            $insertable_items[$i]['qty'] = $quantities[$i];
            $insertable_items[$i]['units'] = $units[$i];
            $insertable_items[$i]['rate'] = $rates[$i];
            $insertable_items[$i]['value'] = $values[$i];
        }

        foreach ($insertable_items as $item) {
            $purchase_order = new PurchaseOrder;

            if (isset(auth()->user()->purchaseOrderSetting) && isset(auth()->user()->purchaseOrderSetting->bill_no_type)) {
                $purchase_order->voucher_no_type = auth()->user()->purchaseOrderSetting->bill_no_type;
            } else {
                $purchase_order->voucher_no_type = 'manual';
            }

            $purchase_order->qty = $item['qty'];
            $purchase_order->unit = isset($item['unit']) ? $item['unit'] : '';
            $purchase_order->rate = $item['rate'];
            $purchase_order->value = $item['value'];
            $purchase_order->party_id = $request->party;
            $purchase_order->item_id = $item['id'];
            $purchase_order->date = $purchase_date;
            $purchase_order->token = $purchase_token;
            $purchase_order->reference_name = $reference_name;

            if ($request->filled('cashed_amount')) {
                $purchase_order->cash_amount = $request->cashed_amount;
            }
            if ($request->filled('banked_amount')) {
                $purchase_order->bank_id = $request->bank;
                $purchase_order->bank_amount = $request->banked_amount;
                $purchase_order->bank_cheque = $request->bank_cheque;

                $purchase_order->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            }
            if ($request->filled('posed_amount')) {
                $purchase_order->pos_amount = $request->posed_amount;

                $purchase_order->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            }

            if ($request->filled('total_amount')) {
                $purchase_order->total_amount = $request->total_amount;
            } else {
                $purchase_order->total_amount = 0;
            }

            if ($request->filled('amount_received')) {
                $purchase_order->amount_received = $request->amount_received;
            } else {
                $purchase_order->amount_received = 0;
            }

            if ($request->filled('amount_remaining')) {
                $purchase_order->amount_remaining = $request->amount_remaining;
            } else {
                $purchase_order->amount_remaining = 0;
            }

            $purchase_order->narration = $request->narration;

            $purchase_order->user_id = Auth::user()->id;

            $purchase_order->save();
        }

        if ($request->has("submit_type") && $request->submit_type == "save") {
            return redirect()->back()->with('success', 'Purchase order created successfully');
        }

        if ($request->has("submit_type") && $request->submit_type == "save_and_print") {
            return redirect(route('print.purchase.order', $purchase_order->token));
        }

        if ($request->has("submit_type") && $request->submit_type == "save_and_send") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendPurchaseOrder($purchase_order->token));
            }
            return redirect()->back()->with('success', 'Purchase order created successfully');
        }

        // return redirect()->back()->with('success', 'Purchase Order Saved with ' . $purchase_order->token . '. Please save this number for future reference.');
    }

    public function create_purchase_from_order($purchase_order_no)
    {
        $purchase_orders = PurchaseOrder::where('token', $purchase_order_no)->where('user_id', Auth::user()->id)->get();

        $party_id = PurchaseOrder::where('token', $purchase_order_no)->where('user_id', Auth::user()->id)->value('party_id');

        $reference_name = PurchaseOrder::where('token', $purchase_order_no)->where('user_id', Auth::user()->id)->value('reference_name');

        $involved_party = Party::find($party_id);

        $myerrors = collect();

        if (isset($involved_party)) {
            $state = State::find($involved_party->billing_state);
            $involved_party->billing_state = $state->name ?? '';
        }

        $parties = Party::where('user_id', Auth::user()->id)->get();
        $transporters = Transporter::where('user_id', Auth::user()->id)->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();
        $groups = Group::all();
        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        foreach ($purchase_orders as $order) {
            $item = Item::find($order->item_id);

            $order->item_name = $item->name;
            $order->group_id = $item->group_id;
            $order->gst_percent = $item->gst;
            $order->item_barcode = $item->barcode;
            $order->measuring_unit = $item->measuring_unit ?? null;
            $order->alternate_unit = $item->alternate_measuring_unit ?? null;
            $order->compound_unit = $item->compound_measuring_unit ?? null;
        }

        $type_of_payment = 'no_payment';
        $cash_payment = 0;
        $bank_payment = 0;
        $pos_payment = 0;
        $discount_payment = 0;


        $cash = $purchase_orders->first()->cash_amount ?? false;
        $bank = $purchase_orders->first()->bank_amount ?? false;
        $pos = $purchase_orders->first()->pos_amount ?? false;
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

        return view('purchase.create', compact('purchase_orders', 'groups', 'parties', 'purchase_order_no', 'transporters', 'banks', 'involved_party', 'reference_name', 'user_profile', 'type_of_payment', 'myerrors'));
    }


    public function find_purchase_by_party()
    {
        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'creditor')->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        $last_payment_party = User::find(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->orderBy('id', 'desc')->first();

        $last_payment_purchase = User::find(Auth::user()->id)->purchaseRemainingAmounts()->orderBy('id', 'desc')->first();

        if ($last_payment_purchase && $last_payment_party) {
            if (\Carbon\Carbon::parse($last_payment_purchase->created_at) > \Carbon\Carbon::parse($last_payment_party->created_at)) {
                $last_payment = $last_payment_purchase;
            } else {
                $last_payment = $last_payment_party;
            }
        }
        else if($last_payment_purchase) {
            $last_payment = $last_payment_purchase;
        }
        else if($last_payment_party) {
            $last_payment = $last_payment_party;
        }
        else {
            $last_payment = null;
        }

        $voucher_no = null;
        $myerrors = array();

        if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->bill_no_type == 'auto') {

            if ($last_payment && isset($last_payment->voucher_no)) {
                $width = isset(auth()->user()->paymentSetting) ? auth()->user()->paymentSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['voucher_no'][] = 'Invalid Max-length provided. Please update your payment settings.';
                        break;
                    case 1:
                        if ($last_payment->token > 9) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 2:
                        if ($last_payment->token > 99) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 3:
                        if ($last_payment->token > 999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 4:
                        if ($last_payment->token > 9999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 5:
                        if ($last_payment->token > 99999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 6:
                        if ($last_payment->token > 999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 7:
                        if ($last_payment->token > 9999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 8:
                        if ($last_payment->token > 99999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 9:
                        if ($last_payment->token > 999999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->paymentSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->paymentSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->paymentSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['voucher_no'][] = 'Applicable date expired for your payment voucher no. Please update your payment settings.';
            }

            if ($last_payment) {
                if (isset($last_payment->voucher_no_type) && $last_payment->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->paymentSetting->updated_at) > \Carbon\Carbon::parse($last_payment->created_at)) {
                        $voucher_no = isset(auth()->user()->paymentSetting->starting_no) ? auth()->user()->paymentSetting->starting_no - 1 : 0;
                    } else {
                        $voucher_no = ($last_payment->voucher_no == '' || $last_payment->voucher_no == null) ? 0 : $last_payment->voucher_no;
                    }
                } else {
                    $voucher_no = isset(auth()->user()->paymentSetting->starting_no) ? auth()->user()->paymentSetting->starting_no - 1 : 0;
                }
            } else {
                $voucher_no = isset(auth()->user()->paymentSetting->starting_no) ? auth()->user()->paymentSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('purchase.find_bill_by_party', compact('banks', 'parties', 'voucher_no'))->with('myerrors', $myerrors);
    }

    public function post_find_purchase_by_party(Request $request)
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

        $purchases = PurchaseRecord::where('party_id', $party->id)->where('type_of_bill', 'regular')->whereBetween('bill_date',[$from_date, $to_date])->get();
        $paid_amounts = PurchaseRemainingAmount::where('party_id', $party->id)->where('is_original_payment', 0)->where('status', 1)->whereBetween('payment_date',[$from_date, $to_date])->get();
        $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'purchase')->where('status', 1)->whereBetween('payment_date',[$from_date, $to_date])->get();
        $purchase_orders = PurchaseOrder::where('party_id', $party->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->groupBy('token')->get();
        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'purchase')
                ->where(function ($query) {
                    $query->where('debit_notes.reason', 'purchase_return')->orWhere('debit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('debit_notes.reason', 'discount_on_purchase');
                })
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();
        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'purchase')->where('credit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();

        $total = 0;
        foreach ($purchases as $purchase) {
            // $debit_notes = DebitNote::where('bill_no', $purchase->id)->where('status', 1)->where('type', 'purchase')->whereIn('reason', ['purchase_return', 'new_rate_or_discount_value_with_gst', 'discount_on_purchase'])->get();

            // $credit_notes = CreditNote::where('invoice_id', $purchase->id)->where('status', 1)->where('type', 'purchase')->whereIn('reason', ['new_rate_or_discount_value_with_gst'])->get();
            $total += $purchase->total_amount;
            $total -= $purchase->bank_payment ?? 0;
            $total -= $purchase->pos_payment ?? 0;
            $total -= $purchase->cash_payment ?? 0;
            $total -= $purchase->discount_payment ?? 0;
        }

        foreach ($paid_amounts as $amount) {
            $total -= $amount->amount_paid;
        }

        foreach ($party_paid_amounts as $amount) {
            $total -= $amount->amount;
        }
        
        foreach ($purchase_orders as $order) {
            $total -= $order->amount_received;
        }

        foreach ($debit_notes as $note) {
            $total -= $note->note_value;
        }

        foreach ($credit_notes as $note) {
            $total += $note->note_value;
        }

        $total += $party->opening_balance;
        $total += $this->calculate_creditor_party_closing_balance($party, $closing_balance_from_date, $closing_balance_to_date);

        foreach ($purchases as $record) {
            $remaining_amount_data = PurchaseRemainingAmount::where('purchase_id', $record->id)->orderBy('id', 'desc')->first();

            $record->remaining_amount = $remaining_amount_data;
        }

        return response()->json(['purchase' => $purchases, 'total_pending' => $total, 'from_date' => $from_date, 'to_date' => $to_date]);
    }

    private function calculate_creditor_party_closing_balance($party, $from_date, $to_date)
    {
        $from_date = \Carbon\Carbon::parse($from_date)->format('Y-m-d');
        $to_date = \Carbon\Carbon::parse($to_date)->format('Y-m-d');
        
        // $isDatesSame = false;
        // if($from_date->eq($to_date)){
        //     $isDatesSame = true;
        // }

        $closing_balance = 0;

        $purchases = PurchaseRecord::where('party_id', $party->id)
            ->where('type_of_bill', 'regular')
            ->whereBetween('bill_date', [$from_date, $to_date])
            ->get();


        $paid_amounts = PurchaseRemainingAmount::where('party_id', $party->id)
            ->where('is_original_payment', 0)
            ->where('status', 1)
            ->whereBetween('payment_date', [$from_date, $to_date])
            ->get();


        $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
            ->where('type', 'purchase')
            ->where('status', 1)
            ->whereBetween('payment_date', [$from_date, $to_date])
            ->get();

        $purchase_orders = PurchaseOrder::where('party_id', $party->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'purchase')
                ->where(function ($query) {
                    $query->where('debit_notes.reason', 'purchase_return')->orWhere('debit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('debit_notes.reason', 'discount_on_purchase');
                })
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();
            

        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'purchase')->where('credit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();


        foreach ($purchases as $purchase) {
            $closing_balance += $purchase->total_amount;

            $closing_balance -= $purchase->bank_payment ?? 0;
            $closing_balance -= $purchase->pos_payment ?? 0;
            $closing_balance -= $purchase->cash_payment ?? 0;
            $closing_balance -= $purchase->discount_payment ?? 0;
        }

        foreach ($paid_amounts as $amount) {
            $closing_balance -= $amount->amount_paid;  
        }

        foreach ($party_paid_amounts as $amount) {
            $closing_balance -= $amount->amount;
        }

        foreach ($purchase_orders as $order) {
            $closing_balance += $order->amount_received;
        }

        foreach( $debitNotes as $debitNote ){
            $closing_balance -= $debitNote->note_value;
        }

        foreach ($creditNotes as $creditNote) {
            $closing_balance += $creditNote->note_value;
        }

        return $closing_balance;
    }

    public function get_purchase_bill($bill, $party)
    {

        $party_id = $party;
        $bill_id = $bill;

        $associated_party = Party::find($party_id);

        $purchase_amounts = PurchaseRemainingAmount::where(['purchase_id' => $bill_id, 'party_id' => $party_id])->get();

        // $debit_notes = DebitNote::where('bill_no', $bill)->where('type', 'purchase')
        // ->where(function ($query) {
        //     $query->where('reason', 'purchase_return')->orWhere('reason', 'new_rate_or_discount_value_with_gst')->orWhere('reason', 'discount_on_purchase');
        // })
        // ->get();

        // $credit_notes = CreditNote::where('invoice_id', $bill)->where('type', 'purchase')->where('reason', 'new_rate_or_discount_value_with_gst')->get();

        // return $credit_notes;
        // return $debit_notes;

        $purchased_amount = PurchaseRecord::where(['id' => $bill_id, 'party_id' => $party_id])->first();

        $total_amount = $purchased_amount->total_amount;

        $bill_no = $purchased_amount->bill_no;
        $bill_date = $purchased_amount->bill_date;

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        foreach ($purchase_amounts as $purchase) {
            if ($purchase->type_of_payment == 'bank') {
                $bank = Bank::find($purchase->bank_id);

                $purchase->bank_name = $bank->name;
                $purchase->bank_branch = $bank->branch;
            }
        }

        // $last_payment = User::find(Auth::user()->id)->purchaseRemainingAmounts()->orderBy('id', 'desc')->first();

        $last_payment_party = User::find(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->orderBy('id', 'desc')->first();

        $last_payment_purchase = User::find(Auth::user()->id)->purchaseRemainingAmounts()->orderBy('id', 'desc')->first();

        if ($last_payment_purchase && $last_payment_party) {
            if (\Carbon\Carbon::parse($last_payment_purchase->created_at) > \Carbon\Carbon::parse($last_payment_party->created_at)) {
                $last_payment = $last_payment_purchase;
            } else {
                $last_payment = $last_payment_party;
            }
        } 
        else if($last_payment_purchase) {
            $last_payment = $last_payment_purchase;
        }
        else if($last_payment_party) {
            $last_payment = $last_payment_party;
        }
        else {
            $last_payment = null;
        }

        $voucher_no = null;
        $myerrors = array();

        if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->bill_no_type == 'auto') {

            if ($last_payment && isset($last_payment->voucher_no)) {
                $width = isset(auth()->user()->paymentSetting) ? auth()->user()->paymentSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['voucher_no'][] = 'Invalid Max-length provided. Please update your payment settings.';
                        break;
                    case 1:
                        if ($last_payment->token > 9) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 2:
                        if ($last_payment->token > 99) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 3:
                        if ($last_payment->token > 999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 4:
                        if ($last_payment->token > 9999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 5:
                        if ($last_payment->token > 99999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 6:
                        if ($last_payment->token > 999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 7:
                        if ($last_payment->token > 9999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 8:
                        if ($last_payment->token > 99999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                    case 9:
                        if ($last_payment->token > 999999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your payment settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->paymentSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->paymentSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->paymentSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['voucher_no'][] = 'Applicable date expired for your payment voucher no. Please update your payment settings.';
            }

            if ($last_payment) {
                if (isset($last_payment->voucher_no_type) && $last_payment->voucher_no_type == 'auto') {
                    if (\Carbon\Carbon::parse(auth()->user()->paymentSetting->updated_at) > \Carbon\Carbon::parse($last_payment->created_at)) {
                        $voucher_no = isset(auth()->user()->paymentSetting->starting_no) ? auth()->user()->paymentSetting->starting_no - 1 : 0;
                    } else {
                        $voucher_no = ($last_payment->voucher_no == '' || $last_payment->voucher_no == null) ? 0 : $last_payment->voucher_no;
                    }
                } else {
                    $voucher_no = isset(auth()->user()->paymentSetting->starting_no) ? auth()->user()->paymentSetting->starting_no - 1 : 0;
                }
            } else {
                $voucher_no = isset(auth()->user()->paymentSetting->starting_no) ? auth()->user()->paymentSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('purchase.get_purchase_bill', compact('purchase_amounts', 'associated_party', 'bill', 'total_amount', 'banks', 'bill_no', 'bill_date', 'voucher_no'))->with('myerrors', $myerrors);
    }

    public function post_pending_payment(Request $request)
    {

        // return response()->json($request);

        $purchase_remaining_amount = new PurchaseRemainingAmount;

        $purchase_remaining_amount->party_id = $request->party;
        // $purchase_remaining_amount->bill_no = $request->bill_no;
        $purchase_remaining_amount->purchase_id = $request->bill_id;
        $purchase_remaining_amount->total_amount = $request->total_amount;

        $is_voucher_valid = $this->validate_payment_voucher_no($request->voucher_no, auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to);

        if(!$is_voucher_valid) {
            return redirect()->back()->with('failure', "Please provide unique voucher no");
        }


        if ($request->has('voucher_no')) {
            $purchase_remaining_amount->voucher_no = $request->voucher_no;
        }

        if (isset(auth()->user()->paymentSetting) && isset(auth()->user()->paymentSetting->bill_no_type)) {
            $purchase_remaining_amount->voucher_no_type = auth()->user()->paymentSetting->bill_no_type;
        } else {
            $purchase_remaining_amount->voucher_no_type = 'manual';
        }

        if ($request->has('tds_income_tax')) {
            $purchase_remaining_amount->tds_income_tax = $request->tds_income_tax;
        }

        if ($request->has('tds_gst')) {
            $purchase_remaining_amount->tds_gst = $request->tds_gst;
        }

        if ($request->has('tcs_income_tax')) {
            $purchase_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
        }

        if ($request->has('tcs_gst')) {
            $purchase_remaining_amount->tcs_gst = $request->tcs_gst;
        }

        if ($request->has('payment_date')) {
            $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));
        }

        $purchase_remaining_amount->amount_remaining = $request->amount_remaining;
        // $purchase_remaining_amount->type_of_payment = $request->type_of_payment;

        // if ($request->type_of_payment == 'bank') {
        //     $purchase_remaining_amount->bank_id = $request->bank_id;
        //     $purchase_remaining_amount->bank_payment = $request->amount_paid;
        //     $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
        // }
        // if($request->type_of_payment == 'cash') {
        //     $purchase_remaining_amount->cash_payment = $request->amount_paid;
        // }
        // if ($request->type_of_payment == 'pos') {
        //     $purchase_remaining_amount->pos_payment = $request->amount_paid;
        //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank_id;
        // }


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
        //         $purchase_remaining_amount->type_of_payment = 'combined';

        //         $purchase_remaining_amount->cash_payment = $request->cashed_amount;
        //         $purchase_remaining_amount->bank_payment = $request->banked_amount;
        //         $purchase_remaining_amount->pos_payment = $request->posed_amount;

        //         $purchase_remaining_amount->bank_id = $request->bank;
        //         $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
        //         $purchase_remaining_amount->pos_bank_id = $request->pos_bank;


        //     } else if ($bank && $pos) {
        //         $purchase_remaining_amount->type_of_payment = 'pos+bank';

        //         $purchase_remaining_amount->bank_payment = $request->banked_amount;
        //         $purchase_remaining_amount->pos_payment = $request->posed_amount;

        //         $purchase_remaining_amount->bank_id = $request->bank;
        //         $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
        //         $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

        //     } else if ($cash && $pos) {
        //         $purchase_remaining_amount->type_of_payment = 'pos+cash';

        //         $purchase_remaining_amount->cash_payment = $request->cashed_amount;
        //         $purchase_remaining_amount->pos_payment = $request->posed_amount;

        //         $purchase_remaining_amount->pos_bank_id = $request->pos_bank;


        //     } else if ($cash && $bank) {
        //         $purchase_remaining_amount->type_of_payment = 'bank+cash';

        //         $purchase_remaining_amount->cash_payment = $request->cashed_amount;
        //         $purchase_remaining_amount->bank_payment = $request->banked_amount;

        //         $purchase_remaining_amount->bank_id = $request->bank;
        //         $purchase_remaining_amount->bank_cheque = $request->bank_cheque;


        //     } else if ($bank) {
        //         $purchase_remaining_amount->type_of_payment = 'bank';

        //         $purchase_remaining_amount->bank_payment = $request->banked_amount;

        //         $purchase_remaining_amount->bank_id = $request->bank;
        //         $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

        //     } else if ($cash) {
        //         $purchase_remaining_amount->type_of_payment = 'cash';

        //         $purchase_remaining_amount->cash_payment = $request->cashed_amount;

        //     } else if ($pos) {
        //         $purchase_remaining_amount->type_of_payment = 'pos';

        //         $purchase_remaining_amount->pos_payment = $request->posed_amount;

        //         $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

        //     }
        // } else {
        //     $purchase_remaining_amount->type_of_payment = 'no_payment';
        // }



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
                $purchase_remaining_amount->type_of_payment = 'combined';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->cashed_amount + $request->banked_amount + $request->posed_amount) - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'bank+pos+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->banked_amount + $request->posed_amount) - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'cash+pos+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->cashed_amount + $request->posed_amount) - $request->discounted_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $purchase_remaining_amount->type_of_payment = 'bank+cash+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->cashed_amount + $request->banked_amount) - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $purchase_remaining_amount->type_of_payment = 'bank+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->banked_amount - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $purchase_remaining_amount->type_of_payment = 'cash+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount - $request->discounted_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'pos+discount';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->posed_amount - $request->discounted_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $purchase_remaining_amount->type_of_payment = 'discount';

                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = 0 - $request->discounted_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $purchase_remaining_amount->type_of_payment = 'cash+bank+pos';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $purchase_remaining_amount->type_of_payment = 'bank+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->cashed_amount + $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $purchase_remaining_amount->type_of_payment = 'pos+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $purchase_remaining_amount->type_of_payment = 'pos+bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->banked_amount + $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $purchase_remaining_amount->type_of_payment = 'bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $purchase_remaining_amount->type_of_payment = 'pos';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $purchase_remaining_amount->type_of_payment = 'cash';

                $purchase_remaining_amount->pos_payment = $request->cashed_amount;

                // $amount = $request->cashed_amount;
            }
        } else {
            $purchase_remaining_amount->type_of_payment = 'no_payment';
        }

        $purchase_remaining_amount->amount_paid = $request->amount_paid;

        if ($purchase_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Data uploaded successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to upload data');
        }
    }

    public function view_pending_payable(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        // $purchase_amounts = auth()->user()->purchaseRemainingAmounts()->whereBetween('payment_date', [$from_date, $to_date])->get();
        // $account_amounts = auth()->user()->partyRemainingAmounts()->where('type', 'purchase')->whereBetween('payment_date', [$from_date, $to_date])->get();

        $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'creditor')->get();
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('purchase.view_pending_payable', compact('parties', 'banks'));
    }

    public function get_pending_payable(Request $request)
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }
        $purchase_amounts = auth()->user()->purchaseRemainingAmounts()->where('purchase_remaining_amounts.party_id', $request->selected_party)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->get();
        $account_amounts = auth()->user()->partyRemainingAmounts()->where('party_pending_payment_account.party_id', $request->selected_party)->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->get();

        foreach($purchase_amounts as $amount) {
            if($amount->is_original_payment == 1) {
                $purchase = PurchaseRecord::find($amount->purchase_id);
                if($purchase) {
                    $amount->voucher_no = $purchase->bill_no;
                    $amount->voucher_url = route('edit.bill.form', $purchase->id);
                }
            }
        }

        return response()->json(['bill' => $purchase_amounts, 'account' => $account_amounts]);
    }

    public function update_purchase_pending_payable_status(Request $request, $id)
    {
        $payment = PurchaseRemainingAmount::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();
        return redirect()->back();
    }

    public function update_party_purchase_pending_payable_status(Request $request, $id)
    {
        $payment = PartyPendingPaymentAccount::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();
        return redirect()->back();
    }

    public function view_pending_payable_detail($id) {
        $payment = PurchaseRemainingAmount::findOrFail($id);
        $banks = Bank::all();
        $type="purchase";
        return view('payments.pending_payments', compact('payment', 'banks', 'type'));
    }

    public function view_party_pending_payable_detail($id) {
        $payment = PartyPendingPaymentAccount::findOrFail($id);
        $banks = Bank::all();
        $type="purchase";
        return view('payments.party_pending_payments', compact('payment', 'banks', 'type'));
    }

    public function update_pending_payable_detail(Request $request, $id)
    {
        $purchase_remaining_amount = PurchaseRemainingAmount::findOrFail($id);

        $purchase_remaining_amount->party_id = $request->party;
        // $purchase_remaining_amount->bill_no = $request->bill_no;
        $purchase_remaining_amount->purchase_id = $request->bill_id;
        $purchase_remaining_amount->total_amount = $request->total_amount;


        if ($request->has('voucher_no')) {
            $purchase_remaining_amount->voucher_no = $request->voucher_no;
        }

        if ($request->has('tds_income_tax')) {
            $purchase_remaining_amount->tds_income_tax = $request->tds_income_tax;
        }

        if ($request->has('tds_gst')) {
            $purchase_remaining_amount->tds_gst = $request->tds_gst;
        }

        if ($request->has('tcs_income_tax')) {
            $purchase_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
        }

        if ($request->has('tcs_gst')) {
            $purchase_remaining_amount->tcs_gst = $request->tcs_gst;
        }

        if ($request->has('payment_date')) {
            $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));
        }

        $purchase_remaining_amount->amount_remaining = $request->amount_remaining;

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
                $purchase_remaining_amount->type_of_payment = 'combined';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->cashed_amount + $request->banked_amount + $request->posed_amount) - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'bank+pos+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->banked_amount + $request->posed_amount) - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'cash+pos+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->cashed_amount + $request->posed_amount) - $request->discounted_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $purchase_remaining_amount->type_of_payment = 'cash+bank+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = ($request->cashed_amount + $request->banked_amount) - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $purchase_remaining_amount->type_of_payment = 'bank+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->banked_amount - $request->discounted_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $purchase_remaining_amount->type_of_payment = 'cash+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->cashed_amount - $request->discounted_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'pos+discount';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = $request->posed_amount - $request->discounted_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $purchase_remaining_amount->type_of_payment = 'discount';

                $purchase_remaining_amount->discount_payment = $request->discounted_amount;

                // $amount = 0 - $request->discounted_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {
                $purchase_remaining_amount->type_of_payment = 'cash+bank+pos';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->banked_amount + $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank) {
                $purchase_remaining_amount->type_of_payment = 'bank+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->cashed_amount + $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $purchase_remaining_amount->type_of_payment = 'pos+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->cashed_amount + $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $pos) {
                $purchase_remaining_amount->type_of_payment = 'pos+bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->banked_amount + $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank) {
                $purchase_remaining_amount->type_of_payment = 'bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                // $amount = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $purchase_remaining_amount->type_of_payment = 'pos';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                // $amount = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $purchase_remaining_amount->type_of_payment = 'cash';

                $purchase_remaining_amount->pos_payment = $request->cashed_amount;

                // $amount = $request->cashed_amount;
            }
        } else {
            $purchase_remaining_amount->type_of_payment = 'no_payment';
        }

        $purchase_remaining_amount->amount_paid = $request->amount_paid;

        if ($purchase_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function update_party_pending_payable_detail(Request $request, $id)
    {
        $pending_payment = PartyPendingPaymentAccount::findOrFail($id);

        $pending_payment->party_id = $request->party_id;

        $amount = 0;

        $pending_payment->remarks = $request->remarks;

        $pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));


        if ($request->has('voucher_no')) {
            $pending_payment->voucher_no = $request->voucher_no;
        }

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

                $amount = ($request->cashed_amount + $request->banked_amount + $request->posed_amount) - $request->discounted_amount;

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

                $amount = ($request->banked_amount + $request->posed_amount) - $request->discounted_amount;

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

                $amount = ($request->cashed_amount + $request->posed_amount) - $request->discounted_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $discount) {
                $pending_payment->type_of_payment = 'cash+bank+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = ($request->cashed_amount + $request->banked_amount) - $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($bank && $discount) {
                $pending_payment->type_of_payment = 'bank+discount';

                $pending_payment->bank_payment = $request->banked_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->banked_amount - $request->discounted_amount;

                $pending_payment->bank_id = $request->bank;
                $pending_payment->bank_cheque = $request->bank_cheque;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($cash && $discount) {
                $pending_payment->type_of_payment = 'cash+discount';

                $pending_payment->cash_payment = $request->cashed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->cashed_amount - $request->discounted_amount;

                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $pending_payment->type_of_payment = 'pos+discount';

                $pending_payment->pos_payment = $request->posed_amount;
                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = $request->posed_amount - $request->discounted_amount;

                $pending_payment->pos_bank_id = $request->pos_bank;
                $pending_payment->discount_type = $request->discount_type;
                $pending_payment->discount_figure = $request->discount_figure;
            } else if ($discount) {
                $pending_payment->type_of_payment = 'discount';

                $pending_payment->discount_payment = $request->discounted_amount;

                $amount = 0 - $request->discounted_amount;

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

        $pending_payment->type = "purchase";

        if ($pending_payment->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
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

        $purchase_records = User::findOrFail(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->OrderBy('id', 'desc')->get();

        foreach ($purchase_records as $record) {
            $debit_notes = DebitNote::where('bill_no', $record->id)->where('type', 'purchase')->first();
            $credit_notes = CreditNote::where('invoice_id', $record->id)->where('type', 'purchase')->first();
            $record->bill_note = BillNote::where('bill_no', $record->id)->where('type', 'purchase')->get();

            if ($debit_notes) {
                $record->hasDebitNote = true;
                $record->debit_note_no = $debit_notes->note_no;
            } else {
                $record->hasDebitNote = false;
            }

            if ($credit_notes) {
                $record->hasCreditNote = true;
                $record->credit_note_no = $credit_notes->note_no;
            } else {
                $record->hasCreditNote = false;
            }

            if (count($record->bill_note) > 0) {
                $record->hasBillNote = true;
            } else {
                $record->hasBillNote = false;
            }
        }

        // return $purchase_records;

        return view('purchase.note', compact('purchase_records'));
    }

    public function bill_detail_debit_note($bill_no)
    {

        // $debitNote = DebitNote::where('bill_no', $bill_no)->where('type', 'purchase')->first();

        // $note_no = $debitNote->note_no;
        // $note_date = Carbon::parse($debitNote->created_at)->format('d/m/Y');

        // $debitNotes = DebitNote::where('bill_no', $bill_no)->where('type', 'purchase')->get();

        // return $debitNotes;

        $purchase = PurchaseRecord::findOrFail($bill_no);

        // $bill = $bill_no;

        // foreach($debitNotes as $note){
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

        return view('purchase.detail_note', compact('purchase', 'note_no'))->with('myerrors', $myerrors);
    }

    public function bill_create_debit_note(Request $request, $bill_no)
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
            $debitNote->bill_no = $bill_no;
            $debitNote->price = $price_difference[$i];
            $debitNote->gst = $gst_difference[$i];
            $debitNote->quantity = $qty;
            $debitNote->original_qty = $original_qty;
            $debitNote->original_unit = $measuring_unit[$i];
            $debitNote->discount = $discount_difference[$i];
            $debitNote->reason = $request->reason_change;
            $debitNote->taxable_value = $request->taxable_value;
            $debitNote->discount_value = $request->discount_value;
            $debitNote->gst_value = $request->gst_value;
            $debitNote->note_value = $request->note_value;

            $debitNote->type = 'purchase';

            $debitNote->save();
        }

        if ($request->submit_type == "print") {
            // return redirect(route('show.purchase.debit.note', $request->note_no));
        } else if ($request->submit_type == "email") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendPurchaseDebitNote($request->note_no));
            }
            // return redirect()->back()->with('success', 'Note created successfully');
        } else if ($request->submit_type == "eway") {
            // return redirect(route('eway.bill.create'));
        }

        return redirect(route('list.bill.credit.note', $bill_no))->with('success', 'Note created successfully!');
        // return redirect()->back()->with('success', 'Note created successfully!');

        // return redirect()->route('bill.detail.debit.note', $bill_no)->with('success', 'Note created successfully!');
    }

    public function unique_bill_debit_note_no(Request $request)
    {
        $is_note_no_valid = true;
        $note = DebitNote::where('note_no', $request->note_no)->where('type', 'purchase')->first();

        if ($note) {
            $is_note_no_valid = false;
        }

        return response()->json($is_note_no_valid);
    }

    public function bill_detail_credit_note($bill_no)
    {

        // $creditNote = CreditNote::where('invoice_id', $bill_no)->where('type', 'purchase')->first();

        // $note_no = $creditNote->note_no;
        // $note_date =  Carbon::parse($creditNote->created_at)->format('d/m/Y');

        // $creditNotes = CreditNote::where('invoice_id', $bill_no)->where('type', 'purchase')->get();

        // return $debitNotes;

        $purchase = PurchaseRecord::findOrFail($bill_no);

        // $bill = $bill_no;

        // foreach($creditNotes as $note){
        //      $item = Item::find($note->item_id);

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

        return view('purchase.detail_credit_note', compact('purchase', 'note_no'))->with('myerrors', $myerrors);
    }

    public function bill_create_credit_note(Request $request, $bill_no)
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
            $creditNote->invoice_id = $bill_no;
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

            $creditNote->type = 'purchase';

            $creditNote->save();
        }

        if ($request->submit_type == "print") {
            // return redirect(route('show.purchase.credit.note', $request->note_no));
        } else if ($request->submit_type == "email") {
            if ($party->email) {
                Mail::to($party->email)->send(new SendPurchaseCreditNote($request->note_no));
            }
            // return redirect()->back()->with('success', 'Note created successfully');
        } else if ($request->submit_type == "eway") {
            // return redirect(route('eway.bill.create'));
        }

        return redirect(route('list.bill.credit.note', $bill_no))->with('success', 'Note created successfully!');

        // return redirect()->back()->with('success', 'Note created successfully!');
        // return redirect()->route('bill.detail.credit.note', $bill_no)->with('success', 'Note created successfully!');
    }

    public function unique_bill_credit_note_no(Request $request)
    {
        $is_note_no_valid = true;
        $note = CreditNote::where('note_no', $request->note_no)->where('type', 'purchase')->first();

        if ($note) {
            $is_note_no_valid = false;
        }

        return response()->json($is_note_no_valid);
    }

    public function edit_purchase_qty(Request $request)
    {
        $purchase = Purchase::find($request->row_id);
        $item = Item::find($purchase->item_id);
        $item->qty = $item->qty - ($request->old_quantity - $request->new_quantity);
        $item->save();

        $purchase->qty = $request->new_quantity;

        // return $request->all();
        if ($purchase->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('success', 'Failed to update data');
        }
    }

    public function list_bill_credit_note($bill_id)
    {
        $credit_notes = CreditNote::where('invoice_id', $bill_id)->where('type', 'purchase')->groupBy('note_no')->get();
        return view('purchase.list-bill-credit-note', compact('credit_notes'));
    }

    public function list_bill_debit_note($bill_id)
    {
        $debit_notes = DebitNote::where('bill_no', $bill_id)->where('type', 'purchase')->groupBy('note_no')->get();
        return view('purchase.list-bill-debit-note', compact('debit_notes'));
    }

    public function get_row_by_bill(Request $request)
    {

        $purchase_record = PurchaseRecord::Where('bill_no', $request->search_bill)->get();

        // return view('purchase.note', compact('purchase_records'));

        return response()->json($purchase_record);
    }

    public function add_commission_to_bill(Request $request)
    {

        $purchase_record = PurchaseRecord::find($request->row_id);

        $purchase_record->commission = $request->commission;

        $purchase_record->save();

        return response()->json($purchase_record);
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

        //     if($request->reason_price_change == 'other'){
        //         $param['reason_price_change_other'] = $request->reason_price_change_other;
        //     }
        // }

        // else if ($request->has('gst')) {
        //     $param = [
        //         'gst' => $request->gst,
        //         'gst_percent_difference' => $request->gst_percent_difference,
        //         'reason_gst_change' => $request->reason_gst_change,
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

        //     if($request->reason_quantity_change == 'other'){
        //         $param['reason_quantity_change_other'] = $request->reason_quantity_change_other;
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

        $item = Item::find($request->item_id);

        if ($request->reason_change == 'purchase_return') {
            $item->qty -= $request->quantity_difference;
            $item->save();
        }

        if ($request->reason_change == 'other') {
            $param['reason'] .= " (" . $request->reason_change_other . ")";
        }

        $param['taxable_value'] = $request->taxable_value;
        $param['discount_value'] = $request->discount_value;
        $param['gst_value'] = $request->gst_value;
        $param['note_value'] = $request->note_value;

        $debitNote = DebitNote::updateOrCreate(
            [
                'item_id' => $request->item_id,
                'bill_no' => $request->bill_no,
                'type' => $request->note_type
            ],
            $param
        );

        $item = Item::find($request->item_id);

        if ($request->note_type == 'purchase') {
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

    public function create_or_update_credit_note(Request $request)
    {

        // return $request->all();

        // if($request->has('price')){
        //     $param = [
        //         'price' => $request->price,
        //         'price_difference' => $request->price_difference,
        //         'reason_price_change' => $request->reason_price_change
        //     ];

        //     if($request->reason_price_change == 'other'){
        //         $param['reason_price_change_other'] = $request->reason_price_change_other;
        //     }
        // }

        // else if ($request->has('gst')) {
        //     $param = [
        //         'gst' => $request->gst,
        //         'gst_percent_difference' => $request->gst_percent_difference,
        //         'reason_gst_change' => $request->reason_gst_change
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

        //     if($request->reason_quantity_change == 'other'){
        //         $param['reason_quantity_change_other'] = $request->reason_quantity_change_other;
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

        $creditNote = CreditNote::updateOrCreate(
            [
                'item_id' => $request->item_id,
                'invoice_id' => $request->bill_no,
                'type' => 'purchase'
            ],
            $param
        );

        $item = Item::find($request->item_id);

        if ($request->note_type == 'purchase') {
            $item->qty += $request->price_difference;
        } else if ($request->note_type == 'sale') {
            $item->qty -= $request->price_difference;
        }

        $item->save();

        if ($creditNote) {
            return redirect()->back()->with('success', 'Note saved/updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save/update note');
        }
    }

    public function delete_credit_note(Request $request)
    {
        if ($request->has('row_id') && $request->row_id != null) {

            $credit_note = CreditNote::find($request->row_id);

            if ($credit_note) {
                $credit_note_count = CreditNote::where('note_no', $credit_note->note_no)->where('type', 'purchase')->count();

                $credit_note->delete();

                if ($credit_note_count > 1) {
                    return redirect()->back()->with('success', 'Item deleted successfully!');
                } else {
                    return redirect()->route('purchase.note')->with('success', 'Credit Note deleted successfully!');
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
                $debit_note_count = DebitNote::where('note_no', $debit_note->note_no)->where('type', 'purchase')->count();

                $debit_note->delete();

                if ($debit_note_count > 1) {
                    return redirect()->back()->with('success', 'Item deleted successfully!');
                } else {
                    return redirect()->route('purchase.note')->with('success', 'Debit Note deleted successfully!');
                }
            }
        }

        return redirect()->back()->with('failure', 'No such item exist in the note');
    }

    public function bill_type_regular($bill_no)
    {
        $purchase_record = PurchaseRecord::find($bill_no);

        $purchase_record->type_of_bill = 'regular';

        $purchases = Purchase::where('purchase_id', $bill_no)->get();

        foreach ($purchases as $purchase) {
            $item = Item::find($purchase->item_id);

            $item->qty = $item->qty + $purchase->qty;

            $item->save();
        }

        $debit_notes = DebitNote::where('bill_no', $bill_no)->where('type', 'sale')->get();
        $credit_notes = CreditNote::where('invoice_id', $bill_no)->where('type', 'sale')->get();

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

        $sale_remaining_amount = PurchaseRemainingAmount::where('purchase_id', $bill_no)->get();

        foreach($sale_remaining_amount as $amount) {
            $foundAmount = PurchaseRemainingAmount::find($amount->id);
            $foundAmount->status = 1;
            $foundAmount->save();
        }

        if ($purchase_record->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function bill_type_cancel($bill_no)
    {
        $purchase_record = PurchaseRecord::find($bill_no);

        $purchase_record->type_of_bill = 'cancel';

        $purchases = Purchase::where('purchase_id', $bill_no)->get();

        foreach ($purchases as $purchase) {
            $item = Item::find($purchase->item_id);

            $item->qty = $item->qty - $purchase->qty;

            $item->save();
        }

        $debit_notes = DebitNote::where('bill_no', $bill_no)->where('type', 'sale')->get();
        $credit_notes = CreditNote::where('invoice_id', $bill_no)->where('type', 'sale')->get();

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

        $sale_remaining_amount = PurchaseRemainingAmount::where('purchase_id', $bill_no)->get();

        foreach($sale_remaining_amount as $amount) {
            $foundAmount = PurchaseRemainingAmount::find($amount->id);
            $foundAmount->status = 0;
            $foundAmount->save();
        }

        if ($purchase_record->save()) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function purchase_report()
    {

        $parties = Party::where('user_id', Auth::user()->id)->get();

        foreach ($parties as $party) {
            $records[$party->id] = PurchaseRecord::where('party_id', $party->id)->where('type_of_bill', 'regular')->get();
            $count = 0;
            foreach ($records[$party->id] as $record) {
                $purchase_remaining = PurchaseRemainingAmount::where('party_id', $party->id)->where('bill_no', $record->bill_no)->orderBy('id', 'desc')->first();

                if ($purchase_remaining != null) {
                    $record->amount_remaining = $purchase_remaining;
                    $record->party_name = $party->name;
                }
                $count++;
            }
        }

        $purchase_records = $records;

        // return $purchase_records;

        return view('purchase.debtor', compact('purchase_records'));
    }

    public function b2b_purchase(Request $request)
    {
        // return $request->all();
        $registered_parties = Party::where('user_id', Auth::user()->id)->where('registered', 1)->get();

        foreach ($registered_parties as $party) {

            if (isset($request->from) && isset($request->to)) {
                $from = strtotime($request->from);
                $to = strtotime($request->to);

                $from = date('Y-m-d', $from);
                $to = date('Y-m-d', $to);

                // $purchase_records = PurchaseRecord::where('party_id', $party->id)->where('bill_date', '>=' , $from)->where('bill_date', '<=', $to)->get();

                $purchase_records[$party->id] = PurchaseRecord::where('party_id', $party->id)->whereBetween('bill_date', [$from, $to])->get();
            } else {
                $purchase_records[$party->id] = PurchaseRecord::where('party_id', $party->id)->get();
            }

            foreach ($purchase_records[$party->id] as $record) {
                $record->gst_no = $party->gst;
            }
        }

        // return $purchase_records;

        return view('purchase.b2b', compact('purchase_records'));
    }

    public function view_purchase_order($token)
    {

        $purchase_records = PurchaseOrder::where('token', $token)->get();
        $party_name = '';
        $purchase_order = '';

        foreach ($purchase_records as $record) {
            $party = Party::find($record->party_id);
            $item = Item::find($record->item_id);

            $party_name = $party->name;
            $record->item_name = $item->name;

            $purchase_order = $record->token;
        }

        return view('purchase.order.view', compact('purchase_records', 'party_name', 'purchase_order'));
    }

    public function view_all_purchase_order()
    {

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->groupBy('token')->get();
        // $purchase_orders = PurchaseOrder::select('*')->where('user_id', Auth::user()->id)->distinct()->get();

        foreach ($purchase_orders as $order) {
            $party = Party::find($order->party_id);
            $order->party_name = $party->name;

            $item = Item::find($order->item_id);
            $order->item_name = $item->name;
        }

        // return $purchase_orders;

        return view('purchase.order.index', compact('purchase_orders'));
    }

    public function update_purchase_order_status(Request $request, $id)
    {

        $purchase_order = PurchaseOrder::findOrFail($id);

        if (strtolower($request->type) == 'cancel') {
            $purchase_order->status = 0;
        } else if (strtolower($request->type) == 'activate') {
            $purchase_order->status = 1;
        }

        $purchase_order->save();

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    private function validate_payment_voucher_no($voucher, $from_date, $to_date) {
        $is_valid = true;
        // $party_payment = PartyPendingPaymentAccount::where('voucher_no', $voucher)->whereBetween('payment_date', [$from_date, $to_date])->get();
        // $purchase_payment = PurchaseRemainingAmount::where('voucher_no', $voucher)->whereBetween('payment_date', [$from_date, $to_date])->get();

        $party_payment = User::find(auth()->user()->id)->partyRemainingAmounts()->where('voucher_no', $voucher)->whereBetween('payment_date', [$from_date, $to_date])->get();
        $purchase_payment = User::find(auth()->user()->id)->purchaseRemainingAmounts()->where('voucher_no', $voucher)->whereBetween('payment_date', [$from_date, $to_date])->get();

        if(count($party_payment) > 0 || count($purchase_payment) > 0) {
            $is_valid = false;
        }

        return $is_valid;
    }


    public function add_pending_payment_to_party(Request $request)
    {

        $pending_payment = new PartyPendingPaymentAccount;

        $pending_payment->party_id = $request->party_id;
        $pending_payment->pending_balance = $request->pending_balance;
        $amount = 0;

        $pending_payment->remarks = $request->remarks;

        $pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

        $is_voucher_valid = $this->validate_payment_voucher_no($request->voucher_no, auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to);

        if(!$is_voucher_valid) {
            return redirect()->back()->with('failure', "Please provide unique voucher no");
        }

        if ($request->has('voucher_no')) {
            $pending_payment->voucher_no = $request->voucher_no;
        }

        if (isset(auth()->user()->paymentSetting) && auth()->user()->paymentSetting->bill_no_type) {
            $pending_payment->voucher_no_type = auth()->user()->paymentSetting->bill_no_type;
        } else {
            $pending_payment->voucher_no_type = 'manual';
        }

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

        $pending_payment->type = "purchase";

        if ($pending_payment->save()) {
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function edit_purchase_order($purchase_order_no)
    {
        $purchase_orders = PurchaseOrder::where('token', $purchase_order_no)->where('user_id', Auth::user()->id)->get();
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


        foreach ($purchase_orders as $purchase_order) {

            $currentItem = Item::findOrFail($purchase_order->item_id);

            $purchase_order->item_name = $currentItem->name;
            $purchase_order->base_unit = $currentItem->measuring_unit ?? null;
            $purchase_order->alternate_unit = $currentItem->alternate_measuring_unit ?? null;
            $purchase_order->compound_unit = $currentItem->compound_measuring_unit ?? null;

            $total_amount = $purchase_order->total_amount;
            $amount_received = $purchase_order->amount_received;
            $amount_remaining = $purchase_order->amount_remaining;

            $party_id = $purchase_order->party_id;
            $date = $purchase_order->date;
            $reference_name = $purchase_order->reference_name;
        }

        return view('purchase.order.edit', compact('items', 'parties', 'groups', 'banks', 'user_profile', 'purchase_orders', 'party_id', 'date', 'reference_name', 'purchase_order_no', 'total_amount', 'amount_received', 'amount_remaining'));
    }

    public function update_purchase_order(Request $request)
    {
        // return $request->all();

        $purchase_order = PurchaseOrder::find($request->id);

        $purchase_order->item_id = $request->item_id;
        $purchase_order->qty = $request->qty;
        $purchase_order->unit = $request->unit;
        $purchase_order->rate = $request->rate;
        $purchase_order->value = $request->value;

        if ($purchase_order->save()) {
            echo "success";
        } else {
            echo "fail";
        }
    }

    public function update_purchase_order_remains(Request $request, $purchase_order_no)
    {
        $purchase_orders = PurchaseOrder::where('token', $purchase_order_no)->get();

        foreach ($purchase_orders as $purchase_order) {

            $order = PurchaseOrder::find($purchase_order->id);

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

            if ($request->has('total_amount') && $request->total_amount > 0) {
                $order->total_amount = $request->total_amount;
            } else {
                $order->total_amount = 0;
            }

            if ($request->has('amount_received') && $request->amount_received > 0) {
                $order->amount_received = $request->amount_received;
            } else {
                $order->amount_received = 0;
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

        return redirect()->back()->with('success', 'Purchase Order updated successfully!');
    }

    public function store_purchase_order_single_row(Request $request)
    {
        // return $request->all();


        $ptime = strtotime($request->date);

        $purchase_date = date('Y-m-d', $ptime);

        $purchase_order = new PurchaseOrder;

        $purchase_order->party_id = $request->party_id;

        $purchase_order->item_id = $request->item_id;

        $purchase_order->qty = $request->qty;

        $purchase_order->date = $purchase_date;

        $purchase_order->token = $request->token;

        $purchase_order->reference_name = $request->reference;

        $purchase_order->user_id = Auth::user()->id;

        if ($purchase_order->save()) {
            echo "success";
        } else {
            echo "fail";
        }
    }

    public function update_purchase_bill_note(Request $request)
    {

        $billNote = BillNote::updateOrCreate(
            ['bill_no' => $request->bill_no, 'type' => 'purchase'],
            ['taxable_value_difference' => $request->taxable_value_difference, 'gst_value_difference' => $request->gst_value_difference, 'reason' => $request->reason]
        );

        if ($billNote) {
            return redirect()->back()->with('success', 'Note saved/updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save/update note');
        }
    }

    public function purchase_bill_note($bill_no)
    {

        $bill_note = new BillNote;

        $bill_note->bill_no = $bill_no;

        $bill_note->taxable_value_difference = 0;

        $bill_note->gst_value_difference = 0;

        $bill_note->type = 'purchase';

        if ($bill_note->save()) {
            return redirect()->back()->with('success', 'Note created successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to create note');
        }
    }

    public function purchase_data_report(Request $request)
    {

        if (isset($request->from_date) && isset($request->to_date)) {
            $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [$request->from_date, $request->to_date])->get();
        } else if (isset($request->query_by) && isset($request->q)) {
            if ($request->query_by == 'bill_no') {
                $purchases = User::find(Auth::user()->id)->purchases()->where($request->query_by, $request->q)->get();
            } else if ($request->query_by == 'name') {
                $party = Party::where('user_id', Auth::user()->id)->where('name', $request->q)->first();

                $purchases = PurchaseRecord::where('party_id', $party->id)->get();
            }
            // else if( $request->query_by == 'state' ) {
            //     $state = State::where('name', strtoupper($request->q))->first();

            //     $parties = Party::where('user_id', Auth::user()->id)->where('business_place', $state->id)->get();

            //     foreach( $parties as $party ) {
            //         $data = PurchaseRecord::where('party_id', $party->id)->get();

            //         $purchases[$party->id] = $data;
            //     }

            //     // $purchases = $data;

            //     $purchases['type'] = 'state';

            //     foreach( $purchases[$party->id] as $purchase ){ 
            //         $party = Party::find($purchase->party_id);

            //         $purchase->party_name = $party->name;
            //         $purchase->party_city = $party->city;
            //     }
            // }
        } else {
            $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->get();
        }

        // $purchases = PurchaseRecord::where('user_id', Auth::user()->id)->where('type_of_bill', 'regular')->get();

        if ($request->query_by != 'state') {
            foreach ($purchases as $purchase) {
                $party = Party::find($purchase->party_id);

                $purchase->party_name = $party->name;
                $purchase->party_city = $party->city;
            }
        }

        return view('report.purchase', compact('purchases'));
    }

    public function find_purchase_order_no(Request $request)
    {

        $purchase_orders = PurchaseOrder::where('token', 'like', $request->key_to_search . '%')->where('user_id', auth()->user()->id)->get();

        return response()->json($purchase_orders);
    }


    public function show_all_bills()
    {
        $bills = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->orderBy('id', 'desc')->get();

        return view('purchase.show_all_purchases', compact('bills'));
    }

    public function edit_bill_form($bill_no)
    {
        $bill = PurchaseRecord::find($bill_no);

        $purchases = Purchase::where('purchase_id', $bill_no)->get();

        foreach ($purchases as $purchase) {
            $purchase->item = Item::find($purchase->item_id);
        }

        $banks = User::find(Auth::user()->id)->banks()->get();

        $payment = PurchaseRemainingAmount::where('purchase_id', $bill_no)->orderBy('id', 'desc')->first();

        // return $invoice;
        return view('purchase.edit_bill_form', compact('bill', 'banks', 'payment', 'purchases'));
    }

    public function update_bill_item(Request $request)
    {

        // return $request;
        $purchase_item = Purchase::find($request->source);

        $item_prev_qty = $purchase_item->qty;
        $item_prev_free_qty = $purchase_item->free_qty;
        $item_prev_price = $purchase_item->price;
        $item_prev_gst = $purchase_item->gst;
        $item_prev_cess = $purchase_item->cess;
        $item_prev_discount = $purchase_item->discount;
        $item_prev_measuring_unit = $purchase_item->item_measuring_unit;

        if ($request->lump_sump == 1) {
            $bill = PurchaseRecord::find($purchase_item->purchase_id);
            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::find($bill->party_id);

            $bill->item_total_amount = $bill->item_total_amount - $item_prev_price + $request->amount;

            if($party->registered == 0 || $party->registered == 2 || $party->registered == 3){
                $bill->item_total_gst = 0;
            } else {
                $bill->item_total_gst = ($bill->item_total_gst - $item_prev_gst) + $request->calculated_tax;
            }

            $bill->item_total_cess = ($bill->item_total_cess - $item_prev_cess) + $request->cess;

            // $bill->amount_before_round_off = ($bill->amount_before_round_off - ($item_prev_price + $item_prev_gst) + ($request->amount + $request->calculated_tax));

            // $bill->total_amount = ($bill->total_amount - ($item_prev_price + $item_prev_gst) + ($request->amount + $request->calculated_tax));

            // $bill->amount_remaining = ($bill->amount_remaining - ($item_prev_price + $item_prev_gst) + ($request->amount + $request->calculated_tax));

            $bill->amount_before_round_off = $bill->item_total_amount + $bill->item_total_gst + $bill->item_total_cess + $bill->tcs;

            if(auth()->user()->roundOffSetting->purchase_round_off_to == "upward") {
                $bill->total_amount = ceil($bill->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->purchase_round_off_to == "downward") {
                $bill->total_amount = floor($bill->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->purchase_round_off_to == "normal") {
                $bill->total_amount = round($bill->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->purchase_round_off_to == "manual") {
                $bill->total_amount = $bill->amount_before_round_off;
            }

            $round_off_difference = $bill->total_amount - $bill->amount_before_round_off;

            if($round_off_difference >= 0) {
                $bill->round_off_operation = '+';
            } else {
                $bill->round_off_operation = '-';
            }

            $bill->round_offed = abs($round_off_difference);

            $bill->amount_remaining = $bill->total_amount - $bill->amount_paid;

            //-------------

            // $bill->total_amount = $bill->total_amount - $item_prev_price + $request->amount;
            // $bill->item_total_amount = $bill->item_total_amount - $item_prev_price + $request->amount;

            $purchase_item->price = $request->rate;
            $purchase_item->item_total = $request->amount;
            $purchase_item->gst = $request->calculated_tax;
            $purchase_item->cess = $request->cess;

            /** ------------updating invoice cgst/sgst or ugst or igst------------ */
            if ($user_profile->place_of_business == $party->business_place) {
                $bill->cgst = $bill->item_total_gst / 2;
                $bill->sgst = $bill->item_total_gst / 2;
            } else {
                $bill->igst = $bill->item_total_gst;
            }

            $bill->save();
            $purchase_item->save();
        } else {

            $item_prev_discount = $item_prev_discount ? $item_prev_discount : 0;
            $request->discount = $request->discount ? $request->discount : 0;

            // $item_prev_amount = ($item_prev_qty * $item_prev_price) - ($item_prev_qty * $item_prev_price * $item_prev_discount / 100);
            $item_prev_amount = $purchase_item->item_total;
            $item_prev_total_amount = ($item_prev_amount + $item_prev_gst);


            $item_new_amount = $request->amount;

            $item_new_total_amount = $item_new_amount + $request->calculated_tax;


            $bill = PurchaseRecord::find($purchase_item->purchase_id);
            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::find($bill->party_id);

            $bill_discount = $bill->total_discount ?? 0;

            

            $bill->item_total_gst = ($bill->item_total_gst - $item_prev_gst) + $request->calculated_tax;
            $bill->item_total_cess = ($bill->item_total_cess - $item_prev_cess) + $request->cess;
            $bill->item_total_amount = ($bill->item_total_amount - $item_prev_amount) + $item_new_amount;
            $bill->amount_before_round_off = $bill->item_total_amount + $bill->item_total_gst + $bill->item_total_cess + $bill->tcs;

            // $bill->total_amount = ($bill->total_amount - $item_prev_total_amount - $item_prev_gst - $item_prev_cess) + $item_new_total_amount - $bill_discount + $request->calculated_tax + $request->cess;


            if(auth()->user()->roundOffSetting->purchase_round_off_to == "upward") {
                $bill->total_amount = ceil($bill->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->purchase_round_off_to == "downward") {
                $bill->total_amount = floor($bill->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->purchase_round_off_to == "normal") {
                $bill->total_amount = round($bill->amount_before_round_off);
            } else if(auth()->user()->roundOffSetting->purchase_round_off_to == "manual") {
                $bill->total_amount = $bill->amount_before_round_off;
            }

            $round_off_difference = $bill->total_amount - $bill->amount_before_round_off;

            if($round_off_difference >= 0) {
                $bill->round_off_operation = '+';
            } else {
                $bill->round_off_operation = '-';
            }

            $bill->round_offed = abs($round_off_difference);

            $bill->amount_remaining = $bill->total_amount - $bill->amount_paid;

            /** ------------updating invoice cgst/sgst or ugst or igst------------ */
            if ($user_profile->place_of_business == $party->business_place) {
                $bill->cgst = $bill->item_total_gst / 2;
                $bill->sgst = $bill->item_total_gst / 2;
            }
            // else if ($user_profile->place_of_business != $party->business_place && ($party->business_place == 4 || $party->business_place == 7 || $party->business_place == 25 || $party->business_place == 26 || $party->business_place == 31 || $party->business_place == 34 || $party->business_place == 35)) {
            //     $bill->ugst = $bill->item_total_gst;
            // } 
            else {
                $bill->igst = $bill->item_total_gst;
            }

            $bill->save();

            $purchase_remaining_amounts = PurchaseRemainingAmount::where('purchase_id', $bill->id)->get();

            foreach ($purchase_remaining_amounts as $remaining_amount) {
                $remaining = PurchaseRemainingAmount::find($remaining_amount->id);
                $remaining->total_amount = $bill->total_amount;
                $remaining->amount_remaining = $remaining->total_amount - $remaining->amount_paid;
                $remaining->save();
            }

            $item = Item::find($purchase_item->item_id);

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

            $purchase_item->qty = $request->qty;
            $purchase_item->free_qty = $request->free_qty;
            $purchase_item->item_measuring_unit = $request->measuring_unit;
            $purchase_item->price = $request->rate;
            $purchase_item->item_total = $request->amount;
            $purchase_item->item_tax_type = $request->item_tax_inclusive;
            $purchase_item->gst = $request->calculated_tax;
            $purchase_item->cess = $request->cess;
            $purchase_item->discount_type = $request->item_discount_type;
            $purchase_item->discount = $request->discount;

            $purchase_item->save();
        }

        return redirect()->back()->with('success', 'Item updated successfully');
    }

    public function update_bill(Request $request, PurchaseRecord $bill)
    {

        // return $request->all();
        $this->validate($request, [
            'bill_no' => 'uniqueBillForParty:party',
            'amount_paid' => 'required',
            'amount_remaining' => 'required'
        ]);

        // $bill = PurchaseRecord::findOrFail($request->bill_id);

        $purchase_remaining_amount = PurchaseRemainingAmount::where('purchase_id', $bill->id)->first();

        // if(!$purchase_remaining_amount){
        //     $purchase_remaining_amount = new PurchaseRemainingAmount;

        //     $purchase_remaining_amount->purchase_id = $bill->id;
        // }

        // if ($request->amount_type == 'inclusive_of_tax') {
        //     $amount_type = 'inclusive';
        // } else if ($request->amount_type == 'exclusive_of_tax') {
        //     $amount_type = 'exclusive';
        // }

        // $bill->amount_type = $amount_type;

        if ($request->filled('bill_no')) {
            $bill->bill_no = $request->bill_no;
        }

        $bill->bill_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));

        if ($request->filled('buyer_name')) {
            $bill->buyer_name = $request->buyer_name;
        }

        if ($request->filled('reference_name')) {
            $bill->reference_name = $request->reference_name;
        }

        if ($request->filled('shipping_bill_no')) {
            $bill->shipping_bill_no = $request->shipping_bill_no;
        } else {
            $bill->shipping_bill_no = null;
        }

        if ($request->filled('date_of_shipping')) {
            $bill->date_of_shipping = date('Y-m-d', strtotime(str_replace('/', '-', $request->date_of_shipping)));
        }

        if ($request->filled('code_of_shipping_port')) {
            $bill->code_of_shipping_port = $request->code_of_shipping_port;
        }

        if ($request->filled('conversion_rate')) {
            $bill->conversion_rate = $request->conversion_rate;
        }

        if ($request->filled('currency_symbol')) {
            $bill->currency_symbol = $request->currency_symbol;
        }

        if ($request->filled('export_type')) {
            $bill->export_type = $request->export_type;
        }

        if ($request->filled('consignee_info')) {
            $bill->consignee_info = $request->consignee_info;
        }

        if ($request->filled('consignor_info')) {
            $bill->consignor_info = $request->consignor_info;
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


            // if ($cash && $bank && $pos) {
            //     $bill->type_of_payment = 'combined';

            //     $bill->cash_payment = $request->cashed_amount;
            //     $bill->bank_payment = $request->banked_amount;
            //     $bill->pos_payment = $request->posed_amount;

            //     $bill->bank_id = $request->bank;
            //     $bill->bank_cheque = $request->bank_cheque;
            //     $bill->pos_bank_id = $request->pos_bank;

            //     $purchase_remaining_amount->type_of_payment = 'combined';

            //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
            //     $purchase_remaining_amount->bank_payment = $request->banked_amount;
            //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

            //     $purchase_remaining_amount->bank_id = $request->bank;
            //     $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            // } else if ($bank && $pos) {
            //     $bill->type_of_payment = 'pos+bank';

            //     $bill->bank_payment = $request->banked_amount;
            //     $bill->pos_payment = $request->posed_amount;

            //     $bill->bank_id = $request->bank;
            //     $bill->bank_cheque = $request->bank_cheque;
            //     $bill->pos_bank_id = $request->pos_bank;

            //     $purchase_remaining_amount->type_of_payment = 'pos+bank';

            //     $purchase_remaining_amount->bank_payment = $request->banked_amount;
            //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

            //     $purchase_remaining_amount->bank_id = $request->bank;
            //     $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            // } else if ($cash && $pos) {
            //     $bill->type_of_payment = 'pos+cash';

            //     $bill->cash_payment = $request->cashed_amount;
            //     $bill->pos_payment = $request->posed_amount;

            //     $bill->pos_bank_id = $request->pos_bank;

            //     $purchase_remaining_amount->type_of_payment = 'pos+cash';

            //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
            //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

            //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            // } else if ($cash && $bank) {
            //     $bill->type_of_payment = 'bank+cash';

            //     $bill->cash_payment = $request->cashed_amount;
            //     $bill->bank_payment = $request->banked_amount;

            //     $bill->bank_id = $request->bank;
            //     $bill->bank_cheque = $request->bank_cheque;

            //     $purchase_remaining_amount->type_of_payment = 'bank+cash';

            //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
            //     $purchase_remaining_amount->bank_payment = $request->banked_amount;

            //     $purchase_remaining_amount->bank_id = $request->bank;
            //     $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            // } else if ($bank) {
            //     $bill->type_of_payment = 'bank';

            //     $bill->bank_payment = $request->banked_amount;

            //     $bill->bank_id = $request->bank;
            //     $bill->bank_cheque = $request->bank_cheque;

            //     $purchase_remaining_amount->type_of_payment = 'bank';

            //     $purchase_remaining_amount->bank_payment = $request->banked_amount;

            //     $purchase_remaining_amount->bank_id = $request->bank;
            //     $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            // } else if ($cash) {
            //     $bill->type_of_payment = 'cash';

            //     $bill->cash_payment = $request->cashed_amount;

            //     $purchase_remaining_amount->type_of_payment = 'cash';

            //     $purchase_remaining_amount->cash_payment = $request->cashed_amount;
            // } else if ($pos) {
            //     $bill->type_of_payment = 'pos';

            //     $bill->pos_payment = $request->posed_amount;

            //     $bill->pos_bank_id = $request->pos_bank;

            //     $purchase_remaining_amount->type_of_payment = 'pos';

            //     $purchase_remaining_amount->pos_payment = $request->posed_amount;

            //     $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            // }


            if ($cash && $bank && $pos && $discount) {

                $bill->type_of_payment = 'combined';

                $bill->cash_payment = $request->cashed_amount;
                $bill->bank_payment = $request->banked_amount;
                $bill->pos_payment = $request->posed_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $bill->pos_bank_id = $request->pos_bank;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'combined';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {

                $bill->type_of_payment = 'cash+bank+pos';

                $bill->cash_payment = $request->cashed_amount;
                $bill->bank_payment = $request->banked_amount;
                $bill->pos_payment = $request->posed_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $bill->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->type_of_payment = 'cash+bank+pos';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank && $discount) {
                $bill->type_of_payment = 'cash+bank+discount';

                $bill->cash_payment = $request->cashed_amount;
                $bill->bank_payment = $request->banked_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'cash+bank+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->discount_payment = $request->discount_amount;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount && $pos) {
                $bill->type_of_payment = 'cash+pos+discount';

                $bill->cash_payment = $request->cashed_amount;
                $bill->pos_payment = $request->posed_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->pos_bank_id = $request->pos_bank;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'cash+pos+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount && $bank && $pos) {
                $bill->type_of_payment = 'bank+pos+discount';

                $bill->bank_payment = $request->banked_amount;
                $bill->pos_payment = $request->posed_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $bill->pos_bank_id = $request->pos_bank;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'bank+pos+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->discount_payment = $request->discount_amount;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank) {

                $bill->type_of_payment = 'bank+cash';

                $bill->cash_payment = $request->cashed_amount;
                $bill->bank_payment = $request->banked_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->type_of_payment = 'bank+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $bill->type_of_payment = 'pos+cash';

                $bill->cash_payment = $request->cashed_amount;
                $bill->pos_payment = $request->posed_amount;

                $bill->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->type_of_payment = 'pos+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $discount) {

                $bill->type_of_payment = 'cash+discount';

                $bill->cash_payment = $request->cashed_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'cash+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;

                $purchase_remaining_amount->discount_payment = $request->discount_amount;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {
                $bill->type_of_payment = 'pos+bank';

                $bill->bank_payment = $request->banked_amount;
                $bill->pos_payment = $request->posed_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;
                $bill->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->type_of_payment = 'pos+bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $discount) {

                $bill->type_of_payment = 'bank+discount';

                $bill->bank_payment = $request->banked_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'bank+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->discount_payment = $request->discount_amount;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $bill->type_of_payment = 'pos+discount';

                $bill->pos_payment = $request->posed_amount;
                $bill->discount_payment = $request->discount_amount;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $bill->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->type_of_payment = 'pos+discount';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->discount_payment = $request->discount_amount;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash) {
                $bill->type_of_payment = 'cash';

                $bill->cash_payment = $request->cashed_amount;

                $purchase_remaining_amount->type_of_payment = 'cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $bill->type_of_payment = 'bank';

                $bill->bank_payment = $request->banked_amount;

                $bill->bank_id = $request->bank;
                $bill->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->type_of_payment = 'bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $bill->type_of_payment = 'pos';

                $bill->pos_payment = $request->posed_amount;

                $bill->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->type_of_payment = 'pos';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount) {
                $bill->type_of_payment = 'discount';

                $bill->discount_payment = $request->discount_amount;

                $bill->discount_type = $request->discount_type;
                $bill->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->type_of_payment = 'discount';
                $purchase_remaining_amount->discount_payment = $request->discount_amount;
                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            }
        } else {
            $bill->type_of_payment = 'no_payment';
            $purchase_remaining_amount->type_of_payment = 'no_payment';
        }

        if ($request->filled('tcs')) {
            $bill->tcs = $request->tcs;
        }

        $bill->item_total_amount = $request->item_total_amount;

        $bill->item_total_gst = $request->total_gst_amounted;
        $bill->item_total_cess = $request->total_cess_amounted;
        $bill->amount_before_round_off = $request->total_amount_before_discount;
        $bill->total_discount = $request->total_discount;
        $bill->round_off_operation = $request->round_off_operation;
        $bill->round_offed = $request->round_offed;
        $bill->total_amount = $request->total_amount;
        $bill->amount_paid = $request->amount_paid;
        $bill->amount_remaining = $request->amount_remaining;
        $bill->remark = $request->overall_remark;

        $bill->save();

        if ($purchase_remaining_amount) {
            $purchase_remaining_amount->amount_paid = $request->amount_paid;
            $purchase_remaining_amount->amount_remaining = $request->amount_remaining;
            $purchase_remaining_amount->save();
        }

        return redirect()->back()->with('success', 'Bill updated successfully!');
    }

    public function update_bill_individual_column(Request $request)
    {
        $purchase = PurchaseRecord::find($request->id);
        if($request->type == 'date_of_shipping') {
            $purchase->date_of_shipping = date('Y-m-d', strtotime(str_replace('/', '-', $request->value)));
        } else {
            $purchase->{$request->type} = $request->value;
        }
        $purchase->save();
    }

    public function edit_purchase_pending_payment_form($id)
    {
        $purchase_remaining_amount = PurchaseRemainingAmount::find($id);

        $associated_party = Party::find($purchase_remaining_amount->party_id);

        $bill = PurchaseRecord::find($purchase_remaining_amount->purchase_id);

        $bill_no = $bill->bill_no;

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('purchase.edit_purchase_pending_payment', compact('purchase_remaining_amount', 'associated_party', 'bill_no', 'banks'));
    }

    public function update_purchase_pending_payment(Request $request, $id)
    {
        $purchase_remaining_amount = PurchaseRemainingAmount::find($id);

        $purchase_remaining_amount->amount_paid = $request->amount_paid;
        $purchase_remaining_amount->amount_remaining = $request->amount_remaining;
        $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));
        $purchase_remaining_amount->voucher_no = $request->voucher_no;

        if ($request->has('tds_income_tax')) {
            $purchase_remaining_amount->tds_income_tax = $request->tds_income_tax;
        }
        if ($request->has('tds_gst')) {
            $purchase_remaining_amount->tds_gst = $request->tds_gst;
        }
        if ($request->has('tcs_income_tax')) {
            $purchase_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
        }
        if ($request->has('tcs_gst')) {
            $purchase_remaining_amount->tcs_gst = $request->tcs_gst;
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

                $purchase_remaining_amount->type_of_payment = 'combined';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank && $pos) {

                $purchase_remaining_amount->type_of_payment = 'cash+bank+pos';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $bank && $discount) {
                $purchase_remaining_amount->type_of_payment = 'cash+bank+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $discount && $pos) {
                $purchase_remaining_amount->type_of_payment = 'cash+pos+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($discount && $bank && $pos) {
                $purchase_remaining_amount->type_of_payment = 'bank+pos+discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($cash && $bank) {

                $purchase_remaining_amount->type_of_payment = 'bank+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($cash && $pos) {
                $purchase_remaining_amount->type_of_payment = 'pos+cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash && $discount) {

                $purchase_remaining_amount->type_of_payment = 'cash+discount';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($bank && $pos) {
                $purchase_remaining_amount->type_of_payment = 'pos+bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($bank && $discount) {

                $purchase_remaining_amount->type_of_payment = 'discount';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            } else if ($pos && $discount) {
                $purchase_remaining_amount->type_of_payment = 'pos+discount';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;
                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($cash) {
                $purchase_remaining_amount->type_of_payment = 'cash';

                $purchase_remaining_amount->cash_payment = $request->cashed_amount;
            } else if ($bank) {

                $purchase_remaining_amount->type_of_payment = 'bank';

                $purchase_remaining_amount->bank_payment = $request->banked_amount;

                $purchase_remaining_amount->bank_id = $request->bank;
                $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            } else if ($pos) {
                $purchase_remaining_amount->type_of_payment = 'pos';

                $purchase_remaining_amount->pos_payment = $request->posed_amount;

                $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
            } else if ($discount) {
                $purchase_remaining_amount->type_of_payment = 'discount';

                $purchase_remaining_amount->discount_payment = $request->discount_amount;

                $purchase_remaining_amount->discount_type = $request->discount_type;
                $purchase_remaining_amount->discount_figure = $request->discount_figure;
            }
        } else {
            $purchase_remaining_amount->type_of_payment = 'no_payment';
        }

        if ($purchase_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Payment updated successfully!!');
        } else {
            return redirect()->back()->with('success', 'Failed to update payment');
        }
    }


    public function cancel_purchase_party_payment(Request $request, $id)
    {
        $party_pending_payment = PartyPendingPaymentAccount::find($id);

        $party_pending_payment->status = 0;

        if ($party_pending_payment->save()) {
            return redirect()->back()->with('success', 'Payment cancelled successfully!!');
        } else {
            return redirect()->back()->with('success', 'Failed to cancel payment');
        }
    }

    public function cancel_purchase_payment()
    {
        $purchase_remaining_amount = PurchaseRemainingAmount::find($id);

        $purchase_remaining_amount->status = 0;

        if ($purchase_remaining_amount->save()) {
            return redirect()->back()->with('success', 'Payment cancelled successfully!!');
        } else {
            return redirect()->back()->with('success', 'Failed to cancel payment');
        }
    }

    public function edit_purchase_party_pending_payment_form($id)
    {
        $party_pending_payment = PartyPendingPaymentAccount::find($id);

        $associated_party = Party::find($party_pending_payment->party_id);

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('purchase.edit_purchase_party_pending_payment', compact('party_pending_payment', 'associated_party', 'banks'));
    }

    public function update_purchase_party_pending_payment(Request $request, $id)
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

        // if ($request->type_of_payment == 'bank') {
        //     $party_pending_payment->bank_id = $request->bank;
        //     $party_pending_payment->bank_cheque = $request->bank_cheque;
        // }

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

        if ($party_pending_payment->save()) {
            return redirect()->back()->with('success', 'Payment updated successfully!!');
        } else {
            return redirect()->back()->with('success', 'Failed to update payment');
        }
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

    public function purchases_account(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $purchase_array = array();
        $credit_note_array = array();
        $debit_note_array = array();

        $opening_balance = $this->calculate_purchases_account_opening_balance($from_date);

        $purchases = User::findOrFail(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->orderBy('bill_date')->get();

        foreach ($purchases as $purchase) {

            if (auth()->user()->profile->registered == 0) {
                $purchase_amount = $purchase->total_amount;
            } else if (auth()->user()->profile->registered == 3) {
                $purchase_amount = $purchase->total_amount;
            } else {
                $purchase_amount = $purchase->item_total_amount;
            }

            $amount_paid = $purchase->amount_paid ?? 0;
            $discount_payment = $purchase->discount_payment ?? 0;

            // as discount is not getting added to amount paid as of now
            $total_amount_paid = $amount_paid + $discount_payment;

            $purchase_array[] = [
                'routable' => $purchase->id,
                'particulars' => $purchase->party->name,
                'voucher_type' => 'Purchase',
                'voucher_no' => $purchase->bill_no,
                'amount' => $purchase_amount,
                'amount_paid' => $total_amount_paid,
                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                'transaction_type' => 'debit',
                'loop' => 'purchase',
                'type' => 'showable',
                'reference_name' => $purchase->reference_name,
                'party_gst_no' => $purchase->party->gst,
                'party_shipping_address' => $purchase->party->shipping_address . ', ' . $purchase->party->shipping_city . ', ' . $purchase->party->shipping_state . ', ' . $purchase->party->shipping_pincode,
                'gross_profit_percent' => $purchase->percent_on_sale_of_invoice,
                'order_detail' => '',
                'shipping_detail' => $purchase->shipping_bill_no . ', ' . $purchase->date_of_shipping,
                'import_export' => $purchase->export_type,
                'port_code' => $purchase->code_of_shipping_port,
                'item_name' => '',
                'quantity_detail' => '',
                'rates' => '',
                'show_taxable_detail' => '',
                'gross_total' => $purchase->total_amount,
            ];
        }

        // foreach ($purchases as $bill) {

        // $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'purchase')->where('credit_notes.status', 1)->whereBetween('credit_notes.created_at', [$from_date, $to_date])->groupBy('credit_notes.note_no')->get();

        // foreach ($creditNotes as $creditNote) {
        //     if ($creditNote->taxable_value > 0) {
        //         $credit_note_array[] = [
        //             'routable' => $creditNote->note_no,
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

        // foreach ($purchases as $bill) {
        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'purchase')->where('debit_notes.status', 1)->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->groupBy('debit_notes.note_no')->get();

        foreach ($debitNotes as $debitNote) {
            if($debitNote->taxable_value > 0){
                $debit_note_array[] = [
                    'routable' => $debitNote->note_no,
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
        }
        // }

        $combined_array = array_merge(
            $purchase_array,
            $debit_note_array
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


        return view('report.purchases_account', compact('opening_balance', 'combined_array', 'from_date', 'to_date'));
    }

    public function export_purchases_account(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $purchase_array = array();
        $credit_note_array = array();
        $debit_note_array = array();

        $opening_balance = $this->calculate_purchases_account_opening_balance($from_date);

        $purchases = User::findOrFail(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->orderBy('bill_date')->get();

        foreach ($purchases as $purchase) {

            if (auth()->user()->profile->registered == 0) {
                $purchase_amount = $purchase->total_amount;
            } else if (auth()->user()->profile->registered == 3) {
                $purchase_amount = $purchase->total_amount;
            } else {
                $purchase_amount = $purchase->item_total_amount;
            }

            $amount_paid = $purchase->amount_paid ?? 0;
            $discount_payment = $purchase->discount_payment ?? 0;

            // as discount is not getting added to amount paid as of now
            $total_amount_paid = $amount_paid + $discount_payment;

            $purchase_array[] = [
                'routable' => $purchase->id,
                'particulars' => $purchase->party->name,
                'voucher_type' => 'Purchase',
                'voucher_no' => $purchase->bill_no,
                'amount' => $purchase_amount,
                'amount_paid' => $total_amount_paid,
                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                'transaction_type' => 'debit',
                'loop' => 'purchase',
                'type' => 'showable',
                'reference_name' => $purchase->reference_name,
                'party_gst_no' => $purchase->party->gst,
                'party_shipping_address' => $purchase->party->shipping_address . ', ' . $purchase->party->shipping_city . ', ' . $purchase->party->shipping_state . ', ' . $purchase->party->shipping_pincode,
                'gross_profit_percent' => $purchase->percent_on_sale_of_invoice,
                'order_detail' => '',
                'shipping_detail' => $purchase->shipping_bill_no . ', ' . $purchase->date_of_shipping,
                'import_export' => $purchase->export_type,
                'port_code' => $purchase->code_of_shipping_port,
                'item_name' => '',
                'quantity_detail' => '',
                'rates' => '',
                'show_taxable_detail' => '',
                'gross_total' => $purchase->total_amount,
            ];
        }

        // foreach ($purchases as $bill) {

        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

        foreach ($creditNotes as $creditNote) {
            if ($creditNote->taxable_value > 0) {
                $credit_note_array[] = [
                    'routable' => $creditNote->note_no,
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

        // foreach ($purchases as $bill) {
        // $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

        // foreach ($debitNotes as $debitNote) {
        //     if($debitNote->taxable_value > 0){
        //         $debit_note_array[] = [
        //             'routable' => $debitNote->note_no,
        //             'particulars' => 'Debit Note',
        //             'voucher_type' => 'Note',
        //             'voucher_no' => $debitNote->note_no,
        //             'amount' => $debitNote->taxable_value,
        //             'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
        //             'month' => Carbon::parse($debitNote->created_at)->format('m'),
        //             'transaction_type' => $debitNote->reason == 'purchase_return' ? 'credit' : 'debit',
        //             'loop' => 'debit_note',
        //             'type' => 'showable',
        //             'reference_name' => '',
        //             'party_gst_no' => '',
        //             'party_shipping_address' => '',
        //              'gross_profit_percent' => '',
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

        $combined_array = array_merge(
            $purchase_array,
            $credit_note_array
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
        // print_r($combined_array);

        $purchase_accounts = array();
        $moving_cash = $opening_balance;
        // $purchase_accounts[]['opening_balance'] = 'opening balance';
        // $purchase_accounts[]['closing balance'] = $moving_cash;
        foreach( $combined_array as $arr ){
            $id = $arr['routable'];
            $purchase_accounts[$id]['Date'] = $arr['date'];
            $purchase_accounts[$id]['Particulars'] = $arr['particulars'];
            $purchase_accounts[$id]['Voucher type'] = $arr['voucher_type'];
            $purchase_accounts[$id]['Voucher no'] = $arr['voucher_no'];
            if($arr['transaction_type'] == 'debit'){
                $moving_cash += $arr['amount'];
                $purchase_accounts[$id]['Debit'] = $arr['amount'] ?? 0;
                $purchase_accounts[$id]['Credit'] = 0;
            }

            if($arr['transaction_type'] == 'credit'){
                $moving_cash -= $arr['amount'];
                $purchase_accounts[$id]['Debit'] = 0;
                $purchase_accounts[$id]['Credit'] = $arr['amount'] ?? 0;
            }
            $purchase_accounts[$id]['Closing balance'] = $moving_cash;
        }

        // echo "<pre>";
        // print_r($purchase_accounts);

        $purchase_accounts[]['OPENING BALANCE'] = 'OPENING BALANCE';
        $purchase_accounts[]['closing balance'] = $opening_balance;

        Excel::create('purchases_account', function ($excel) use ($purchase_accounts) {
            $excel->sheet('FirstSheet', function ($sheet) use ($purchase_accounts) {
                $sheet->fromArray($purchase_accounts);
            });
        })->export('xlsx');
    }

    private function calculate_purchases_account_opening_balance($till_date)
    {
        $opening_balance = 0;

        $purchases = User::findOrFail(Auth::user()->id)->purchases()
            ->where('bill_date', '<', $till_date)
            ->orderBy('bill_date')
            ->get();

        // $creditNotes = User::find(auth()->user()->id)->creditNotes()
        //     ->where('credit_notes.type', 'purchase')
        //     ->where('credit_notes.created_at', '<', $till_date)
        //     ->get();

        $debitNotes = $debitNotes = User::find(auth()->user()->id)->debitNotes()
            ->where('debit_notes.type', 'purchase')
            ->where('debit_notes.created_at', '<', $till_date)
            ->get();

        foreach ($purchases as $bill) {
            if (auth()->user()->profile->registered == 0) {
                $purchase_amount = $bill->item_total_amount + $purchase->item_total_gst;
            } else {
                $purchase_amount = $bill->item_total_amount;
            }

            $opening_balance += $purchase_amount;
        }


        // foreach ($creditNotes as $creditNote) {
        //     $opening_balance -= $creditNote->taxable_value;
        // }

        foreach ($debitNotes as $debitNote) {
            if ($debitNote->reason == 'purchase_return')
                $opening_balance -= $debitNote->taxable_value;
            else
                $opening_balance += $debitNote->taxable_value;
        }

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

    public function purchase_gst_report(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $purchases = User::findOrFail(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->orderBy('bill_date')->get();

        foreach ($purchases as $purchase) {
            if (Auth::user()->profile->place_of_business == $purchase->party->business_place) {
                $purchase->has_igst = false;
            } else {
                $purchase->has_igst = true;
            }
            $purchase->fivePercentTotal = 0;
            $purchase->twelvePercentTotal = 0;
            $purchase->eighteenPercentTotal = 0;
            $purchase->twentyEightPercentTotal = 0;
            $purchase->exemptPercentTotal = 0;
            $purchase->nilPercentTotal = 0;
            $purchase->exportPercentTotal = 0;

            $purchase->fivePercentItemTotal = 0;
            $purchase->twelvePercentItemTotal = 0;
            $purchase->eighteenPercentItemTotal = 0;
            $purchase->twentyEightPercentItemTotal = 0;
            $purchase->exemptPercentItemTotal = 0;
            $purchase->nilPercentItemTotal = 0;
            $purchase->exportPercentItemTotal = 0;
            foreach ($purchase->purchase_items->groupBy('gst_rate') as $data) {
                foreach ($data as $item) {
                    // Credit/debit note value must not be shown in SALE  GST AND PURCHASE GST report (16 oct 2020) point date
                    // $q = DebitNote::where('bill_no', $purchase->id)->where('type', 'purchase')->whereBetween('created_at', [$from_date, $to_date])->where('reason', ['discount_on_purchase', 'purchase_return']);
                    switch ($item->gst_rate) {
                        case 5:
                            $purchase->fivePercentTotal += $item->gst;
                            $purchase->fivePercentItemTotal += $item->item_total;
                            // $debit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($debit_notes as $note) {
                            //     $purchase->fivePercentTotal += $note->gst;
                            //     $purchase->fivePercentItemTotal += $note->price;
                            // }
                            break;
                        case 12:
                            $purchase->twelvePercentTotal += $item->gst;
                            $purchase->twelvePercentItemTotal += $item->item_total;
                            // $debit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($debit_notes as $note) {
                            //     $purchase->twelvePercentTotal += $note->gst;
                            //     $purchase->twelvePercentItemTotal += $note->price;
                            // }
                            break;
                        case 18:
                            $purchase->eighteenPercentTotal += $item->gst;
                            $purchase->eighteenPercentItemTotal += $item->item_total;
                            // $debit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($debit_notes as $note) {
                            //     $purchase->eighteenPercentTotal += $note->gst;
                            //     $purchase->eighteenPercentItemTotal += $note->price;
                            // }
                            break;
                        case 28:
                            $purchase->twentyEightPercentTotal += $item->gst;
                            $purchase->twentyEightPercentItemTotal += $item->item_total;
                            // $debit_notes = $q->where('item_id', $item->item_id)->get();
                            // foreach($debit_notes as $note) {
                            //     $purchase->twentyEightPercentTotal += $note->gst;
                            //     $purchase->twentyEightPercentItemTotal += $note->price;
                            // }
                            break;
                        case 'exempt':
                            $purchase->exemptPercentTotal += $item->gst;
                            $purchase->exemptPercentItemTotal += $item->item_total;
                            break;
                        case 'nil':
                            $purchase->nilPercentTotal += $item->gst;
                            $purchase->nilPercentItemTotal += $item->item_total;
                            break;
                        case 'export':
                            $purchase->exportPercentTotal += $item->gst;
                            $purchase->exportPercentItemTotal += $item->item_total;
                            break;
                    }
                }
            }
        }

        // return $purchases;

        return view('report.purchase_gst_report', compact('purchases'));
    }

    public function export_purchase_gst_report(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $purchases = User::findOrFail(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->get();

        foreach ($purchases as $purchase) {
            $purchase->fivePercentTotal = 0;
            $purchase->twelvePercentTotal = 0;
            $purchase->eighteenPercentTotal = 0;
            $purchase->twentyEightPercentTotal = 0;
            foreach ($purchase->purchase_items->groupBy('gst_rate') as $data) {
                foreach ($data as $item) {
                    $q = DebitNote::where('bill_no', $purchase->id)->where('type', 'purchase')->whereBetween('created_at', [$from_date, $to_date])->where('reason', ['discount_on_purchase', 'purchase_return']);
                    switch ($item->gst_rate) {
                        case 5:
                            $purchase->fivePercentTotal += $item->gst;
                            $debit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($debit_notes as $note) {
                                $purchase->fivePercentTotal += $note->gst;
                            }
                            break;
                        case 12:
                            $purchase->twelvePercentTotal += $item->gst;
                            $debit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($debit_notes as $note) {
                                $purchase->twelvePercentTotal += $note->gst;
                            }
                            break;
                        case 18:
                            $purchase->eighteenPercentTotal += $item->gst;
                            $debit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($debit_notes as $note) {
                                $purchase->eighteenPercentTotal += $note->gst;
                            }
                            break;
                        case 28:
                            $purchase->twentyEightPercentTotal += $item->gst;
                            $debit_notes = $q->where('item_id', $item->item_id)->get();
                            foreach ($debit_notes as $note) {
                                $purchase->twentyEightPercentTotal += $note->gst;
                            }
                            break;
                    }
                }
            }
        }

        $purchaseArray = array();

        foreach ($purchases as $purchase) {
            $purchaseArray[$purchase->id]['Date'] = Carbon::parse($purchase->bill_date)->format('d/m/Y');
            $purchaseArray[$purchase->id]['Bill No'] = $purchase->bill_no;
            $purchaseArray[$purchase->id]['Buyer Name'] = $purchase->party->name;
            $purchaseArray[$purchase->id]['5%'] = $purchase->fivePercentTotal;
            $purchaseArray[$purchase->id]['sgst @2.5%'] = $purchase->fivePercentTotal / 2;
            $purchaseArray[$purchase->id]['cgst/utgst @2.5%'] = $purchase->fivePercentTotal / 2;
            $purchaseArray[$purchase->id]['12%'] = $purchase->twelvePercentTotal;
            $purchaseArray[$purchase->id]['sgst @6%'] = $purchase->twelvePercentTotal / 2;
            $purchaseArray[$purchase->id]['cgst/utgst @6%'] = $purchase->twelvePercentTotal / 2;
            $purchaseArray[$purchase->id]['18%'] = $purchase->eighteenPercentTotal;
            $purchaseArray[$purchase->id]['sgst @9%'] = $purchase->eighteenPercentTotal / 2;
            $purchaseArray[$purchase->id]['cgst/utgst @9%'] = $purchase->eighteenPercentTotal / 2;
            $purchaseArray[$purchase->id]['28%'] = $purchase->twentyEightPercentTotal;
            $purchaseArray[$purchase->id]['sgst @14%'] = $purchase->twentyEightPercentTotal / 2;
            $purchaseArray[$purchase->id]['cgst/utgst @14%'] = $purchase->twentyEightPercentTotal / 2;
        }

        // $purchaseArray[]['5%'] = '5';
        // $purchaseArray[]['12% '] = '12';
        // $purchaseArray[]['18%'] = '18';
        // $purchaseArray[]['28%'] = '28';

        // echo "<pre>";
        // print_r($purchaseArray);

        // die();

        Excel::create('purchase_gst_report', function ($excel) use ($purchaseArray) {
            $excel->sheet('FirstSheet', function ($sheet) use ($purchaseArray) {
                $sheet->fromArray($purchaseArray);
            });
        })->export('xlsx');
    }

    public function edit_debit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $note_no)->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->get();
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

        $purchase = PurchaseRecord::findOrFail($debit_notes->first()->bill_no);

        return view('purchase.edit_debit_note', compact('note_no', 'debit_notes', 'purchase', 'note_date'));
    }

    public function edit_credit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $note_no)->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->get();

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

        $purchase = PurchaseRecord::findOrFail($credit_notes->first()->invoice_id);

        return view('purchase.edit_credit_note', compact('note_no', 'credit_notes', 'purchase', 'note_date'));
    }

    public function update_credit_note_item(Request $request)
    {

        // return $request->all();

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
        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $request->search_by_note_no)->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

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

        return redirect()->route('purchase.credit.note.edit', $request->note_no)->with('success', 'Credit Note Updated successfully');
    }

    public function update_debit_note(Request $request)
    {
        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $request->search_by_note_no)->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

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

        return redirect()->route('purchase.debit.note.edit', $request->note_no)->with('success', 'Debit Note Updated successfully');
    }

    public function show_debit_note($note_no)
    {
        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $note_no)->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

        foreach ($debit_notes as $note) {
            $note->item_name = Item::findOrFail($note->item_id)->name ?? '';
        }

        $purchase = PurchaseRecord::findOrFail($debit_notes->first()->bill_no);

        return view('purchase.show_debit_note', compact('note_no', 'debit_notes', 'purchase'));
    }

    public function show_credit_note($note_no)
    {
        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $note_no)->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.note_date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->get();

        foreach ($credit_notes as $note) {
            $note->item_name = Item::findOrFail($note->item_id)->name ?? '';
        }

        $purchase = PurchaseRecord::findOrFail($credit_notes->first()->invoice_id);

        return view('purchase.show_credit_note', compact('note_no', 'credit_notes', 'purchase'));
    }

    public function validate_purchase_order_voucher_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('purchase_order')
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

    public function validate_purchase_party_payment_voucher_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('party_pending_payment_account')
            ->where('type', 'purchase')
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

    public function validate_purchase_payment_voucher_no(Request $request)
    {
        $party_id = $request->party;
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('purchase_remaining_amounts')
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
}
