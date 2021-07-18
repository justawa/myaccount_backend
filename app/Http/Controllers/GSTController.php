<?php

namespace App\Http\Controllers;

use App\AdvanceCashPayment;
use Illuminate\Http\Request;

use DB;
use Auth;
use Carbon\Carbon;

use App\Bank;
use App\CashGST;
use App\GSTComputation;
use App\Invoice;
use App\Party;
use App\PurchaseRecord;
use App\State;
use App\User;
use App\GSTCashLedgerBalance;
use App\GSTCreditLedgerBalance;
use App\GSTLiabilityLedgerBalance;
use App\GSTSetOff;
use App\GSTToBePaidInCash;
use App\GSTPayable;
use App\DebitNote;
use App\CreditNote;
use App\GSTSetoffLiabilityCharge;
use App\GSTSetoffOtherThanReverseCharge;
use App\GSTSetoffReverseCharge;
use App\IneligibleReversalOfInput;
use App\UserProfile;

class GSTController extends Controller
{
    public function get_gst_report() {

        $parties = Party::where('user_id', Auth::user()->id)->get();

        foreach( $parties as $party ) {

            $state = State::find( $party->shipping_state );

            $invoices[$party->id] = Invoice::where('party_id', $party->id)->get();

            foreach( $invoices[$party->id] as $invoice ) {
                $invoice->party_name = $party->name;
                $invoice->party_gst = $party->gst;
                $invoice->place_of_supply = $state->state_code . "-" . $state->name;
            }

        }

        return view('report.gst', compact('invoices', 'parties'));

    }

    public function get_gst_purchase_report(Request $request) {


        $parties = Party::where('user_id', Auth::user()->id)->get();

        foreach( $parties as $party ) {

            $state = State::find( $party->shipping_state );

            if( isset( $request->from_date ) && isset( $request->to_date )  ){
                $purchases[$party->id] = PurchaseRecord::where('party_id', $party->id)->whereBetween('bill_date', [$request->from_date, $request->to_date])->get();
            } else {
                $purchases[$party->id] = PurchaseRecord::where('party_id', $party->id)->get();
            }

            foreach( $purchases[$party->id] as $purchase ) {
                $purchase->party_name = $party->name;
                $purchase->party_gst = $party->gst;
                $purchase->place_of_supply = $state->state_code . "-" . $state->name;
            }

        }

        return view('report.purchase_gst', compact('purchases', 'parties'));

    }

    // This method is fetching data from computation
    // public function show_gst_ledger(Request $request) {

    //     $year = $request->year;
    //     $month = $request->month;

    //     $gst_computations = GSTComputation::where('user_id', Auth::user()->id)->where('month', $month)->where('year', $year)->get();

    //     // return $gst_computations;

    //     $tax_cgst = 0;
    //     $tax_sgst = 0;
    //     $tax_igst = 0;
    //     $tax_cess = 0;
    //     $tax_interest = 0;
    //     $tax_late_fees = 0;
    //     $tax_total = 0;

    //     $liability_igst = 0;
    //     $liability_cgst = 0;
    //     $liability_sgst = 0;
    //     $liability_cess = 0;

    //     $credit_igst = 0;
    //     $credit_cgst = 0;
    //     $credit_sgst = 0;
    //     $credit_cess = 0;

    //     foreach( $gst_computations as $computation ) {
    //         $tax_cgst += $computation->payable_cgst;
    //         $tax_sgst += $computation->payable_sgst;
    //         $tax_igst += $computation->payable_igst;
    //         $tax_cess += $computation->payble_cess;
    //         $tax_interest += $computation->payable_interest;
    //         $tax_late_fees += $computation->payable_late_fees;
    //         $tax_total += $computation->payable_total;
    //     }

    //     $sales = User::find(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->get();
    //     foreach( $sales as $sale ){
    //         $liability_igst += $sale->igst;
    //         $liability_cgst += $sale->cgst;
    //         $liability_sgst += $sale->sgst;
    //         $liability_cess += $sale->cess;
    //     }

    //     $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->get();
    //     foreach( $purchases as $purchase ){
    //         $credit_igst += $purchase->igst;
    //         $credit_cgst += $purchase->cgst;
    //         $credit_sgst += $purchase->sgst;
    //         $credit_cess += $purchase->cess;
    //     }

    //     return view('gst.show_gst_ledger', compact( 'tax_cgst', 'tax_sgst', 'tax_igst', 'tax_cess','tax_interest', 'tax_late_fees', 'tax_total', 'liability_igst', 'liability_cgst', 'liability_sgst', 'liability_cess', 'credit_igst', 'credit_cgst', 'credit_sgst', 'credit_cess' ));
    // }

    public function show_gst_ledger(Request $request)
    {
        // if( $request->has('year') ) {
        //     $year = $request->year;
        // } else {
        //     $year = Carbon::now()->format('Y');
        // }

        // if( $request->has('month') ) {
        //     $month = $request->month;
        // } else {
        //     $month = Carbon::now()->format('m');
        // }

        // if( Carbon::parse(auth()->user()->profile->financial_year_from)->format('m') > $month ){
            
        // } else if( Carbon::parse(auth()->user()->profile->financial_year_from)->format('Y') > $year ) { 
            
        // }

        if( $request->has('to_date') && $request->has('to_date') ){
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = date('Y-m-d', time());
        }

        // $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->get();

        // $reverse_charge_igst = 0;
        // $reverse_charge_cgst = 0;
        // $reverse_charge_sgst = 0;
        // $reverse_charge_cess = 0;

        // foreach($purchases as $purchase){
        //     $reverse_charge_igst += $purchase->igst;
        //     $reverse_charge_cgst += $purchase->ugst;
        //     $reverse_charge_sgst += $purchase->cgst;
        //     $reverse_charge_cess += $purchase->item_total_cess;
        // }

        $liability = array();
        $cash = array();
        $credit = array();

        $liability_ledger_balances = GSTLiabilityLedgerBalance::where('user_id', auth()->user()->id)->where('status', 'changing')->whereBetween('date', [$from_date, $to_date])->get();

        $fixed_liablility_balance = GSTLiabilityLedgerBalance::where('user_id', auth()->user()->id)->where('status', 'fixed')->whereBetween('date', [auth()->user()->profile->financial_year_from, auth()->user()->profile->financial_year_to])->orderBy('created_at', 'desc')->first();

        $invoices = User::find(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->get();

        // return $invoices;

        $balance_tax_reverse_charge_igst = 0;
        $balance_tax_reverse_charge_cgst = 0;
        $balance_tax_reverse_charge_sgst = 0;
        $balance_tax_reverse_charge_cess = 0;

        $balance_other_than_reverse_charge_igst = 0;
        $balance_other_than_reverse_charge_cgst = 0;
        $balance_other_than_reverse_charge_sgst = 0;
        $balance_other_than_reverse_charge_cess = 0;

        $other_than_reverse_charge_igst = 0;
        $other_than_reverse_charge_cgst = 0;
        $other_than_reverse_charge_sgst = 0;
        $other_than_reverse_charge_cess = 0;

        foreach ($invoices as $thisInvoice) {
            $other_than_reverse_charge_igst += $thisInvoice->igst;
            $other_than_reverse_charge_cgst += $thisInvoice->cgst;
            $other_than_reverse_charge_sgst += $thisInvoice->sgst;
            if($thisInvoice->ugst != null || $thisInvoice->ugst != 0){
                $other_than_reverse_charge_cgst += $thisInvoice->ugst/2;
                $other_than_reverse_charge_sgst += $thisInvoice->ugst/2;
            }
            $other_than_reverse_charge_cess += $thisInvoice->cess;

            $debitNotes = User::find(auth()->user()->id)->debitNotes()->where("bill_no", $thisInvoice->id)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();
            $creditNotes = User::find(auth()->user()->id)->creditNotes()->where("invoice_id", $thisInvoice->id)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

            foreach ($debitNotes as $note) {
                if (auth()->user()->profile->place_of_business == $thisInvoice->party->business_place) {
                    $other_than_reverse_charge_cgst += $note->gst_percent_difference / 2;
                    $other_than_reverse_charge_sgst += $note->gst_percent_difference / 2;
                }
                else if (auth()->user()->place_of_business != $thisInvoice->party->business_place && ($thisInvoice->party->business_place == 4 || $thisInvoice->party->business_place == 7 || $thisInvoice->party->business_place == 25 || $thisInvoice->party->business_place == 26 || $thisInvoice->party->business_place == 31 || $thisInvoice->party->business_place == 34 || $thisInvoice->party->business_place == 35)) {
                    $other_than_reverse_charge_cgst += $note->gst_percent_difference / 2;
                    $other_than_reverse_charge_sgst += $note->gst_percent_difference / 2;
                }
                else {
                    $other_than_reverse_charge_igst += $note->gst_percent_difference;
                }
            }

            foreach ($creditNotes as $note) {
                if (auth()->user()->profile->place_of_business == $thisInvoice->party->business_place) {
                    $other_than_reverse_charge_cgst -= $note->gst_percent_difference / 2;
                    $other_than_reverse_charge_sgst -= $note->gst_percent_difference / 2;
                }
                else if( auth()->user()->place_of_business != $thisInvoice->party->business_place && ( $thisInvoice->party->business_place == 4 || $thisInvoice->party->business_place == 7 || $thisInvoice->party->business_place == 25 || $thisInvoice->party->business_place == 26 || $thisInvoice->party->business_place == 31 || $thisInvoice->party->business_place == 34 || $thisInvoice->party->business_place == 35 ) ) {
                    $other_than_reverse_charge_cgst -= $note->gst_percent_difference / 2;
                    $other_than_reverse_charge_sgst -= $note->gst_percent_difference / 2;
                }
                else {
                    $other_than_reverse_charge_igst -= $note->gst_percent_difference;
                }
            }
        }

        $balance_liability_igst_late_fees = 0;
        $balance_liability_cgst_late_fees = 0;
        $balance_liability_sgst_late_fees = 0;
        $balance_liability_cess_late_fees = 0;
        $balance_liability_total_late_fees = 0;

        $balance_liability_igst_interest = 0;
        $balance_liability_cgst_interest = 0;
        $balance_liability_sgst_interest = 0;
        $balance_liability_cess_interest = 0;
        $balance_liability_total_interest = 0;

        $balance_liability_igst_penalty = 0;
        $balance_liability_cgst_penalty = 0;
        $balance_liability_sgst_penalty = 0;
        $balance_liability_cess_penalty = 0;
        $balance_liability_total_penalty = 0;

        $balance_liability_igst_others = 0;
        $balance_liability_cgst_others = 0;
        $balance_liability_sgst_others = 0;
        $balance_liability_cess_others = 0;
        $balance_liability_total_others = 0;

        // return $fixed_liablility_balance;
        
        if($fixed_liablility_balance){
            $balance_tax_reverse_charge_igst += $fixed_liablility_balance->igst_tax_reverse_charge;
            $balance_tax_reverse_charge_cgst += $fixed_liablility_balance->cgst_tax_reverse_charge;
            $balance_tax_reverse_charge_sgst += $fixed_liablility_balance->sgst_tax_reverse_charge;
            $balance_tax_reverse_charge_cess += $fixed_liablility_balance->cess_tax_reverse_charge;
    
            $balance_other_than_reverse_charge_igst += $fixed_liablility_balance->igst_tax_other_than_reverse_charge;
            $balance_other_than_reverse_charge_cgst += $fixed_liablility_balance->cgst_tax_other_than_reverse_charge;
            $balance_other_than_reverse_charge_sgst += $fixed_liablility_balance->sgst_tax_other_than_reverse_charge;
            $balance_other_than_reverse_charge_cess += $fixed_liablility_balance->cess_tax_other_than_reverse_charge;
        }

        foreach($liability_ledger_balances as $balance){

            $balance_liability_igst_late_fees += $balance->igst_late_fees;
            $balance_liability_cgst_late_fees += $balance->cgst_late_fees;
            $balance_liability_sgst_late_fees += $balance->sgst_late_fees;
            $balance_liability_cess_late_fees += $balance->cess_late_fees;

            $balance_liability_igst_interest += $balance->igst_interest;
            $balance_liability_cgst_interest += $balance->cgst_interest;
            $balance_liability_sgst_interest += $balance->sgst_interest;
            $balance_liability_cess_interest += $balance->cess_interest;

            $balance_liability_igst_penalty += $balance->igst_penalty;
            $balance_liability_cgst_penalty += $balance->cgst_penalty;
            $balance_liability_sgst_penalty += $balance->sgst_penalty;
            $balance_liability_cess_penalty += $balance->cess_penalty;

            $balance_liability_igst_others += $balance->igst_others;
            $balance_liability_cgst_others += $balance->cgst_others;
            $balance_liability_sgst_others += $balance->sgst_others;
            $balance_liability_cess_others += $balance->cess_others;

            $liability_total_late_fees = $balance_liability_igst_late_fees + $balance_liability_cgst_late_fees + $balance_liability_sgst_late_fees + $balance_liability_cess_late_fees;

            $liability_total_interest = $balance_liability_igst_interest + $balance_liability_cgst_interest + $balance_liability_sgst_interest + $balance_liability_cess_interest;

            $liability_total_penalty = $balance_liability_igst_penalty + $balance_liability_cgst_penalty + $balance_liability_sgst_penalty + $balance_liability_cess_penalty;

            $liability_total_others = $balance_liability_igst_others + $balance_liability_cgst_others + $balance_liability_sgst_others + $balance_liability_cess_others;

        }
        // -------------------------------------------------------------------------------------------------

        $cash_ledger_balances = GSTCashLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get();
        $make_payment_gst_setoff_otrc = GSTSetOff::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get(); //->where('type', 'other_than_reverse_charge') (saare setoff ke make payment ka add hoga cash ledger me)
        $gst_set_off_otrc = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereBetween('otr_date', [$from_date, $to_date])->get();
        

        $cash_igst_tax = 0;
        $cash_cgst_tax = 0;
        $cash_sgst_tax = 0;
        $cash_cess_tax = 0;
        $cash_total_tax = 0;

        $balance_cash_igst_tax = 0;
        $balance_cash_cgst_tax = 0;
        $balance_cash_sgst_tax = 0;
        $balance_cash_cess_tax = 0;
        
        $cash_total_tax = 0;

        $balance_cash_igst_interest = 0;
        $balance_cash_cgst_interest = 0;
        $balance_cash_sgst_interest = 0;
        $balance_cash_cess_interest = 0;
        
        $cash_total_interest = 0;

        $balance_cash_igst_late_fees = 0;
        $balance_cash_sgst_late_fees = 0;
        $balance_cash_cgst_late_fees = 0;
        $balance_cash_cess_late_fees = 0;
        
        $cash_total_late_fees = 0;

        $balance_cash_igst_penalty = 0;
        $balance_cash_cgst_penalty = 0;
        $balance_cash_sgst_penalty = 0;
        $balance_cash_cess_penalty = 0;
        
        $cash_total_penalty = 0;

        $balance_cash_igst_others = 0;
        $balance_cash_cgst_others = 0;
        $balance_cash_sgst_others = 0;
        $balance_cash_cess_others = 0;
        
        $cash_total_others = 0;

        foreach($make_payment_gst_setoff_otrc as $make_payment){
            $cash_igst_tax += $make_payment->igst;
            $cash_cgst_tax += $make_payment->cgst;
            $cash_sgst_tax += $make_payment->sgst;
            $cash_cess_tax += $make_payment->cess;
            $cash_total_tax += $make_payment->total;
        }

        foreach ($gst_set_off_otrc as $set_off) {
            $cash_igst_tax -= $set_off->otr_ptgcl_igst;
            $cash_cgst_tax -= $set_off->otr_ptgcl_cgst;
            $cash_sgst_tax -= $set_off->otr_ptgcl_sgst;
            $cash_cess_tax -= $set_off->otr_ptgcl_cess;
            $cash_total_tax -= ($set_off->otr_ptgcl_igst + $set_off->otr_ptgcl_cgst + $set_off->otr_ptgcl_sgst + $set_off->otr_ptgcl_cess);
        }

        foreach($cash_ledger_balances as $balance){

            $balance_cash_igst_tax += $balance->igst_tax;
            $balance_cash_cgst_tax += $balance->cgst_tax;
            $balance_cash_sgst_tax += $balance->sgst_tax;
            $balance_cash_cess_tax += $balance->cess_tax;

            $balance_cash_igst_interest += $balance->igst_interest;
            $balance_cash_cgst_interest += $balance->cgst_interest;
            $balance_cash_sgst_interest += $balance->sgst_interest;
            $balance_cash_cess_interest += $balance->cess_interest;

            $balance_cash_igst_late_fees += $balance->igst_late_fees;
            $balance_cash_sgst_late_fees += $balance->sgst_late_fees;
            $balance_cash_cgst_late_fees += $balance->cgst_late_fees;
            $balance_cash_cess_late_fees += $balance->cess_late_fees;

            $balance_cash_igst_penalty += $balance->igst_penalty;
            $balance_cash_cgst_penalty += $balance->cgst_penalty;
            $balance_cash_sgst_penalty += $balance->sgst_penalty;
            $balance_cash_cess_penalty += $balance->cess_penalty;

            $balance_cash_igst_others += $balance->igst_others;
            $balance_cash_cgst_others += $balance->cgst_others;
            $balance_cash_sgst_others += $balance->sgst_others;
            $balance_cash_cess_others += $balance->cess_others;

            $cash_total_tax = $cash_igst_tax + $cash_cgst_tax + $cash_sgst_tax + $cash_cess_tax + $balance_cash_igst_tax + $balance_cash_cgst_tax + $balance_cash_sgst_tax + $balance_cash_cess_tax;
            $cash_total_interest = $balance_cash_igst_interest + $balance_cash_cgst_interest + $balance_cash_sgst_interest + $balance_cash_cess_interest;
            $cash_total_late_fees = $balance_cash_igst_late_fees + $balance_cash_sgst_late_fees + $balance_cash_cgst_late_fees + $balance_cash_cess_late_fees;
            $cash_total_penalty = $balance_cash_igst_penalty + $balance_cash_cgst_penalty + $balance_cash_sgst_penalty + $balance_cash_cess_penalty;
            $cash_total_others = $balance_cash_igst_others + $balance_cash_cgst_others + $balance_cash_sgst_others + $balance_cash_cess_others; 
        }

        // -------------------------------------------------------------------------------------------------

        $credit_ledger_balances = GSTCreditLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get();
        $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->get();

        // return $purchases;

        $purchase_igst = 0;
        $purchase_cgst = 0;
        $purchase_sgst = 0;
        $purchase_cess = 0;

        foreach ($purchases as $thisBill) {
            $purchase_igst += $thisBill->igst;
            $purchase_cgst += $thisBill->cgst;
            $purchase_sgst += $thisBill->sgst;
            if($thisBill->ugst != null || $thisBill->ugst != 0){
                $purchase_cgst += $thisBill->ugst/2;
                $purchase_sgst += $thisBill->ugst/2;
            }

            $purchase_cess += $thisBill->cess;

            $debitNotes = User::find(auth()->user()->id)->debitNotes()->where("bill_no", $thisBill->id)->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();
            $creditNotes = User::find(auth()->user()->id)->creditNotes()->where("invoice_id", $thisBill->id)->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

            foreach ($debitNotes as $note) {
                if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
                    $purchase_cgst -= $note->gst_percent_difference / 2;
                    $purchase_sgst -= $note->gst_percent_difference / 2;
                }
                else if( auth()->user()->place_of_business != $thisBill->party->business_place && ( $thisBill->party->business_place == 4 || $thisBill->party->business_place == 7 || $thisBill->party->business_place == 25 || $thisBill->party->business_place == 26 || $thisBill->party->business_place == 31 || $thisBill->party->business_place == 34 || $thisBill->party->business_place == 35 ) ) {
                    $purchase_cgst -= $note->gst_percent_difference / 2;
                    $purchase_sgst -= $note->gst_percent_difference / 2;
                } else {
                    $purchase_igst -= $request->gst_percent_difference;
                }
            }

            foreach ($creditNotes as $note) {
                if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
                    $purchase_cgst += $note->gst_percent_difference / 2;
                    $purchase_sgst += $note->gst_percent_difference / 2;
                } else if (auth()->user()->place_of_business != $thisBill->party->business_place && ($thisBill->party->business_place == 4 || $thisBill->party->business_place == 7 || $thisBill->party->business_place == 25 || $thisBill->party->business_place == 26 || $thisBill->party->business_place == 31 || $thisBill->party->business_place == 34 || $thisBill->party->business_place == 35)) {
                    $purchase_cgst += $note->gst_percent_difference / 2;
                    $purchase_sgst += $note->gst_percent_difference / 2;
                } else {
                    $purchase_igst += $request->gst_percent_difference;
                }
            }
        }

