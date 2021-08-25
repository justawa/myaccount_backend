<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use Carbon\Carbon;

use App\Bank;
use App\CashInHand;
use App\CashDeposit;
use App\CashWithdraw;
use App\SaleOrder;
use App\PurchaseOrder;
use App\CashGST;
use App\GSTCashLedgerBalance;
use App\User;
use App\Party;
use App\GSTSetOff;

class CashController extends Controller
{
    public function cash_in_hand() {

        $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->first();

        return view('cash.cash_in_hand', compact('cash_in_hand'));
    }

    public function post_cash_in_hand(Request $request) {

        $this->validate($request, [
            'opening_balance' => 'numeric',
            'balance_date' => 'date_format:d/m/Y',
            'narration' => 'string|nullable'
        ]);

        $financialYearFrom = auth()->user()->profile->financial_year_from;
        $financialYearTo = auth()->user()->profile->financial_year_to;

        $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->whereBetween('balance_date', [ $financialYearFrom, $financialYearTo ])->first();

        if( $cash_in_hand == null ){
            $cash_in_hand = new CashInHand;
        }

        $cash_in_hand->opening_balance = $request->opening_balance;
        $cash_in_hand->balance_date = date('Y-m-d', strtotime( str_replace('/', '-', $request->balance_date) ));
        $cash_in_hand->balance_type = $request->balance_type;
        $cash_in_hand->narration = $request->narration;
        $cash_in_hand->user_id = Auth::user()->id;

        if( auth()->user()->profile->financial_year_from > $cash_in_hand->balance_date ){
            return redirect()->back()->with('failure', 'Please select valid opening balance date for current financial year');
        }

        if( auth()->user()->profile->financial_year_to < $cash_in_hand->balance_date ){
            return redirect()->back()->with('failure', 'Please select valid opening balance date for current financial year');
        }

        if( $cash_in_hand->save() ){
            return redirect()->back()->with('success', 'Data inserted/updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to insert/update data');
        }
    }


