<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\CashDeposit;
use App\CashWithdraw;
use App\Invoice;
use App\Invoice_Item;
use App\Item;
use App\PurchaseRecord;
use App\User;
use App\Party;
use App\Purchase;
use App\PurchaseRemainingAmount;
use App\SaleRemainingAmount;
use App\UserProfile;
use App\SaleOrder;
use App\PurchaseOrder;
use App\CashGST;
use App\GSTSetOff;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $invoices = User::findOrFail(Auth::user()->id)->invoices()->OrderBy('id', 'desc')->take(5)->get();
        // $bills = User::find(Auth::user()->id)->purchases()->OrderBy('id', 'desc')->take(5)->get();

        // $items = Item::OrderBy('id', 'desc')->where('user_id', Auth::user()->id)->take(5)->get();

        $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

        $invoices = User::find(Auth::user()->id)->invoices()->get();
        $sale_data = array(
            'total_amount_sale' => 0,
            'total_sale' => count($invoices),
            'total_sale_tax' => 0,
            'total_igst' => 0,
            'total_sgst' => 0,
            'total_cgst' => 0
        );

        foreach( $invoices as $invoice ){
            $sale_data['total_amount_sale'] += $invoice->total_amount;
            $sale_data['total_sale_tax'] += $invoice->gst;
            $sale_data['total_igst'] += $invoice->igst;
            $sale_data['total_sgst'] += $invoice->sgst;
            $sale_data['total_cgst'] += $invoice->cgst;
        }

        $bills = User::find(Auth::user()->id)->purchases()->get();
        $purchase_data = array(
            'total_amount_purchase' => 0,
            'total_purchase' => count($bills),
            'total_purchase_tax' => 0,
            'total_igst' => 0,
            'total_sgst' => 0,
            'total_cgst' => 0
        );

        foreach( $bills as $bill ){
            $purchase_data['total_amount_purchase'] += $bill->item_total_amount;
            $purchase_data['total_purchase_tax'] += $bill->item_total_gst;
            $purchase_data['total_igst'] += $bill->igst;
            $purchase_data['total_sgst'] += $bill->sgst;
            $purchase_data['total_cgst'] += $bill->cgst;
        }


        $parties = Party::where('user_id', Auth::user()->id)->get();
        
        $receipt = array(
            'amount_paid' => 0
        );

        $payment = array(
            'amount_paid' => 0
        );

        foreach( $parties as $party ) {

            $sale_remaining_amounts = SaleRemainingAmount::where('party_id', $party->id)->get();

            foreach( $sale_remaining_amounts as $amount ){
                $receipt['amount_paid'] += $amount->amount_paid;
            }

            $purchase_remaining_amounts = PurchaseRemainingAmount::where('party_id', $party->id)->get();

            foreach ($purchase_remaining_amounts as $amount) {
                $payment['amount_paid'] += $amount->amount_paid;
            }
        
        }

        $latest_debtors = Party::where('user_id', Auth::user()->id)->where('balance_type', 'debitor')->orderBy('id', 'desc')->take(5)->get();

        $latest_creditors = Party::where('user_id', Auth::user()->id)->where('balance_type', 'creditor')->orderBy('id', 'desc')->take(5)->get();

        $latest_invoices = User::findOrFail(Auth::user()->id)->invoices()->orderBy('id', 'desc')->take(5)->get();
        $latest_bills = User::find(Auth::user()->id)->purchases()->orderBy('id', 'desc')->take(5)->get();

        $most_active_items = DB::table('invoice_item')
            ->select(DB::raw('item_id'), DB::raw('sum(item_qty) as qty'))
            ->groupBy(DB::raw('item_id'))
            ->orderBy('qty', 'desc')
            ->take(5)
            ->get();

            
        foreach($most_active_items as $item){
            $current_item = Item::findOrFail($item->item_id);
            
            $item->name = $current_item->name;
        }

        $from = $user_profile->financial_year_from ?? null;
        $to = $user_profile->financial_year_to ?? null;

        $bank_balance = 0;
        $cash_balance = 0;
        $cash_deposit = 0;
        $cash_withdrawn = 0;

        $cd = CashDeposit::where('user_id', Auth::user()->id);
        if($from && $to){
            $cd = $cd->whereBetween('date', [$from, $to]);
        }
        $cash_deposits = $cd->get();

        foreach($cash_deposits as $deposit){
            $cash_deposit += $deposit->amount;
            $cash_balance -= $deposit->amount;
            $bank_balance += $deposit->amount;
        }

        $cw = CashWithdraw::where('user_id', Auth::user()->id);
        if($from && $to){
            $cw = $cw->whereBetween('date', [$from, $to]);
        }
        $cash_withdraws = $cw->get();

        foreach($cash_withdraws as $withdraw){
            $cash_withdrawn += $withdraw->amount;
            $cash_balance += $withdraw->amount;
            $bank_balance -= $withdraw->amount;
        }

        $userAllInvoices = User::findOrFail(Auth::user()->id)->invoices()->get();
        $userAllPurchases = User::findOrFail(Auth::user()->id)->purchases()->get();
        $userAllPurchaseRemainingAmounts = User::find(Auth::user()->id)->purchaseRemainingAmounts()->get();
        $userAllSaleRemainingAmounts = User::find(Auth::user()->id)->saleRemainingAmounts()->get();
        $userAllSaleOrder = SaleOrder::where('user_id', Auth::user()->id)->get();
        $userAllPurchaseOrder = PurchaseOrder::where('user_id', Auth::user()->id)->get();
        $userAllCashGST = CashGST::where('user_id', Auth::user()->id)->get();
        $userAllSalePartyRemainingAmount = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->get();
        $userAllPurchasePartyRemainingAmount = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->get();
        $userAllGSTSetoff = GSTSetOff::where('user_id', Auth::user()->id)->get();

        foreach($userAllInvoices as $invoice) {
            $bank_balance += $invoice->bank_payment;
            $cash_balance += $invoice->cash_payment;
        }

        foreach($userAllPurchases as $purchase) {
            $bank_balance -= $purchase->bank_payment;
            $cash_balance -= $purchase->cash_payment;
        }

        foreach($userAllPurchaseRemainingAmounts as $amount){
            $bank_balance -= $amount->bank_payment;
            $cash_balance -= $amount->cash_payment;
        }

        foreach($userAllSaleRemainingAmounts as $amount) {
            $bank_balance += $amount->bank_payment;
            $cash_balance += $amount->cash_payment;
        }

        foreach($userAllSaleOrder as $order){
            $bank_balance += $order->bank_amount;
            $cash_balance += $order->cash_amount;
        }

        foreach($userAllPurchaseOrder as $order){
            $bank_balance -= $order->bank_amount;
            $cash_balance -= $order->cash_amount;
        }

        foreach($userAllCashGST as $gst){
            $bank_balance -= $gst->bank_amount;
            $cash_balance -= $gst->cash_amount;
        }

        foreach($userAllSalePartyRemainingAmount as $amount){
            $bank_balance += $amount->bank_payment;;
            $cash_balance += $amount->cash_payment;;
        }

        foreach($userAllPurchasePartyRemainingAmount as $amount){
            $bank_balance -= $amount->bank_payment;;
            $cash_balance -= $amount->cash_payment;;
        }

        foreach($userAllGSTSetoff as $gst){
            $bank_balance -= $gst->bank_payment;
            $cash_balance -= $gst->cash_payment;
        }
        

        $itemsWithNegQty = Item::where('user_id', Auth::user()->id)->where('qty', '<', 0)->get();


        $sale_stock['jan'] = $this->sale_stock('01', Carbon::parse($user_profile->financial_year_to)->format('Y'));
        $sale_stock['feb'] = $this->sale_stock('02', Carbon::parse($user_profile->financial_year_to)->format('Y'));
        $sale_stock['mar'] = $this->sale_stock('03', Carbon::parse($user_profile->financial_year_to)->format('Y'));
        $sale_stock['apr'] = $this->sale_stock('04', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['may'] = $this->sale_stock('05', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['jun'] = $this->sale_stock('06', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['jul'] = $this->sale_stock('07', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['aug'] = $this->sale_stock('08', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['sep'] = $this->sale_stock('09', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['oct'] = $this->sale_stock('10', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['nov'] = $this->sale_stock('11', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $sale_stock['dec'] = $this->sale_stock('12', Carbon::parse($user_profile->financial_year_from)->format('Y'));

        $purchase_stock['jan'] = $this->purchase_stock('01', Carbon::parse($user_profile->financial_year_to)->format('Y'));
        $purchase_stock['feb'] = $this->purchase_stock('02', Carbon::parse($user_profile->financial_year_to)->format('Y'));
        $purchase_stock['mar'] = $this->purchase_stock('03', Carbon::parse($user_profile->financial_year_to)->format('Y'));
        $purchase_stock['apr'] = $this->purchase_stock('04', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['may'] = $this->purchase_stock('05', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['jun'] = $this->purchase_stock('06', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['jul'] = $this->purchase_stock('07', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['aug'] = $this->purchase_stock('08', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['sep'] = $this->purchase_stock('09', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['oct'] = $this->purchase_stock('10', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['nov'] = $this->purchase_stock('11', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        $purchase_stock['dec'] = $this->purchase_stock('12', Carbon::parse($user_profile->financial_year_from)->format('Y'));
    
        // return $most_active_items;
        // return $this->purchase_stock('05', Carbon::parse($user_profile->financial_year_from)->format('Y'));
        // return $purchase_stock['may'];

        return view('dashboard.index', compact('sale_data', 'purchase_data', 'receipt', 'payment', 'user_profile', 'latest_debtors', 'latest_creditors', 'latest_invoices', 'latest_bills', 'most_active_items', 'cash_deposit', 'cash_withdrawn', 'bank_balance', 'cash_balance', 'itemsWithNegQty', 'purchase_stock', 'sale_stock'));

    }

    private function sale_stock($month, $year)
    {

        $invoices = User::findOrFail(Auth::user()->id)->invoices()->whereMonth('invoice_date', $month)->whereYear('invoice_date', $year)->get();
        $qty = 0;

        foreach($invoices as $invoice){
            $qty += Invoice_Item::where('invoice_id', $invoice->id)->sum('item_qty');
        }

        return $qty;
    }

    private function purchase_stock($month, $year)
    {
        // return $year;

        $purchases = User::find(Auth::user()->id)->purchases()->whereMonth('bill_date', $month)->whereYear('bill_date', $year)->get();
        $qty = 0;

        foreach($purchases as $purchase){
            $qty += Purchase::where('purchase_id', $purchase->id)->sum('qty');
        }

        return $qty;
    }
}