        $credit_igst = $purchase_igst;
        $credit_cgst = $purchase_cgst;
        $credit_sgst = $purchase_sgst;
        $credit_cess = $purchase_cess;

        $balance_credit_igst = 0;
        $balance_credit_cgst = 0;
        $balance_credit_sgst = 0;
        $balance_credit_cess = 0;

        $credit_total = 0;

        foreach($credit_ledger_balances as $balance){

            $balance_credit_igst += $balance->igst;
            $balance_credit_cgst += $balance->cgst;
            $balance_credit_sgst += $balance->sgst;
            $balance_credit_cess += $balance->cess;
        }

        $credit_total = $credit_igst + $credit_cgst + $credit_sgst + $credit_cess + $balance_credit_igst + $balance_credit_cgst + $balance_credit_sgst + $balance_credit_cess;

//-----------------------------------------------------------------------


        $liability = [

            'balance_tax_reverse_charge_igst' => $balance_tax_reverse_charge_igst,
            'balance_tax_reverse_charge_cgst' => $balance_tax_reverse_charge_cgst,
            'balance_tax_reverse_charge_sgst' => $balance_tax_reverse_charge_sgst,
            'balance_tax_reverse_charge_cess' => $balance_tax_reverse_charge_cess,

            'other_than_reverse_charge_igst' => $other_than_reverse_charge_igst,
            'other_than_reverse_charge_cgst' => $other_than_reverse_charge_cgst,
            'other_than_reverse_charge_sgst' => $other_than_reverse_charge_sgst,
            'other_than_reverse_charge_cess' => $other_than_reverse_charge_cess,

            'balance_other_than_reverse_charge_igst' => $balance_other_than_reverse_charge_igst,
            'balance_other_than_reverse_charge_cgst' => $balance_other_than_reverse_charge_cgst,
            'balance_other_than_reverse_charge_sgst' => $balance_other_than_reverse_charge_sgst,
            'balance_other_than_reverse_charge_cess' => $balance_other_than_reverse_charge_cess,

            'igst_late_fees' => $balance_liability_igst_late_fees,
            'cgst_late_fees' => $balance_liability_cgst_late_fees,
            'sgst_late_fees' => $balance_liability_sgst_late_fees,
            'cess_late_fees' => $balance_liability_cess_late_fees,
            'total_late_fees' => $balance_liability_total_late_fees,

            'igst_interest' => $balance_liability_igst_interest,
            'cgst_interest' => $balance_liability_cgst_interest,
            'sgst_interest' => $balance_liability_sgst_interest,
            'cess_interest' => $balance_liability_cess_interest,
            'total_interest' => $balance_liability_total_interest,

            'igst_penalty' => $balance_liability_igst_penalty,
            'cgst_penalty' => $balance_liability_cgst_penalty,
            'sgst_penalty' => $balance_liability_sgst_penalty,
            'cess_penalty' => $balance_liability_cess_penalty,
            'total_cess' => $balance_liability_total_penalty,

            'igst_others' => $balance_liability_igst_others,
            'cgst_others' => $balance_liability_cgst_others,
            'sgst_others' => $balance_liability_sgst_others,
            'cess_others' => $balance_liability_cess_others,
            'total_others' => $balance_liability_cess_others
        ];

        $cash = [

            'balance_igst_tax' => $balance_cash_igst_tax,
            'balance_cgst_tax' => $balance_cash_cgst_tax,
            'balance_sgst_tax' => $balance_cash_sgst_tax,
            'balance_cess_tax' => $balance_cash_cess_tax,

            'igst_tax' => $cash_igst_tax,
            'cgst_tax' => $cash_cgst_tax,
            'sgst_tax' => $cash_sgst_tax,
            'cess_tax' => $cash_cess_tax,
            'total_tax' => $cash_total_tax,

            'balance_igst_interest' => $balance_cash_igst_interest,
            'balance_cgst_interest' => $balance_cash_cgst_interest,
            'balance_sgst_interest' => $balance_cash_sgst_interest,
            'balance_cess_interest' => $balance_cash_cess_interest,
            
            'total_interest' => $cash_total_interest,

            'balance_igst_late_fees' => $balance_cash_igst_late_fees,
            'balance_cgst_late_fees' => $balance_cash_sgst_late_fees,
            'balance_sgst_late_fees' => $balance_cash_cgst_late_fees,
            'balance_cess_late_fees' => $balance_cash_cess_late_fees,
            
            'total_late_fees' => $cash_total_late_fees,

            'balance_igst_penalty' => $balance_cash_igst_penalty,
            'balance_cgst_penalty' => $balance_cash_cgst_penalty,
            'balance_sgst_penalty' => $balance_cash_sgst_penalty,
            'balance_cess_penalty' => $balance_cash_cess_penalty,
            
            'total_penalty' => $cash_total_penalty,

            'balance_igst_others' => $balance_cash_igst_others,
            'balance_cgst_others' => $balance_cash_igst_others,
            'balance_sgst_others' => $balance_cash_sgst_others,
            'balance_cess_others' => $balance_cash_cess_others,
            
            'total_others' => $cash_total_others,
        ];

        $credit = [
            'balance_igst' => $balance_credit_igst,
            'balance_cgst' => $balance_credit_cgst,
            'balance_sgst' => $balance_credit_sgst,
            'balance_cess' => $balance_credit_cess,
            'igst' => $credit_igst,
            'cgst' => $credit_cgst,
            'sgst' => $credit_sgst,
            'cess' => $credit_cess,

            'total' => $credit_total
        ];

        // echo "<pre>";
        // print_r($liability);
        // die();