    public function cash_deposit () {

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        $last_withdraw = User::find(Auth::user()->id)->cashWithdraw()->orderBy('id', 'desc')->first();

        $last_deposit = User::find(Auth::user()->id)->cashDeposit()->orderBy('id', 'desc')->first();

        $last_bank_to_bank_transfer = User::find(Auth::user()->id)->bankToBankTransfers()->orderBy('id', 'desc')->first();

        if($last_withdraw && $last_deposit && $last_bank_to_bank_transfer){
            if (\Carbon\Carbon::parse($last_withdraw->created_at) > \Carbon\Carbon::parse($last_deposit->created_at)) {
                if (\Carbon\Carbon::parse($last_withdraw->created_at) > \Carbon\Carbon::parse($last_bank_to_bank_transfer->created_at)) {
                    $last_transaction = $last_withdraw;
                } else {
                    $last_transaction = $last_bank_to_bank_transfer;
                }
            } else {
                if (\Carbon\Carbon::parse($last_deposit->created_at) > \Carbon\Carbon::parse($last_bank_to_bank_transfer->created_at)) {
                    $last_transaction = $last_deposit;
                } else {
                    $last_transaction = $last_bank_to_bank_transfer;
                }
            } 
        } else {
            $last_transaction = null;
        }

        $contra = null;
        $myerrors = array();

        if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->bill_no_type == 'auto') {
            if (isset($last_transaction->contra)) {
                $width = isset(auth()->user()->cashSetting) ? auth()->user()->cashSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['contra'][] = 'Invalid Max-length provided. Please update your cash settings.';
                        break;
                    case 1:
                        if ($last_transaction->contra > 9) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 2:
                        if ($last_transaction->contra > 99) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 3:
                        if ($last_transaction->contra > 999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 4:
                        if ($last_transaction->contra > 9999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 5:
                        if ($last_transaction->contra > 99999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 6:
                        if ($last_transaction->contra > 999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 7:
                        if ($last_transaction->contra > 9999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 8:
                        if ($last_transaction->contra > 99999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 9:
                        if ($last_transaction->contra > 999999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->cashSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->cashSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->cashSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['contra'][] = 'Applicable date expired for contra. Please update your cash settings.';
            }

            if ($last_transaction) {

                if( isset($last_transaction->voucher_no_type) && $last_transaction->voucher_no_type == 'auto' ){
                    if (\Carbon\Carbon::parse(auth()->user()->cashSetting->updated_at) > \Carbon\Carbon::parse($last_transaction->created_at)) {
                        $contra = isset(auth()->user()->cashSetting->starting_no) ? auth()->user()->cashSetting->starting_no - 1 : 0;
                    } else {
                        $contra = ($last_transaction->contra == '' || $last_transaction->contra == null) ? 0 : $last_transaction->contra;
                    }
                } else {
                    $contra = isset(auth()->user()->cashSetting->starting_no) ? auth()->user()->cashSetting->starting_no - 1 : 0;
                }

            } else {
                $contra = isset(auth()->user()->cashSetting->starting_no) ? auth()->user()->cashSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('cash.cash_deposit', compact('banks', 'contra'))->with('myerrors', $myerrors);
    }

    public function post_cash_deposit (Request $request) {

        $this->validate($request, [
            'amount' => 'required|numeric',
            'date' => 'required|date_format:d/m/Y',
            'contra' => 'required|string',
            'narration' => 'string|nullable'
        ]);

        $cash_deposit = new CashDeposit;

        $cash_deposit->amount = $request->amount;
        $cash_deposit->date = date('Y-m-d', strtotime( str_replace('/', '-', $request->date) ));
        $cash_deposit->bank = $request->bank;
        // $cash_deposit->bank_cheque = $request->bank_cheque;
        // $cash_deposit->bank_amount = $request->bank_amount;
        // $cash_deposit->cash_amount = $request->cash_amount;
        $cash_deposit->contra = $request->contra;

        if( isset(auth()->user()->cashSetting) && isset(auth()->user()->cashSetting->bill_no_type) ){
            $cash_deposit->voucher_no_type = auth()->user()->cashSetting->bill_no_type;
        } else {
            $cash_deposit->voucher_no_type = 'manual';
        }

        $cash_deposit->narration = $request->narration;
        $cash_deposit->user_id = Auth::user()->id;

        if (auth()->user()->profile->financial_year_from > $cash_deposit->date) {
            return redirect()->back()->with('failure', 'Please select valid date for current financial year');
        }

        if (auth()->user()->profile->financial_year_to < $cash_deposit->date) {
            return redirect()->back()->with('failure', 'Please select valid date for current financial year');
        }

        if( $cash_deposit->save() ){
            return redirect()->back()->with('success', 'Data save successfully');
        } else {
            return redirect()->back()->with('success', 'Failed to save data');
        }

    }

    public function cash_withdraw () {

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        $last_withdraw = User::find(Auth::user()->id)->cashWithdraw()->orderBy('id', 'desc')->first();

        $last_deposit = User::find(Auth::user()->id)->cashDeposit()->orderBy('id', 'desc')->first();

        $last_bank_to_bank_transfer = User::find(Auth::user()->id)->bankToBankTransfers()->orderBy('id', 'desc')->first();

        if($last_withdraw && $last_deposit && $last_bank_to_bank_transfer){
            if (\Carbon\Carbon::parse($last_withdraw->created_at) > \Carbon\Carbon::parse($last_deposit->created_at)) {
                if (\Carbon\Carbon::parse($last_withdraw->created_at) > \Carbon\Carbon::parse($last_bank_to_bank_transfer->created_at)) {
                    $last_transaction = $last_withdraw;
                } else {
                    $last_transaction = $last_bank_to_bank_transfer;
                }
            } else {
                if (\Carbon\Carbon::parse($last_deposit->created_at) > \Carbon\Carbon::parse($last_bank_to_bank_transfer->created_at)) {
                    $last_transaction = $last_deposit;
                } else {
                    $last_transaction = $last_bank_to_bank_transfer;
                }
            } 
        } else {
            $last_transaction = null;
        }
        

        $contra = null;
        $myerrors = array();

        if( isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->bill_no_type == 'auto' ) {
            if ($last_transaction && isset($last_transaction->contra)) {
                $width = isset(auth()->user()->cashSetting) ? auth()->user()->cashSetting->width_of_numerical : 0;
                switch ($width) {
                    case 0:
                        $myerrors['contra'][] = 'Invalid Max-length provided. Please update your cash settings.';
                        break;
                    case 1:
                        if ($last_transaction->contra > 9) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 2:
                        if ($last_transaction->contra > 99) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 3:
                        if ($last_transaction->contra > 999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 4:
                        if ($last_transaction->contra > 9999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 5:
                        if ($last_transaction->contra > 99999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 6:
                        if ($last_transaction->contra > 999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 7:
                        if ($last_transaction->contra > 9999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 8:
                        if ($last_transaction->contra > 99999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                    case 9:
                        if ($last_transaction->contra > 999999999) {
                            $myerrors['contra'][] = 'Max-length exceeded for contra. Please update your cash settings.';
                        }
                        break;
                }
            }

            $date_ahead = \Carbon\Carbon::now();

            if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->period == 'week') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->cashSetting->start_no_applicable_date)->addWeek();
            }

            if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->period == 'month') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->cashSetting->start_no_applicable_date)->addMonth();
            }

            if (isset(auth()->user()->cashSetting) && auth()->user()->cashSetting->period == 'year') {
                $date_ahead = \Carbon\Carbon::parse(auth()->user()->cashSetting->start_no_applicable_date)->addYear();
            }

            if (\Carbon\Carbon::now() > $date_ahead) {
                $myerrors['contra'][] = 'Applicable date expired for contra. Please update your cash settings.';
            }

            if ($last_transaction) {

                if( isset($last_transaction->voucher_no_type) && $last_transaction->voucher_no_type == 'auto' ){
                    if (\Carbon\Carbon::parse(auth()->user()->cashSetting->updated_at) > \Carbon\Carbon::parse($last_transaction->created_at)) {
                        $contra = isset(auth()->user()->cashSetting->starting_no) ? auth()->user()->cashSetting->starting_no - 1 : 0;
                    } else {
                        $contra = ($last_transaction->contra == '' || $last_transaction->contra == null) ? 0 : $last_transaction->contra;
                    }
                } else {
                    $contra = isset(auth()->user()->cashSetting->starting_no) ? auth()->user()->cashSetting->starting_no - 1 : 0;
                }

            } else {
                $contra = isset(auth()->user()->cashSetting->starting_no) ? auth()->user()->cashSetting->starting_no - 1 : 0;
            }
        }

        $myerrors = collect($myerrors);

        return view('cash.cash_withdraw', compact('banks', 'contra'))->with('myerrors', $myerrors);
    }

    public function post_cash_withdraw (Request $request) {

        $this->validate($request, [
            'amount' => 'required|numeric',
            'date' => 'required|date_format:d/m/Y',
            'contra' => 'required|string',
            'narration' => 'string|nullable'
        ]);

        $cash_withdraw = new CashWithdraw;

        $cash_withdraw->amount = $request->amount;
        $cash_withdraw->date = date('Y-m-d', strtotime( str_replace('/', '-', $request->date) ));
        $cash_withdraw->bank = $request->bank;
        // $cash_withdraw->bank_cheque = $request->bank_cheque;
        // $cash_deposit->bank_amount = $request->bank_amount;
        // $cash_withdraw->cash_amount = $request->cash_amount;
        $cash_withdraw->contra = $request->contra;

        if( isset(auth()->user()->cashSetting) && isset(auth()->user()->cashSetting->bill_no_type) ){
            $cash_withdraw->voucher_no_type = auth()->user()->cashSetting->bill_no_type;
        } else {
            $cash_withdraw->voucher_no_type = 'manual';
        }

        $cash_withdraw->narration = $request->narration;
        $cash_withdraw->user_id = Auth::user()->id;

        if (auth()->user()->profile->financial_year_from > $cash_withdraw->date) {
            return redirect()->back()->with('failure', 'Please select valid date from current financial year');
        }

        if (auth()->user()->profile->financial_year_to < $cash_withdraw->date) {
            return redirect()->back()->with('failure', 'Please select valid date from current financial year');
        }

        if( $cash_withdraw->save() ){
            return redirect()->back()->with('success', 'Data save successfully');
        } else {
            return redirect()->back()->with('success', 'Failed to save data');
        }

    }

    // public function generate_cash_book (Request $request) {

    //     if( $request->has('from_date') && $request->has('to_date') ){
    //         $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
    //         $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
    //     } else {
    //         $from_date = auth()->user()->profile->financial_year_from;
    //         // $to_date = Carbon::parse( auth()->user()->profile->financial_year_from )->format('Y').'-'.Carbon::now()->format('m').'-'.Carbon::now()->format('d');

    //         $to_date = auth()->user()->profile->financial_year_to;
    //     }

    //     if ($from_date < auth()->user()->profile->book_beginning_from) {
    //         return redirect()->back()->with('failure', 'Please select dates on or after the book beginning date');
    //     }

    //     if( auth()->user()->profile->financial_year_from < $from_date && auth()->user()->profile->financial_year_to >= $from_date ){
    //         $opening_balance_from_date = auth()->user()->profile->financial_year_from;
    //         $opening_balance_to_date = $from_date;
    //     } else {
    //         $month = Carbon::parse($from_date)->format('m');
    //         // $date = Carbon::parse($from_date)->format('d');

    //         // echo $month;

    //         // die();
            
    //         // $isFirstDateOfFinancialYear = false;

    //         if( $month == "01" || $month == "02" || $month == "03" ){
    //             $year = Carbon::parse( $from_date )->format('Y') - 1;
    //         }
    //         // else if($month == "04" && $date == "01") {
    //         //     $year = Carbon::parse( $from_date )->format('Y') - 1;
    //         //     // $from_date = \Carbon\Carbon::parse($from_date)->subDay();
    //         //     $isFirstDateOfFinancialYear = true;  
    //         // }
    //         else {
    //             $year = Carbon::parse( $from_date )->format('Y');
    //         }

    //         // echo $from_date . "<br/>";
    //         // echo $year;
    //         // die();

    //         $opening_balance_from_date = $year. "-04" ."-01";
    //         // $opening_balance_to_date = $isFirstDateOfFinancialYear ? \Carbon\Carbon::parse($from_date)->subDay() : $from_date;
    //         $opening_balance_to_date = $from_date;
    //     }
        
    //         // echo $opening_balance_from_date;
    //         // echo $opening_balance_to_date;
            
    //         // die;
        
    //         $static_opening_balance_from = auth()->user()->profile->financial_year_from;
    //         $static_opening_balance_to = $to_date;
            
    //         // echo $static_opening_balance_to;
    //         // echo $static_opening_balance_from;

    //         // die;

    //         $closing_balance_till_date = $opening_balance_from_date;

    //         $opening_balance = $this->fetch_static_balance($static_opening_balance_to, $static_opening_balance_from);
    //         $opening_balance += $this->calculate_opening_balance( $opening_balance_from_date, $opening_balance_to_date );
    //         // $opening_balance = null;
    //         $closing_balance = 0;

    //         if( $opening_balance == null ){

    //             // if(\Carbon\Carbon::parse(auth()->user()->profile->book_beginning_from) < \Carbon\Carbon::parse($opening_balance_to_date) ){
    //             //     $opening_balance = $this->calculate_opening_balance( $opening_balance_from_date, $opening_balance_to_date );
    //             // } else {
    //             //     $opening_balance = 0;
    //             // }
                
    //             // echo $opening_balance;
                
    //             // die;
    
    //             $opening_balance = $this->calculate_opening_balance( $opening_balance_from_date, $opening_balance_to_date );
    //             $closing_balance = $this->calculate_closing_balance( $closing_balance_till_date );
                
    //             $closing_balance += $this->fetch_static_balance($closing_balance_till_date);
                

    //             if($opening_balance == 0 && $closing_balance == 0){
    //                 $revisedYearForClosingBalance = Carbon::parse($closing_balance_till_date)->format('Y') - 1;
    //                 $revisedMonthForClosingBalance = Carbon::parse($closing_balance_till_date)->format('m');
    //                 $revisedDateForClosingBalance = Carbon::parse($closing_balance_till_date)->format('d');

    //                 $revisedClosingBalanceDate = $revisedYearForClosingBalance . '-' . $revisedMonthForClosingBalance . '-' . $revisedDateForClosingBalance;

    //                 $closing_balance = $this->calculate_closing_balance($revisedClosingBalanceDate);

    //                 $closing_balance += $this->fetch_static_balance($revisedClosingBalanceDate);

    //                 $cash_in_hand = new CashInHand;

    //                 $cash_in_hand->opening_balance = $closing_balance;
    //                 $cash_in_hand->balance_date = $revisedClosingBalanceDate;
    //                 if($closing_balance < 0){
    //                     $cash_in_hand->balance_type = 'creditor';
    //                 } else {
    //                     $cash_in_hand->balance_type = 'debitor';
    //                 }
    //                 $cash_in_hand->narration = 'Carry Forward of Previous Year. Must be updated manually';
    //                 $cash_in_hand->user_id = Auth::user()->id;

    //                 $cash_in_hand->save();
    //             }
    //         }

    //         $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //         // return $sales;

    //         $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //         $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //         $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //         $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->whereBetween('date',[ $from_date, $to_date])->get();

    //         $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->whereBetween('date',[ $from_date, $to_date])->get();

    //         $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

    //         $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

    //         $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

    //         $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //         $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //         $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

    //         $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();


    //     // } else {
           
    //     //     $opening_balance_date = date('Y-m-d', time());

    //     //     $till_date = $opening_balance_date;

    //     //     // return $till_date;

    //     //     $opening_balance = $this->calculate_opening_balance($till_date);

    //     //     $sales = User::findOrFail(Auth::user()->id)->invoices()->where('invoice_date', '<=', $till_date)->get();

    //     //     $purchases = User::find(Auth::user()->id)->purchases()->where('bill_date', '<=', $till_date)->get();

    //     //     $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where( 'purchase_remaining_amounts.payment_date', '<=', $till_date)->get();

    //     //     $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where( 'sale_remaining_amounts.payment_date', '<=', $till_date)->get();

    //     //     $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->get();

    //     //     $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->get();

    //     //     $gst_payments = CashGST::where('user_id', Auth::user()->id)->where('created_at', '<=', $till_date)->get();

    //     //     $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->get();

    //     //     $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->get();

    //     //     $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.payment_date', '<=', $till_date)->get();

    //     //     $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'purchase')->where( 'party_pending_payment_account.payment_date', '<=', $till_date)->get();
    //     // }

    //     $combined_array = array();
        
    //     $sale_array = array();
    //     $purchase_array = array();
    //     $sale_order_array = array();
    //     $purchase_order_array = array();
    //     $gst_payment_array = array();
    //     $receipt_array = array();
    //     $payment_array = array();
    //     $sale_party_payment_array = array();
    //     $purchase_party_payment_array = array();
    //     $cash_withdrawn_array = array();
    //     $cash_deposited_array = array();
    //     $setoff_make_payment_array = array();
    //     $advance_payment_cash = array();

    //     if ( isset( $sales) && !empty( $sales) ) {
            
    //         // return $sales;

    //         foreach( $sales as $sale ){
    //             if($sale->cash_payment != null && $sale->cash_payment > 0) {
    //                 $sale_array[] = [
    //                     'routable' => $sale->id,
    //                     'particulars' => $sale->party->name,
    //                     'voucher_type' => 'Sale',
    //                     'voucher_no' => $sale->invoice_no,
    //                     'amount' => $sale->cash_payment,
    //                     'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($sale->invoice_date)->format('m'),
    //                     'transaction_type' => 'debit',
    //                     'loop' => 'sale',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if ( isset( $purchases) && !empty( $purchases) ) {

    //         foreach( $purchases as $purchase ){
    //             if($purchase->cash_payment != null && $purchase->cash_payment > 0) {
    //                 $purchase_array[] = [
    //                     'routable' => $purchase->id,
    //                     'particulars' => $purchase->party->name,
    //                     'voucher_type' => 'Purchase',
    //                     'voucher_no' => $purchase->bill_no,
    //                     'amount' => $purchase->cash_payment,
    //                     'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($purchase->bill_date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'purchase',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if ( isset( $sale_orders) && !empty( $sale_orders) ) {

    //         foreach( $sale_orders as $order ){

    //             $party = Party::find($order->party_id);
    
    //             $order->party_name = $party->name;

    //             if($order->cash_amount != null && $order->cash_amount > 0) {
    //                 $sale_order_array[] = [
    //                     'routable' => $order->token,
    //                     'particulars' => $order->party_name,
    //                     'voucher_type' => 'Sale Order',
    //                     'voucher_no' => $order->token,
    //                     'amount' => $order->cash_amount,
    //                     'date' => Carbon::parse($order->date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($order->date)->format('m'),
    //                     'transaction_type' => 'debit',
    //                     'loop' => 'sale_order',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if ( isset( $purchase_orders) && !empty( $purchase_orders) ) {

    //         foreach( $purchase_orders as $order ) {

    //             $party = Party::find($order->party_id);
    
    //             $order->party_name = $party->name;

    //             if($order->cash_amount != null && $order->cash_amount > 0) {
    //                 $purchase_order_array[] = [
    //                     'routable' => $order->token,
    //                     'particulars' => $order->party_name,
    //                     'voucher_type' => 'Purchase Order',
    //                     'voucher_no' => $order->token,
    //                     'amount' => $order->cash_amount,
    //                     'date' => Carbon::parse($order->date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($order->date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'purchase_order',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if ( isset( $gst_payments) && !empty( $gst_payments) ) {

    //         foreach( $gst_payments as $payment ){

    //             if($payment->cash_amount != null && $payment->cash_amount > 0) {
    //                 $gst_payment_array[] = [
    //                     'routable' => null,
    //                     'particulars' => 'GST Cash',
    //                     'voucher_type' => 'GST',
    //                     'voucher_no' => null,
    //                     'amount' => $payment->cash_amount,
    //                     'date' => Carbon::parse($payment->created_at)->format('Y-m-d'),
    //                     'month' => Carbon::parse($payment->created_at)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'gst_payment',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($receipts) && !empty($receipts) ){
    //         foreach ($receipts as $receipt) {
    //             // $opening_balance += $receipt->cash_payment;
    
    //             $party = Party::find($receipt->party_id);
    
    //             $receipt->party_name = $party->name;

    //             if($receipt->cash_payment != null && $receipt->cash_payment > 0) {
    //                 $receipt_array[] = [
    //                     'routable' => $receipt->id,
    //                     'particulars' => $receipt->party_name,
    //                     'voucher_type' => 'Receipt',
    //                     'voucher_no' => $receipt->voucher_no,
    //                     'amount' => $receipt->cash_payment,
    //                     'date' => Carbon::parse($receipt->created_at)->format('Y-m-d'),
    //                     'month' => Carbon::parse($receipt->created_at)->format('m'),
    //                     'transaction_type' => 'debit',
    //                     'loop' => 'receipt',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($payments) && !empty($payments) ){
    //         foreach ($payments as $payment) {
    //             // $opening_balance -= $payment->cash_payment;
    
    //             $party = Party::find($payment->party_id);
    
    //             $payment->party_name = $party->name;

    //             if($payment->cash_payment != null && $payment->cash_payment > 0) {
    //                 $payment_array[] = [
    //                     'routable' => $payment->id,
    //                     'particulars' => $payment->party_name,
    //                     'voucher_type' => 'Payment',
    //                     'voucher_no' => $payment->voucher_no,
    //                     'amount' => $payment->cash_payment,
    //                     'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'payment',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($sale_party_payments) && !empty($sale_party_payments) ){
    //         foreach ($sale_party_payments as $payment) {
    //             $party = Party::find($payment->party_id);
                
    //             $payment->party_name = $party->name;

    //             if($payment->cash_payment != null && $payment->cash_payment > 0) {
    //                 $sale_party_payment_array[] = [
    //                     'routable' => $payment->id,
    //                     'particulars' => $payment->party_name,
    //                     'voucher_type' => 'Sale Party Receipt',
    //                     'voucher_no' => $payment->voucher_no,
    //                     'amount' => $payment->cash_payment,
    //                     'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                     'transaction_type' => 'debit',
    //                     'loop' => 'sale_party_payment',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($purchase_party_payments) && !empty($purchase_party_payments) ){
    //         foreach ($purchase_party_payments as $payment) {
    //             $party = Party::find($payment->party_id);
    
    //             $payment->party_name = $party->name;
    //             $payment->transaction_type = 'credit';

    //             if($payment->cash_payment != null && $payment->cash_payment > 0) {
    //                 $purchase_party_payment_array[] = [
    //                     'routable' => $payment->id,
    //                     'particulars' => $payment->party_name,
    //                     'voucher_type' => 'Purchase Party Payment',
    //                     'voucher_no' => $payment->voucher_no,
    //                     'amount' => $payment->cash_payment,
    //                     'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'purchase_party_payment',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($cash_withdrawn) && !empty($cash_withdrawn) ){
    //         foreach ( $cash_withdrawn as $cash) {
    //             $bank = Bank::find($cash->bank);
    
    //             $cash->bank_name = $bank->name;
    //             $cash->transaction_type = 'debit';

    //             if($cash->amount != null && $cash->amount > 0) {
    //                 $cash_withdrawn_array[] = [
    //                     'routable' => $cash->id,
    //                     'particulars' => $cash->bank_name,
    //                     'voucher_type' => 'Contra',
    //                     'voucher_no' => $cash->contra,
    //                     'amount' => $cash->amount,
    //                     'date' => Carbon::parse($cash->date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($cash->date)->format('m'),
    //                     'transaction_type' => 'debit',
    //                     'loop' => 'cash_withdraw',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($cash_deposited) && !empty($cash_deposited) ){
    //         foreach ( $cash_deposited as $cash) {
    //             $bank = Bank::find($cash->bank);
    
    //             if($bank) {
    //                 $cash->bank_name = $bank->name;
    //             } else{
    //                 $cash->bank_name = null;
    //             }
    //             $cash->transaction_type = 'credit';

    //             if($cash->amount != null && $cash->amount > 0) {
    //                 $cash_deposited_array[] = [
    //                     'routable' => $cash->id,
    //                     'particulars' => $cash->bank_name,
    //                     'voucher_type' => 'Contra',
    //                     'voucher_no' => $cash->contra,
    //                     'amount' => $cash->amount,
    //                     'date' => Carbon::parse($cash->date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($cash->date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'cash_deposit',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if( isset($setoff_make_payments) && !empty($setoff_make_payments) ){
    //         foreach($setoff_make_payments as $payment ){

    //             if($payment->cash_payment != null && $payment->cash_payment > 0) {
    //                 $setoff_make_payment_array[] = [
    //                     'routable' => '',
    //                     'particulars' => 'GST Setoff Make Payment',
    //                     'voucher_type' => 'Setoff Payment',
    //                     'voucher_no' => $payment->id,
    //                     'amount' => $payment->cash_payment,
    //                     'date' => Carbon::parse($payment->date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($payment->date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'setoff_payment',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }

    //     if (isset($advance_cash_ledger_cash_payments) && !empty($advance_cash_ledger_cash_payments)) {
    //         foreach ($advance_cash_ledger_cash_payments as $payment) {

    //             if($payment->cash_payment != null && $payment->cash_payment > 0) {
    //                 $advance_payment_cash[] = [
    //                     'routable' => $payment->id,
    //                     'particulars' => 'Advance Payment',
    //                     'voucher_type' => 'Payment',
    //                     'voucher_no' => $payment->voucher_no ?? $payment->id,
    //                     'amount' => $payment->cash_payment,
    //                     'date' => Carbon::parse($payment->date)->format('Y-m-d'),
    //                     'month' => Carbon::parse($payment->date)->format('m'),
    //                     'transaction_type' => 'credit',
    //                     'loop' => 'advanced_payment',
    //                     'type' => 'showable'
    //                 ];
    //             }
    //         }
    //     }


    //     // $sales_array = (array) $sales;
    //     // $purchases_array = (array) $purchases;
    //     // $sale_orders_array = (array) $sale_orders;
    //     // $purchase_orders_array = (array) $purchase_orders;
    //     // $gst_payments_array = (array) $gst_payments;
    //     // $receipts_array = (array) $receipts;
    //     // $payments_array = (array) $payments;
    //     // $sale_party_payments_array = (array) $sale_party_payments;
    //     // $purchase_party_payments_array = (array) $purchase_party_payments;
    //     // $cash_withdrawn_array = (array) $cash_withdrawn;
    //     // $cash_deposited_array = (array) $cash_deposited;

    //     // $combined_array[] = $sales;
    //     // $combined_array[] = $purchases;
    //     // $combined_array[] = $sale_orders;
    //     // $combined_array[] = $purchase_orders;
    //     // $combined_array[] = $gst_payments;
    //     // $combined_array[] = $receipts;
    //     // $combined_array[] = $payments;
    //     // $combined_array[] = $sale_party_payments;
    //     // $combined_array[] = $purchase_party_payments;
    //     // $combined_array[] = $cash_withdrawn;
    //     // $combined_array[] = $cash_deposited;

    //     $combined_array = array_merge(
    //         $sale_array,
    //         $purchase_array,
    //         $sale_order_array,
    //         $purchase_order_array,
    //         $gst_payment_array,
    //         $receipt_array,
    //         $payment_array,
    //         $sale_party_payment_array,
    //         $purchase_party_payment_array,
    //         $cash_withdrawn_array,
    //         $cash_deposited_array,
    //         $setoff_make_payment_array,
    //         $advance_payment_cash
    //     );

    //     // return $combined_array;

    //     // $combined_array = collect($combined_array)->sortBy('date')->all();

    //     $this->array_sort_by_column($combined_array, 'date');

    //     // return $combined_array;

    //     $group = [];
    //     foreach ($combined_array as $item) {
    //         $count = 0;
    //         $month = Carbon::parse($item['date'])->format('F');

    //         if ( isset( $group[ $month ] ) ) {
    //             foreach( $group[ $month ] as $key => $value ){
    //                 $count = $key;
    //             }
    //         }
    //         $count++;
    //         // echo "<pre>";
    //         // print_r($item);
    //         // print_r( $group[$item['month']][$count] );
    //         foreach ($item as $key => $value) {
    //             // if ($key == 'month') continue;
    //             $group[ $month ][$count][$key] = $value;
    //         }
    //     }

    //     foreach( $group as $key => $value ){
    //         $creditTotal = 0;
    //         $debitTotal = 0;
    //         foreach( $value as $data ) {
    //             if( $data['transaction_type'] == 'credit' ){
    //                 $creditTotal += $data['amount'];
    //             } elseif( $data['transaction_type'] == 'debit' ){
    //                 $debitTotal += $data['amount'];
    //             }
    //         }
    //         $group[$key]['credit_total'] = $creditTotal;
    //         $group[$key]['debit_total'] = $debitTotal;
    //         $group[$key]['closing_total'] = $debitTotal - $creditTotal;
    //     }

    //     // print_r($group);
        
    //     $combined_array = $group;

    //     // echo "<pre>";
    //     // print_r($combined_array);
    //     // die();

    //     $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->whereBetween('balance_date', [ $from_date, $to_date])->first();

    //     // return view('cash.cash_book', compact( 'opening_balance', 'opening_balance_date', 'sales', 'purchases', 'payments', 'receipts', 'sale_orders', 'purchase_orders', 'gst_payments', 'cash_withdrawn', 'cash_deposited', 'sale_party_payments', 'purchase_party_payments', 'cash_in_hand'));

    //     return view('cash.cash_book', compact('opening_balance', 'closing_balance', 'combined_array', 'cash_in_hand', 'from_date', 'to_date'));

    // }

    public function generate_cash_book (Request $request) {

        if( $request->has('from_date') && $request->has('to_date') ){
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        if ($from_date < auth()->user()->profile->book_beginning_from) {
            return redirect()->back()->with('failure', 'Please select dates on or after the book beginning date');
        }

        $opening_balance_from_date = auth()->user()->profile->financial_year_from;
        $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

        $closing_balance_from_date = auth()->user()->profile->book_beginning_from;
        // $closing_balance_to_date = \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)->subDay();
        // $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        if( \Carbon\Carbon::parse($from_date)->lt(\Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)) ){
            $closing_balance_to_date = \Carbon\Carbon::parse($from_date);
        } else {
            $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        }

        $opening_balance = $this->fetch_static_balance($opening_balance_from_date, $opening_balance_to_date);

        // return $opening_balance;
        
        if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
            $this_balance = $this->fetch_opening_balance($opening_balance_from_date, $opening_balance_to_date);
            // return $this_balance;
            $opening_balance += $this_balance;
        }
        // return $opening_balance;
        // else {
        //     $opening_balance = $this->fetch_static_balance($opening_balance_from_date, $opening_balance_to_date);
        // }
        // closing balance will only change if there is no opening balance
        $closing_balance = 0;
        
        // if($opening_balance == 0){
            $closing_balance = $this->calculate_closing_balance($closing_balance_from_date, $closing_balance_to_date);
        // }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();
        
        // return $sale_party_payments;

        // return $receipts;

        $combined_array = array();
        
        $sale_array = array();
        $purchase_array = array();
        $sale_order_array = array();
        $purchase_order_array = array();
        $gst_payment_array = array();
        $receipt_array = array();
        $payment_array = array();
        $sale_party_payment_array = array();
        $purchase_party_payment_array = array();
        $cash_withdrawn_array = array();
        $cash_deposited_array = array();
        $setoff_make_payment_array = array();
        $advance_payment_cash = array();

        if ( isset( $sales) && !empty( $sales) ) {
            
            // return $sales;

            foreach( $sales as $sale ){
                if($sale->cash_payment != null && $sale->cash_payment > 0) {
                    $sale_array[] = [
                        'routable' => $sale->id,
                        'particulars' => $sale->party->name,
                        'voucher_type' => 'Sale',
                        'voucher_no' => $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix,
                        'amount' => $sale->cash_payment,
                        'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                        'month' => Carbon::parse($sale->invoice_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale',
                        'type' => 'showable'
                    ];
                }
                // if(isset($sale->discount_payment) && $sale->discount_payment != null && $sale->discount_payment > 0) {
                //     $sale_array[] = [
                //         'routable' => $sale->id,
                //         'particulars' => $sale->party->name,
                //         'voucher_type' => 'Sale (Cash Discount)',
                //         'voucher_no' => $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix,
                //         'amount' => $sale->discount_payment,
                //         'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                //         'month' => Carbon::parse($sale->invoice_date)->format('m'),
                //         'transaction_type' => 'debit',
                //         'loop' => 'sale',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        if ( isset( $purchases) && !empty( $purchases) ) {

            foreach( $purchases as $purchase ){
                if($purchase->cash_payment != null && $purchase->cash_payment > 0) {
                    $purchase_array[] = [
                        'routable' => $purchase->id,
                        'particulars' => $purchase->party->name,
                        'voucher_type' => 'Purchase',
                        'voucher_no' => $purchase->bill_no,
                        'amount' => $purchase->cash_payment,
                        'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                        'month' => Carbon::parse($purchase->bill_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase',
                        'type' => 'showable'
                    ];
                }
                // if(isset($purchase->discount_payment) && $purchase->discount_payment != null && $purchase->discount_payment > 0) {
                //     $purchase_array[] = [
                //         'routable' => $purchase->id,
                //         'particulars' => $purchase->party->name,
                //         'voucher_type' => 'Purchase (Cash Discount)',
                //         'voucher_no' => $purchase->bill_no,
                //         'amount' => $purchase->discount_payment,
                //         'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                //         'month' => Carbon::parse($purchase->bill_date)->format('m'),
                //         'transaction_type' => 'credit',
                //         'loop' => 'purchase',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        if ( isset( $sale_orders) && !empty( $sale_orders) ) {
            $order_no = null;
            foreach( $sale_orders as $order ){

                if($order_no == $order->token) continue;

                $order_no = $order->token;
                $party = Party::find($order->party_id);
    
                $order->party_name = $party->name;

                if($order->cash_amount != null && $order->cash_amount > 0) {
                    $sale_order_array[] = [
                        'routable' => $order->token,
                        'particulars' => $order->party_name,
                        'voucher_type' => 'Sale Order',
                        'voucher_no' => $order->token,
                        'amount' => $order->cash_amount,
                        'date' => Carbon::parse($order->date)->format('Y-m-d'),
                        'month' => Carbon::parse($order->date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale_order',
                        'type' => 'showable'
                    ];
                }

            }
        }

        if ( isset( $purchase_orders) && !empty( $purchase_orders) ) {
            $order_no = null;
            foreach( $purchase_orders as $order ) {

                if($order_no == $order->token) continue;

                $order_no = $order->token;
                $party = Party::find($order->party_id);
    
                $order->party_name = $party->name;

                if($order->cash_amount != null && $order->cash_amount > 0) {
                    $purchase_order_array[] = [
                        'routable' => $order->token,
                        'particulars' => $order->party_name,
                        'voucher_type' => 'Purchase Order',
                        'voucher_no' => $order->token,
                        'amount' => $order->cash_amount,
                        'date' => Carbon::parse($order->date)->format('Y-m-d'),
                        'month' => Carbon::parse($order->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_order',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if ( isset( $gst_payments) && !empty( $gst_payments) ) {

            foreach( $gst_payments as $payment ){

                if($payment->cash_amount != null && $payment->cash_amount > 0) {
                    $gst_payment_array[] = [
                        'routable' => null,
                        'particulars' => 'GST Cash',
                        'voucher_type' => 'GST',
                        'voucher_no' => null,
                        'amount' => $payment->cash_amount,
                        'date' => Carbon::parse($payment->created_at)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->created_at)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'gst_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($receipts) && !empty($receipts) ){
            foreach ($receipts as $receipt) {
                // $opening_balance += $receipt->cash_payment;
    
                $party = Party::find($receipt->party_id);
    
                $receipt->party_name = $party->name;

                if($receipt->cash_payment != null && $receipt->cash_payment > 0) {
                    $receipt_array[] = [
                        'routable' => $receipt->id,
                        'particulars' => $receipt->party_name,
                        'voucher_type' => 'Receipt',
                        'voucher_no' => $receipt->voucher_no,
                        'amount' => $receipt->cash_payment,
                        'date' => Carbon::parse($receipt->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($receipt->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'receipt',
                        'type' => 'showable'
                    ];
                }
                // if(isset($receipt->discount_payment) && $receipt->discount_payment != null && $receipt->discount_payment > 0) {
                //     $receipt_array[] = [
                //         'routable' => $receipt->id,
                //         'particulars' => $receipt->party_name,
                //         'voucher_type' => 'Receipt (Cash Discount)',
                //         'voucher_no' => $receipt->voucher_no,
                //         'amount' => $receipt->discount_payment,
                //         'date' => Carbon::parse($receipt->created_at)->format('Y-m-d'),
                //         'month' => Carbon::parse($receipt->created_at)->format('m'),
                //         'transaction_type' => 'debit',
                //         'loop' => 'receipt',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        if( isset($payments) && !empty($payments) ){
            foreach ($payments as $payment) {
                // $opening_balance -= $payment->cash_payment;
    
                $party = Party::find($payment->party_id);
    
                $payment->party_name = $party->name;

                if($payment->cash_payment != null && $payment->cash_payment > 0) {
                    $payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Payment',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->cash_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'payment',
                        'type' => 'showable'
                    ];
                }

                // if(isset($payment->discount_payment) && $payment->discount_payment != null && $payment->discount_payment > 0) {
                //     $payment_array[] = [
                //         'routable' => $payment->id,
                //         'particulars' => $payment->party_name,
                //         'voucher_type' => 'Payment (Cash Discount)',
                //         'voucher_no' => $payment->voucher_no,
                //         'amount' => $payment->discount_payment,
                //         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                //         'month' => Carbon::parse($payment->payment_date)->format('m'),
                //         'transaction_type' => 'credit',
                //         'loop' => 'payment',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        // return $sale_party_payments;

        if( isset($sale_party_payments) && !empty($sale_party_payments) ){
            foreach ($sale_party_payments as $payment) {
                $party = Party::find($payment->party_id);
                
                $payment->party_name = $party->name;

                if($payment->cash_payment != null && $payment->cash_payment > 0) {
                    $sale_party_payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Sale Party Receipt',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->cash_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale_party_payment',
                        'type' => 'showable'
                    ];
                }
                // if(isset($payment->discount_payment) && $payment->discount_payment != null && $payment->discount_payment > 0) {
                //     $sale_party_payment_array[] = [
                //         'routable' => $payment->id,
                //         'particulars' => $payment->party_name,
                //         'voucher_type' => 'Sale Party Receipt (Cash Discount)',
                //         'voucher_no' => $payment->voucher_no,
                //         'amount' => $payment->discount_payment,
                //         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                //         'month' => Carbon::parse($payment->payment_date)->format('m'),
                //         'transaction_type' => 'debit',
                //         'loop' => 'sale_party_payment',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        // return $sale_party_payment_array;

        if( isset($purchase_party_payments) && !empty($purchase_party_payments) ){
            foreach ($purchase_party_payments as $payment) {
                $party = Party::find($payment->party_id);
    
                $payment->party_name = $party->name;
                $payment->transaction_type = 'credit';

                if($payment->cash_payment != null && $payment->cash_payment > 0) {
                    $purchase_party_payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Purchase Party Payment',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->cash_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_party_payment',
                        'type' => 'showable'
                    ];
                }
                // if(isset($payment->discount_payment) && $payment->discount_payment != null && $payment->discount_payment > 0) {
                //     $purchase_party_payment_array[] = [
                //         'routable' => $payment->id,
                //         'particulars' => $payment->party_name,
                //         'voucher_type' => 'Purchase Party Payment (Cash Discount)',
                //         'voucher_no' => $payment->voucher_no,
                //         'amount' => $payment->discount_payment,
                //         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                //         'month' => Carbon::parse($payment->payment_date)->format('m'),
                //         'transaction_type' => 'credit',
                //         'loop' => 'purchase_party_payment',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        if( isset($cash_withdrawn) && !empty($cash_withdrawn) ){
            foreach ( $cash_withdrawn as $cash) {
                $bank = Bank::find($cash->bank);
    
                $cash->bank_name = $bank->name;
                $cash->transaction_type = 'debit';

                if($cash->amount != null && $cash->amount > 0) {
                    $cash_withdrawn_array[] = [
                        'routable' => $cash->id,
                        'particulars' => $cash->bank_name,
                        'voucher_type' => 'Contra',
                        'voucher_no' => $cash->contra,
                        'amount' => $cash->amount,
                        'date' => Carbon::parse($cash->date)->format('Y-m-d'),
                        'month' => Carbon::parse($cash->date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'cash_withdraw',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($cash_deposited) && !empty($cash_deposited) ){
            foreach ( $cash_deposited as $cash) {
                $bank = Bank::find($cash->bank);
    
                if($bank) {
                    $cash->bank_name = $bank->name;
                } else{
                    $cash->bank_name = null;
                }
                $cash->transaction_type = 'credit';

                if($cash->amount != null && $cash->amount > 0) {
                    $cash_deposited_array[] = [
                        'routable' => $cash->id,
                        'particulars' => $cash->bank_name,
                        'voucher_type' => 'Contra',
                        'voucher_no' => $cash->contra,
                        'amount' => $cash->amount,
                        'date' => Carbon::parse($cash->date)->format('Y-m-d'),
                        'month' => Carbon::parse($cash->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'cash_deposit',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($setoff_make_payments) && !empty($setoff_make_payments) ){
            foreach($setoff_make_payments as $payment ){

                if($payment->cash_payment != null && $payment->cash_payment > 0) {
                    $setoff_make_payment_array[] = [
                        'routable' => '',
                        'particulars' => 'GST Setoff Make Payment',
                        'voucher_type' => 'Setoff Payment',
                        'voucher_no' => $payment->id,
                        'amount' => $payment->cash_payment,
                        'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'setoff_payment',
                        'type' => 'showable'
                    ];
                }

                // if(isset($payment->discount_payment) && $payment->discount_payment != null && $payment->discount_payment > 0) {
                //     $setoff_make_payment_array[] = [
                //         'routable' => '',
                //         'particulars' => 'GST Setoff Make Payment',
                //         'voucher_type' => 'Setoff Payment (Cash Discount)',
                //         'voucher_no' => $payment->id,
                //         'amount' => $payment->discount_payment,
                //         'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                //         'month' => Carbon::parse($payment->date)->format('m'),
                //         'transaction_type' => 'credit',
                //         'loop' => 'setoff_payment',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        if (isset($advance_cash_ledger_cash_payments) && !empty($advance_cash_ledger_cash_payments)) {
            foreach ($advance_cash_ledger_cash_payments as $payment) {

                if($payment->cash_payment != null && $payment->cash_payment > 0) {
                    $advance_payment_cash[] = [
                        'routable' => $payment->id,
                        'particulars' => 'Advance Payment',
                        'voucher_type' => 'Payment',
                        'voucher_no' => $payment->voucher_no ?? $payment->id,
                        'amount' => $payment->cash_payment,
                        'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'advanced_payment',
                        'type' => 'showable'
                    ];
                }
                // if(isset($payment->discount_payment) && $payment->discount_payment != null && $payment->discount_payment > 0) {
                //     $advance_payment_cash[] = [
                //         'routable' => $payment->id,
                //         'particulars' => 'Advance Payment (Cash Discount)',
                //         'voucher_type' => 'Payment',
                //         'voucher_no' => $payment->voucher_no ?? $payment->id,
                //         'amount' => $payment->discount_payment,
                //         'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                //         'month' => Carbon::parse($payment->date)->format('m'),
                //         'transaction_type' => 'credit',
                //         'loop' => 'advanced_payment',
                //         'type' => 'showable'
                //     ];
                // }
            }
        }

        $combined_array = array_merge(
            $sale_array,
            $purchase_array,
            $sale_order_array,
            $purchase_order_array,
            $gst_payment_array,
            $receipt_array,
            $payment_array,
            $sale_party_payment_array,
            $purchase_party_payment_array,
            $cash_withdrawn_array,
            $cash_deposited_array,
            $setoff_make_payment_array,
            $advance_payment_cash
        );

        // return $combined_array;

        // $combined_array = collect($combined_array)->sortBy('date')->all();

        $this->array_sort_by_column($combined_array, 'date');

        // return $combined_array;

        $group = [];
        foreach ($combined_array as $item) {
            $count = 0;
            $month = Carbon::parse($item['date'])->format('F');

            if ( isset( $group[ $month ] ) ) {
                foreach( $group[ $month ] as $key => $value ){
                    $count = $key;
                }
            }
            $count++;
            // echo "<pre>";
            // print_r($item);
            // print_r( $group[$item['month']][$count] );
            foreach ($item as $key => $value) {
                // if ($key == 'month') continue;
                $group[ $month ][$count][$key] = $value;
            }
        }

        foreach( $group as $key => $value ){
            $creditTotal = 0;
            $debitTotal = 0;
            foreach( $value as $data ) {
                if( $data['transaction_type'] == 'credit' ){
                    $creditTotal += $data['amount'];
                } elseif( $data['transaction_type'] == 'debit' ){
                    $debitTotal += $data['amount'];
                }
            }
            $group[$key]['credit_total'] = $creditTotal;
            $group[$key]['debit_total'] = $debitTotal;
            $group[$key]['closing_total'] = $debitTotal - $creditTotal;
        }

        // print_r($group);
        
        $combined_array = $group;

        // echo "<pre>";
        // print_r($combined_array);
        // die();

        $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();
        //->whereBetween('balance_date', [ $from_date, $to_date])

        // return view('cash.cash_book', compact( 'opening_balance', 'opening_balance_date', 'sales', 'purchases', 'payments', 'receipts', 'sale_orders', 'purchase_orders', 'gst_payments', 'cash_withdrawn', 'cash_deposited', 'sale_party_payments', 'purchase_party_payments', 'cash_in_hand'));

        return view('cash.cash_book', compact('opening_balance', 'closing_balance', 'combined_array', 'cash_in_hand', 'from_date', 'to_date'));

    }

    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    // private function calculate_closing_balance ($till_date) {
    //     $closing_balance = 0;

    //     // $to_date = $opening_balance_date;

    //     // $financialYearFromYear = Carbon::parse( $from_date )->format('Y') - 1;
    //     // $financialYearFromDate = Carbon::parse( auth()->user()->profile->financial_year_from )->format('d');
    //     // $financialYearFromMonth = Carbon::parse( auth()->user()->profile->financial_year_from )->format('m');

    //     // $financialYearToYear = Carbon::parse( $to_date )->format('Y') - 1;
    //     // $financialYearToDate = Carbon::parse( auth()->user()->profile->financial_year_to )->format('d');
    //     // $financialYearToMonth = Carbon::parse( auth()->user()->profile->financial_year_to )->format('m');


    //     // $previousFinancialYearFrom = $financialYearFromYear .'-'. $financialYearFromMonth . '-' . $financialYearFromDate;
    //     // $previousFinancialYearTo = $financialYearToYear .'-'. $financialYearToMonth . '-' . $financialYearToDate;


    //     // echo $till_date;

    //     // die();

    //     $year = Carbon::parse($till_date)->format('Y');

    //     // was this before updating
    //     // $from_date = $year - 1 . "-04-01";
    //     // $to_date = $year . "-03-31";

    //     // $from_date = Carbon::parse(auth()->user()->profile->book_beginning_from)->format('Y-m-d');
    //     // $to_date = $till_date;

    //     $from_date = $year - 1 . "-04-01";
    //     $to_date = $year . "-03-31";

    //     // echo $from_date;
    //     // echo "<br/>";
    //     // echo $to_date;

    //     // if( $from_date < auth()->user()->profile->book_beginning_from && $to_date > auth()->user()->profile->book_beginning_from  ){
    //     //     $from_date = auth()->user()->profile->book_beginning_from;
    //     // }

    //     // die();

    //     $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoices.invoice_date', [$from_date, $to_date])->whereIn('invoices.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //     $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //     $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //     $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //     $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

    //     $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

    //     $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

    //     $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

    //     $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

    //     $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //     $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

    //     $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

    //     // $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->where('balance_date', '<', $till_date)->first();


    //     // if($cash_in_hand != null){
    //     //     $closing_balance += $cash_in_hand->opening_balance;
    //     // }

    //     foreach( $sales as $sale ){
    //         $closing_balance += $sale->cash_payment;
    //     }

    //     foreach( $purchases as $purchase ){
    //         $closing_balance -= $purchase->cash_payment;
    //     }

    //     foreach( $payments as $payment ){
    //         $closing_balance -= $payment->cash_payment;
    //     }

    //     foreach( $receipts as $receipt ){
    //         $closing_balance += $receipt->cash_payment;
    //     }

    //     foreach( $sale_orders as $order ){
    //         $closing_balance += $order->cash_amount;
    //     }

    //     foreach( $purchase_orders as $order ){
    //         $closing_balance -= $order->cash_amount;
    //     }

    //     foreach( $gst_payments as $gst ){
    //         $closing_balance -= $gst->cash_amount;
    //     }

    //     foreach( $cash_withdrawn as $cash ){
    //         $closing_balance += $cash->amount;
    //     }

    //     foreach( $cash_deposited as $cash ){
    //         $closing_balance -= $cash->amount;
    //     }

    //     foreach( $sale_party_payments as $payment ){
    //         $closing_balance += $payment->cash_payment;
    //     }

    //     foreach( $purchase_party_payments as $payment ){
    //         $closing_balance -= $payment->cash_payment;
    //     }

    //     foreach( $setoff_make_payments as $payment ){
    //         $closing_balance -= $payment->cash_payment;
    //     }

    //     // echo $closing_balance;

    //     // die();

    //     return $closing_balance;

    //     // return 0;

    // }

    private function calculate_closing_balance ($from_date, $to_date) {
        $from_date = \Carbon\Carbon::parse($from_date);
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();
        
        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }

        $closing_balance = 0;

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();


        // $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->whereBetween('balance_date', [$from_date, $to_date])->orderBy('id', 'desc')->first();


        // $q = CashInHand::where('user_id', Auth::user()->id);
        // $cashInHand = 0;
        // if ($isDatesSame){
        //     $q = $q->where('balance_date', $from_date)->orderBy('id', 'desc');
        //     $cash_in_hand = $q->first();

        //     if( isset($cash_in_hand) && !empty($cash_in_hand) ){
        //         if ($cash_in_hand->balance_type == 'creditor') {
        //             $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
        //         } else {
        //             $fixed_opening_balance = $cash_in_hand->opening_balance;
        //         }
        //         $cashInHand = $fixed_opening_balance;
        //     }

        // } else {
        //     $q = $q->whereBetween('balance_date', [$from_date, $to_date])->orderBy('id', 'desc');

        //     // can be "get" if need multiple or "first" if need only one
        //     $cash_in_hand = $q->first();

        //     if( isset($cash_in_hand) && !empty($cash_in_hand) ){
        //         if ($cash_in_hand->balance_type == 'creditor') {
        //             $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
        //         } else {
        //             $fixed_opening_balance = $cash_in_hand->opening_balance;
        //         }
        //         $cashInHand = $fixed_opening_balance;
        //     }

        // }


        // if($cash_in_hand != null){
        //     $closing_balance += $cashInHand;
        // }

        foreach( $sales as $sale ){
            $opening_balance += $sale->cash_payment;
        }

        foreach( $receipts as $receipt ){
            $opening_balance += $receipt->cash_payment;
        }

        foreach( $sale_orders as $order ){
            $opening_balance += $order->cash_amount;
        }
 

        foreach( $purchases as $purchase ){
            $opening_balance -= $purchase->cash_payment;
        }

        foreach( $payments as $payment ){
            $opening_balance -= $payment->cash_payment;
        }

        foreach( $purchase_orders as $order ){
            $opening_balance -= $order->cash_amount;
        }

        foreach( $gst_payments as $payment ){
            $opening_balance -= $payment->cash_amount;
        }


        foreach( $cash_withdrawn as $withdrawn ){
            $opening_balance += $withdrawn->amount;
        }

        foreach ($cash_deposited as $deposited) {
            $opening_balance -= $deposited->amount;
        }

        foreach( $sale_party_payments as $sale ){
            $opening_balance += $sale->cash_payment;
        }

        foreach( $purchase_party_payments as $payment ){
            $opening_balance -= $payment->cash_payment;
        }

        foreach( $setoff_make_payments as $payment ){
            $opening_balance -= $payment->cash_payment;
        }

        foreach($advance_cash_ledger_cash_payments as $payment){
            $opening_balance -= $payment->cash_payment;
        }

        return $closing_balance;

    }

    // private function fetch_static_balance($to, $from=null) {

    //     $opening_balance = null;

    //     $q = CashInHand::where('user_id', Auth::user()->id);

    //     if ($from != null){
    //         $q = $q->whereBetween('balance_date', [$from, $to]);
    //         $cash_in_hand = $q->first();

    //         if( isset($cash_in_hand) && !empty($cash_in_hand) ){
    //             if ($cash_in_hand->balance_type == 'creditor') {
    //                 $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
    //             } else {
    //                 $fixed_opening_balance = $cash_in_hand->opening_balance;
    //             }
    //             $opening_balance = $fixed_opening_balance;
    //         }

    //     } else {
    //         $opening_balance = 0;

    //         $year = Carbon::parse($to)->format('Y');

    //         $from_date = $year - 1 . "-04-01";
    //         $to_date = $year . "-03-31";

    //         $q = $q->whereBetween('balance_date', [$from_date, $to_date]);
    //         $cash_in_hand = $q->get();

    //         foreach( $cash_in_hand as $cash ){
    //             if ( $cash->balance_type == 'creditor') {
    //                 $fixed_opening_balance = "-" . $cash->opening_balance;
    //             } else {
    //                 $fixed_opening_balance = $cash->opening_balance;
    //             }

    //             $opening_balance += $fixed_opening_balance;
    //         }

    //     }

    //     return $opening_balance;
    // }

    private function fetch_static_balance($from, $to) {

        $from_date = \Carbon\Carbon::parse($from);
        $to_date = \Carbon\Carbon::parse($to);

        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }
        // adding a day to make both from and to dates inclusive for searching
        $to_date = \Carbon\Carbon::parse($to)->addDay();

        $opening_balance = 0;

        $q = CashInHand::where('user_id', Auth::user()->id);

        if ($isDatesSame){
            $q = $q->where('balance_date', $from_date)->orderBy('id', 'desc');
            $cash_in_hand = $q->first();

            if( isset($cash_in_hand) && !empty($cash_in_hand) ){
                if ($cash_in_hand->balance_type == 'creditor') {
                    $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
                } else {
                    $fixed_opening_balance = $cash_in_hand->opening_balance;
                }
                $opening_balance = $fixed_opening_balance;
            }

        } else {
            $q = $q->whereBetween('balance_date', [$from_date, $to_date])->orderBy('id', 'desc');

            // can be "get" if need multiple or "first" if need only one
            $cash_in_hand = $q->first();

            // foreach( $cash_in_hand as $cash ){
            //     if ( $cash->balance_type == 'creditor') {
            //         $fixed_opening_balance = "-" . $cash->opening_balance;
            //     } else {
            //         $fixed_opening_balance = $cash->opening_balance;
            //     }

            //     $opening_balance += $fixed_opening_balance;
            // }

            if( isset($cash_in_hand) && !empty($cash_in_hand) ){
                if ($cash_in_hand->balance_type == 'creditor') {
                    $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
                } else {
                    $fixed_opening_balance = $cash_in_hand->opening_balance;
                }
                $opening_balance = $fixed_opening_balance;
            }

        }

        return $opening_balance;
    }

    private function fetch_opening_balance($from_date, $to_date) {

        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date);
        // subtract a minute as we want to search till that date
        // eg if to_date is 03-04-2020 subtract 1 min will become 02-04-2020 23:59:00
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();

        // commented cause opening balance will only be for 01-04
        // $opening_balance += $this->fetch_static_balance($from_date, $to_date);

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();


        foreach( $sales as $sale ){
            $opening_balance += $sale->cash_payment;
        }

        foreach( $receipts as $receipt ){
            $opening_balance += $receipt->cash_payment;
        }

        foreach( $sale_orders as $order ){
            $opening_balance += $order->cash_amount;
        }
 

        foreach( $purchases as $purchase ){
            $opening_balance -= $purchase->cash_payment;
        }

        foreach( $payments as $payment ){
            $opening_balance -= $payment->cash_payment;
        }

        foreach( $purchase_orders as $order ){
            $opening_balance -= $order->cash_amount;
        }

        foreach( $gst_payments as $payment ){
            $opening_balance -= $payment->cash_amount;
        }


        foreach( $cash_withdrawn as $withdrawn ){
            $opening_balance += $withdrawn->amount;
        }

        foreach ($cash_deposited as $deposited) {
            $opening_balance -= $deposited->amount;
        }

        foreach( $sale_party_payments as $sale ){
            $opening_balance += $sale->cash_payment;
        }

        foreach( $purchase_party_payments as $payment ){
            $opening_balance -= $payment->cash_payment;
        }

        foreach( $setoff_make_payments as $payment ){
            $opening_balance -= $payment->cash_payment;
        }

        foreach($advance_cash_ledger_cash_payments as $payment){
            $opening_balance -= $payment->cash_payment;
        }

        return $opening_balance;
    }

//     private function calculate_opening_balance ( $opening_balance_from_date, $opening_balance_to_date ) {

//         $opening_balance = 0;

//         $from_date = $opening_balance_from_date;
//         $to_date = $opening_balance_to_date;


//         $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

//     // print_r($sales);
        
//     //     die();

//         $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

//     // print_r($purchases);
        
//     //     die();
//         $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

//     // print_r($payments);
        
//     //     die();

//         $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

// // print_r($receipts);
        
// //         die();

//         $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

// // print_r($sale_orders);
        
// //         die();

//         $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

//         $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

//         $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

//         $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

//         $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

//         $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

//         $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();


//         foreach( $sales as $sale ){
//             $opening_balance += $sale->cash_payment;
//         }

//         foreach( $receipts as $receipt ){
//             $opening_balance += $receipt->cash_payment;
//         }

//         foreach( $sale_orders as $order ){
//             $opening_balance += $order->cash_amount;
//         }


//         foreach( $purchases as $purchase ){
//             $opening_balance -= $purchase->cash_payment;
//         }

//         foreach( $payments as $payment ){
//             $opening_balance -= $payment->cash_payment;
//         }

//         foreach( $purchase_orders as $order ){
//             $opening_balance -= $order->cash_amount;
//         }

//         foreach( $gst_payments as $payment ){
//             $opening_balance -= $payment->cash_amount;
//         }


//         foreach( $cash_withdrawn as $withdrawn ){
//             $opening_balance += $withdrawn->amount;
//         }

//         foreach ($cash_deposited as $deposited) {
//             $opening_balance -= $deposited->amount;
//         }

//         foreach( $sale_party_payments as $sale ){
//             $opening_balance += $sale->cash_payment;
//         }

//         foreach( $purchase_party_payments as $payment ){
//             $opening_balance -= $payment->cash_payment;
//         }

//         foreach( $setoff_make_payments as $payment ){
//             $opening_balance -= $payment->cash_payment;
//         }
        
//         // echo $opening_balance;
//         // die();

//         return $opening_balance;

//     }

    public function edit_cash_withdraw_form($id)
    {
        $cash_withdraw = CashWithdraw::find($id);

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('cash.edit_cash_withdraw', compact('cash_withdraw', 'banks'));
    }

    public function update_cash_withdraw(Request $request, $id)
    {
        $cash_withdraw = CashWithdraw::find($id);

        $cash_withdraw->amount = $request->amount;
        $cash_withdraw->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $cash_withdraw->bank = $request->bank;
        $cash_withdraw->contra = $request->contra;
        $cash_withdraw->narration = $request->narration;

        if($cash_withdraw->save()){
            return redirect()->back()->with('success', 'Data updated successfully!!');
        } else{
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function edit_cash_deposit_form($id)
    {
        $cash_deposit = CashDeposit::find($id);

        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('cash.edit_cash_deposit', compact('cash_deposit', 'banks'));
    }

    public function update_cash_deposit(Request $request, $id)
    {
        $cash_deposit = CashDeposit::find($id);

        $cash_deposit->amount = $request->amount;
        $cash_deposit->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $cash_deposit->bank = $request->bank;
        $cash_deposit->contra = $request->contra;
        $cash_deposit->narration = $request->narration;

        if ($cash_deposit->save()) {
            return redirect()->back()->with('success', 'Data updated successfully!!');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
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

        foreach($rows as $row) {
            if(  $row->party_id == $party_id && $user->profile->financial_year_from <= $row->created_at && $user->profile->financial_year_to >= $row->created_at ) {
                $isValidated = false;
                break;
            }
        }

        if(!$isValidated){
            return response()->json(array(
                    'success' => false,
                    'errors' => 'Please provide unique order no for selected party in the current financial year'
                ), 400);
        }

        return response()->json(array('success' => true), 200);
    }

    public function validate_cash_deposit_voucher_no(Request $request)
    {
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('cash_deposit')
            ->where('contra', $request->token)
            ->get();

        foreach($rows as $row) {
            if(  $user->profile->financial_year_from <= $row->date && $user->profile->financial_year_to >= $row->date ) {
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

    public function validate_cash_withdraw_voucher_no(Request $request)
    {
        $user = User::find($request->user);
        $isValidated = true;

        $rows = DB::table('cash_withdraw')
            ->where('contra', $request->token)
            ->get();

        foreach($rows as $row) {
            if(  $user->profile->financial_year_from <= $row->date && $user->profile->financial_year_to >= $row->date ) {
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

    public function view_cash_deposit(Request $request)
    {
        $cash_amounts = Auth::user()->cashDeposit()->get();
        foreach($cash_amounts as $amount) {
            $amount->banks = Bank::find($amount->bank)->name;
        }
        return view('cash.view_cash_deposit', compact('cash_amounts'));
    }

    public function update_cash_deposit_status(Request $request, $id)
    {
        $cash_withdraw = CashDeposit::findOrFail($id);
        $cash_withdraw->status = $request->status;
        $cash_withdraw->save();
        return redirect()->back();
    }

    public function view_cash_withdraw(Request $request)
    {
        $cash_amounts = Auth::user()->cashWithdraw()->get();
        foreach($cash_amounts as $amount) {
            $amount->banks = Bank::find($amount->bank)->name;
        }
        return view('cash.view_cash_withdraw', compact('cash_amounts'));
    }

    public function update_cash_withdraw_status(Request $request, $id)
    {
        $cash_withdraw = CashWithdraw::findOrFail($id);
        $cash_withdraw->status = $request->status;
        $cash_withdraw->save();
        return redirect()->back();
    }

    public function view_contra(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $deposit_amounts = Auth::user()->cashDeposit()->whereBetween('date', [$from_date, $to_date])->get();
        foreach($deposit_amounts as $amount) {
            $amount->banks = Bank::find($amount->bank)->name;
        }

        $withdraw_amounts = Auth::user()->cashWithdraw()->whereBetween('date', [$from_date, $to_date])->get();
        foreach($withdraw_amounts as $amount) {
            $amount->banks = Bank::find($amount->bank)->name;
        }

        $bank_amounts = Auth::user()->bankToBankTransfers()->whereBetween('date', [$from_date, $to_date])->get();
        foreach($bank_amounts as $amount) {
            $amount->to_banks = Bank::find($amount->to_bank)->name;
            $amount->from_banks = Bank::find($amount->from_bank)->name;
        }


        return view('cash.view_contra', compact('deposit_amounts', 'withdraw_amounts', 'bank_amounts'));
    }
}
