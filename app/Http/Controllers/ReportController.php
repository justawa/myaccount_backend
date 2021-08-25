<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Bank;
use App\Item;
use App\Invoice;
use App\Invoice_Item;
use App\Party;
use App\Purchase;
use App\PurchaseRecord;
use App\User;
use App\CreditNote;
use App\State;
use App\CashDeposit;
use App\CashWithdraw;
use App\CashInHand;
use App\PurchaseRemainingAmount;
use App\SaleRemainingAmount;
use App\PartyPendingPaymentAccount;
use App\DebitNote;
use App\PurchaseOrder;
use App\SaleOrder;

class ReportController extends Controller
{
    public function hsn_sale(Request $request) {
        
        $items = Item::where('user_id', Auth::user()->id)->get();

        foreach( $items as $item )
        {
            $invoice_items = Invoice_Item::where('item_id', $item->id)->get();
            
            $total_gst = 0;
            $total_qty = 0;
            $total_value = 0;
            $taxable_value = 0;
            $integrated_tax_value = 0;
            $central_tax_value = 0;
            $state_tax_value = 0;
            $cess_amount = 0;
            foreach($invoice_items as $per_item) {
                $total_gst += $per_item->gst;
                $total_qty += $per_item->item_qty;
                $total_value += $per_item->item_price;
                $taxable_value += $per_item->item_price * $per_item->item_qty;
                $integrated_tax_value += $per_item->igst;
                $central_tax_value += $per_item->cgst;
                $state_tax_value += $per_item->sgst;
                $cess_amount += $per_item->gst;
            }

            $item->gst_per_item = $total_gst;
            $item->total_qty_per_item = $total_qty;
            $item->total_value_per_item = $total_qty;
            $item->taxable_value_per_item = $taxable_value;
            $item->integrated_tax_value_per_item = $integrated_tax_value;
            $item->central_tax_value_per_item = $central_tax_value;
            $item->state_tax_value_per_item = $state_tax_value;
            $item->cess_amount_per_item = $cess_amount;
        }

        // dd($items);

        // return $items;

        if( isset($request->export_to_excel) && $request->export_to_excel == "yes" ) {
            $hsnArray = [];

            // Define the Excel spreadsheet headers
            $hsnArray[] = ['HSN', 'Description', 'UQC', 'Total Quantity', 'Total Value', 'Taxable Value', 'Integrated Tax Amount', 'Central Tax Amount', 'State/UT Tax Amount', 'CESS Amount', 'GST'] ;

            foreach( $items as $item )
            {

                $hsnArray[] = [ $item->hsc_code, $item->name, $item->measuring_unit, $item->total_qty_per_item, $item->total_value_per_item, $item->taxable_value_per_item, $item->integrated_tax_value_per_item, $item->central_tax_value_per_item, $item->state_tax_value_per_item, $item->cess_amount_per_item, $item->gst_per_item ];
            }

            Excel::create('HSN', function($excel) use ( $hsnArray ) {

                // Set the spreadsheet title, creator, and description
                $excel->setTitle('HSN Report');
                $excel->setCreator('Admin')->setCompany('admin@test.com');
                $excel->setDescription('HSN ReportSheet');

                // Build the spreadsheet, passing in the hsn array
                $excel->sheet('sheet1', function($sheet) use ( $hsnArray) {
                    $sheet->fromArray( $hsnArray,  null, 'A1', false, false);
                });
            })->download('xlsx');
        } else {
            return view('report.hsn_sale', compact('items'));
        }

    }

    public function hsn_purchase(Request $request) {
        
        $items = Item::where('user_id', Auth::user()->id)->get(['id', 'name', 'hsc_code']);

        foreach( $items as $item )
        {
            $invoice_items = Purchase::where('item_id', $item->id)->get(['gst']);
            $total_gst = 0;
            foreach($invoice_items as $per_item) {
                $total_gst += $per_item->gst;
            }

            $item->gst_per_item = $total_gst;
        }

        // dd($items);

        // return $items;

        return view('report.hsn_purchase', compact('items'));

    }

    public function tax_on_purchase() {
        $purchases = User::find(Auth::user()->id)->purchases()->where('type_of_bill', 'regular')->get();

        return view('report.tax_paid', compact('purchases'));
    }

    public function tax_on_sale() {
        $invoices = User::findOrFail(Auth::user()->id)->invoices()->where('type_of_bill', 'regular')->get();

        return view('report.tax_collected', compact('invoices'));
    }

    public function creditor_report( Request $request ) {

        if( isset( $request->query_by ) && isset( $request->q ) ){

            if( $request->query_by == 'name' ) {
                $parties = Party::where('user_id', Auth::user()->id)->where('name', $request->q)->where('balance_type', 'creditor')->get();
            }
            //same reason as of invoice
            // } else if( $request->query_by == 'bill' ) {
            //     $bill = PurchaseRemainingAmount::where('bill_no', $request->q)->orderBy('created_at', 'desc')->first();

            //     if( $bill ) {
            //         $parties = Party::where('id', $bill->party_id)->get();

            //         foreach( $parties as $party ) {
            //             $party->total_amount = $bill->amount_remaining;

            //             $party->total_amount += $party->opening_balance;

            //             $party->total_amount = $party->total_amount;
            //         }
            //     }
            // }

        } else {
            $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'creditor')->get();
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            // $from_date = date('Y') . '-04-01';
            // $to_date = date('Y-m-d', time());

            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $opening_balance_from_date = auth()->user()->profile->financial_year_from;
        $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

        $closing_balance_from_date = auth()->user()->profile->book_beginning_from;
        // $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        if( \Carbon\Carbon::parse($from_date)->lt(\Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)) ){
            $closing_balance_to_date = \Carbon\Carbon::parse($from_date);
        } else {
            $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        }

        //if( $request->query_by != 'bill' ) {

            foreach ($parties as $party) {

                // $party->opening_balance = $this->fetch_creditor_static_balance($party, $opening_balance_from_date, $opening_balance_to_date); 
                $party->opening_balance = $party->opening_balance;

                if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
                    $this_balance = $this->fetch_creditor_opening_balance($party, $opening_balance_from_date, $opening_balance_to_date);
                    // return $this_balance;
                    $party->opening_balance += $this_balance;
                }

                // closing balance will only change if there is no opening balance
                // if($party->opening_balance == 0){
                    $party->opening_balance += $this->calculate_creditor_closing_balance($party, $closing_balance_from_date, $closing_balance_to_date);
                // }

                // $party->opening_balance = $this->calculate_creditor_opening_balance($party, $from_date);

                $combined_array = array();

                $purchase_array = array();
                $discount_array = array();
                $debit_note_array = array();
                $credit_note_array = array();
                $payment_array = array();
                $party_payment_array = array();
                $purchase_order_array = array();
                
                $cash_array = array();
                $bank_array = array();
                $pos_array = array();

                $query1 = PurchaseRecord::where('party_id', $party->id)->where('type_of_bill', 'regular');

