<?php

namespace App\Http\Controllers\Api;

use Config;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\EwaybillApi\EwaybillApi as Ewayapi;

use App\Bank;
use App\CashWithdraw;
use App\CashDeposit;
use App\Group;
use App\Invoice;
use App\Item;
use App\Party;
use App\Purchase;
use App\PurchaseRecord;
use App\PurchaseRemainingAmount;
use App\SaleRemainingAmount;
use App\User;
use App\UploadedBill;
use App\Invoice_Item;
use App\CashWithdrawDocument;
use App\State;
use App\CashInHand;
use App\SaleOrder;
use App\PurchaseOrder;
use App\CashGST;
use App\FirebaseToken;
use App\PartyPendingPaymentAccount;
use App\UserProfile;
use App\Transporter;
use App\GSTCashLedgerBalance;
use App\GstList;
use App\GSTSetOff;
use App\MeasuringUnit;
use App\CreditNote;
use App\DebitNote;
use App\UploadedDocument;
use App\AdditionalCharge;
use App\EwayBill;
use App\TransporterDetail;
use App\RoundOffSetting;
use App\BankToBankTransfer;

class MobileApiController extends Controller
{

    private $gstin, $username, $ewbpwd;
    // Mobile APIs

    public function login()
    {
        if (request('ukey') === 'rApbmnMrWs7hfOkY8sgtThwWk2fjCJoM') {
            if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
                $user = Auth::user();

                $user->profile = Auth::user()->profile;

                // $firebase = FirebaseToken::where('user_id', $user->id)->first();

                // if( $firebase ){
                //     if( $firebase->token != request('devicetoken') ) {
                //         $firebase->token = request('devicetoken');
                //         $firebase->save();
                //     }
                // } else {
                //     $fireBaseData = new FirebaseToken;

                //     $fireBaseData->token = request('devicetoken');
                //     $fireBaseData->user_id = $user->id;

                //     $fireBaseData->save();
                // }

                return response()->json(['message' => 'success', 'data' => $user], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 401);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    /*
    public function items_report(Request $request) {

        if (request('ukey') === 'aT6plNArDWQdgKj4IXsHsxHQFhoV9skk') {
            if($request->user_id != null) {
                $items = Item::where('user_id', $request->user_id)->get();
            } else {
                $items = Item::all();
            }

            if( $items ) {
                foreach ($items as $item) {
                    $group = Group::find($item->group_id);

                    $item->group_name = $group->name ?? '';
                }
                return response()->json($items);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }

    }
    */

    public function items_report(Request $request)
    {
        if (request('ukey') === 'aT6plNArDWQdgKj4IXsHsxHQFhoV9skk') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'Invalid user']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'User Id is required']);
            }

            $items = Item::where('user_id', $user->id)->orderBy('updated_at', 'asc')->get();

            // return $items;

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            $value_type = $request->value_type;
            $price_type = $request->price_type;

            // return $items;

            $last_updated = null;

            foreach ($items as $item) {
                $last_updated = $item->updated_at;

                $group = Group::find($item->group_id);

                $item->group_name = $group->name ?? '';

                // return $item;

                /*----------Purchased Item--------------*/
                $purchasedItem = Purchase::where('item_id', $item->id)->whereBetween('bought_on', [$from_date, $to_date])->get();
                /*-----------------------------------*/
                // Add sale credit note quantity to inward side
                $soldItemCreditNotes = CreditNote::where('type', 'sale')->where('reason', 'sale_return')->whereBetween('created_at', [$from_date, $to_date])->get();

                $item->purchasedRate =  0;
                $item->purchasedQty = 0;
                foreach ($purchasedItem as $thisItem) {
                    $item->purchasedQty += $thisItem->qty;
                    $item->purchasedRate += $thisItem->price;
                }

                foreach ($soldItemCreditNotes as $note) {
                    $item->purchasedQty += $note->quantity;
                }

                if (count($purchasedItem) > 0) {
                    $item->purchasedRate = $item->purchasedRate / count($purchasedItem);
                }

                //return $item;

                // return $purchasedItem;

                /*-----------Sold Item----------------------*/
                $soldItem = Invoice_Item::where('item_id', $item->id)->whereBetween('sold_on', [$from_date, $to_date])->get();
                /*------------------------------------------*/
                // Add purchase debit note quantity to outward side
                $purchasedItemDebitNotes = DebitNote::where('type', 'purchase')->where('reason', 'purchase_return')->whereBetween('created_at', [$from_date, $to_date])->get();

                $item->soldRate =  0;
                $item->soldQty = 0;
                foreach ($soldItem as $thisItem) {
                    $item->soldQty += $thisItem->item_qty;
                    $item->soldRate += $thisItem->item_price;
                }

                foreach ($purchasedItemDebitNotes as $note) {
                    $item->soldQty -= $note->quantity;
                }

                if (count($soldItem) > 0) {
                    $item->soldRate = $item->soldRate / count($soldItem);
                }


                $standard_price = 0;
                if ($price_type == 'standard') {
                    if ($request->has('price')) {
                        $standard_price = $request->has('price');
                    }
                }

                if ($user->profile->percent_on_sale_of_invoice) {
                    $item->closing_value = $this->get_data_using_fifo_or_lifo($item, $value_type, $price_type, $standard_price, $user->profile->percent_on_sale_of_invoice) ?? 0;
                } else {
                    $item->closing_value = $this->get_data_using_fifo_or_lifo($item, $value_type, $price_type, $standard_price) ?? 0;
                }
            }

            if ($items) {
                return response()->json(['success' => true, 'last_updated' => $last_updated, 'data' => ['items' => $items, 'from_date' => $from_date, 'to_date' => $to_date]]);
            } else {
                return response()->json(['success' => false, 'message' => 'No Items']);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    private function get_data_using_fifo_or_lifo($item = null, $value_type = 'fifo', $price_type = 'normal', $standard_price_value = 0, $gp_percent = 0)
    {

        if (is_array($value_type)) {

            $lifo = in_array('lifo', $value_type);
            $fifo = in_array('fifo', $value_type);
            $lump_sump = in_array('lump_sump', $value_type);
        } else {
            $fifo = true;
            $lifo = false;
            $lump_sump = false;
        }

        if (!is_null($item)) {
            $sold_items = array();
            $sold_item_prices = array();

            $sold_items_fifo = array();
            $sold_item_prices_fifo = array();
            $sold_items_lifo = array();
            $sold_item_prices_lifo = array();

            $purchased_items_fifo = array();
            $purchased_items_lifo = array();
            $purchased_item_prices_fifo = array();
            $purchased_item_prices_lifo = array();

            $invoice_items = Invoice_Item::where("item_id", $item->id)->get();

            foreach ($invoice_items as $sold_item) {
                array_push($sold_items, $sold_item->item_qty);
                array_push($sold_item_prices, $sold_item->item_price);
            }

            $sold_items_fifo = $sold_items;
            $sold_item_prices_fifo = $sold_item_prices;

            $sold_items_lifo = $sold_items;
            $sold_item_prices_lifo = $sold_item_prices;


            $purchase_fifo_items = Purchase::where('item_id', $item->id)->orderBy('bought_on', 'asc')->get();
            $purchase_lifo_items = Purchase::where('item_id', $item->id)->orderBy('bought_on', 'desc')->get();


            // if( $order == 'asc'){
            array_push($purchased_items_fifo, $item->opening_stock);
            array_push($purchased_item_prices_fifo, $item->opening_stock_rate);
            // }

            foreach ($purchase_fifo_items as $purchase_item) {
                array_push($purchased_items_fifo, $purchase_item->qty);
                array_push($purchased_item_prices_fifo, $purchase_item->price);
            }

            foreach ($purchase_lifo_items as $purchase_item) {
                array_push($purchased_items_lifo, $purchase_item->qty);
                array_push($purchased_item_prices_lifo, $purchase_item->price);
            }

            // if ( $order == 'desc' ) {
            array_push($purchased_items_lifo, $item->opening_stock);
            array_push($purchased_item_prices_lifo, $item->opening_stock_rate);
            // }

            // $sold_count = count($sold_items);

            $sold_fifo_count = count($sold_items_fifo);
            $sold_lifo_count = count($sold_items_lifo);

            $purchase_fifo_count = count($purchased_items_fifo);
            $purchase_lifo_count = count($purchased_items_lifo);

            $closing_value_fifo = 0;
            $gross_profit_fifo = 0;

            $closing_value_lifo = 0;
            $gross_profit_lifo = 0;

            $sold_items_copy_fifo = $sold_items_fifo;
            $sold_items_copy_lifo = $sold_items_lifo;
            $purchased_items_copy_fifo = $purchased_items_fifo;
            $purchased_items_copy_lifo = $purchased_items_lifo;

            if ($lump_sump) {
                $purchase_total = array_sum($purchased_items_fifo);
                $sale_total = array_sum($sold_items_fifo);
                $gp_total = $sale_total * $gp_percent / 100;

                return $purchase_total + $gp_total - $sale_total;
            }


            if ($fifo) {
                for ($i = 0; $i < $sold_fifo_count; $i++) {
                    if ($sold_items_fifo[$i] == 0)
                        continue;

                    for ($j = 0; $j < $purchase_fifo_count; $j++) {


                        if ($sold_items_fifo[$i]  == 0) {
                            continue 2;
                        }

                        if ($purchased_items_fifo[$j] == 0) {
                            continue;
                        }


                        if ($sold_items_fifo[$i] > $purchased_items_fifo[$j]) {
                            $gross_profit_fifo += ($purchased_items_fifo[$j] * $sold_item_prices_fifo[$i]) - ($purchased_items_fifo[$j] * $purchased_item_prices_fifo[$j]);


                            $sold_items_fifo[$i] = $sold_items_fifo[$i] - $purchased_items_fifo[$j];
                            $purchased_items_fifo[$j] = 0;
                        }

                        if ($purchased_items_fifo[$j] >= $sold_items_fifo[$i]) {

                            // echo "(".$i.")" . $sold_items_fifo[$i] . " " . $sold_item_prices_fifo[$i] . "-" . $sold_items_fifo[$i] . " " . $purchased_items_fifo[$j];

                            $gross_profit_fifo += ($sold_items_fifo[$i] * $sold_item_prices_fifo[$i]) - ($sold_items_fifo[$i] * $purchased_item_prices_fifo[$j]);

                            $purchased_items_fifo[$j] = $purchased_items_fifo[$j] - $sold_items_fifo[$i];
                            $sold_items_fifo[$i] = 0;
                        }
                    }
                }

                // print_r($sold_items_fifo);
                // print_r($sold_item_prices_fifo);

                // echo $gross_profit_fifo . "<br/>";

                for ($j = 0; $j < $purchase_fifo_count; $j++) {
                    $closing_value_fifo += $purchased_items_fifo[$j] * $purchased_item_prices_fifo[$j];
                }

                $total_value_fifo = 0;
                $standard_closing_value_fifo = 0;
                $average_closing_value_fifo = 0;

                if (is_array($price_type)) {
                    $standard = in_array('standard', $price_type);
                    $average = in_array('average', $price_type);
                    $normal = in_array('normal', $price_type);
                } else {
                    $standard = false;
                    $average = false;
                    $normal = true;
                }

                $sold_item_standard_totals_fifo = 0;
                $sold_item_average_totals_fifo = 0;
                $sold_item_standard_calculated_totals_fifo = 0;
                $sold_item_average_calculated_totals_fifo = 0;

                if ($standard) {
                    $price = $standard_price_value;

                    $standard_closing_value_fifo = array_sum($purchased_items_fifo) * $price;

                    for ($i = 0; $i < $sold_fifo_count; $i++) {

                        $sold_item_standard_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                        $sold_item_standard_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                    }

                    $gross_profit_standard_fifo = $sold_item_standard_totals_fifo - $sold_item_standard_calculated_totals_fifo;
                }

                if ($average) {
                    if (count($purchased_item_prices_fifo) > 0) {
                        $price = array_sum($purchased_item_prices_fifo) / count($purchased_item_prices_fifo);
                        $average_closing_value_fifo = array_sum($purchased_items_fifo) * $price;
                    } else {
                        $price = 0;
                        $average_closing_value_fifo = 0;
                    }

                    for ($i = 0; $i < $sold_fifo_count; $i++) {
                        $sold_item_average_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                        $sold_item_average_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                    }

                    $gross_profit_average_fifo = $sold_item_average_totals_fifo - $sold_item_average_calculated_totals_fifo;
                }

                // $item->value_amount_fifo = $total_value_fifo;
                // $item->gross_profit_fifo = $gross_profit_fifo;

                return $closing_value_fifo;
            }

            //----------------------------Lifo starts

            if ($lifo) {
                for ($i = 0; $i < $sold_lifo_count; $i++) {
                    if ($sold_items_lifo[$i] == 0)
                        continue;

                    for ($j = 0; $j < $purchase_lifo_count; $j++) {


                        if ($sold_items_lifo[$i]  == 0) {
                            continue 2;
                        }

                        if ($purchased_items_lifo[$j] == 0) {
                            continue;
                        }


                        if ($sold_items_lifo[$i] > $purchased_items_lifo[$j]) {
                            $gross_profit_lifo += ($purchased_items_lifo[$j] * $sold_item_prices_lifo[$i]) - ($purchased_items_lifo[$j] * $purchased_item_prices_lifo[$j]);


                            $sold_items_lifo[$i] = $sold_items_lifo[$i] - $purchased_items_lifo[$j];
                            $purchased_items_lifo[$j] = 0;
                        }

                        if ($purchased_items_lifo[$j] >= $sold_items_lifo[$i]) {

                            $gross_profit_lifo += ($sold_items_lifo[$i] * $sold_item_prices_lifo[$i]) - ($sold_items_lifo[$i] * $purchased_item_prices_lifo[$j]);

                            $purchased_items_lifo[$j] = $purchased_items_lifo[$j] - $sold_items_lifo[$i];
                            $sold_items_lifo[$i] = 0;
                        }
                    }
                }

                for ($j = 0; $j < $purchase_lifo_count; $j++) {
                    $closing_value_lifo += $purchased_items_lifo[$j] * $purchased_item_prices_lifo[$j];
                }

                $total_value_lifo = 0;
                $standard_closing_value_lifo = 0;
                $average_closing_value_lifo = 0;

                if (is_array($price_type)) {
                    $standard = in_array('standard', $price_type);
                    $average = in_array('average', $price_type);
                    $normal = in_array('normal', $price_type);
                } else {
                    $standard = false;
                    $average = false;
                    $normal = true;
                }

                $sold_item_standard_totals_lifo = 0;
                $sold_item_average_totals_lifo = 0;
                $sold_item_standard_calculated_totals_lifo = 0;
                $sold_item_average_calculated_totals_lifo = 0;

                if ($standard) {
                    $price = $standard_price_value;

                    $standard_closing_value_lifo = array_sum($purchased_items_lifo) * $price;

                    for ($i = 0; $i < $sold_lifo_count; $i++) {

                        $sold_item_standard_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                        $sold_item_standard_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                    }

                    $gross_profit_standard_lifo = $sold_item_standard_totals_lifo - $sold_item_standard_calculated_totals_lifo;
                }

                if ($average) {
                    if (count($purchased_item_prices_lifo) > 0) {
                        $price = array_sum($purchased_item_prices_lifo) / count($purchased_item_prices_lifo);
                        $average_closing_value_lifo = array_sum($purchased_items_lifo) * $price;
                    } else {
                        $price = 0;
                        $average_closing_value_lifo = 0;
                    }

                    for ($i = 0; $i < $sold_lifo_count; $i++) {
                        $sold_item_average_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                        $sold_item_average_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                    }

                    $gross_profit_average_lifo = $sold_item_average_totals_lifo - $sold_item_average_calculated_totals_lifo;
                }



                // $item->value_amount_lifo = $total_value_lifo;
                // $item->gross_profit_lifo = $gross_profit_lifo

                return $closing_value_lifo;
            }

            //-----------------------------Lifo ends

        } else {
            return 0;
        }
    }

    // public function sales_report(Request $request){

    //     if (request('ukey') === 'efQTCHnP5bndUtNfFXLaZHmOkgdReETP') {

    //         if ($request->user_id != null) {
    //             $parties = Party::where('user_id', $request->user_id)->get();
    //         } else {
    //             $parties = Party::all();
    //         }

    //         if( count($parties) > 0 ) {
    //             foreach ($parties as $party) {
    //                 $records = Invoice::where('party_id', $party->id)->get();
    //                 $count = 0;
    //                 foreach ($records as $record) {
    //                     $sale_remaining = SaleRemainingAmount::where('party_id', $party->id)->where('invoice_id', $record->id)->orderBy('id', 'desc')->first();

    //                     if ($sale_remaining != null) {
    //                         $sale_records[$party->id] = $sale_remaining;
    //                         $sale_records[$party->id]->party_name = $party->name;
    //                     }
    //                     $count++;
    //                 }
    //             }

    //             return response()->json($sale_records);
    //         } else {
    //             return response()->json(['message' => 'no_data', 'data' => null], 200);
    //         }
    //     } else {
    //         return response()->json(['message' => 'invalid_key', 'data' => null], 401);
    //     }

    // }

    // public function purchases_report(Request $request)
    // {
    //     if (request('ukey') === 'FQTPDQjSfVzaZYwSGyrgWLNjIxNySxUj') {

    //         if ($request->user_id != null) {
    //             $parties = Party::where('user_id', $request->user_id)->get();
    //         } else {
    //             $parties = Party::all();
    //         }

    //         if( count($parties) > 0 ){
    //             foreach ($parties as $party) {
    //                 $records = PurchaseRecord::where('party_id', $party->id)->get();
    //                 $count = 0;
    //                 foreach ($records as $record) {
    //                     $purchase_remaining = PurchaseRemainingAmount::where('party_id', $party->id)->where('bill_no', $record->bill_no)->orderBy('id', 'desc')->first();

    //                     if ($purchase_remaining != null) {
    //                         $purchase_records[$party->id] = $purchase_remaining;
    //                         $purchase_records[$party->id]->party_name = $party->name;
    //                     }
    //                     $count++;
    //                 }
    //             }

    //             return response()->json($purchase_records);
    //         } else {
    //             return response()->json(['message' => 'no_data', 'data' => null], 200);
    //         }

    //     } else {
    //         return response()->json(['message' => 'invalid_key', 'data' => null], 401);
    //     }
    // }

    public function purchases_report(Request $request)
    {

        if (request('ukey') === 'FQTPDQjSfVzaZYwSGyrgWLNjIxNySxUj') {

            $purchases = User::find($request->user_id)->purchases()->where('type_of_bill', 'regular')->get();

            // $purchases = PurchaseRecord::where('user_id', Auth::user()->id)->where('type_of_bill', 'regular')->get();
            if ($purchases) {
                foreach ($purchases as $purchase) {
                    $party = Party::find($purchase->party_id);

                    $purchase->party_name = $party->name;
                }

                return response()->json($purchases);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function sales_report(Request $request)
    {

        if (request('ukey') === 'efQTCHnP5bndUtNfFXLaZHmOkgdReETP') {

            $invoices = User::findOrFail($request->user_id)->invoices()->where('type_of_bill', 'regular')->get();

            if ($invoices) {

                foreach ($invoices as $invoice) {
                    $party = Party::find($invoice->party_id);

                    $invoice->party_name = $party->name;
                }

                return response()->json($invoices);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function b2b_purchases(Request $request)
    {
        if (request('ukey') === 'i3DJeJtUCwPIgISXGcc7lOAfG2gdlKO8') {
            if ($request->user_id != null) {
                $registered_parties = Party::where('user_id', $request->user_id)->where('registered', 1)->get();
            } else {
                $registered_parties = Party::where('registered', 1)->get();
            }

            if (count($registered_parties) > 0) {
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
                return response()->json($purchase_records);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }


    public function b2b_sales(Request $request)
    {

        if (request('ukey') === '9IMSrwcoiDMCGOeLAietspD6RnYuphcz') {
            if ($request->user_id != null) {
                $registered_parties = Party::where('user_id', $request->user_id)->where('registered', 1)->get();
            } else {
                $registered_parties = Party::where('registered', 1)->get();
            }


            if (count($registered_parties) > 0) {
                foreach ($registered_parties as $party) {

                    if (isset($request->from) && isset($request->to)) {
                        $from = strtotime($request->from);
                        $to = strtotime($request->to);

                        $from = date('Y-m-d', $from);
                        $to = date('Y-m-d', $to);

                        $invoices[$party->id] = Invoice::where('party_id', $party->id)->whereBetween('invoice_date', [$from, $to])->get();
                    } else {
                        $invoices[$party->id] = Invoice::where('party_id', $party->id)->get();
                    }

                    foreach ($invoices[$party->id] as $record) {
                        $record->gst_no = $party->gst;
                    }
                }
                return response()->json($invoices);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_party(Request $request)
    {

        if (request('ukey') === 't5acN2R9pSrwwhY59ikW9qbgJuHL19j6') {
            $parties = Party::where('user_id', $request->user_id)->get();

            if ($parties) {
                return response()->json($parties);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_party_data(Request $request)
    {

        if (request('ukey') === 't5acN2R9pSrwwhY59ikW9qbgJuHL19j6') {
            $parties = Party::where('user_id', $request->user_id)->get();

            if ($parties) {
                return response()->json(['success' => true, 'data' => $parties]);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null]);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_bill_by_party(Request $request)
    {
        // return $request->all();

        if (request('ukey') === '0V5S6aKN97R9WGDh9guYsfjBpEn6mFAv') {
            $purchase_record = PurchaseRecord::where('party_id', $request->selected_party)->get();

            if (count($purchase_record) > 0) {
                foreach ($purchase_record as $record) {
                    $remaining_amount_data = PurchaseRemainingAmount::where('purchase_id', $record->id)->orderBy('id', 'desc')->first();

                    $record->remaining_amount = $remaining_amount_data;
                }
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }

            return response()->json($purchase_record);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_purchase_amounts(Request $request)
    {

        if (request('ukey') === '4xQB9kn9u0iLwRUCYCopLH01LSEcqvyc') {

            $party_id = $request->party_id;
            $bill_no = $request->bill_no;
            $user_id = $request->user_id;

            $associated_party = Party::find($party_id);

            $purchase_amounts = PurchaseRemainingAmount::where(['purchase_id' => $bill_no, 'party_id' => $party_id])->get();

            $purchased_amount = PurchaseRecord::where(['id' => $bill_no, 'party_id' => $party_id])->first();

            $total_amount = $purchased_amount->total_amount;

            $banks = Bank::where('user_id', $user_id)->get();

            foreach ($purchase_amounts as $purchase) {
                if ($purchase->type_of_payment == 'bank') {
                    $bank = Bank::find($purchase->bank_id);

                    $purchase->bank_name = $bank->name;
                    $purchase->bank_branch = $bank->branch;
                }
            }

            return response()->json(['purchase_amounts' => $purchase_amounts, 'associated_party' => $associated_party, 'bill_no' => $bill_no, 'total_amount' => $total_amount, 'banks' => $banks]);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_invoice_by_party(Request $request)
    {
        // return $request->all();

        if (request('ukey') === 'aWwkHFVh2MxwcUabbqdsrUdmMsA7WWDP') {
            $invoices = Invoice::where('party_id', $request->selected_party)->get();

            if ($invoices) {
                foreach ($invoices as $record) {
                    $remaining_amount_data = SaleRemainingAmount::where('invoice_id', $record->id)->orderBy('id', 'desc')->first();

                    $record->remaining_amount = $remaining_amount_data;
                }
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }

            return response()->json($invoices);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_sale_amounts(Request $request)
    {

        if (request('ukey') === 'glnKNLkJ89ySTXQe8SgvRWk3tS8yV3N5') {

            $party_id = $request->party_id;
            $invoice_id = $request->invoice_id;
            $user_id = $request->user_id;

            $associated_party = Party::find($party_id);

            $sale_amounts = SaleRemainingAmount::where(['invoice_id' => $invoice_id, 'party_id' => $party_id])->get();

            $sale_amount = Invoice::where(['id' => $invoice_id, 'party_id' => $party_id])->first();

            $total_amount = $sale_amount->total_amount;

            $banks = Bank::where('user_id', $user_id)->get();

            foreach ($sale_amounts as $sale) {
                if ($sale->type_of_payment == 'bank') {
                    $bank = Bank::find($sale->bank_id);

                    $sale->bank_name = $bank->name;
                    $sale->bank_branch = $bank->branch;
                }
            }

            return response()->json(['sale_amounts' => $sale_amounts, 'associated_party' => $associated_party, 'invoice_id' => $invoice_id, 'total_amount' => $total_amount, 'banks' => $banks]);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_bank(Request $request)
    {

        if (request('ukey') === 'qjtqIVUeISJ7tBRClwottnMjz0HSka2q') {

            $banks = Bank::where('user_id', $request->user_id)->get();

            if ($banks) {
                return response()->json($banks);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function post_bank(Request $request)
    {
        if (request('ukey') === 'UBkNIserRBBP06JrYg3uWySc8bF9Ga62') {

            $bank = new Bank;
            $bank->name = $request->name;
            $bank->account_no = $request->account_no;
            $bank->branch = $request->branch;
            $bank->ifsc = $request->ifsc;
            $bank->classification = $request->classification;
            $bank->type = $request->type;
            $bank->opening_balance = $request->opening_balance;
            $bank->balance_type = $request->balance_type;

            $timestamp = strtotime($request->opening_balance_on_date);
            $opening_balance_on_date = date("Y-m-d", $timestamp);

            $bank->opening_balance_on_date = $opening_balance_on_date;
            $bank->user_id = $request->user_id;

            $bank->created_on = 'mobile';

            if ($bank->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function upload_purchase_bill_images(Request $request)
    {

        if (request('ukey') === '0SIM5r9ReTDJzQ4GrdIT3H5pajdctwVz') {

            if ($request->hasfile('image')) {

                foreach ($request->file('image') as $image) {
                    // ++$count;

                    $path = Storage::disk('public')->putFile('bills', $image);

                    $upload_bill = new UploadedBill;

                    // $upload_bill->user_id = Auth::user()->id;
                    if ($request->has('user_id')) {
                        $upload_bill->user_id = $request->user_id;
                    } else {
                        $upload_bill->user_id = 1;
                    }

                    $upload_bill->month = (int)$request->month;
                    $upload_bill->year = $request->year;
                    $upload_bill->type = $request->type;

                    $upload_bill->image_path = $path;

                    $upload_bill->created_on = 'mobile';

                    $upload_bill->save();
                }
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }

        // $upload_bill = new App\UploadedBill;

        // $upload_bill->user_id = Auth::user()->id;

        // $form = new Form();
        // $form->filename=json_encode($data);


        // $form->save();

        // if( request('ukey') === '0SIM5r9ReTDJzQ4GrdIT3H5pajdctwVz' ){

        // return $request->images;

        // foreach($request->images as $image) {
        //     $this->saveBase64ToImage($image);
        // }

        // } else {
        // return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        // }

    }

    // public function saveBase64ToImage($image) {
    //     $path = base_path('traffic/upload/users/img/profile/');
    //     //$base = $_REQUEST['image'];
    //     $base = $image;
    //     $binary = base64_decode($base);
    //     //$binary = base64_decode(urldecode($base));
    //     header('Content-Type: bitmap; charset=utf-8');

    //     $f = finfo_open();
    //     $mime_type = finfo_buffer($f, $binary, FILEINFO_MIME_TYPE);
    //     $mime_type = str_ireplace('image/', '', $mime_type);

    //     $filename = md5(\Carbon\Carbon::now()) . '.' . $mime_type;
    //     $file = fopen($path . $filename, 'wb');
    //     if (fwrite($file, $binary)) {
    //         return $filename;
    //     } else {
    //         return FALSE;
    //     }

    //     fclose($file);
    // }

    public function get_item_list(Request $request)
    {

        if (request('ukey') === 'SRgwff01oL1rkA5Ah2yefMkolua9OnF5') {
            $items = Item::where('user_id', $request->user_id)->get();

            if ($items) {
                return response()->json($items);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_item_list_data(Request $request)
    {

        if (request('ukey') === 'SRgwff01oL1rkA5Ah2yefMkolua9OnF5') {
            $items = Item::where('user_id', $request->user_id)->get();

            if ($items) {
                return response()->json(['success' => true, 'data' => $items]);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null]);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_summary_data(Request $request)
    {

        if (request('ukey') === 'caqFGl7aCjcuJjtVVIZTe8L4KoGBaNgb') {

            $item = Invoice::where('party_id', $request->party_id)->orderBy('created_at', 'desc')->first();

            if ($item != null) {
                $last_sale_date = $item->created_at->format('Y-m-d');
                $last_receipt_date = $item->created_at->format('Y-m-d');
            } else {
                $last_sale_date = "0000-00-00";
                $last_receipt_date = "0000-00-00";
            }

            // $invoice_count = Invoice::where('party_id', $request->party_id)->count();

            // return $invoice_count;

            $total = 0;

            $invoices = Invoice::where('party_id', $request->party_id)->get();
            if ($invoices != null) {
                $invoice_count = count($invoices);

                foreach ($invoices as $invoice) {

                    $total += $invoice->total_amount;
                }
            } else {
                $invoice_count = 0;
            }


            $no_of_sale_invoices = $invoice_count;
            $avg_sale_invoice_amount = $total;

            $data = ['last_sale_date' => $last_sale_date, 'last_receipt_date' => $last_receipt_date, 'no_of_sale_invoices' => $no_of_sale_invoices, 'avg_sale_invoice_amount' => $avg_sale_invoice_amount];

            return response()->json($data);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_sold_data(Request $request)
    {
        if (request('ukey') === 'qi0Zx9zGEBa17kphWa1yXslSLUUgVGJa') {
            $invoices = Invoice::where('party_id', $request->party_id)->get();

            if (count($invoices) > 0) {
                foreach ($invoices as $invoice) {
                    $invoice_items = Invoice_Item::where('invoice_id', $invoice->id)->get();

                    foreach ($invoice_items  as $invoice_item) {
                        $item = Item::find($invoice_item->item_id);

                        $invoice->item_name = $item->name;
                    }
                }

                return response()->json($invoices);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_purchase_data(Request $request)
    {
        if (request('ukey') === '011CiMN0gjsRWyMSsOfMssGBtdPbnFvT') {
            $purchases = PurchaseRecord::where('party_id', $request->party_id)->get();

            if (count($purchases) > 0) {
                foreach ($purchases as  $purchase) {
                    $purchase_items = Purchase::where('purchase_id', $purchase->id)->get();

                    foreach ($purchase_items as $purchase_item) {
                        $item = Item::find($purchase_item->item_id);

                        $purchase->item_name = $item->name;
                    }
                }

                return response()->json($purchases);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_json_report_data(Request $request)
    {

        $parties = Party::where('user_id', $request->user_id)->get();

        foreach ($parties as $party) {
            $invoice = Invoice::where('party_id', $party->id)->get();

            $party->invoice = $invoice;
        }


        return response()->json($parties);
    }

    public function get_document(Request $request)
    {

        if (request('ukey') === 'lO6MXIteuPhdBIg5mg8WzfnJ2XdqfyNw') {

            $month = $request->month ?? null;
            $year = $request->year ?? null;

            $q = UploadedBill::where('user_id', $request->user_id)->where('type', $request->type)->where('status', 0);
            if ($month && $year) {
                $q = $q->where('year', $request->year)->where('month', $request->month);
            } else if ($month) {
                $q = $q->where('month', $request->month);
            } else if ($year) {
                $q = $q->where('year', $request->year);
            }

            $uploaded_bills = $q->orderBy('created_at', 'desc')->get();

            // return response()->json( $uploaded_bills );

            if (count($uploaded_bills) > 0) {
                return response()->json($uploaded_bills);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function delete_document(Request $request)
    {

        if (request('ukey') === 'YNh14ZQrOje9cyrGwXJlRALnTpfAj4xu') {
            $uploaded_bill = UploadedBill::find($request->id);

            if ($uploaded_bill->delete()) {
                return response()->json(['message' => 'data_deleted', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failed_to_delete_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    /*
    public function debtor_report( Request $request )
    {
        if(request( 'ukey') === '7uHW6VycgQAGgtTNHjcD69LIl3JOgJ8M') {
            
            if($request->has('user_id')){
                $user = User::find($request->user_id);

                if(!$user){
                    return response()->json(['success' => false, 'message' => 'Invalid user']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'User Id is required']);
            }

            $parties = $user->parties()->where('balance_type', 'debitor')->get();

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                // $from_date = date('Y') . '-04-01';
                // $to_date = date('Y-m-d', time());

                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            //if( $request->query_by != 'invoice' ) {
            foreach ($parties as $party) {

                $party->opening_balance = $this->calculate_debtor_opening_balance($party, $from_date);

                $combined_array = array();

                $sale_array = array();
                $credit_note_array = array();
                $debit_note_array = array();
                $receipt_array = array();
                $party_receipt_array = array();

                $query1 = Invoice::where('party_id', $party->id)->where('type_of_bill', 'regular');

                $query1 = $query1->whereBetween('invoice_date', [$from_date, $to_date]);

                $invoices = $query1->get();

                $total = 0;
                foreach ($invoices as $invoice) {
                    $total += $invoice->total_amount;
                    $sale_array[] = [
                        'routable' => $invoice->id,
                        'particulars' => 'Sale',
                        'voucher_type' => 'Sale',
                        'voucher_no' => $invoice->invoice_no,
                        'amount' => $invoice->total_amount,
                        'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                        'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'sale',
                        'type' => 'showable'
                    ];

                    //fetching credit notes for this invoice
                    $creditNotes = CreditNote::where('invoice_id', $invoice->id)->where('type', 'sale')->whereBetween('created_at', [$from_date, $to_date])->get();

                    foreach ($creditNotes as $creditNote) {
                        $credit_note_array[] = [
                            'routable' => $invoice->id,
                            'particulars' => 'Credit Note',
                            'voucher_type' => 'Note',
                            'voucher_no' => $creditNote->note_no,
                            'amount' => $creditNote->note_value ?? 0,
                            'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($creditNote->created_at)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'sale_credit_note',
                            'type' => 'showable'
                        ];
                    }

                    //fetching debit notes for this invoice
                    $debitNotes = DebitNote::where('bill_no', $invoice->id)->where('type', 'sale')->whereBetween('created_at', [$from_date, $to_date])->get();

                    foreach ($debitNotes as $debitNote) {
                        $debit_note_array[] = [
                            'routable' => $invoice->id,
                            'particulars' => 'Debit Note',
                            'voucher_type' => 'Note',
                            'voucher_no' => $debitNote->note_no,
                            'amount' => $debitNote->note_value ?? 0,
                            'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($debitNote->created_at)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'sale_debit_note',
                            'type' => 'showable'
                        ];
                    }
                }

                $query2 = SaleRemainingAmount::where('party_id', $party->id);

                $query2 = $query2->whereBetween('payment_date', [$from_date, $to_date]);

                $paid_amounts = $query2->get();

                foreach ($paid_amounts as $amount) {
                    $total -= $amount->amount_paid;
                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name;
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name;
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name;
                    } else {

                        $particulars = 'Cash';
                    }

                    $receipt_array[] = [
                        'routable' => $amount->id,
                        'particulars' => $particulars,
                        'voucher_type' => 'Receipt',
                        'voucher_no' => $amount->id,
                        'amount' => $amount->amount_paid,
                        'date' => Carbon::parse($amount->created_at)->format('Y-m-d'),
                        'month' => Carbon::parse($amount->created_at)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'receipt',
                        'type' => 'showable'
                    ];
                }

                $query3 = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'sale');

                $query3 = $query3->whereBetween('payment_date', [$from_date, $to_date]);

                $party_paid_amounts = $query3->get();

                foreach ($party_paid_amounts as $amount) {
                    $total -= $amount->amount;
                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '(Bank)+' . $this_pos->name . '(POS)+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '(Bank)+' . $this_pos->name . '(POS)';
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '(POS)+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);

                        $particulars = $this_bank->name . '(Bank)+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);

                        $particulars = $this_bank->name . '(Bank)';
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '(Bank)';
                    } else {

                        $particulars = 'Cash';
                    }
                    $party_receipt_array[] = [
                        'routable' => $amount->id,
                        'particulars' => $particulars,
                        'voucher_type' => 'Receipt',
                        'voucher_no' => $amount->id,
                        'amount' => $amount->amount,
                        'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($amount->payment_date)->format('m'),
                        'transaction_type' => 'credit',
                        'loop' => 'sale_party_payment',
                        'type' => 'showable'
                    ];
                }

                $combined_array = array_merge(
                    $sale_array,
                    $credit_note_array,
                    $receipt_array,
                    $party_receipt_array
                );

                $this->array_sort_by_column($combined_array, 'date');

                if (count($combined_array) > 0) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $debitTotal += $party->opening_balance;
                    // $combined_array['credit_total'] = $creditTotal;
                    // $combined_array['debit_total'] = $debitTotal;
                    // $combined_array['closing_total'] = $debitTotal - $creditTotal;
                    $party->credit_total = $creditTotal;
                    $party->debit_total = $debitTotal;
                    $party->closing_total = $debitTotal - $creditTotal;
                } else {
                    // $combined_array['credit_total'] = 0;
                    // $combined_array['debit_total'] = $party->opening_balance;
                    // $combined_array['closing_total'] = $party->opening_balance;

                    $party->credit_total = 0;
                    $party->debit_total = $party->opening_balance;
                    $party->closing_total = $party->opening_balance;
                }

                $party->combined_array = $combined_array;
            }

            if(count( $parties ) > 0) {
                return response()->json( ['success' => true, 'data' => ['parties' => $parties, 'from_date' => $from_date, 'to_date' => $to_date]] );
            } else {
                return response()->json(['success' => false, 'message' => 'no_data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }
    */

    public function debtor_report(Request $request)
    {

        if (request('ukey') === '7uHW6VycgQAGgtTNHjcD69LIl3JOgJ8M') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'Invalid user']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'User Id is required']);
            }

            $parties = $user->parties()->where('balance_type', 'debitor')->get();

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                // $from_date = date('Y') . '-04-01';
                // $to_date = date('Y-m-d', time());

                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            $opening_balance_from_date = $user->profile->financial_year_from;
            $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

            $closing_balance_from_date = $user->profile->book_beginning_from;
            $closing_balance_to_date = $user->profile->financial_year_from;

            /*
            foreach ($parties as $party) {

                $party->opening_balance = $this->calculate_debtor_opening_balance($user, $party, $from_date);

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

                $query1 = $query1->whereBetween('invoice_date', [$from_date, $to_date]);

                $invoices = $query1->get();

                $total = 0;
                foreach ($invoices as $invoice) {

                    if ($invoice->gst_classification == 'rcm') {
                        $total += $invoice->item_total_amount;
                        $amount_to_show = $invoice->item_total_amount;
                    } else {
                        $total += $invoice->total_amount;
                        $amount_to_show = $invoice->amount_before_round_off;
                    }

                    if ($amount_to_show > 0) {
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

                    if ($invoice->total_discount != null || $invoice->total_discount != 0) {
                        $discount_array[] = [
                            'routable' => $invoice->id,
                            'particulars' => 'Discount',
                            'voucher_type' => 'Sale',
                            'voucher_no' => $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix,
                            'amount' => $invoice->total_discount,
                            'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                            'month' => Carbon::parse($invoice->invoice_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'sale',
                            'type' => 'showable'
                        ];
                    }

                    // if ($invoice->type_of_payment == 'no_payment') {
                    //     continue;
                    // } 
                    if ($invoice->type_of_payment == 'combined') {
                        $this_bank = Bank::find($invoice->bank_id);
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if ($invoice->bank_payment > 0) {
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

                        if ($invoice->cash_payment > 0) {
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
                    } else if ($invoice->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($invoice->bank_id);
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if ($invoice->pos_payment > 0) {
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

                        if ($invoice->bank_payment > 0) {
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
                    } else if ($invoice->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if ($invoice->pos_payment > 0) {
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

                        if ($invoice->cash_payment > 0) {
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
                    } else if ($invoice->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($invoice->bank_id);

                        if ($invoice->bank_payment > 0) {
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

                        if ($invoice->cash_payment > 0) {
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
                    } else if ($invoice->type_of_payment == 'bank') {
                        $this_bank = Bank::find($invoice->bank_id);

                        if ($invoice->bank_payment > 0) {
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
                    } else if ($invoice->type_of_payment == 'pos') {
                        $this_pos = Bank::find($invoice->pos_bank_id);

                        if ($invoice->pos_payment > 0) {
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
                    } else {
                        if ($invoice->cash_payment > 0) {
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

                    //fetching credit notes for this invoice
                    $creditNotes = CreditNote::where('invoice_id', $invoice->id)->where('type', 'sale')->get();

                    //->whereBetween('created_at', [$from_date, $to_date])

                    foreach ($creditNotes as $creditNote) {
                        if ($creditNote->reason == 'sale_return' || $creditNote->reason == 'new_rate_or_discount_value_with_gst' || $creditNote->reason == 'discount_on_sale') {

                            if ($creditNote->note_value > 0) {
                                $credit_note_array[] = [
                                    'routable' => $creditNote->note_no ?? 0,
                                    'particulars' => 'Credit Note',
                                    'voucher_type' => 'Note',
                                    'voucher_no' => $creditNote->note_no,
                                    'amount' => $creditNote->note_value,
                                    'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                                    'month' => Carbon::parse($creditNote->created_at)->format('m'),
                                    'transaction_type' => 'credit',
                                    'loop' => 'sale_credit_note',
                                    'type' => 'showable'
                                ];
                            }
                        }
                    }

                    // fetching debit notes for this invoice
                    // changed because of new mail provided
                    $debitNotes = DebitNote::where('bill_no', $invoice->id)->where('type', 'sale')->get();

                    // ->whereBetween('created_at', [$from_date, $to_date])

                    foreach ($debitNotes as $debitNote) {
                        if ($debitNote->reason == 'new_rate_or_discount_value_with_gst') {

                            if ($debitNote->note_value > 0) {
                                $debit_note_array[] = [
                                    'routable' => $debitNote->note_no ?? 0,
                                    'particulars' => 'Debit Note',
                                    'voucher_type' => 'Note',
                                    'voucher_no' => $debitNote->note_no,
                                    'amount' => $debitNote->note_value,
                                    'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
                                    'month' => Carbon::parse($debitNote->created_at)->format('m'),
                                    'transaction_type' => 'debit',
                                    'loop' => 'sale_debit_note',
                                    'type' => 'showable'
                                ];
                            }
                        }
                    }
                }

                $query2 = SaleRemainingAmount::where('party_id', $party->id);

                $query2 = $query2->whereBetween('payment_date', [$from_date, $to_date]);

                $paid_amounts = $query2->get();

                foreach ($paid_amounts as $amount) {
                    $total -= $amount->amount_paid;
                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name;
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name;
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name;
                    } else {

                        $particulars = 'Cash';
                    }

                    if ($amount->amount_paid > 0) {
                        $receipt_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Receipt',
                            'voucher_no' => $amount->id,
                            'amount' => $amount->amount_paid,
                            'date' => Carbon::parse($amount->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->created_at)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'receipt',
                            'type' => 'showable'
                        ];
                    }
                }

                $query3 = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'sale');

                $query3 = $query3->whereBetween('payment_date', [$from_date, $to_date]);

                $party_paid_amounts = $query3->get();

                foreach ($party_paid_amounts as $amount) {
                    $total -= $amount->amount;
                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '(Bank)+' . $this_pos->name . '(POS)+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '(Bank)+' . $this_pos->name . '(POS)';
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '(POS)+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '(Bank)+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '(Bank)';
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '(Bank)';
                    } else {

                        $particulars = 'Cash';
                    }

                    if ($amount->amount > 0) {
                        $party_receipt_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Receipt',
                            'voucher_no' => $amount->id,
                            'amount' => $amount->amount,
                            'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->payment_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'sale_party_payment',
                            'type' => 'showable'
                        ];
                    }
                }

                $query4 = SaleOrder::where('party_id', $party->id);

                $query4 = $query4->whereBetween('date', [$from_date, $to_date]);

                $sale_orders = $query4->get();

                foreach ($sale_orders as $order) {
                    if ($order->amount_received > 0) {
                        $sale_order_array[] = [
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
                //     $debitTotal += $party->opening_balance;
                //     $combined_array['credit_total'] = $creditTotal;
                //     $combined_array['debit_total'] = $debitTotal;
                //     $combined_array['closing_total'] = $debitTotal - $creditTotal;
                // } else {
                //     $combined_array['credit_total'] = 0;
                //     $combined_array['debit_total'] = $party->opening_balance;
                //     $combined_array['closing_total'] = $party->opening_balance;
                // }

                if (count($combined_array) > 0) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $debitTotal += $party->opening_balance;
                    // $combined_array['credit_total'] = $creditTotal;
                    // $combined_array['debit_total'] = $debitTotal;
                    // $combined_array['closing_total'] = $debitTotal - $creditTotal;
                    $party->credit_total = $creditTotal;
                    $party->debit_total = $debitTotal;
                    $party->closing_total = $debitTotal - $creditTotal;
                } else {
                    // $combined_array['credit_total'] = 0;
                    // $combined_array['debit_total'] = $party->opening_balance;
                    // $combined_array['closing_total'] = $party->opening_balance;

                    $party->credit_total = 0;
                    $party->debit_total = $party->opening_balance;
                    $party->closing_total = $party->opening_balance;
                }

                // return $sales;

                // $total += $party->opening_balance;
                $party->combined_array = array_values($combined_array);
            }
            */

            foreach( $parties as $party) {

                $party->opening_balance = $party->opening_balance;

                if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
                    $this_balance = $this->fetch_debtor_opening_balance($user, $party, $opening_balance_from_date, $opening_balance_to_date);
                    $party->opening_balance += $this_balance;
                }

                // closing balance will only change if there is no opening balance
                // if($party->opening_balance == 0){
                    $party->opening_balance += $this->calculate_debtor_closing_balance($user, $party, $closing_balance_from_date, $closing_balance_to_date);
                // }

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

                $invoices = Invoice::where('party_id', $party->id)
                ->where('type_of_bill', 'regular')
                ->whereBetween('invoice_date', [ $from_date, $to_date ])
                ->get();

                $total = 0;
                foreach( $invoices as $invoice ) {
                    
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

                }

                $paid_amounts = SaleRemainingAmount::where('party_id', $party->id)
                ->where('is_original_payment', 0)
                ->where('status', 1)
                ->whereBetween('payment_date',[ $from_date, $to_date ])
                ->get();

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

                $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
                ->where('type', 'sale')
                ->where('status', 1)
                ->whereBetween( 'payment_date', [ $from_date, $to_date ])
                ->get();

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

                $sale_orders = SaleOrder::where('party_id', $party->id)
                ->where('status', 1)
                ->whereBetween('date', [$from_date, $to_date])
                ->get();

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

                $creditNotes = $user->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'sale')
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


                $debitNotes = $user->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'sale')->where('debit_notes.reason', 'new_rate_or_discount_value_with_gst')
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
                    // $combined_array['credit_total'] = $creditTotal;
                    // $combined_array['debit_total'] = $debitTotal;
                    // $combined_array['closing_total'] = $debitTotal - $creditTotal;

                    $party->credit_total = $creditTotal;
                    $party->debit_total = $debitTotal;
                    $party->closing_total = $debitTotal - $creditTotal;
                } else {
                    // $combined_array['credit_total'] = 0;
                    // $combined_array['debit_total'] = $party->opening_balance;
                    // $combined_array['closing_total'] = $party->opening_balance;

                    $party->credit_total = 0;
                    $party->debit_total = $party->opening_balance;
                    $party->closing_total = $party->opening_balance;
                }

                // return $sales;

                // $total += $party->opening_balance;
                $party->combined_array = $combined_array;
            }

            if (count($parties) > 0) {
                return response()->json(['success' => true, 'data' => ['parties' => $parties, 'from_date' => $from_date, 'to_date' => $to_date]]);
            } else {
                return response()->json(['success' => false, 'message' => 'no_data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
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

    private function fetch_debtor_opening_balance($user, $party, $from_date, $to_date)
    {
        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date);
        // subtract a minute as we want to search till that date
        // eg if to_date is 03-04-2020 subtract 1 min will become 02-04-2020 23:59:00
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();

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

        $creditNotes = $user->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'sale')
                ->where(function ($query) {
                    $query->where('credit_notes.reason', 'sale_return')->orWhere('credit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('credit_notes.reason', 'discount_on_sale');
                })
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();

        $debitNotes = $user->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'sale')->where('debit_notes.reason', 'new_rate_or_discount_value_with_gst')
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

    private function calculate_debtor_closing_balance ($user, $party, $from_date, $to_date)
    {
        $from_date = \Carbon\Carbon::parse($from_date);
        $to_date = \Carbon\Carbon::parse($to_date);
        
        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }

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

        $creditNotes = $user->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'sale')
                ->where(function ($query) {
                    $query->where('credit_notes.reason', 'sale_return')->orWhere('credit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('credit_notes.reason', 'discount_on_sale');
                })
                ->whereBetween('credit_notes.note_date', [$from_date, $to_date])
                ->groupBy('credit_notes.note_no')
                ->get();

        $debitNotes = $user->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'sale')->where('debit_notes.reason', 'new_rate_or_discount_value_with_gst')
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();

        foreach( $invoices as $invoice ) {
            // if(auth()->user()->profile->registered == 3){
            //     $item_total_amount = $invoice->item_total_amount;
            //     $total_discount = $invoice->total_discount ? $invoice->total_discount : 0;
            //     $closing_balance += ($item_total_amount - $total_discount);
            // } else{
            //     $closing_balance += $invoice->total_amount;
            // }

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

    /*
    private function calculate_debtor_opening_balance($user, $party, $till_date)
    {
        if ($party->opening_balance != null) {
            $opening_balance = $party->opening_balance;
        } else {
            $opening_balance = 0;
        }

        $invoices = Invoice::where('party_id', $party->id)->where('type_of_bill', 'regular')
            ->where('invoice_date', '<', $till_date)
            ->get();


        $paid_amounts = SaleRemainingAmount::where('party_id', $party->id)
            ->where('payment_date', '<', $till_date)
            ->get();


        $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
            ->where('type', 'sale')
            ->where('payment_date', '<', $till_date)
            ->get();

        foreach ($invoices as $invoice) {
            if ($user->profile->registered == 3) {
                $item_total_amount = $invoice->item_total_amount;
                $total_discount = $invoice->total_discount ? $invoice->total_discount : 0;
                $opening_balance += ($item_total_amount - $total_discount);
            } else {
                $opening_balance += $invoice->total_amount;
            }

            $creditNotes = CreditNote::where('invoice_id', $invoice->id)->whereIn('reason', ['sale_return', 'discount_on_sale', 'other'])->where('type', 'sale')->get();

            $debitNotes = DebitNote::where('bill_no', $invoice->id)->whereIn('reason', ['other'])->where('type', 'sale')->get();

            foreach ($creditNotes as $creditNote) {
                $opening_balance -= $creditNote->note_value;
            }

            foreach ($debitNotes as $debitNote) {
                $opening_balance += $debitNote->note_value;
            }
        }

        foreach ($paid_amounts as $amount) {
            $opening_balance -= $amount->amount_paid;
        }

        foreach ($party_paid_amounts as $amount) {
            $opening_balance -= $amount->amount;
        }

        return $opening_balance;
    }
    */

    /*
    public function creditor_report( Request $request )
    {
        if(request('ukey') === 'DacUUSCQmqLGFo9A4zKqePvm9igHMoV4') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'Invalid user']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'User Id is required']);
            }
            
            $parties = $user->parties()->where('balance_type', 'creditor')->get();

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                // $from_date = date('Y') . '-04-01';
                // $to_date = date('Y-m-d', time());

                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            //if( $request->query_by != 'bill' ) {

            foreach ($parties as $party) {

                $party->opening_balance = $this->calculate_creditor_opening_balance($party, $from_date);

                $combined_array = array();

                $purchase_array = array();
                $debit_note_array = array();
                $credit_note_array = array();
                $payment_array = array();
                $party_payment_array = array();

                $query1 = PurchaseRecord::where('party_id', $party->id);

                $query1 = $query1->whereBetween('bill_date', [$from_date, $to_date]);

                $purchases = $query1->get();

                // $total = 0;

                foreach ($purchases as $purchase) {
                    // $total += $purchase->total_amount;

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

                    //fetching debit notes for this bill
                    $debitNotes = DebitNote::where('bill_no', $purchase->id)->where('type', 'purchase')->whereBetween('created_at', [$from_date, $to_date])->get();

                    foreach ($debitNotes as $debitNote) {
                        $debit_note_array[] = [
                            'routable' => $purchase->id,
                            'particulars' => 'Debit Note',
                            'voucher_type' => 'Note',
                            'voucher_no' => $debitNote->note_no,
                            'amount' => $debitNote->note_value ?? 0,
                            'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($debitNote->created_at)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'purchase_debit_note',
                            'type' => 'showable'
                        ];
                    }


                    //fetching credit notes for this invoice
                    $creditNotes = CreditNote::where('invoice_id', $purchase->id)->where('type', 'purchase')->whereBetween('created_at', [$from_date, $to_date])->get();

                    foreach ($creditNotes as $creditNote) {
                        $credit_note_array[] = [
                            'routable' => $purchase->id,
                            'particulars' => 'Credit Note',
                            'voucher_type' => 'Note',
                            'voucher_no' => $creditNote->note_no,
                            'amount' => $creditNote->note_value ?? 0,
                            'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($creditNote->created_at)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'purchase_credit_note',
                            'type' => 'showable'
                        ];
                    }
                }

                $query2 = PurchaseRemainingAmount::where('party_id', $party->id);

                $query2 = $query2->whereBetween('payment_date', [$from_date, $to_date]);

                $paid_amounts = $query2->get();

                foreach ($paid_amounts as $amount) {

                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name;
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);

                        $particulars = $this_bank->name;
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name;
                    } else {

                        $particulars = 'Cash';
                    }

                    $payment_array[] = [
                        'routable' => $amount->id,
                        'particulars' => $particulars,
                        'voucher_type' => 'Payment',
                        'voucher_no' => $amount->id,
                        'amount' => $amount->amount_paid,
                        'date' => Carbon::parse($amount->created_at)->format('Y-m-d'),
                        'month' => Carbon::parse($amount->created_at)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'payment',
                        'type' => 'showable'
                    ];
                }

                $query3 = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'purchase');

                $query3 = $query3->whereBetween('payment_date', [$from_date, $to_date]);

                $party_paid_amounts = $query3->get();

                foreach ($party_paid_amounts as $amount) {

                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name;
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name;
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name;
                    } else {

                        $particulars = 'Cash';
                    }

                    $party_payment_array[] = [
                        'routable' => $amount->id,
                        'particulars' => $particulars,
                        'voucher_type' => 'Payment',
                        'voucher_no' => $amount->id,
                        'amount' => $amount->amount,
                        'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                        'month' => Carbon::parse($amount->payment_date)->format('m'),
                        'transaction_type' => 'debit',
                        'loop' => 'purchase_party_payment',
                        'type' => 'showable'
                    ];
                }

                $combined_array = array_merge(
                    $purchase_array,
                    $debit_note_array,
                    $credit_note_array,
                    $payment_array,
                    $party_payment_array
                );

                $this->array_sort_by_column($combined_array, 'date');

                if (count($combined_array) > 0) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $creditTotal += $party->opening_balance;
                    // $combined_array['credit_total'] = $creditTotal;
                    // $combined_array['debit_total'] = $debitTotal;
                    // $combined_array['closing_total'] = $debitTotal - $creditTotal;

                    $party->credit_total = $creditTotal;
                    $party->debit_total = $debitTotal;
                    $party->closing_total = $debitTotal - $creditTotal;
                } else {
                    // $combined_array['credit_total'] = $party->opening_balance;
                    // $combined_array['debit_total'] = 0;
                    // $combined_array['closing_total'] = $party->opening_balance;

                    $party->credit_total = $party->opening_balance;
                    $party->debit_total = 0;
                    $party->closing_total = $party->opening_balance;
                }

                $party->combined_array = $combined_array;


                // if (count($combined_array) > 0) {
                //     $creditTotal = 0;
                //     $debitTotal = 0;
                //     foreach ($combined_array as $data) {

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

                // $party->combined_array = $combined_array;
            }


            if(count( $parties ) > 0) {
                return response()->json(['success' => true, 'data' => ['parties' => $parties, 'from_date' => $from_date, 'to_date' => $to_date]]);
            } else {
                return response()->json(['success' => false, 'message' => 'no_data']);
            }

        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }
    */

    public function creditor_report(Request $request)
    {

        if (request('ukey') === 'DacUUSCQmqLGFo9A4zKqePvm9igHMoV4') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'Invalid user']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'User Id is required']);
            }

            $parties = $user->parties()->where('balance_type', 'creditor')->get();

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            $opening_balance_from_date = $user->profile->financial_year_from;
            $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

            $closing_balance_from_date = $user->profile->book_beginning_from;
            $closing_balance_to_date = $user->profile->financial_year_from;

            /*
            foreach ($parties as $party) {

                $party->opening_balance = $this->calculate_creditor_opening_balance($party, $from_date);

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

                $query1 = PurchaseRecord::where('party_id', $party->id);

                $query1 = $query1->whereBetween('bill_date', [$from_date, $to_date]);

                $purchases = $query1->get();

                $total = 0;


                foreach ($purchases as $purchase) {
                    // $total += $purchase->total_amount;

                    if ($purchase->amount_before_round_off > 0) {
                        $purchase_array[] = [
                            'routable' => $purchase->id,
                            'particulars' => 'Purchase',
                            'voucher_type' => 'Purchase',
                            'voucher_no' => $purchase->bill_no,
                            'amount' => $purchase->amount_before_round_off,
                            'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                            'month' => Carbon::parse($purchase->bill_date)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'purchase',
                            'type' => 'showable'
                        ];
                    }

                    if ($purchase->total_discount > 0) {
                        $discount_array[] = [
                            'routable' => $purchase->id,
                            'particulars' => 'Discount',
                            'voucher_type' => 'Purchase',
                            'voucher_no' => $purchase->bill_no,
                            'amount' => $purchase->total_discount,
                            'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                            'month' => Carbon::parse($purchase->bill_date)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'purchase',
                            'type' => 'showable'
                        ];
                    }

                    // if ($purchase->type_of_payment == 'no_payment') {
                    //     continue;
                    // } 
                    if ($purchase->type_of_payment == 'combined') {
                        $this_bank = Bank::find($purchase->bank_id);
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if ($purchase->bank_payment > 0) {
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

                        if ($purchase->pos_payment > 0) {
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

                        if ($purchase->cash_payment > 0) {
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
                    } else if ($purchase->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($purchase->bank_id);
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if ($purchase->pos_payment > 0) {
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

                        if ($purchase->bank_payment > 0) {
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
                    } else if ($purchase->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if ($purchase->pos_payment > 0) {
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

                        if ($purchase->cash_payment > 0) {
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
                    } else if ($purchase->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($purchase->bank_id);

                        if ($purchase->bank_payment > 0) {
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

                        if ($purchase->cash_payment > 0) {
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
                    } else if ($purchase->type_of_payment == 'bank') {
                        $this_bank = Bank::find($purchase->bank_id);

                        if ($purchase->bank_payment > 0) {
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
                    } else if ($purchase->type_of_payment == 'pos') {
                        $this_pos = Bank::find($purchase->pos_bank_id);

                        if ($purchase->post_payment > 0) {
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
                    } else {

                        if ($purchase->cash_payment > 0) {
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

                    // fetching debit notes for this bill
                    $debitNotes = DebitNote::where('bill_no', $purchase->id)->where('type', 'purchase')->whereIn('reason', ['purchase_return', 'new_rate_or_discount_value_with_gst', 'discount_on_purchase'])->get();
                    //->whereBetween('created_at', [$from_date, $to_date])

                    foreach ($debitNotes as $debitNote) {
                        // if($debitNote->reason == 'purchase_return' || $debitNote->reason == 'new_rate_or_discount_value_with_gst' || $debitNote->reason == 'discount_on_purchase'){
                        if ($debitNote->note_value > 0) {
                            $debit_note_array[] = [
                                'routable' => $debitNote->note_no ?? 0,
                                'particulars' => 'Debit Note',
                                'voucher_type' => 'Note',
                                'voucher_no' => $debitNote->note_no,
                                'amount' => $debitNote->note_value,
                                'date' => Carbon::parse($debitNote->created_at)->format('Y-m-d'),
                                'month' => Carbon::parse($debitNote->created_at)->format('m'),
                                'transaction_type' => 'debit',
                                'loop' => 'purchase_debit_note',
                                'type' => 'showable'
                            ];
                        }
                        // }
                    }


                    //fetching credit notes for this invoice
                    $creditNotes = CreditNote::where('invoice_id', $purchase->id)->where('type', 'purchase')->whereIn('reason', ['new_rate_or_discount_value_with_gst'])->get();
                    //->whereBetween('created_at', [$from_date, $to_date])

                    foreach ($creditNotes as $creditNote) {
                        // if($creditNote->reason == 'new_rate_or_discount_value_with_gst'){
                        $credit_note_array[] = [
                            'routable' => $creditNote->note_no ?? 0,
                            'particulars' => 'Credit Note',
                            'voucher_type' => 'Note',
                            'voucher_no' => $creditNote->note_no,
                            'amount' => $creditNote->note_value,
                            'date' => Carbon::parse($creditNote->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($creditNote->created_at)->format('m'),
                            'transaction_type' => 'credit',
                            'loop' => 'purchase_credit_note',
                            'type' => 'showable'
                        ];
                        // }
                    }
                }

                $query2 = PurchaseRemainingAmount::where('party_id', $party->id);

                $query2 = $query2->whereBetween('payment_date', [$from_date, $to_date]);

                $paid_amounts = $query2->get();

                foreach ($paid_amounts as $amount) {
                    // $total -= $amount->amount_paid;

                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name;
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);

                        $particulars = $this_bank->name;
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name;
                    } else {

                        $particulars = 'Cash';
                    }

                    if ($amount->amount_paid > 0) {
                        $payment_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Payment',
                            'voucher_no' => $amount->id,
                            'amount' => $amount->amount_paid,
                            'date' => Carbon::parse($amount->created_at)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->created_at)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'payment',
                            'type' => 'showable'
                        ];
                    }
                }

                $query3 = PartyPendingPaymentAccount::where('party_id', $party->id)->where('type', 'purchase');

                $query3 = $query3->whereBetween('payment_date', [$from_date, $to_date]);

                $party_paid_amounts = $query3->get();

                foreach ($party_paid_amounts as $amount) {
                    // $total -= $amount->amount;

                    if ($amount->type_of_payment == 'no_payment') {
                        continue;
                    } else if ($amount->type_of_payment == 'combined') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'pos+bank') {
                        $this_bank = Bank::find($amount->bank_id);
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_bank->name . '+' . $this_pos->name;
                    } else if ($amount->type_of_payment == 'pos+cash') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank+cash') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name . '+' . 'Cash';
                    } else if ($amount->type_of_payment == 'bank') {
                        $this_bank = Bank::find($amount->bank_id);


                        $particulars = $this_bank->name;
                    } else if ($amount->type_of_payment == 'pos') {
                        $this_pos = Bank::find($amount->pos_bank_id);

                        $particulars = $this_pos->name;
                    } else {

                        $particulars = 'Cash';
                    }

                    if ($amount->amount > 0) {
                        $party_payment_array[] = [
                            'routable' => $amount->id,
                            'particulars' => $particulars,
                            'voucher_type' => 'Payment',
                            'voucher_no' => $amount->id,
                            'amount' => $amount->amount,
                            'date' => Carbon::parse($amount->payment_date)->format('Y-m-d'),
                            'month' => Carbon::parse($amount->payment_date)->format('m'),
                            'transaction_type' => 'debit',
                            'loop' => 'purchase_party_payment',
                            'type' => 'showable'
                        ];
                    }
                }

                $query4 = PurchaseOrder::where('party_id', $party->id);

                $query4 = $query4->whereBetween('date', [$from_date, $to_date]);

                $purchase_orders = $query4->get();

                foreach ($purchase_orders as $order) {

                    if ($order->amount_received > 0) {
                        $purchase_order_array[] = [
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

                // if( count($combined_array) > 0 ) {
                //     $creditTotal = 0;
                //     $debitTotal = 0;
                //     foreach ($combined_array as $data) {

                //         if ($data['transaction_type'] == 'credit') {
                //             $creditTotal += $data['amount'];
                //         } elseif ($data['transaction_type'] == 'debit') {
                //             $debitTotal += $data['amount'];
                //         }
                //     }
                //     $creditTotal += $party->opening_balance;
                //     $combined_array['credit_total'] = $creditTotal;
                //     $combined_array['debit_total'] = $debitTotal;
                //     $combined_array['closing_total'] = $debitTotal - $creditTotal;
                // } else {
                //     $combined_array['credit_total'] = $party->opening_balance;
                //     $combined_array['debit_total'] = 0;
                //     $combined_array['closing_total'] = $party->opening_balance;
                // }

                if (count($combined_array) > 0) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $creditTotal += $party->opening_balance;

                    $party->credit_total = $creditTotal;
                    $party->debit_total = $debitTotal;
                    $party->closing_total = $debitTotal - $creditTotal;
                } else {

                    $party->credit_total = $party->opening_balance;
                    $party->debit_total = 0;
                    $party->closing_total = $party->opening_balance;
                }

                $party->combined_array = array_values($combined_array);
            }
            */

            foreach ($parties as $party) {

                // $party->opening_balance = $this->fetch_creditor_static_balance($party, $opening_balance_from_date, $opening_balance_to_date); 
                $party->opening_balance = $party->opening_balance;

                if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
                    $this_balance = $this->fetch_creditor_opening_balance($user, $party, $opening_balance_from_date, $opening_balance_to_date);
                    // return $this_balance;
                    $party->opening_balance += $this_balance;
                }

                // closing balance will only change if there is no opening balance
                // if($party->opening_balance == 0){
                    $party->opening_balance += $this->calculate_creditor_closing_balance($user, $party, $closing_balance_from_date, $closing_balance_to_date);
                // }

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
                    
                }

                $query2 = PurchaseRemainingAmount::where('party_id', $party->id)->where('is_original_payment', 0)->where('status', 1);

                $query2 = $query2->whereBetween('payment_date',[$from_date, $to_date]);

                $paid_amounts = $query2->get();

                foreach ($paid_amounts as $amount) {
                    // $total -= $amount->amount_paid;
                    
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


                $debitNotes = $user->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'purchase')
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


                $creditNotes = $user->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'purchase')->where('credit_notes.reason', 'new_rate_or_discount_value_with_gst')
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

                // return $combined_array;


                if( count($combined_array) > 0 ) {
                    $creditTotal = 0;
                    $debitTotal = 0;
                    foreach ($combined_array as $data) {

                        if ($data['transaction_type'] == 'credit') {
                            $creditTotal += $data['amount'];
                        } elseif ($data['transaction_type'] == 'debit') {
                            $debitTotal += $data['amount'];
                        }
                    }
                    $creditTotal += $party->opening_balance;
                    // $combined_array['credit_total'] = $creditTotal;
                    // $combined_array['debit_total'] = $debitTotal;
                    // $combined_array['closing_total'] = $debitTotal - $creditTotal;

                    $party->credit_total = $creditTotal;
                    $party->debit_total = $debitTotal;
                    $party->closing_total = $debitTotal - $creditTotal;
                } else {
                    // $combined_array['credit_total'] = $party->opening_balance;
                    // $combined_array['debit_total'] = 0;
                    // $combined_array['closing_total'] = $party->opening_balance;

                    $party->credit_total = $party->opening_balance;
                    $party->debit_total = 0;
                    $party->closing_total = $party->opening_balance;
                }

                $party->combined_array = $combined_array;
            }

            return $parties;

            if (count($parties) > 0) {
                return response()->json(['success' => true, 'data' => ['parties' => $parties, 'from_date' => $from_date, 'to_date' => $to_date]]);
            } else {
                return response()->json(['success' => false, 'message' => 'no_data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
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

    private function fetch_creditor_opening_balance($user, $party, $from_date, $to_date)
    {
        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date);
        // subtract a minute as we want to search till that date
        // eg if to_date is 03-04-2020 subtract 1 min will become 02-04-2020 23:59:00
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();

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

        $debitNotes = $user->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'purchase')
                ->where(function ($query) {
                    $query->where('debit_notes.reason', 'purchase_return')->orWhere('debit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('debit_notes.reason', 'discount_on_purchase');
                })
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();
            

        $creditNotes = $user->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'purchase')->where('credit_notes.reason', 'new_rate_or_discount_value_with_gst')
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

    private function calculate_creditor_closing_balance ($user, $party, $from_date, $to_date)
    {
        $from_date = \Carbon\Carbon::parse($from_date);
        $to_date = \Carbon\Carbon::parse($to_date);
        
        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }

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

        $debitNotes = $user->debitNotes()->where('debit_notes.status', 1)->where('debit_notes.type', 'purchase')
                ->where(function ($query) {
                    $query->where('debit_notes.reason', 'purchase_return')->orWhere('debit_notes.reason', 'new_rate_or_discount_value_with_gst')->orWhere('debit_notes.reason', 'discount_on_purchase');
                })
                ->whereBetween('debit_notes.note_date', [$from_date, $to_date])
                ->groupBy('debit_notes.note_no')
                ->get();
            

        $creditNotes = $user->creditNotes()->where('credit_notes.status', 1)->where('credit_notes.type', 'purchase')->where('credit_notes.reason', 'new_rate_or_discount_value_with_gst')
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

    /*
    private function calculate_creditor_opening_balance($party, $till_date)
    {
        if ($party->opening_balance != null) {
            $opening_balance = $party->opening_balance;
        } else {
            $opening_balance = 0;
        }

        $purchases = PurchaseRecord::where('party_id', $party->id)
            ->where('bill_date', '<', $till_date)
            ->get();


        $paid_amounts = PurchaseRemainingAmount::where('party_id', $party->id)
            ->where('payment_date', '<', $till_date)
            ->get();


        $party_paid_amounts = PartyPendingPaymentAccount::where('party_id', $party->id)
            ->where('type', 'purchase')
            ->where('payment_date', '<', $till_date)
            ->get();


        foreach ($purchases as $purchase) {
            $opening_balance += $purchase->total_amount;

            $debitNotes = DebitNote::where('bill_no', $purchase->id)->whereIn('reason', ['purchase_return', 'discount_on_purchase', 'other'])->where('type', 'purchase')->get();

            $creditNotes = CreditNote::where('invoice_id', $purchase->id)->whereIn('reason', ['other'])->where('type', 'purchase')->get();

            foreach ($debitNotes as $debitNote) {
                $opening_balance -= $debitNote->note_value;
            }

            foreach ($creditNotes as $creditNote) {
                $opening_balance += $creditNote->note_value;
            }
        }

        foreach ($paid_amounts as $amount) {
            $opening_balance -= $amount->amount_paid;
        }

        foreach ($party_paid_amounts as $amount) {
            $opening_balance -= $amount->amount;
        }

        return $opening_balance;
    }
    */

    // public function get_bank_document( Request $request )
    // {
    //
    //     if(request( 'ukey') === 'lO6MXIteuPhdBIg5mg8WzfnJ2XdqfyNw') {
    //         $uploaded_bills = UploadedBill::where('user_id', $request->user_id)->where('type', $request->type)->where('status', 0)->orderBy('created_at', 'desc')->get();
    //
    //         return response()->json( $uploaded_bills );
    //
    //         if( count( $uploaded_bills ) > 0) {
    //             return response()->json( $uploaded_bills );
    //         } else {
    //             return response()->json(['message' => 'no_data', 'data' => null], 200);
    //         }
    //     } else {
    //         return response()->json(['message' => 'invalid_key', 'data' => null], 401);
    //     }
    //
    // }
    //
    // public function delete_bank_document(Request $request)
    // {
    //
    //     if(request('ukey') === 'YNh14ZQrOje9cyrGwXJlRALnTpfAj4xu') {
    //         $uploaded_bill = UploadedBill::find($request->id);
    //
    //         if( $uploaded_bill->delete() ) {
    //             return response()->json(['message' => 'data_deleted', 'data' => null], 200);
    //         } else {
    //             return response()->json(['message' => 'failed_to_delete_data', 'data' => null], 200);
    //         }
    //     } else {
    //         return response()->json(['message' => 'invalid_key', 'data' => null], 401);
    //     }
    //
    // }

    public function cash_withdrawn(Request $request)
    {

        // $this->validate($request, [
        //     'amount' => 'required',
        //     'date' => 'required|date',
        //     'document' => 'nullable|file',
        //     'ukey' => 'required|alpha_num',
        // ]);


        if ($request->ukey == 'M07UxBRuIm1PJeTZWw0PpoUv1bJK24hI') {

            $cash_withdraw = new CashWithdraw;

            // $document_saved = false;
            // $data_saved = false;

            // if ( $request->hasfile('document') ) {

            //     $path = Storage::disk('public')->putFile('cash_withdrawn', $request->document);

            //     $cash_withdraw_document = new CashWithdraw;

            //     $cash_withdraw_document->document = $path;
            //     $cash_withdraw_document->user_id = $request->user_id;
            //     $cash_withdraw_document->date = $date;
            //     // $cash_withdraw_document->status = 0;

            //     $cash_withdraw_document->save();

            //     $document_saved = true;
            // }


            // if ($request->has('amount')) {
            //     $cash_withdraw->amount = $request->amount;
            //     $cash_withdraw->date = $date;
            //     $cash_withdraw->bank = $request->bank;
            //     $cash_withdraw->contra = $request->contra;
            //     $cash_withdraw->narration = $request->narration;
            //     $cash_withdraw->user_id = $request->user_id;

            //     $cash_withdraw->save();

            //     $data_saved = true;
            // }

            if ($request->hasfile('document')) {
                $path = Storage::disk('public')->putFile('cash_withdrawn', $request->document);

                $cash_withdraw->document = $path;
            }

            if ($request->has('amount')) {
                $cash_withdraw->amount = $request->amount;
            }

            if ($request->has('date')) {
                $date = date('Y-m-d', strtotime($request->date));
                $cash_withdraw->date = $date;
            }

            if ($request->has('bank')) {
                $cash_withdraw->bank = $request->bank;
            }

            if ($request->has('contra')) {
                $cash_withdraw->contra = $request->contra;
            }

            if ($request->has('narration')) {
                $cash_withdraw->narration = $request->narration;
            }

            $cash_withdraw->user_id = $request->user_id;

            $cash_withdraw->created_on = 'mobile';

            if ($cash_withdraw->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function cash_deposited(Request $request)
    {

        // $this->validate($request, [
        //     'amount' => 'required',
        //     'date' => 'required|date',
        //     'document' => 'nullable|file',
        //     'ukey' => 'required|alpha_num',
        // ]);



        if ($request->ukey == 'fDwvNoiXN0B5QhuLYluTFoP6nzZ6Ti2S') {
            $cash_deposit = new CashDeposit;

            // $date = date('Y-m-d', strtotime($request->date));

            // $document_saved = false;
            // $data_saved = false;

            // if ($request->hasfile('document')) {

            //     $path = Storage::disk('public')->putFile('cash_deposited', $request->document);

            //     $cash_deposit_document = new CashDeposit;

            //     $cash_deposit_document->user_id = $request->user_id;
            //     $cash_deposit_document->date = $date;
            //     $cash_deposit_document->document = $path;
            //     // $cash_deposit_document->status = 0;

            //     $cash_deposit_document->save();

            //     $document_saved = true;
            // }

            // if( $request->has('amount') ){
            //     $cash_deposit->amount = $request->amount;
            //     $cash_deposit->date = $date;
            //     $cash_deposit->bank = $request->bank;
            //     $cash_deposit->contra = $request->contra;
            //     $cash_deposit->narration = $request->narration;
            //     $cash_deposit->user_id = $request->user_id;

            //     $cash_deposit->save();

            //     $data_saved = true;
            // }

            if ($request->hasfile('document')) {
                $path = Storage::disk('public')->putFile('cash_deposited', $request->document);

                $cash_deposit->document = $path;
            }

            if ($request->has('amount')) {
                $cash_deposit->amount = $request->amount;
            }

            if ($request->has('date')) {
                $date = date('Y-m-d', strtotime($request->date));
                $cash_deposit->date = $date;
            }

            if ($request->has('bank')) {
                $cash_deposit->bank = $request->bank;
            }

            if ($request->has('contra')) {
                $cash_deposit->contra = $request->contra;
            }

            if ($request->has('narration')) {
                $cash_deposit->narration = $request->narration;
            }

            $cash_deposit->user_id = $request->user_id;

            $cash_deposit->created_on = 'mobile';

            if ($cash_deposit->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }


    public function purchase_pending_payment(Request $request)
    {

        if ($request->ukey == 'ZONwCKjkpfI8o1dRu5QrXLGuZcniB1OO') {
            $purchase_remaining_amount = new PurchaseRemainingAmount;

            $purchase_remaining_amount->party_id = $request->party_id;
            $purchase_remaining_amount->purchase_id = $request->bill_no;
            $purchase_remaining_amount->total_amount = $request->total_amount;
            $purchase_remaining_amount->amount_paid = $request->amount_paid;

            if ($request->has('tds_income_tax')) {
                $purchase_remaining_amount->tds_income_tax = $request->tds_income_tax;
            } else {
                $purchase_remaining_amount->tds_income_tax = 0;
            }

            if ($request->has('tds_gst')) {
                $purchase_remaining_amount->tds_gst = $request->tds_gst;
            } else {
                $purchase_remaining_amount->tds_gst = 0;
            }

            if ($request->has('tcs_income_tax')) {
                $purchase_remaining_amount->tcs_income_tax = $request->tcs_income_tax;
            } else {
                $purchase_remaining_amount->tcs_income_tax = 0;
            }

            if ($request->has('tcs_gst')) {
                $purchase_remaining_amount->tcs_gst = $request->tcs_gst;
            } else {
                $purchase_remaining_amount->tcs_income_tax = 0;
            }

            $purchase_remaining_amount->amount_remaining = $request->amount_remaining;

            $purchase_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

            // $purchase_remaining_amount->type_of_payment = $request->type_of_payment;
            // if($request->type_of_payment == 'bank'){
            //     $purchase_remaining_amount->bank_id = $request->bank_id;
            //     $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
            //     $purchase_remaining_amount->bank_payment = $request->amount_paid;
            // }

            // if ($request->type_of_payment == 'cash') {
            //     $purchase_remaining_amount->cash_payment = $request->amount_paid;
            // }

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
                    $purchase_remaining_amount->type_of_payment = 'combined';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->bank_payment = $request->banked_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                } else if ($pos && $bank) {
                    $purchase_remaining_amount->type_of_payment = 'pos+bank';

                    $purchase_remaining_amount->bank_payment = $request->banked_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                } else if ($pos && $cash) {
                    $purchase_remaining_amount->type_of_payment = 'pos+cash';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                } else if ($bank && $cash) {
                    $purchase_remaining_amount->type_of_payment = 'bank+cash';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                    $purchase_remaining_amount->bank_payment = $request->banked_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                } else if ($bank) {
                    $purchase_remaining_amount->type_of_payment = 'bank';

                    $purchase_remaining_amount->bank_payment = $request->banked_amount;

                    $purchase_remaining_amount->bank_id = $request->bank;
                    $purchase_remaining_amount->bank_cheque = $request->bank_cheque;
                } else if ($cash) {
                    $purchase_remaining_amount->type_of_payment = 'cash';

                    $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                } else if ($pos) {
                    $purchase_remaining_amount->type_of_payment = 'pos';

                    $purchase_remaining_amount->pos_payment = $request->posed_amount;

                    $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
                }
            } else {
                $purchase_remaining_amount->type_of_payment = 'no_payment';
            }

            $purchase_remaining_amount->created_on = 'mobile';

            if ($purchase_remaining_amount->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function sale_pending_payment(Request $request)
    {

        if ($request->ukey == 'Gydxhl50Xf6P8aQjoniQDyzH1xJIkc8N') {
            $sale_remaining_amount = new SaleRemainingAmount;

            $sale_remaining_amount->party_id = $request->party_id;
            $sale_remaining_amount->invoice_id = $request->invoice_id;
            $sale_remaining_amount->total_amount = $request->total_amount;
            $sale_remaining_amount->amount_paid = $request->amount_paid;

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

            $sale_remaining_amount->amount_remaining = $request->amount_remaining;

            $sale_remaining_amount->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

            // $sale_remaining_amount->type_of_payment = $request->type_of_payment;
            // if ($request->type_of_payment == 'bank') {
            //     $sale_remaining_amount->bank_id = $request->bank_id;
            //     $sale_remaining_amount->bank_cheque = $request->bank_cheque;
            //     $sale_remaining_amount->bank_payment = $request->amount_paid;
            // }

            // if ($request->type_of_payment == 'cash') {
            //     $sale_remaining_amount->cash_payment = $request->amount_paid;
            // }

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
                    $sale_remaining_amount->type_of_payment = 'combined';

                    $sale_remaining_amount->cash_payment = $request->cashed_amount;
                    $sale_remaining_amount->bank_payment = $request->banked_amount;
                    $sale_remaining_amount->pos_payment = $request->posed_amount;

                    $sale_remaining_amount->bank_id = $request->bank;
                    $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                    $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                } else if ($pos && $bank) {
                    $sale_remaining_amount->type_of_payment = 'pos+bank';

                    $sale_remaining_amount->bank_payment = $request->banked_amount;
                    $sale_remaining_amount->pos_payment = $request->posed_amount;

                    $sale_remaining_amount->bank_id = $request->bank;
                    $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                    $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                } else if ($pos && $cash) {
                    $sale_remaining_amount->type_of_payment = 'pos+cash';

                    $sale_remaining_amount->cash_payment = $request->cashed_amount;
                    $sale_remaining_amount->pos_payment = $request->posed_amount;

                    $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                } else if ($bank && $cash) {
                    $sale_remaining_amount->type_of_payment = 'bank+cash';

                    $sale_remaining_amount->cash_payment = $request->cashed_amount;
                    $sale_remaining_amount->bank_payment = $request->banked_amount;

                    $sale_remaining_amount->bank_id = $request->bank;
                    $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                } else if ($bank) {
                    $sale_remaining_amount->type_of_payment = 'bank';

                    $sale_remaining_amount->bank_payment = $request->banked_amount;

                    $sale_remaining_amount->bank_id = $request->bank;
                    $sale_remaining_amount->bank_cheque = $request->bank_cheque;
                } else if ($cash) {
                    $sale_remaining_amount->type_of_payment = 'cash';

                    $sale_remaining_amount->cash_payment = $request->cashed_amount;
                } else if ($pos) {
                    $sale_remaining_amount->type_of_payment = 'pos';

                    $sale_remaining_amount->pos_payment = $request->posed_amount;

                    $sale_remaining_amount->pos_bank_id = $request->pos_bank;
                }
            } else {
                $sale_remaining_amount->type_of_payment = 'no_payment';
            }

            $sale_remaining_amount->created_on = 'mobile';

            if ($sale_remaining_amount->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function sale_party_pending_payment(Request $request)
    {

        if ($request->ukey == 'v1ka0UUN0VX6rtQRshItP5psLdOgXp4W') {
            $pending_payment = new PartyPendingPaymentAccount;

            $pending_payment->party_id = $request->party_id;
            $pending_payment->amount = $request->amount;

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

            if ($request->has('tds_income_tax_amount')) {
                $pending_payment->tds_income_tax_checked = 1;
                $pending_payment->tds_income_tax_amount = $request->tds_income_tax;
            } else {
                $pending_payment->tds_income_tax_checked = 0;
                $pending_payment->tds_income_tax_amount = 0;
            }

            if ($request->has('tds_gst_amount')) {
                $pending_payment->tds_gst_checked = 1;
                $pending_payment->tds_gst_amount = $request->tds_gst;
            } else {
                $pending_payment->tds_gst_checked = 0;
                $pending_payment->tds_gst_amount = 0;
            }

            if ($request->has('tcs_income_tax_amount')) {
                $pending_payment->tcs_income_tax_checked = 1;
                $pending_payment->tcs_income_tax_amount = $request->tcs_income_tax;
            } else {
                $pending_payment->tcs_income_tax_checked = 0;
                $pending_payment->tcs_income_tax_amount = 0;
            }

            if ($request->has('tcs_gst_amount')) {
                $pending_payment->tcs_gst_checked = 1;
                $pending_payment->tcs_gst_amount = $request->tcs_gst;
            } else {
                $pending_payment->tcs_gst_checked = 0;
                $pending_payment->tcs_gst_amount = 0;
            }

            $pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

            $pending_payment->remarks = $request->remarks;

            // $pending_payment->type_of_payment = $request->type_of_payment;

            // if ($request->type_of_payment == 'bank') {
            //     $pending_payment->bank_cheque = $request->bank_cheque;
            //     $pending_payment->bank_id = $request->bank;
            // }

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
                    $pending_payment->type_of_payment = 'combined';

                    $pending_payment->cash_payment = $request->cashed_amount;
                    $pending_payment->bank_payment = $request->banked_amount;
                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                    $pending_payment->pos_bank_id = $request->pos_bank;
                } else if ($pos && $bank) {
                    $pending_payment->type_of_payment = 'pos+bank';

                    $pending_payment->bank_payment = $request->banked_amount;
                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                    $pending_payment->pos_bank_id = $request->pos_bank;
                } else if ($pos && $cash) {
                    $pending_payment->type_of_payment = 'pos+cash';

                    $pending_payment->cash_payment = $request->cashed_amount;
                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->pos_bank_id = $request->pos_bank;
                } else if ($bank && $cash) {
                    $pending_payment->type_of_payment = 'bank+cash';

                    $pending_payment->cash_payment = $request->cashed_amount;
                    $pending_payment->bank_payment = $request->banked_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                } else if ($bank) {
                    $pending_payment->type_of_payment = 'bank';

                    $pending_payment->bank_payment = $request->banked_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                } else if ($cash) {
                    $pending_payment->type_of_payment = 'cash';

                    $pending_payment->cash_payment = $request->cashed_amount;
                } else if ($pos) {
                    $pending_payment->type_of_payment = 'pos';

                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->pos_bank_id = $request->pos_bank;
                }
            } else {
                $pending_payment->type_of_payment = 'no_payment';
            }

            $pending_payment->type = "sale";

            $pending_payment->created_on = 'mobile';

            if ($pending_payment->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function purchase_party_pending_payment(Request $request)
    {

        if ($request->ukey == 'oqYfjmOBESOhcncQzOduUaLEdiy5kyug') {
            $pending_payment = new PartyPendingPaymentAccount;

            $pending_payment->party_id = $request->party_id;
            $pending_payment->amount = $request->amount;

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

            if ($request->has('tds_income_tax_amount')) {
                $pending_payment->tds_income_tax_checked = 1;
                $pending_payment->tds_income_tax_amount = $request->tds_income_tax;
            } else {
                $pending_payment->tds_income_tax_checked = 0;
                $pending_payment->tds_income_tax_amount = 0;
            }

            if ($request->has('tds_gst_amount')) {
                $pending_payment->tds_gst_checked = 1;
                $pending_payment->tds_gst_amount = $request->tds_gst;
            } else {
                $pending_payment->tds_gst_checked = 0;
                $pending_payment->tds_gst_amount = 0;
            }

            if ($request->has('tcs_income_tax_amount')) {
                $pending_payment->tcs_income_tax_checked = 1;
                $pending_payment->tcs_income_tax_amount = $request->tcs_income_tax;
            } else {
                $pending_payment->tcs_income_tax_checked = 0;
                $pending_payment->tcs_income_tax_amount = 0;
            }

            if ($request->has('tcs_gst_amount')) {
                $pending_payment->tcs_gst_checked = 1;
                $pending_payment->tcs_gst_amount = $request->tcs_gst;
            } else {
                $pending_payment->tcs_gst_checked = 0;
                $pending_payment->tcs_gst_amount = 0;
            }

            $pending_payment->payment_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->payment_date)));

            $pending_payment->remarks = $request->remarks;

            // $pending_payment->type_of_payment = $request->type_of_payment;

            // if ($request->type_of_payment == 'bank') {
            //     $pending_payment->bank_cheque = $request->bank_cheque;
            //     $pending_payment->bank_id = $request->bank;
            // }

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
                    $pending_payment->type_of_payment = 'combined';

                    $pending_payment->cash_payment = $request->cashed_amount;
                    $pending_payment->bank_payment = $request->banked_amount;
                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                    $pending_payment->pos_bank_id = $request->pos_bank;
                } else if ($pos && $bank) {
                    $pending_payment->type_of_payment = 'pos+bank';

                    $pending_payment->bank_payment = $request->banked_amount;
                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                    $pending_payment->pos_bank_id = $request->pos_bank;
                } else if ($pos && $cash) {
                    $pending_payment->type_of_payment = 'pos+cash';

                    $pending_payment->cash_payment = $request->cashed_amount;
                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->pos_bank_id = $request->pos_bank;
                } else if ($bank && $cash) {
                    $pending_payment->type_of_payment = 'bank+cash';

                    $pending_payment->cash_payment = $request->cashed_amount;
                    $pending_payment->bank_payment = $request->banked_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                } else if ($bank) {
                    $pending_payment->type_of_payment = 'bank';

                    $pending_payment->bank_payment = $request->banked_amount;

                    $pending_payment->bank_id = $request->bank;
                    $pending_payment->bank_cheque = $request->bank_cheque;
                } else if ($cash) {
                    $pending_payment->type_of_payment = 'cash';

                    $pending_payment->cash_payment = $request->cashed_amount;
                } else if ($pos) {
                    $pending_payment->type_of_payment = 'pos';

                    $pending_payment->pos_payment = $request->posed_amount;

                    $pending_payment->pos_bank_id = $request->pos_bank;
                }
            } else {
                $pending_payment->type_of_payment = 'no_payment';
            }

            $pending_payment->type = "purchase";

            $pending_payment->created_on = 'mobile';

            if ($pending_payment->save()) {
                return response()->json(['message' => 'success', 'data' => null], 200);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function gst_return(Request $request)
    {
        if ($request->ukey == '3DxzOcdHBcdIdZBYSRzctM0stHpVJ1Yq') {
            // SELECT invoice_id, gst_rate, SUM(gst), SUM(cess) FROM invoice_item GROUP BY gst_rate, invoice_id

            if (isset($request->from) && isset($request->to)) {
                $from = date('Y-m-d', strtotime($request->from));
                $to = date('Y-m-d', strtotime($request->to));

                $invoices = User::findOrFail($request->user_id)->invoices()->whereBetween('invoice_date', [$from, $to])->get();
            } else {
                $invoices = User::findOrFail($request->user_id)->invoices()->get();
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

            if (count($invoice_items) > 0) {
                return response()->json($invoice_items);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function combined_data(Request $request)
    {
        if ($request->ukey === 'LaFRjFsoFicXDlYq2KDGAk6rwTSneTZq') {
            $user = null;

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }

            $user_profile = UserProfile::where('user_id', $user->id)->first();

            $invoices = User::find($user->id)->invoices()->get();
            $sale_data = array(
                'total_amount_sale' => 0,
                'total_sale' => count($invoices),
                'total_sale_tax' => 0,
                'total_igst' => 0,
                'total_sgst' => 0,
                'total_cgst' => 0
            );

            foreach ($invoices as $invoice) {
                $sale_data['total_amount_sale'] += $invoice->total_amount;
                $sale_data['total_sale_tax'] += $invoice->gst;
                $sale_data['total_igst'] += $invoice->igst;
                $sale_data['total_sgst'] += $invoice->sgst;
                $sale_data['total_cgst'] += $invoice->cgst;
            }

            $bills = User::find($user->id)->purchases()->get();
            $purchase_data = array(
                'total_amount_purchase' => 0,
                'total_purchase' => count($bills),
                'total_purchase_tax' => 0,
                'total_igst' => 0,
                'total_sgst' => 0,
                'total_cgst' => 0
            );

            foreach ($bills as $bill) {
                $purchase_data['total_amount_purchase'] += $bill->item_total_amount;
                $purchase_data['total_purchase_tax'] += $bill->item_total_gst;
                $purchase_data['total_igst'] += $bill->igst;
                $purchase_data['total_sgst'] += $bill->sgst;
                $purchase_data['total_cgst'] += $bill->cgst;
            }


            $parties = Party::where('user_id', $user->id)->get();

            $receipt = array(
                'amount_paid' => 0
            );

            $payment = array(
                'amount_paid' => 0
            );

            foreach ($parties as $party) {

                $sale_remaining_amounts = SaleRemainingAmount::where('party_id', $party->id)->get();

                foreach ($sale_remaining_amounts as $amount) {
                    $receipt['amount_paid'] += $amount->amount_paid;
                }

                $purchase_remaining_amounts = PurchaseRemainingAmount::where('party_id', $party->id)->get();

                foreach ($purchase_remaining_amounts as $amount) {
                    $payment['amount_paid'] += $amount->amount_paid;
                }
            }

            $latest_debtors = Party::where('user_id', $user->id)->where('balance_type', 'debitor')->orderBy('id', 'desc')->take(5)->get();

            $latest_creditors = Party::where('user_id', $user->id)->where('balance_type', 'creditor')->orderBy('id', 'desc')->take(5)->get();

            $latest_invoices = User::findOrFail($user->id)->invoices()->orderBy('id', 'desc')->take(5)->get();
            $latest_bills = User::find($user->id)->purchases()->orderBy('id', 'desc')->take(5)->get();

            $most_active_items = DB::table('invoice_item')
                ->select(DB::raw('item_id'), DB::raw('sum(item_qty) as qty'))
                ->groupBy(DB::raw('item_id'))
                ->orderBy('qty', 'desc')
                ->take(5)
                ->get();


            foreach ($most_active_items as $item) {
                $current_item = Item::findOrFail($item->item_id);

                $item->name = $current_item->name;
            }

            $from = $user_profile->financial_year_from ?? null;
            $to = $user_profile->financial_year_to ?? null;

            $bank_balance = 0;
            $cash_balance = 0;
            $cash_deposit = 0;
            $cash_withdrawn = 0;

            $cd = CashDeposit::where('user_id', $user->id);
            if ($from && $to) {
                $cd = $cd->whereBetween('date', [$from, $to]);
            }
            $cash_deposits = $cd->get();

            foreach ($cash_deposits as $deposit) {
                $cash_deposit += $deposit->amount;
                $cash_balance -= $deposit->amount;
                $bank_balance += $deposit->amount;
            }

            $cw = CashWithdraw::where('user_id', $user->id);
            if ($from && $to) {
                $cw = $cw->whereBetween('date', [$from, $to]);
            }
            $cash_withdraws = $cw->get();

            foreach ($cash_withdraws as $withdraw) {
                $cash_withdrawn += $withdraw->amount;
                $cash_balance += $withdraw->amount;
                $bank_balance -= $withdraw->amount;
            }

            $userAllInvoices = User::findOrFail($user->id)->invoices()->get();
            $userAllPurchases = User::findOrFail($user->id)->purchases()->get();
            $userAllPurchaseRemainingAmounts = User::find($user->id)->purchaseRemainingAmounts()->get();
            $userAllSaleRemainingAmounts = User::find($user->id)->saleRemainingAmounts()->get();
            $userAllSaleOrder = SaleOrder::where('user_id', $user->id)->get();
            $userAllPurchaseOrder = PurchaseOrder::where('user_id', $user->id)->get();
            $userAllCashGST = CashGST::where('user_id', $user->id)->get();
            $userAllSalePartyRemainingAmount = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->get();
            $userAllPurchasePartyRemainingAmount = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->get();
            $userAllGSTSetoff = GSTSetOff::where('user_id', $user->id)->get();

            foreach ($userAllInvoices as $invoice) {
                $bank_balance += $invoice->bank_payment;
                $cash_balance += $invoice->cash_payment;
            }

            foreach ($userAllPurchases as $purchase) {
                $bank_balance -= $purchase->bank_payment;
                $cash_balance -= $purchase->cash_payment;
            }

            foreach ($userAllPurchaseRemainingAmounts as $amount) {
                $bank_balance -= $amount->bank_payment;
                $cash_balance -= $amount->cash_payment;
            }

            foreach ($userAllSaleRemainingAmounts as $amount) {
                $bank_balance += $amount->bank_payment;
                $cash_balance += $amount->cash_payment;
            }

            foreach ($userAllSaleOrder as $order) {
                $bank_balance += $order->bank_amount;
                $cash_balance += $order->cash_amount;
            }

            foreach ($userAllPurchaseOrder as $order) {
                $bank_balance -= $order->bank_amount;
                $cash_balance -= $order->cash_amount;
            }

            foreach ($userAllCashGST as $gst) {
                $bank_balance -= $gst->bank_amount;
                $cash_balance -= $gst->cash_amount;
            }

            foreach ($userAllSalePartyRemainingAmount as $amount) {
                $bank_balance += $amount->bank_payment;
                $cash_balance += $amount->cash_payment;
            }

            foreach ($userAllPurchasePartyRemainingAmount as $amount) {
                $bank_balance -= $amount->bank_payment;
                $cash_balance -= $amount->cash_payment;
            }

            foreach ($userAllGSTSetoff as $gst) {
                $bank_balance -= $gst->bank_payment;
                $cash_balance -= $gst->cash_payment;
            }


            $itemsWithNegQty = Item::where('user_id', $user->id)->where('qty', '<', 0)->get();


            // $sale_stock['jan'] = $this->sale_stock('01', Carbon::parse($user_profile->financial_year_to)->format('Y'));
            // $sale_stock['feb'] = $this->sale_stock('02', Carbon::parse($user_profile->financial_year_to)->format('Y'));
            // $sale_stock['mar'] = $this->sale_stock('03', Carbon::parse($user_profile->financial_year_to)->format('Y'));
            // $sale_stock['apr'] = $this->sale_stock('04', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['may'] = $this->sale_stock('05', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['jun'] = $this->sale_stock('06', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['jul'] = $this->sale_stock('07', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['aug'] = $this->sale_stock('08', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['sep'] = $this->sale_stock('09', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['oct'] = $this->sale_stock('10', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['nov'] = $this->sale_stock('11', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $sale_stock['dec'] = $this->sale_stock('12', Carbon::parse($user_profile->financial_year_from)->format('Y'));

            // $purchase_stock['jan'] = $this->purchase_stock('01', Carbon::parse($user_profile->financial_year_to)->format('Y'));
            // $purchase_stock['feb'] = $this->purchase_stock('02', Carbon::parse($user_profile->financial_year_to)->format('Y'));
            // $purchase_stock['mar'] = $this->purchase_stock('03', Carbon::parse($user_profile->financial_year_to)->format('Y'));
            // $purchase_stock['apr'] = $this->purchase_stock('04', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['may'] = $this->purchase_stock('05', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['jun'] = $this->purchase_stock('06', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['jul'] = $this->purchase_stock('07', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['aug'] = $this->purchase_stock('08', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['sep'] = $this->purchase_stock('09', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['oct'] = $this->purchase_stock('10', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['nov'] = $this->purchase_stock('11', Carbon::parse($user_profile->financial_year_from)->format('Y'));
            // $purchase_stock['dec'] = $this->purchase_stock('12', Carbon::parse($user_profile->financial_year_from)->format('Y'));

            if ($this->stock_in_hand($user->id)) {
                $stock_values = $this->stock_in_hand($user->id) ?? 0;
            } else {
                $stock_values = 0;
            }

            return response()->json(['cash' => $cash_balance, 'bank' => $bank_balance, 'customer' => $receipt, 'supplier' => $payment, 'stock_in_hand' => $stock_values, 'sale' => $sale_data['total_amount_sale'], 'purchase' => $purchase_data['total_amount_purchase']]);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    // public function combined_data(Request $request)
    // {
    //     if( $request->ukey === 'LaFRjFsoFicXDlYq2KDGAk6rwTSneTZq' ){

    //         if( $this->cash_in_hand($request->user_id) ){
    //             $cash_in_hand = $this->cash_in_hand($request->user_id) ?? 0;
    //         } else {
    //             $cash_in_hand = 0;
    //         }

    //         if ( $this->bank_amount($request->user_id) ) {
    //             $bank_amount = $this->bank_amount($request->user_id) ?? 0;
    //         } else {
    //             $bank_amount = 0;
    //         }

    //         if ( $this->sale_amount($request->user_id) ) {
    //             $sale_amount = $this->sale_amount($request->user_id) ?? 0;
    //         } else {
    //             $sale_amount = 0;
    //         }

    //         if ( $this->purchase_amount($request->user_id) ) {
    //             $purchase_amount = $this->purchase_amount($request->user_id) ?? 0;
    //         } else {
    //             $purchase_amount = 0;
    //         }

    //         if ( $this->stock_in_hand($request->user_id) ) {
    //             $stock_values = $this->stock_in_hand($request->user_id) ?? 0;
    //         } else {
    //             $stock_values = 0;
    //         }

    //         return response()->json(['cash' => $cash_in_hand, 'bank' => $bank_amount, 'customer' => $sale_amount, 'supplier' => $purchase_amount, 'stock in hand' => $stock_values]);
    //     } else {
    //         return response()->json(['message' => 'invalid_key', 'data' => null], 401);
    //     }
    // }

    private function stock_in_hand($user_id)
    {
        $items = Item::where('user_id', $user_id)->get();
        $items_value = 0;

        if ($items != null) {
            foreach ($items as $item) {
                $items_value += $item->qty * $item->opening_stock_rate;
            }
        }

        return $items_value;
    }

    private function purchase_amount($user_id)
    {
        $purchases = User::find($user_id)->purchases()->where('type_of_bill', 'regular')->get();
        $total_amount = 0;

        if ($purchases != null) {
            foreach ($purchases as $purchase) {
                $total_amount += $purchase->total_amount;
            }
        }

        return $total_amount;
    }

    private function sale_amount($user_id)
    {
        $sales = User::find($user_id)->invoices()->where('type_of_bill', 'regular')->get();
        $total_amount = 0;

        if ($sales != null) {
            foreach ($sales as $sale) {
                $total_amount += $sale->total_amount;
            }
        }

        return $total_amount;
    }

    private function cash_in_hand($user_id)
    {

        $opening_balance_date = date('Y-m-d', time());

        $till_date = $opening_balance_date;

        $opening_balance = $this->calculate_cash_opening_balance($user_id, $till_date);

        $sales = User::findOrFail($user_id)->invoices()->where('invoice_date', '<=', $till_date)->get();

        $purchases = User::find($user_id)->purchases()->where('bill_date', '<=', $till_date)->get();

        $payments = User::find($user_id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.created_at', '<=', $till_date)->get();

        $receipts = User::find($user_id)->saleRemainingAmounts()->where('sale_remaining_amounts.created_at', '<=', $till_date)->get();

        $sale_orders = SaleOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $gst_payments = CashGST::where('user_id', $user_id)->where('created_at', '<=', $till_date)->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $cash_deposited = CashDeposit::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $sale_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();

        $purchase_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();

        if ($receipts != null) {
            foreach ($receipts as $receipt) {
                $opening_balance += $receipt->cash_payment;
            }
        }

        if ($payments != null) {
            foreach ($payments as $payment) {
                $opening_balance -= $payment->cash_payment;
            }
        }

        // --------------------------------

        if ($sales != null) {
            foreach ($sales as $sale) {
                $opening_balance += $sale->cash_payment;
            }
        }


        if ($sale_orders != null) {
            foreach ($sale_orders as $order) {
                $opening_balance += $order->cash_amount;
            }
        }


        if ($purchases != null) {
            foreach ($purchases as $purchase) {
                $opening_balance -= $purchase->cash_payment;
            }
        }


        if ($purchase_orders != null) {
            foreach ($purchase_orders as $order) {
                $opening_balance -= $order->cash_amount;
            }
        }

        if ($gst_payments != null) {
            foreach ($gst_payments as $payment) {
                $opening_balance -= $payment->cash_amount;
            }
        }


        if ($cash_withdrawn != null) {
            foreach ($cash_withdrawn as $withdrawn) {
                $opening_balance += $withdrawn->amount;
            }
        }

        if ($cash_deposited != null) {
            foreach ($cash_deposited as $deposited) {
                $opening_balance -= $deposited->amount;
            }
        }

        if ($sale_party_payments != null) {
            foreach ($sale_party_payments as $sale) {
                $opening_balance += $sale->amount;
            }
        }

        if ($purchase_party_payments != null) {
            foreach ($purchase_party_payments as $payment) {
                $opening_balance -= $payment->amount;
            }
        }

        return $opening_balance;
    }

    private function calculate_cash_opening_balance($user_id, $till_date = null)
    {

        $opening_balance = 0;

        $cash_in_hand = CashInHand::where('user_id', $user_id)->first();


        if ($cash_in_hand != null) {
            if ($cash_in_hand->balance_type == 'creditor') {
                $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
            } else {
                $fixed_opening_balance = $cash_in_hand->opening_balance;
            }

            if ($till_date == null) {
                return $fixed_opening_balance;
            } else {
                if ($cash_in_hand->balance_date <= $till_date) {
                    $opening_balance += $fixed_opening_balance;
                }
            }
        }

        $sales = User::findOrFail($user_id)->invoices()->where('invoice_date', '<=', $till_date)->get();

        $purchases = User::find($user_id)->purchases()->where('bill_date', '<=', $till_date)->get();

        $payments = User::find($user_id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.created_at', '<=', $till_date)->get();

        $receipts = User::find($user_id)->saleRemainingAmounts()->where('sale_remaining_amounts.created_at', '<=', $till_date)->get();

        $sale_orders = SaleOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $gst_payments = CashGST::where('user_id', $user_id)->where('created_at', '<=', $till_date)->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $cash_deposited = CashDeposit::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $sale_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();

        $purchase_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'cash')->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();


        foreach ($sales as $sale) {
            $opening_balance += $sale->cash_payment;
        }

        foreach ($receipts as $receipt) {
            $opening_balance += $receipt->cash_payment;
        }

        foreach ($sale_orders as $order) {
            $opening_balance += $order->cash_amount;
        }


        foreach ($purchases as $purchase) {
            $opening_balance -= $purchase->cash_payment;
        }

        foreach ($payments as $payment) {
            $opening_balance -= $payment->cash_payment;
        }

        foreach ($purchase_orders as $order) {
            $opening_balance -= $order->cash_amount;
        }

        foreach ($gst_payments as $payment) {
            $opening_balance -= $payment->cash_amount;
        }


        foreach ($cash_withdrawn as $withdrawn) {
            $opening_balance += $withdrawn->amount;
        }

        foreach ($cash_deposited as $deposited) {
            $opening_balance -= $deposited->amount;
        }

        foreach ($sale_party_payments as $sale) {
            $opening_balance += $sale->amount;
        }

        foreach ($purchase_party_payments as $payment) {
            $opening_balance -= $payment->amount;
        }

        return $opening_balance;
    }

    private function bank_amount($user_id)
    {
        $opening_balance_date = date('Y-m-d', time());

        $till_date = $opening_balance_date;

        $opening_balance = $this->calculate_bank_opening_balance($user_id, $till_date);

        $sales = User::findOrFail($user_id)->invoices()->where('invoice_date', '<=', $till_date)->get();

        $purchases = User::find($user_id)->purchases()->where('bill_date', '<=', $till_date)->get();

        $payments = User::find($user_id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.created_at', '<=', $till_date)->get();

        $receipts = User::find($user_id)->saleRemainingAmounts()->where('sale_remaining_amounts.created_at', '<=', $till_date)->get();

        $sale_orders = SaleOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        // $gst_payments = CashGST::where('user_id', Auth::user()->id)->where('created_at', '<=', $till_date)->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $cash_deposited = CashDeposit::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $sale_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'bank')->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();

        $purchase_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'bank')->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();

        foreach ($receipts as $receipt) {
            $opening_balance += $receipt->cash_payment;
        }

        foreach ($payments as $payment) {
            $opening_balance -= $payment->cash_payment;
        }

        foreach ($sales as $sale) {
            $opening_balance += $sale->bank_payment;
        }

        foreach ($receipts as $receipt) {
            $opening_balance += $receipt->bank_payment;
        }

        foreach ($sale_orders as $order) {
            $opening_balance += $order->bank_amount;
        }


        foreach ($purchases as $purchase) {
            $opening_balance -= $purchase->bank_payment;
        }

        foreach ($payments as $payment) {
            $opening_balance -= $payment->bank_payment;
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

        foreach ($sale_party_payments as $sale) {
            $opening_balance += $sale->amount;
        }

        foreach ($purchase_party_payments as $payment) {
            $opening_balance -= $payment->amount;
        }

        return $opening_balance;
    }

    private function calculate_bank_opening_balance($user_id, $till_date = null, $to_date = null)
    {

        $banks = Bank::where('user_id', $user_id)->get();
        $opening_balance = 0;
        $bank_opening_balance = 0;

        if ($banks != null) {
            foreach ($banks as $bank) {
                $bank_opening_balance += $bank->opening_balance;


                if ($bank->balance_type == 'creditor') {
                    $fixed_opening_balance = "-" . $bank_opening_balance;
                } else {
                    $fixed_opening_balance = $bank_opening_balance;
                }

                if ($till_date == null) {

                    return $fixed_opening_balance;
                } else {
                    if ($to_date != null) {
                        if ($bank->opening_balance_on_date <= $till_date) {
                            $opening_balance += $fixed_opening_balance;
                        }
                    }
                }
            }
        }


        $sales = User::findOrFail($user_id)->invoices()->where('invoice_date', '<=', $till_date)->get();

        $purchases = User::find($user_id)->purchases()->where('bill_date', '<=', $till_date)->get();

        $payments = User::find($user_id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.created_at', '<=', $till_date)->get();

        $receipts = User::find($user_id)->saleRemainingAmounts()->where('sale_remaining_amounts.created_at', '<=', $till_date)->get();

        $sale_orders = SaleOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        // $gst_payments = CashGST::where('user_id', $user_id)->where('created_at', '<=', $till_date)->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $cash_deposited = CashDeposit::where('user_id', $user_id)->where('date', '<=', $till_date)->get();

        $sale_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'bank')->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();

        $purchase_party_payments = User::findOrFail($user_id)->partyRemainingAmounts()->where('party_pending_payment_account.type_of_payment', 'bank')->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.created_at', '<=', $till_date)->get();


        foreach ($sales as $sale) {
            $opening_balance += $sale->bank_payment;
        }

        foreach ($receipts as $receipt) {
            $opening_balance += $receipt->bank_payment;
        }

        foreach ($sale_orders as $order) {
            $opening_balance += $order->bank_amount;
        }


        foreach ($purchases as $purchase) {
            $opening_balance -= $purchase->bank_payment;
        }

        foreach ($payments as $payment) {
            $opening_balance -= $payment->bank_payment;
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

        foreach ($sale_party_payments as $sale) {
            $opening_balance += $sale->amount;
        }

        foreach ($purchase_party_payments as $payment) {
            $opening_balance -= $payment->amount;
        }

        return $opening_balance;
    }

    public function send_user_added_items(Request $request)
    {
        if ($request->ukey == '0hL8QZxrKL2zKfiSdMyKzc8SAAtA3O8E') {

            if ($request->has('user_id')) {
                $items = Item::where('user_id', $request->user_id)->orderBy('name')->get();
                if ($items) {
                    return response()->json(['message' => 'success', 'data' => $items], 200);
                } else {
                    return response()->json(['message' => 'no_data', 'data' => null], 200);
                }
            } else {
                return response()->json(['message' => 'failed', 'data' => null], 400);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function send_specific_item_detail(Request $request)
    {

        if ($request->ukey == 'yVEuYJVk2CyZ7tRfcmkcpaVIycKXBZP6') {

            if ($request->has('item_id')) {
                $item = Item::find($request->item_id);
                if ($item) {
                    return response()->json(['message' => 'success', 'data' => $item], 200);
                } else {
                    return response()->json(['message' => 'no_data', 'data' => null], 200);
                }
            } else {
                return response()->json(['message' => 'failed', 'data' => null], 400);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    // public function new_store_invoice()
    // {
    //     if ($request->ukey == 'YklaVY4KkiIe3aEFpUSfIR708eQQBAmF') {

    //         $invoice = new Invoice;
    //         $insertable_items = array();
    //         $amount = 0;

    //         $invoice->party_id = $request->party_id;


    //         $party = Party::find($request->party_id);

    //         if(!$party){
    //             return response()->json(['message' => 'invalid party id', 'data' => null], 201);
    //         }

    //         if($request->has('user_id')){
    //             $user = User::find($request->user_id);

    //             if(!$user){
    //                 return response()->json(['message' => 'invalid user id', 'data' => null], 201);    
    //             }

    //         } else {
    //             return response()->json(['message' => 'user id is required', 'data' => null], 201);
    //         }


    //         if( isset($user->profile) && isset($user->profile->bill_no_type) ){
    //             $invoice->invoice_no_type = $user->profile->bill_no_type;
    //         } else {
    //             $invoice->invoice_no_type = 'manual';
    //         }

    //         $invoice->billing_address = isset($request->billing_address) ? $request->billing_address : $party->billing_address . ', ' .
    //             $party->billing_city . ', ' .
    //             $party->billing_state . ', ' .
    //             $party->billing_pincode;

    //         if( $request->has('buyer_name') ) {
    //             $invoice->buyer_name = $request->buyer_name;
    //         }

    //         $i_date = $request->invoice_date;
    //         $idate = str_replace('/', '-', $i_date);
    //         $invoice_date = date('Y-m-d', strtotime($idate));


    //         if ($user->profile->financial_year_from > $invoice_date) {
    //             return redirect()->back()->with('failure', 'Please select valid invoice date for current financial year', $request->invoice_date);
    //         }

    //         if ($user->profile->financial_year_to < $invoice_date) {
    //             return redirect()->back()->with('failure', 'Please select valid invoice date for current financial year');
    //         }

    //         $d_date = $request->due_date;
    //         $ddate = str_replace('/', '-', $d_date);
    //         $due_date = date('Y-m-d', strtotime($ddate));


    //         if ($user->profile->financial_year_from > $due_date) {
    //             return redirect()->back()->with('failure', 'Please select valid due date for current financial year');
    //         }

    //         if ($user->profile->financial_year_to < $due_date) {
    //             return redirect()->back()->with('failure', 'Please select valid due date for current financial year');
    //         }

    //         $invoice->invoice_date = $invoice_date;

    //         $invoice->due_date = $due_date;

    //         $invoice->reference_name = $request->reference_name;

    //         if( $request->tax_inclusive == 'inclusive_of_tax' ){
    //             $invoice->amount_type = 'inclusive';
    //         } else if( $request->tax_inclusive == 'exclusive_of_tax' ){
    //             $invoice->amount_type = 'exclusive';
    //         }

    //         if (Session::has("transporter_details")) {
    //             $transporter_id = session("transporter_details.transporter_id");
    //             $vehicle_type = session("transporter_details.vehicle_type");
    //             $vehicle_number = session("transporter_details.vehicle_number");
    //             $delivery_date = session("transporter_details.delivery_date");

    //             $dtime = strtotime($delivery_date);

    //             $delivery_date = date('Y-m-d', $dtime);

    //             $invoice->transporter_id = $transporter_id;
    //             $invoice->vehicle_type = $vehicle_type;
    //             $invoice->vehicle_number = $vehicle_number;
    //             $invoice->delivery_date = $delivery_date;
    //         }

    //         $invoice->labour_charge = $request->labour_charges;
    //         $invoice->freight_charge = $request->freight_charges;
    //         $invoice->transport_charge = $request->transport_charges;
    //         $invoice->insurance_charge = $request->insurance_charges;
    //         $invoice->gst_charged_on_additional_charge = $request->gst_charged;

    //         $invoice->insurance_id = $request->insurance_company;

    //         $invoice->item_total_amount = $request->item_total_amount;
    //         $invoice->gst = $request->item_total_gst;
    //         $invoice->item_total_rcm_gst = $request->item_total_rcm_gst;
    //         $invoice->amount_paid = $request->amount_paid;
    //         $invoice->amount_remaining = $request->amount_remaining;
    //         $invoice->total_discount = $request->total_discount;
    //         $invoice->amount_before_round_off = $request->total_amount;
    //         $invoice->round_offed = $request->round_offed;
    //         $invoice->total_amount = $request->amount_to_pay;

    //         $invoice->remark = $request->overall_remark;

    //         $invoice->cess = $request->item_total_cess;

    //         $invoice->tcs = $request->tcs;

    //         //----------------------------------------------------------------------------------

    //         $user_profile = UserProfile::where('user_id', Auth::user()->id)->first();

    //         if ($party->reverse_charge == "yes") {
    //             $invoice->gst_classification = 'rcm';
    //         }

    //         if ( $user_profile->place_of_business == $party->business_place ) {
    //             $invoice->cgst = $request->item_total_gst/2;
    //             $invoice->sgst = $request->item_total_gst/2;
    //         }

    //         else {
    //             $invoice->igst = $request->item_total_gst;
    //         }



    //         if( $request->has('type_of_payment') ){

    //             $type_of_payment = $request->type_of_payment;

    //             $cash = array_search('cash', $type_of_payment);
    //             $bank = array_search('bank', $type_of_payment);
    //             $pos = array_search('pos', $type_of_payment);
    //             $discount = array_search('cash_discount', $type_of_payment);

    //             if(!is_bool($cash)){
    //                 $cash+=1;
    //             }

    //             if(!is_bool($bank)){
    //                 $bank+=1;
    //             }

    //             if(!is_bool($pos)){
    //                 $pos+=1;
    //             }

    //             if(!is_bool($discount)){
    //                 $discount+=1;
    //             }

    //             if( $cash && $bank && $pos && $discount ) {

    //                 $invoice->type_of_payment = 'combined';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->bank_payment = $request->banked_amount;
    //                 $invoice->pos_payment = $request->posed_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //                 $invoice->pos_bank_id = $request->pos_bank;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;

    //             } else if( $cash && $bank && $pos ) {

    //                 $invoice->type_of_payment = 'cash+bank+pos';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->bank_payment = $request->banked_amount;
    //                 $invoice->pos_payment = $request->posed_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //                 $invoice->pos_bank_id = $request->pos_bank;


    //             } else if( $cash && $bank && $discount ) {
    //                 $invoice->type_of_payment = 'cash+bank+discount';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->bank_payment = $request->banked_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;


    //             } else if( $cash && $discount && $pos ) {
    //                 $invoice->type_of_payment = 'cash+pos+discount';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->pos_payment = $request->posed_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->pos_bank_id = $request->pos_bank;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;


    //             } else if( $discount && $bank && $pos ) {
    //                 $invoice->type_of_payment = 'bank+pos+discount';

    //                 $invoice->bank_payment = $request->banked_amount;
    //                 $invoice->pos_payment = $request->posed_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //                 $invoice->pos_bank_id = $request->pos_bank;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;

    //             } else if( $cash && $bank ) {

    //                 $invoice->type_of_payment = 'bank+cash';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->bank_payment = $request->banked_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //             } else if( $cash && $pos ) {
    //                 $invoice->type_of_payment = 'pos+cash';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->pos_payment = $request->posed_amount;

    //                 $invoice->pos_bank_id = $request->pos_bank;

    //             } else if( $cash && $discount ) {

    //                 $invoice->type_of_payment = 'cash+discount';

    //                 $invoice->cash_payment = $request->cashed_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;

    //             } else if( $bank && $pos ) {
    //                 $invoice->type_of_payment = 'pos+bank';

    //                 $invoice->bank_payment = $request->banked_amount;
    //                 $invoice->pos_payment = $request->posed_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;
    //                 $invoice->pos_bank_id = $request->pos_bank;

    //             } else if( $bank && $discount ) {

    //                 $invoice->type_of_payment = 'discount';

    //                 $invoice->bank_payment = $request->banked_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;

    //             } else if( $pos && $discount ) {
    //                 $invoice->type_of_payment = 'pos+discount';

    //                 $invoice->pos_payment = $request->posed_amount;
    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;

    //                 $invoice->pos_bank_id = $request->pos_bank;

    //             } else if( $cash ) {
    //                 $invoice->type_of_payment = 'cash';

    //                 $invoice->cash_payment = $request->cashed_amount;

    //             } else if( $bank ) {

    //                 $invoice->type_of_payment = 'bank';

    //                 $invoice->bank_payment = $request->banked_amount;

    //                 $invoice->bank_id = $request->bank;
    //                 $invoice->bank_cheque = $request->bank_cheque;

    //             } else if( $pos ) {
    //                 $invoice->type_of_payment = 'pos';

    //                 $invoice->pos_payment = $request->posed_amount;

    //                 $invoice->pos_bank_id = $request->pos_bank;

    //             } else if( $discount ) {
    //                 $invoice->type_of_payment = 'discount';

    //                 $invoice->discount_payment = $request->discount_amount;

    //                 $invoice->discount_type = $request->discount_type;
    //                 $invoice->discount_figure = $request->discount_figure;
    //             }
    //         } else {
    //             $invoice->type_of_payment = 'no_payment';
    //         }

    //         $invoice->shipping_bill_no = $request->shipping_bill_no;
    //         $invoice->date_of_shipping = $request->date_of_shipping;
    //         $invoice->code_of_shipping_port = $request->code_of_shipping_port;
    //         $invoice->conversion_rate = $request->conversion_rate;
    //         $invoice->currency_symbol = $request->currency_symbol;
    //         $invoice->export_type = $request->export_type;

    //         $invoice->consignee_info = $request->consignee_info;
    //         $invoice->consignor_info = $request->consignor_info;

    //         if( $request->invoice_no != null ){
    //             $invoice->invoice_no = $request->invoice_no;
    //         }

    //         if( $request->has('invoice_prefix') ){
    //             $invoice->invoice_prefix = $request->invoice_prefix;
    //         }

    //         if( $request->has('invoice_suffix') ){
    //             $invoice->invoice_suffix = $request->invoice_suffix;
    //         }

    //         if( $request->has('gst_classification') ) {
    //             for($i=0; $i<count($request->gst_classification); $i++){
    //                 if($request->gst_classification[$i] == 'rcm'){
    //                     $invoice->gst_classification = 'rcm';
    //                 }
    //             }
    //         } else {
    //             for($i=0; $i<count($request->item); $i++){
    //                 $item = Item::find($request->item[$i]);

    //                 if(isset($item) && $item->item_under_rcm == "yes") {
    //                     $invoice->gst_classification = 'rcm';
    //                 }
    //             }
    //         }

    //         $invoice->save();

    //         $sale_remaining_amount = new SaleRemainingAmount;

    //         $sale_remaining_amount->party_id = $request->party;
    //         $sale_remaining_amount->invoice_id = $invoice->id;
    //         $sale_remaining_amount->total_amount = $request->total_amount;
    //         $sale_remaining_amount->amount_paid = $request->amount_paid;
    //         $sale_remaining_amount->amount_remaining = $request->amount_remaining;



    //         if ($request->has('type_of_payment')) {


    //             if( $cash && $bank && $pos && $discount ) {

    //                 $sale_remaining_amount->type_of_payment = 'combined';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;
    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;
    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;
    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;
    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;

    //             } else if( $cash && $bank && $pos ) {

    //                 $sale_remaining_amount->type_of_payment = 'cash+bank+pos';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;
    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;
    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;
    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //             } else if( $cash && $bank && $discount ) {

    //                 $sale_remaining_amount->type_of_payment = 'cash+bank+discount';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;
    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;

    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;
    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;

    //             } else if( $cash && $discount && $pos ) {

    //                 $sale_remaining_amount->type_of_payment = 'cash+pos+discount';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;
    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;

    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //             } else if( $discount && $bank && $pos ) {

    //                 $sale_remaining_amount->type_of_payment = 'bank+pos+discount';

    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;
    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;
    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;

    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;

    //             } else if( $cash && $bank ) {

    //                 $sale_remaining_amount->type_of_payment = 'bank+cash';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;
    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;

    //             } else if( $cash && $pos ) {

    //                 $sale_remaining_amount->type_of_payment = 'pos+cash';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;
    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;

    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //             } else if( $cash && $discount ) {

    //                 $sale_remaining_amount->type_of_payment = 'cash+discount';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;

    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;
    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;

    //             } else if( $bank && $pos ) {

    //                 $sale_remaining_amount->type_of_payment = 'pos+bank';

    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;
    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;
    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //             } else if( $bank && $discount ) {

    //                 $sale_remaining_amount->type_of_payment = 'pos+bank';

    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;

    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;
    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;

    //             } else if( $pos && $discount ) {

    //                 $sale_remaining_amount->type_of_payment = 'pos+discount';

    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;

    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;
    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;

    //             } else if( $cash ) {

    //                 $sale_remaining_amount->type_of_payment = 'cash';

    //                 $sale_remaining_amount->cash_payment = $request->cashed_amount;

    //             } else if( $bank ) {

    //                 $sale_remaining_amount->type_of_payment = 'bank';

    //                 $sale_remaining_amount->bank_payment = $request->banked_amount;

    //                 $sale_remaining_amount->bank_id = $request->bank;
    //                 $sale_remaining_amount->bank_cheque = $request->bank_cheque;

    //             } else if( $pos ) {

    //                 $sale_remaining_amount->type_of_payment = 'pos';

    //                 $sale_remaining_amount->pos_payment = $request->posed_amount;

    //                 $sale_remaining_amount->pos_bank_id = $request->pos_bank;

    //             } else if( $discount ) {

    //                 $sale_remaining_amount->type_of_payment = 'discount';
    //                 $sale_remaining_amount->discount_payment = $request->discount_amount;
    //                 $sale_remaining_amount->discount_type = $request->discount_type;
    //                 $sale_remaining_amount->discount_figure = $request->discount_figure;
    //             }

    //         } else {
    //             $sale_remaining_amount->type_of_payment = 'no_payment';
    //         }

    //         $sale_remaining_amount->save();

    //         $hasLumpSump = false;

    //         $items = $request->item;
    //         $quantities = $request->quantity;

    //         if($request->has('add_lump_sump') && $request->add_lump_sump == 'yes'){
    //             $prices = $request->amount;
    //             $hasLumpSump = true;
    //         }
    //         else if($request->has('price')){
    //             $prices = $request->price;
    //         }

    //         $discounts = $request->item_discount;
    //         $remarks = $request->item_remark;
    //         $calculated_gst = $request->calculated_gst;
    //         $gst_tax_types = $request->gst_tax_type;
    //         $calculated_rcm_gst = $request->calculated_gst_rcm;
    //         $measuring_unit = $request->measuring_unit;
    //         $free_qty = $request->free_quantity;
    //         $gst_classification = $request->gst_classification;
    //         $cesses = $request->cess_amount;

    //         for ($i=0; $i < count($items); $i++) {
    //             $insertable_items[$i]['id'] = $items[$i];
    //             $insertable_items[$i]['qty'] = $quantities[$i];
    //             $insertable_items[$i]['price'] = $prices[$i];
    //             $insertable_items[$i]['discount'] = $discounts[$i];
    //             $insertable_items[$i]['remark'] = isset($remarks[$i]) ? $remarks[$i] : null;
    //             $insertable_items[$i]['gst'] = $calculated_gst[$i] ?? 0;
    //             $insertable_items[$i]['gst_tax_type'] = $gst_tax_types[$i];
    //             $insertable_items[$i]['rcm_gst'] = $calculated_rcm_gst[$i] ?? 0;
    //             $insertable_items[$i]['measuring_unit'] = $measuring_unit[$i];
    //             $insertable_items[$i]['free_qty'] = isset($free_qty[$i]) ? $free_qty[$i] : 0;
    //             $insertable_items[$i]['gst_classification'] = isset($gst_classification[$i]) ? $gst_classification[$i] : null;
    //             $insertable_items[$i]['cess'] = isset($cesses[$i]) ? $cesses[$i] : null;
    //         }

    //         foreach($insertable_items as $item){
    //             $invoice_item = new Invoice_Item;

    //             $sold_item = Item::find($item['id']);

    //             $invoice_item->invoice_id = $invoice->id;

    //             $invoice_item->item_id = $item['id'];
    //             $invoice_item->sold_on = date('Y-m-d', strtotime(str_replace('/', '-', $request->invoice_date)));
    //             $invoice_item->gst = $item['gst'];
    //             $invoice_item->rcm_gst = $item['rcm_gst'];
    //             $invoice_item->gst_rate = $sold_item->gst;
    //             $invoice_item->item_price = $item['price'];
    //             $invoice_item->item_total = $this->calculate_item_total($item['price'], $item['qty'], $item['gst'], $item['gst_tax_type']);
    //             $invoice_item->item_tax_type = $item['gst_tax_type'];
    //             $invoice_item->discount = $item['discount'];
    //             $invoice_item->party_id = $request->party;
    //             $invoice_item->gst_classification = $item['gst_classification'];
    //             $invoice_item->remark = $item['remark'];
    //             $invoice_item->cess = $item['cess'];
    //             $invoice_item->has_lump_sump = ($hasLumpSump) ? 1 : 0;

    //             if( isset($item['measuring_unit']) ) {

    //                 if($item['free_qty'] > 0){
    //                     $qty = $item['qty'] + $item['free_qty'];
    //                 } else {
    //                     $qty = $item['qty'];
    //                 }

    //                 if( $item['measuring_unit'] == $sold_item->measuring_unit ){
    //                     $sold_item->qty = $sold_item->qty - $qty;

    //                     $invoice_item->item_qty = $item['qty'];
    //                     $invoice_item->item_alt_qty = 0;
    //                     $invoice_item->item_comp_qty = 0;
    //                 }

    //                 if( $item['measuring_unit'] == $sold_item->alternate_measuring_unit ){

    //                     $alternate_to_base = $sold_item->conversion_of_alternate_to_base_unit_value;

    //                     $original_qty = $qty * $alternate_to_base;

    //                     $sold_item->qty = $sold_item->qty - $original_qty;

    //                     $invoice_item->item_qty = $original_qty;
    //                     $invoice_item->item_alt_qty = $item['qty'];
    //                     $invoice_item->item_comp_qty = 0;
    //                 }

    //                 if( $item['measuring_unit'] == $sold_item->compound_measuring_unit ){

    //                     $alternate_to_base = $sold_item->conversion_of_alternate_to_base_unit_value;
    //                     $compound_to_alternate = $sold_item->conversion_of_compound_to_alternate_unit_value;

    //                     $original_qty = $alternate_to_base * $compound_to_alternate * $qty;

    //                     $sold_item->qty = $sold_item->qty - $original_qty;

    //                     $invoice_item->item_qty = $original_qty;
    //                     $invoice_item->item_alt_qty = $compound_to_alternate * $qty;
    //                     $invoice_item->item_comp_qty = $item['qty'];
    //                 }
    //                 $invoice_item->item_measuring_unit = $item['measuring_unit'];
    //             } else {

    //                 if ($item['free_qty'] > 0) {
    //                     $qty = $item['qty'] + $item['free_qty'];
    //                 } else {
    //                     $qty = $item['qty'];
    //                 }

    //                 $sold_item->qty = $sold_item->qty - $qty;

    //                 $invoice_item->item_qty = $item['qty'];
    //                 $invoice_item->item_alt_qty = 0;
    //                 $invoice_item->item_comp_qty = 0;
    //             }

    //             $invoice_item->free_qty = $item['free_qty'];

    //             $sold_item->save();

    //             $invoice_item->save();
    //         }
    //     }

    // }

    public function store_to_invoice(Request $request)
    {

        if ($request->ukey == 'YklaVY4KkiIe3aEFpUSfIR708eQQBAmF') {

            $invoice = new Invoice;
            $insertable_items = array();
            $amount = 0;

            $invoice->party_id = $request->party_id;


            $party = Party::find($request->party_id);

            if (!$party) {
                return response()->json(['message' => 'invalid party id', 'data' => null], 500);
            }

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json(['message' => 'invalid user id', 'data' => null], 500);
                }
            } else {
                return response()->json(['message' => 'user id is required', 'data' => null], 500);
            }


            if (isset($user->profile) && isset($user->profile->bill_no_type)) {
                $invoice->invoice_no_type = $user->profile->bill_no_type;
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


            $d_date = $request->due_date;
            $ddate = str_replace('/', '-', $d_date);
            $due_date = date('Y-m-d', strtotime($ddate));


            $invoice->invoice_date = $invoice_date;

            $invoice->due_date = $due_date;

            $invoice->reference_name = $request->reference_name;

            if ($request->tax_inclusive == 'inclusive_of_tax') {
                $invoice->amount_type = 'inclusive';
            } else if ($request->tax_inclusive == 'exclusive_of_tax') {
                $invoice->amount_type = 'exclusive';
            }

            $invoice->labour_charge = $request->labour_charges ?? 0;
            $invoice->freight_charge = $request->freight_charges ?? 0;
            $invoice->transport_charge = $request->transport_charges ?? 0;
            $invoice->insurance_charge = $request->insurance_charges ?? 0;
            $invoice->gst_charged_on_additional_charge = $request->gst_charged ?? 0;

            $invoice->insurance_id = $request->insurance_company;

            $invoice->item_total_amount = $request->item_total_amount;
            $invoice->gst = $request->item_total_gst;
            $invoice->amount_paid = $request->amount_paid ?? 0;
            $invoice->amount_remaining = $request->amount_remaining;
            $invoice->total_discount = $request->total_discount;
            $invoice->amount_before_round_off = $request->total_amount;
            $invoice->round_off_operation = $request->round_off_operation;
            $invoice->round_offed = $request->round_offed;
            $invoice->total_amount = $request->amount_to_pay;

            $invoice->remark = $request->overall_remark;

            $invoice->cess = $request->item_total_cess;

            $invoice->tcs = $request->tcs;



            $user_profile = $user->profile;

            if ($party->reverse_charge == "yes") {
                $invoice->gst_classification = 'rcm';
            }

            if ($user_profile->place_of_business == $party->business_place) {
                $invoice->cgst = $request->item_total_gst / 2;
                $invoice->sgst = $request->item_total_gst / 2;
            } else {
                $invoice->igst = $request->item_total_gst;
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

                    $invoice->type_of_payment = 'discount';

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
            $invoice->date_of_shipping = date('Y-m-d', strtotime(str_replace('/', '-', $request->date_of_shipping)));
            $invoice->code_of_shipping_port = $request->code_of_shipping_port;
            $invoice->conversion_rate = $request->conversion_rate;
            $invoice->currency_symbol = $request->currency_symbol;
            $invoice->export_type = $request->export_type;

            $invoice->consignee_info = $request->consignee_info;
            $invoice->consignor_info = $request->consignor_info;

            if ($request->has('invoice_no')) {
                if (is_numeric($request->invoice_no)) {
                    if($invoice->invoice_no_type == 'manual') {
                        $validateInvoice = Invoice::where('invoice_no', $request->invoice_no)->where('party_id', $request->party_id)->whereBetween('invoice_date', [$user->profile->financial_year_from, $user->profile->financial_year_to])->first();

                        if($validateInvoice) {
                            return response()->json(['message' => 'Invoice no must be unique for a party', 'data' => null], 500);
                        }
                    }
                    $invoice->invoice_no = $request->invoice_no;
                } else {
                    return response()->json(['message' => 'Invoice no must be numeric', 'data' => null], 500);
                }
            } else {
                return response()->json(['message' => 'invoice no is required', 'data' => null], 500);
            }

            if ($request->has('invoice_prefix')) {
                $invoice->invoice_prefix = $request->invoice_prefix;
            }

            if ($request->has('invoice_suffix')) {
                $invoice->invoice_suffix = $request->invoice_suffix;
            }

            if(gettype($request->item) !== "array") {
                return response()->json(['message' => 'items should be of type `array`', 'data' => null], 500);
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

            $invoice->created_on = 'mobile';

            $invoice->save();

            $sale_remaining_amount = new SaleRemainingAmount;

            $sale_remaining_amount->party_id = $request->party_id;
            $sale_remaining_amount->invoice_id = $invoice->id;
            $sale_remaining_amount->total_amount = $request->total_amount;
            $sale_remaining_amount->amount_paid = $request->amount_paid ?? 0;
            $sale_remaining_amount->amount_remaining = $request->amount_remaining;
            // $sale_remaining_amount->type_of_payment = $request->type_of_payment;
            // if($request->type_of_payment == 'bank'){
            //     $sale_remaining_amount->bank_id;
            // }


            if ($request->has('type_of_payment')) {

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

            $sale_remaining_amount->created_on = 'mobile';

            $sale_remaining_amount->save();

            $hasLumpSump = false;

            $items = $request->item;
            $quantities = $request->quantity;

            if ($request->has('add_lump_sump') && $request->add_lump_sump == 'yes') {
                $prices = $request->amount;
                $hasLumpSump = true;
            } else if ($request->has('price')) {
                $prices = $request->price;
            }

            $discounts = $request->item_discount;
            $remarks = $request->item_remark;
            $calculated_gst = $request->calculated_gst;
            $gst_tax_types = $request->gst_tax_type;
            $measuring_unit = $request->measuring_unit;
            $free_qty = $request->free_quantity;
            $gst_classification = $request->gst_classification;
            $cesses = $request->cess_amount;

            for ($i = 0; $i < count($items); $i++) {
                $insertable_items[$i]['id'] = $items[$i];
                $insertable_items[$i]['qty'] = $quantities[$i];
                $insertable_items[$i]['price'] = $prices[$i];
                $insertable_items[$i]['discount'] = $discounts[$i];
                $insertable_items[$i]['remark'] = isset($remarks[$i]) ? $remarks[$i] : null;
                $insertable_items[$i]['gst'] = $calculated_gst[$i];
                $insertable_items[$i]['gst_tax_type'] = $gst_tax_types[$i];
                $insertable_items[$i]['measuring_unit'] = $measuring_unit[$i];
                $insertable_items[$i]['free_qty'] = isset($free_qty[$i]) ? $free_qty[$i] : 0;
                $insertable_items[$i]['gst_classification'] = isset($gst_classification[$i]) ? $gst_classification[$i] : null;
                $insertable_items[$i]['cess'] = isset($cesses[$i]) ? $cesses[$i] : null;
            }

            foreach ($insertable_items as $item) {
                $invoice_item = new Invoice_Item;

                $sold_item = Item::find($item['id']);

                $invoice_item->invoice_id = $invoice->id;

                $invoice_item->item_id = $item['id'];
                $invoice_item->sold_on = date('Y-m-d', strtotime(str_replace('/', '-', $request->invoice_date)));
                $invoice_item->gst = $item['gst'];
                $invoice_item->gst_rate = $sold_item->gst;
                $invoice_item->item_price = $item['price'];
                $invoice_item->item_total = $this->calculate_item_total($item['price'], $item['qty'], $item['gst'], $item['gst_tax_type']);
                $invoice_item->item_tax_type = $item['gst_tax_type'];
                $invoice_item->discount = $item['discount'];
                $invoice_item->party_id = $request->party_id;
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
                    }

                    if ($item['measuring_unit'] == $sold_item->alternate_measuring_unit) {

                        $alternate_to_base = $sold_item->conversion_of_alternate_to_base_unit_value;

                        $original_qty = $qty * $alternate_to_base;

                        $sold_item->qty = $sold_item->qty - $original_qty;

                        $invoice_item->item_qty = $original_qty;
                        $invoice_item->item_alt_qty = $item['qty'];
                        $invoice_item->item_comp_qty = 0;
                    }

                    if ($item['measuring_unit'] == $sold_item->compound_measuring_unit) {

                        $alternate_to_base = $sold_item->conversion_of_alternate_to_base_unit_value;
                        $compound_to_alternate = $sold_item->conversion_of_compound_to_alternate_unit_value;

                        $original_qty = $alternate_to_base * $compound_to_alternate * $qty;

                        $sold_item->qty = $sold_item->qty - $original_qty;

                        $invoice_item->item_qty = $original_qty;
                        $invoice_item->item_alt_qty = $compound_to_alternate * $qty;
                        $invoice_item->item_comp_qty = $item['qty'];
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
                }

                $invoice_item->free_qty = $item['free_qty'];

                $invoice_item->created_on = 'mobile';

                $sold_item->save();

                $invoice_item->save();
            }

            return response()->json(['message' => 'success', 'data' => null], 200);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function store_to_bill(Request $request)
    {
        if ($request->ukey == 'OKKsXpfK00bgtyqRrJBEeW58IdtFc1WF') {
            $purchase_record = new PurchaseRecord;

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User is required']);
            }

            $purchase_record->party_id = $request->party;

            if ($request->has('buyer_name')) {
                $purchase_record->buyer_name = $request->buyer_name;
            }

            $purchase_record->bill_no = $request->bill_no;

            if (isset($user->purchaseSetting) && isset($user->purchaseSetting->bill_no_type)) {
                $purchase_record->bill_no_type = $user->purchaseSetting->bill_no_type;
            } else {
                $purchase_record->bill_no_type = 'manual';
            }

            if ($request->has('bill_date')) {
                $purchase_record->bill_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));
            } else {
                $purchase_record->bill_date = date('Y-m-d', time());
            }

            $purchase_record->item_total_amount = $request->item_total_amount;
            $purchase_record->item_total_gst = $request->item_total_gst;
            $purchase_record->item_total_cess = $request->item_total_cess;
            $purchase_record->total_discount = $request->total_discount;
            // $purchase_record->total_amount = $request->total_amount;

            $purchase_record->amount_before_round_off = $request->total_amount;
            $purchase_record->round_off_operation = $request->round_off_operation;
            $purchase_record->round_offed = $request->round_offed;
            $purchase_record->total_amount = $request->amount_to_pay;

            $purchase_record->amount_paid = $request->amount_paid ?? 0;
            $purchase_record->amount_remaining = $request->amount_remaining;

            $purchase_record->discount = $request->overall_discount;


            $purchase_record->tcs = $request->tcs;
            $purchase_record->remark = $request->overall_remark;

            $purchase_record->purchase_order_no = $request->purchase_order_no;

            if ($request->tax_inclusive == 'inclusive_of_tax') {
                $purchase_record->amount_type = 'inclusive';
            } else if ($request->tax_inclusive == 'exclusive_of_tax') {
                $purchase_record->amount_type = 'exclusive';
            }

            /*----------------------------------------------------------------------------------*/

            $user_profile = UserProfile::where('user_id', $user->id)->first();

            $party = Party::where('id', $request->party)->first();

            if ($party->reverse_charge == "yes") {
                $purchase_record->gst_classification = 'rcm';
            }

            if ($user_profile->place_of_business == $party->business_place) {
                $purchase_record->cgst = $request->item_total_gst / 2;
                $purchase_record->sgst = $request->item_total_gst / 2;
            }
            // else if( $user_profile->place_of_business != $party->business_place && ( $party->business_place == 4 || $party->business_place == 7 || $party->business_place == 25 || $party->business_place == 26 || $party->business_place == 31 || $party->business_place == 34 || $party->business_place == 35 ) ) {
            //     $purchase_record->ugst = $request->item_total_gst;
            // }
            else {
                $purchase_record->igst = $request->item_total_gst;
            }

            //-----------------------------------------------------------------------------------

            $purchase_record->reference_name = $request->reference_name;

            $purchase_record->labour_charge = $request->labour_charges ?? 0;
            $purchase_record->freight_charge  = $request->freight_charges ?? 0;
            $purchase_record->transport_charge  = $request->transport_charges ?? 0;
            $purchase_record->insurance_charge  = $request->insurance_charges ?? 0;
            $purchase_record->gst_charged_on_additional_charge = $request->gst_charged ?? 0;

            $purchase_record->insurance_id = $request->insurance_company;

            // if (Session::has("transporter_details")) {
            //     $transporter_id = session("transporter_details.transporter_id");
            //     $vehicle_type = session("transporter_details.vehicle_type");
            //     $vehicle_number = session("transporter_details.vehicle_number");
            //     $delivery_date = session("transporter_details.delivery_date");

            //     $dtime = strtotime($delivery_date);

            //     $delivery_date = date('Y-m-d', $dtime);

            //     $purchase_record->transporter_id = $transporter_id;
            //     $purchase_record->vehicle_type = $vehicle_type;
            //     $purchase_record->vehicle_number = $vehicle_number;
            //     $purchase_record->delivery_date = $delivery_date;
            // }

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
                } else if ($cash && $bank && $pos) {

                    $purchase_record->type_of_payment = 'cash+bank+pos';

                    $purchase_record->cash_payment = $request->cashed_amount;
                    $purchase_record->bank_payment = $request->banked_amount;
                    $purchase_record->pos_payment = $request->posed_amount;

                    $purchase_record->bank_id = $request->bank;
                    $purchase_record->bank_cheque = $request->bank_cheque;

                    $purchase_record->pos_bank_id = $request->pos_bank;
                } else if ($cash && $bank && $discount) {
                    $purchase_record->type_of_payment = 'cash+bank+discount';

                    $purchase_record->cash_payment = $request->cashed_amount;
                    $purchase_record->bank_payment = $request->banked_amount;
                    $purchase_record->discount_payment = $request->discount_amount;

                    $purchase_record->bank_id = $request->bank;
                    $purchase_record->bank_cheque = $request->bank_cheque;

                    $purchase_record->discount_type = $request->discount_type;
                    $purchase_record->discount_figure = $request->discount_figure;
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
                } else if ($cash && $bank) {

                    $purchase_record->type_of_payment = 'bank+cash';

                    $purchase_record->cash_payment = $request->cashed_amount;
                    $purchase_record->bank_payment = $request->banked_amount;

                    $purchase_record->bank_id = $request->bank;
                    $purchase_record->bank_cheque = $request->bank_cheque;
                } else if ($cash && $pos) {
                    $purchase_record->type_of_payment = 'pos+cash';

                    $purchase_record->cash_payment = $request->cashed_amount;
                    $purchase_record->pos_payment = $request->posed_amount;

                    $purchase_record->pos_bank_id = $request->pos_bank;
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
                } else if ($bank && $discount) {

                    $purchase_record->type_of_payment = 'discount';

                    $purchase_record->bank_payment = $request->banked_amount;
                    $purchase_record->discount_payment = $request->discount_amount;

                    $purchase_record->bank_id = $request->bank;
                    $purchase_record->bank_cheque = $request->bank_cheque;

                    $purchase_record->discount_type = $request->discount_type;
                    $purchase_record->discount_figure = $request->discount_figure;
                } else if ($pos && $discount) {
                    $purchase_record->type_of_payment = 'pos+discount';

                    $purchase_record->pos_payment = $request->posed_amount;
                    $purchase_record->discount_payment = $request->discount_amount;

                    $purchase_record->discount_type = $request->discount_type;
                    $purchase_record->discount_figure = $request->discount_figure;

                    $invoice->pos_bank_id = $request->pos_bank;
                } else if ($cash) {
                    $purchase_record->type_of_payment = 'cash';

                    $purchase_record->cash_payment = $request->cashed_amount;
                } else if ($bank) {

                    $purchase_record->type_of_payment = 'bank';

                    $purchase_record->bank_payment = $request->banked_amount;

                    $purchase_record->bank_id = $request->bank;
                    $purchase_record->bank_cheque = $request->bank_cheque;
                } else if ($pos) {
                    $purchase_record->type_of_payment = 'pos';

                    $purchase_record->pos_payment = $request->posed_amount;

                    $purchase_record->pos_bank_id = $request->pos_bank;
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
            $purchase_record->date_of_shipping = $request->date_of_shipping;
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

            $purchase_record->created_on = 'mobile';

            if ($purchase_record->save()) {

                /**-------Purchase pending payments------*/
                $purchase_remaining_amount = new PurchaseRemainingAmount;

                $purchase_remaining_amount->party_id = $request->party;
                // $purchase_remaining_amount->bill_no = $request->bill_no;
                $purchase_remaining_amount->purchase_id = $purchase_record->id;
                $purchase_remaining_amount->total_amount = $request->total_amount;
                $purchase_remaining_amount->amount_paid = $request->amount_paid ?? 0;
                $purchase_remaining_amount->amount_remaining = $request->amount_remaining;


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

                        $purchase_remaining_amount->bank_id = $request->bank;
                        $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                        $purchase_remaining_amount->discount_payment = $request->discount_amount;
                        $purchase_remaining_amount->discount_type = $request->discount_type;
                        $purchase_remaining_amount->discount_figure = $request->discount_figure;
                    } else if ($cash && $discount && $pos) {

                        $purchase_remaining_amount->type_of_payment = 'cash+pos+discount';

                        $purchase_remaining_amount->cash_payment = $request->cashed_amount;
                        $purchase_remaining_amount->pos_payment = $request->posed_amount;

                        $purchase_remaining_amount->pos_bank_id = $request->pos_bank;
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

                        $purchase_remaining_amount->type_of_payment = 'pos+bank';

                        $purchase_remaining_amount->bank_payment = $request->banked_amount;

                        $purchase_remaining_amount->bank_id = $request->bank;
                        $purchase_remaining_amount->bank_cheque = $request->bank_cheque;

                        $purchase_remaining_amount->discount_payment = $request->discount_amount;
                        $purchase_remaining_amount->discount_type = $request->discount_type;
                        $purchase_remaining_amount->discount_figure = $request->discount_figure;
                    } else if ($pos && $discount) {

                        $purchase_remaining_amount->type_of_payment = 'pos+discount';

                        $purchase_remaining_amount->pos_payment = $request->posed_amount;

                        $purchase_remaining_amount->pos_bank_id = $request->pos_bank;

                        $purchase_remaining_amount->discount_payment = $request->discount_amount;
                        $purchase_remaining_amount->discount_type = $request->discount_type;
                        $purchase_remaining_amount->discount_figure = $request->discount_figure;
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

                $purchase_remaining_amount->created_on = 'mobile';

                $purchase_remaining_amount->save();

                /**-------purchase Items------- */

                $insertable_items = array();

                $items = $request->item;
                $quantities = $request->quantity;
                $prices = $request->price;
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
                    $purchase->item_total = $this->calculate_item_total($item['price'], $item['qty'], $item['calculated_gst'], $item['gst_tax_type']);
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
                    $purchase->discount = $item['discount'];
                    $purchase->gst_classification = $item['gst_classification'];
                    $purchase->cess = $item['cess'];


                    // if (Session::has("item_extra" . $item['id'])) {

                    //     $manufacture = session("item_extra" . $item['id'] . '.manufacture');
                    //     $expiry = session("item_extra" . $item['id'] . '.expiry');
                    //     $batch = session("item_extra" . $item['id'] . '.batch');
                    //     $size = session("item_extra" . $item['id'] . '.size');
                    //     $pieces = session("item_extra" . $item['id'] . '.pieces');

                    //     $mtime = strtotime($manufacture);
                    //     $etime = strtotime($expiry);

                    //     $manufacture1 = date('Y-m-d', $mtime);
                    //     $expiry1 = date('Y-m-d', $etime);

                    //     $purchase->manufacture = $manufacture1;
                    //     $purchase->expiry = $expiry1;
                    //     $purchase->batch = $batch;
                    //     $purchase->size = $size;
                    //     $purchase->pieces = $pieces;
                    // }

                    // if (Session::has("short_rate" . $item['id'])) {

                    //     // $gross_rate = session("short_rate" . $item['id'] . '.gross_rate');
                    //     $short_rate = session("short_rate" . $item['id'] . '.short_rate');
                    //     $net_rate = session("short_rate" . $item['id'] . '.net_rate');

                    //     $purchase->short_rate = $short_rate;
                    //     $purchase->net_rate = $net_rate;
                    // }

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
                        }

                        if ($item['measuring_unit'] == $purchased_item->alternate_measuring_unit) {

                            $alternate_to_base = $purchased_item->conversion_of_alternate_to_base_unit_value;

                            $original_qty = $qty * $alternate_to_base;

                            $purchased_item->qty = $purchased_item->qty + $original_qty;

                            $purchase->qty = $original_qty;
                            $purchase->alt_qty = $item['qty'];
                            $purchase->comp_qty = 0;
                        }

                        if ($item['measuring_unit'] == $purchased_item->compound_measuring_unit) {

                            $alternate_to_base = $purchased_item->conversion_of_alternate_to_base_unit_value;
                            $compound_to_alternate = $purchased_item->conversion_of_alternate_to_base_unit_value;

                            $original_qty = $alternate_to_base * $compound_to_alternate * $qty;

                            $purchased_item->qty = $purchased_item->qty + $original_qty;

                            $purchase->qty = $original_qty;
                            $purchase->alt_qty = $compound_to_alternate * $qty;
                            $purchase->comp_qty = $item['qty'];
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
                    }

                    $purchase->free_qty = $item['free_qty'];

                    $purchase->created_on = 'mobile';

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
            }
            return response()->json(['message' => 'success', 'data' => null], 200);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    private function calculate_item_total($price, $qty, $calculated_gst, $gst_tax_type)
    {
        if ($gst_tax_type == "inclusive_of_tax") {
            return ($price * $qty) - $calculated_gst; // as inclusive of tax price would be price - gst;
        }

        return $price * $qty;
    }

    public function provide_unique_invoice_no(Request $request)
    {
        if ($request->ukey == 'U9B3hYMwzG9ZIvMCsxJ9l9mJvGiF1c12') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                $invoice = $user->invoices()->orderBy('id', 'desc')->first();
                // return $invoice;
                if ($invoice) {
                    $invoice_no = $invoice->invoice_no + 1;
                } else {
                    $invoice_no = 1;
                }
                $prefix = $user->profile->name_of_prefix ?? '';
                $suffix = $user->profile->name_of_suffix ?? '';
                return response()->json([ 'message' => 'success', 'data' => [ 'invoice' => $invoice_no, 'prefix' => $prefix, 'suffix' => $suffix ] ], 200);
            } else {
                response()->json(['message' => 'user_id is required', 'data' => 'no_data'], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function provide_unique_bill_no(Request $request)
    {
        if ($request->ukey == 'DEetJfPH5GJg3CwOQ1VUiS99zJscYvUX') {

            if ($request->has('user_id')) {
                $bill = User::findOrFail($request->user_id)->purchases()->orderBy('id', 'desc')->first();
                if ($bill) {
                    $bill_no = $bill->bill_no + 1;
                } else {
                    $bill_no = 1;
                }

                return response()->json(['message' => 'success', 'data' => $bill_no], 200);
            } else {
                response()->json(['message' => 'user_id is required', 'data' => 'no_data'], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_transporter_details(Request $request)
    {
        if (request('ukey') === 'zpmRGfR8L2ewngpVkMtg8WaJffS45vg8') {
            $transporters = Transporter::where('user_id', $request->user_id)->get();

            if ($transporters) {
                return response()->json(['message' => 'success', 'data' => $transporters], 200);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_all_groups(Request $request)
    {
        if (request('ukey') === '6DMjTlHHCvMBygsPeG5hM0qaD54C9ETw') {
            $groups = Group::where('user_id', $request->user_id)->get();

            if ($groups) {
                return response()->json(['message' => 'success', 'data' => $groups], 200);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_gst_list(Request $request)
    {
        if (request('ukey') === 'pdxTQIHfadY6SXzPq98WMyKkF2d071Gn') {
            $gst_list = GstList::all();

            if ($gst_list) {
                return response()->json(['message' => 'success', 'data' => $gst_list], 200);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function get_measuring_unit_list()
    {
        if (request('ukey') === 'Ej5SIHWlwlv6acQUKvejF5aEwWahpULq') {
            $measuring_unit = MeasuringUnit::all();

            if ($measuring_unit) {
                return response()->json(['message' => 'succes', 'data' => $measuring_unit], 200);
            } else {
                return response()->json(['message' => 'no_data', 'data' => null], 200);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    // public function store_item(Request $request)
    // {

    //     if (request('ukey') === 'JFYqdr4ARNZpRrhNW8B0cRZ350cC1EJD') {

    //         $item = new Item;

    //         $item->type = $request->type_of_service;
    //         $item->name = $request->name;
    //         $item->category = $request->category;

    //         if( $request->has('hsc_code') ){
    //             $item->hsc_code = $request->hsc_code;
    //         }

    //         if( $request->has('sac_code') ){
    //             $item->sac_code = $request->sac_code;
    //         }

    //         $item->group_id = $request->group;

    //         $item->gst = $request->gst;

    //         $item->has_additional_items = $request->has_additional_items;

    //         if ($request->has('manufacture')) {
    //             $item->manufacture = date('Y-m-d', strtotime(str_replace('/', '-', $request->manufacture)));
    //         }

    //         if ($request->has('expiry')) {
    //             $item->expiry = date('Y-m-d', strtotime(str_replace('/', '-', $request->expiry)));
    //         }

    //         if ($request->has('batch')) {
    //             $item->batch = $request->batch;
    //         }

    //         if ($request->has('size')) {
    //             $item->size = $request->size;
    //         }

    //         $item->measuring_unit = $request->measuring_unit;

    //         if ($request->has('measuring_unit_short_name')) {
    //             $item->measuring_unit_short_name = $request->measuring_unit_short_name;
    //         }

    //         if ($request->has('measuring_unit_decimal_place')) {
    //             $item->measuring_unit_decimal_place = $request->measuring_unit_decimal_place;
    //         }

    //         $item->has_alternate_unit = strtolower($request->has_alternate_unit);

    //         if ($request->has('has_alternate_unit') && strtolower($request->has_alternate_unit) == "yes") {

    //             $item->alternate_measuring_unit = $request->alternate_measuring_unit;
    //             $item->alternate_unit_short_name = $request->alternate_unit_short_name;
    //             $item->alternate_unit_decimal_place = $request->alternate_unit_decimal_place;
    //             $item->conversion_of_alternate_to_base_unit_value = $request->conversion_of_alternate_to_base_unit_value;
    //         }

    //         $item->has_compound_unit = strtolower($request->has_compound_unit);

    //         if ($request->has('has_compound_unit') && strtolower($request->has_compound_unit) == "yes") {

    //             $item->compound_measuring_unit = $request->compound_measuring_unit;
    //             $item->compound_unit_short_name = $request->compound_unit_short_name;
    //             $item->compound_unit_decimal_place = $request->compound_unit_decimal_place;
    //             $item->conversion_of_compound_to_alternate_unit_value = $request->conversion_of_compound_to_alternate_unit_value;
    //         }

    //         $item->opening_stock = $request->opening_stock;
    //         $item->opening_stock_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->opening_stock_date)));
    //         $item->opening_stock_rate = $request->opening_stock_rate;
    //         $item->opening_stock_value = $request->opening_stock_value;

    //         $item->item_under_rcm = $request->item_under_rcm;

    //         if ($request->has('mrp')) {
    //             $item->mrp = $request->mrp;
    //         }

    //         if ($request->has('purchase_price')) {
    //             $item->purchase_price = $request->purchase_price;
    //         }

    //         if ($request->has('sale_price')) {
    //             $item->sale_price = $request->sale_price;
    //         }

    //         if ($request->has('barcode')) {
    //             $item->barcode = $request->barcode;
    //         }

    //         $item->qty = $request->opening_stock;

    //         $item->free_qty = $request->free_qty;

    //         $item->user_id = $request->user_id;

    //         $item->created_on = 'mobile';

    //         if ($item->save()) {
    //             return response()->json(['message' => 'success', 'data' => null], 200);
    //         } else {
    //             return response()->json(['message' => 'failure', 'data' => null], 200);
    //         }

    //     } else {
    //         return response()->json(['message' => 'invalid_key', 'data' => null], 401);
    //     }
    // }

    public function store_item(Request $request)
    {
        if (request('ukey') === 'JFYqdr4ARNZpRrhNW8B0cRZ350cC1EJD') {
            $item = new Item;

            $item->type = $request->type_of_service;
            $item->name = $request->name;
            $item->category = $request->category;
            if ($request->has('hsc_code')) {
                $item->hsc_code = $request->hsc_code;
            }

            if ($request->has('sac_code')) {
                $item->sac_code = $request->sac_code;
            }
            $item->group_id = $request->group;
            $item->igst = $request->igst;
            $item->cgst = $request->cgst;
            $item->sgst = $request->sgst;
            $item->gst = $request->gst;

            if ($request->has('manufacture')) {
                $item->manufacture = date('Y-m-d', strtotime(str_replace('/', '-', $request->manufacture)));
            }

            if ($request->has('expiry')) {
                $item->expiry = date('Y-m-d', strtotime(str_replace('/', '-', $request->expiry)));
            }

            if ($request->has('batch')) {
                $item->batch = $request->batch;
            }

            if ($request->has('size')) {
                $item->size = $request->size;
            }

            $item->has_additional_items = $request->has_additional_items;

            $item->measuring_unit = $request->measuring_unit;

            $opening_stock = $request->opening_stock;

            if ($request->has('measuring_unit_short_name')) {
                $item->measuring_unit_short_name = $request->measuring_unit_short_name;
            }

            if ($request->has('measuring_unit_decimal_place')) {
                $item->measuring_unit_decimal_place = $request->measuring_unit_decimal_place;
            }

            $item->has_alternate_unit = $request->has_alternate_unit;

            if ($request->has('has_alternate_unit') && strtolower($request->has_alternate_unit) == "yes") {

                $alternate_unit_input = $request->alternate_unit_input;
                $conversion_of_alternate_to_base = $request->conversion_of_alternate_to_base_unit_value;

                $base_unit_conversion = $conversion_of_alternate_to_base / $alternate_unit_input;

                if ($request->has('opening_stock_unit') && $request->opening_stock_unit == $request->alternate_measuring_unit) {
                    $opening_stock = $base_unit_conversion * $opening_stock;
                }

                $item->alternate_unit_input = $alternate_unit_input;
                $item->alternate_measuring_unit = $request->alternate_measuring_unit;
                $item->alternate_unit_short_name = $request->alternate_unit_short_name;
                $item->alternate_unit_decimal_place = $request->alternate_unit_decimal_place;
                $item->conversion_of_alternate_to_base_unit_value = $request->conversion_of_alternate_to_base_unit_value;
            }

            $item->has_compound_unit = $request->has_compound_unit;

            if ($request->has('has_compound_unit') && strtolower($request->has_compound_unit) == "yes") {

                $alternate_unit_input = $request->alternate_unit_input;
                $conversion_of_alternate_to_base = $request->conversion_of_alternate_to_base_unit_value;

                $base_unit_conversion = $conversion_of_alternate_to_base / $alternate_unit_input;

                //-----------------------------------

                $compound_unit_input = $request->compound_unit_input;
                $conversion_of_compound_to_alternate_unit_value = $request->conversion_of_compound_to_alternate_unit_value;

                $alt_unit_conversion = $conversion_of_compound_to_alternate_unit_value / $compound_unit_input;



                if ($request->has('opening_stock_unit') && $request->opening_stock_unit == $request->compound_measuring_unit) {
                    $opening_stock = $base_unit_conversion * $alt_unit_conversion * $opening_stock;
                }

                $item->compound_unit_input = $compound_unit_input;
                $item->compound_measuring_unit = $request->compound_measuring_unit;
                $item->compound_unit_short_name = $request->compound_unit_short_name;
                $item->compound_unit_decimal_place = $request->compound_unit_decimal_place;
                $item->conversion_of_compound_to_alternate_unit_value = $request->conversion_of_compound_to_alternate_unit_value;
            }

            $item->opening_stock = $opening_stock;

            $item->opening_stock_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->opening_stock_date)));
            $item->opening_stock_rate = $request->opening_stock_rate;
            $item->opening_stock_value = $request->opening_stock_value;

            $item->item_under_rcm = $request->item_under_rcm;

            if ($request->has('mrp')) {
                $item->mrp = $request->mrp;
            }

            if ($request->has('purchase_price')) {
                $item->purchase_price = $request->purchase_price;
            }

            if ($request->has('sale_price')) {
                $item->sale_price = $request->sale_price;
            }

            if ($request->has('barcode')) {
                $item->barcode = $request->barcode;
            }

            $item->qty = $request->opening_stock;

            $item->free_qty = $request->free_qty;

            $item->user_id = $request->user_id;

            if ($item->save()) {
                return response()->json(['message' => 'success', 'data' => null], 201);
            } else {
                return response()->json(['message' => 'failure', 'data' => null], 400);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function generate_cashbook(Request $request)
    {
        if ($request->ukey === 'W55IpyzzJF4JLNisXLaUKxI8i0xrF93d') {
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User does not exist']);
            }

            if( $request->has('from_date') && $request->has('to_date') ){
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            if ($from_date < $user->profile->book_beginning_from) {
                return response()->json(['success' => false, 'message' => 'Please select dates on or after the book beginning date']);
            }

            $opening_balance_from_date = $user->profile->financial_year_from;
            $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

            $closing_balance_from_date = $user->profile->book_beginning_from;
            $closing_balance_to_date = $user->profile->financial_year_from;

            $opening_balance = $this->fetch_cash_static_balance($user, $opening_balance_from_date, $opening_balance_to_date);
            
            if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
                $this_balance = $this->fetch_cash_opening_balance($user, $opening_balance_from_date, $opening_balance_to_date);
                
                $opening_balance += $this_balance;
            }

            $closing_balance = 0;
            
            if($opening_balance == 0){
                $closing_balance = $this->calculate_cash_closing_balance($user, $closing_balance_from_date, $closing_balance_to_date);
            }

            $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

            $purchases = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

            $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

            $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

            $sale_orders = SaleOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

            $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

            $gst_payments = CashGST::where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->get();

            $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

            $cash_deposited = CashDeposit::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

            $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

            $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

            $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

            $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

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
                foreach ($item as $key => $value) {
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

        

            $cash_in_hand = CashInHand::where('user_id', $user->id)->orderBy('id', 'desc')->first();

            return response()->json(['message' => 'success', 'data' => ['opening_balance' => $opening_balance, 'closing_balance' => $closing_balance, 'combined_array' => $combined_array, 'cash_in_hand' => $cash_in_hand, 'from_date' => $from_date, 'to_date' => $to_date]], 200);
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    private function calculate_cash_closing_balance($user, $from_date, $to_date)
    {
        $from_date = \Carbon\Carbon::parse($from_date);
        $to_date = \Carbon\Carbon::parse($to_date);
        
        $isDatesSame = false;
        if($from_date->eq($to_date)){
            $isDatesSame = true;
        }

        $closing_balance = 0;

        $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $purchases = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $sale_orders = SaleOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $gst_payments = CashGST::where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $cash_deposited = CashDeposit::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();


        // $cash_in_hand = CashInHand::where('user_id', Auth::user()->id)->whereBetween('balance_date', [$from_date, $to_date])->orderBy('id', 'desc')->first();


        $q = CashInHand::where('user_id', $user->id);
        $cashInHand = 0;
        if ($isDatesSame){
            $q = $q->where('balance_date', $from_date)->orderBy('id', 'desc');
            $cash_in_hand = $q->first();

            if( isset($cash_in_hand) && !empty($cash_in_hand) ){
                if ($cash_in_hand->balance_type == 'creditor') {
                    $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
                } else {
                    $fixed_opening_balance = $cash_in_hand->opening_balance;
                }
                $cashInHand = $fixed_opening_balance;
            }

        } else {
            $q = $q->whereBetween('balance_date', [$from_date, $to_date])->orderBy('id', 'desc');

            // can be "get" if need multiple or "first" if need only one
            $cash_in_hand = $q->first();

            if( isset($cash_in_hand) && !empty($cash_in_hand) ){
                if ($cash_in_hand->balance_type == 'creditor') {
                    $fixed_opening_balance = "-" . $cash_in_hand->opening_balance;
                } else {
                    $fixed_opening_balance = $cash_in_hand->opening_balance;
                }
                $cashInHand = $fixed_opening_balance;
            }

        }


        if($cash_in_hand != null){
            $closing_balance += $cashInHand;
        }

        foreach( $sales as $sale ){
            $closing_balance += $sale->cash_payment;
        }

        foreach( $purchases as $purchase ){
            $closing_balance -= $purchase->cash_payment;
        }

        foreach( $payments as $payment ){
            $closing_balance -= $payment->cash_payment;
        }

        foreach( $receipts as $receipt ){
            $closing_balance += $receipt->cash_payment;
        }

        foreach( $sale_orders as $order ){
            $closing_balance += $order->cash_amount;
        }

        foreach( $purchase_orders as $order ){
            $closing_balance -= $order->cash_amount;
        }

        foreach( $gst_payments as $gst ){
            $closing_balance -= $gst->cash_amount;
        }

        foreach( $cash_withdrawn as $cash ){
            $closing_balance += $cash->amount;
        }

        foreach( $cash_deposited as $cash ){
            $closing_balance -= $cash->amount;
        }

        foreach( $sale_party_payments as $payment ){
            $closing_balance += $payment->cash_payment;
        }

        foreach( $purchase_party_payments as $payment ){
            $closing_balance -= $payment->cash_payment;
        }

        foreach( $setoff_make_payments as $payment ){
            $closing_balance -= $payment->cash_payment;
        }

        return $closing_balance;
    }

    private function fetch_cash_static_balance($user, $from, $to)
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

        $q = CashInHand::where('user_id', $user->id);

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

    private function fetch_cash_opening_balance($user, $from_date, $to_date)
    {
        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date);
        // subtract a minute as we want to search till that date
        // eg if to_date is 03-04-2020 subtract 1 min will become 02-04-2020 23:59:00
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();

        // commented cause opening balance will only be for 01-04
        // $opening_balance += $this->fetch_static_balance($from_date, $to_date);

        $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $purchases = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [ $from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'cash+bank', 'cash+pos', 'cash'])->get();

        $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $sale_orders = SaleOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date',[ $from_date, $to_date])->groupBy('token')->get();

        $gst_payments = CashGST::where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $cash_deposited = CashDeposit::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->get();

        $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.status', 1)->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->get();

        $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_cash_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'pos+cash+discount', 'bank+cash+discount', 'cash+discount', 'cash+bank+pos', 'bank+cash', 'cash+pos', 'cash'])->whereBetween('date', [$from_date, $to_date])->get();


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

    public function generate_bankbook(Request $request)
    {
        if ($request->ukey === 'QZATTz3d5DlNqkyAwGjvB1nhpGRK1Wdg') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);

                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'User does not exist']);
                }
            }

            $bank = User::find($user->id)->banks()->where('id', $request->bank_id)->first();

            if (!$bank) {
                return response()->json(['success' => true, 'message' => 'Invalid Bank']);
            }

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime( str_replace('/', '-', $request->from_date) ));

                $to_date = date('Y-m-d', strtotime( str_replace('/', '-', $request->to_date) ));
            } else {
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            if ($from_date < $user->profile->book_beginning_from) {
                return response()->json(['success' => false, 'message' => 'Please select dates on or after the book beginning date']);
            }

            $opening_balance_from_date = $user->profile->financial_year_from;
            $opening_balance_to_date = \Carbon\Carbon::parse($from_date);

            $closing_balance_from_date = $user->profile->book_beginning_from;
            $closing_balance_to_date = $user->profile->financial_year_from;


            $opening_balance = $this->fetch_bank_static_balance($user, $bank, $opening_balance_from_date, $opening_balance_to_date);


            if(! \Carbon\Carbon::parse($opening_balance_from_date)->eq(\Carbon\Carbon::parse($from_date)) ){
                $this_balance = $this->fetch_bank_opening_balance($user, $bank, $opening_balance_from_date, $opening_balance_to_date);

                $opening_balance += $this_balance;
            }

            $closing_balance = 0;
            
            if($opening_balance == 0){
                // $ this->fetch_opening_balance();
                $closing_balance = $this->calculate_bank_closing_balance($user, $bank, $closing_balance_from_date, $closing_balance_to_date);
            }  

            $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

            $sales_pos = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

            $purchases = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

            $purchases_pos = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

            $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

            $payments_pos = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

            $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

            $receipts_pos = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

            $sale_orders = SaleOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

            $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

            // $gst_payments = CashGST::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->get();

            $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

            $cash_deposited = CashDeposit::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

            $from_bank_transfers = BankToBankTransfer::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

            $to_bank_transfers = BankToBankTransfer::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

            $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

            $sale_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

            $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

            $purchase_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

            $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

            $setoff_make_pos_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

            $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

            $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

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
                    }
                }
            }

            if( isset($cash_deposited) && !empty($cash_deposited) ){
                foreach ($cash_deposited as $cash) {
                    $current_bank = Bank::find($cash->bank);

                    $cash->bank_name = $current_bank->name;

                    if($cash->amount != null && $cash->amount > 0) {

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

            return response()->json(['success' => true, 'opening_balance' => $opening_balance, 'closing_balance' => $closing_balance, 'combined_array' => $combined_array, 'from_date' => $from_date, 'to_date' => $to_date, 'bank' => $bank]);
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    private function fetch_bank_opening_balance($user, $bank, $from_date, $to_date) 
    {

        $opening_balance = 0;

        $from_date = \Carbon\Carbon::parse($from_date);
        // subtract a minute as we want to search till that date
        // eg if to_date is 03-04-2020 subtract 1 min will become 02-04-2020 23:59:00
        $to_date = \Carbon\Carbon::parse($to_date)->subMinute();

        // commented cause opening balance will only be for 01-04
        // $opening_balance += $this->fetch_static_balance($from_date, $to_date);

        $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $sales_pos = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $purchases = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $purchases_pos = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        $payments_pos = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        $receipts_pos = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        $sale_orders = SaleOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        // $gst_payments = CashGST::where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $cash_deposited = CashDeposit::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $from_bank_transfers = BankToBankTransfer::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

        $to_bank_transfers = BankToBankTransfer::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

        $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $sale_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $purchase_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $setoff_make_pos_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();


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

    private function calculate_bank_closing_balance($user, $bank, $from_date, $to_date)
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

        $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $sales_pos = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $purchases = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

        $purchases_pos = User::find($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

        $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

        $payments_pos = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.is_original_payment', 0)->where('purchase_remaining_amounts.status', 1)->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

        $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

        $receipts_pos = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.is_original_payment', 0)->where('sale_remaining_amounts.status', 1)->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

        $sale_orders = SaleOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->groupBy('token')->get();

        // $gst_payments = CashGST::where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->get();

        $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $cash_deposited = CashDeposit::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

        $from_bank_transfers = BankToBankTransfer::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('from_bank', $bank->id)->get();

        $to_bank_transfers = BankToBankTransfer::where('user_id', $user->id)->where('status', 1)->whereBetween('date', [$from_date, $to_date])->where('to_bank', $bank->id)->get();

        $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $sale_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

        $purchase_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->where('party_pending_payment_account.status', 1)->whereIn( 'party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

        $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $setoff_make_pos_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

        $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();


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

    private function fetch_bank_static_balance($user, $bank, $from, $to)
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

    // public function generate_bankbook(Request $request)
    // {
    //     if ($request->ukey === 'QZATTz3d5DlNqkyAwGjvB1nhpGRK1Wdg') {

    //         if ($request->has('user_id')) {
    //             $user = User::find($request->user_id);

    //             if (!$user) {
    //                 return response()->json(['success' => false, 'message' => 'User does not exist']);
    //             }
    //         }

    //         $bank = User::find($user->id)->banks()->where('id', $request->bank_id)->first();

    //         if (!$bank) {
    //             return response()->json(['success' => true, 'message' => 'Invalid Bank']);
    //         }

    //         // return $bank;

    //         if ($request->has('from_date') && $request->has('to_date')) {
    //             $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));

    //             $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
    //         } else {
    //             $from_date = $user->profile->financial_year_from;
    //             $to_date = $user->profile->financial_year_to;
    //         }

    //         if ($from_date < $user->profile->book_beginning_from) {
    //             return response()->json(['success' => false, 'message' => 'Please select dates on or after the book beginning date']);
    //         }

    //         if ($user->profile->financial_year_from <= $from_date && $user->profile->financial_year_to >= $from_date) {
    //             $opening_balance_from_date = $user->profile->financial_year_from;
    //             $opening_balance_to_date = $from_date;
    //         } else {
    //             $month = Carbon::parse($from_date)->format('m');

    //             if ($month == "01" || $month == "02" || $month == "03") {
    //                 $year = Carbon::parse($from_date)->format('Y') - 1;
    //             } else {
    //                 $year = Carbon::parse($from_date)->format('Y');
    //             }

    //             $opening_balance_from_date = $year . "-04" . "-01";
    //             $opening_balance_to_date = $from_date;
    //         }

    //         $static_opening_balance_from = $opening_balance_from_date;
    //         $static_opening_balance_to = $to_date;

    //         $closing_balance_till_date = $opening_balance_from_date;


    //         $opening_balance = $this->fetch_bank_static_balance($user, $bank, $static_opening_balance_to, $static_opening_balance_from);
    //         $closing_balance = 0;

    //         if ($opening_balance == null) {
    //             $opening_balance = $this->calculated_bank_opening_balance($user, $bank, $opening_balance_from_date, $opening_balance_to_date);

    //             $closing_balance = $this->calculated_bank_closing_balance($user, $bank, $closing_balance_till_date);
    //             $closing_balance += $this->fetch_bank_static_balance($user, $bank, $closing_balance_till_date);
    //         }

    //         $sales = User::findOrFail($user->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

    //         $sales_pos = User::findOrFail($user->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

    //         $purchases = User::find($user->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('bank_id', $bank->id)->get();

    //         $purchases_pos = User::find($user->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('pos_bank_id', $bank->id)->get();

    //         $payments = User::find($user->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank->id)->get();

    //         $payments_pos = User::find($user->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank->id)->get();

    //         $receipts = User::find($user->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('sale_remaining_amounts.bank_id', $bank->id)->get();

    //         $receipts_pos = User::find($user->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank->id)->get();

    //         $sale_orders = SaleOrder::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->get();

    //         $purchase_orders = PurchaseOrder::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank->id)->get();

    //         // $gst_payments = CashGST::where('user_id', $user->id)->whereBetween('created_at', [$from_date, $to_date])->get();

    //         $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

    //         $cash_deposited = CashDeposit::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank->id)->get();

    //         $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

    //         $sale_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

    //         $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank->id)->get();

    //         $purchase_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank->id)->get();

    //         $setoff_make_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

    //         $setoff_make_pos_payments = GSTSetOff::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();

    //         $advance_cash_ledger_bank_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+bank+discount', 'bank+pos+discount', 'bank+cash', 'pos+bank', 'bank+discount', 'bank'])->whereBetween('date', [$from_date, $to_date])->get();

    //         $advance_cash_ledger_pos_payments = GSTCashLedgerBalance::where('user_id', $user->id)->whereIn('type_of_payment', ['combined', 'cash+bank+pos', 'cash+pos+discount', 'bank+pos+discount', 'pos+cash', 'pos+bank', 'pos+discount', 'pos'])->whereBetween('date', [$from_date, $to_date])->get();



    //         $combined_array = array();

    //         $sale_array = array();
    //         $sale_pos_array = array();
    //         $purchase_array = array();
    //         $purchase_pos_array = array();
    //         $sale_order_array = array();
    //         $purchase_order_array = array();
    //         $gst_payment_array = array();
    //         $receipt_array = array();
    //         $receipt_pos_array = array();
    //         $payment_array = array();
    //         $payment_pos_array = array();
    //         $sale_party_payment_array = array();
    //         $purchase_party_payment_array = array();
    //         $cash_withdrawn_array = array();
    //         $cash_deposited_array = array();

    //         $setoff_make_payment_array = array();
    //         $setoff_make_pos_payment_array = array();

    //         $advance_payment_bank = array();
    //         $advance_payment_pos = array();

    //         if (isset($sales) && !empty($sales)) {
    //             foreach ($sales as $sale) {

    //                 if ($sale->bank_payment != null && $sale->bank_payment > 0) {
    //                     $sale_array[] = [
    //                         'routable' => $sale->id,
    //                         'particulars' => $sale->party->name,
    //                         'voucher_type' => 'Sale',
    //                         'voucher_no' => $sale->invoice_no,
    //                         'amount' => $sale->bank_payment,
    //                         'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($sale->invoice_date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'sale',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($sales_pos) && !empty($sales_pos)) {
    //             foreach ($sales_pos as $sale) {

    //                 if ($sale->pos_payment != null && $sale->pos_payment > 0) {
    //                     $sale_pos_array[] = [
    //                         'routable' => $sale->id,
    //                         'particulars' => $sale->party->name,
    //                         'voucher_type' => 'Sale POS',
    //                         'voucher_no' => $sale->invoice_no,
    //                         'amount' => $sale->pos_payment,
    //                         'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($sale->invoice_date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'sale_pos',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($purchases) && !empty($purchases)) {
    //             foreach ($purchases as $purchase) {

    //                 if ($purchase->bank_payment != null && $purchase->bank_payment > 0) {
    //                     $purchase_array[] = [
    //                         'routable' => $purchase->id,
    //                         'particulars' => $purchase->party->name,
    //                         'voucher_type' => 'Purchase',
    //                         'voucher_no' => $purchase->bill_no,
    //                         'amount' => $purchase->bank_payment,
    //                         'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($purchase->bill_date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'purchase',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($purchases_pos) && !empty($purchases)) {

    //             foreach ($purchases_pos as $purchase) {

    //                 if ($purchase->pos_payment != null && $purchase->pos_payment > 0) {
    //                     $purchase_pos_array[] = [
    //                         'routable' => $purchase->id,
    //                         'particulars' => $purchase->party->name,
    //                         'voucher_type' => 'Purchase POS',
    //                         'voucher_no' => $purchase->bill_no,
    //                         'amount' => $purchase->pos_payment,
    //                         'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($purchase->bill_date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'purchase_pos',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($sale_orders) && !empty($sale_orders)) {
    //             foreach ($sale_orders as $order) {
    //                 $bankOp = 'bank' . $order->token;
    //                 $posOp = 'pos' . $order->token;

    //                 if ($order->bank_amount != null && $order->bank_amount > 0) {
    //                     $sale_order_array[$bankOp] = [
    //                         'routable' => $order->token,
    //                         'particulars' => $order->party->name,
    //                         'voucher_type' => 'Sale Order (Bank)',
    //                         'voucher_no' => $order->token,
    //                         'amount' => $order->bank_amount,
    //                         'date' => Carbon::parse($order->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($order->date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'sale_order',
    //                         'type' => 'showable'
    //                     ];
    //                 }

    //                 if ($order->pos_amount != null && $order->pos_amount > 0) {
    //                     $sale_order_array[$posOp] = [
    //                         'routable' => $order->token,
    //                         'particulars' => $order->party->name,
    //                         'voucher_type' => 'Sale Order (POS)',
    //                         'voucher_no' => $order->token,
    //                         'amount' => $order->pos_amount,
    //                         'date' => Carbon::parse($order->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($order->date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'sale_order',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($purchase_orders) && !empty($purchase_orders)) {
    //             foreach ($purchase_orders as $order) {
    //                 $bankOp = 'bank' . $order->token;
    //                 $posOp = 'pos' . $order->token;

    //                 if ($order->bank_amount != null && $order->bank_amount > 0) {
    //                     $purchase_order_array[$bankOp] = [
    //                         'routable' => $order->token,
    //                         'particulars' => $order->party->name,
    //                         'voucher_type' => 'Purchase Order',
    //                         'voucher_no' => $order->token,
    //                         'amount' => $order->bank_amount,
    //                         'date' => Carbon::parse($order->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($order->date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'purchase_order',
    //                         'type' => 'showable'
    //                     ];
    //                 }

    //                 if ($order->pos_amount != null && $order->pos_amount > 0) {
    //                     $purchase_order_array[$posOp] = [
    //                         'routable' => $order->token,
    //                         'particulars' => $order->party->name,
    //                         'voucher_type' => 'Purchase Order',
    //                         'voucher_no' => $order->token,
    //                         'amount' => $order->pos_amount,
    //                         'date' => Carbon::parse($order->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($order->date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'purchase_order',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($receipts) && !empty($receipts)) {
    //             foreach ($receipts as $receipt) {
    //                 // $opening_balance += $receipt->cash_payment;

    //                 $party = Party::find($receipt->party_id);

    //                 $receipt->party_name = $party->name;

    //                 if ($receipt->bank_payment != null && $receipt->bank_payment > 0) {
    //                     $receipt_array[] = [
    //                         'routable' => $receipt->id,
    //                         'particulars' => $receipt->party_name,
    //                         'voucher_type' => 'Receipt',
    //                         'voucher_no' => $receipt->voucher_no,
    //                         'amount' => $receipt->bank_payment,
    //                         'date' => Carbon::parse($receipt->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($receipt->payment_date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'receipt',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($receipts_pos) && !empty($receipts_pos)) {

    //             foreach ($receipts_pos as $receipt) {

    //                 $party = Party::find($receipt->party_id);

    //                 $receipt->party_name = $party->name;

    //                 if ($receipt->bank_payment != null && $receipt->bank_payment > 0) {
    //                     $receipt_array[] = [
    //                         'routable' => $receipt->id,
    //                         'particulars' => $receipt->party_name,
    //                         'voucher_type' => 'Receipt POS',
    //                         'voucher_no' => $receipt->voucher_no,
    //                         'amount' => $receipt->bank_payment,
    //                         'date' => Carbon::parse($receipt->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($receipt->payment_date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'receipt_pos',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($payments) && !empty($payments)) {
    //             foreach ($payments as $payment) {
    //                 // $opening_balance -= $payment->cash_payment;

    //                 $party = Party::find($payment->party_id);

    //                 $payment->party_name = $party->name;

    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $payment_array[] = [
    //                         'routable' => $payment->id,
    //                         'particulars' => $payment->party_name,
    //                         'voucher_type' => 'Payment',
    //                         'voucher_no' => $payment->voucher_no,
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($payments_pos) && !empty($payments_pos)) {
    //             foreach ($payments_pos as $payment) {
    //                 // $opening_balance -= $payment->cash_payment;

    //                 $party = Party::find($payment->party_id);

    //                 $payment->party_name = $party->name;

    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $payment_array[] = [
    //                         'routable' => $payment->id,
    //                         'particulars' => $payment->party_name,
    //                         'voucher_type' => 'Payment POS',
    //                         'voucher_no' => $payment->voucher_no,
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'payment_pos',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($sale_party_payments) && !empty($sale_party_payments)) {
    //             foreach ($sale_party_payments as $payment) {
    //                 $party = Party::find($payment->party_id);

    //                 $payment->party_name = $party->name;

    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $sale_party_payment_array[] = [
    //                         'routable' => $payment->id,
    //                         'particulars' => $payment->party_name,
    //                         'voucher_type' => 'Sale Party Receipt',
    //                         'voucher_no' => $payment->voucher_no,
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'sale_party_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($sale_party_pos_payments) && !empty($sale_party_pos_payments)) {
    //             foreach ($sale_party_pos_payments as $payment) {
    //                 $party = Party::find($payment->party_id);

    //                 $payment->party_name = $party->name;

    //                 if ($payment->pos_payment != null && $payment->pos_payment > 0) {
    //                     $sale_party_payment_array[] = [
    //                         'routable' => $payment->id,
    //                         'particulars' => $payment->party_name,
    //                         'voucher_type' => 'Sale Party Receipt POS',
    //                         'voucher_no' => $payment->voucher_no,
    //                         'amount' => $payment->pos_payment,
    //                         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                         'transaction_type' => 'debit',
    //                         'loop' => 'sale_party_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($purchase_party_payments) && !empty($purchase_party_payments)) {
    //             foreach ($purchase_party_payments as $payment) {
    //                 $party = Party::find($payment->party_id);

    //                 $payment->party_name = $party->name;

    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $purchase_party_payment_array[] = [
    //                         'routable' => $payment->id,
    //                         'particulars' => $payment->party_name,
    //                         'voucher_type' => 'Purchase Party Payment',
    //                         'voucher_no' => $payment->voucher_no,
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'purchase_party_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($purchase_party_pos_payments) && !empty($purchase_party_pos_payments)) {
    //             foreach ($purchase_party_pos_payments as $payment) {
    //                 $party = Party::find($payment->party_id);

    //                 $payment->party_name = $party->name;

    //                 if ($payment->pos_payment != null && $payment->pos_payment > 0) {
    //                     $purchase_party_payment_array[] = [
    //                         'routable' => $payment->id,
    //                         'particulars' => $payment->party_name,
    //                         'voucher_type' => 'Purchase Party Payment POS',
    //                         'voucher_no' => $payment->voucher_no,
    //                         'amount' => $payment->pos_payment,
    //                         'date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->payment_date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'purchase_party_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($cash_withdrawn) && !empty($cash_withdrawn)) {
    //             foreach ($cash_withdrawn as $cash) {
    //                 $bank = Bank::find($cash->bank);

    //                 $cash->bank_name = $bank->name;

    //                 if ($cash->amount != null && $cash->amount > 0) {
    //                     if ($bank->classification == 'current asset' || $bank->classification == 'fixed asset') {
    //                         $cash_withdrawn_array[] = [
    //                             'routable' => $cash->id,
    //                             'particulars' => $cash->bank_name,
    //                             'voucher_type' => 'Contra',
    //                             'voucher_no' => $cash->contra,
    //                             'amount' => $cash->amount,
    //                             'date' => Carbon::parse($cash->date)->format('Y-m-d'),
    //                             'month' => Carbon::parse($cash->date)->format('m'),
    //                             'transaction_type' => 'debit',
    //                             'loop' => 'cash_withdraw',
    //                             'type' => 'showable'
    //                         ];
    //                     } else {
    //                         $cash_withdrawn_array[] = [
    //                             'routable' => $cash->id,
    //                             'particulars' => $cash->bank_name,
    //                             'voucher_type' => 'Contra',
    //                             'voucher_no' => $cash->contra,
    //                             'amount' => $cash->amount,
    //                             'date' => Carbon::parse($cash->date)->format('Y-m-d'),
    //                             'month' => Carbon::parse($cash->date)->format('m'),
    //                             'transaction_type' => 'credit',
    //                             'loop' => 'cash_withdraw',
    //                             'type' => 'showable'
    //                         ];
    //                     }
    //                 }
    //             }
    //         }

    //         if (isset($cash_deposited) && !empty($cash_deposited)) {
    //             foreach ($cash_deposited as $cash) {
    //                 $bank = Bank::find($cash->bank);

    //                 $cash->bank_name = $bank->name;

    //                 if ($cash->amount != null && $cash->amount > 0) {
    //                     if ($bank->classification == 'current asset' || $bank->classification == 'fixed asset') {
    //                         $cash_deposited_array[] = [
    //                             'routable' => $cash->id,
    //                             'particulars' => $cash->bank_name,
    //                             'voucher_type' => 'Contra',
    //                             'voucher_no' => $cash->contra,
    //                             'amount' => $cash->amount,
    //                             'date' => Carbon::parse($cash->date)->format('Y-m-d'),
    //                             'month' => Carbon::parse($cash->date)->format('m'),
    //                             'transaction_type' => 'credit',
    //                             'loop' => 'cash_deposit',
    //                             'type' => 'showable'
    //                         ];
    //                     } else {
    //                         $cash_deposited_array[] = [
    //                             'routable' => $cash->id,
    //                             'particulars' => $cash->bank_name,
    //                             'voucher_type' => 'Contra',
    //                             'voucher_no' => $cash->contra,
    //                             'amount' => $cash->amount,
    //                             'date' => Carbon::parse($cash->date)->format('Y-m-d'),
    //                             'month' => Carbon::parse($cash->date)->format('m'),
    //                             'transaction_type' => 'debit',
    //                             'loop' => 'cash_deposit',
    //                             'type' => 'showable'
    //                         ];
    //                     }
    //                 }
    //             }
    //         }

    //         if (isset($setoff_make_payments) && !empty($setoff_make_payments)) {
    //             foreach ($setoff_make_payments as $payment) {

    //                 $bank = Bank::find($payment->bank_id);

    //                 if ($bank) {
    //                     $payment->bank_name = $bank->name;
    //                 } else {
    //                     $payment->bank_name = null;
    //                 }

    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $setoff_make_payment_array[] = [
    //                         'routable' => '',
    //                         'particulars' => 'GST Setoff Make Payment',
    //                         'voucher_type' => 'Setoff Payment',
    //                         'voucher_no' => $payment->id,
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'setoff_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($setoff_make_pos_payments) && !empty($setoff_make_pos_payments)) {
    //             foreach ($setoff_make_pos_payments as $payment) {

    //                 $bank = Bank::find($payment->pos_bank_id);

    //                 if ($bank) {
    //                     $payment->bank_name = $bank->name;
    //                 } else {
    //                     $payment->bank_name = null;
    //                 }

    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $setoff_make_pos_payment_array[] = [
    //                         'routable' => '',
    //                         'particulars' => 'GST Setoff Make Payment',
    //                         'voucher_type' => 'Setoff Payment',
    //                         'voucher_no' => $payment->id,
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'setoff_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($advance_cash_ledger_bank_payments) && !empty($advance_cash_ledger_bank_payments)) {
    //             foreach ($advance_cash_ledger_bank_payments as $payment) {
    //                 if ($payment->bank_payment != null && $payment->bank_payment > 0) {
    //                     $advance_payment_bank[] = [
    //                         'routable' => '',
    //                         'particulars' => 'Advance Payment',
    //                         'voucher_type' => 'Payment',
    //                         'amount' => $payment->bank_payment,
    //                         'date' => Carbon::parse($payment->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'advanced_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         if (isset($advance_cash_ledger_pos_payments) && !empty($advance_cash_ledger_pos_payments)) {
    //             foreach ($advance_cash_ledger_pos_payments as $payment) {
    //                 if ($payment->pos_payment != null && $payment->pos_payment > 0) {
    //                     $advance_payment_pos[] = [
    //                         'routable' => '',
    //                         'particulars' => 'Advance Payment',
    //                         'voucher_type' => 'Payment',
    //                         'amount' => $payment->pos_payment,
    //                         'date' => Carbon::parse($payment->date)->format('Y-m-d'),
    //                         'month' => Carbon::parse($payment->date)->format('m'),
    //                         'transaction_type' => 'credit',
    //                         'loop' => 'advanced_payment',
    //                         'type' => 'showable'
    //                     ];
    //                 }
    //             }
    //         }

    //         $combined_array = array_merge(
    //             $sale_array,
    //             $sale_pos_array,
    //             $purchase_array,
    //             $sale_order_array,
    //             $purchase_pos_array,
    //             $purchase_order_array,
    //             $gst_payment_array,
    //             $receipt_array,
    //             $payment_array,
    //             $sale_party_payment_array,
    //             $purchase_party_payment_array,
    //             $cash_withdrawn_array,
    //             $cash_deposited_array,
    //             $setoff_make_payment_array,
    //             $setoff_make_pos_payment_array,
    //             $advance_payment_bank,
    //             $advance_payment_pos
    //         );

    //         $this->array_sort_by_column($combined_array, 'date');

    //         $group = [];
    //         foreach ($combined_array as $item) {
    //             $count = 0;
    //             $month = Carbon::parse($item['date'])->format('F');

    //             if (isset($group[$month])) {
    //                 foreach ($group[$month] as $key => $value) {
    //                     $count = $key;
    //                 }
    //             }
    //             $count++;
    //             // echo "<pre>";
    //             // print_r($item);
    //             // print_r( $group[$item['month']][$count] );
    //             foreach ($item as $key => $value) {
    //                 // if ($key == 'month') continue;
    //                 $group[$month][$count][$key] = $value;
    //             }
    //         }

    //         foreach ($group as $key => $value) {
    //             $creditTotal = 0;
    //             $debitTotal = 0;
    //             foreach ($value as $data) {
    //                 if ($data['transaction_type'] == 'credit') {
    //                     $creditTotal += $data['amount'];
    //                 } elseif ($data['transaction_type'] == 'debit') {
    //                     $debitTotal += $data['amount'];
    //                 }
    //             }
    //             $group[$key]['credit_total'] = $creditTotal;
    //             $group[$key]['debit_total'] = $debitTotal;
    //             $group[$key]['closing_total'] = $debitTotal - $creditTotal;
    //         }

    //         $combined_array = $group;


    //         return response()->json(['success' => true, 'opening_balance' => $opening_balance, 'closing_balance' => $closing_balance, 'combined_array' => $combined_array, 'from_date' => $from_date, 'to_date' => $to_date, 'bank' => $bank]);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
    //     }
    // }

    // private function fetch_bank_static_balance($bank, $to, $from = null)
    // {

    //     $opening_balance = null;

    //     if (isset($bank) && !empty($bank)) {

    //         if ($bank->balance_type == 'creditor' || $bank->classification == 'current liability' || $bank->classfication == 'fixed liability') {
    //             $fixed_opening_balance = "-" . $bank->opening_balance;
    //         } else {
    //             $fixed_opening_balance = $bank->opening_balance;
    //         }

    //         if ($from != null) {
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

    // private function calculated_bank_opening_balance($user, $bank, $opening_balance_from_date, $opening_balance_to_date)
    // {

    //     $opening_balance = 0;

    //     $from_date = $opening_balance_from_date;
    //     $to_date = $opening_balance_to_date;

    //     // return $bank;

    //     $bank_id = $bank->id;

    //     $sales = User::findOrFail($user->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('bank_id', $bank_id)->get();

    //     $sales_pos = User::findOrFail($user->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('pos_bank_id', $bank_id)->get();

    //     $purchases = User::find($user->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('bank_id', $bank_id)->get();

    //     $purchases_pos = User::find($user->id)->purchases()->whereBetween('bill_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('pos_bank_id', $bank_id)->get();

    //     $payments = User::find($user->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('purchase_remaining_amounts.bank_id', $bank_id)->get();

    //     $payments_pos = User::find($user->id)->purchaseRemainingAmounts()->whereBetween('purchase_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank_id)->get();

    //     $receipts = User::find($user->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('sale_remaining_amounts.bank_id', $bank_id)->get();

    //     $receipts_pos = User::find($user->id)->saleRemainingAmounts()->whereBetween('sale_remaining_amounts.payment_date', [$from_date, $to_date])->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank_id)->get();

    //     $sale_orders = SaleOrder::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank_id)->get();

    //     $purchase_orders = PurchaseOrder::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank_id', $bank_id)->get();

    //     // $gst_payments = CashGST::where('user_id', Auth::user()->id)->where('created_at', '<', $till_date)->get();

    //     $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank_id)->get();

    //     $cash_deposited = CashDeposit::where('user_id', $user->id)->whereBetween('date', [$from_date, $to_date])->where('bank', $bank_id)->get();

    //     $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank_id)->get();

    //     $sale_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('party_pending_payment_account.type', 'sale')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank_id)->get();

    //     $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.bank_id', $bank_id)->get();

    //     $purchase_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('party_pending_payment_account.type', 'purchase')->whereBetween('party_pending_payment_account.payment_date', [$from_date, $to_date])->where('party_pending_payment_account.pos_bank_id', $bank_id)->get();


    //     foreach ($sales as $sale) {
    //         $opening_balance += $sale->bank_payment;
    //     }

    //     foreach ($sales_pos as $sale) {
    //         $opening_balance += $sale->pos_payment;
    //     }

    //     foreach ($receipts as $receipt) {
    //         $opening_balance += $receipt->bank_payment;
    //     }

    //     foreach ($receipts_pos as $receipt) {
    //         $opening_balance += $receipt->pos_payment;
    //     }

    //     foreach ($sale_orders as $order) {
    //         $opening_balance += $order->bank_amount;
    //     }


    //     foreach ($purchases as $purchase) {
    //         $opening_balance -= $purchase->bank_payment;
    //     }

    //     foreach ($purchases_pos as $purchase) {
    //         $opening_balance -= $purchase->pos_payment;
    //     }

    //     foreach ($payments as $payment) {
    //         $opening_balance -= $payment->bank_payment;
    //     }

    //     foreach ($payments_pos as $payment) {
    //         $opening_balance -= $payment->pos_payment;
    //     }

    //     foreach ($purchase_orders as $order) {
    //         $opening_balance -= $order->bank_amount;
    //     }

    //     // foreach ($gst_payments as $payment) {
    //     //     $opening_balance -= $payment->cash_amount;
    //     // }


    //     foreach ($cash_withdrawn as $withdrawn) {
    //         $opening_balance -= $withdrawn->amount;
    //     }

    //     foreach ($cash_deposited as $deposited) {
    //         $opening_balance += $deposited->amount;
    //     }

    //     foreach ($sale_party_payments as $sale) {
    //         $opening_balance += $sale->bank_payment;
    //     }

    //     foreach ($sale_party_pos_payments as $sale) {
    //         $opening_balance += $sale->pos_payment;
    //     }

    //     foreach ($purchase_party_payments as $payment) {
    //         $opening_balance -= $payment->bank_payment;
    //     }

    //     foreach ($purchase_party_pos_payments as $payment) {
    //         $opening_balance -= $payment->pos_payment;
    //     }

    //     return $opening_balance;
    // }

    // private function calculated_bank_closing_balance($user, $bank, $till_date)
    // {
    //     $closing_balance = 0;

    //     // $year = Carbon::parse($till_date)->format('Y');

    //     // $from_date = $year - 1 . "-04-01";
    //     // $to_date = $year . "-03-31";

    //     $bank_id = $bank->id;

    //     $sales = User::findOrFail($user->id)->invoices()->where('invoice_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('bank_id', $bank_id)->get();

    //     $sales_pos = User::findOrFail($user->id)->invoices()->where('invoice_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('pos_bank_id', $bank_id)->get();

    //     $purchases = User::find($user->id)->purchases()->where('bill_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('bank_id', $bank_id)->get();

    //     $purchases_pos = User::find($user->id)->purchases()->where('bill_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('pos_bank_id', $bank_id)->get();

    //     $payments = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.payment_date', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('purchase_remaining_amounts.bank_id', '<', $bank_id)->get();

    //     $payments_pos = User::find($user->id)->purchaseRemainingAmounts()->where('purchase_remaining_amounts.payment_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('purchase_remaining_amounts.pos_bank_id', $bank_id)->get();

    //     $receipts = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.payment_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('sale_remaining_amounts.bank_id', $bank_id)->get();

    //     $receipts_pos = User::find($user->id)->saleRemainingAmounts()->where('sale_remaining_amounts.payment_date', '<', $till_date)->whereIn('type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('sale_remaining_amounts.pos_bank_id', $bank_id)->get();

    //     $sale_orders = SaleOrder::where('user_id', $user->id)->where('date', '<', $till_date)->where('bank_id', $bank_id)->get();

    //     $purchase_orders = PurchaseOrder::where('user_id', $user->id)->where('date', '<', $till_date)->where('bank_id', $bank_id)->get();

    //     $cash_withdrawn = CashWithdraw::where('user_id', $user->id)->where('date', '<', $till_date)->where('bank', $bank_id)->get();

    //     $cash_deposited = CashDeposit::where('user_id', $user->id)->where('date', '<', $till_date)->where('bank', $bank_id)->get();

    //     $sale_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.payment_date', '<', $till_date)->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.bank_id', $bank_id)->get();

    //     $sale_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('party_pending_payment_account.type', 'sale')->where('party_pending_payment_account.payment_date', '<', $till_date)->where('party_pending_payment_account.pos_bank_id', $bank_id)->get();

    //     $purchase_party_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'bank+cash', 'bank'])->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.payment_date', '<', $till_date)->where('party_pending_payment_account.bank_id', $bank_id)->get();

    //     $purchase_party_pos_payments = User::findOrFail($user->id)->partyRemainingAmounts()->whereIn('party_pending_payment_account.type_of_payment', ['combined', 'pos+bank', 'pos+cash', 'pos'])->where('party_pending_payment_account.type', 'purchase')->where('party_pending_payment_account.payment_date', '<', $till_date)->where('party_pending_payment_account.pos_bank_id', $bank_id)->get();


    //     foreach ($sales as $sale) {
    //         $closing_balance += $sale->bank_payment;
    //     }

    //     foreach ($sales_pos as $sale) {
    //         $closing_balance += $sale->pos_payment;
    //     }

    //     foreach ($purchases as $purchase) {
    //         $closing_balance -= $purchase->bank_payment;
    //     }

    //     foreach ($purchases_pos as $purchase) {
    //         $closing_balance -= $purchase->pos_payment;
    //     }

    //     foreach ($payments as $payment) {
    //         $closing_balance -= $payment->bank_payment;
    //     }

    //     foreach ($payments_pos as $payment) {
    //         $closing_balance -= $payment->pos_payment;
    //     }

    //     foreach ($receipts as $receipt) {
    //         $closing_balance += $receipt->bank_payment;
    //     }

    //     foreach ($receipts_pos as $receipt) {
    //         $closing_balance -= $receipt->pos_payment;
    //     }

    //     foreach ($sale_orders as $order) {
    //         $closing_balance += $order->bank_amount;
    //     }

    //     foreach ($purchase_orders as $order) {
    //         $closing_balance -= $order->bank_amount;
    //     }

    //     foreach ($cash_withdrawn as $cash) {
    //         $closing_balance -= $cash->amount;
    //     }

    //     foreach ($cash_deposited as $cash) {
    //         $closing_balance += $cash->amount;
    //     }

    //     foreach ($sale_party_payments as $payment) {
    //         $closing_balance += $payment->bank_payment;
    //     }

    //     foreach ($sale_party_pos_payments as $payment) {
    //         $closing_balance += $payment->pos_payment;
    //     }

    //     foreach ($purchase_party_payments as $payment) {
    //         $closing_balance -= $payment->bank_payment;
    //     }

    //     foreach ($purchase_party_pos_payments as $payment) {
    //         $closing_balance -= $payment->pos_payment;
    //     }

    //     return $closing_balance;
    // }

    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    public function post_cash_in_hand(Request $request)
    {

        if ($request->ukey === 'azLFYmHyxuNFutOcEcsWxdtzI3rQkUpc') {

            if ($request->has('user_id')) {

                $user = User::find($request->user_id);

                if ($user) {
                    $financialYearFrom = $user->profile->financial_year_from;
                    $financialYearTo = $user->profile->financial_year_to;

                    $cash_in_hand = CashInHand::where('user_id', $user->id)->whereBetween('balance_date', [$financialYearFrom, $financialYearTo])->first();

                    if ($cash_in_hand == null) {
                        $cash_in_hand = new CashInHand;
                    }

                    $cash_in_hand->opening_balance = $request->opening_balance;
                    $cash_in_hand->balance_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->balance_date)));
                    $cash_in_hand->balance_type = $request->balance_type;
                    $cash_in_hand->narration = $request->narration;
                    $cash_in_hand->user_id = $user->id;

                    if ($user->profile->financial_year_from > $cash_in_hand->balance_date) {
                        return response()->json(['message' => 'failure', 'data' => 'Please select valid opening balance date for current financial year']);
                    }

                    if ($user->profile->financial_year_to < $cash_in_hand->balance_date) {
                        return response()->json(['message' => 'failure', 'data' => 'Please select valid opening balance date for current financial year']);
                    }

                    $cash_in_hand->created_on = 'mobile';

                    if ($cash_in_hand->save()) {
                        return response()->json(['message' => 'success', 'data' => null], 200);
                    } else {
                        return response()->json(['message' => 'failure', 'data' => null]);
                    }
                } else {
                    return response()->json(['message' => 'invalid_user', 'data' => null]);
                }
            } else {
                return response()->json(['message' => 'user_required', 'data' => null]);
            }
        } else {
            return response()->json(['message' => 'invalid_key', 'data' => null], 401);
        }
    }

    public function update_opening_balance(Request $request)
    {
        if ($request->ukey === 'azLFYmHyxuNFutOcEcsWxdtzI3rQkUpc') {

            if (!$request->has('user_id')) {
                return response()->json(['success' => false, 'message' => 'User is required']);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User does not exist']);
            }

            if (!$request->has('bank_id')) {
                return response()->json(['success' => false, 'message' => 'Bank is required']);
            }

            $bank = Bank::find($request->bank_id);

            if (!$bank) {
                return response()->json(['success' => false, 'message' => 'Bank does not exist']);
            }

            $bank->opening_balance = $request->opening_balance;
            $bank->opening_balance_on_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->opening_balance_on_date)));
            $bank->balance_type = $request->balance_type;

            if ($bank->opening_balance_on_date != $user->profile->financial_year_from && $bank->opening_balance_on_date < $user->profile->financial_year_from) {
                return response()->json(['success' => false, 'message' => 'Please provide valid opening balance date for current financial year']);
            }

            if ($bank->opening_balance_on_date != $user->profile->financial_year_from && $bank->opening_balance_on_date > $user->profile->financial_year_to) {
                return response()->json(['success' => false, 'message' => 'Please provide valid opening balance date for current financial year']);
            }

            if ($bank->save()) {
                return response()->json(['success' => true, 'message' => 'Opening Balance updated successfully!!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to update bank balance!!']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function view_party(Request $request)
    {
        if ($request->ukey === 'NSFkbwWiLCiSvP5CRSf20HrJNKHEUujV') {
            if ($request->has('id')) {
                $party = Party::find($request->id);
            } else {
                return response()->json(['success' => false, 'message' => 'Party id is required']);
            }
            if ($party) {
                return response()->json(['success' => true, 'data' => $party]);
            } else {
                return response()->json(['success' => true, 'data' => 'No data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function view_item(Request $request)
    {
        if ($request->ukey === 'RHMuqw3sGZFCYsmHivocHTJMVtTJ5PnF') {
            if ($request->has('id')) {
                $item = Item::find($request->id);
            } else {
                return response()->json(['success' => false, 'message' => 'Item id is required']);
            }
            if ($item) {
                return response()->json(['success' => true, 'data' => $item]);
            } else {
                return response()->json(['success' => true, 'data' => 'No data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function view_item_data(Request $request)
    {
        if ($request->ukey === 'RHMuqw3sGZFCYsmHivocHTJMVtTJ5PnF') {
            if ($request->has('id')) {
                $item = Item::find($request->id);
            } else {
                return response()->json(['success' => false, 'message' => 'Item id is required']);
            }
            if ($item) {
                return response()->json(['success' => true, 'data' => [$item]]);
            } else {
                return response()->json(['success' => true, 'data' => 'No data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function stock_summary(Request $request)
    {

        if ($request->ukey === 'MqAyVKS15jvifi7tha9RkgGlP5QeTE5A') {
            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            $query = Item::where('user_id', $user->id);

            // if ($request->has('item')) {
            //     $query = $query->where('name', $request->item);
            // }

            $items = $query->get();

            // if (count($items) <= 0) {
            //     return response()->json(['success' => false, 'data' => 'No Data']);
            // }

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                $from_date = $user->profile->book_beginning_from;
                $to_date = date('Y-m-d', time());
            }

            // return $items;
            $data = [];
            foreach ($items as $item) {
                $group = Group::find($item->group_id);

                $item->group_name = $group->name ?? '';
                $item->data = $this->calculate_stock_summary_and_return($user, $request, $item->id, $from_date, $to_date);
            }

            foreach($items as $item) {
                $item->opening_rate = 0;
                $item->opening_qty = 0;
                $item->opening_value = 0;

                $item->purchase_rate = 0;
                $item->purchase_qty = 0;
                $item->purchase_value = 0;

                $item->sale_rate = 0;
                $item->sale_qty = 0;
                $item->sale_value = 0;

                $item->closing_rate = 0;
                $item->closing_qty = 0;
                $item->closing_value = 0;
                foreach($item->data as $row) {
                    if($row['transaction_type'] == 'receipt') {
                        if($row['particulars'] == 'Opening') {
                            $item->opening_rate = $row['rate'];
                            $item->opening_qty = $row['qty'];
                            $item->opening_value = $row['amount'];
                        } else {
                            $item->purchase_rate += $row['rate'];
                            $item->purchase_qty += $row['qty'];
                            $item->purchase_value += $row['amount'];
                        }
                    }

                    if($row['transaction_type'] == 'issued'){
                        foreach($row['rate'] as $rate) {
                            $item->sale_rate += $rate;
                        }
                        foreach($row['qty'] as $qty) {
                            $item->sale_qty += $qty;
                        }
                        foreach($row['amount'] as $amount){
                            $item->sale_value += $amount;
                        }
                    }

                    foreach($row['balance']['qty'] as $qty){
                        $item->closing_qty = $item->opening_qty + $item->purchase_qty - $item->sale_qty;
                    }
                    foreach($row['balance']['amount'] as $amount){
                        $item->closing_value = $item->opening_value + $item->purchase_value - $item->sale_value;
                    }
                }
            }

            // return view('report.item', compact('items', 'from_date', 'to_date'));

            if ($items) {
                return response()->json(['success' => true, 'data' => $items]);
            } else {
                return response()->json(['success' => false, 'data' => 'No data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    private function calculate_stock_item_opening_balance($user, $item, $from_date, $to_date)
    {
        $id = $item->id;
        $fifo = true;
        $purchase_items = $user->purchaseItems()->where('item_id', $id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on')->get();
        $invoice_items = $user->invoiceItems()->where('item_id', $id)->whereBetween('sold_on', [$from_date, $to_date])->orderBy('sold_on')->get();
        $credit_notes = $user->creditNotes()->where('item_id', $id)->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->orderBy('credit_notes.note_date')->get();
        $debit_notes = $user->debitNotes()->where('item_id', $id)->where('debit_notes.type', 'purchase')->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->orderBy('debit_notes.note_date')->get();
        // $physical_stock = $user->managedInventories()->where('item_id', $id)->orderBy('created_at')->get();


        $balance_qty_array = array();
        $balance_rate_array = array();
        $balance_amount_array = array();

        $combined_array = array();
        $purchase_array = array();
        $sale_array = array();
        $freeQty_array = array();
        $creditNote_array = array();
        $debitNote_array = array();

        foreach ($purchase_items as $item) {
            $purchase = PurchaseRecord::findOrFail($item->purchase_id);
            $foundItem = Item::findOrFail($item->item_id);

            // default for base unit
            $rate = $item->price;
            if($item->qty_type == "alternate") {
                $base_difference = $item->qty / $item->alt_qty;
                $rate = $item->price / $base_difference;
            }
            if($item->qty_type == "compound") {
                $base_difference = $item->qty / ($item->alt_qty / $item->comp_qty);
                $rate = $item->price / $base_difference;
            }
            if($purchase->type_of_bill == "regular") {
                $purchase_array[] = [
                    'routable' => $item->purchase_id,
                    'particulars' => 'Purchase',
                    'voucher_no' => $purchase->bill_no,
                    'qty' => $item->qty,
                    'rate' => $rate,
                    'amount' => $item->item_total,
                    'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                    // 'balance' => ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array],
                    'transaction_type' => 'receipt',
                    'type' => 'showable'
                ];

                // add free qty
                if($item->free_qty != 0) {
                    $purchase_array[] = [
                    'routable' => $item->purchase_id,
                    'particulars' => 'Purchase (Free Qty)',
                    'voucher_no' => $purchase->bill_no,
                    'qty' => $item->free_qty,
                    'rate' => 0,
                    'amount' => 0,
                    'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                    // 'balance' => ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array],
                    'transaction_type' => 'receipt',
                    'type' => 'showable'
                    ];
                }
            }
        }

        foreach ($invoice_items as $item) {
            $sale = Invoice::findOrFail($item->invoice_id);
            
            if($sale->type_of_bill == "regular") {
                $sale_array[] = [
                    'routable' => $item->invoice_id,
                    'particulars' => 'Sale',
                    'voucher_no' => $sale->invoice_no,
                    'req_qty' => $item->item_qty,
                    'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                    'transaction_type' => 'issued',
                    'type' => 'showable'
                ];

                // add free qty
                if($item->free_qty != 0) {
                    $freeQty_array[] = [
                    'routable' => $item->invoice_id,
                    'particulars' => 'Sale (Free Qty)',
                    'voucher_no' => $sale->invoice_no,
                    'req_qty' => $item->free_qty,
                    'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                    'transaction_type' => 'issued',
                    'type' => 'showable'
                    ];
                }
            }
        }

        foreach ($credit_notes as $note) {
            if($note->status == 1) {
                $creditNote_array[] = [
                    'routable' => $note->note_no,
                    'particulars' => 'Credit Note',
                    'voucher_no' => $note->note_no,
                    'qty' => $note->quantity,
                    'rate' => $note->price,
                    'amount' => $note->quantity * $note->price,
                    'date' => Carbon::parse($note->note_date)->format('Y-m-d'),
                    'transaction_type' => 'receipt',
                    'type' => 'showable'
                ];
            }
        }

        foreach ($debit_notes as $note) {
            if($note->status == 1) {
                $debitNote_array[] = [
                    'routable' => $note->note_no,
                    'particulars' => 'Debit Note',
                    'voucher_no' => $note->note_no,
                    'req_qty' => $note->quantity,
                    'date' => Carbon::parse($note->note_date)->format('Y-m-d'),
                    'transaction_type' => 'issued',
                    'type' => 'showable'
                ];
            }
        }


        $combined_array = array_merge(
            $purchase_array,
            $sale_array,
            $freeQty_array,
            $creditNote_array,
            $debitNote_array
        );

        $this->array_sort_by_column($combined_array, 'date');

        $data = [];
        foreach($combined_array as $row) {

            if($row['transaction_type'] == 'receipt') {
                //pushing data (bought item to stock) at the end in the array
                array_push($balance_qty_array, $row['qty']);
                array_push($balance_rate_array, $row['rate']);
                array_push($balance_amount_array, $row['amount']);
            }

            if($row['transaction_type'] == 'issued') {
                $qty = $row['req_qty'];
                $qty_array = [];
                $rate_array = [];
                $amount_array = [];
                if($fifo) {
                    for($i=0; $i<count($balance_qty_array); $i++) {
                        if($qty > 0 && $qty > $balance_qty_array[$i]) {
                            $qty -= $balance_qty_array[$i];
                            // pushing data (sold item to issued) at the end in the array
                            // array_shift removes data from the front of the stock (FIFO). Item which was bought first will be sold first
                            array_push($qty_array, array_shift($balance_qty_array));
                            array_push($rate_array, array_shift($balance_rate_array));
                            array_push($amount_array, array_shift($balance_amount_array));
                        } else if ($qty > 0 && $qty < $balance_qty_array[$i]) {
                            array_push($qty_array, $qty);
                            array_push($rate_array, $balance_rate_array[$i]);
                            array_push($amount_array, $qty * $balance_rate_array[$i]);
                            $balance_qty_array[$i] -= $qty;
                            $balance_amount_array[$i] = $balance_qty_array[$i] * $balance_rate_array[$i];
                            $qty = 0;
                        } else if ($qty > 0 && $qty == $balance_qty_array[$i]) {
                            $removed_rate = array_shift($balance_rate_array);
                            array_shift($balance_amount_array);
                            array_push($qty_array, array_shift($balance_qty_array));
                            array_push($rate_array, $removed_rate);
                            array_push($amount_array, $qty * $removed_rate);
                            $qty = 0;
                        }
                    }
                } else {
                    for($i=count($balance_qty_array)-1; $i >= 0; $i--) {
                        if($qty > 0 && $qty > $balance_qty_array[$i]) {
                            $qty -= $balance_qty_array[$i];
                            // pushing data (sold item to issued) at the end in the array
                            // array_pop removes data from the end of the stock (LIFO). Item which was bought last will be sold first
                            array_push($qty_array, array_pop($balance_qty_array));
                            array_push($rate_array, array_pop($balance_rate_array));
                            array_push($amount_array, array_pop($balance_amount_array));
                        } else if ($qty > 0 && $qty < $balance_qty_array[$i]) {
                            array_push($qty_array, $qty);
                            array_push($rate_array, $balance_rate_array[$i]);
                            array_push($amount_array, $qty * $balance_rate_array[$i]);
                            $balance_qty_array[$i] -= $qty;
                            $balance_amount_array[$i] = $balance_qty_array[$i] * $balance_rate_array[$i];
                            $qty = 0;
                        } else if ($qty > 0 && $qty == $balance_qty_array[$i]) {
                            $removed_rate = array_pop($balance_rate_array);
                            array_pop($balance_amount_array);
                            array_push($qty_array, array_pop($balance_qty_array));
                            array_push($rate_array, $removed_rate);
                            array_push($amount_array, $qty * $removed_rate);
                            $qty = 0;
                        }
                    }
                }
                $row['qty'] = $qty_array;
                $row['rate'] = $rate_array;
                $row['amount'] = $amount_array;
            }

            $row['balance'] = ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array];
            $data[] = $row;
        }

        $opening_value = 0;
        $opening_qty = 0;
        $opening_rate = 0;
        foreach($data as $row) {
            if($row['transaction_type'] == 'receipt') {
                $opening_qty += $row['qty'];
                $opening_value += $row['amount'];
            }

            if($row['transaction_type'] == 'issued'){
                foreach($row['qty'] as $qty) {
                    $opening_qty -= $qty;
                }
                foreach($row['amount'] as $amount){
                    $opening_value -= $amount;
                }
            }
        }

        if($from_date <= $item->opening_stock_date && $item->opening_stock_date <= $to_date) {
            $opening_value = $item->opening_stock_value;
            $opening_qty = $item->opening_stock;
        }

        if($opening_qty == 0) {
            $opening_rate = $opening_value;
        } else {
            $opening_rate = $opening_value / $opening_qty;
        }

        return ['qty' => $opening_qty, 'rate' => $opening_rate, 'value' => $opening_value, 'date' => $to_date];
    }

    private function calculate_stock_summary_and_return($user, $request, $id, $from_date, $to_date)
    {
        $fifo = isset($request->type) && $request->type == "lifo" ? false : true;
        $current_item = Item::findOrFail($id);
        
        $purchase_items = $user->purchaseItems()->where('item_id', $id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on')->get();
        $invoice_items = $user->invoiceItems()->where('item_id', $id)->whereBetween('sold_on', [$from_date, $to_date])->orderBy('sold_on')->get();
        $credit_notes = $user->creditNotes()->where('item_id', $id)->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->orderBy('credit_notes.note_date')->get();
        $debit_notes = $user->debitNotes()->where('item_id', $id)->where('debit_notes.type', 'purchase')->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->orderBy('debit_notes.note_date')->get();
        // $physical_stock = $user->managedInventories()->where('item_id', $id)->orderBy('created_at')->get();

        $balance_qty_array = array();
        $balance_rate_array = array();
        $balance_amount_array = array();

        $combined_array = array();
        $opening_array = array();
        $purchase_array = array();
        $sale_array = array();
        $freeQty_array = array();
        $creditNote_array = array();
        $debitNote_array = array();
        // $physicalStock_array = array();

        $closing_balance_from_date = $user->profile->book_beginning_from;
        $closing_balance_to_date = \Carbon\Carbon::parse($from_date)->format('Y-m-d');

        $opening_stock = $this->calculate_stock_item_opening_balance($user, $current_item, $closing_balance_from_date, $closing_balance_to_date);

        // $opening_stock = $current_item->opening_stock == 0 ? 1 : $current_item->opening_stock;
        // $rate = $current_item->opening_stock_value / $opening_stock;

        // $opening_array[] = [
        //     'routable' => '',
        //     'particulars' => 'Opening',
        //     'voucher_no' => '',
        //     'qty' => $current_item->opening_stock,
        //     'rate' => $rate,
        //     'amount' => $current_item->opening_stock * $rate,
        //     'date' => Carbon::parse($current_item->opening_stock_date)->format('Y-m-d'),
        //     // 'balance' => ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array],
        //     'transaction_type' => 'receipt',
        //     'type' => 'showable'
        // ];

        $opening_array[] = [
            'routable' => '',
            'particulars' => 'Opening',
            'voucher_no' => '',
            'qty' => $opening_stock['qty'],
            'rate' => $opening_stock['rate'],
            'amount' => $opening_stock['value'],
            'date' => Carbon::parse($opening_stock['date'])->format('Y-m-d'),
            // 'balance' => ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array],
            'transaction_type' => 'receipt',
            'type' => 'showable'
        ];

        foreach ($purchase_items as $item) {
            $purchase = PurchaseRecord::findOrFail($item->purchase_id);
            $foundItem = Item::findOrFail($item->item_id);

            // default for base unit
            $rate = $item->price;
            if($item->qty_type == "alternate") {
                $base_difference = $item->qty / $item->alt_qty;
                $rate = $item->price / $base_difference;
            }
            if($item->qty_type == "compound") {
                $base_difference = $item->qty / ($item->alt_qty / $item->comp_qty);
                $rate = $item->price / $base_difference;
            }
            if($purchase->type_of_bill == "regular") {
                $purchase_array[] = [
                    'routable' => $item->purchase_id,
                    'particulars' => 'Purchase',
                    'voucher_no' => $purchase->bill_no,
                    'qty' => $item->qty,
                    'rate' => $rate,
                    'amount' => $item->item_total,
                    'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                    // 'balance' => ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array],
                    'transaction_type' => 'receipt',
                    'type' => 'showable'
                ];

                // add free qty
                if($item->free_qty != 0) {
                    $purchase_array[] = [
                    'routable' => $item->purchase_id,
                    'particulars' => 'Purchase (Free Qty)',
                    'voucher_no' => $purchase->bill_no,
                    'qty' => $item->free_qty,
                    'rate' => 0,
                    'amount' => 0,
                    'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                    // 'balance' => ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array],
                    'transaction_type' => 'receipt',
                    'type' => 'showable'
                    ];
                }
            }
        }

        foreach ($invoice_items as $item) {
            $sale = Invoice::findOrFail($item->invoice_id);
            
            if($sale->type_of_bill == "regular") {
                $sale_array[] = [
                    'routable' => $item->invoice_id,
                    'particulars' => 'Sale',
                    'voucher_no' => $sale->invoice_no,
                    'req_qty' => $item->item_qty,
                    'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                    'transaction_type' => 'issued',
                    'type' => 'showable'
                ];

                // add free qty
                if($item->free_qty != 0) {
                    $freeQty_array[] = [
                    'routable' => $item->invoice_id,
                    'particulars' => 'Sale (Free Qty)',
                    'voucher_no' => $sale->invoice_no,
                    'req_qty' => $item->free_qty,
                    'date' => Carbon::parse($sale->invoice_date)->format('Y-m-d'),
                    'transaction_type' => 'issued',
                    'type' => 'showable'
                    ];
                }
            }
        }

        foreach ($credit_notes as $note) {
            if($note->status == 1) {
                $creditNote_array[] = [
                    'routable' => $note->note_no,
                    'particulars' => 'Credit Note',
                    'voucher_no' => $note->note_no,
                    'qty' => $note->quantity,
                    'rate' => $note->price,
                    'amount' => $note->quantity * $note->price,
                    'date' => Carbon::parse($note->note_date)->format('Y-m-d'),
                    'transaction_type' => 'receipt',
                    'type' => 'showable'
                ];
            }
        }

        foreach ($debit_notes as $note) {
            if($note->status == 1) {
                $debitNote_array[] = [
                    'routable' => $note->note_no,
                    'particulars' => 'Debit Note',
                    'voucher_no' => $note->note_no,
                    'req_qty' => $note->quantity,
                    'date' => Carbon::parse($note->note_date)->format('Y-m-d'),
                    'transaction_type' => 'issued',
                    'type' => 'showable'
                ];
            }
        }

        // add physical stock
        // foreach($physical_stock as $stock) {
        //     $physicalStock_array[] = [
        //         'routable' => $stock->id,
        //         'particulars' => 'Physical Stock',
        //         'voucher_no' => $stock->id,
        //         'req_qty' => $stock->item_qty,
        //         'date' => Carbon::parse($stock->value_updated_on)->format('Y-m-d'),
        //         'transaction_type' => 'issued',
        //         'type' => 'showable'
        //     ];
        // }

        $combined_array = array_merge(
            $opening_array,
            $purchase_array,
            $sale_array,
            $freeQty_array,
            $creditNote_array,
            $debitNote_array
        );

        $this->array_sort_by_column($combined_array, 'date');

        $data = [];
        foreach($combined_array as $row) {

            if($row['transaction_type'] == 'receipt') {
                //pushing data (bought item to stock) at the end in the array
                array_push($balance_qty_array, $row['qty']);
                array_push($balance_rate_array, $row['rate']);
                array_push($balance_amount_array, $row['amount']);
            }

            if($row['transaction_type'] == 'issued') {
                $qty = $row['req_qty'];
                $qty_array = [];
                $rate_array = [];
                $amount_array = [];
                if($fifo) {
                    for($i=0; $i<count($balance_qty_array); $i++) {
                        if($qty > 0 && $qty > $balance_qty_array[$i]) {
                            $qty -= $balance_qty_array[$i];
                            // pushing data (sold item to issued) at the end in the array
                            // array_shift removes data from the front of the stock (FIFO). Item which was bought first will be sold first
                            array_push($qty_array, array_shift($balance_qty_array));
                            array_push($rate_array, array_shift($balance_rate_array));
                            array_push($amount_array, array_shift($balance_amount_array));
                        } else if ($qty > 0 && $qty < $balance_qty_array[$i]) {
                            array_push($qty_array, $qty);
                            array_push($rate_array, $balance_rate_array[$i]);
                            array_push($amount_array, $qty * $balance_rate_array[$i]);
                            $balance_qty_array[$i] -= $qty;
                            $balance_amount_array[$i] = $balance_qty_array[$i] * $balance_rate_array[$i];
                            $qty = 0;
                        } else if ($qty > 0 && $qty == $balance_qty_array[$i]) {
                            $removed_rate = array_shift($balance_rate_array);
                            array_shift($balance_amount_array);
                            array_push($qty_array, array_shift($balance_qty_array));
                            array_push($rate_array, $removed_rate);
                            array_push($amount_array, $qty * $removed_rate);
                            $qty = 0;
                        }
                    }
                } else {
                    for($i=count($balance_qty_array)-1; $i >= 0; $i--) {
                        if($qty > 0 && $qty > $balance_qty_array[$i]) {
                            $qty -= $balance_qty_array[$i];
                            // pushing data (sold item to issued) at the end in the array
                            // array_pop removes data from the end of the stock (LIFO). Item which was bought last will be sold first
                            array_push($qty_array, array_pop($balance_qty_array));
                            array_push($rate_array, array_pop($balance_rate_array));
                            array_push($amount_array, array_pop($balance_amount_array));
                        } else if ($qty > 0 && $qty < $balance_qty_array[$i]) {
                            array_push($qty_array, $qty);
                            array_push($rate_array, $balance_rate_array[$i]);
                            array_push($amount_array, $qty * $balance_rate_array[$i]);
                            $balance_qty_array[$i] -= $qty;
                            $balance_amount_array[$i] = $balance_qty_array[$i] * $balance_rate_array[$i];
                            $qty = 0;
                        } else if ($qty > 0 && $qty == $balance_qty_array[$i]) {
                            $removed_rate = array_pop($balance_rate_array);
                            array_pop($balance_amount_array);
                            array_push($qty_array, array_pop($balance_qty_array));
                            array_push($rate_array, $removed_rate);
                            array_push($amount_array, $qty * $removed_rate);
                            $qty = 0;
                        }
                    }
                }
                $row['qty'] = $qty_array;
                $row['rate'] = $rate_array;
                $row['amount'] = $amount_array;
            }

            $row['balance'] = ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array];
            $data[] = $row;
        }

        return $data;
    }

    public function store_party(Request $request)
    {
        if ($request->ukey === 'xMfFRE9setuTS0dsMzkcdtY67N4KjIgn') {
            $party = new Party;

            $party->contact_person_name = $request->contact_person_name;
            $party->name = $request->name;
            $party->email = $request->email;
            $party->registered = $request->status_of_registration;
            if ($request->status_of_registration == 1 || $request->status_of_registration == 3 || $request->status_of_registration == 4) {
                $party->gst = $request->gst;
            }

            $party->is_operator = $request->is_operator;

            $party->tds_income_tax = $request->tds_income_tax;
            $party->tds_gst = $request->tds_gst;

            $party->tcs_income_tax = $request->tcs_income_tax;
            $party->tcs_gst = $request->tcs_gst;

            $party->business_place = $request->business_place;

            $party->reverse_charge = $request->reverse_charge;

            $party->status_of_registration = $request->status_of_registration;

            $party->phone = $request->phone;


            // if (isset($request->checkbox_shipping_address) && $request->checkbox_shipping_address == "1") {
            //     $party->shipping_address = $request->address;
            //     $party->shipping_state = $request->state;
            //     $party->shipping_city = $request->city;
            //     $party->shipping_pincode = $request->pincode;
            // } else {
            $party->shipping_address = $request->shipping_address;
            $party->shipping_state = $request->business_place;
            $party->shipping_city = $request->shipping_city;
            $party->shipping_pincode = $request->shipping_pincode;
            // }

            if (isset($request->checkbox_billing_address) && $request->checkbox_billing_address == "1") {
                $party->billing_address = $request->shipping_address;
                $party->billing_state = $request->business_place;
                $party->billing_city = $request->shipping_city;
                $party->billing_pincode = $request->shipping_pincode;
            } else {
                $party->billing_address = $request->billing_address;
                $party->billing_state = $request->billing_state;
                $party->billing_city = $request->billing_city;
                $party->billing_pincode = $request->billing_pincode;
            }

            if (isset($request->checkbox_communication_address) && $request->checkbox_communication_address == "1") {
                $party->communication_address = $request->shipping_address;
                $party->communication_state = $request->business_place;
                $party->communication_city = $request->shipping_city;
                $party->communication_pincode = $request->shipping_pincode;
            } else {
                $party->communication_address = $request->communication_address;
                $party->communication_state = $request->communication_state;
                $party->communication_city = $request->communication_city;
                $party->communication_pincode = $request->communication_pincode;
            }

            $party->opening_balance = $request->opening_balance;
            $time = strtotime(str_replace('/', '-', $request->opening_balance_as_on));
            $formatedDate = date('Y-m-d', $time);


            $party->away_from_us_distance = $request->away_distance;
            $party->opening_balance_as_on = $formatedDate;


            $party->balance_type = $request->balance_type;

            $party->terms_and_condition = $request->terms_and_condition;

            $party->user_id = $request->user_id;

            $party->created_on = 'mobile';

            if ($party->save()) {
                return response()->json(['success' => true, 'data' => $party]);
            } else {
                return response()->json(['success' => false, 'data' => 'Failed to add party']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function get_invoices(Request $request)
    {
        if ($request->ukey === 'zysAToDoIGbaXc7N3LT4mPtW2dYeyg2n') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if ($request->search_by == 'invoice_no') {
                $invoices = $user->invoices()->where('invoice_no', $request->q)->orderBy('updated_at', 'asc')->get();
            } else {
                $invoices = $user->invoices()->orderBy('updated_at', 'asc')->get();
            }

            $last_updated = null;

            foreach ($invoices as $invoice) {
                $party = Party::find($invoice->party_id);

                $invoice->party_name = $party->name;
                $invoice->party_city = $party->city;

                $last_updated = $invoice->updated_at;
            }

            if ($invoices) {
                return response()->json(['success' => true, 'last_updated' => $last_updated, 'data' => $invoices]);
            } else {
                return response()->json(['success' => false, 'data' => 'No invoice found']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function get_bills(Request $request)
    {
        if ($request->ukey === 'VeReSLQagIDhP3oHnOQnCyH1WjWJz24c') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if ($request->search_by == 'bill_no') {
                $purchases = $user->purchases()->where('bill_no', $request->q)->orderBy('updated_at', 'asc')->get();
            } else {
                $purchases = $user->purchases()->orderBy('updated_at', 'asc')->get();
            }

            $last_updated = null;

            foreach ($purchases as $purchase) {
                $party = Party::find($purchase->party_id);

                $purchase->party_name = $party->name;
                $purchase->party_city = $party->city;

                $last_updated = $purchase->updated_at;
            }

            if ($purchases) {
                return response()->json(['success' => true, 'last_updated' => $last_updated, 'data' => $purchases]);
            } else {
                return response()->json(['success' => false, 'data' => 'No bill found']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function get_state_list(Request $request)
    {
        if ($request->ukey === 'mXHHzKT7IZxdp7vjSDQbpewrYhgnIs8u') {
            $states = State::all();

            if ($states) {
                return response()->json(['success' => true, 'data' => $states]);
            } else {
                return response()->json(['success' => false, 'data' => 'No data']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function get_app_summary_data(Request $request)
    {
        if ($request->ukey === 'EEhXcR5zazADzInAF5Lw1YZJNLgQbikZ') {
            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            $invoices = $user->invoices;
            $purchases = $user->purchases;
            $debitNotes = $user->debitNotes;
            $creditNotes = $user->creditNotes;

            $invoice_amount = 0;
            $purchase_amount = 0;
            $debit_note_amount = 0;
            $credit_note_amount = 0;

            foreach ($invoices as $invoice) {
                $invoice_amount += $invoice->total_amount;
            }

            foreach ($purchases as $purchase) {
                $purchase_amount += $purchase->total_amount;
            }

            foreach ($debitNotes as $note) {
                $debit_note_amount += $note->note_value;
            }

            foreach ($creditNotes as $note) {
                $credit_note_amount += $note->note_value;
            }

            return response()->json(['success' => true, 'invoice_amount' => $invoice_amount, 'purchase_amount' => $purchase_amount, 'debit_note_amount' => $debit_note_amount, 'credit_note_amount' => $credit_note_amount]);
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }


    public function sales_account(Request $request)
    {
        if ($request->ukey === 'BIiQmR2VqTCYpOh9i4Yhcz49KjknJAQu') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            $sale_array = array();
            $credit_note_array = array();
            $debit_note_array = array();

            $opening_balance = $this->calculate_sales_account_opening_balance($user, $from_date);

            $sales = User::findOrFail($user->id)->invoices()->where('type_of_bill', 'regular')->whereBetween('invoice_date', [$from_date, $to_date])->orderBy('invoice_date')->get();

            foreach ($sales as $invoice) {

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
            $creditNotes = User::find($user->id)->creditNotes()->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.created_at', [$from_date, $to_date])->groupBy('credit_notes.note_no')->get();

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

            // // foreach ($sales as $invoice) {
            // $debitNotes = User::find($user->id)->debitNotes()->where('debit_notes.type', 'sale')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->get();

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
            // // }   

            $combined_array = array_merge(
                $sale_array,
                $credit_note_array
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
                
                foreach ($item as $key => $value) {
                    $group[$month][$count][$key] = $value;
                }
            }

            $combined_array = $group;

            return response()->json(['success' => true, 'opening_balance' => $opening_balance, 'combined_array' => $combined_array, 'from_date' => $from_date, 'to_date' => $to_date]);
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    private function calculate_sales_account_opening_balance($user, $till_date)
    {
        $opening_balance = 0;

        $sales = User::findOrFail($user->id)->invoices()
            ->where('invoice_date', '<', $till_date)
            ->orderBy('invoice_date')
            ->get();


        $creditNotes = User::find($user->id)->creditNotes()
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

    public function purchases_account(Request $request)
    {
        if ($request->ukey === 'p1SBabsES9q419jGFh0vDAbZsXqU7g4m') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }


            if ($request->has('from_date') && $request->has('to_date')) {
                $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
                $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            } else {
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;
            }

            $purchase_array = array();
            $credit_note_array = array();
            $debit_note_array = array();

            $opening_balance = $this->calculate_purchases_account_opening_balance($user, $from_date);

            $purchases = User::findOrFail($user->id)->purchases()->where('type_of_bill', 'regular')->whereBetween('bill_date', [$from_date, $to_date])->orderBy('bill_date')->get();

            foreach ($purchases as $purchase) {

                if ($user->profile->registered == 0) {
                    $purchase_amount = $purchase->total_amount;
                } else if ($user->profile->registered == 3) {
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
            $debitNotes = User::find($user->id)->debitNotes()->where('debit_notes.type', 'purchase')->where('debit_notes.status', 1)->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.created_at', [$from_date, $to_date])->groupBy('debit_notes.note_no')->get();

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

                foreach ($item as $key => $value) {

                    $group[$month][$count][$key] = $value;
                }
            }

            $combined_array = $group;

            return response()->json(['success' => true, 'opening_balance' => $opening_balance, 'combined_array' => $combined_array, 'from_date' => $from_date, 'to_date' => $to_date]);
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    private function calculate_purchases_account_opening_balance($user, $till_date)
    {
        $opening_balance = 0;

        $purchases = User::findOrFail($user->id)->purchases()
            ->where('bill_date', '<', $till_date)
            ->orderBy('bill_date')
            ->get();

        // $creditNotes = User::find($user->id)->creditNotes()
        //     ->where('credit_notes.type', 'purchase')
        //     ->where('credit_notes.created_at', '<', $till_date)
        //     ->get();

        $debitNotes = $debitNotes = User::find($user->id)->debitNotes()
            ->where('debit_notes.type', 'purchase')
            ->where('debit_notes.created_at', '<', $till_date)
            ->get();

        foreach ($purchases as $bill) {
            if ($user->profile->registered == 0) {
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

    public function get_additional_document(Request $request)
    {
        if ($request->ukey === 'p1SBabsES9q419jGFh0vDAbZsXqU7g4m') {

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }


            $type = "All";

            if ($request->type) {
                $type = $request->type;
            }

            $query = UploadedDocument::where('user_id', $user->id);

            if ($request->month == null && $request->year == null) {
                $month = "All";
                $from_date = $user->profile->financial_year_from;
                $to_date = $user->profile->financial_year_to;

                $query = $query->whereBetween('created_at', [$from_date, $to_date]);
            } else if ($request->month == null) {
                $query = $query->where('year', $request->year);
            } else if ($request->year == null) {
                $query = $query->where('month', $request->month);
            } else {
                $query = $query->where('month', $request->month)->where('year', $request->year);
            }

            if ($type != "All") {
                $query = $query->where('type', $type);
            }

            $uploaded_statements = $query->orderBy('created_at', 'desc')->get();

            foreach ($uploaded_statements as $bill) {
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

            if ($request->month != null) {
                switch ($request->month) {
                    case 1:
                        $month = "Jan";
                        break;
                    case 2:
                        $month = "Feb";
                        break;
                    case 3:
                        $month = "Mar";
                        break;
                    case 4:
                        $month = "Apr";
                        break;
                    case 5:
                        $month = "May";
                        break;
                    case 6:
                        $month = "Jun";
                        break;
                    case 7:
                        $month = "Jul";
                        break;
                    case 8:
                        $month = "Aug";
                        break;
                    case 9:
                        $month = "Sep";
                        break;
                    case 10:
                        $month = "Oct";
                        break;
                    case 11:
                        $month = "Nov";
                        break;
                    case 12:
                        $month = "Dec";
                        break;
                }
            } else {
                $month = 'All';
            }

            // return $uploaded_bill;
            $total_count = count($uploaded_statements);


            return response()->json(['success' => true, 'uploaded_statements' => $uploaded_statements, 'month' => $month, 'total_count' => $total_count]);
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function save_invoice_transport_detail(Request $request)
    {
        if ($request->ukey === 'ssuPI5TAEZnempHsnXTLUafBCeGV4cDG') {
            $validator = Validator::make($request->all(), [
                "user_id" => "required",
                "invoice_id" => "required",
                "transporter_id" => "required",
                "transporter_name" => "required",
                "transporter_doc_no" => "required",
                "transport_doc_date" => "required",
                "transport_mode" => "required",
                "transport_distance" => "required",
                "vehicle_type" => "required",
                "vehicle_number" => "required",
                "delivery_date" => "required"
            ]);
            
            $user = null;
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->toJson(), 'data' => null], 422);
            }

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if ($request->has('invoice_id')) {
                $invoice = Invoice::find($request->invoice_id);
            } else {
                return response()->json(['success' => false, 'message' => 'Invoice id is required']);
            }

            if(!$user) {
                return response()->json(['success' => false, 'message' => 'user not found']);
            }

            $accesstoken_endpoint = Config::get('ewaybill.urls.ACCESSTOKEN.production');
            $geneway_endpoint = Config::get('ewaybill.urls.EWAYBILL.production');
            $this->gstin = $user->ewaybillDetail ? $user->ewaybillDetail->gst : null;
            $this->username = $user->ewaybillDetail ? $user->ewaybillDetail->username : null;
            $this->ewbpwd = $user->ewaybillDetail ? $user->ewaybillDetail->password : null;

            $itemList = array();

            foreach($invoice->invoice_items as $invItem){
                $invoiceItem = array();

                $strToRemoveIndex = strpos($invItem->item_measuring_unit,"(");
                
                if($strToRemoveIndex){
                    $qtyUnit = trim(substr($invItem->item_measuring_unit, 0, $strToRemoveIndex));
                }else {
                    $qtyUnit = $invItem->item_measuring_unit;
                }

                $invoiceItem["productName"] = $invItem->item->name;
                $invoiceItem["productDesc"] = $invItem->item->name;
                $invoiceItem["hsnCode"] = 1001 ?? $invItem->item->hsc_code;
                $invoiceItem["quantity"] = $invItem->item_qty;
                $invoiceItem["qtyUnit"] = "Box" ?? $qtyUnit;
                $invoiceItem["cgstRate"] = $invItem->cgst ?? 0;
                $invoiceItem["sgstRate"] = $invItem->sgst ?? 0;
                $invoiceItem["igstRate"] = $invItem->igst ?? 0;
                $invoiceItem["cessRate"] = $invItem->cess ?? 0;
                $invoiceItem["cessNonAdvol"] = 0;
                $invoiceItem["taxableAmount"] = $invItem->item_total;

                $itemList[] = $invoiceItem;
            }

            $docNo = rand(100,999) . '-' . $invoice->invoice_prefix . $invoice->invoice_no . $invoice->invoice_suffix;
            
            $fromGstin = $this->gstin;
            // (auth()->user()->profile->registered != 0 && auth()->user()->profile->gst != null) ? auth()->user()->profile->gst : 'URP';
            
            $fromStateCode = ($user->profile->communication_state > 9) ? $user->profile->communication_state : "0".$user->profile->communication_state;

            $toGstin = ($invoice->party->registered !=0 && $invoice->party->gst != null) ? $invoice->party->gst : 'URP';
            $toStateCode = ($invoice->party->shipping_state > 9) ? $invoice->party->shipping_state : "0".$invoice->party->shipping_state;

            $transporterName = $request->transporter_name ?? '';
            $transporterDocNo = $request->transporter_doc_no ?? '';
            $transportMode = $request->transport_mode ?? '1';
            $transportDistance = $request->transport_distance ?? '68';
            $transportDocDate = $request->transport_doc_date ?? '';
            $vehicleNumber = $request->vehicle_number;
            $vehicleType = (isset($request->vehicle_type) && !empty($request->vehicle_type)) ? strtoupper($request->vehicle_type) : 'R';

            try{
                $authToken = Ewayapi::getAuthToken($accesstoken_endpoint, $this->gstin, $this->username, $this->ewbpwd);
            } catch(\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Failed to generate token - ' . $e->getMessage()]);
            }

            $dataArray = [
                "supplyType" => "O",
                "subSupplyType" => "1",
                "subSupplyDesc" => " ",
                "docType" => "INV",
                "docNo" => $docNo ?? "777-9",
                "docDate" => \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') ?? "15/12/2017",
                "fromGstin" => $fromGstin ?? "05AAACG1625Q1ZK",
                "fromTrdName" => $user->profile->name ?? "welton",
                "fromAddr1" => $user->profile->communication_address ?? "2ND CROSS NO 59  19  A",
                "fromAddr2" => "" ?? "GROUND FLOOR OSBORNE ROAD",
                "fromPlace" => $user->profile->communication_city ?? "FRAZER TOWN",
                "fromPincode" => $user->profile->communication_pincode ?? 263652,
                "actFromStateCode" => $fromStateCode ?? "05",
                "fromStateCode" => $fromStateCode ?? "05",
                "toGstin" => $toGstin ?? "02EHFPS5910D2Z0",
                "toTrdName" => $invoice->party->name ?? "sthuthya",
                "toAddr1" => $invoice->party->shipping_address ?? "Shree Nilaya",
                "toAddr2" => "" ?? "Dasarahosahalli",
                "toPlace" => $invoice->party->shipping_city ?? "Beml Nagar",
                "toPincode" => $invoice->party->shipping_pincode ?? 176036,
                "actToStateCode" => $toStateCode ?? "02",
                "toStateCode" => $toStateCode ?? "02",
                "transactionType" => 4,
                "dispatchFromGSTIN" => $fromGstin ?? "29AAAAA1303P1ZV",
                "dispatchFromTradeName" => auth()->user()->profile->name ?? "ABC Traders",
                "shipToGSTIN" => $toGstin ?? "29ALSPR1722R1Z3",
                "shipToTradeName" => $invoice->party->name ?? "XYZ Traders",
                "otherValue" => 0 ?? -100 ,
                "totalValue" => $invoice->item_total_amount ?? 56099,
                "cgstValue" => $invoice->cgst ?? 0,
                "sgstValue" => $invoice->sgst ?? 0,
                "igstValue" => $invoice->igst ?? 0,
                "cessValue" => $invoice->cess ?? 0,
                "cessNonAdvolValue" => 0,
                "totInvValue" => $invoice->total_amount ?? 56099,
                "transporterId" => "",
                "transporterName" => $transporterName, 
                "transDocNo" => $transporterDocNo,
                "transMode" => $transportMode,
                "transDistance" => $transportDistance,
                "transDocDate" => \Carbon\Carbon::parse($transportDocDate)->format('d/m/Y') ?? "",
                "vehicleNo" => $vehicleNumber,
                "vehicleType" => $vehicleType,
                "itemList" => $itemList
            ];

            // return json_encode($dataArray, JSON_PRETTY_PRINT);

            try{
                $response = Ewayapi::generateEwayBill($geneway_endpoint, $this->gstin, $this->username, $this->ewbpwd, $authToken, $dataArray);
                $invoice_id = $request->invoice_id;
                $content = $response->getBody();
                $content = json_decode($content, true);

                $ewaybill = new Ewaybill;
                $ewaybill->bill_no = $content['ewayBillNo'];
                $ewaybill->created_on = $content['ewayBillDate'];
                $ewaybill->valid_upto = $content['validUpto'];
                $ewaybill->invoice_id = $request->invoice_id;
                $ewaybill->user_id = $user->id;
            
                DB::beginTransaction();
                if($ewaybill->save()){
                    $transporter_detail = new TransporterDetail;
                    $transporter_detail->invoice_id = $invoice->id;
                    $transporter_detail->transporter = $request->transporter_id;
                    $transporter_detail->transporter_name = $transporterName;
                    $transporter_detail->transporter_doc_no = $transporterDocNo;
                    $transporter_detail->transport_doc_date = $transportDocDate;
                    $transporter_detail->transport_mode = $transportMode;
                    $transporter_detail->transport_distance = $transportDistance;
                    $transporter_detail->vehicle_type = $vehicleType;
                    $transporter_detail->vehicle_number = $vehicleNumber;
                    $transporter_detail->delivery_date = $request->delivery_date;

                    if($transporter_detail->save()){
                        DB::commit();
                        return response()->json(['success' => true, 'message' => 'Details added successfully and Eway bill created successfully']);
                    } else {
                        DB::rollback();
                        return response()->json(['success' => false, 'message' => 'Failed to add data']);
                    }
                }
            } catch(\Exception $e) {
                DB::rollBack();
                $split_error = explode("{", $e->getMessage());
                return response()->json(['success' => false, 'message' => $split_error[2]]); // ' Failed to generate ewaybill'
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function cancel_eway(Request $request)
    {
        if ($request->ukey === 'ssuPI5TAEZnempHsnXTLUafBCeGV4cDG') {

            $user = null;
            $bill_no = null;
            if ($request->has('bill_no')) {
                $bill_no = $request->bill_no;
            }

            if(!$bill_no) {
                return response()->json(['success' => false, 'message' => 'Bill no is required']);
            }

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if(!$user) {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }

            $accesstoken_endpoint = Config::get('ewaybill.urls.ACCESSTOKEN.production');
            $caneway_endpoint = Config::get('ewaybill.urls.EWAYBILL.production');
            $this->gstin = $user->ewaybillDetail ? $user->ewaybillDetail->gst : null;
            $this->username = $user->ewaybillDetail ? $user->ewaybillDetail->username : null;
            $this->ewbpwd = $user->ewaybillDetail ? $user->ewaybillDetail->password : null;

            try{
                $authToken = Ewayapi::getAuthToken($accesstoken_endpoint,  $this->gstin, $this->username, $this->ewbpwd);
            } catch(\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Failed to generate token - ' . $e->getMessage()]);
            }

            $dataArray = [
                "ewbNo" => $bill_no,
                "cancelRsnCode" => 2,
                "cancelRmrk" => "Cancelled the order"
            ];

            try {
                $response = Ewayapi::cancelEwayBill($caneway_endpoint, $this->gstin, $this->username, $this->ewbpwd, $authToken, $dataArray);

                if($response == null){
                    return response()->json(['success' => false, 'message' => 'Cannot cancel ewaybill']);
                }

                $content = $response->getBody();
                $content = json_decode($content, true);

                $ewaybill = Ewaybill::where('bill_no', $bill_no)->first();
                $ewaybill->status = 0;
                $ewaybill->save();
            } catch(\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage() . 'Failed to cancel ewaybill']);
            }

            return response()->json(['success' => true, 'message' => 'Successfully cancelled ewaybill']);
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function show_eway(Request $request)
    {
        if ($request->ukey === 'GzKWyz8TOzMOGzSAjvHoq1oL93nu30Nx') {
            $user = null;
            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if ($user) {
                $ewaybills = $user->ewaybills()->get();
                return response()->json(['success' => true, 'ewaybills' => $ewaybills]);
            } else {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function profile(Request $request)
    {
        if ($request->ukey === 'JEKE10RaiKTNaV9e3R7kjU9GvMF2QfOB') {
            $user = null;
            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }
            if ($user) {
                $user_profile = UserProfile::where('user_id', $user->id)->first();
                $round_off_settings = RoundOffSetting::where('user_id', $user->id)->first();
                $states = State::all();
                return response()->json(['success' => true, 'user_profile' => $user_profile, 'round_off_settings' => $round_off_settings, 'states' => $states]);
            } else {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }

    public function store_profile(Request $request)
    {
        if ($request->ukey === 'O5u7NoPuKmotRqdVBiTfcMnkgua1xbtN') {
            $user = null;

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                return response()->json(['success' => false, 'message' => 'User id is required']);
            }

            if ($request->has('book_beginning_from')) {
                $request->book_beginning_from = date('Y-m-d', strtotime(str_replace('/', '-', $request->book_beginning_from)));
            } else {
                return response()->json(['success' => false, 'message' => 'Book begining from is required']);
            }

            if ($request->has('book_ending_on')) {
                $request->book_ending_on = date('Y-m-d', strtotime(str_replace('/', '-', $request->book_ending_on)));
            } else {
                return response()->json(['success' => false, 'message' => 'Book ending on is required']);
            }


            if ($request->has('financial_year_from')) {
                $request->financial_year_from = date('Y-m-d', strtotime(str_replace('/', '-', $request->financial_year_from)));
            } else {
                return response()->json(['success' => false, 'message' => 'Financial year from is required']);
            }

            if ($request->has('financial_year_to')) {
                $request->financial_year_to = date('Y-m-d', strtotime(str_replace('/', '-', $request->financial_year_to)));
            } else {
                return response()->json(['success' => false, 'message' => 'Financial year to is required']);
            }

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

            if ($request->is_registered == '4') {
                $is_operator = 'yes';
            } else {
                $is_operator = 'no';
            }

            if (Carbon::parse($request->financial_year_from) >= Carbon::parse($request->financial_year_to)) {
                return response()->json(['success' => false, 'message' => 'Please provide valid date range for Financial Year']);
            }

            $request->starting_no = 1;

            $request->width_of_numerical = 9;

            $start_no_applicable_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->financial_year_from)));

            $request->period = 'year';

            $suffix_applicable_date = isset($request->suffix_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->suffix_applicable_date))) : null;

            $prefix_applicable_date = isset($request->prefix_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->prefix_applicable_date))) : null;



            $composition_applicable_date = isset($request->composition_applicable_date) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->composition_applicable_date))) : null;

            if ($composition_applicable_date) {
                if (Carbon::parse($request->financial_year_from) > Carbon::parse($composition_applicable_date)) {
                    return response()->json(['success' => false, 'message' => 'Please provide valid composition applicable date']);
                }

                if (Carbon::parse($request->financial_year_to) < Carbon::parse($composition_applicable_date)) {
                    return response()->json(['success' => false, 'message' => 'Please provide valid composition applicable date']);
                }
            }

            if ($request->has('inventory_type')) {
                $inventory_type = $request->inventory_type;
                if ($request->inventory_type == 'without_inventory') {
                    $add_lump_sump = 'yes';
                } else if ($request->inventory_type == 'with_inventory') {
                    $add_lump_sump = 'no';
                }
            } else {
                $inventory_type = 'without_inventory';
                $add_lump_sump = 'yes';
            }

            if ($request->has('inventory_type') && $request->inventory_type == 'with_inventory') {
                if ($request->has('with_inventory_type')) {
                    $with_inventory_type = $request->with_inventory_type;
                } else {
                    $with_inventory_type = 'fifo';
                }
            } else {
                $with_inventory_type = null;
            }

            if ($user) {
                if ($request->hasFile('logo') && $request->hasFile('authorised_signature')) {
                    $path = Storage::disk('public')->putFile('logos', $request->file('logo'));
                    $authorised_signature = Storage::disk('public')->putFile('authorised_signature', $request->file('authorised_signature'));

                    $user_profile = UserProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['name' => $request->name, 'logo' => $path, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'place_of_business' => $request->place_of_business, 'communication_address' => $request->address, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'authorised_signature' => $authorised_signature, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
                    );
                } else if ($request->hasFile('logo')) {
                    $path = Storage::disk('public')->putFile('logos', $request->file('logo'));

                    $user_profile = UserProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['name' => $request->name, 'logo' => $path, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'place_of_business' => $request->place_of_business, 'communication_address' => $request->address, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
                    );
                } else if ($request->hasFile('authorised_signature')) {
                    $authorised_signature = Storage::disk('public')->putFile('authorised_signature', $request->file('authorised_signature'));

                    $user_profile = UserProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['name' => $request->name, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'place_of_business' => $request->place_of_business, 'communication_address' => $request->address, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'authorised_signature' => $authorised_signature, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
                    );
                } else {
                    $user_profile = UserProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['name' => $request->name, 'registered' => $request->is_registered, 'gst' => $request->gst, 'phone' => $request->phone, 'communication_address' => $request->address, 'place_of_business' => $request->place_of_business, 'communication_city' => $request->city, 'communication_state' => $request->state, 'communication_pincode' => $request->pincode, 'shipping_address' => $shipping_address, 'shipping_city' => $shipping_city, 'shipping_state' => $shipping_state, 'shipping_pincode' => $shipping_pincode, 'billing_address' => $billing_address, 'billing_city' => $billing_city, 'billing_state' => $billing_state, 'billing_pincode' => $billing_pincode, 'opening_balance' => $request->opening_balance, 'opening_balance_as_on' => $request->opening_balance_as_on, 'type_of_company' => $request->type_of_company, 'book_beginning_from' => $request->book_beginning_from, 'book_ending_on' => $request->book_ending_on, 'financial_year_from' => $request->financial_year_from, 'financial_year_to' => $request->financial_year_to, 'type' => $request->type, 'authorised_name' => $request->authorised_name, 'ecommerce_operator' => $is_operator, 'type_of_company_state' => $request->type_of_company_state, 'gst_invoice' => $request->gst_invoice, 'bill_of_supply' => $request->bill_of_supply, 'terms_and_condition' => $request->terms_and_condition, 'bill_no_type' => $request->bill_no_type, 'starting_no' => $request->starting_no, 'width_of_numerical' => $request->width_of_numerical, 'start_no_applicable_date' => $start_no_applicable_date, 'period' => $request->period, 'suffix_applicable_date' => $suffix_applicable_date, 'name_of_suffix' => $request->name_of_suffix, 'prefix_applicable_date' => $prefix_applicable_date, 'name_of_prefix' => $request->name_of_prefix, 'format_of_invoice' => $request->format_of_invoice, 'invoice_heading' => $request->invoice_heading, 'add_lump_sump' => $add_lump_sump, 'gross_profit' => $request->gross_profit, 'percent_on_sale_of_invoice' => $request->percent_on_sale_of_invoice, 'composition_applicable_date' => $composition_applicable_date, 'inventory_type' => $inventory_type, 'with_inventory_type' => $with_inventory_type, 'gp_percent_on_sale_value' => $request->gp_percent_on_sale_value]
                    );
                }

                if ($user_profile) {
                    return response()->json(['success' => true, 'message' => 'Profile Updated Successfully']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Failed to update profile']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'invalid_key'], 401);
        }
    }
}
