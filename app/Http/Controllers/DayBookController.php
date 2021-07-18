<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\SaleOrder;
use App\PurchaseOrder;
use App\CashGST;
use App\CashWithdraw;
use App\CashDeposit;
use Carbon\Carbon;
use App\Party;
use App\Bank;

class DayBookController extends Controller
{
    public function generate_day_book(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));

            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = date('Y') . '-04-01';
            $to_date = date('Y-m-d', time());
        }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->get();

        $purchases = User::find(Auth::user()->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->get();

        $payments = User::find(Auth::user()->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->get();

        $receipts = User::find(Auth::user()->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->get();

        $sale_orders = SaleOrder::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

        $purchase_orders = PurchaseOrder::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

        $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

        $cash_deposited = CashDeposit::where('user_id', Auth::user()->id)->whereBetween('date', [$from_date, $to_date])->get();

        $sale_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->get();

        $purchase_party_payments = User::findOrFail(Auth::user()->id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->get();

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

        if (isset($sales) && !empty($sales)) {

            foreach ($sales as $sale) {

                $sale_array[] = [
                    'routable' => $sale->id,
                    'particulars' => $sale->party->name,
                    'voucher_type' => 'Sale',
                    'voucher_no' => $sale->invoice_no,
                    'amount' => $sale->total_amount,
                    'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                    'month' => Carbon::parse($sale->invoice_date)->format('m'),
                    'transaction_type' => 'debit',
                    'loop' => 'sale',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($purchases) && !empty($purchases)) {

            foreach ($purchases as $purchase) {

                $purchase_array[] = [
                    'routable' => $purchase->id,
                    'particulars' => $purchase->party->name,
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
        }

        if (isset($sale_orders) && !empty($sale_orders)) {

            foreach ($sale_orders as $order) {

                $party = Party::find($order->party_id);

                $order->party_name = $party->name;

                $sale_order_array[] = [
                    'routable' => $order->token,
                    'particulars' => $order->party_name,
                    'voucher_type' => 'Sale Order',
                    'voucher_no' => $order->token,
                    'amount' => $order->cash_amount + $order->bank_amount + $order->pos_amount,
                    'date' => Carbon::parse($order->date)->format('Y-m-d'),
                    'month' => Carbon::parse($order->date)->format('m'),
                    'transaction_type' => 'debit',
                    'loop' => 'sale_order',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($purchase_orders) && !empty($purchase_orders)) {

            foreach ($purchase_orders as $order) {

                $party = Party::find($order->party_id);

                $order->party_name = $party->name;

                $purchase_order_array[] = [
                    'routable' => $order->token,
                    'particulars' => $order->party_name,
                    'voucher_type' => 'Purchase Order',
                    'voucher_no' => $order->token,
                    'amount' => $order->cash_amount + $order->bank_amount + $order->pos_amount,
                    'date' => Carbon::parse($order->date)->format('Y-m-d'),
                    'month' => Carbon::parse($order->date)->format('m'),
                    'transaction_type' => 'credit',
                    'loop' => 'purchase_order',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($gst_payments) && !empty($gst_payments)) {

            foreach ($gst_payments as $payment) {

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

        if (isset($receipts) && !empty($receipts)) {
            foreach ($receipts as $receipt) {
                // $opening_balance += $receipt->cash_payment;

                $party = Party::find($receipt->party_id);

                $receipt->party_name = $party->name;

                $receipt_array[] = [
                    'routable' => $receipt->id,
                    'particulars' => $receipt->party_name,
                    'voucher_type' => 'Receipt',
                    'voucher_no' => $receipt->id,
                    'amount' => $receipt->amount_paid,
                    'date' => Carbon::parse($receipt->created_at)->format('Y-m-d'),
                    'month' => Carbon::parse($receipt->created_at)->format('m'),
                    'transaction_type' => 'debit',
                    'loop' => 'receipt',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($payments) && !empty($payments)) {
            foreach ($payments as $payment) {
                // $opening_balance -= $payment->cash_payment;

                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                $payment_array[] = [
                    'routable' => $payment->id,
                    'particulars' => $payment->party_name,
                    'voucher_type' => 'Payment',
                    'voucher_no' => $payment->id,
                    'amount' => $payment->amount_paid,
                    'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                    'month' => Carbon::parse($payment->payment_date)->format('m'),
                    'transaction_type' => 'credit',
                    'loop' => 'payment',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($sale_party_payments) && !empty($sale_party_payments)) {
            foreach ($sale_party_payments as $payment) {
                $party = Party::find($payment->party_id);

                $payment->party_name = $party->name;

                $sale_party_payment_array[] = [
                    'routable' => $payment->id,
                    'particulars' => $payment->party_name,
                    'voucher_type' => 'Sale Party Receipt',
                    'voucher_no' => $payment->id,
                    'amount' => $payment->amount,
                    'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                    'month' => Carbon::parse($payment->payment_date)->format('m'),
                    'transaction_type' => 'debit',
                    'loop' => 'sale_party_payment',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($purchase_party_payments) && !empty($purchase_party_payments)) {
            foreach ($purchase_party_payments as $payment) {
                $party = Party::find($payment->party_id);
                
                $payment->party_name = $party->name;
                $payment->transaction_type = 'credit';

                $purchase_party_payment_array[] = [
                    'routable' => $payment->id,
                    'particulars' => $payment->party_name,
                    'voucher_type' => 'Purchase Party Payment',
                    'voucher_no' => $payment->id,
                    'amount' => $payment->amount,
                    'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
                    'month' => Carbon::parse($payment->payment_date)->format('m'),
                    'transaction_type' => 'credit',
                    'loop' => 'purchase_party_payment',
                    'type' => 'showable'
                ];
            }
        }

        if (isset($cash_withdrawn) && !empty($cash_withdrawn)) {
            foreach ($cash_withdrawn as $cash) {
                $bank = Bank::find($cash->bank);

                $cash->bank_name = $bank->name;
                $cash->transaction_type = 'debit';

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

        if (isset($cash_deposited) && !empty($cash_deposited)) {
            foreach ($cash_deposited as $cash) {
                $bank = Bank::find($cash->bank);

                if ($bank) {
                    $cash->bank_name = $bank->name;
                } else {
                    $cash->bank_name = null;
                }
                $cash->transaction_type = 'credit';

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
            $cash_deposited_array
        );

        $this->array_sort_by_column($combined_array, 'date');

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

        foreach ($group as $key => $value) {
            $creditTotal = 0;
            $debitTotal = 0;
            foreach ($value as $data) {
                if ($data['transaction_type'] == 'credit') {
                    $creditTotal += $data['amount'];
                } elseif ($data['transaction_type'] == 'debit') {
                    $debitTotal += $data['amount'];
                }
            }
            $group[$key]['credit_total'] = $creditTotal;
            $group[$key]['debit_total'] = $debitTotal;
            $group[$key]['closing_total'] = $debitTotal - $creditTotal;
        }

        $combined_array = $group;

        // echo "<pre>";
        // print_r($combined_array);

        // die();

        return view('report.day_book', compact('combined_array', 'from_date', 'to_date'));
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