        return view('gst.show_gst_ledger', compact('liability', 'cash', 'credit', 'fixed_liablility_balance'));
    }


    public function show_gst_computation(Request $request) 
    {

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        if( $request->has('month') ){
            $month = $request->month;
        } else {
            $month = Carbon::now()->format('m');
        }

        if( $request->has('year') ) {
            $year = $request->year;
        } else {
            $year = Carbon::now()->format('Y');
        }

        $invoices = Invoice::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->get();

        $purchases = PurchaseRecord::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->get();

        $cash_gst = CashGST::where('month', $month)
                        ->where('year', $year)
                        ->first();

        // return $cash_gst;

        if( $cash_gst == null ){
            $cash_gst = new CashGST;
            $cash_gst->cgst = 0;
            $cash_gst->sgst = 0;
            $cash_gst->igst = 0;
            $cash_gst->cess = 0;
            $cash_gst->interest = 0;
            $cash_gst->late_fees = 0;

        }

        $invoice_cgst = 0;
        $invoice_sgst = 0;
        $invoice_igst = 0;
        $invoice_cess = 0;

        $purchase_cgst = 0;
        $purchase_sgst = 0;
        $purchase_igst = 0;
        $purchase_cess = 0;

        foreach( $invoices as $invoice ){

            $invoice_cgst += $invoice->cgst;
            $invoice_sgst += $invoice->sgst;
            $invoice_igst += $invoice->igst;
            $invoice_cess += $invoice->cess;
        }

        foreach( $purchases as $purchase ){

            $purchase_cgst += $purchase->cgst;
            $purchase_sgst += $purchase->sgst;
            $purchase_igst += $purchase->igst;
            $purchase_cess += $purchase->cess;
        }



        return view('gst.show_gst_computation', compact('invoice_cgst', 'invoice_sgst', 'invoice_igst', 'invoice_cess', 'purchase_cgst', 'purchase_sgst', 'purchase_igst', 'purchase_cess', 'cash_gst', 'banks'));
    }

    public function gst_paid_in_cash(Request $request) {

        if($request ->month != null){
             $month = $request->month;
        } else {
            $month = Carbon::now()->format('m');
        }

        if($request ->year != null) {
            $year = $request->year;
        } else {
            $year = Carbon::now()->format('Y');
        }

        $invoices = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        $purchases = PurchaseRecord::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        $invoice_cgst = 0;
        $invoice_sgst = 0;
        $invoice_igst = 0;
        $invoice_cess = 0;

        $purchase_cgst = 0;
        $purchase_sgst = 0;
        $purchase_igst = 0;
        $purchase_cess = 0;

        foreach($invoices as $invoice){

            $invoice_cgst += $invoice->cgst;
            $invoice_sgst += $invoice->sgst;
            $invoice_igst += $invoice->igst;
            $invoice_cess += $invoice->cess;
        }

        foreach($purchases as $purchase){

            $purchase_cgst += $purchase->cgst;
            $purchase_sgst += $purchase->sgst;
            $purchase_igst += $purchase->igst;
            $purchase_cess += $purchase->cess;
        }

        $cgst_payable = $invoice_cgst - $purchase_cgst;
        $sgst_payable = $invoice_sgst - $purchase_sgst;
        $igst_payable = $invoice_igst - $purchase_igst;
        $cess_payable = $invoice_cess - $purchase_cess;

        return view('gst.paid_in_cash', compact('banks', 'cgst_payable', 'sgst_payable', 'igst_payable', 'cess_payable') );
    }

    public function post_gst_paid_in_cash( Request $request ) {

        $cash_gst = new CashGST;

        $cash_gst->month = $request->month;
        $cash_gst->year = $request->year;
        $cash_gst->cgst = $request->cgst;
        $cash_gst->sgst = $request->sgst;
        $cash_gst->igst = $request->igst;
        $cash_gst->cess = $request->cess;
        $cash_gst->interest = $request->interest;
        $cash_gst->late_fees = $request->late_fees;
        $cash_gst->others = $request->others;
        $cash_gst->penalty = $request->penalty;
        $cash_gst->total = $request->total;

        $cash_gst->cash_amount = $request->cashed_amount;
        $cash_gst->bank_amount = $request->banked_amount;

        $cash_gst->bank = $request->bank;

        $cash_gst->cin = $request->cin;

        $cash_gst->user_id = Auth::user()->id;

        if( $cash_gst->save() ) {
            return redirect()->back()->with('success', 'Data added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add Data');
        }
    }

    public function post_gst_computation (Request $request) {

        $gst_computation = new GSTComputation;

        $gst_computation->invoice_cgst = $request->invoice_cgst;
        $gst_computation->invoice_sgst = $request->invoice_sgst;
        $gst_computation->invoice_igst = $request->invoice_igst;
        $gst_computation->invoice_cess = $request->invoice_cess;
        $gst_computation->invoice_interest = $request->invoice_interest;
        $gst_computation->invoice_late_fees = $request->invoice_late_fees;
        $gst_computation->invoice_penalty = $request->invoice_penalty;
        $gst_computation->invoice_other_charge = $request->invoice_other_charge;
        $gst_computation->invoice_total = $request->invoice_total;

        $gst_computation->purchase_cgst = $request->purchase_cgst;
        $gst_computation->purchase_sgst = $request->purchase_sgst;
        $gst_computation->purchase_igst = $request->purchase_igst;
        $gst_computation->purchase_cess = $request->purchase_cess;
        $gst_computation->purchase_interest = $request->purchase_interest;
        $gst_computation->purchase_late_fees = $request->purchase_late_fees;
        $gst_computation->purchase_penalty = $request->purchase_penalty;
        $gst_computation->purchase_other_charge = $request->purchase_other_charge;
        $gst_computation->purchase_total = $request->purchase_total;

        $gst_computation->cash_cgst = $request->cash_cgst;
        $gst_computation->cash_sgst = $request->cash_sgst;
        $gst_computation->cash_igst = $request->cash_igst;
        $gst_computation->cash_cess = $request->cash_cess;
        $gst_computation->cash_interest = $request->cash_interest;
        $gst_computation->cash_late_fees = $request->cash_late_fees;
        $gst_computation->cash_penalty = $request->cash_penalty;
        $gst_computation->cash_other_charge = $request->cash_other_charge;
        $gst_computation->cash_total = $request->cash_total;

        $gst_computation->payable_cgst = $request->payable_cgst;
        $gst_computation->payable_sgst = $request->payable_sgst;
        $gst_computation->payable_igst = $request->payable_igst;
        $gst_computation->payable_cess = $request->payable_cess;
        $gst_computation->payable_interest = $request->payable_interest;
        $gst_computation->payable_late_fees = $request->payable_late_fees;
        $gst_computation->payable_penalty = $request->payable_penalty;
        $gst_computation->payable_other_charge = $request->payable_other_charge;
        $gst_computation->payable_total = $request->payable_total;

        $gst_computation->month = $request->post_month;
        $gst_computation->year = $request->post_year;

        $gst_computation->user_id = Auth::user()->id;

        if( $gst_computation->save() ){
            return redirect()->back()->with('success', 'Data inserted successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to insert data');
        }

    }

    public function get_gst_input_report(Request $request)
    {
        if( $request->has('from') && $request->has('to') ){
            $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [date('Y-m-d', strtotime( str_replace('/', '-', $request->from))), date('Y-m-d', strtotime( str_replace('/', '-', $request->to)))])->get();
        } else {
            $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->orderBy('bill_date', 'desc')->get();
        }

        return view('report.gst_input_report', compact('purchases'));
    }

    public function get_gst_output_report(Request $request)
    {
        if( $request->has('from') && $request->has('to') ){
            $sales = User::find(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [date('Y-m-d', strtotime( str_replace('/', '-', $request->from))), date('Y-m-d', strtotime( str_replace('/', '-', $request->to)))])->get();
        } else {
            $sales = User::find(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->orderBy('invoice_date', 'desc')->get();
        }

        return view('report.gst_output_report', compact('sales'));
    }

    public function save_cash_ledger_balance(Request $request)
    {
        $cash_ledger = new GSTCashLedgerBalance;

        $cash_ledger->igst_tax = $request->igst_tax;
        $cash_ledger->cgst_tax = $request->cgst_tax;
        $cash_ledger->sgst_tax = $request->sgst_tax;
        $cash_ledger->cess_tax = $request->cess_tax;
        
        $cash_ledger->igst_interest = $request->igst_interest;
        $cash_ledger->cgst_interest = $request->cgst_interest;
        $cash_ledger->sgst_interest = $request->sgst_interest;
        $cash_ledger->cess_interest = $request->cess_interest;

        $cash_ledger->igst_late_fees = $request->igst_late_fees;
        $cash_ledger->cgst_late_fees = $request->cgst_late_fees;
        $cash_ledger->sgst_late_fees = $request->sgst_late_fees;
        $cash_ledger->cess_late_fees = $request->cess_late_fees;

        $cash_ledger->igst_penalty = $request->igst_penalty;
        $cash_ledger->cgst_penalty = $request->cgst_penalty;
        $cash_ledger->sgst_penalty = $request->sgst_penalty;
        $cash_ledger->cess_penalty = $request->cess_penalty;

        $cash_ledger->igst_others = $request->igst_others;
        $cash_ledger->cgst_others = $request->cgst_others;
        $cash_ledger->sgst_others = $request->sgst_others;
        $cash_ledger->cess_others = $request->cess_others;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);

            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if ($cash && $bank && $pos) {
                $cash_ledger->type_of_payment = 'combined';

                $cash_ledger->cash_payment = $request->cashed_amount;
                $cash_ledger->bank_payment = $request->banked_amount;
                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;
                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($pos && $bank) {
                $cash_ledger->type_of_payment = 'pos+bank';

                $cash_ledger->bank_payment = $request->banked_amount;
                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;
                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($pos && $cash) {
                $cash_ledger->type_of_payment = 'pos+cash';

                $cash_ledger->cash_payment = $request->cashed_amount;
                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($bank && $cash) {
                $cash_ledger->type_of_payment = 'bank+cash';

                $cash_ledger->cash_payment = $request->cashed_amount;
                $cash_ledger->bank_payment = $request->banked_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($bank) {
                $cash_ledger->type_of_payment = 'bank';

                $cash_ledger->bank_payment = $request->banked_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($cash) {
                $cash_ledger->type_of_payment = 'cash';

                $cash_ledger->cash_payment = $request->cashed_amount;
            } else if ($pos) {
                $cash_ledger->type_of_payment = 'pos';

                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            }
        } else {
            $cash_ledger->type_of_payment = 'no_payment';
        }

        $cash_ledger->voucher_no = $request->voucher_no;

        if( isset(auth()->user()->gstPaymentSetting) && isset(auth()->user()->gstPaymentSetting->bill_no_type) ){
            $cash_ledger->voucher_no_type = auth()->user()->gstPaymentSetting->bill_no_type;
        } else {
            $cash_ledger->voucher_no_type = 'manual';
        }

        $cash_ledger->cin = $request->cin;
        $cash_ledger->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $cash_ledger->user_id = auth()->user()->id;

        if ($cash_ledger->save()) {
            return redirect()->back()->with('success', 'Data saved successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function update_cash_ledger_balance(Request $request)
    {
        $cash_ledger = GSTCashLedgerBalance::findOrFail($request->row_id);

        $cash_ledger->igst_tax = $request->igst_tax;
        $cash_ledger->cgst_tax = $request->cgst_tax;
        $cash_ledger->sgst_tax = $request->sgst_tax;
        $cash_ledger->cess_tax = $request->cess_tax;

        $cash_ledger->igst_interest = $request->igst_interest;
        $cash_ledger->cgst_interest = $request->cgst_interest;
        $cash_ledger->sgst_interest = $request->sgst_interest;
        $cash_ledger->cess_interest = $request->cess_interest;

        $cash_ledger->igst_late_fees = $request->igst_late_fees;
        $cash_ledger->cgst_late_fees = $request->cgst_late_fees;
        $cash_ledger->sgst_late_fees = $request->sgst_late_fees;
        $cash_ledger->cess_late_fees = $request->cess_late_fees;

        $cash_ledger->igst_penalty = $request->igst_penalty;
        $cash_ledger->cgst_penalty = $request->cgst_penalty;
        $cash_ledger->sgst_penalty = $request->sgst_penalty;
        $cash_ledger->cess_penalty = $request->cess_penalty;

        $cash_ledger->igst_others = $request->igst_others;
        $cash_ledger->cgst_others = $request->cgst_others;
        $cash_ledger->sgst_others = $request->sgst_others;
        $cash_ledger->cess_others = $request->cess_others;

        if ($cash_ledger->save()) {
            return redirect()->back()->with('success', 'Data saved successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function save_credit_ledger_balance(Request $request)
    {
        $credit_ledger = new GSTCreditLedgerBalance;

        $credit_ledger->igst = $request->igst;
        $credit_ledger->cgst = $request->cgst;
        $credit_ledger->sgst = $request->sgst;
        $credit_ledger->cess = $request->cess;
        $credit_ledger->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $credit_ledger->user_id = auth()->user()->id;

        if ($credit_ledger->save()) {
            return redirect()->back()->with('success', 'Data saved successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function update_credit_ledger_balance(Request $request)
    {
        $credit_ledger = GSTCreditLedgerBalance::findOrFail($request->row_id);

        $credit_ledger->igst = $request->igst;
        $credit_ledger->cgst = $request->cgst;
        $credit_ledger->sgst = $request->sgst;
        $credit_ledger->cess = $request->cess;

        if ($credit_ledger->save()) {
            return redirect()->back()->with('success', 'Data updated successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function save_liability_ledger_balance(Request $request)
    {

        // return $request->all();

        $liability_ledger = new GSTLiabilityLedgerBalance;

        if ( $request->status == "fixed" ) {
            $liability_ledger->igst_tax_reverse_charge = $request->igst_tax_reverse_charge;
            $liability_ledger->igst_tax_other_than_reverse_charge = $request->igst_tax_other_than_reverse_charge;
            $liability_ledger->cgst_tax_reverse_charge = $request->cgst_tax_reverse_charge;
            $liability_ledger->cgst_tax_other_than_reverse_charge = $request->cgst_tax_other_than_reverse_charge;
            $liability_ledger->sgst_tax_reverse_charge = $request->sgst_tax_reverse_charge;
            $liability_ledger->sgst_tax_other_than_reverse_charge = $request->sgst_tax_other_than_reverse_charge;
            $liability_ledger->cess_tax_reverse_charge = $request->cess_tax_reverse_charge;
            $liability_ledger->cess_tax_other_than_reverse_charge = $request->cess_tax_other_than_reverse_charge;
        }

        if( $request->status == "changing" ) {
            $liability_ledger->igst_interest = $request->igst_interest;
            $liability_ledger->cgst_interest = $request->cgst_interest;
            $liability_ledger->sgst_interest = $request->sgst_interest;
            $liability_ledger->cess_interest = $request->cess_interest;

            $liability_ledger->igst_late_fees = $request->igst_late_fees;
            $liability_ledger->cgst_late_fees = $request->cgst_late_fees;
            $liability_ledger->sgst_late_fees = $request->sgst_late_fees;
            $liability_ledger->cess_late_fees = $request->cess_late_fees;

            $liability_ledger->igst_penalty = $request->igst_penalty;
            $liability_ledger->cgst_penalty = $request->cgst_penalty;
            $liability_ledger->sgst_penalty = $request->sgst_penalty;
            $liability_ledger->cess_penalty = $request->cess_penalty;

            $liability_ledger->igst_others = $request->igst_others;
            $liability_ledger->cgst_others = $request->cgst_others;
            $liability_ledger->sgst_others = $request->sgst_others;
            $liability_ledger->cess_others = $request->cess_others;

            if ($request->has('type_of_payment')) {

                $type_of_payment = $request->type_of_payment;

                $cash = array_search('cash', $type_of_payment);
                $bank = array_search('bank', $type_of_payment);
                $pos = array_search('pos', $type_of_payment);

                if (!is_bool($cash)) {
                    $cash += 1;
                }

                if (!is_bool($bank)) {
                    $bank += 1;
                }

                if (!is_bool($pos)) {
                    $pos += 1;
                }

                if ($cash && $bank && $pos) {
                    $liability_ledger->type_of_payment = 'combined';

                    $liability_ledger->cash_payment = $request->cashed_amount;
                    $liability_ledger->bank_payment = $request->banked_amount;
                    $liability_ledger->pos_payment = $request->posed_amount;

                    $liability_ledger->bank_id = $request->bank;
                    $liability_ledger->bank_cheque = $request->bank_cheque;
                    $liability_ledger->pos_bank_id = $request->pos_bank;

                    $liability_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                    $liability_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
                } else if ($pos && $bank) {
                    $liability_ledger->type_of_payment = 'pos+bank';

                    $liability_ledger->bank_payment = $request->banked_amount;
                    $liability_ledger->pos_payment = $request->posed_amount;

                    $liability_ledger->bank_id = $request->bank;
                    $liability_ledger->bank_cheque = $request->bank_cheque;
                    $liability_ledger->pos_bank_id = $request->pos_bank;

                    $liability_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                    $liability_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
                } else if ($pos && $cash) {
                    $liability_ledger->type_of_payment = 'pos+cash';

                    $liability_ledger->cash_payment = $request->cashed_amount;
                    $liability_ledger->pos_payment = $request->posed_amount;

                    $liability_ledger->pos_bank_id = $request->pos_bank;

                    $liability_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
                } else if ($bank && $cash) {
                    $liability_ledger->type_of_payment = 'bank+cash';

                    $liability_ledger->cash_payment = $request->cashed_amount;
                    $liability_ledger->bank_payment = $request->banked_amount;

                    $liability_ledger->bank_id = $request->bank;
                    $liability_ledger->bank_cheque = $request->bank_cheque;

                    $liability_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                } else if ($bank) {
                    $liability_ledger->type_of_payment = 'bank';

                    $liability_ledger->bank_payment = $request->banked_amount;

                    $liability_ledger->bank_id = $request->bank;
                    $liability_ledger->bank_cheque = $request->bank_cheque;

                    $liability_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                } else if ($cash) {
                    $liability_ledger->type_of_payment = 'cash';

                    $liability_ledger->cash_payment = $request->cashed_amount;
                } else if ($pos) {
                    $liability_ledger->type_of_payment = 'pos';

                    $liability_ledger->pos_payment = $request->posed_amount;

                    $liability_ledger->pos_bank_id = $request->pos_bank;

                    $liability_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
                }

            } else {
                $liability_ledger->type_of_payment = 'no_payment';
            }

            if($request->filled('narration')){
                $liability_ledger->narration = $request->narration;
            }
        }

        $liability_ledger->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));

        $liability_ledger->user_id = auth()->user()->id;

        $liability_ledger->status = $request->status;


        if (Carbon::parse(auth()->user()->profile->financial_year_from) > Carbon::parse($liability_ledger->date)) {
            return redirect()->back()->with('failure', 'Please provide valid liability ledger from date');
        }

        if (Carbon::parse(auth()->user()->profile->financial_year_to) < Carbon::parse($liability_ledger->date)) {
            return redirect()->back()->with('failure', 'Please provide valid liability ledger to date');
        }

        if ($liability_ledger->save()) {
            return redirect()->back()->with('success', 'Data saved successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function update_liability_ledger_balance(Request $request, $id)
    {
        $liability_ledger = GSTLiabilityLedgerBalance::find($id);

        if ( $request->status == "fixed" ) {
            $liability_ledger->igst_tax_reverse_charge = $request->igst_tax_reverse_charge;
            $liability_ledger->igst_tax_other_than_reverse_charge = $request->igst_tax_other_than_reverse_charge;
            $liability_ledger->cgst_tax_reverse_charge = $request->cgst_tax_reverse_charge;
            $liability_ledger->cgst_tax_other_than_reverse_charge = $request->cgst_tax_other_than_reverse_charge;
            $liability_ledger->sgst_tax_reverse_charge = $request->sgst_tax_reverse_charge;
            $liability_ledger->sgst_tax_other_than_reverse_charge = $request->sgst_tax_other_than_reverse_charge;
            $liability_ledger->cess_tax_reverse_charge = $request->cess_tax_reverse_charge;
            $liability_ledger->cess_tax_other_than_reverse_charge = $request->cess_tax_other_than_reverse_charge;
        }

        // $liability_ledger->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));

        // $liability_ledger->user_id = auth()->user()->id;

        // $liability_ledger->status = $request->status;


        if (Carbon::parse(auth()->user()->profile->financial_year_from) > Carbon::parse($liability_ledger->date)) {
            return redirect()->back()->with('failure', 'Please provide valid liability ledger from date');
        }

        if (Carbon::parse(auth()->user()->profile->financial_year_to) < Carbon::parse($liability_ledger->date)) {
            return redirect()->back()->with('failure', 'Please provide valid liability ledger to date');
        }

        if ($liability_ledger->save()) {
            return redirect()->back()->with('success', 'Data saved successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function gst_setoff(Request $request)
    {

        // return $request->all();

        // if( $request->has('year') ) {
        //     $year = $request->year;
        // } else {
        //     $year = Carbon::now()->format('Y');
        // }

        // if( $request->has('month') ) {
        //     $month = $request->month;
        // } else {
        //     $month = Carbon::now()->format('m');
        // }

        if ($request->has('to_date')) {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = date('Y-m-d', time());
        }

        $invoices = User::find(Auth::user()->id)->invoices()->where(function ($query) {
            $query->where('gst_classification', '!=', 'rcm')->orWhereNull('gst_classification');
        })->whereBetween('invoice_date', [$from_date, $to_date])->get();

        $gst_liability_ledger_balances = GSTLiabilityLedgerBalance::where('user_id', Auth::user()->id)->where('status', 'fixed')->whereBetween('date', [$from_date, $to_date])->get();

        $other_than_reverse_charge_igst = 0;
        $other_than_reverse_charge_cgst = 0;
        $other_than_reverse_charge_sgst = 0;
        $other_than_reverse_charge_cess = 0;

        $reverse_charge_igst = 0;
        $reverse_charge_cgst = 0;
        $reverse_charge_sgst = 0;
        $reverse_charge_cess = 0;

        foreach($gst_liability_ledger_balances as $balance){
            $other_than_reverse_charge_igst += $balance->igst_tax_other_than_reverse_charge;
            $other_than_reverse_charge_cgst += $balance->cgst_tax_other_than_reverse_charge;
            $other_than_reverse_charge_sgst += $balance->sgst_tax_other_than_reverse_charge;
            $other_than_reverse_charge_cess += $balance->cess_tax_other_than_reverse_charge;
        }

        foreach ($invoices as $thisInvoice) {
            $party = Party::findOrFail($thisInvoice->party_id);

            // if ($party->balance_type == 'debitor' && $party->registered == 0) {
            //     continue;
            // }

            $other_than_reverse_charge_igst += $thisInvoice->igst;
            $other_than_reverse_charge_cgst += $thisInvoice->cgst;
            $other_than_reverse_charge_sgst += $thisInvoice->sgst;
            if($thisInvoice->ugst != null || $thisInvoice->ugst != 0){
                $other_than_reverse_charge_cgst += $thisInvoice->ugst/2;
                $other_than_reverse_charge_sgst += $thisInvoice->ugst/2;
            }
            $other_than_reverse_charge_cess += $thisInvoice->cess;

            $debitNotes = User::find(auth()->user()->id)->debitNotes()->where("bill_no", $thisInvoice->id)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();
            $creditNotes = User::find(auth()->user()->id)->creditNotes()->where("invoice_id", $thisInvoice->id)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

            foreach ($debitNotes as $note) {
                if (auth()->user()->profile->place_of_business == $thisInvoice->party->business_place) {
                    $other_than_reverse_charge_cgst += $note->gst_percent_difference / 2;
                    $other_than_reverse_charge_sgst += $note->gst_percent_difference / 2;
                } 
                // else if (auth()->user()->place_of_business != $thisInvoice->party->business_place && ($thisInvoice->party->business_place == 4 || $thisInvoice->party->business_place == 7 || $thisInvoice->party->business_place == 25 || $thisInvoice->party->business_place == 26 || $thisInvoice->party->business_place == 31 || $thisInvoice->party->business_place == 34 || $thisInvoice->party->business_place == 35)) {
                //     $other_than_reverse_charge_cgst += $note->gst_percent_difference / 2;
                //     $other_than_reverse_charge_sgst += $note->gst_percent_difference / 2;
                // } 
                else {
                    $other_than_reverse_charge_igst += $note->gst_percent_difference;
                }
            }

            foreach ($creditNotes as $note) {
                if (auth()->user()->profile->place_of_business == $thisInvoice->party->business_place) {
                    $other_than_reverse_charge_cgst -= $note->gst_percent_difference / 2;
                    $other_than_reverse_charge_sgst -= $note->gst_percent_difference / 2;
                } 
                // else if (auth()->user()->place_of_business != $thisInvoice->party->business_place && ($thisInvoice->party->business_place == 4 || $thisInvoice->party->business_place == 7 || $thisInvoice->party->business_place == 25 || $thisInvoice->party->business_place == 26 || $thisInvoice->party->business_place == 31 || $thisInvoice->party->business_place == 34 || $thisInvoice->party->business_place == 35)) {
                //     $other_than_reverse_charge_cgst -= $note->gst_percent_difference / 2;
                //     $other_than_reverse_charge_sgst -= $note->gst_percent_difference / 2;
                // } 
                else {
                    $other_than_reverse_charge_igst -= $note->gst_percent_difference;
                }
            }
        }

        $other_than_reverse_charge_setoffs = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereBetween('otr_date', [$from_date, $to_date])->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('gst_classification', 'rcm')->whereBetween('bill_date', [$from_date, $to_date])->get();

        $reverse_charge_setoffs = GSTSetoffReverseCharge::where('user_id', auth()->user()->id)->whereBetween('r_date', [$from_date, $to_date])->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->get();

        foreach ($payments as $payment) {

            $purchase = PurchaseRecord::findOrFail($payment->purchase_id);

            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::findOrFail($payment->party_id);

            if ($purchase->gst_classification == "rcm") {
                if ($user_profile->place_of_business == $party->business_place) {
                    $reverse_charge_cgst -= $payment->tds_gst / 2;
                    $reverse_charge_sgst -= $payment->tds_gst / 2;

                    $reverse_charge_cgst -= $payment->tcs_gst / 2;
                    $reverse_charge_sgst -= $payment->tcs_gst / 2;
                } else {
                    $reverse_charge_igst -= $payment->tds_gst;
                    $reverse_charge_igst -= $payment->tcs_gst;
                }
            } else {
                if ($user_profile->place_of_business == $party->business_place) {
                    $other_than_reverse_charge_cgst -= $payment->tds_gst / 2;
                    $other_than_reverse_charge_sgst -= $payment->tds_gst / 2;

                    $other_than_reverse_charge_cgst -= $payment->tcs_gst / 2;
                    $other_than_reverse_charge_sgst -= $payment->tcs_gst / 2;
                } else {
                    $other_than_reverse_charge_igst -= $payment->tds_gst;
                    $other_than_reverse_charge_igst -= $payment->tcs_gst;
                }
            }
        }

        $ot_reverse_charge_igst = 0;
        $ot_reverse_charge_cgst = 0;
        $ot_reverse_charge_sgst = 0;
        $ot_reverse_charge_cess = 0;
        

        foreach ($other_than_reverse_charge_setoffs as $setoff) {
            $other_than_reverse_charge_igst -= $setoff->ot_reverse_charge_igst;
            $other_than_reverse_charge_cgst -= $setoff->ot_reverse_charge_cgst;
            $other_than_reverse_charge_sgst -= $setoff->ot_reverse_charge_sgst;
            $other_than_reverse_charge_cess -= $setoff->ot_reverse_charge_cess;
        }

        $rcm_invoices = User::find(Auth::user()->id)->invoices()->where('gst_classification', 'rcm')->whereBetween('invoice_date', [$from_date, $to_date])->get();

        foreach ($gst_liability_ledger_balances as $balance) {
            $reverse_charge_igst += $balance->igst_tax_reverse_charge;
            $reverse_charge_cgst += $balance->cgst_tax_reverse_charge;
            $reverse_charge_sgst += $balance->sgst_tax_reverse_charge;
            $reverse_charge_cess += $balance->cess_tax_reverse_charge;
        }

        foreach ($reverse_charge_setoffs as $setoff) {
            $reverse_charge_igst -= $setoff->reverse_charge_igst;
            $reverse_charge_cgst -= $setoff->reverse_charge_cgst;
            $reverse_charge_sgst -= $setoff->reverse_charge_sgst;
            $reverse_charge_cess -= $setoff->reverse_charge_cess;
        }

        // foreach($rcm_invoices as $thisInvoice){

        //     $party = Party::findOrFail($thisInvoice->party_id);

        //     if ($party->balance_type == 'debitor' && $party->registered == 0) {
        //         continue;
        //     }

        //     $reverse_charge_igst += $thisInvoice->igst;
        //     $reverse_charge_cgst += $thisInvoice->cgst;
        //     $reverse_charge_sgst += $thisInvoice->sgst;
        //     if ($thisInvoice->ugst != null) {
        //         $reverse_charge_cgst += $thisInvoice->ugst / 2;
        //         $reverse_charge_sgst += $thisInvoice->ugst / 2;
        //     }
        //     $reverse_charge_cess += $thisInvoice->cess;
        // }

        foreach ($purchases as $purchase) {
            $reverse_charge_igst += $purchase->igst;
            $reverse_charge_cgst += $purchase->cgst;
            $reverse_charge_sgst += $purchase->sgst;
            if ($purchase->ugst != null) {
                $reverse_charge_cgst += $purchase->ugst / 2;
                $reverse_charge_sgst += $purchase->ugst / 2;
            }
            $reverse_charge_cess += $purchase->cess;
        }

        $gst_liability_late_fees_igst = 0;
        $gst_liability_late_fees_cgst = 0;
        $gst_liability_late_fees_sgst = 0;
        $gst_liability_late_fees_cess = 0;

        $gst_liability_interest_igst = 0;
        $gst_liability_interest_cgst = 0;
        $gst_liability_interest_sgst = 0;
        $gst_liability_interest_cess = 0;

        $gst_liability_penalty_igst = 0;
        $gst_liability_penalty_cgst = 0;
        $gst_liability_penalty_sgst = 0;
        $gst_liability_penalty_cess = 0;

        $gst_liability_others_igst = 0;
        $gst_liability_others_cgst = 0;
        $gst_liability_others_sgst = 0;
        $gst_liability_others_cess = 0;

        $liability_ledger_balances = GSTLiabilityLedgerBalance::where('user_id', auth()->user()->id)->where('status', 'changing')->whereBetween('date', [$from_date, $to_date])->get();

        $liability_latefees_setoffs = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_type', 'latefees')->whereBetween('liability_date', [$from_date, $to_date])->get();

        $liability_interest_setoffs = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_type', 'interest')->whereBetween('liability_date', [$from_date, $to_date])->get();

        $liability_penalty_setoffs = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_type', 'penalty')->whereBetween('liability_date', [$from_date, $to_date])->get();

        $liability_others_setoffs = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_type', 'others')->whereBetween('liability_date', [$from_date, $to_date])->get();

        // return $liability_ledger_balances;
        
        // return $liability_latefees_setoffs;

        // return $liability_interest_setoffs;

        // return $liability_penalty_setoffs;

        // return $liability_others_setoffs;

        foreach($liability_ledger_balances as $liability){
            $gst_liability_late_fees_igst += $liability->igst_late_fees;
            $gst_liability_late_fees_cgst += $liability->cgst_late_fees;
            $gst_liability_late_fees_sgst += $liability->sgst_late_fees;
            $gst_liability_late_fees_cess += $liability->cess_late_fees;

            $gst_liability_interest_igst += $liability->igst_interest;
            $gst_liability_interest_cgst += $liability->cgst_interest;
            $gst_liability_interest_sgst += $liability->sgst_interest;
            $gst_liability_interest_cess += $liability->cess_interest;

            $gst_liability_penalty_igst += $liability->igst_penalty;
            $gst_liability_penalty_cgst += $liability->cgst_penalty;
            $gst_liability_penalty_sgst += $liability->sgst_penalty;
            $gst_liability_penalty_cess += $liability->cess_penalty;

            $gst_liability_others_igst += $liability->igst_others;
            $gst_liability_others_cgst += $liability->cgst_others;
            $gst_liability_others_sgst += $liability->sgst_others;
            $gst_liability_others_cess += $liability->cess_others;
        }

        foreach($liability_latefees_setoffs as $setoff)
        {
            $gst_liability_late_fees_igst -= $setoff->liability_igst_latefees;
            $gst_liability_late_fees_cgst -= $setoff->liability_cgst_latefees;
            $gst_liability_late_fees_sgst -= $setoff->liability_sgst_latefees;
            $gst_liability_late_fees_cess -= $setoff->liability_cess_latefees;
        }

        foreach($liability_interest_setoffs as $setoff)
        {
            $gst_liability_interest_igst -= $setoff->liability_igst_interest;
            $gst_liability_interest_cgst -= $setoff->liability_cgst_interest;
            $gst_liability_interest_sgst -= $setoff->liability_sgst_interest;
            $gst_liability_interest_cess -= $setoff->liability_cess_interest;
        }

        foreach($liability_penalty_setoffs as $setoff)
        {
            $gst_liability_penalty_igst -= $setoff->liability_igst_penalty;
            $gst_liability_penalty_cgst -= $setoff->liability_cgst_penalty;
            $gst_liability_penalty_sgst -= $setoff->liability_sgst_penalty;
            $gst_liability_penalty_cess -= $setoff->liability_cess_penalty;
        }

        foreach($liability_others_setoffs as $setoff)
        {
            $gst_liability_others_igst -= $setoff->liability_igst_others;
            $gst_liability_others_cgst -= $setoff->liability_cgst_others;
            $gst_liability_others_sgst -= $setoff->liability_sgst_others;
            $gst_liability_others_cess -= $setoff->liability_cess_others;
        }

        $banks = User::find(auth()->user()->id)->banks()->get();

        $cash_gst_payment = GSTCashLedgerBalance::where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();
        $gst_payment = User::find(Auth::user()->id)->gstPayments()->orderBy('id', 'desc')->first();

        if($cash_gst_payment && $gst_payment){
            if (\Carbon\Carbon::parse($cash_gst_payment->created_at) > \Carbon\Carbon::parse($gst_payment->created_at)) {
                $last_gst_payment = $cash_gst_payment;
            } else {
                $last_gst_payment = $gst_payment;
            }
        } else {
            $last_gst_payment = $cash_gst_payment ? $cash_gst_payment : $gst_payment;
        }

        $voucher_no = null;
        $myerrors = array();

        if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') {

            if ($last_gst_payment && isset($last_gst_payment->voucher_no)) {
                $width = isset(auth()->user()->gstPaymentSetting) ? auth()->user()->gstPaymentSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['voucher_no'][] = 'Invalid Max-length provided. Please update your gst payment settings.';
                        break;
                    case 1:
                        if ($last_gst_payment->voucher_no > 9) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 2:
                        if ($last_gst_payment->voucher_no > 99) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 3:
                        if ($last_gst_payment->voucher_no > 999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 4:
                        if ($last_gst_payment->voucher_no > 9999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 5:
                        if ($last_gst_payment->voucher_no > 99999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 6:
                        if ($last_gst_payment->voucher_no > 999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 7:
                        if ($last_gst_payment->voucher_no > 9999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 8:
                        if ($last_gst_payment->voucher_no > 99999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 9:
                        if ($last_gst_payment->voucher_no > 999999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['voucher_no'][] = 'Applicable date expired for GST Payment. Please update your gst payment settings.';
            }

            if ($last_gst_payment) {
                if(isset($last_gst_payment->voucher_no_type) && $last_gst_payment->voucher_no_type == 'auto'){
                    if (\Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->updated_at) > \Carbon\Carbon::parse($last_gst_payment->created_at)) {
                        $voucher_no = isset(auth()->user()->gstPaymentSetting->starting_no) ? auth()->user()->gstPaymentSetting->starting_no - 1 : 0;
                    } else {
                        $voucher_no = $last_gst_payment->voucher_no;
                    }
                } else {
                    $voucher_no = isset(auth()->user()->gstPaymentSetting->starting_no) ? auth()->user()->gstPaymentSetting->starting_no - 1 : 0;
                }

            } else {
                $voucher_no = isset(auth()->user()->gstPaymentSetting->starting_no) ? auth()->user()->gstPaymentSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('gst.setoff', compact('banks', 'other_than_reverse_charge_igst', 'other_than_reverse_charge_cgst', 'other_than_reverse_charge_sgst', 'other_than_reverse_charge_cess', 'reverse_charge_igst', 'reverse_charge_cgst', 'reverse_charge_sgst', 'reverse_charge_cess', 'gst_liability_late_fees_igst', 'gst_liability_late_fees_cgst', 'gst_liability_late_fees_sgst', 'gst_liability_late_fees_cess', 'gst_liability_interest_igst', 'gst_liability_interest_cgst', 'gst_liability_interest_sgst', 'gst_liability_interest_cess', 'gst_liability_penalty_igst', 'gst_liability_penalty_cgst', 'gst_liability_penalty_sgst', 'gst_liability_penalty_cess', 'gst_liability_others_igst', 'gst_liability_others_cgst', 'gst_liability_others_sgst', 'gst_liability_others_cess', 'voucher_no'))->with('myerrors', $myerrors);
    }

    public function post_gst_setoff(Request $request)
    {

        // return $request->all();

        $gst_set_off = new GSTSetOff;

        $gst_set_off->cgst = $request->cgst;
        $gst_set_off->sgst = $request->sgst;
        $gst_set_off->igst = $request->igst;
        $gst_set_off->cess = $request->cess;
        $gst_set_off->total = $request->total;
        $gst_set_off->voucher_no = $request->voucher_no;
        if( isset(auth()->user()->gstPaymentSetting) && isset(auth()->user()->gstPaymentSetting->bill_no_type) ){
            $gst_set_off->voucher_no_type = auth()->user()->gstPaymentSetting->bill_no_type;
        } else {
            $gst_set_off->voucher_no_type = 'manual';
        }
        
        $gst_set_off->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $gst_set_off->cin = $request->cin;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);

            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if ($cash && $bank && $pos) {
                $gst_set_off->type_of_payment = 'combined';

                $gst_set_off->cash_payment = $request->cashed_amount;
                $gst_set_off->bank_payment = $request->banked_amount;
                $gst_set_off->pos_payment = $request->posed_amount;

                $gst_set_off->bank_id = $request->bank;
                $gst_set_off->bank_cheque = $request->bank_cheque;
                $gst_set_off->pos_bank_id = $request->pos_bank;
            } else if ($pos && $bank) {
                $gst_set_off->type_of_payment = 'pos+bank';

                $gst_set_off->bank_payment = $request->banked_amount;
                $gst_set_off->pos_payment = $request->posed_amount;

                $gst_set_off->bank_id = $request->bank;
                $gst_set_off->bank_cheque = $request->bank_cheque;
                $gst_set_off->pos_bank_id = $request->pos_bank;
            } else if ($pos && $cash) {
                $gst_set_off->type_of_payment = 'pos+cash';

                $gst_set_off->cash_payment = $request->cashed_amount;
                $gst_set_off->pos_payment = $request->posed_amount;

                $gst_set_off->pos_bank_id = $request->pos_bank;
            } else if ($bank && $cash) {
                $gst_set_off->type_of_payment = 'bank+cash';

                $gst_set_off->cash_payment = $request->cashed_amount;
                $gst_set_off->bank_payment = $request->banked_amount;

                $gst_set_off->bank_id = $request->bank;
                $gst_set_off->bank_cheque = $request->bank_cheque;
            } else if ($bank) {
                $gst_set_off->type_of_payment = 'bank';

                $gst_set_off->bank_payment = $request->banked_amount;

                $gst_set_off->bank_id = $request->bank;
                $gst_set_off->bank_cheque = $request->bank_cheque;
            } else if ($cash) {
                $gst_set_off->type_of_payment = 'cash';

                $gst_set_off->cash_payment = $request->cashed_amount;
            } else if ($pos) {
                $gst_set_off->type_of_payment = 'pos';

                $gst_set_off->pos_payment = $request->posed_amount;

                $gst_set_off->pos_bank_id = $request->pos_bank;
            }
        } else {
            $gst_set_off->type_of_payment = 'no_payment';
        }

        if($request->amount_received != ''){
            $gst_set_off->amount_received = $request->amount_received;
        } else {
            $gst_set_off->amount_received = 0;
        }

        $gst_set_off->type = $request->type;
        $gst_set_off->user_id = auth()->user()->id;

        if($gst_set_off->save()){
            // return redirect()->back()->with('success', 'Data saved successfully');
            return response()->json(["success" => true, "message" => "Data saved successfully"]);
        } else {
            // return redirect()->back()->with('failure', 'Failed to save data');
            return response()->json(["success" => false, "message" => "Failed to save data"]);
        }
    }

    public function gst_composition()
    {
        return view('gst.composition');
    }

    public function post_gst_payable(Request $request)
    {
        $gst_payable = new GSTPayable;


        $gst_payable->cgst = $request->cgst;
        $gst_payable->sgst = $request->sgst;
        $gst_payable->igst = $request->igst;
        $gst_payable->cess = $request->cess;
        $gst_payable->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $gst_payable->user_id = auth()->user()->id;

        if ($gst_payable->save()) {
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save Data');
        }
    }

    public function post_gst_to_be_paid_in_cash(Request $request)
    {
        $gst_to_be_paid_in_cash = new GSTToBePaidInCash;

        // $gst_to_be_paid_in_cash

        $gst_to_be_paid_in_cash->cgst_late_fees = $request->cgst_late_fees;
        $gst_to_be_paid_in_cash->sgst_late_fees = $request->sgst_late_fees;
        $gst_to_be_paid_in_cash->igst_late_fees = $request->igst_late_fees;
        $gst_to_be_paid_in_cash->cess_late_fees = $request->cess_late_fees;

        $gst_to_be_paid_in_cash->cgst_interest = $request->cgst_interest;
        $gst_to_be_paid_in_cash->sgst_interest = $request->sgst_interest;
        $gst_to_be_paid_in_cash->igst_interest = $request->igst_interest;
        $gst_to_be_paid_in_cash->cess_interest = $request->cess_interest;

        $gst_to_be_paid_in_cash->cgst_penalty = $request->cgst_penalty;
        $gst_to_be_paid_in_cash->sgst_penalty = $request->sgst_penalty;
        $gst_to_be_paid_in_cash->igst_penalty = $request->igst_penalty;
        $gst_to_be_paid_in_cash->cess_penalty = $request->cess_penalty;

        $gst_to_be_paid_in_cash->cgst_others = $request->cgst_others;
        $gst_to_be_paid_in_cash->sgst_others = $request->sgst_others;
        $gst_to_be_paid_in_cash->igst_others = $request->igst_others;
        $gst_to_be_paid_in_cash->cess_others = $request->cess_others;

        $gst_to_be_paid_in_cash->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));

        $gst_to_be_paid_in_cash->user_id = auth()->user()->id;

        if($gst_to_be_paid_in_cash->save()){
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save Data');
        }
    }


    public function ineligible_reversal_of_input(Request $request)
    {

        // if($request->has('month') && $request->has('year')){
        //     $month = Carbon::createFromFormat('m', $request->month)->format('m');
        //     $year = Carbon::createFromFormat('Y', $request->year)->format('Y');
        // } else {
        //     $month = Carbon::now()->format('m');
        //     $year = Carbon::now()->format('Y');
        // }
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $data['igst'] = 0;
        $data['sgst'] = 0;
        $data['cgst'] = 0;
        $data['cess'] = 0;

        $purchases = User::find(auth()->user()->id)->purchases()->where(function ($query) {
            $query->where('gst_classification', '!=', 'rcm')->orWhereNull('gst_classification');
        })->whereBetween('bill_date', [$from_date, $to_date])->get();

        foreach($purchases as $thisBill){
            $party = Party::findOrFail($thisBill->party_id);

            if ($party->balance_type == 'creditor' && $party->registered == 0) {
                continue;
            }

            $data['igst'] += $thisBill->igst;
            $data['cgst'] += $thisBill->cgst;
            $data['sgst'] += $thisBill->sgst;
            if ($thisBill->ugst != null || $thisBill->ugst != 0) {
                $data['cgst'] += $thisBill->ugst / 2;
                $data['sgst'] += $thisBill->ugst / 2;
            }

            $data['cess'] += $thisBill->cess;
        }

        $credit_ledger_balances = GSTCreditLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get();

        foreach ($credit_ledger_balances as $balance) {

            $data['igst'] += $balance->igst;
            $data['cgst'] += $balance->cgst;
            $data['sgst'] += $balance->sgst;
            $data['cess'] += $balance->cess;
        }

        $credit_setoffs = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereBetween('otr_date', [$from_date, $to_date])->get();

        foreach($credit_setoffs as $setoff){
            $igst_setoff = $setoff->otr_input_igst_igst + $setoff->otr_input_cgst_igst + $setoff->otr_input_sgst_igst + $setoff->otr_input_cess_igst;

            $cgst_setoff = $setoff->otr_input_igst_cgst + $setoff->otr_input_cgst_cgst + $setoff->otr_input_sgst_cgst + $setoff->otr_input_cess_cgst;
            
            $sgst_setoff = $setoff->otr_input_igst_sgst + $setoff->otr_input_cgst_sgst + $setoff->otr_input_sgst_sgst + $setoff->otr_input_cess_sgst;
            
            $cess_setoff = $setoff->otr_input_igst_cess + $setoff->otr_input_cgst_cess + $setoff->otr_input_sgst_cess + $setoff->otr_input_cess_cess;

            $data['igst'] -= $igst_setoff;
            $data['cgst'] -= $cgst_setoff;
            $data['sgst'] -= $sgst_setoff;
            $data['cess'] -= $cess_setoff;
        }

        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

        foreach ($debitNotes as $note) {
            if($note->reason == 'purchase_return' || $note->reason == 'new_rate_or_discount_value_with_gst') {
                if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
                    $data['cgst'] -= $note->gst / 2;
                    $data['sgst'] -= $note->gst / 2;
                } else {
                    $data['igst'] -= $request->gst;
                }
            }
        }

        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

        foreach ($creditNotes as $note) {
            if($note->reason == 'new_rate_or_discount_value_with_gst'){
                if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
                    $data['cgst'] += $note->gst / 2;
                    $data['sgst'] += $note->gst / 2;
                }
                else {
                    $data['igst'] += $request->gst;
                }
            }
        }

        return view('gst.ineligible_reversal_of_input', $data);
    }

    public function post_ineligible_reversal_of_input(Request $request)
    {
        // return $request->all();

        $data = new IneligibleReversalOfInput();

        $data->itc_cgst = $request->itc_cgst - $request->reverse_cgst;
        $data->reverse_cgst = $request->reverse_cgst;
        $data->ineligible_cgst = $request->ineligible_cgst;

        $data->itc_sgst = $request->itc_sgst - $request->reverse_sgst;
        $data->reverse_sgst = $request->reverse_sgst;
        $data->ineligible_sgst = $request->ineligible_sgst;

        $data->itc_igst = $request->itc_igst - $request->reverse_igst;
        $data->reverse_igst = $request->reverse_igst;
        $data->ineligible_igst = $request->ineligible_igst;

        $data->itc_cess = $request->itc_cess - $request->reverse_cess;
        $data->reverse_cess = $request->reverse_cess;
        $data->ineligible_cess = $request->ineligible_cess;

        $data->itc_total = $request->itc_total - $request->reverse_total;
        $data->reverse_total = $request->reverse_total;
        $data->ineligible_total = $request->ineligible_total;

        $data->submit_date = Carbon::createFromFormat('d/m/Y', $request->submit_date)->format('Y-m-d');

        $data->narration = $request->narration;

        $data->user_id = Auth::user()->id;

        if($data->save()){
            return redirect()->back()->with('success', 'Data submitted successfully!!');
        } else {
            return redirect()->back()->with('failure', 'Failed to submit data');
        }
    }

    public function show_ineligible_reversal_of_input_dates(Request $request)
    {
        if($request->filled('fix_month')){
            $fix_month = $request->fix_month;
        }else {
            $fix_month = \Carbon\Carbon::now()->format('m');
        }

        $input = IneligibleReversalOfInput::where('user_id', auth()->user()->id)->whereMonth('submit_date', $fix_month)->get();

        return view('gst.before_show_ineligible_reversal_of_input', compact('input'));
    }

    public function show_ineligible_reversal_of_input(Request $request)
    {
        if ($request->has('fix_date')) {
            $submit_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->fix_date)));
        } else {
            $submit_date = Carbon::now()->format('Y-m-d');
        }

        $input = IneligibleReversalOfInput::where('user_id', auth()->user()->id)->where('submit_date', $submit_date)->first();

        return view('gst.show_ineligible_reversal_of_input', compact('input'));
    }

    public function post_other_than_reverse_charge(Request $request)
    {
        $gst_set_off = new GSTSetoffOtherThanReverseCharge;

        $gst_set_off->ot_reverse_charge_igst = $request->ot_reverse_charge_igst;
        $gst_set_off->otr_input_igst_igst = $request->otr_input_igst_igst;
        $gst_set_off->otr_input_igst_cgst = $request->otr_input_igst_cgst;
        $gst_set_off->otr_input_igst_sgst = $request->otr_input_igst_sgst;
        $gst_set_off->otr_input_igst_cess = $request->otr_input_igst_cess;
        $gst_set_off->otr_ptgcl_igst = $request->otr_ptgcl_igst;
        $gst_set_off->otr_btbpic_igst = $request->otr_btbpic_igst;

        $gst_set_off->ot_reverse_charge_cgst = $request->ot_reverse_charge_cgst;
        $gst_set_off->otr_input_cgst_igst = $request->otr_input_cgst_igst;
        $gst_set_off->otr_input_cgst_cgst = $request->otr_input_cgst_cgst;
        $gst_set_off->otr_input_cgst_sgst = $request->otr_input_cgst_sgst;
        $gst_set_off->otr_input_cgst_cess = $request->otr_input_cgst_cess;
        $gst_set_off->otr_ptgcl_cgst = $request->otr_ptgcl_cgst;
        $gst_set_off->otr_btbpic_cgst = $request->otr_btbpic_cgst;

        $gst_set_off->ot_reverse_charge_sgst = $request->ot_reverse_charge_sgst;
        $gst_set_off->otr_input_sgst_igst = $request->otr_input_sgst_igst;
        $gst_set_off->otr_input_sgst_cgst = $request->otr_input_sgst_cgst;
        $gst_set_off->otr_input_sgst_sgst = $request->otr_input_sgst_sgst;
        $gst_set_off->otr_input_sgst_cess = $request->otr_input_sgst_cess;
        $gst_set_off->otr_ptgcl_sgst = $request->otr_ptgcl_sgst;
        $gst_set_off->otr_btbpic_sgst = $request->otr_btbpic_sgst;

        $gst_set_off->ot_reverse_charge_cess = $request->ot_reverse_charge_cess;
        $gst_set_off->otr_input_cess_igst = $request->otr_input_cess_igst;
        $gst_set_off->otr_input_cess_cgst = $request->otr_input_cess_cgst;
        $gst_set_off->otr_input_cess_sgst = $request->otr_input_cess_sgst;
        $gst_set_off->otr_input_cess_cess = $request->otr_input_cess_cess;
        $gst_set_off->otr_ptgcl_cess = $request->otr_ptgcl_cess;
        $gst_set_off->otr_btbpic_cess = $request->otr_btbpic_cess;

        $gst_set_off->otr_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->otr_date)));

        $gst_set_off->user_id = auth()->user()->id;

        $year = \Carbon\Carbon::parse($gst_set_off->otr_date)->format('Y');

        $cash_total_igst = $gst_set_off->otr_ptgcl_igst;
        $cash_total_cgst = $gst_set_off->otr_ptgcl_cgst;
        $cash_total_sgst = $gst_set_off->otr_ptgcl_sgst;
        $cash_total_cess = $gst_set_off->otr_ptgcl_cess;

        $credit_total_igst = $gst_set_off->otr_input_igst_igst + $gst_set_off->otr_input_cgst_igst + $gst_set_off->otr_input_sgst_igst + $gst_set_off->otr_input_cess_igst;
        $credit_total_cgst = $gst_set_off->otr_input_igst_cgst + $gst_set_off->otr_input_cgst_cgst + $gst_set_off->otr_input_sgst_cgst + $gst_set_off->otr_input_cess_cgst;
        $credit_total_sgst = $gst_set_off->otr_input_igst_sgst + $gst_set_off->otr_input_cgst_sgst + $gst_set_off->otr_input_sgst_sgst + $gst_set_off->otr_input_cess_sgst;
        $credit_total_cess = $gst_set_off->otr_input_igst_cess + $gst_set_off->otr_input_cgst_cess + $gst_set_off->otr_input_sgst_cess + $gst_set_off->otr_input_cess_cess;

        $liability_total_igst = $gst_set_off->ot_reverse_charge_igst;
        $liability_total_cgst = $gst_set_off->ot_reverse_charge_cgst;
        $liability_total_sgst = $gst_set_off->ot_reverse_charge_sgst;
        $liability_total_cess = $gst_set_off->ot_reverse_charge_cess;

        $cash_ledger_balance = GSTCashLedgerBalance::whereYear('date', $year)->first();

        $cash_ledger_balance->igst_tax -= $cash_total_igst;
        $cash_ledger_balance->cgst_tax -= $cash_total_cgst;
        $cash_ledger_balance->sgst_tax -= $cash_total_sgst;
        $cash_ledger_balance->cess_tax -= $cash_total_cess;

        $cash_ledger_balance->save();
        
        $credit_ledger_balance = GSTCreditLedgerBalance::whereYear('date', $year)->first();

        $credit_ledger_balance->igst -= $credit_total_igst;
        $credit_ledger_balance->cgst -= $credit_total_cgst;
        $credit_ledger_balance->sgst -= $credit_total_sgst;
        $credit_ledger_balance->cess -= $credit_total_cess;

        $credit_ledger_balance->save();

        $liability_ledger_balance = GSTLiabilityLedgerBalance::whereYear('date', $year)->first();

        $liability_ledger_balance->igst_tax_other_than_reverse_charge -= $liability_total_igst;
        $liability_ledger_balance->cgst_tax_other_than_reverse_charge -= $liability_total_cgst;
        $liability_ledger_balance->sgst_tax_other_than_reverse_charge -= $liability_total_sgst;
        $liability_ledger_balance->cess_tax_other_than_reverse_charge -= $liability_total_cess;

        $liability_ledger_balance->save();

        if($gst_set_off->save()){
            return response()->json('success');
        } else {
            return response()->json('failure');
        }
    }

    public function post_reverse_charge(Request $request)
    {
        $gst_set_off = new GSTSetoffReverseCharge;

        $gst_set_off->reverse_charge_igst = $request->reverse_charge_igst;
        $gst_set_off->reverse_charge_ptgcl_igst = $request->reverse_charge_ptgcl_igst;
        $gst_set_off->reverse_charge_btbpic_igst = $request->reverse_charge_btbpic_igst;

        $gst_set_off->reverse_charge_cgst = $request->reverse_charge_cgst;
        $gst_set_off->reverse_charge_ptgcl_cgst = $request->reverse_charge_ptgcl_cgst;
        $gst_set_off->reverse_charge_btbpic_cgst = $request->reverse_charge_btbpic_cgst;

        $gst_set_off->reverse_charge_sgst = $request->reverse_charge_sgst;
        $gst_set_off->reverse_charge_ptgcl_sgst = $request->reverse_charge_ptgcl_sgst;
        $gst_set_off->reverse_charge_btbpic_sgst = $request->reverse_charge_btbpic_sgst;

        $gst_set_off->reverse_charge_cess = $request->reverse_charge_cess;
        $gst_set_off->reverse_charge_ptgcl_cess = $request->reverse_charge_ptgcl_cess;
        $gst_set_off->reverse_charge_btbpic_cess = $request->reverse_charge_btbpic_cess;

        $gst_set_off->r_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->r_date)));

        $gst_set_off->user_id = auth()->user()->id;

        if ($gst_set_off->save()) {
            return response()->json('success');
        } else {
            return response()->json('failure');
        }
    }

    public function post_liability_charge(Request $request) {

        // return response()->json($request->all());

        $gst_set_off = new GSTSetoffLiabilityCharge;

        // $gst_set_off->liability_igst_latefees = $request->liability_igst_latefees;
        // $gst_set_off->liability_igst_interest = $request->liability_igst_interest;
        // $gst_set_off->liability_igst_penalty = $request->liability_igst_penalty;
        // $gst_set_off->liability_igst_others = $request->liability_igst_others;
        // $gst_set_off->liability_ptgcl_igst = $request->liability_ptgcl_igst;
        // $gst_set_off->liability_btbpic_igst = $request->liability_btbpic_igst;

        // $gst_set_off->liability_cgst_latefees = $request->liability_cgst_latefees;
        // $gst_set_off->liability_cgst_interest = $request->liability_cgst_interest;
        // $gst_set_off->liability_cgst_penalty = $request->liability_cgst_penalty;
        // $gst_set_off->liability_cgst_others = $request->liability_cgst_others;
        // $gst_set_off->liability_ptgcl_cgst = $request->liability_ptgcl_cgst;
        // $gst_set_off->liability_btbpic_cgst = $request->liability_btbpic_cgst;

        // $gst_set_off->liability_sgst_latefees = $request->liability_sgst_latefees;
        // $gst_set_off->liability_sgst_interest = $request->liability_sgst_interest;
        // $gst_set_off->liability_sgst_penalty = $request->liability_sgst_penalty;
        // $gst_set_off->liability_sgst_others = $request->liability_sgst_others;
        // $gst_set_off->liability_ptgcl_sgst = $request->liability_ptgcl_sgst;
        // $gst_set_off->liability_btbpic_sgst = $request->liability_btbpic_sgst;

        // $gst_set_off->liability_cess_latefees = $request->liability_cess_latefees;
        // $gst_set_off->liability_cess_interest = $request->liability_cess_interest;
        // $gst_set_off->liability_cess_penalty = $request->liability_cess_penalty;
        // $gst_set_off->liability_cess_others = $request->liability_cess_others;
        // $gst_set_off->liability_ptgcl_cess = $request->liability_ptgcl_cess;
        // $gst_set_off->liability_btbpic_cess = $request->liability_btbpic_cess;


        $gst_set_off->liability_igst_latefees = $request->liability_igst_latefees ?? 0;
        $gst_set_off->liability_ptgcl_latefees_igst = $request->liability_ptgcl_latefees_igst ?? 0;
        $gst_set_off->liability_btbpic_latefees_igst = $request->liability_btbpic_latefees_igst ?? 0;

        $gst_set_off->liability_igst_interest = $request->liability_igst_interest ?? 0;
        $gst_set_off->liability_ptgcl_interest_igst = $request->liability_ptgcl_interest_igst ?? 0;
        $gst_set_off->liability_btbpic_interest_igst = $request->liability_btbpic_interest_igst ?? 0;

        $gst_set_off->liability_igst_penalty = $request->liability_igst_penalty ?? 0;
        $gst_set_off->liability_ptgcl_penalty_igst = $request->liability_ptgcl_penalty_igst ?? 0;
        $gst_set_off->liability_btbpic_penalty_igst = $request->liability_btbpic_penalty_igst ?? 0;

        $gst_set_off->liability_igst_others = $request->liability_igst_others ?? 0;
        $gst_set_off->liability_ptgcl_others_igst = $request->liability_ptgcl_others_igst ?? 0;
        $gst_set_off->liability_btbpic_others_igst = $request->liability_btbpic_others_igst ?? 0;


        $gst_set_off->liability_cgst_latefees = $request->liability_cgst_latefees ?? 0;
        $gst_set_off->liability_ptgcl_latefees_cgst = $request->liability_ptgcl_latefees_cgst ?? 0;
        $gst_set_off->liability_btbpic_latefees_cgst = $request->liability_btbpic_latefees_cgst ?? 0;

        $gst_set_off->liability_cgst_interest = $request->liability_cgst_interest ?? 0;
        $gst_set_off->liability_ptgcl_interest_cgst = $request->liability_ptgcl_interest_cgst ?? 0;
        $gst_set_off->liability_btbpic_interest_cgst = $request->liability_btbpic_interest_cgst ?? 0;

        $gst_set_off->liability_cgst_penalty = $request->liability_cgst_penalty ?? 0;
        $gst_set_off->liability_ptgcl_penalty_cgst = $request->liability_ptgcl_penalty_cgst ?? 0;
        $gst_set_off->liability_btbpic_penalty_cgst = $request->liability_btbpic_penalty_cgst ?? 0;

        $gst_set_off->liability_cgst_others = $request->liability_cgst_others ?? 0;
        $gst_set_off->liability_ptgcl_others_cgst = $request->liability_ptgcl_others_cgst ?? 0;
        $gst_set_off->liability_btbpic_others_cgst = $request->liability_btbpic_others_cgst ?? 0;

        $gst_set_off->liability_sgst_latefees = $request->liability_sgst_latefees ?? 0;
        $gst_set_off->liability_ptgcl_latefees_sgst = $request->liability_ptgcl_latefees_sgst ?? 0;
        $gst_set_off->liability_btbpic_latefees_sgst = $request->liability_btbpic_latefees_sgst ?? 0;

        $gst_set_off->liability_sgst_interest = $request->liability_sgst_interest ?? 0;
        $gst_set_off->liability_ptgcl_interest_sgst = $request->liability_ptgcl_interest_sgst ?? 0;
        $gst_set_off->liability_btbpic_interest_sgst = $request->liability_btbpic_interest_sgst ?? 0;

        $gst_set_off->liability_sgst_penalty = $request->liability_sgst_penalty ?? 0;
        $gst_set_off->liability_ptgcl_penalty_sgst = $request->liability_ptgcl_penalty_sgst ?? 0;
        $gst_set_off->liability_btbpic_penalty_sgst = $request->liability_btbpic_penalty_sgst ?? 0;

        $gst_set_off->liability_sgst_others = $request->liability_sgst_others ?? 0;
        $gst_set_off->liability_ptgcl_others_sgst = $request->liability_ptgcl_others_sgst ?? 0;
        $gst_set_off->liability_btbpic_others_sgst = $request->liability_btbpic_others_sgst ?? 0;

        $gst_set_off->liability_cess_latefees = $request->liability_cess_latefees ?? 0;
        $gst_set_off->liability_ptgcl_latefees_cess = $request->liability_ptgcl_latefees_cess ?? 0;
        $gst_set_off->liability_btbpic_latefees_cess = $request->liability_btbpic_latefees_cess ?? 0;

        $gst_set_off->liability_cess_interest = $request->liability_cess_interest ?? 0;
        $gst_set_off->liability_ptgcl_interest_cess = $request->liability_ptgcl_interest_cess ?? 0;
        $gst_set_off->liability_btbpic_interest_cess = $request->liability_btbpic_interest_cess ?? 0;

        $gst_set_off->liability_cess_penalty = $request->liability_cess_penalty ?? 0;
        $gst_set_off->liability_ptgcl_penalty_cess = $request->liability_ptgcl_penalty_cess ?? 0;
        $gst_set_off->liability_btbpic_penalty_cess = $request->liability_btbpic_penalty_cess ?? 0;

        $gst_set_off->liability_cess_others = $request->liability_cess_others ?? 0;
        $gst_set_off->liability_ptgcl_others_cess = $request->liability_ptgcl_others_cess ?? 0;
        $gst_set_off->liability_btbpic_others_cess = $request->liability_btbpic_others_cess ?? 0;

        $gst_set_off->liability_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->liability_date)));

        $gst_set_off->liability_type = $request->liability_type;

        $gst_set_off->user_id = auth()->user()->id;

        // return response()->json($gst_set_off);

        if ($gst_set_off->save()) {
            return response()->json('success');
        } else {
            return response()->json('failure');
        }
    }

    public function show_setoff_dates(Request $request)
    {
        if($request->filled('fix_month')){
            $fix_month = $request->fix_month;
        }else {
            $fix_month = \Carbon\Carbon::now()->format('m');
        }

        $otherThanReverseCharge = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereMonth('otr_date', $fix_month)->get();
        $reverseCharge = GSTSetoffReverseCharge::where('user_id', auth()->user()->id)->whereMonth('r_date', $fix_month)->get();
        $liabilityCharge = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->whereMonth('liability_date', $fix_month)->get();

        return view('gst.before_show_setoff', compact('otherThanReverseCharge', 'reverseCharge', 'liabilityCharge'));
    }

    public function show_gst_setoff(Request $request) {

        // if($request->has('from_date') && $request->has('to_date')){
        //     $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
        //     $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        // } else {
        //     $from_date = auth()->user()->profile->financial_year_from;
        //     $to_date = auth()->user()->profile->financial_year_to;
        // }

        if($request->filled('fix_date')){
            $fix_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->fix_date)->format('Y-m-d');
        } else {
            $fix_date = \Carbon\Carbon::now()->format('Y-m-d');
        }

        $gst_set_off_other_than_reverse_charge = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->where('otr_date', $fix_date)->first();

        $gst_set_off_reverse_charge = GSTSetoffReverseCharge::where('user_id', auth()->user()->id)->where('r_date', $fix_date)->first();

        $gst_set_off_latefees = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_date', $fix_date)->where('liability_type', 'latefees')->first();
        $gst_set_off_interest = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_date', $fix_date)->where('liability_type', 'interest')->first();
        $gst_set_off_penalty = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_date', $fix_date)->where('liability_type', 'penalty')->first();
        $gst_set_off_others = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->where('liability_date', $fix_date)->where('liability_type', 'others')->first();
        
        // return $gst_set_off_reverse_charge;

        return view('gst.show_setoff', compact('gst_set_off_other_than_reverse_charge', 'gst_set_off_reverse_charge', 'gst_set_off_latefees', 'gst_set_off_interest', 'gst_set_off_penalty', 'gst_set_off_others'));
    }

    public function show_calculated_gst_ledger(Request $request)
    {

        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }


        $liability = array();
        $cash = array();
        $credit = array();

        $banks = Bank::all();

        $liability_ledger_balances = GSTLiabilityLedgerBalance::where('user_id', auth()->user()->id)->where('status', 'changing')->whereBetween('date', [$from_date, $to_date])->get();

        // for modal
        $fixed_liablility_balance = GSTLiabilityLedgerBalance::where('user_id', auth()->user()->id)->where('status', 'fixed')->whereBetween('date', [$from_date, $to_date])->first();
        // for modal

        $fixed_liablility_balances = GSTLiabilityLedgerBalance::where('user_id', auth()->user()->id)->where('status', 'fixed')->whereBetween('date', [$from_date, $to_date])->get();

        $invoices = User::find(Auth::user()->id)->invoices()->where(function ($query) {
            $query->where('gst_classification', '!=', 'rcm')->orWhereNull('gst_classification');
        })->whereBetween('invoice_date', [$from_date, $to_date])->get();
        // return $invoices;

        
        $purchases = User::find(Auth::user()->id)->purchases()
        ->where('gst_classification', 'rcm')
        ->whereBetween('bill_date', [$from_date, $to_date])
        ->get();
        // return $purchases;
        
        $rcm_invoices = User::find(Auth::user()->id)->invoices()->where('gst_classification', 'rcm')->whereBetween('invoice_date', [$from_date, $to_date])->get();
        
        // return $rcm_invoices;
        
        $other_than_reverse_charge_setoffs = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereBetween('otr_date', [$from_date, $to_date])->get();
        // return $other_than_reverse_charge_setoffs;
        
        $reverse_charge_setoffs = GSTSetoffReverseCharge::where('user_id', auth()->user()->id)->whereBetween('r_date', [$from_date, $to_date])->get();

        //add to liablitiy ledger
        $payments_to_add_to_liability_ledger = User::find(Auth::user()->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->get();
        
        
        //for testing purposes
        // return $invoices; 

        // used in cash and liability
        $gst_set_off_liability = GSTSetoffLiabilityCharge::where('user_id', auth()->user()->id)->whereBetween('liability_date', [$from_date, $to_date])->get();
        // used in cash and liability

        // return $gst_set_off_liability;

        $balance_tax_reverse_charge_igst = 0;
        $balance_tax_reverse_charge_cgst = 0;
        $balance_tax_reverse_charge_sgst = 0;
        $balance_tax_reverse_charge_cess = 0;

        $balance_other_than_reverse_charge_igst = 0;
        $balance_other_than_reverse_charge_cgst = 0;
        $balance_other_than_reverse_charge_sgst = 0;
        $balance_other_than_reverse_charge_cess = 0;

        $other_than_reverse_charge_igst = 0;
        $other_than_reverse_charge_cgst = 0;
        $other_than_reverse_charge_sgst = 0;
        $other_than_reverse_charge_cess = 0;

        $reverse_charge_igst = 0;
        $reverse_charge_cgst = 0;
        $reverse_charge_sgst = 0;
        $reverse_charge_cess = 0;

        $liability_igst_latefees = 0;
        $liability_cgst_latefees = 0;
        $liability_sgst_latefees = 0;
        $liability_cess_latefees = 0;

        $liability_igst_interest = 0;
        $liability_cgst_interest = 0;
        $liability_sgst_interest = 0;
        $liability_cess_interest = 0;

        $liability_igst_penalty = 0;
        $liability_cgst_penalty = 0;
        $liability_sgst_penalty = 0;
        $liability_cess_penalty = 0;

        $liability_igst_others = 0;
        $liability_cgst_others = 0;
        $liability_sgst_others = 0;
        $liability_cess_others = 0;

        foreach($payments_to_add_to_liability_ledger as $payment) {
            $purchase = PurchaseRecord::findOrFail($payment->purchase_id);

            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::findOrFail($payment->party_id);

            if($purchase->gst_classification == "rcm") {
                if ($user_profile->place_of_business == $party->business_place) {
                    $reverse_charge_cgst += $payment->tds_gst/2;
                    $reverse_charge_sgst += $payment->tds_gst/2;

                    $reverse_charge_cgst += $payment->tcs_gst/2;
                    $reverse_charge_sgst += $payment->tcs_gst/2;
                } else {
                    $reverse_charge_igst += $payment->tds_gst;
                    $reverse_charge_igst += $payment->tcs_gst;
                }

            } else {
                if ($user_profile->place_of_business == $party->business_place) {
                    $other_than_reverse_charge_cgst += $payment->tds_gst/2;
                    $other_than_reverse_charge_sgst += $payment->tds_gst/2;

                    $other_than_reverse_charge_cgst += $payment->tcs_gst/2;
                    $other_than_reverse_charge_sgst += $payment->tcs_gst/2;
                } else {
                    $other_than_reverse_charge_igst += $payment->tds_gst;
                    $other_than_reverse_charge_igst += $payment->tcs_gst;
                }
            }
        }

        foreach ($other_than_reverse_charge_setoffs as $setoff) {
            $other_than_reverse_charge_igst -= $setoff->ot_reverse_charge_igst;
            $other_than_reverse_charge_cgst -= $setoff->ot_reverse_charge_cgst;
            $other_than_reverse_charge_sgst -= $setoff->ot_reverse_charge_sgst;
            $other_than_reverse_charge_cess -= $setoff->ot_reverse_charge_cess;
        }

        foreach ($reverse_charge_setoffs as $setoff) {
            $reverse_charge_igst -= $setoff->reverse_charge_igst;
            $reverse_charge_cgst -= $setoff->reverse_charge_cgst;
            $reverse_charge_sgst -= $setoff->reverse_charge_sgst;
            $reverse_charge_cess -= $setoff->reverse_charge_cess;
        }

        // return $reverse_charge_cgst;
        // return $reverse_charge_sgst;

        foreach ($gst_set_off_liability as $set_off) {
            $liability_igst_latefees -= $set_off->liability_igst_latefees;
            $liability_cgst_latefees -= $set_off->liability_cgst_latefees;
            $liability_sgst_latefees -= $set_off->liability_sgst_latefees;
            $liability_cess_latefees -= $set_off->liability_cess_latefees;

            $liability_igst_interest -= $set_off->liability_igst_interest;
            $liability_cgst_interest -= $set_off->liability_cgst_interest;
            $liability_sgst_interest -= $set_off->liability_sgst_interest;
            $liability_cess_interest -= $set_off->liability_cess_interest;

            $liability_igst_penalty -= $set_off->liability_igst_penalty;
            $liability_cgst_penalty -= $set_off->liability_cgst_penalty;
            $liability_sgst_penalty -= $set_off->liability_sgst_penalty;
            $liability_cess_penalty -= $set_off->liability_cess_penalty;

            $liability_igst_others -= $set_off->liability_igst_others;
            $liability_cgst_others -= $set_off->liability_cgst_others;
            $liability_sgst_others -= $set_off->liability_sgst_others;
            $liability_cess_others -= $set_off->liability_cess_others;
        }

        // foreach ($rcm_invoices as $thisInvoice) {

        //     $party = Party::findOrFail($thisInvoice->party_id);

        //     if ($party->balance_type == 'debitor' && $party->registered == 0) {
        //         continue;
        //     }

        //     $reverse_charge_igst += $thisInvoice->igst;
        //     $reverse_charge_cgst += $thisInvoice->cgst;
        //     $reverse_charge_sgst += $thisInvoice->sgst;
        //     if ($thisInvoice->ugst != null) {
        //         $reverse_charge_cgst += $thisInvoice->ugst / 2;
        //         $reverse_charge_sgst += $thisInvoice->ugst / 2;
        //     }
        //     $reverse_charge_cess += $thisInvoice->cess;
        // }

        foreach ( $purchases as $purchase ) {
            $party = Party::findOrFail($purchase->party_id);
            $user_profile = auth()->user()->profile;

            if ( $user_profile->place_of_business == $party->business_place ) {
                $reverse_charge_cgst = $purchase->item_total_rcm_gst/2;
                $reverse_charge_sgst = $purchase->item_total_rcm_gst/2;
            }
            // else if( $user_profile->place_of_business != $party->business_place && ( $party->business_place == 4 || $party->business_place == 7 || $party->business_place == 25 || $party->business_place == 26 || $party->business_place == 31 || $party->business_place == 34 || $party->business_place == 35 ) ) {
            //     $purchase_record->ugst = $request->item_total_gst;
            // }
            else {
                $reverse_charge_igst = $purchase->item_total_rcm_gst;
            }

            // $reverse_charge_igst += $purchase->igst;
            // $reverse_charge_cgst += $purchase->cgst;
            // $reverse_charge_sgst += $purchase->sgst;
            // if ($purchase->ugst != null) {
            //     $reverse_charge_cgst += $purchase->ugst / 2;
            //     $reverse_charge_sgst += $purchase->ugst / 2;
            // }
            $reverse_charge_cess += $purchase->cess;
        }

        // return $reverse_charge_cgst;
        // return $reverse_charge_sgst;

        foreach ($invoices as $thisInvoice) {

            $party = Party::findOrFail($thisInvoice->party_id);

            // if ($party->balance_type == 'debitor' && $party->registered == 0) {
            //     continue;
            // }

            if (($party->balance_type == 'creditor' && $party->registered == 0) || ($party->balance_type == 'creditor' && $party->registered == 3)) {
                continue;
            }

            $other_than_reverse_charge_igst += $thisInvoice->igst;
            $other_than_reverse_charge_cgst += $thisInvoice->cgst;
            $other_than_reverse_charge_sgst += $thisInvoice->sgst;
            if ($thisInvoice->ugst != null || $thisInvoice->ugst != 0) {
                $other_than_reverse_charge_cgst += $thisInvoice->ugst / 2;
                $other_than_reverse_charge_sgst += $thisInvoice->ugst / 2;
            }
            $other_than_reverse_charge_cess += $thisInvoice->cess;

            // $debitNotes = User::find(auth()->user()->id)->debitNotes()->where("bill_no", $thisInvoice->id)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();
            // $creditNotes = User::find(auth()->user()->id)->creditNotes()->where("invoice_id", $thisInvoice->id)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

            // foreach ($debitNotes as $note) {
            //     if (auth()->user()->profile->place_of_business == $thisInvoice->party->business_place) {
            //         $other_than_reverse_charge_cgst += $note->gst_percent_difference / 2;
            //         $other_than_reverse_charge_sgst += $note->gst_percent_difference / 2;
            //     }
            //     else {
            //         $other_than_reverse_charge_igst += $note->gst_percent_difference;
            //     }
            // }

            // foreach ($creditNotes as $note) {
            //     if (auth()->user()->profile->place_of_business == $thisInvoice->party->business_place) {
            //         $other_than_reverse_charge_cgst -= $note->gst_percent_difference / 2;
            //         $other_than_reverse_charge_sgst -= $note->gst_percent_difference / 2;
            //     }
            //     else {
            //         $other_than_reverse_charge_igst -= $note->gst_percent_difference;
            //     }
            // }

        }

        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'sale')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();
        $credit_notes = [];

        foreach ($creditNotes as $note) {
            $thisParty = Invoice::find($note->invoice_id)->party;
            if($note->reason == 'sale_return' || $note->reason == 'new_rate_or_discount_value_with_gst'){
                
                if (auth()->user()->profile->place_of_business == $thisParty->business_place) {
                    $credit_notes[] = $note;
                    $other_than_reverse_charge_cgst -= $note->gst / 2;
                    $other_than_reverse_charge_sgst -= $note->gst / 2;
                }
                else {
                    $other_than_reverse_charge_igst -= $note->gst;
                }
            }
        }

        //return $other_than_reverse_charge_cgst . ' ' . $other_than_reverse_charge_sgst . ' ' . $other_than_reverse_charge_igst;

        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

        foreach ($debitNotes as $note) {
            $thisParty = Invoice::find($note->bill_no)->party;
            if($note->reason == 'new_rate_or_discount_value_with_gst'){
                if (auth()->user()->profile->place_of_business == $thisParty->business_place) {
                    $other_than_reverse_charge_cgst += $note->gst / 2;
                    $other_than_reverse_charge_sgst += $note->gst / 2;
                }
                else {
                    $other_than_reverse_charge_igst += $note->gst;
                }
            }
        }

        // return $debitNotes;

        //return $other_than_reverse_charge_cgst . ' ' . $other_than_reverse_charge_sgst . ' ' . $other_than_reverse_charge_igst;

        $balance_liability_igst_late_fees = 0;
        $balance_liability_cgst_late_fees = 0;
        $balance_liability_sgst_late_fees = 0;
        $balance_liability_cess_late_fees = 0;
        $balance_liability_total_late_fees = 0;

        $balance_liability_igst_interest = 0;
        $balance_liability_cgst_interest = 0;
        $balance_liability_sgst_interest = 0;
        $balance_liability_cess_interest = 0;
        $balance_liability_total_interest = 0;

        $balance_liability_igst_penalty = 0;
        $balance_liability_cgst_penalty = 0;
        $balance_liability_sgst_penalty = 0;
        $balance_liability_cess_penalty = 0;
        $balance_liability_total_penalty = 0;

        $balance_liability_igst_others = 0;
        $balance_liability_cgst_others = 0;
        $balance_liability_sgst_others = 0;
        $balance_liability_cess_others = 0;
        $balance_liability_total_others = 0;

        // return $fixed_liablility_balance;

        foreach($fixed_liablility_balances as $fixed_liablility_balance){
            $balance_tax_reverse_charge_igst += $fixed_liablility_balance->igst_tax_reverse_charge;
            $balance_tax_reverse_charge_cgst += $fixed_liablility_balance->cgst_tax_reverse_charge;
            $balance_tax_reverse_charge_sgst += $fixed_liablility_balance->sgst_tax_reverse_charge;
            $balance_tax_reverse_charge_cess += $fixed_liablility_balance->cess_tax_reverse_charge;
    
            $balance_other_than_reverse_charge_igst += $fixed_liablility_balance->igst_tax_other_than_reverse_charge;
            $balance_other_than_reverse_charge_cgst += $fixed_liablility_balance->cgst_tax_other_than_reverse_charge;
            $balance_other_than_reverse_charge_sgst += $fixed_liablility_balance->sgst_tax_other_than_reverse_charge;
            $balance_other_than_reverse_charge_cess += $fixed_liablility_balance->cess_tax_other_than_reverse_charge;
        }

        foreach ($liability_ledger_balances as $balance) {

            $balance_liability_igst_late_fees += $balance->igst_late_fees;
            $balance_liability_cgst_late_fees += $balance->cgst_late_fees;
            $balance_liability_sgst_late_fees += $balance->sgst_late_fees;
            $balance_liability_cess_late_fees += $balance->cess_late_fees;

            $balance_liability_igst_interest += $balance->igst_interest;
            $balance_liability_cgst_interest += $balance->cgst_interest;
            $balance_liability_sgst_interest += $balance->sgst_interest;
            $balance_liability_cess_interest += $balance->cess_interest;

            $balance_liability_igst_penalty += $balance->igst_penalty;
            $balance_liability_cgst_penalty += $balance->cgst_penalty;
            $balance_liability_sgst_penalty += $balance->sgst_penalty;
            $balance_liability_cess_penalty += $balance->cess_penalty;

            $balance_liability_igst_others += $balance->igst_others;
            $balance_liability_cgst_others += $balance->cgst_others;
            $balance_liability_sgst_others += $balance->sgst_others;
            $balance_liability_cess_others += $balance->cess_others;

            $liability_total_late_fees = $balance_liability_igst_late_fees + $balance_liability_cgst_late_fees + $balance_liability_sgst_late_fees + $balance_liability_cess_late_fees;

            $liability_total_interest = $balance_liability_igst_interest + $balance_liability_cgst_interest + $balance_liability_sgst_interest + $balance_liability_cess_interest;

            $liability_total_penalty = $balance_liability_igst_penalty + $balance_liability_cgst_penalty + $balance_liability_sgst_penalty + $balance_liability_cess_penalty;

            $liability_total_others = $balance_liability_igst_others + $balance_liability_cgst_others + $balance_liability_sgst_others + $balance_liability_cess_others;
        }
        // -------------------------------------------------------------------------------------------------


        $fixed_cash_ledger_balance = GSTCashLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->orderBy('id', 'asc')->first();
        $cash_ledger_balances = GSTCashLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get();
        $make_payment_gst_setoff = GSTSetOff::where('user_id', auth()->user()->id)->whereIn('type', ['other_than_reverse_charge', 'reverse_charge'])->whereBetween('date', [$from_date, $to_date])->get(); //->where('type', 'other_than_reverse_charge')
        

        $gst_set_off_otrc = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereBetween('otr_date', [$from_date, $to_date])->get();

        $gst_set_off_rc = GSTSetoffReverseCharge::where('user_id', auth()->user()->id)->whereBetween('r_date', [$from_date, $to_date])->get();
        

        $advance_cash_payments = AdvanceCashPayment::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get();
        $last_advance_cash_payment = AdvanceCashPayment::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->orderBy('id', 'desc')->first();

        // add this to cash ledger
        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()
        ->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])
        ->get();

        // add this to cash ledger
        // $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()
        // ->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])
        // ->get();

        // add this to cash ledger
        $partyAmounts = User::find(Auth::user()->id)->partyRemainingAmounts()
        ->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])
        ->get();

        // return $payments;

        $make_payment_gst_setoff_liability_latefees = GSTSetOff::where('user_id', auth()->user()->id)->where('type', 'liability_latefees_charge')->whereBetween('date', [$from_date, $to_date])->get();
        $make_payment_gst_setoff_liability_interest = GSTSetOff::where('user_id', auth()->user()->id)->where('type', 'liability_interest_charge')->whereBetween('date', [$from_date, $to_date])->get();
        $make_payment_gst_setoff_liability_penalty = GSTSetOff::where('user_id', auth()->user()->id)->where('type', 'liability_penalty_charge')->whereBetween('date', [$from_date, $to_date])->get();
        $make_payment_gst_setoff_liability_others = GSTSetOff::where('user_id', auth()->user()->id)->where('type', 'liability_others_charge')->whereBetween('date', [$from_date, $to_date])->get();


        $cash_igst_tax = 0;
        $cash_cgst_tax = 0;
        $cash_sgst_tax = 0;
        $cash_cess_tax = 0;
        $cash_total_tax = 0;

        $cash_igst_interest = 0;
        $cash_cgst_interest = 0;
        $cash_sgst_interest = 0;
        $cash_cess_interest = 0;
        $cash_total_interest = 0;

        $cash_igst_late_fees = 0;
        $cash_sgst_late_fees = 0;
        $cash_cgst_late_fees = 0;
        $cash_cess_late_fees = 0;
        $cash_total_late_fees = 0;

        $cash_igst_penalty = 0;
        $cash_cgst_penalty = 0;
        $cash_sgst_penalty = 0;
        $cash_cess_penalty = 0;
        $cash_total_penalty = 0;

        $cash_igst_others = 0;
        $cash_cgst_others = 0;
        $cash_sgst_others = 0;
        $cash_cess_others = 0;
        $cash_total_others = 0;

        $balance_cash_igst_tax = 0;
        $balance_cash_cgst_tax = 0;
        $balance_cash_sgst_tax = 0;
        $balance_cash_cess_tax = 0;


        $balance_cash_igst_interest = 0;
        $balance_cash_cgst_interest = 0;
        $balance_cash_sgst_interest = 0;
        $balance_cash_cess_interest = 0;


        $balance_cash_igst_late_fees = 0;
        $balance_cash_sgst_late_fees = 0;
        $balance_cash_cgst_late_fees = 0;
        $balance_cash_cess_late_fees = 0;


        $balance_cash_igst_penalty = 0;
        $balance_cash_cgst_penalty = 0;
        $balance_cash_sgst_penalty = 0;
        $balance_cash_cess_penalty = 0;


        $balance_cash_igst_others = 0;
        $balance_cash_cgst_others = 0;
        $balance_cash_sgst_others = 0;
        $balance_cash_cess_others = 0;


        foreach($receipts as $receipt){
            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::findOrFail($receipt->party_id);

            if ($user_profile->place_of_business == $party->business_place) {
                $cash_cgst_tax += $receipt->tds_gst / 2;
                $cash_sgst_tax += $receipt->tds_gst / 2;

                $cash_cgst_tax += $receipt->tcs_gst / 2;
                $cash_sgst_tax += $receipt->tcs_gst / 2;
            }
            else {
                $cash_igst_tax += $receipt->tds_gst;

                $cash_igst_tax += $receipt->tcs_gst;
            }
        }

        // foreach($payments as $payment){
        //     $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
        //     $party = Party::findOrFail($payment->party_id);

        //     if ($user_profile->place_of_business == $party->business_place) {
        //         $cash_cgst_tax += $payment->tds_gst / 2;
        //         $cash_sgst_tax += $payment->tds_gst / 2;

        //         $cash_cgst_tax += $payment->tcs_gst / 2;
        //         $cash_sgst_tax += $payment->tcs_gst / 2;
        //     }
        //     else {
        //         $cash_igst_tax += $payment->tds_gst;

        //         $cash_igst_tax += $payment->tcs_gst;
        //     }
        // }

        foreach($partyAmounts as $amount){
            $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();
            $party = Party::findOrFail($amount->party_id);

            if ($user_profile->place_of_business == $party->business_place) {
                $cash_cgst_tax += $amount->tds_gst / 2;
                $cash_sgst_tax += $amount->tds_gst / 2;

                $cash_cgst_tax += $amount->tcs_gst / 2;
                $cash_sgst_tax += $amount->tcs_gst / 2;
            }
            else {
                $cash_igst_tax += $amount->tds_gst;
                $cash_igst_tax += $amount->tcs_gst;
            }
        }


        foreach ($advance_cash_payments as $payment) {
            $cash_igst_tax += $payment->igst_tax;
            $cash_cgst_tax += $payment->cgst_tax;
            $cash_sgst_tax += $payment->sgst_tax;
            $cash_cess_tax += $payment->cess_tax;

            $cash_igst_interest += $payment->igst_interest;
            $cash_cgst_interest += $payment->cgst_interest;
            $cash_sgst_interest += $payment->sgst_interest;
            $cash_cess_interest += $payment->cess_interest;

            $cash_igst_late_fees += $payment->igst_latefees;
            $cash_sgst_late_fees += $payment->sgst_latefees;
            $cash_cgst_late_fees += $payment->cgst_latefees;
            $cash_cess_late_fees += $payment->cess_latefees;

            $cash_igst_penalty += $payment->igst_penalty;
            $cash_cgst_penalty += $payment->cgst_penalty;
            $cash_sgst_penalty += $payment->sgst_penalty;
            $cash_cess_penalty += $payment->cess_penalty;

            $cash_igst_others += $payment->igst_others;
            $cash_cgst_others += $payment->cgst_others;
            $cash_sgst_others += $payment->sgst_others;
            $cash_cess_others += $payment->cess_others;

        }


        foreach ($make_payment_gst_setoff as $make_payment) {
            $cash_igst_tax += $make_payment->igst;
            $cash_cgst_tax += $make_payment->cgst;
            $cash_sgst_tax += $make_payment->sgst;
            $cash_cess_tax += $make_payment->cess;
            $cash_total_tax += $make_payment->total;
        }

        foreach ($make_payment_gst_setoff_liability_latefees as $make_payment) {

            $cash_igst_late_fees += $make_payment->igst;
            $cash_cgst_late_fees += $make_payment->cgst;
            $cash_sgst_late_fees += $make_payment->sgst;
            $cash_cess_late_fees += $make_payment->cess;
            $cash_total_late_fees += $make_payment->total;
        }

        
        foreach ($make_payment_gst_setoff_liability_interest as $make_payment) {
            $cash_igst_interest += $make_payment->igst;
            $cash_cgst_interest += $make_payment->cgst;
            $cash_sgst_interest += $make_payment->sgst;
            $cash_cess_interest += $make_payment->cess;
            $cash_total_interest += $make_payment->total;
        }


        foreach ($make_payment_gst_setoff_liability_penalty as $make_payment) {

            $cash_igst_penalty += $make_payment->igst;
            $cash_cgst_penalty += $make_payment->cgst;
            $cash_sgst_penalty += $make_payment->sgst;
            $cash_cess_penalty += $make_payment->cess;
            $cash_total_penalty += $make_payment->total;

        }
        
        foreach ($make_payment_gst_setoff_liability_others as $make_payment) {
            $cash_igst_others += $make_payment->igst;
            $cash_cgst_others += $make_payment->cgst;
            $cash_sgst_others += $make_payment->sgst;
            $cash_cess_others += $make_payment->cess;
            $cash_total_others += $make_payment->total;
        }
        

        foreach ($gst_set_off_otrc as $set_off) {
            $cash_igst_tax -= $set_off->otr_ptgcl_igst;
            $cash_cgst_tax -= $set_off->otr_ptgcl_cgst;
            $cash_sgst_tax -= $set_off->otr_ptgcl_sgst;
            $cash_cess_tax -= $set_off->otr_ptgcl_cess;
        }

        foreach ($gst_set_off_rc as $set_off) {
            $cash_igst_tax -= $set_off->reverse_charge_ptgcl_igst;
            $cash_cgst_tax -= $set_off->reverse_charge_ptgcl_cgst;
            $cash_sgst_tax -= $set_off->reverse_charge_ptgcl_sgst;
            $cash_cess_tax -= $set_off->reverse_charge_ptgcl_cess;
        }

        foreach ($gst_set_off_liability as $set_off) {
            $cash_igst_late_fees -= $set_off->liability_igst_latefees;
            $cash_cgst_late_fees -= $set_off->liability_cgst_latefees;
            $cash_sgst_late_fees -= $set_off->liability_sgst_latefees;
            $cash_cess_late_fees -= $set_off->liability_cess_latefees;

            $cash_igst_interest -= $set_off->liability_igst_interest;
            $cash_cgst_interest -= $set_off->liability_cgst_interest;
            $cash_sgst_interest -= $set_off->liability_sgst_interest;
            $cash_cess_interest -= $set_off->liability_cess_interest;

            $cash_igst_penalty -= $set_off->liability_igst_penalty;
            $cash_cgst_penalty -= $set_off->liability_cgst_penalty;
            $cash_sgst_penalty -= $set_off->liability_sgst_penalty;
            $cash_cess_penalty -= $set_off->liability_cess_penalty;

            $cash_igst_others -= $set_off->liability_igst_others;
            $cash_cgst_others -= $set_off->liability_cgst_others;
            $cash_sgst_others -= $set_off->liability_sgst_others;
            $cash_cess_others -= $set_off->liability_cess_others;
        }

        foreach ($cash_ledger_balances as $balance) {

            $balance_cash_igst_tax += $balance->igst_tax;
            $balance_cash_cgst_tax += $balance->cgst_tax;
            $balance_cash_sgst_tax += $balance->sgst_tax;
            $balance_cash_cess_tax += $balance->cess_tax;

            $balance_cash_igst_interest += $balance->igst_interest;
            $balance_cash_cgst_interest += $balance->cgst_interest;
            $balance_cash_sgst_interest += $balance->sgst_interest;
            $balance_cash_cess_interest += $balance->cess_interest;

            $balance_cash_igst_late_fees += $balance->igst_late_fees;
            $balance_cash_sgst_late_fees += $balance->sgst_late_fees;
            $balance_cash_cgst_late_fees += $balance->cgst_late_fees;
            $balance_cash_cess_late_fees += $balance->cess_late_fees;

            $balance_cash_igst_penalty += $balance->igst_penalty;
            $balance_cash_cgst_penalty += $balance->cgst_penalty;
            $balance_cash_sgst_penalty += $balance->sgst_penalty;
            $balance_cash_cess_penalty += $balance->cess_penalty;

            $balance_cash_igst_others += $balance->igst_others;
            $balance_cash_cgst_others += $balance->cgst_others;
            $balance_cash_sgst_others += $balance->sgst_others;
            $balance_cash_cess_others += $balance->cess_others;

            $cash_total_tax += ($cash_igst_tax + $cash_cgst_tax + $cash_sgst_tax + $cash_cess_tax + $balance_cash_igst_tax + $balance_cash_cgst_tax + $balance_cash_sgst_tax + $balance_cash_cess_tax);
            $cash_total_interest += ($balance_cash_igst_interest + $balance_cash_cgst_interest + $balance_cash_sgst_interest + $balance_cash_cess_interest + $cash_igst_interest + $cash_cgst_interest + $cash_sgst_interest + $cash_cess_interest);
            $cash_total_late_fees += ($balance_cash_igst_late_fees + $balance_cash_sgst_late_fees + $balance_cash_cgst_late_fees + $balance_cash_cess_late_fees + $cash_igst_late_fees + $cash_sgst_late_fees + $cash_cgst_late_fees + $cash_cess_late_fees);
            $cash_total_penalty += ($balance_cash_igst_penalty + $balance_cash_cgst_penalty + $balance_cash_sgst_penalty + $balance_cash_cess_penalty + $cash_igst_penalty + $cash_cgst_penalty + $cash_sgst_penalty + $cash_cess_penalty);
            $cash_total_others += ($balance_cash_igst_others + $balance_cash_cgst_others + $balance_cash_sgst_others + $balance_cash_cess_others + $cash_igst_others + $cash_cgst_others + $cash_sgst_others + $cash_cess_others);
        }

        //------------------------------------------------------------

        $fixed_credit_ledger_balance = GSTCreditLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->orderBy('id', 'desc')->first();

        $credit_ledger_balances = GSTCreditLedgerBalance::where('user_id', auth()->user()->id)->whereBetween('date', [$from_date, $to_date])->get();

        $purchases = User::find(auth()->user()->id)->purchases()->where(function ($query) {
            $query->where('gst_classification', '!=', 'rcm')->orWhereNull('gst_classification');
        })->whereBetween('bill_date', [$from_date, $to_date])->get();

        $credit_setoffs = GSTSetoffOtherThanReverseCharge::where('user_id', auth()->user()->id)->whereBetween('otr_date', [$from_date, $to_date])->get();


        /* only substract ineligible reversal of input from calculated-gst-ledger as it is already saved after substracting in ineligible table */ 
        $ineligible_reversal_of_inputs = IneligibleReversalOfInput::where('user_id', auth()->user()->id)->whereBetween('submit_date', [$from_date, $to_date])->get();

        // return $purchases;

        $purchase_igst = 0;
        $purchase_cgst = 0;
        $purchase_sgst = 0;
        $purchase_cess = 0;

        foreach($ineligible_reversal_of_inputs as $input){
            $purchase_igst -= $input->reverse_igst;
            $purchase_cgst -= $input->reverse_cgst;
            $purchase_sgst -= $input->reverse_sgst;
            $purchase_cess -= $input->reverse_cess;
        }

        foreach ($purchases as $thisBill) {

            $party = Party::findOrFail($thisBill->party_id);

            if ($party->balance_type == 'creditor' && $party->registered == 0) {
                continue;
            }

            $purchase_igst += $thisBill->igst;
            $purchase_cgst += $thisBill->cgst;
            $purchase_sgst += $thisBill->sgst;
            if ($thisBill->ugst != null || $thisBill->ugst != 0) {
                $purchase_cgst += $thisBill->ugst / 2;
                $purchase_sgst += $thisBill->ugst / 2;
            }

            $purchase_cess += $thisBill->cess;

            // $debitNotes = User::find(auth()->user()->id)->debitNotes()->where("bill_no", $thisBill->id)->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();
            // $creditNotes = User::find(auth()->user()->id)->creditNotes()->where("invoice_id", $thisBill->id)->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

            // foreach ($debitNotes as $note) {
            //     if($note->reason == 'purchase_return' || $note->reason == 'new_rate_or_discount_value_with_gst') {
            //         if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
            //             $purchase_cgst -= $note->gst / 2;
            //             $purchase_sgst -= $note->gst / 2;
            //         } else {
            //             $purchase_igst -= $request->gst;
            //         }
            //     }
            // }

            // foreach ($creditNotes as $note) {
            //     if($note->reason == 'new_rate_or_discount_value_with_gst'){
            //         if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
            //             $purchase_cgst += $note->gst / 2;
            //             $purchase_sgst += $note->gst / 2;
            //         }
            //         // else if (auth()->user()->place_of_business != $thisBill->party->business_place && ($thisBill->party->business_place == 4 || $thisBill->party->business_place == 7 || $thisBill->party->business_place == 25 || $thisBill->party->business_place == 26 || $thisBill->party->business_place == 31 || $thisBill->party->business_place == 34 || $thisBill->party->business_place == 35)) {
            //         //     $purchase_cgst += $note->gst_percent_difference / 2;
            //         //     $purchase_sgst += $note->gst_percent_difference / 2;
            //         //} 
            //         else {
            //             $purchase_igst += $request->gst;
            //         }
            //     }
            // }

        }

        //return $purchase_cgst;

        $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();
        $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

        foreach ($debitNotes as $note) {
            if($note->reason == 'purchase_return' || $note->reason == 'new_rate_or_discount_value_with_gst') {
                if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
                    $purchase_cgst -= $note->gst / 2;
                    $purchase_sgst -= $note->gst / 2;
                } else {
                    $purchase_igst -= $request->gst;
                }
            }
        }

        foreach ($creditNotes as $note) {
            if($note->reason == 'new_rate_or_discount_value_with_gst'){
                if (auth()->user()->profile->place_of_business == $thisBill->party->business_place) {
                    $purchase_cgst += $note->gst / 2;
                    $purchase_sgst += $note->gst / 2;
                }
                else {
                    $purchase_igst += $request->gst;
                }
            }
        }

        $credit_igst = $purchase_igst;
        $credit_cgst = $purchase_cgst;
        $credit_sgst = $purchase_sgst;
        $credit_cess = $purchase_cess;

        $balance_credit_igst = 0;
        $balance_credit_cgst = 0;
        $balance_credit_sgst = 0;
        $balance_credit_cess = 0;

        $credit_total = 0;

        foreach ($credit_ledger_balances as $balance) {

            $balance_credit_igst += $balance->igst;
            $balance_credit_cgst += $balance->cgst;
            $balance_credit_sgst += $balance->sgst;
            $balance_credit_cess += $balance->cess;
        }

        foreach($credit_setoffs as $setoff){
            $igst_setoff = $setoff->otr_input_igst_igst + $setoff->otr_input_cgst_igst + $setoff->otr_input_sgst_igst + $setoff->otr_input_cess_igst;

            $cgst_setoff = $setoff->otr_input_igst_cgst + $setoff->otr_input_cgst_cgst + $setoff->otr_input_sgst_cgst + $setoff->otr_input_cess_cgst;
            
            $sgst_setoff = $setoff->otr_input_igst_sgst + $setoff->otr_input_cgst_sgst + $setoff->otr_input_sgst_sgst + $setoff->otr_input_cess_sgst;
            
            $cess_setoff = $setoff->otr_input_igst_cess + $setoff->otr_input_cgst_cess + $setoff->otr_input_sgst_cess + $setoff->otr_input_cess_cess;

            $credit_igst -= $igst_setoff;
            $credit_cgst -= $cgst_setoff;
            $credit_sgst -= $sgst_setoff;
            $credit_cess -= $cess_setoff;
        }

        $credit_total = $credit_igst + $credit_cgst + $credit_sgst + $credit_cess + $balance_credit_igst + $balance_credit_cgst + $balance_credit_sgst + $balance_credit_cess;

        //-----------------------------------------------------------------------


        $liability = [

            'reverse_charge_igst' => $reverse_charge_igst,
            'reverse_charge_cgst' => $reverse_charge_cgst,
            'reverse_charge_sgst' => $reverse_charge_sgst,
            'reverse_charge_cess' => $reverse_charge_cess,

            'balance_tax_reverse_charge_igst' => $balance_tax_reverse_charge_igst,
            'balance_tax_reverse_charge_cgst' => $balance_tax_reverse_charge_cgst,
            'balance_tax_reverse_charge_sgst' => $balance_tax_reverse_charge_sgst,
            'balance_tax_reverse_charge_cess' => $balance_tax_reverse_charge_cess,

            'other_than_reverse_charge_igst' => $other_than_reverse_charge_igst,
            'other_than_reverse_charge_cgst' => $other_than_reverse_charge_cgst,
            'other_than_reverse_charge_sgst' => $other_than_reverse_charge_sgst,
            'other_than_reverse_charge_cess' => $other_than_reverse_charge_cess,

            'balance_other_than_reverse_charge_igst' => $balance_other_than_reverse_charge_igst,
            'balance_other_than_reverse_charge_cgst' => $balance_other_than_reverse_charge_cgst,
            'balance_other_than_reverse_charge_sgst' => $balance_other_than_reverse_charge_sgst,
            'balance_other_than_reverse_charge_cess' => $balance_other_than_reverse_charge_cess,

            'balance_igst_late_fees' => $balance_liability_igst_late_fees,
            'balance_cgst_late_fees' => $balance_liability_cgst_late_fees,
            'balance_sgst_late_fees' => $balance_liability_sgst_late_fees,
            'balance_cess_late_fees' => $balance_liability_cess_late_fees,
            
            'igst_late_fees' => $liability_igst_latefees,
            'cgst_late_fees' => $liability_cgst_latefees,
            'sgst_late_fees' => $liability_sgst_latefees,
            'cess_late_fees' => $liability_cess_latefees,

            'balance_igst_interest' => $balance_liability_igst_interest,
            'balance_cgst_interest' => $balance_liability_cgst_interest,
            'balance_sgst_interest' => $balance_liability_sgst_interest,
            'balance_cess_interest' => $balance_liability_cess_interest,

            'igst_interest' => $liability_igst_interest,
            'cgst_interest' => $liability_cgst_interest,
            'sgst_interest' => $liability_sgst_interest,
            'cess_interest' => $liability_cess_interest,

            'balance_igst_penalty' => $balance_liability_igst_penalty,
            'balance_cgst_penalty' => $balance_liability_cgst_penalty,
            'balance_sgst_penalty' => $balance_liability_sgst_penalty,
            'balance_cess_penalty' => $balance_liability_cess_penalty,

            'igst_penalty' => $liability_igst_penalty,
            'cgst_penalty' => $liability_cgst_penalty,
            'sgst_penalty' => $liability_sgst_penalty,
            'cess_penalty' => $liability_cess_penalty,

            'balance_igst_others' => $balance_liability_igst_others,
            'balance_cgst_others' => $balance_liability_cgst_others,
            'balance_sgst_others' => $balance_liability_sgst_others,
            'balance_cess_others' => $balance_liability_cess_others,

            'igst_others' => $liability_igst_others,
            'cgst_others' => $liability_cgst_others,
            'sgst_others' => $liability_sgst_others,
            'cess_others' => $liability_cess_others,
        ];

        $cash = [

            'balance_igst_tax' => $balance_cash_igst_tax,
            'balance_cgst_tax' => $balance_cash_cgst_tax,
            'balance_sgst_tax' => $balance_cash_sgst_tax,
            'balance_cess_tax' => $balance_cash_cess_tax,

            'igst_tax' => $cash_igst_tax,
            'cgst_tax' => $cash_cgst_tax,
            'sgst_tax' => $cash_sgst_tax,
            'cess_tax' => $cash_cess_tax,
            'total_tax' => $cash_total_tax,

            'balance_igst_interest' => $balance_cash_igst_interest,
            'balance_cgst_interest' => $balance_cash_cgst_interest,
            'balance_sgst_interest' => $balance_cash_sgst_interest,
            'balance_cess_interest' => $balance_cash_cess_interest,

            'igst_interest' => $cash_igst_interest,
            'cgst_interest' => $cash_cgst_interest,
            'sgst_interest' => $cash_sgst_interest,
            'cess_interest' => $cash_cess_interest,

            'total_interest' => $cash_total_interest,

            'balance_igst_late_fees' => $balance_cash_igst_late_fees,
            'balance_cgst_late_fees' => $balance_cash_sgst_late_fees,
            'balance_sgst_late_fees' => $balance_cash_cgst_late_fees,
            'balance_cess_late_fees' => $balance_cash_cess_late_fees,

            'igst_late_fees' => $cash_igst_late_fees,
            'cgst_late_fees' => $cash_cgst_late_fees,
            'sgst_late_fees' => $cash_sgst_late_fees,
            'cess_late_fees' => $cash_cess_late_fees,

            'total_late_fees' => $cash_total_late_fees,

            'balance_igst_penalty' => $balance_cash_igst_penalty,
            'balance_cgst_penalty' => $balance_cash_cgst_penalty,
            'balance_sgst_penalty' => $balance_cash_sgst_penalty,
            'balance_cess_penalty' => $balance_cash_cess_penalty,

            'igst_penalty' => $cash_igst_penalty,
            'cgst_penalty' => $cash_cgst_penalty,
            'sgst_penalty' => $cash_sgst_penalty,
            'cess_penalty' => $cash_cess_penalty,

            'total_penalty' => $cash_total_penalty,

            'balance_igst_others' => $balance_cash_igst_others,
            'balance_cgst_others' => $balance_cash_igst_others,
            'balance_sgst_others' => $balance_cash_sgst_others,
            'balance_cess_others' => $balance_cash_cess_others,

            'igst_others' => $cash_igst_others,
            'cgst_others' => $cash_cgst_others,
            'sgst_others' => $cash_sgst_others,
            'cess_others' => $cash_cess_others,

            'total_others' => $cash_total_others,
        ];

        $credit = [
            'balance_igst' => $balance_credit_igst,
            'balance_cgst' => $balance_credit_cgst,
            'balance_sgst' => $balance_credit_sgst,
            'balance_cess' => $balance_credit_cess,
            'igst' => $credit_igst,
            'cgst' => $credit_cgst,
            'sgst' => $credit_sgst,
            'cess' => $credit_cess,

            'total' => $credit_total
        ];

        // echo "<pre>";
        // print_r($liability);
        // die();

        $cash_gst_payment = GSTCashLedgerBalance::where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();
        $gst_payment = User::find(Auth::user()->id)->gstPayments()->orderBy('id', 'desc')->first();

        if($cash_gst_payment && $gst_payment){
            if (\Carbon\Carbon::parse($cash_gst_payment->created_at) > \Carbon\Carbon::parse($gst_payment->created_at)) {
                $last_gst_payment = $cash_gst_payment;
            } else {
                $last_gst_payment = $gst_payment;
            }
        } else {
            $last_gst_payment = $cash_gst_payment ? $cash_gst_payment : $gst_payment;
        }

        $voucher_no = null;
        $myerrors = array();

        if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->bill_no_type == 'auto') {

            if ($last_gst_payment && isset($last_gst_payment->voucher_no)) {
                $width = isset(auth()->user()->gstPaymentSetting) ? auth()->user()->gstPaymentSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['voucher_no'][] = 'Invalid Max-length provided. Please update your gst payment settings.';
                        break;
                    case 1:
                        if ($last_gst_payment->voucher_no > 9) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 2:
                        if ($last_gst_payment->voucher_no > 99) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 3:
                        if ($last_gst_payment->voucher_no > 999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 4:
                        if ($last_gst_payment->voucher_no > 9999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 5:
                        if ($last_gst_payment->voucher_no > 99999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 6:
                        if ($last_gst_payment->voucher_no > 999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 7:
                        if ($last_gst_payment->voucher_no > 9999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 8:
                        if ($last_gst_payment->voucher_no > 99999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                    case 9:
                        if ($last_gst_payment->voucher_no > 999999999) {
                            $myerrors['voucher_no'][] = 'Max-length exceeded for voucher no. Please update your gst payment settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->gstPaymentSetting) && auth()->user()->gstPaymentSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['voucher_no'][] = 'Applicable date expired for GST Payment. Please update your gst payment settings.';
            }

            if ($last_gst_payment) {
                if(isset($last_gst_payment->voucher_no_type) && $last_gst_payment->voucher_no_type == 'auto'){
                    if (\Carbon\Carbon::parse(auth()->user()->gstPaymentSetting->updated_at) > \Carbon\Carbon::parse($last_gst_payment->created_at)) {
                        $voucher_no = isset(auth()->user()->gstPaymentSetting->starting_no) ? auth()->user()->gstPaymentSetting->starting_no - 1 : 0;
                    } else {
                        $voucher_no = $last_gst_payment->voucher_no;
                    }
                } else {
                    $voucher_no = isset(auth()->user()->gstPaymentSetting->starting_no) ? auth()->user()->gstPaymentSetting->starting_no - 1 : 0;
                }

            } else {
                $voucher_no = isset(auth()->user()->gstPaymentSetting->starting_no) ? auth()->user()->gstPaymentSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('gst.show_gst_ledger', compact('liability', 'banks', 'cash', 'credit', 'fixed_liablility_balance', 'fixed_cash_ledger_balance', 'fixed_credit_ledger_balance', 'last_advance_cash_payment', 'voucher_no'))->with('myerrors',$myerrors);
    }

    public function add_cash_advance_payment(Request $request)
    {

        $advance_payment = new AdvanceCashPayment;

        $advance_payment->igst_tax = $request->igst_tax;
        $advance_payment->igst_interest = $request->igst_interest;
        $advance_payment->igst_latefees = $request->igst_late_fees;
        $advance_payment->igst_penalty = $request->igst_penalty;
        $advance_payment->igst_others = $request->igst_others;

        $advance_payment->cgst_tax = $request->cgst_tax;
        $advance_payment->cgst_interest = $request->cgst_interest;
        $advance_payment->cgst_latefees = $request->cgst_late_fees;
        $advance_payment->cgst_penalty = $request->cgst_penalty;
        $advance_payment->cgst_others = $request->cgst_others;

        $advance_payment->sgst_tax = $request->sgst_tax;
        $advance_payment->sgst_interest = $request->sgst_interest;
        $advance_payment->sgst_latefees = $request->sgst_late_fees;
        $advance_payment->sgst_penalty = $request->sgst_penalty;
        $advance_payment->sgst_others = $request->sgst_others;

        $advance_payment->cess_tax = $request->cess_tax;
        $advance_payment->cess_interest = $request->cess_interest;
        $advance_payment->cess_latefees = $request->cess_late_fees;
        $advance_payment->cess_penalty = $request->cess_penalty;
        $advance_payment->cess_others = $request->cess_others;

        $advance_payment->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));

        $advance_payment->save();

        return redirect()->back()->with('success', 'Advance Payment Successful');

    }

    public function add_credit_advance_payment(Request $request)
    {
        $advance_payment = new AdvanceCreditPayment;

        $advance_payment->igst = $request->igst;
        $advance_payment->cgst = $request->cgst;
        $advance_payment->sgst = $request->sgst;
        $advance_payment->cess = $request->cess;

        $advance_payment->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));

        $advance_payment->save();

        return redirect()->back()->with('success', 'Advance Payment Successful');
    }

    public function add_liability_advance_payment(Request $request)
    {
        $advance_payment = new AdvanceLiabilityPayment;

        $advance_payment->igst_under_reverse_charge = $request->igst_under_reverse_charge;
        $advance_payment->igst_other_than_reverse_charge = $request->igst_other_than_reverse_charge;
        $advance_payment->igst_latefees = $request->igst_latefees;
        $advance_payment->igst_interest = $request->igst_interest;
        $advance_payment->igst_penalty = $request->igst_penalty;
        $advance_payment->igst_others = $request->igst_others;

        $advance_payment->cgst_under_reverse_charge = $request->cgst_under_reverse_charge;
        $advance_payment->cgst_other_than_reverse_charge = $request->cgst_other_than_reverse_charge;
        $advance_payment->cgst_latefees = $request->cgst_latefees;
        $advance_payment->cgst_interest = $request->cgst_interest;
        $advance_payment->cgst_penalty = $request->cgst_penalty;
        $advance_payment->cgst_others = $request->cgst_others;

        $advance_payment->sgst_under_reverse_charge = $request->sgst_under_reverse_charge;
        $advance_payment->sgst_other_than_reverse_charge = $request->sgst_other_than_reverse_charge;
        $advance_payment->sgst_latefees = $request->sgst_latefees;
        $advance_payment->sgst_interest = $request->sgst_interest;
        $advance_payment->sgst_penalty = $request->sgst_penalty;
        $advance_payment->sgst_others = $request->sgst_others;

        $advance_payment->cess_under_reverse_charge = $request->cess_under_reverse_charge;
        $advance_payment->cess_other_than_reverse_charge = $request->cess_other_than_reverse_charge;
        $advance_payment->cess_latefees = $request->cess_latefees;
        $advance_payment->cess_interest = $request->cess_interest;
        $advance_payment->cess_penalty = $request->cess_penalty;
        $advance_payment->cess_others = $request->cess_others;

        $advance_payment->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));

        $advance_payment->save();

        return redirect()->back()->with('success', 'Advance Payment Successful');
    }

    public function edit_advance_payment($id)
    {
        $advance_cash_payment = GSTCashLedgerBalance::findOrFail($id);
        $banks = Bank::all();

        return view('gst.edit_advance_payment', compact('advance_cash_payment', 'banks'));
    }

    public function update_advance_payment(Request $request, $id)
    {
        $cash_ledger = GSTCashLedgerBalance::findOrFail($id);

        $cash_ledger->igst_tax = $request->igst_tax;
        $cash_ledger->cgst_tax = $request->cgst_tax;
        $cash_ledger->sgst_tax = $request->sgst_tax;
        $cash_ledger->cess_tax = $request->cess_tax;
        
        $cash_ledger->igst_interest = $request->igst_interest;
        $cash_ledger->cgst_interest = $request->cgst_interest;
        $cash_ledger->sgst_interest = $request->sgst_interest;
        $cash_ledger->cess_interest = $request->cess_interest;

        $cash_ledger->igst_late_fees = $request->igst_late_fees;
        $cash_ledger->cgst_late_fees = $request->cgst_late_fees;
        $cash_ledger->sgst_late_fees = $request->sgst_late_fees;
        $cash_ledger->cess_late_fees = $request->cess_late_fees;

        $cash_ledger->igst_penalty = $request->igst_penalty;
        $cash_ledger->cgst_penalty = $request->cgst_penalty;
        $cash_ledger->sgst_penalty = $request->sgst_penalty;
        $cash_ledger->cess_penalty = $request->cess_penalty;

        $cash_ledger->igst_others = $request->igst_others;
        $cash_ledger->cgst_others = $request->cgst_others;
        $cash_ledger->sgst_others = $request->sgst_others;
        $cash_ledger->cess_others = $request->cess_others;

        if ($request->has('type_of_payment')) {

            $type_of_payment = $request->type_of_payment;

            $cash = array_search('cash', $type_of_payment);
            $bank = array_search('bank', $type_of_payment);
            $pos = array_search('pos', $type_of_payment);

            if (!is_bool($cash)) {
                $cash += 1;
            }

            if (!is_bool($bank)) {
                $bank += 1;
            }

            if (!is_bool($pos)) {
                $pos += 1;
            }

            if ($cash && $bank && $pos) {
                $cash_ledger->type_of_payment = 'combined';

                $cash_ledger->cash_payment = $request->cashed_amount;
                $cash_ledger->bank_payment = $request->banked_amount;
                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;
                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($pos && $bank) {
                $cash_ledger->type_of_payment = 'pos+bank';

                $cash_ledger->bank_payment = $request->banked_amount;
                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;
                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($pos && $cash) {
                $cash_ledger->type_of_payment = 'pos+cash';

                $cash_ledger->cash_payment = $request->cashed_amount;
                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            } else if ($bank && $cash) {
                $cash_ledger->type_of_payment = 'bank+cash';

                $cash_ledger->cash_payment = $request->cashed_amount;
                $cash_ledger->bank_payment = $request->banked_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($bank) {
                $cash_ledger->type_of_payment = 'bank';

                $cash_ledger->bank_payment = $request->banked_amount;

                $cash_ledger->bank_id = $request->bank;
                $cash_ledger->bank_cheque = $request->bank_cheque;

                $cash_ledger->bank_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bank_payment_date)));
            } else if ($cash) {
                $cash_ledger->type_of_payment = 'cash';

                $cash_ledger->cash_payment = $request->cashed_amount;
            } else if ($pos) {
                $cash_ledger->type_of_payment = 'pos';

                $cash_ledger->pos_payment = $request->posed_amount;

                $cash_ledger->pos_bank_id = $request->pos_bank;

                $cash_ledger->pos_payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->pos_payment_date)));
            }
        } else {
            $cash_ledger->type_of_payment = 'no_payment';
        }

        $cash_ledger->voucher_no = $request->voucher_no;

        if( isset(auth()->user()->gstPaymentSetting) && isset(auth()->user()->gstPaymentSetting->bill_no_type) ){
            $cash_ledger->voucher_no_type = auth()->user()->gstPaymentSetting->bill_no_type;
        } else {
            $cash_ledger->voucher_no_type = 'manual';
        }

        $cash_ledger->cin = $request->cin;
        $cash_ledger->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $cash_ledger->user_id = auth()->user()->id;

        if ($cash_ledger->save()) {
            return redirect()->back()->with('success', 'Data updated successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function validate_gst_payment_voucher_no(Request $request)
    {
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('cash_ledger_balance')
            ->where('voucher_no', $request->token)
            ->get();

        foreach($rows as $row) {
            if(  $user->profile->financial_year_from <= $row->created_at && $user->profile->financial_year_to >= $row->created_at ) {
                $isValidated = false;
                break;
            }
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide unique voucher no for the current financial year'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }
}