                $query1 = $query1->whereBetween('bill_date',[$from_date, $to_date]);

                $purchases = $query1->get();

                $total = 0;

                // return $purchases;

                foreach( $purchases as $purchase ){
                    // $total += $purchase->total_amount;

                    if($purchase->total_amount > 0) {
                        $purchase_array[] = [
                            'routable' => $purchase->id,
                            'particulars' => 'Purchase',
                            'voucher_type' => 'Purchase',
                            'voucher_no' => $purchase->bill_no,
                            'amount' => $purchase->total_amount,
                            'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                            'month' => Carbon::parse($purchase->bill_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'purchase',
                            'type' => 'showable'
                        ];
                    }

                    // if($purchase->total_discount > 0){
                    //     $discount_array[] = [
                    //         'routable' => $purchase->id,
                    //         'particulars' => 'Discount',
                    //         'voucher_type' => 'Purchase',
                    //         'voucher_no' => $purchase->bill_no,
                    //         'amount' => $purchase->total_discount,
                    //         'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                    //         'month' => Carbon::parse($purchase->bill_date)->format('m'),
                    //         'transaction_type' => 'debit',
                    //         'loop' => 'purchase',
                    //         'type' => 'showable'
                    //     ];
                    // }

                    // if ($purchase->type_of_payment == 'no_payment') {
                    //     continue;
                    // } 
                    if ($purchase->type_of_payment == 'combined') {
                        $this_bank = Bank::find($purchase->bank_id);
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    else if($purchase->type_of_payment == 'cash+bank+pos') {
                        $this_bank = Bank::find($purchase->bank_id);
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if($purchase->type_of_payment == 'cash+bank+discount') {
                        $this_bank = Bank::find($purchase->bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if($purchase->type_of_payment == 'cash+pos+discount') {
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }
                    }

                    else if($purchase->type_of_payment == 'bank+pos+discount') {
                        $this_bank = Bank::find($purchase->bank_id);
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }
                    }

                    else if ($purchase->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($purchase->bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    }
                    
                    else if ($purchase->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    }

                    else if ($purchase->type_of_payment == 'cash+discount') {
                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }
                    }

                    else if ($purchase->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($purchase->bank_id);
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    }

                    else if ($purchase->type_of_payment == 'bank+discount') {
                        $this_bank = Bank::find($purchase->bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    }

                    else if ($purchase->type_of_payment == 'discount') {

                        if($purchase->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Purchase (Cash Discount Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->discount_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    
                    else if ($purchase->type_of_payment == 'bank') {
                        $this_bank = Bank::find($purchase->bank_id);

                        if($purchase->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Purchase (Bank Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->bank_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    
                    else if ($purchase->type_of_payment == 'pos') {
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if($purchase->post_payment > 0){
                            $pos_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Purchase (POS Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->pos_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    else {

                        if($purchase->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $purchase->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Purchase (Cash Payment)',
                                'voucher_no' => $purchase->bill_no,
                                'amount' => $purchase->cash_payment,
                                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                                'month' => Carbon::parse($purchase->bill_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase',
                                'type' => 'showable'
                            ];
                        }

                    }
                    
                    // // fetching debit notes for this bill
                    // $debitNotes = DebitNote::where('bill_no', $purchase->id)
                    // ->where('status', 1)->where('type', 'purchase')
                    // ->where(function ($query) {
                    //     $query->where('reason', 'purchase_return')->orWhere('reason', 'new_rate_or_discount_value_with_gst')->orWhere('reason', 'discount_on_purchase');
                    // })
                    // ->groupBy('note_no')
                    // ->get();

                    // // ->whereIn('reason', ['purchase_return', 'new_rate_or_discount_value_with_gst', 'discount_on_purchase'])->get();
                    // //->whereBetween('created_at', [$from_date, $to_date])

                    // foreach( $debitNotes as $debitNote){
                    //     // if($debitNote->reason == 'purchase_return' || $debitNote->reason == 'new_rate_or_discount_value_with_gst' || $debitNote->reason == 'discount_on_purchase'){
                    //         if($debitNote->note_value > 0){
                    //             $debit_note_array[] = [
                    //                 'routable' => $debitNote->note_no ?? 0,
                    //                 'particulars' => 'Debit Note',
                    //                 'voucher_type' => 'Note',
                    //                 'voucher_no' => $debitNote->note_no,
                    //                 'amount' => $debitNote->note_value,
                    //                 'date' => Carbon::parse( $debitNote->note_date)->format('Y-m-d'),
                    //                 'month' => Carbon::parse( $debitNote->note_date)->format('m'),
                    //                 'transaction_type' => 'debit',
                    //                 'loop' => 'purchase_debit_note',
                    //                 'type' => 'showable'
                    //             ];
                    //         }
                    //     // }
                    // }


                    // //fetching credit notes for this invoice
                    // $creditNotes = CreditNote::where('invoice_id', $purchase->id)->where('status', 1)->where('type', 'purchase')->where('reason', 'new_rate_or_discount_value_with_gst')
                    // ->groupBy('note_no')
                    // ->get();
                    // //->whereBetween('created_at', [$from_date, $to_date])

                    // foreach( $creditNotes as $creditNote){
                    //     // if($creditNote->reason == 'new_rate_or_discount_value_with_gst'){
                    //         $credit_note_array[] = [
                    //             'routable' => $creditNote->note_no ?? 0,
                    //             'particulars' => 'Credit Note',
                    //             'voucher_type' => 'Note',
                    //             'voucher_no' => $creditNote->note_no,
                    //             'amount' => $creditNote->note_value,
                    //             'date' => Carbon::parse($creditNote->note_date)->format('Y-m-d'),
                    //             'month' => Carbon::parse($creditNote->note_date)->format('m'),
                    //             'transaction_type' => 'credit',
                    //             'loop' => 'purchase_credit_note',
                    //             'type' => 'showable'
                    //         ];
                    //     // }
                    // }

                // return $creditNotes;
                }

                $query2 = PurchaseRemainingAmount::where('party_id', $party->id)->where('is_original_payment', 0)->where('status', 1);

                $query2 = $query2->whereBetween('payment_date',[$from_date, $to_date]);

                $paid_amounts = $query2->get();

                foreach ($paid_amounts as $amount) {
                    // $total -= $amount->amount_paid;
                    
                    if ($amount->type_of_payment == 'no_payment'){ 
                        continue;
                    } 
                    // else if($amount->type_of_payment == 'combined'){
                    //     $this_bank = Bank::find($amount->bank_id);
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    // } else if($amount->type_of_payment == 'pos+bank'){
                    //     $this_bank = Bank::find($amount->bank_id);
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_bank->name . '+' . $this_pos->name;
                    // } else if ($amount->type_of_payment == 'pos+cash') {
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_pos->name . '+' . 'Cash';
                    // } else if ($amount->type_of_payment == 'bank+cash') {
                    //     $this_bank = Bank::find($amount->bank_id);
                        

                    //     $particulars = $this_bank->name . '+' . 'Cash';
                    // } else if ($amount->type_of_payment == 'bank') {
                    //     $this_bank = Bank::find($amount->bank_id);

                    //     $particulars = $this_bank->name;
                    // } else if ($amount->type_of_payment == 'pos') {
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_pos->name;
                    // } else {

                    //     $particulars = 'Cash';
                    // }

                    else if($amount->type_of_payment == 'combined'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+bank+pos'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash';

                        // $particulars = $amount->type_of_payment;
                    } 
                    else if($amount->type_of_payment == 'cash+bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'bank+pos+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'cash+discount') {
                        $particulars = 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+bank'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)';
                    }
                    else if($amount->type_of_payment == 'bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)';
                    }
                    else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)';
                    }
                    else if ($amount->type_of_payment == 'discount') {
                        $particulars = 'Discount';
                    } 
                    else {
                        $particulars = 'Cash';
                    }

                    if($amount->amount_paid > 0){
                        $payment_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Payment',
                            'voucher_no' => $amount->voucher_no,
                            'amount' => $amount->amount_paid,
                            'date' => Carbon::parse($amount->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->created_at)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'payment',
                            'type' => 'showable'
                        ];
                    }
                }

                $query3 = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'purchase')->where('status', 1);

                $query3 = $query3->whereBetween('payment_date',[$from_date, $to_date]);

                $party_paid_amounts = $query3->get();

                foreach ($party_paid_amounts as $amount) {
                    // $total -= $amount->amount;

                    if ($amount->type_of_payment == 'no_payment'){ 
                        continue;
                    }
                    
                    // else if($amount->type_of_payment == 'combined'){
                    //     $this_bank = Bank::find($amount->bank_id);
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    // } else if($amount->type_of_payment == 'pos+bank'){
                    //     $this_bank = Bank::find($amount->bank_id);
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_bank->name . '+' . $this_pos->name;
                    // } else if ($amount->type_of_payment == 'pos+cash') {
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_pos->name . '+' . 'Cash';
                    // } else if ($amount->type_of_payment == 'bank+cash') {
                    //     $this_bank = Bank::find($amount->bank_id);
                        

                    //     $particulars = $this_bank->name . '+' . 'Cash';
                    // } else if ($amount->type_of_payment == 'bank') {
                    //     $this_bank = Bank::find($amount->bank_id);
                        

                    //     $particulars = $this_bank->name;
                    // } else if ($amount->type_of_payment == 'pos') {
                    //     $this_pos = Bank::find($amount->pos_bank_id);

                    //     $particulars = $this_pos->name;
                    // } else {

                    //     $particulars = 'Cash';
                    // }

                    else if($amount->type_of_payment == 'combined'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+bank+pos'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash';

                        // $particulars = $amount->type_of_payment;
                    } 
                    else if($amount->type_of_payment == 'cash+bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'bank+pos+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'cash+discount') {
                        $particulars = 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+bank'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)';
                    }
                    else if($amount->type_of_payment == 'bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)';
                    }
                    else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)';
                    }
                    else if ($amount->type_of_payment == 'discount') {
                        $particulars = 'Discount';
                    } 
                    else {
                        $particulars = 'Cash';
                    }

                    if($amount->amount > 0){
                        $party_payment_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Payment',
                            'voucher_no' => $amount->voucher_no,
                            'amount' => $amount->amount,
                            'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->payment_date)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'purchase_party_payment',
                            'type' => 'showable'
                        ];
                    }
                }

                $query4 = PurchaseOrder::where('party_id', $party->id)->where('status', 1);

                $query4 = $query4->whereBetween('date', [$from_date, $to_date]);

                $purchase_orders = $query4->get();

                foreach ($purchase_orders as $order) {

                    if($order->amount_received > 0){
                        $purchase_order_array[$order->token] = [
                            'routable' => $order->token,
                            'particulars' => 'Purchase Order',
                            'voucher_type' => 'Purchase Order',
                            'voucher_no' => $order->token,
                            'amount' => $order->amount_received,
                            'date' => Carbon::parse($order->date)->format('Y-m-d'),
                            'month' => Carbon::parse($order->date)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'purchase_order',
                            'type' => 'showable'
                        ];
                    }
                }


                $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'purchase')
                ->where(function ($query) {
                    $query->where('debit_notes.reason', 'purchase_return')->orWhere('debit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('debit_notes.reason', 'discount_on_purchase');
                })
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();

                foreach( $debitNotes as $debitNote){
                    // if($debitNote->reason == 'purchase_return' || $debitNote->reason == 'new_rate_or_discount_value_with_gst' || $debitNote->reason == 'discount_on_purchase'){
                        if($debitNote->note_value > 0){
                            $debit_note_array[] = [
                                'routable' => $debitNote->note_no ?? 0,
                                'particulars' => 'Debit Note',
                                'voucher_type' => 'Note',
                                'voucher_no' => $debitNote->note_no,
                                'amount' => $debitNote->note_value,
                                'date' => Carbon::parse( $debitNote->note_date)->format('Y-m-d'),
                                'month' => Carbon::parse( $debitNote->note_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase_debit_note',
                                'type' => 'showable'
                            ];
                        }
                    // }
                }


                $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'purchase')->where('credit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();

                foreach( $creditNotes as $creditNote){
                    // if($creditNote->reason == 'new_rate_or_discount_value_with_gst'){
                        $credit_note_array[] = [
                            'routable' => $creditNote->note_no ?? 0,
                            'particulars' => 'Credit Note',
                            'voucher_type' => 'Note',
                            'voucher_no' => $creditNote->note_no,
                            'amount' => $creditNote->note_value,
                            'date' => Carbon::parse($creditNote->note_date)->format('Y-m-d'),
                            'month' => Carbon::parse($creditNote->note_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'purchase_credit_note',
                            'type' => 'showable'
                        ];
                    // }
                }

                // $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

                // foreach( $debitNotes as $debitNote){
                //     if($debitNote->reason == 'purchase_return' || $debitNote->reason == 'new_rate_or_discount_value_with_gst' || $debitNote->reason == 'discount_on_sale'){
                //         if($debitNote->note_value > 0){
                //             $debit_note_array[] = [
                //                 'routable' => $debitNote->note_no ?? 0,
                //                 'particulars' => 'Debit Note',
                //                 'voucher_type' => 'Note',
                //                 'voucher_no' => $debitNote->note_no,
                //                 'amount' => $debitNote->note_value,
                //                 'date' => Carbon::parse( $debitNote->created_at)->format('Y-m-d'),
                //                 'month' => Carbon::parse( $debitNote->created_at)->format('m'),
                //                 'transaction_type' => 'debit',
                //                 'loop' => 'purchase_debit_note',
                //                 'type' => 'showable'
                //             ];
                //         }
                //     }
                // }


                // $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

                // foreach( $creditNotes as $creditNote){
                //     if($creditNote->reason == 'new_rate_or_discount_value_with_gst'){
                //         if($creditNote->note_value > 0){
                //             $credit_note_array[] = [
                //                 'routable' => $creditNote->note_no ?? 0,
                //                 'particulars' => 'Credit Note',
                //                 'voucher_type' => 'Note',
                //                 'voucher_no' => $creditNote->note_no,
                //                 'amount' => $creditNote->note_value,
                //                 'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                //                 'month' => Carbon::parse($creditNote->created_at)->format('m'),
                //                 'transaction_type' => 'credit',
                //                 'loop' => 'purchase_credit_note',
                //                 'type' => 'showable'
                //             ];
                //         }
                //     }
                // }

                $combined_array = array_merge(
                    $purchase_array,
                    $cash_array,
                    $bank_array,
                    $pos_array,
                    $discount_array,
                    $debit_note_array,
                    $credit_note_array,
                    $payment_array,
                    $party_payment_array,
                    $purchase_order_array
                );

                $this->array_sort_by_column($combined_array, 'date');

                // $total += $party->opening_balance;
                // $party->total_amount = $total;

                if( count($combined_array) > 0 ) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {
                        

                        // print_r($data);
                        // echo "<br/>break";

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $creditTotal += $party->opening_balance;
                    $combined_array['credit_total'] = $creditTotal;
                    $combined_array['debit_total'] = $debitTotal;
                    $combined_array['closing_total'] = $debitTotal - $creditTotal;
                } else {
                    $combined_array['credit_total'] = $party->opening_balance;
                    $combined_array['debit_total'] = 0;
                    $combined_array['closing_total'] = $party->opening_balance;
                }

                // return $sales;

                // $total += $party->opening_balance;
                $party->combined_array = $combined_array;


                // if( count($combined_array) > 0 ) {
                //     $creditTotal = 0;
                //     $debitTotal = 0;
                //     foreach ($combined_array as $data) {
                        

                //         // print_r($data);
                //         // echo "<br/>break";

                //         if ($data['transaction_type'] == 'credit') {
                //             $creditTotal += $data['amount'];
                //         } elseif ($data['transaction_type'] == 'debit') {
                //             $debitTotal += $data['amount'];
                //         }
                //     }
                //     $creditTotal += $party->opening_balance;
                //     $combined_array['credit_total'] = $creditTotal;
                //     $combined_array['debit_total'] = $debitTotal;
                //     $combined_array['closing_total'] = $creditTotal - $debitTotal;
                // } else {
                //     $combined_array['credit_total'] = 0;
                //     $combined_array['debit_total'] = $party->opening_balance;
                //     $combined_array['closing_total'] = $party->opening_balance;
                // }

                // // return $sales;

                // // $total += $party->opening_balance;
                // $party->combined_array = $combined_array;
            }
        //}

        // return $parties;

        // return $purchases;

        return view('report.creditor', compact('parties', 'from_date', 'to_date'));
    }

    private function fetch_creditor_static_balance($party, $from, $to)
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

        $q = Party::where('id', $party->id);

        // if ($isDatesSame) {
        //     $foundParty = $q->where('opening_balance_as_on', $from_date)
        //         ->orderBy('id', 'desc')
        //         ->first();
            
        //     if ($foundParty) {
        //         $opening_balance = $foundParty->opening_balance;
        //     }
        // } else {
            $foundParty = $q->whereBetween('opening_balance_as_on', [$from_date, $to_date])->orderBy('id', 'desc')->first();

            if ($foundParty) {
                $opening_balance = $foundParty->opening_balance;
            }
        // }

        return $opening_balance;
    }

    private function fetch_creditor_opening_balance($party, $from_date, $to_date)
    {
        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date)->format('Y-m-d');
        
        $to_date = \Carbon\Carbon::parse($to_date)->format('Y-m-d');

        // commented cause opening balance will only be for 01-04
        // $opening_balance += $this->fetch_static_balance($from_date, $to_date);

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

        $purchase_orders = PurchaseOrder::where('party_id', $party->id) 
            ->where('status', 1)
            ->whereBetween('date', [$from_date, $to_date])
            ->get();

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
            $opening_balance += $purchase->total_amount;

            $opening_balance -= $purchase->bank_payment ?? 0;
            $opening_balance -= $purchase->pos_payment ?? 0;
            $opening_balance -= $purchase->cash_payment ?? 0;
            $opening_balance -= $purchase->discount_payment ?? 0;
        }

        foreach ($paid_amounts as $amount) {
            $opening_balance -= $amount->amount_paid;  
        }

        foreach ($party_paid_amounts as $amount) {
            $opening_balance -= $amount->amount;
        }

        foreach ($purchase_orders as $order) {
            $opening_balance += $order->amount_received;
        }

        foreach( $debitNotes as $debitNote ){
            $opening_balance -= $debitNote->note_value;
        }

        foreach ($creditNotes as $creditNote) {
            $opening_balance += $creditNote->note_value;
        }

        return $opening_balance;
    }

    private function calculate_creditor_closing_balance ($party, $from_date, $to_date)
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

    // private function calculate_creditor_opening_balance($party, $till_date)
    // {
    //     if($party->opening_balance != null){
    //         $opening_balance = $party->opening_balance;
    //     } else {
    //         $opening_balance = 0;
    //     }

    //     $purchases = PurchaseRecord::where('party_id', $party->id)
    //         ->where('type_of_bill', 'regular')
    //         ->where('bill_date', '<', $till_date)
    //         ->get();


    //     $paid_amounts = PurchaseRemainingAmount::where('party_id', $party->id)
    //         ->where('is_original_payment', 0)
    //         ->where('status', 1)
    //         ->where('payment_date', '<', $till_date)
    //         ->get();


    //     $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
    //         ->where('type', 'purchase')
    //         ->where('status', 1)
    //         ->where('payment_date', '<', $till_date)
    //         ->get();

    //     $purchase_orders = PurchaseOrder::where('party_id', $party->id)->where('status', 1)->where('date', '<', $till_date)->get();


    //     foreach ($purchases as $purchase) {
    //         $opening_balance += $purchase->total_amount;

    //         $debitNotes = DebitNote::where('bill_no', $purchase->id)->where('status', 1)->where('type', 'purchase')->whereIn('reason', ['purchase_return', 'new_rate_or_discount_value_with_gst', 'discount_on_purchase'])->get();
            

    //         $creditNotes = CreditNote::where('invoice_id', $purchase->id)->where('status', 1)->where('type', 'purchase')->whereIn('reason', ['new_rate_or_discount_value_with_gst'])->get();

            
    //         foreach( $debitNotes as $debitNote ){
    //             $opening_balance -= $debitNote->note_value;
    //         }

    //         foreach ($creditNotes as $creditNote) {
    //             $opening_balance += $creditNote->note_value;
    //         }
    //     }

    //     foreach ($paid_amounts as $amount) {
    //         $opening_balance -= $amount->amount_paid;  
    //     }

    //     foreach ($party_paid_amounts as $amount) {
    //         $opening_balance -= $amount->amount;
    //     }

    //     foreach ($purchase_orders as $order) {
    //         $opening_balance += $order->amount_received;
    //     }

    //     return $opening_balance;
    // }

    public function debtor_report( Request $request ) 
    {


        if( isset( $request->query_by ) && isset( $request->q ) ){

            if( $request->query_by == 'name' ) {
                $parties = Party::where('user_id', Auth::user()->id)->where('name', $request->q)->where('balance_type', 'debitor')->get();
            }
            // cannot search by invoice because amount paid to party can not be adjusted here
            // } else if( $request->query_by == 'invoice' ) {
            //     $invoice = SaleRemainingAmount::where('invoice_id', $request->q)->orderBy('created_at', 'desc')->first();

            //     if( $invoice ) {
            //         $parties = Party::where('id', $invoice->party_id)->get();

            //         foreach( $parties as $party ) {
            //             $party->total_amount = $invoice->amount_remaining;

            //             $party->total_amount += $party->opening_balance;

            //             $party->total_amount = $party->total_amount;
            //         }
            //     }
            // }

        } else {
            $parties = Party::where('user_id', Auth::user()->id)->where('balance_type', 'debitor')->get();
        }

        // dd( $parties );

        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            // $from_date = date('Y') . '-04-01';
            // $to_date = date('Y-m-d', time());

            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $opening_balance_from_date = auth()->user()->profile->financial_year_from;
        $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

        $closing_balance_from_date = auth()->user()->profile->book_beginning_from;
        // $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        if( \Carbon\Carbon::parse($from_date)->lt(\Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)) ){
            $closing_balance_to_date = \Carbon\Carbon::parse($from_date);
        } else {
            $closing_balance_to_date = auth()->user()->profile->financial_year_from;
        }

        //if( $request->query_by != 'invoice' ) {
            foreach( $parties as $party) {

                // $party->opening_balance = $this->fetch_debtor_static_balance($party, $opening_balance_from_date, $opening_balance_to_date);
                $party->opening_balance = $party->opening_balance;

                if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
                    $this_balance = $this->fetch_debtor_opening_balance($party, $opening_balance_from_date, $opening_balance_to_date);
                    // return $this_balance;
                    // $party->fetch_opening_balance = $this_balance;
                    $party->opening_balance += $this_balance;
                }

                // return $party->opening_balance;

                // closing balance will only change if there is no opening balance
                // if($party->opening_balance == 0){
                    $party->opening_balance += $this->calculate_debtor_closing_balance($party, $closing_balance_from_date, $closing_balance_to_date);
                // }

                // $party->opening_balance = $this->calculate_debtor_opening_balance($party, $from_date);

                $combined_array = array();

                $sale_array = array();
                $discount_array = array();
                $credit_note_array = array();
                $debit_note_array = array();
                $receipt_array = array();
                $party_receipt_array = array();
                $sale_order_array = array();

                $cash_array = array();
                $bank_array = array();
                $pos_array = array();

                $query1 = Invoice::where('party_id', $party->id)->where('type_of_bill', 'regular');

                $query1 = $query1->whereBetween('invoice_date', [ $from_date, $to_date ]);

                $invoices = $query1->get();

                $total = 0;
                foreach( $invoices as $invoice ) {
                    
                    // if($invoice->gst_classification == 'rcm'){
                    //     $total += $invoice->item_total_amount;
                    //     $amount_to_show = $invoice->item_total_amount;
                    // } else {
                    //     $total += $invoice->total_amount;
                    //     $amount_to_show = $invoice->amount_before_round_off;
                    // }
                    if($invoice->gst_classification == 'rcm'){
                        $total += $invoice->total_amount;
                        $amount_to_show = $invoice->total_amount;
                    } else {
                        $total += $invoice->total_amount;
                        $amount_to_show = $invoice->total_amount;
                    }
                    
                    if($amount_to_show > 0){
                        $sale_array[] = [
                            'routable' => $invoice->id,
                            'particulars' => 'Sale',
                            'voucher_type' => 'Sale',
                            'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                            'amount' => $amount_to_show,
                            'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                            'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'sale',
                            'type' => 'showable'
                        ];
                    }

                    // if($invoice->discount_payment != null || $invoice->discount_payment != 0){
                    //     $discount_array[] = [
                    //         'routable' => $invoice->id,
                    //         'particulars' => 'Discount',
                    //         'voucher_type' => 'Sale',
                    //         'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                    //         'amount' => $invoice->discount_payment,
                    //         'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                    //         'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                    //         'transaction_type' => 'credit',
                    //         'loop' => 'sale',
                    //         'type' => 'showable'
                    //     ];

                    // }

                    // if ($invoice->type_of_payment == 'no_payment') {
                    //     continue;
                    // } 
                    if ($invoice->type_of_payment == 'combined') {
                        $this_bank = Bank::find($invoice->bank_id);
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    else if($invoice->type_of_payment == 'cash+bank+pos') {
                        $this_bank = Bank::find($invoice->bank_id);
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if($invoice->type_of_payment == 'cash+bank+discount'){
                        $this_bank = Bank::find($invoice->bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if($invoice->type_of_payment == 'cash+pos+discount') {
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if($invoice->type_of_payment == 'bank+pos+discount'){
                        $this_bank = Bank::find($invoice->bank_id);
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if ($invoice->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($invoice->bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                    }
                    else if ($invoice->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                    }
                    else if($invoice->type_of_payment == 'cash+discount'){
                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if ($invoice->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($invoice->bank_id);
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                    }
                    else if($invoice->type_of_payment == 'bank+discount'){
                        $this_bank = Bank::find($invoice->bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if($invoice->type_of_payment == 'pos+discount'){
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else if ($invoice->type_of_payment == 'bank') {
                        $this_bank = Bank::find($invoice->bank_id);

                        if($invoice->bank_payment > 0){
                            $bank_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_bank->name ?? '',
                                'voucher_type' => 'Sale (Bank Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->bank_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    else if ($invoice->type_of_payment == 'pos') {
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if($invoice->pos_payment > 0){
                            $pos_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => $this_pos->name ?? '',
                                'voucher_type' => 'Sale (POS Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->pos_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }

                    } 
                    else if ($invoice->type_of_payment == 'discount') {
                        if($invoice->discount_payment > 0){
                            $discount_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Discount',
                                'voucher_type' => 'Sale (Cash Discount Payment)',
                                'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                                'amount' => $invoice->discount_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }
                    else {
                        if($invoice->cash_payment > 0){
                            $cash_array[] = [
                                'routable' => $invoice->id,
                                'particulars' => 'Cash',
                                'voucher_type' => 'Sale (Cash Payment)',
                                'voucher_no' => $invoice->invoice_no,
                                'amount' => $invoice->cash_payment,
                                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                                'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale',
                                'type' => 'showable'
                            ];
                        }
                    }

                    // //fetching credit notes for this invoice
                    // $creditNotes = CreditNote::where('invoice_id', $invoice->id)->where('status', 1)->where('type', 'sale')
                    // ->where(function ($query) {
                    //     $query->where('reason', 'sale_return')->orWhere('reason', 'new_rate_or_discount_value_with_gst')->orWhere('reason', 'discount_on_sale');
                    // })
                    // ->groupBy('note_no')
                    // ->get();

                    // //->whereBetween('created_at', [$from_date, $to_date])

                    // foreach( $creditNotes as $creditNote){
                    //     if($creditNote->reason == 'sale_return' || $creditNote->reason == 'new_rate_or_discount_value_with_gst' || $creditNote->reason == 'discount_on_sale') {

                    //         if($creditNote->note_value > 0){
                    //             $credit_note_array[] = [
                    //                 'routable' => $creditNote->note_no ?? 0,
                    //                 'particulars' => 'Credit Note',
                    //                 'voucher_type' => 'Note',
                    //                 'voucher_no' => $creditNote->note_no,
                    //                 'amount' => $creditNote->note_value,
                    //                 'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                    //                 'month' => Carbon::parse($creditNote->created_at)->format('m'),
                    //                 'transaction_type' => 'credit',
                    //                 'loop' => 'sale_credit_note',
                    //                 'type' => 'showable'
                    //             ];
                    //         }
                    //     }
                    // }

                    // // fetching debit notes for this invoice
                    // // changed because of new mail provided
                    // $debitNotes = DebitNote::where('bill_no', $invoice->id)->where('status', 1)->where('type', 'sale')->where('reason', 'new_rate_or_discount_value_with_gst')
                    // ->groupBy('note_no')
                    // ->get();

                    // // ->whereBetween('created_at', [$from_date, $to_date])

                    // foreach( $debitNotes as $debitNote){
                    //     if($debitNote->reason == 'new_rate_or_discount_value_with_gst') {

                    //         if($debitNote->note_value > 0){
                    //             $debit_note_array[] = [
                    //                 'routable' => $debitNote->note_no ?? 0,
                    //                 'particulars' => 'Debit Note',
                    //                 'voucher_type' => 'Note',
                    //                 'voucher_no' => $debitNote->note_no,
                    //                 'amount' => $debitNote->note_value,
                    //                 'date' => Carbon::parse( $debitNote->created_at)->format('Y-m-d'),
                    //                 'month' => Carbon::parse( $debitNote->created_at)->format('m'),
                    //                 'transaction_type' => 'debit',
                    //                 'loop' => 'sale_debit_note',
                    //                 'type' => 'showable'
                    //             ];
                    //         }
                    //     }
                    // }
                }

                $query2 = SaleRemainingAmount::where('party_id', $party->id)->where('is_original_payment', 0)->where('status', 1);

                $query2 = $query2->whereBetween('payment_date',[ $from_date, $to_date ]);

                $paid_amounts = $query2->get();

                foreach( $paid_amounts as $amount ){
                    $total -= $amount->amount_paid;
                    if ($amount->type_of_payment == 'no_payment'){ 
                        continue;
                    }
                    else if($amount->type_of_payment == 'combined'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+bank+pos'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash';

                        // $particulars = $amount->type_of_payment;
                    } 
                    else if($amount->type_of_payment == 'cash+bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'bank+pos+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'cash+discount') {
                        $particulars = 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+bank'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)';
                    }
                    else if($amount->type_of_payment == 'bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)';
                    }
                    else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)';
                    }
                    else if ($amount->type_of_payment == 'discount') {
                        $particulars = 'Discount';
                    } 
                    else {
                        $particulars = 'Cash';
                    }

                    if($amount->amount_paid > 0){
                        $receipt_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Receipt',
                            'voucher_no' => $amount->voucher_no,
                            'amount' => $amount->amount_paid,
                            'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->payment_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'receipt',
                            'type' => 'showable'
                        ];
                    }
                }

                $query3 = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'sale')->where('status', 1);
                $query3 = $query3->whereBetween( 'payment_date', [ $from_date, $to_date ]);
                $party_paid_amounts = $query3->get();

                foreach( $party_paid_amounts as $amount ){

                    $total -= $amount->amount;
                    if ($amount->type_of_payment == 'no_payment'){ 
                        continue;
                    }
                    else if($amount->type_of_payment == 'combined'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+bank+pos'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Cash';

                        // $particulars = $amount->type_of_payment;
                    } 
                    else if($amount->type_of_payment == 'cash+bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'cash+pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'bank+pos+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Cash';
                    }
                    else if ($amount->type_of_payment == 'cash+discount') {
                        $particulars = 'Cash+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+bank'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . $this_pos . '(POS)';
                    }
                    else if($amount->type_of_payment == 'bank+discount'){
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)+' . 'Discount';
                    }
                    else if($amount->type_of_payment == 'pos+discount'){
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)+' . 'Discount';
                    }
                    else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id)->name ?? '';

                        $particulars = $this_bank . '(Bank)';
                    }
                    else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id)->name ?? '';

                        $particulars = $this_pos . '(POS)';
                    }
                    else if ($amount->type_of_payment == 'discount') {
                        $particulars = 'Discount';
                    } 
                    else if ($amount->type_of_payment == 'cash') {
                        $particulars = 'Cash';
                    }

                    if($amount->amount > 0){
                        $party_receipt_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Receipt',
                            'voucher_no' => $amount->voucher_no,
                            'amount' => $amount->amount,
                            'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->payment_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'sale_party_payment',
                            'type' => 'showable'
                        ];
                    }
                }

                $query4 = SaleOrder::where('party_id', $party->id)->where('status', 1);

                $query4 = $query4->whereBetween('date', [$from_date, $to_date]);

                $sale_orders = $query4->get();

                foreach ($sale_orders as $order) {
                    if($order->amount_received > 0) {
                        $sale_order_array[$order->token] = [
                            'routable' => $order->token,
                            'particulars' => 'Sale Order',
                            'voucher_type' => 'Sale Order',
                            'voucher_no' => $order->token,
                            'amount' => $order->amount_received,
                            'date' => Carbon::parse($order->date)->format('Y-m-d'),
                            'month' => Carbon::parse($order->date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'sale_order',
                            'type' => 'showable'
                        ];
                    }
                }

                $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'sale')
                ->where(function ($query) {
                    $query->where('credit_notes.reason', 'sale_return')->orWhere('credit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('credit_notes.reason', 'discount_on_sale');
                })
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();


                foreach($creditNotes as $creditNote){
                    if($creditNote->reason == 'sale_return' || $creditNote->reason == 'new_rate_or_discount_value_with_gst' || $creditNote->reason == 'discount_on_sale') {

                        if($creditNote->note_value > 0){
                            $credit_note_array[] = [
                                'routable' => $creditNote->note_no ?? 0,
                                'particulars' => 'Credit Note',
                                'voucher_type' => 'Note',
                                'voucher_no' => $creditNote->note_no,
                                'amount' => $creditNote->note_value,
                                'date' => Carbon::parse($creditNote->note_date)->format('Y-m-d'),
                                'month' => Carbon::parse($creditNote->note_date)->format('m'),
                                'transaction_type' => 'credit',
                                'loop' => 'sale_credit_note',
                                'type' => 'showable'
                            ];
                        }
                    }
                }


                $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'sale')->where('debit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();


                foreach($debitNotes as $debitNote){
                    if($debitNote->reason == 'new_rate_or_discount_value_with_gst') {

                        if($debitNote->note_value > 0){
                            $debit_note_array[] = [
                                'routable' => $debitNote->note_no ?? 0,
                                'particulars' => 'Debit Note',
                                'voucher_type' => 'Note',
                                'voucher_no' => $debitNote->note_no,
                                'amount' => $debitNote->note_value,
                                'date' => Carbon::parse( $debitNote->note_date)->format('Y-m-d'),
                                'month' => Carbon::parse( $debitNote->note_date)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'sale_debit_note',
                                'type' => 'showable'
                            ];
                        }
                    }
                }

                // $creditNotes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.type', 'sale')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->get();

                // foreach( $creditNotes as $creditNote){
                //     if($creditNote->reason == 'sale_return' || $creditNote->reason == 'new_rate_or_discount_value_with_gst' || $creditNote->reason == 'discount_on_sale') {

                //         if($creditNote->note_value > 0){
                //             $credit_note_array[] = [
                //                 'routable' => $creditNote->note_no ?? 0,
                //                 'particulars' => 'Credit Note',
                //                 'voucher_type' => 'Note',
                //                 'voucher_no' => $creditNote->note_no,
                //                 'amount' => $creditNote->note_value,
                //                 'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                //                 'month' => Carbon::parse($creditNote->created_at)->format('m'),
                //                 'transaction_type' => 'credit',
                //                 'loop' => 'sale_credit_note',
                //                 'type' => 'showable'
                //             ];
                //         }
                //     }
                // }

                // $debitNotes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

                // foreach( $debitNotes as $debitNote){
                //     if($debitNote->reason == 'new_rate_or_discount_value_with_gst') {

                //         if($debitNote->note_value > 0){
                //             $debit_note_array[] = [
                //                 'routable' => $debitNote->note_no ?? 0,
                //                 'particulars' => 'Debit Note',
                //                 'voucher_type' => 'Note',
                //                 'voucher_no' => $debitNote->note_no,
                //                 'amount' => $debitNote->note_value,
                //                 'date' => Carbon::parse( $debitNote->created_at)->format('Y-m-d'),
                //                 'month' => Carbon::parse( $debitNote->created_at)->format('m'),
                //                 'transaction_type' => 'debit',
                //                 'loop' => 'sale_debit_note',
                //                 'type' => 'showable'
                //             ];
                //         }
                //     }
                // }

                $combined_array = array_merge(
                    $sale_array,
                    $cash_array,
                    $bank_array,
                    $pos_array,
                    $discount_array,
                    $credit_note_array,
                    $debit_note_array,
                    $receipt_array,
                    $party_receipt_array,
                    $sale_order_array
                );

                $this->array_sort_by_column($combined_array, 'date');
                
                // echo "<pre>";
                // print_r($combined_array);

                if( count($combined_array) > 0 ) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {
                        

                        // print_r($data);
                        // echo "<br/>break";

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $debitTotal += $party->opening_balance;
                    $combined_array['credit_total'] = $creditTotal;
                    $combined_array['debit_total'] = $debitTotal;
                    $combined_array['closing_total'] = $debitTotal - $creditTotal;
                } else {
                    $combined_array['credit_total'] = 0;
                    $combined_array['debit_total'] = $party->opening_balance;
                    $combined_array['closing_total'] = $party->opening_balance;
                }

                // return $sales;

                // $total += $party->opening_balance;
                $party->combined_array = $combined_array;
            }
        //}
        
        // return $parties;

        // die();

        return view( 'report.debtor', compact('parties', 'from_date', 'to_date'));
    }

    private function fetch_debtor_static_balance($party, $from, $to)
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

        $q = Party::where('id', $party->id);

        if ($isDatesSame) {
            $foundParty = $q->where('opening_balance_as_on', $from_date)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($foundParty) {
                $opening_balance = $foundParty->opening_balance;
            }
        } else {
            $foundParty = $q->whereBetween('balance_date', [$from_date, $to_date])->orderBy('id', 'desc')->first();

            if ($foundParty) {
                $opening_balance = $foundParty->opening_balance;
            }
        }

        return $opening_balance;
    }

    private function fetch_debtor_opening_balance($party, $from_date, $to_date)
    {
        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date)->format('Y-m-d');

        $to_date = \Carbon\Carbon::parse($to_date)->format('Y-m-d');

        // commented cause opening balance will only be for 01-04
        // $opening_balance += $this->fetch_static_balance($from_date, $to_date);

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
                $opening_balance += $invoice->total_amount;
            } else{
                $opening_balance += $invoice->total_amount;
            }

            $opening_balance -= $invoice->bank_payment ?? 0;
            $opening_balance -= $invoice->pos_payment ?? 0;
            $opening_balance -= $invoice->cash_payment ?? 0;
            $opening_balance -= $invoice->discount_payment ?? 0;
        }    

        foreach( $paid_amounts as $amount ){
            $opening_balance -= $amount->amount_paid;
        }

        foreach( $party_paid_amounts as $amount ){
            $opening_balance -= $amount->amount;
        }

        foreach ($sale_orders as $order) {
            $opening_balance -= $order->amount_received;
        }

        foreach ($creditNotes as $creditNote) {
            $opening_balance -= $creditNote->note_value;
        }

        foreach ($debitNotes as $debitNote) {
            $opening_balance += $debitNote->note_value;
        }

        return $opening_balance;
    }

    private function calculate_debtor_closing_balance ($party, $from_date, $to_date)
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

    // private function calculate_debtor_opening_balance($party, $till_date)
    // {
    //     if ($party->opening_balance != null) {
    //         $opening_balance = $party->opening_balance;
    //     } else {
    //         $opening_balance = 0;
    //     }

    //     $invoices = Invoice::where('party_id', $party->id)->where('type_of_bill', 'regular')
    //         ->where('invoice_date', '<', $till_date)
    //         ->get();


    //     $paid_amounts = SaleRemainingAmount::where('party_id', $party->id)
    //         ->where('payment_date', '<', $till_date)
    //         ->get();


    //     $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
    //         ->where('type', 'sale')
    //         ->where('payment_date', '<', $till_date)
    //         ->get();

    //     $sale_orders = SaleOrder::where('party_id', $party->id)
    //         ->where('status', 1)
    //         ->where('date', '<', $till_date)
    //         ->get();

    //     foreach( $invoices as $invoice ){
    //         if(auth()->user()->profile->registered == 3){
    //             $item_total_amount = $invoice->item_total_amount;
    //             $total_discount = $invoice->total_discount ? $invoice->total_discount : 0;
    //             $opening_balance += ($item_total_amount - $total_discount);
    //         } else{
    //             $opening_balance += $invoice->total_amount;
    //         }

    //         $creditNotes = CreditNote::where('invoice_id', $invoice->id)->whereIn('reason', ['sale_return', 'discount_on_sale', 'other'])->where('type', 'sale')->get();

    //         $debitNotes = DebitNote::where('bill_no', $invoice->id)->whereIn('reason', ['other'])->where('type', 'sale')->get();

    //         foreach ($creditNotes as $creditNote) {
    //             $opening_balance -= $creditNote->note_value;
    //         }

    //         foreach ($debitNotes as $debitNote) {
    //             $opening_balance += $debitNote->note_value;
    //         }
    //     }

    //     foreach( $paid_amounts as $amount ){
    //         $opening_balance -= $amount->amount_paid;
    //     }

    //     foreach( $party_paid_amounts as $amount ){
    //         $opening_balance -= $amount->amount;
    //     }

    //     foreach ($sale_orders as $order) {
    //         $opening_balance -= $order->amount_received;
    //     }

    //     return $opening_balance;
    // }

    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    public function credit_debit_note_report(Request $request) {

        $credit_notes = User::findOrFail(Auth::user()->id)->creditNotes()->get();

        $debit_notes = User::findOrFail(Auth::user()->id)->debitNotes()->get();

        $notes = array();

        foreach( $credit_notes as $note ){
            $note->note_type = 'credit';

            $note->date = $note->created_at->format('d-m-Y');

            $note->voucher_no = "CN000".$note->id;

            $note->pre_gst = 'N';

            $item = Item::find($note->item_id);

            $note->gst_rate = $item->gst;

            if( $note->type == 'sale' ) {
                $bill = Invoice::find($note->invoice_id);
                $particular_item = Invoice_Item::where( 'invoice_id', $note->invoice_id )->where('item_id', $note->item_id)->first();
            } else {
                $bill = PurchaseRecord::where('bill_no', $note->invoice_id)->first();
                $particular_item = Purchase::where('bill_no', $note->invoice_id)->where( 'item_id', $note->item_id )->first();
            }

            $party = Party::find( $bill->party_id );

            $state = State::find($party->business_place);

            $note->place_of_supply = $state->state_code . '-' . $state->name;

            $note->cess = $particular_item->cess;

            $notes[] = $note->toArray();
        }

        foreach( $debit_notes as $note ){
            $note->note_type = 'debit';

            $note->date = $note->created_at->format('d-m-Y');

            $note->voucher_no = "DN000".$note->id;

            $note->pre_gst = 'N';

            $item = Item::find($note->item_id);

            $note->gst_rate = $item->gst;

            if($note->type  == 'sale') {
                $bill = Invoice::find($note->bill_no);
                $particular_item = Invoice_Item::where('invoice_id', $note->bill_no)->where('item_id', $note->item_id)->first();
            } else {
                $bill = PurchaseRecord::where('bill_no', $note->bill_no)->first();
                $particular_item = Purchase::where('bill_no', $note->bill_no)->where('item_id', $note->item_id )->first();
            }

            $party = Party::find( $bill->party_id);

            $state = State::find($party->business_place);

            $note->place_of_supply = $state->state_code . '-' . $state->name;

            $note->cess = $particular_item->cess;

            $notes[] = $note->toArray();
        }

        // dd( collect($notes) );

        // dd( $notes );

        if( isset($request->export_to_excel) && $request->export_to_excel == "yes" ) {
            $cdnrArray = [];

            // Define the Excel spreadsheet headers
            $cdnrArray[] = [ 'Note/Refund Voucher Number', 'Note/Refund Voucher Date', 'Document Type', 'Place of Supply', 'Note/Refund Voucher Value', 'Rate', 'Applicable % of Tax Rate', 'Taxable Value', 'Cess Amount', 'Pre GST'] ;

            foreach($notes as $note ) {

                $cdnrArray[] = [ $note['voucher_no'], $note['date'], $note['note_type'], $note['place_of_supply'], $note['price_difference'], $note['gst_rate'], '', $note['price'], $note['cess'], $note['pre_gst'] ];
            }

            Excel::create('CDNR', function($excel) use ( $cdnrArray ) {

                // Set the spreadsheet title, creator, and description
                $excel->setTitle('Credit Debit Note Report');
                $excel->setCreator('Admin')->setCompany('admin@test.com');
                $excel->setDescription('Credit Debit Note Report Sheet');

                // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function($sheet) use ( $cdnrArray) {
                    $sheet->fromArray( $cdnrArray,  null, 'A1', false, false);
                });
            })->download('xlsx');
        } else {


            return view( 'report.credit_or_debit_note_report', compact('notes') );
        
        }
    }


    // public function amount_in_bank_report() {
    //     return 'amount_in_bank_report';
    // }

    // public function amount_as_cash_report() {
        
    //     return 'amount_as_cash_report';
    // }

    public function cash_in_hand_report() {
        $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->get();

        return view('report.cash_in_hand_report', compact('cash_in_hand'));
    }

    public function cash_deposit_report() {
        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->get();

        foreach( $cash_deposited as $cash ) {
            $bank = Bank::find($cash->bank);

            $cash->bank = $bank->name;
        }
        
        return view('report.cash_deposit_report', compact('cash_deposited'));
    }

    public function cash_withdraw_report() {
        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->get();

        foreach( $cash_withdrawn as $cash ) {
            $bank = Bank::find($cash->bank);

            $cash->bank = $bank->name;
        }

        return view('report.cash_withdraw_report', compact('cash_withdrawn'));
    }

    public function sale_reference_name_report(Request $request)
    {
        if($request->has('reference_name')){
            $reference_name = $request->reference_name;
        } else {
            $reference_name = null;
        }

        if($reference_name){
            $invoices = User::find(auth()->user()->id)->invoices()->where('reference_name', $reference_name)->get();
        } else {
            $invoices = [];
        }

        $reference_names = User::find(auth()->user()->id)->invoices()->select('reference_name')->distinct('reference_name')->get();

        // return $reference_names;

        // return $invoices;

        return view('report.sale_reference_name_report', compact('invoices', 'reference_names'));
    }

    public function purchase_reference_name_report(Request $request)
    {
        if ($request->has('reference_name')) {
            $reference_name = $request->reference_name;
        } else {
            $reference_name = null;
        }

        if ($reference_name) {
            $purchases = User::find(auth()->user()->id)->purchases()->where('reference_name', $reference_name)->get();
        } else {
            $purchases = [];
        }

        $reference_names = User::find(auth()->user()->id)->purchases()->select('reference_name')->distinct('reference_name')->get();

        // return $reference_names;

        // return $purchases;

        return view('report.purchase_reference_name_report', compact('purchases', 'reference_names'));
    }

    public function item_wise_report()
    {
        $items = User::findOrFail(Auth::user()->id)->items;

        // return $items;

        return view('report.item_wise_report', compact('items'));
    }
}