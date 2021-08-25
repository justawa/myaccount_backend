<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Bank;
use App\BankToBankTransfer;
use App\User;
use App\SaleOrder;
use App\PurchaseOrder;
use App\CashDeposit;
use App\CashWithdraw;
use App\GSTCashLedgerBalance;
use App\GSTSetOff;
use App\Party;


class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        foreach( $banks as $bank ) {
            $sales = User::findOrFail(Auth::user()->id)->invoices()->where('bank_id', $bank->id)->get();
            $purchases = User::find(Auth::user()->id)->purchases()->where('bank_id', $bank->id)->get();
            $purchase_remaining_amounts = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('bank_id', $bank->id)->get();
            $sale_remaining_amounts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('bank_id', $bank->id)->get();

            $sale_party_pending_amount = User::find(Auth::user()->id)->partyRemainingAmounts()->where('bank_id', $bank->id)->where('type', 'sale')->get();

            $purchase_party_pending_amount = User::find(Auth::user()->id)->partyRemainingAmounts()->where('bank_id', $bank->id)->where('type', 'purchase')->get();

            $bank->deposited_in_bank = 0;
            $bank->withdrawn_from_bank = 0;
            
            foreach( $sales as $sale ){
                $bank->deposited_in_bank += $sale->bank_payment;
            }

            foreach( $sale_remaining_amounts as $sale ){
                $bank->deposited_in_bank += $sale->bank_payment;
            }

            foreach( $sale_party_pending_amount as $sale ){
                $bank->deposited_in_bank += $sale->amount;
            }

            foreach( $purchases as $purchase ){
                $bank->withdrawn_from_bank += $purchase->bank_payment;
            }

            foreach( $purchase_remaining_amounts as $purchase ){
                $bank->withdrawn_from_bank += $purchase->bank_payment;
            }

            foreach( $purchase_party_pending_amount as $purchase ){
                $bank->withdrawn_from_bank += $purchase->amount;
            }

        }

        return view('bank.index', compact('banks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('bank.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|string',
            'account_no' => 'required|numeric',
            'branch' => 'required|string',
            'ifsc' => 'required|alpha_num',
            'opening_balance' => 'numeric|nullable',
            'opening_balance_on_date' => 'date_format:d/m/Y|nullable',
            'balance_type' => 'string|nullable',
            'classification' => 'required|string',
            'type' => 'string|nullable'
        ]);

        $bank = new Bank;

        $bank->name = $request->name;
        $bank->account_no = $request->account_no;
        $bank->branch = $request->branch;
        $bank->ifsc = $request->ifsc;
        $bank->opening_balance = $request->opening_balance;
        $bank->opening_balance_on_date = date('Y-m-d', strtotime( str_replace('/', '-', $request->opening_balance_on_date)));
        $bank->balance_type = $request->balance_type;
        $bank->classification = $request->classification;
        $bank->type = $request->type;
        $bank->user_id = Auth::user()->id;

        if( $bank->opening_balance_on_date < auth()->user()->profile->financial_year_from ){
            return redirect()->back()->with('failure', 'Please select valid opening balance date for current financial year');
        }

        if( $bank->opening_balance_on_date > auth()->user()->profile->financial_year_to ){
            return redirect()->back()->with('failure', 'Please select valid opening balance date for current financial year');
        }

        if ($bank->save()) {
            return redirect()->back()->with('success', 'Bank inserted successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to insert bank');
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
        $bank = Bank::find($id);

        return view('bank.edit', compact('bank'));
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
        $bank = Bank::find($id);

        $bank->name = $request->name;
        $bank->account_no = $request->account_no;
        $bank->branch = $request->branch;
        $bank->ifsc = $request->ifsc;
        $bank->opening_balance = $request->opening_balance;
        $bank->opening_balance_on_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->opening_balance_on_date)));
        $bank->balance_type = $request->balance_type;
        $bank->classification = $request->classification;
        $bank->type = $request->type;

        if( $bank->opening_balance_on_date < auth()->user()->profile->financial_year_from ){
            return redirect()->back()->with('failure', 'Please provide valid opening balance date for current financial year');
        }

        if( $bank->opening_balance_on_date > auth()->user()->profile->financial_year_to ){
            return redirect()->back()->with('failure', 'Please provide valid opening balance date for current financial year');
        }

        if($bank->save()){
            return redirect()->back()->with('success', 'Bank details updated successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to update bank');
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

    public function generate_bank_book(Request $request, $bank_id)
    {

        $bank = User::find(auth()->user()->id)->banks()->where('id', $bank_id)->first();

        // return $bank;

        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime( str_replace('/', '-', $request->from_date) ));

            $to_date = date('Y-m-d', strtotime( str_replace('/', '-', $request->to_date) ));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        if ($from_date < auth()->user()->profile->book_beginning_from) {
            return redirect()->back()->with('failure', 'Please select dates on or after the book beginning date');
        }

        // if (auth()->user()->profile->financial_year_from <= $from_date && auth()->user()->profile->financial_year_to >= $from_date) {
        //     $opening_balance_from_date = auth()->user()->profile->financial_year_from;
        //     $opening_balance_to_date = $from_date;
            
        // } else {
        //     $month = Carbon::parse($from_date)->format('m');

        //     if ($month == "01" || $month == "02" || $month == "03") {
        //         $year = Carbon::parse($from_date)->format('Y') - 1;
        //     }
        //     else {
        //         $year = Carbon::parse($from_date)->format('Y');
        //     }

        //     $opening_balance_from_date = $year . "-04" . "-01";
        //     $opening_balance_to_date = $from_date;
        // }

        // $static_opening_balance_from = auth()->user()->profile->financial_year_from;
        // $static_opening_balance_to = $to_date;
        // $closing_balance_till_date = $opening_balance_from_date;
        // $opening_balance = null;

            /*if( $opening_balance == null ){

                // if(\Carbon\Carbon::parse(auth()->user()->profile->book_beginning_from) < \Carbon\Carbon::parse($opening_balance_to_date) ){
                //     $opening_balance = $this->calculate_opening_balance($bank, $opening_balance_from_date, $opening_balance_to_date);
                // } else {
                //     $opening_balance = 0;
                // }
                
                $opening_balance = $this->calculate_opening_balance($bank, $opening_balance_from_date, $opening_balance_to_date);
                $closing_balance = $this->calculate_closing_balance($bank, $closing_balance_till_date);
                $closing_balance += $this->fetch_static_balance($bank, $closing_balance_till_date);
            }*/

        $opening_balance_from_date = auth()->user()->profile->financial_year_from;
        $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

        $closing_balance_from_date = auth()->user()->profile->book_beginning_from;
        // $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        if( \Carbon\Carbon::parse($from_date)->lt(\Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)) ){
            $closing_balance_to_date = \Carbon\Carbon::parse($from_date);
        } else {
            $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        }

        // echo $opening_balance_from_date . " " . $opening_balance_to_date;
        // echo "<br/>";
        // return 
        
        $opening_balance = $this->fetch_static_balance($bank, $opening_balance_from_date, $opening_balance_to_date);
        
        // return $opening_balance;

        if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
            $this_balance = $this->fetch_opening_balance($bank, $opening_balance_from_date, $opening_balance_to_date);
            // return $this_balance;
            $opening_balance += $this_balance;
        }

        // return $opening_balance;

        $closing_balance = 0;
        
        // if($opening_balance == 0){
            $closing_balance = $this->calculate_closing_balance($bank, $closing_balance_from_date, $closing_balance_to_date);
        // }

        // $from_date_d = Carbon::parse($from_date)->format('d') + 1;
        // $from_date_m = Carbon::parse($from_date)->format('m');
        // $from_date_Y = Carbon::parse($from_date)->format('Y');

        // $from_date = $from_date_Y . '-' . $from_date_m . '-' . $from_date_d;

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $sales_pos = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $purchases_pos = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        $payments_pos = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        $receipts_pos = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        // $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $from_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

        $to_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $sale_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $purchase_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $setoff_make_pos_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        // } else {

        //     $opening_balance_date = date('Y-m-d', time());

        //     $till_date = $opening_balance_date;

        //     $opening_balance = $this->calculate_opening_balance($bank->id, $till_date);

        //     $sales = User::findOrFail(Auth::user()->id)->invoices()->where('invoice_date', '<=', $till_date)->where('bank_id', $bank->id)->get();

        //     $sales_pos = User::findOrFail(Auth::user()->id)->invoices()->where('invoice_date', '<=', $till_date)->where('pos_bank_id', $bank->id)->get();

        //     $purchases = User::find(Auth::user()->id)->purchases()->where('bill_date', '<=', $till_date)->where('bank_id', $bank->id)->get();

        //     $purchases_pos = User::find(Auth::user()->id)->purchases()->where('bill_date', '<=', $till_date)->where('pos_bank_id', $bank->id)->get();

        //     $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.payment_date', '<=', $till_date)->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        //     $payments_pos = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.payment_date', '<=', $till_date)->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        //     $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.payment_date', '<=', $till_date)->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        //     $receipts_pos = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.payment_date', '<=', $till_date)->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        //     $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->where('bank_id', $bank->id)->get();

        //     $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->where('bank_id', $bank->id)->get();

        //     // $gst_payments = CashGST::where('user_id', Auth::user()->id)->where('created_at', '<=', $till_date)->get();

        //     $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->where('bank', $bank->id)->get();

        //     $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('date', '<=', $till_date)->where('bank', $bank->id)->get();

        //     $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'bank')->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.payment_date', '<=', $till_date)->where('party_pending_payment_account.bank_id', $bank->id)->get();

        //     $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'bank')->where('party_pending_payment_account.type', 'purchase')->where( 'party_pending_payment_account.payment_date', '<=', $till_date)->where('party_pending_payment_account.bank_id', $bank->id)->get();
        // }

        $combined_array = array();
        
        $sale_array = array();
        $sale_pos_array = array();
        $purchase_array = array();
        $purchase_pos_array = array();
        $sale_order_array = array();
        $purchase_order_array = array();
        $gst_payment_array = array();
        $receipt_array = array();
        $receipt_pos_array = array();
        $payment_array = array();
        $payment_pos_array = array();
        $sale_party_payment_array = array();
        $purchase_party_payment_array = array();
        $cash_withdrawn_array = array();
        $cash_deposited_array = array();
        $from_bank_transfers_array = array();
        $to_bank_transfers_array = array();

        $setoff_make_payment_array = array();
        $setoff_make_pos_payment_array = array();

        $advance_payment_bank = array();
        $advance_payment_pos = array();

        if ( isset( $sales) && !empty( $sales) ) {
            foreach( $sales as $sale ){

                if($sale->bank_payment != null && $sale->bank_payment > 0) {
                    $sale_array[] = [
                        'routable' => $sale->id,
                        'particulars' => $sale->party->name,
                        'voucher_type' => 'Sale',
                        'voucher_no' => $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix,
                        'amount' => $sale->bank_payment,
                        'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                        'month' => Carbon::parse($sale->invoice_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($sales_pos) && !empty($sales_pos) ) {
            foreach( $sales_pos as $sale ) {

                if($sale->pos_payment != null && $sale->pos_payment > 0) {
                    $sale_pos_array[] = [
                        'routable' => $sale->id,
                        'particulars' => $sale->party->name,
                        'voucher_type' => 'Sale POS',
                        'voucher_no' => $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix,
                        'amount' => $sale->pos_payment,
                        'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                        'month' => Carbon::parse($sale->invoice_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale_pos',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if ( isset( $purchases) && !empty( $purchases) ) {
            foreach( $purchases as $purchase ){

                if($purchase->bank_payment != null && $purchase->bank_payment > 0) {
                    $purchase_array[] = [
                        'routable' => $purchase->id,
                        'particulars' => $purchase->party->name,
                        'voucher_type' => 'Purchase',
                        'voucher_no' => $purchase->bill_no,
                        'amount' => $purchase->bank_payment,
                        'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                        'month' => Carbon::parse($purchase->bill_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($purchases_pos) && !empty($purchases) ) {

            foreach( $purchases_pos as $purchase ){

                if($purchase->pos_payment != null && $purchase->pos_payment > 0) {
                    $purchase_pos_array[] = [
                        'routable' => $purchase->id,
                        'particulars' => $purchase->party->name,
                        'voucher_type' => 'Purchase POS',
                        'voucher_no' => $purchase->bill_no,
                        'amount' => $purchase->pos_payment,
                        'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                        'month' => Carbon::parse($purchase->bill_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_pos',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if ( isset( $sale_orders) && !empty( $sale_orders) ) {
            $order_no = null;
            foreach( $sale_orders as $order ){

                if($order_no == $order->token) continue;

                $order_no = $order->token;

                $bankOp = 'bank'.$order->token;
                $posOp = 'pos'.$order->token;

                if($order->bank_amount != null && $order->bank_amount > 0) {
                    $sale_order_array[$bankOp] = [
                        'routable' => $order->token,
                        'particulars' => $order->party->name,
                        'voucher_type' => 'Sale Order (Bank)',
                        'voucher_no' => $order->token,
                        'amount' => $order->bank_amount,
                        'date' => Carbon::parse($order->date)->format('Y-m-d'),
                        'month' => Carbon::parse($order->date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale_order',
                        'type' => 'showable'
                    ];
                }

                if($order->pos_amount != null && $order->pos_amount > 0) {
                    $sale_order_array[$posOp] = [
                        'routable' => $order->token,
                        'particulars' => $order->party->name,
                        'voucher_type' => 'Sale Order (POS)',
                        'voucher_no' => $order->token,
                        'amount' => $order->pos_amount,
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

                $bankOp = 'bank'.$order->token;
                $posOp = 'pos'.$order->token;

                if($order->bank_amount != null && $order->bank_amount > 0) {
                    $purchase_order_array[$bankOp] = [
                        'routable' => $order->token,
                        'particulars' => $order->party->name,
                        'voucher_type' => 'Purchase Order',
                        'voucher_no' => $order->token,
                        'amount' => $order->bank_amount,
                        'date' => Carbon::parse($order->date)->format('Y-m-d'),
                        'month' => Carbon::parse($order->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_order',
                        'type' => 'showable'
                    ];
                }

                if($order->pos_amount != null && $order->pos_amount > 0) {
                    $purchase_order_array[$posOp] = [
                        'routable' => $order->token,
                        'particulars' => $order->party->name,
                        'voucher_type' => 'Purchase Order',
                        'voucher_no' => $order->token,
                        'amount' => $order->pos_amount,
                        'date' => Carbon::parse($order->date)->format('Y-m-d'),
                        'month' => Carbon::parse($order->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_order',
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

                if($receipt->bank_payment != null && $receipt->bank_payment > 0) {
                    $receipt_array[] = [
                        'routable' => $receipt->id,
                        'particulars' => $receipt->party_name,
                        'voucher_type' => 'Receipt',
                        'voucher_no' => $receipt->voucher_no,
                        'amount' => $receipt->bank_payment,
                        'date' => Carbon::parse($receipt->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($receipt->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'receipt',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($receipts_pos) && !empty($receipts_pos) ){

            foreach ($receipts_pos as $receipt) {

                $party = Party::find($receipt->party_id);

                $receipt->party_name = $party->name;

                if($receipt->pos_payment != null && $receipt->pos_payment > 0) {
                    $receipt_array[] = [
                        'routable' => $receipt->id,
                        'particulars' => $receipt->party_name,
                        'voucher_type' => 'Receipt POS',
                        'voucher_no' => $receipt->voucher_no,
                        'amount' => $receipt->pos_payment,
                        'date' => Carbon::parse($receipt->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($receipt->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'receipt_pos',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($payments) && !empty($payments) ){
            foreach ($payments as $payment) {
                // $opening_balance -= $payment->cash_payment;

                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                if($payment->bank_payment != null && $payment->bank_payment > 0) {
                    $payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Payment',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->bank_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($payments_pos) && !empty($payments_pos) ){
            foreach ($payments_pos as $payment) {
                // $opening_balance -= $payment->cash_payment;

                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                if($payment->pos_payment != null && $payment->pos_payment > 0) {
                    $payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Payment POS',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->pos_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'payment_pos',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($sale_party_payments) && !empty($sale_party_payments) ){
            foreach ($sale_party_payments as $payment) {
                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                if($payment->bank_payment != null && $payment->bank_payment > 0) {
                    $sale_party_payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Sale Party Receipt',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->bank_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale_party_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if (isset($sale_party_pos_payments) && !empty($sale_party_pos_payments)) {
            foreach ($sale_party_pos_payments as $payment) {
                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                if($payment->pos_payment != null && $payment->pos_payment > 0) {
                    $sale_party_payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Sale Party Receipt POS',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->pos_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale_party_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($purchase_party_payments) && !empty($purchase_party_payments) ){
            foreach ($purchase_party_payments as $payment) {
                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                if($payment->bank_payment != null && $payment->bank_payment > 0) {
                    $purchase_party_payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Purchase Party Payment',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->bank_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_party_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if (isset($purchase_party_pos_payments) && !empty($purchase_party_pos_payments)) {
            foreach ($purchase_party_pos_payments as $payment) {
                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                if($payment->pos_payment != null && $payment->pos_payment > 0) {
                    $purchase_party_payment_array[] = [
                        'routable' => $payment->id,
                        'particulars' => $payment->party_name,
                        'voucher_type' => 'Purchase Party Payment POS',
                        'voucher_no' => $payment->voucher_no,
                        'amount' => $payment->pos_payment,
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'purchase_party_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if( isset($cash_withdrawn) && !empty($cash_withdrawn) ) {
            foreach ($cash_withdrawn as $cash) {
                $current_bank = Bank::find($cash->bank);

                $cash->bank_name = $current_bank->name;

                if($cash->amount != null && $cash->amount > 0) {
                    // if ($current_bank->classification == 'current asset' || $current_bank->classification == 'fixed asset') {
                    //     $cash_withdrawn_array[] = [
                    //         'routable' => $cash->id,
                    //         'particulars' => $cash->bank_name,
                    //         'voucher_type' => 'Contra',
                    //         'voucher_no' => $cash->contra,
                    //         'amount' => $cash->amount,
                    //         'date' => Carbon::parse($cash->date)->format('Y-m-d'),
                    //         'month' => Carbon::parse($cash->date)->format('m'),
                    //         'transaction_type' => 'debit',
                    //         'loop' => 'cash_withdraw',
                    //         'type' => 'showable'
                    //     ];
                    // } else {
                        $cash_withdrawn_array[] = [
                            'routable' => $cash->id,
                            'particulars' => $cash->bank_name,
                            'voucher_type' => 'Contra',
                            'voucher_no' => $cash->contra,
                            'amount' => $cash->amount,
                            'date' => Carbon::parse($cash->date)->format('Y-m-d'),
                            'month' => Carbon::parse($cash->date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'cash_withdraw',
                            'type' => 'showable'
                        ];
                    // }
                }
            }
        }

        if( isset($cash_deposited) && !empty($cash_deposited) ){
            foreach ($cash_deposited as $cash) {
                $current_bank = Bank::find($cash->bank);

                $cash->bank_name = $current_bank->name;

                if($cash->amount != null && $cash->amount > 0) {
                    // if ($current_bank->classification == 'current asset' || $current_bank->classification == 'fixed asset') {
                    //     $cash_deposited_array[] = [
                    //         'routable' => $cash->id,
                    //         'particulars' => $cash->bank_name,
                    //         'voucher_type' => 'Contra',
                    //         'voucher_no' => $cash->contra,
                    //         'amount' => $cash->amount,
                    //         'date' => Carbon::parse($cash->date)->format('Y-m-d'),
                    //         'month' => Carbon::parse($cash->date)->format('m'),
                    //         'transaction_type' => 'credit',
                    //         'loop' => 'cash_deposit',
                    //         'type' => 'showable'
                    //     ];
                    // } else {
                        $cash_deposited_array[] = [
                            'routable' => $cash->id,
                            'particulars' => $cash->bank_name,
                            'voucher_type' => 'Contra',
                            'voucher_no' => $cash->contra,
                            'amount' => $cash->amount,
                            'date' => Carbon::parse($cash->date)->format('Y-m-d'),
                            'month' => Carbon::parse($cash->date)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'cash_deposit',
                            'type' => 'showable'
                        ];
                    // }
                }
            }
        }

        if(isset($from_bank_transfers) && !empty($from_bank_transfers)) {
            foreach ($from_bank_transfers as $transfer) {
                $current_bank = Bank::find($transfer->to_bank);

                if($transfer->amount != null && $transfer->amount > 0) {
                    $from_bank_transfers_array[] = [
                        'routable' => $transfer->id,
                        'particulars' => $current_bank->name,
                        'voucher_type' => 'Bank Transfer',
                        'voucher_no' => $transfer->contra,
                        'amount' => $transfer->amount,
                        'date' => Carbon::parse($transfer->date)->format('Y-m-d'),
                        'month' => Carbon::parse($transfer->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'from_bank_transfers',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if(isset($to_bank_transfers) && !empty($to_bank_transfers)) {
            foreach ($to_bank_transfers as $transfer) {
                $current_bank = Bank::find($transfer->from_bank);

                if($transfer->amount != null && $transfer->amount > 0) {
                    $to_bank_transfers_array[] = [
                        'routable' => $transfer->id,
                        'particulars' => $current_bank->name,
                        'voucher_type' => 'Bank Transfer',
                        'voucher_no' => $transfer->contra,
                        'amount' => $transfer->amount,
                        'date' => Carbon::parse($transfer->date)->format('Y-m-d'),
                        'month' => Carbon::parse($transfer->date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'to_bank_transfers',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if (isset($setoff_make_payments) && !empty($setoff_make_payments)) {
            foreach ($setoff_make_payments as $payment) {

                $current_bank = Bank::find($payment->bank_id);

                if($current_bank){
                    $payment->bank_name = $current_bank->name;
                } else {
                    $payment->bank_name = null;
                }

                if($payment->bank_payment != null && $payment->bank_payment > 0) {
                    $setoff_make_payment_array[] = [
                        'routable' => '',
                        'particulars' => 'GST Setoff Make Payment',
                        'voucher_type' => 'Setoff Payment',
                        'voucher_no' => $payment->id,
                        'amount' => $payment->bank_payment,
                        'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'setoff_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if (isset($setoff_make_pos_payments) && !empty($setoff_make_pos_payments)) {
            foreach ($setoff_make_pos_payments as $payment) {

                $current_bank = Bank::find($payment->pos_bank_id);

                if ($current_bank) {
                    $payment->bank_name = $current_bank->name;
                } else {
                    $payment->bank_name = null;
                }

                if($payment->pos_payment != null && $payment->pos_payment > 0) {
                    $setoff_make_pos_payment_array[] = [
                        'routable' => '',
                        'particulars' => 'GST Setoff Make Payment',
                        'voucher_type' => 'Setoff Payment',
                        'voucher_no' => $payment->id,
                        'amount' => $payment->pos_payment,
                        'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'setoff_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if (isset($advance_cash_ledger_bank_payments) && !empty($advance_cash_ledger_bank_payments)) {
            foreach ($advance_cash_ledger_bank_payments as $payment) {
                if($payment->bank_payment != null && $payment->bank_payment > 0) {
                    $advance_payment_bank[] = [
                        'routable' => $payment->id,
                        'particulars' => 'Advance Payment',
                        'voucher_type' => 'Payment',
                        'voucher_no' => $payment->voucher_no ?? $payment->id,
                        'amount' => $payment->bank_payment,
                        'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'advanced_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        if (isset($advance_cash_ledger_pos_payments) && !empty($advance_cash_ledger_pos_payments)) {
            foreach ($advance_cash_ledger_pos_payments as $payment) {
                if($payment->pos_payment != null && $payment->pos_payment > 0) {
                    $advance_payment_pos[] = [
                        'routable' => $payment->id,
                        'particulars' => 'Advance Payment',
                        'voucher_type' => 'Payment',
                        'voucher_no' => $payment->voucher_no ?? $payment->id,
                        'amount' => $payment->pos_payment,
                        'date' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'month' => Carbon::parse($payment->date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'advanced_payment',
                        'type' => 'showable'
                    ];
                }
            }
        }

        $combined_array = array_merge(
            $sale_array,
            $sale_pos_array,
            $purchase_array,
            $sale_order_array,
            $purchase_pos_array,
            $purchase_order_array,
            $gst_payment_array,
            $receipt_array,
            $payment_array,
            $sale_party_payment_array,
            $purchase_party_payment_array,
            $cash_withdrawn_array,
            $cash_deposited_array,
            $from_bank_transfers_array,
            $to_bank_transfers_array,
            $setoff_make_payment_array,
            $setoff_make_pos_payment_array,
            $advance_payment_bank,
            $advance_payment_pos
        );

        $this->array_sort_by_column($combined_array, 'date');

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

        $combined_array = $group;

        // return view('cash.bank_book', compact('opening_balance', 'opening_balance_date', 'sales', 'sales_pos', 'purchases', 'purchases_pos', 'payments', 'payments_pos', 'receipts', 'receipts_pos', 'sale_orders', 'purchase_orders', 'cash_withdrawn', 'cash_deposited', 'sale_party_payments', 'purchase_party_payments', 'bank'));

        // return $bank;
        
        return view('cash.bank_book', compact('opening_balance', 'closing_balance', 'combined_array', 'from_date', 'to_date', 'bank'));
    }

    private function fetch_opening_balance($bank, $from_date, $to_date) {

        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date);
        // subtract a minute as we want to search till that date
        // eg if to_date is 03-04-2020 subtract 1 min will become 02-04-2020 23:59:00
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();

        // commented cause opening balance will only be for 01-04
        // $opening_balance += $this->fetch_static_balance($from_date, $to_date);

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $sales_pos = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $purchases_pos = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        $payments_pos = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        $receipts_pos = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        // $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $from_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

        $to_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $sale_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $purchase_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $setoff_make_pos_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();


        foreach( $sales as $sale ){
            $opening_balance += $sale->bank_payment;
        }

        foreach( $sales_pos as $sale ){
            $opening_balance += $sale->pos_payment;
        }

        foreach( $purchases as $purchase ){
            $opening_balance -= $purchase->bank_payment;
        }

        foreach( $purchases_pos as $purchase ){
            $opening_balance -= $purchase->pos_payment;
        }

        foreach( $sale_orders as $order ){
            $opening_balance += $order->bank_amount;
            $opening_balance += $order->pos_amount;
        }

        foreach( $purchase_orders as $order ){
            $opening_balance -= $order->bank_amount;
            $opening_balance -= $order->pos_amount;
        }

        foreach( $receipts as $receipt ){
            $opening_balance += $receipt->bank_payment;
        }

        foreach( $receipts_pos as $receipt ){
            $opening_balance += $receipt->pos_payment;
        }

        foreach( $payments as $payment ){
            $opening_balance -= $payment->bank_payment;
        }

        foreach( $payments_pos as $payment ){
            $opening_balance -= $payment->pos_payment;
        }

        foreach( $sale_party_payments as $payment ){
            $opening_balance += $payment->bank_payment;
        }

        foreach ($sale_party_pos_payments as $payment) {
            $opening_balance += $payment->pos_payment;
        }

        foreach( $purchase_party_payments as $payment ){
            $opening_balance -= $payment->bank_payment;
        }

        foreach ($purchase_party_pos_payments as $payment) {
            $opening_balance -= $payment->pos_payment;
        }

        foreach( $cash_withdrawn as $cash ){
            $opening_balance -= $cash->amount;
        }

        foreach( $cash_deposited as $cash ){
            $opening_balance += $cash->amount;
        }

        foreach( $from_bank_transfers as $transfer ){
            $opening_balance -= $transfer->amount;
        }

        foreach( $to_bank_transfers as $transfer ){
            $opening_balance += $transfer->amount;
        }

        foreach ($setoff_make_payments as $payment) {
            $opening_balance -= $payment->bank_payment;
        }

        foreach ($setoff_make_pos_payments as $payment) {
            $opening_balance -= $payment->pos_payment;
        }

        foreach($advance_cash_ledger_bank_payments as $payment){
            $opening_balance -= $payment->bank_payment;
        }

        foreach($advance_cash_ledger_pos_payments as $payment){
            $opening_balance -= $payment->pos_payment;
        }

        return $opening_balance;
    }

    private function calculate_closing_balance($bank, $from_date, $to_date)
    {
        $from_date = \Carbon\Carbon::parse($from_date);
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();
        
        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }
        $closing_balance = 0;


        // $year = Carbon::parse($till_date)->format('Y');


        // $from_date = $year - 1 . "-04-01";
        // $to_date = $year . "-03-31";


        $bank_id = $bank->id;

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $sales_pos = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $purchases_pos = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        $payments_pos = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        $receipts_pos = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        // $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $from_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

        $to_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $sale_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $purchase_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $setoff_make_pos_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();


        foreach( $sales as $sale ){
            $closing_balance += $sale->bank_payment;
        }

        foreach( $sales_pos as $sale ){
            $closing_balance += $sale->pos_payment;
        }

        foreach( $purchases as $purchase ){
            $closing_balance -= $purchase->bank_payment;
        }

        foreach( $purchases_pos as $purchase ){
            $closing_balance -= $purchase->pos_payment;
        }

        foreach( $sale_orders as $order ){
            $closing_balance += $order->bank_amount;
            $closing_balance += $order->pos_amount;
        }

        foreach( $purchase_orders as $order ){
            $closing_balance -= $order->bank_amount;
            $closing_balance -= $order->pos_amount;
        }

        foreach( $receipts as $receipt ){
            $closing_balance += $receipt->bank_payment;
        }

        foreach( $receipts_pos as $receipt ){
            $closing_balance += $receipt->pos_payment;
        }

        foreach( $payments as $payment ){
            $closing_balance -= $payment->bank_payment;
        }

        foreach( $payments_pos as $payment ){
            $closing_balance -= $payment->pos_payment;
        }

        foreach( $sale_party_payments as $payment ){
            $closing_balance += $payment->bank_payment;
        }

        foreach ($sale_party_pos_payments as $payment) {
            $closing_balance += $payment->pos_payment;
        }

        foreach( $purchase_party_payments as $payment ){
            $closing_balance -= $payment->bank_payment;
        }

        foreach ($purchase_party_pos_payments as $payment) {
            $closing_balance -= $payment->pos_payment;
        }

        foreach( $cash_withdrawn as $cash ){
            $closing_balance -= $cash->amount;
        }

        foreach( $cash_deposited as $cash ){
            $closing_balance += $cash->amount;
        }

        foreach( $from_bank_transfers as $transfer ){
            $closing_balance -= $transfer->amount;
        }

        foreach( $to_bank_transfers as $transfer ){
            $closing_balance += $transfer->amount;
        }

        foreach ($setoff_make_payments as $payment) {
            $closing_balance -= $payment->bank_payment;
        }

        foreach ($setoff_make_pos_payments as $payment) {
            $closing_balance -= $payment->pos_payment;
        }

        foreach($advance_cash_ledger_bank_payments as $payment){
            $closing_balance -= $payment->bank_payment;
        }

        foreach($advance_cash_ledger_pos_payments as $payment){
            $closing_balance -= $payment->pos_payment;
        }

        return $closing_balance;

    }

    private function fetch_static_balance($bank, $from, $to)
    {
        $from_date = \Carbon\Carbon::parse($from);
        $to_date = \Carbon\Carbon::parse($to);

        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }
        // adding a day to make both from and to dates inclusive for searching
        $to_date = \Carbon\Carbon::parse($to)->addDay();

        $opening_balance = 0;

        // if (isset($bank) && !empty($bank)) {

        //     if ($bank->balance_type == 'creditor' || $bank->classification == 'current liability' || $bank->classfication == 'fixed liability') {
        //         $fixed_opening_balance = "-" . $bank->opening_balance;
        //     } else {
        //         $fixed_opening_balance = $bank->opening_balance;
        //     }

        //     if ($isDatesSame){
        //         if (\Carbon\Carbon::parse($bank->opening_balance_on_date)->eq($from_date)){
        //             $opening_balance = $fixed_opening_balance;
        //         }
        //     }
            
        //     else if (\Carbon\Carbon::parse($bank->opening_balance_on_date)->gte($from_date)  && \Carbon\Carbon::parse($bank->opening_balance_on_date)->lte($to_date)) {
        //         $opening_balance = $fixed_opening_balance;
        //     }

        // }

        // return $opening_balance;

        // returning static balance of bank
        if ($bank->balance_type == 'creditor' || $bank->classification == 'current liability' || $bank->classfication == 'fixed liability') {
            $bank->opening_balance = "-" . $bank->opening_balance;
        }
        return $bank->opening_balance ?? 0;
    }

    // private function fetch_static_balance($bank, $to, $from=null){

    //     $opening_balance = null;

    //     if (isset($bank) && !empty($bank)) {

    //         if ($bank->balance_type == 'creditor' || $bank->classification == 'current liability' || $bank->classfication == 'fixed liability') {
    //             $fixed_opening_balance = "-" . $bank->opening_balance;
    //         } else {
    //             $fixed_opening_balance = $bank->opening_balance;
    //         }

    //         if($from != null){
    //             if ($bank->opening_balance_on_date >= $from && $bank->opening_balance_on_date <= $to) {
    //                 $opening_balance = $fixed_opening_balance;
    //             }
    //         } else {
    //             $opening_balance = 0;
    //             if ($bank->opening_balance_on_date < $to) {
    //                 $opening_balance += $fixed_opening_balance;
    //             }
    //         }

    //     }

    //     return $opening_balance;
    // }

    private function calculate_opening_balance($bank, $opening_balance_from_date, $opening_balance_to_date)
    {
        // $bank = Bank::where('id', $bank_id)->where('user_id', auth()->user()->id)->take(1)->get();
        // $bank = User::find(auth()->user()->id)->banks()->where('id', $bank_id)->take(1)->get();
        $opening_balance = 0;

        $from_date = $opening_balance_from_date;
        $to_date = $opening_balance_to_date;

        // return $bank;

        $bank_id = $bank->id;

        $sales = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $sales_pos = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $purchases_pos = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        $payments_pos = User::find(Auth::user()->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        $receipts_pos = User::find(Auth::user()->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        // $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $from_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

        $to_bank_transfers = BankToBankTransfer::where('user_id', Auth::user()->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $sale_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $purchase_party_pos_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $setoff_make_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $setoff_make_pos_payments = GSTSetOff::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', Auth::user()->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        foreach ($sales as $sale) {
            $opening_balance += $sale->bank_payment;
        }

        foreach ($sales_pos as $sale) {
            $opening_balance += $sale->pos_payment;
        }

        foreach ($receipts as $receipt) {
            $opening_balance += $receipt->bank_payment;
        }

        foreach ($receipts_pos as $receipt) {
            $opening_balance += $receipt->pos_payment;
        }

        foreach ($sale_orders as $order) {
            $opening_balance += $order->bank_amount;
        }

        foreach ($purchases as $purchase) {
            $opening_balance -= $purchase->bank_payment;
        }

        foreach ($purchases_pos as $purchase) {
            $opening_balance -= $purchase->pos_payment;
        }

        foreach ($payments as $payment) {
            $opening_balance -= $payment->bank_payment;
        }

        foreach ($payments_pos as $payment) {
            $opening_balance -= $payment->pos_payment;
        }

        foreach ($purchase_orders as $order) {
            $opening_balance -= $order->bank_amount;
        }

        // foreach ($gst_payments as $payment) {
        //     $opening_balance -= $payment->cash_amount;
        // }


        foreach ($cash_withdrawn as $withdrawn) {
            $opening_balance -= $withdrawn->amount;
        }

        foreach ($cash_deposited as $deposited) {
            $opening_balance += $deposited->amount;
        }

        foreach( $from_bank_transfers as $transfer ){
            $closing_balance -= $transfer->amount;
        }

        foreach( $to_bank_transfers as $transfer ){
            $closing_balance += $transfer->amount;
        }

        foreach ($sale_party_payments as $sale) {
            $opening_balance += $sale->bank_payment;
        }

        foreach ($sale_party_pos_payments as $sale) {
            $opening_balance += $sale->pos_payment;
        }

        foreach ($purchase_party_payments as $payment) {
            $opening_balance -= $payment->bank_payment;
        }

        foreach ($purchase_party_pos_payments as $payment) {
            $opening_balance -= $payment->pos_payment;
        }

        foreach ($setoff_make_payments as $payment) {
            $opening_balance -= $payment->bank_payment;
        }

        foreach ($setoff_make_pos_payments as $payment) {
            $opening_balance -= $payment->pos_payment;
        }

        return $opening_balance;
    }

    public function get_import_to_table()
    {
        return view('bank.import_to_table');
    }

    public function post_import_to_table(Request $request)
    {

        $this->validate($request, [
            'bank_file' => 'required'
        ]);


        if ( $request->hasFile('bank_file') ) {

            $path = $request->file('bank_file')->getRealPath();

            $data = Excel::load($path)->get();

            if (!empty($data) && $data->count()) {
                foreach ($data->toArray() as $row) {
                    if (!empty($row)) {
                        $dataArray[] = [
                            'name' => $row['name'],
                            'account_no' => $row['account_no'],
                            'branch' => $row['branch'],
                            'ifsc' => $row['ifsc'],
                            'opening_balance' => $row['opening_balance'],
                            'opening_balance_on_date' => $row['opening_balance_on_date'],
                            'classification' => $row['classification'],
                            'type' => $row['type'],
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s', time()),
                            'updated_at' => date('Y-m-d H:i:s', time()),
                        ];
                    }
                }
                if (!empty($dataArray)) {
                    Bank::insert($dataArray);
                    return redirect()->back()->with('success', 'Data uploaded successfully');
                }
            }
        }

    }

    public function edit_form_opening_balance($id)
    {
        $bank = Bank::findOrFail($id);
        return view('bank.edit_opening_balance', compact('bank'));
    }

    public function update_opening_balance(Request $request, $id)
    {
        $this->validate($request, [
            'opening_balance' => 'required|numeric',
            'opening_balance_on_date' => 'required|date_format:d/m/Y',
            'balance_type' => 'required'
        ]);

        $bank = Bank::findOrFail($id);

        $bank->opening_balance = $request->opening_balance;
        $bank->opening_balance_on_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->opening_balance_on_date)) );
        $bank->balance_type = $request->balance_type;

        if( $bank->opening_balance_on_date != auth()->user()->profile->financial_year_from && $bank->opening_balance_on_date < auth()->user()->profile->financial_year_from ){
            return redirect()->back()->with('failure', 'Please provide valid opening balance date for current financial year');
        }

        if( $bank->opening_balance_on_date != auth()->user()->profile->financial_year_from && $bank->opening_balance_on_date > auth()->user()->profile->financial_year_to ){
            return redirect()->back()->with('failure', 'Please provide valid opening balance date for current financial year');
        }

        if($bank->save()){
            return redirect()->back()->with('success', 'Opening Balance updated successfully!!');
        } else {
            return redirect()->back()->with('failed', 'Failed to update bank balance!!');
        }
    }

    public function bank_all()
    {
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        return view('cash.all_banks', compact('banks'));
    }

    public function bank_to_bank_transfer()
    {
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

        return view('cash.bank_to_bank', compact('banks', 'contra'))->with('myerrors', $myerrors);
    }

    public function post_bank_to_bank_transfer(Request $request)
    {
        $this->validate($request, [
            'from_bank' => 'required',
            'to_bank' => 'required',
            'amount' => 'required',
            'date' => 'required',
            'voucher_no' => 'required'
        ]);

        $transfer = new BankToBankTransfer;

        $transfer->from_bank = $request->from_bank;
        $transfer->to_bank = $request->to_bank;
        $transfer->contra = $request->voucher_no;
        if( isset(auth()->user()->cashSetting) && isset(auth()->user()->cashSetting->bill_no_type) ){
            $transfer->voucher_no_type = auth()->user()->cashSetting->bill_no_type;
        } else {
            $transfer->voucher_no_type = 'manual';
        }
        $transfer->amount = $request->amount;
        $transfer->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $transfer->narration = $transfer->narration;
        $transfer->user_id = auth()->user()->id;

        if($transfer->save()){
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function edit_bank_to_bank_transfer($id)
    {
        $transfer = BankToBankTransfer::findOrFail($id);
        $banks = Bank::where('user_id', Auth::user()->id)->get();

        // return $transfer;

        return view('cash.edit_bank_to_bank', compact('transfer', 'banks'));
    }

    public function update_bank_to_bank_transfer(Request $request, $id)
    {
       $this->validate($request, [
            'from_bank' => 'required',
            'to_bank' => 'required',
            'amount' => 'required',
            'date' => 'required',
            'voucher_no' => 'required'
        ]);

        $transfer = BankToBankTransfer::findOrFail($id);

        $transfer->from_bank = $request->from_bank;
        $transfer->to_bank = $request->to_bank;
        $transfer->contra = $request->voucher_no;
        $transfer->amount = $request->amount;
        $transfer->date = date('Y-m-d', strtotime(str_replace('/', '-', $request->date)));
        $transfer->narration = $transfer->narration;

        if($transfer->save()){
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        } 
    }

    public function view_bank_to_bank_transfer()
    {
        $bank_amounts = Auth::user()->bankToBankTransfers()->get();
        foreach($bank_amounts as $amount) {
            $amount->to_banks = Bank::find($amount->to_bank)->name;
            $amount->from_banks = Bank::find($amount->from_bank)->name;
        }
        return view('cash.view_bank_to_bank_transfer', compact('bank_amounts'));
    }

    public function update_bank_to_bank_transfer_status(Request $request, $id)
    {
        $bank_to_bank = BankToBankTransfer::findOrFail($id);
        $bank_to_bank->status = $request->status;
        $bank_to_bank->save();
        return redirect()->back();
    }


    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }
    
}