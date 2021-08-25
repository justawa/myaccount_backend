<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// use Auth;
use Excel;
use Carbon\Carbon;

use App\Group;
use App\GstList;
use App\Invoice;
use App\Item;
use App\ManagedInventory;
use App\MeasuringUnit;
use App\Party;
use App\Purchase;
use App\Purchase_Item;
use App\Invoice_Item;
use App\DebitNote;
use App\CreditNote;
use App\PurchaseRecord;
use App\User;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if( isset( $request->item ) ){
            $items = Item::where('user_id', Auth::user()->id)->where('name', $request->item)->get();
        } else {
            $items = Item::where('user_id', Auth::user()->id)->get();
        }

        foreach($items as $item){
            $group = Group::find($item->group_id);

            if($group){
                $item->group_name = $group->name;
            } else {
                $item->group_name = '';
            }
        }

        return view('item.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = Group::where('user_id', Auth::user()->id)->get();
        $measuring_units = MeasuringUnit::all();

        $gsts = GstList::all();

        return view('item.create', compact('groups', 'measuring_units', 'gsts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $item = new Item;

        $item->type = $request->type_of_service;
        $item->name = $request->name;
        $item->category = $request->category;
        $item->hsc_code = $request->hsc_code;
        $item->sac_code = $request->sac_code;
        $item->group_id = $request->group;
        $item->igst = $request->igst;
        $item->cgst = $request->cgst;
        $item->sgst = $request->sgst;
        $item->gst = $request->gst;

        if($request->show_additional_option == "yes") {

            $manufacture1 = null;
            $expiry1 = null;

            if($request->filled('manufacture')){
                $mtime = strtotime(str_replace('/', '-', $request->manufacture));
                $manufacture1 = date('Y-m-d', $mtime);
            }

            if($request->filled('expiry')){
                $etime = strtotime(str_replace('/', '-', $request->expiry));
                $expiry1 = date('Y-m-d', $etime);
            }

            $item->manufacture = $manufacture1 ?? null;
            $item->expiry = $expiry1 ?? null;
            $item->batch = $request->batch ?? null;
            $item->size = $request->size ?? null;
            $item->purchase_price = $request->purchase_price ?? null;
            $item->sale_price = $request->sale_price ?? null;
            $item->free_qty = $request->free_qty;
        }

        $item->has_additional_items = $request->show_additional_option;

        $item->measuring_unit = $request->measuring_unit;


        $item->original_opening_stock = $request->opening_stock;
        $item->original_opening_stock_unit = $request->opening_stock_unit;

        $opening_stock = $request->opening_stock;

        if ($request->has('measuring_unit_short_name')) {
            $item->measuring_unit_short_name = $request->measuring_unit_short_name;
        }

        if ($request->has('measuring_unit_decimal_place')) {
            $item->measuring_unit_decimal_place = $request->measuring_unit_decimal_place;
        }

        $item->has_alternate_unit = $request->has_alternate_unit;

        if ($request->has('has_alternate_unit') && $request->has_alternate_unit == "yes") {

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

        if ($request->has('has_compound_unit') && $request->has_compound_unit == "yes") {

            $alternate_unit_input = $request->alternate_unit_input;
            $conversion_of_alternate_to_base = $request->conversion_of_alternate_to_base_unit_value;

            $base_unit_conversion = $conversion_of_alternate_to_base / $alternate_unit_input;

            //-----------------------------------

            $compound_unit_input = $request->compound_unit_input;
            $conversion_of_compound_to_alternate_unit_value = $request->conversion_of_compound_to_alternate_unit_value;

            $alt_unit_conversion = $conversion_of_compound_to_alternate_unit_value / $compound_unit_input;



            if($request->has('opening_stock_unit') && $request->opening_stock_unit == $request->compound_measuring_unit){
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

        $item->qty = $request->opening_stock;

        $item->user_id = Auth::user()->id;

        if ($item->save()) {
            return redirect()->back()->with('success', 'Item added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add item');
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
        $item = Item::findOrFail($id);

        return view('item.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $groups = Group::where('user_id', Auth::user()->id)->get();
        $measuring_units = MeasuringUnit::all();
        $item = Item::findOrFail($id);
        $gsts = GstList::all();

        return view('item.edit', compact('groups', 'measuring_units', 'item', 'gsts'));
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
        $item = Item::findOrFail($id);

        $item->type = $request->type_of_service;
        $item->name = $request->name;
        $item->category = $request->category;
        if($request->type_of_service == "service"){
            $item->sac_code = $request->sac_code;
        } else if($request->type_of_service == "physical") {
            $item->hsc_code = $request->hsc_code;
        }
        $item->group_id = $request->group;

        $item->gst = $request->gst;

        $item->has_additional_items = $request->show_additional_option;

        if($request->show_additional_option == "yes") {
            $manufacture1 = null;
            $expiry1 = null;

            if($request->filled('manufacture')){
                $mtime = strtotime(str_replace('/', '-', $request->manufacture));
                $manufacture1 = date('Y-m-d', $mtime);
            }

            if($request->filled('expiry')){
                $etime = strtotime(str_replace('/', '-', $request->expiry));
                $expiry1 = date('Y-m-d', $etime);
            }

            $item->manufacture = $manufacture1 ?? null;
            $item->expiry = $expiry1 ?? null;
            $item->batch = $request->batch ?? null;
            $item->size = $request->size ?? null;
            $item->purchase_price = $request->purchase_price ?? null;
            $item->sale_price = $request->sale_price ?? null;
            $item->free_qty = $request->free_qty;
        }
        
        $item->original_opening_stock = $request->opening_stock;
        $item->original_opening_stock_unit = $request->opening_stock_unit;
        $item->measuring_unit = $request->measuring_unit;
        $alternate_qty = 0;
        $compound_qty = 0;

        if ($request->has('measuring_unit_short_name')) {
            $item->measuring_unit_short_name = $request->measuring_unit_short_name;
        }

        if ($request->has('measuring_unit_decimal_place')) {
            $item->measuring_unit_decimal_place = $request->measuring_unit_decimal_place;
        }

        $item->has_alternate_unit = $request->has_alternate_unit;

        if ($request->has('has_alternate_unit') && $request->has_alternate_unit == "yes") {

            if ($request->has('opening_stock_unit') && $request->opening_stock_unit == $request->alternate_measuring_unit) {
                $alternate_qty = $request->opening_stock;
                $request->opening_stock = $request->conversion_of_alternate_to_base_unit_value * $request->opening_stock;
            }

            $item->alternate_measuring_unit = $request->alternate_measuring_unit;
            $item->alternate_unit_short_name = $request->alternate_unit_short_name;
            $item->alternate_unit_decimal_place = $request->alternate_unit_decimal_place;
            $item->conversion_of_alternate_to_base_unit_value = $request->conversion_of_alternate_to_base_unit_value;
        }

        $item->has_compound_unit = $request->has_compound_unit;

        if ($request->has('has_compound_unit') && $request->has_compound_unit == "yes") {

            if($request->has('opening_stock_unit') && $request->opening_stock_unit == $request->compound_measuring_unit){
                $compound_qty = $request->opening_stock;
                $request->opening_stock = $request->conversion_of_alternate_to_base_unit_value * $request->conversion_of_compound_to_alternate_unit_value * $request->opening_stock;
            }

            $item->compound_measuring_unit = $request->compound_measuring_unit;
            $item->compound_unit_short_name = $request->compound_unit_short_name;
            $item->compound_unit_decimal_place = $request->compound_unit_decimal_place;
            $item->conversion_of_compound_to_alternate_unit_value = $request->conversion_of_compound_to_alternate_unit_value;
        }

        $item->opening_stock = $request->opening_stock;
        $item->opening_alternate_stock = $alternate_qty;
        $item->opening_compound_stock = $compound_qty;
        $item->opening_stock_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->opening_stock_date)));
        $item->opening_stock_rate = $request->opening_stock_rate;
        $item->opening_stock_value = $request->opening_stock_value;

        $item->item_under_rcm = $request->item_under_rcm;


        $item->qty = $request->opening_stock;

        // $item->edit_applicable_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->edit_applicable_date)));

        if ($item->save()) {
            return redirect()->route('item.index')->with('success', 'Item updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update item');
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
        $item = Item::findOrFail($id);

        if ($item->delete()) {
            return redirect()->back()->with('success', 'Item deleted successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to delete item');
        }
    }


    public function fetch_item(Request $request){
        $items = Item::where('group_id', $request->group)->get();

        // return response()->json($items);

        echo json_encode($items);
    }

    public function inventory_report(Request $request)
    {

        $query = Item::where('user_id', Auth::user()->id);

        if($request->has('item')){
            $query = $query->where('name', $request->item);
        }

        $items = $query->get();

        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }
        
        $data = [];
        foreach ($items as $item){
            $group = Group::find($item->group_id);

            $item->group_name = $group->name ?? '';
            $item->data = $this->calculate_stock_summary_and_return($request, $item->id, $from_date, $to_date);
            // dd($item->data);
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
                        // $item->opening_qty = $item->qty;
                        $item->opening_value = $row['amount'];
                    } else {
                       
                        $item->purchase_rate += $row['rate'];
                        $item->purchase_qty += $row['qty'];
                        $item->purchase_value += $row['amount'];
                    }
                }
                
                // $item->opening_qty = $item->qty;
                
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
                     
                    // dd($item->closing_qty);
                }
                foreach($row['balance']['amount'] as $amount){
                   $item->closing_value = $item->opening_value + $item->purchase_value - $item->sale_value;
                }
            }
        }

        // return $item;
        // dd($item);
        return view('report.stock_summary', compact('items', 'from_date', 'to_date'));
    }

    // public function inventory_report(Request $request)
    // {

    //     // if( $request->has('item') ){
    //     //     $items = Item::where('name', $request->item)->where('user_id', Auth::user()->id)->get();
    //     // } else {
    //     //     $items = Item::where('user_id', Auth::user()->id)->get();
    //     // }

    //     $query = Item::where('user_id', Auth::user()->id);

    //     if($request->has('item')){
    //         $query = $query->where('name', $request->item);
    //     }

    //     $items = $query->get();

    //     if ($request->has('from_date') && $request->has('to_date')) {
    //         $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
    //         $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
    //     } else {
    //         $from_date = auth()->user()->profile->financial_year_from;
    //         $to_date = auth()->user()->profile->financial_year_to;
    //     }

    //     $value_type = $request->value_type;
    //     // $price_type = $request->price_type;

    //     // return $items;

        
    //     foreach ($items as $item) {
    //         $group = Group::find($item->group_id);

    //         $item->group_name = $group->name ?? '';

    //         // return $item;

    //         /*----------Purchased Item--------------*/
    //         $purchasedItem = Purchase::where('item_id', $item->id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on')->get();
    //         /*-----------------------------------*/
            
    //         $item->purchasedRate =  0;
    //         $item->purchasedQty = 0;
    //         foreach($purchasedItem as $thisItem) {
    //             // Add sale credit note quantity to inward side
    //             $soldItemCreditNotes = User::find(Auth::user()->id)->creditNotes()->where('credit_notes.item_id', $thisItem->id)->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->orderBy('credit_notes.note_date')->get();
    //             $item->purchasedQty += $thisItem->qty;
    //             $item->purchasedQty += $thisItem->free_qty;
    //             $item->purchasedRate += $thisItem->item_total;
    //             foreach($soldItemCreditNotes as $note) {
    //                 $item->purchasedQty += $note->quantity;
    //             }
    //         }


    //         if($value_type == "average") {
    //             if(count($purchasedItem) > 0) {
    //                 $item->purchasedRate = $item->purchasedRate / count($purchasedItem);
    //             }
    //         }

    //         // return $purchasedItem;

    //         /*-----------Sold Item----------------------*/
    //         $soldItem = Invoice_Item::where('item_id', $item->id)->whereBetween('sold_on', [$from_date, $to_date])->get();
    //         /*------------------------------------------*/
            
    //         $item->soldRate =  0;
    //         $item->soldQty = 0;
    //         foreach($soldItem as $thisItem){
    //             // Add purchase debit note quantity to outward side
    //             $purchasedItemDebitNotes = User::find(Auth::user()->id)->debitNotes()->where('debit_notes.item_id', $thisItem->id)->where('debit_notes.type', 'purchase')->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->get();
                
    //             $item->soldQty += $thisItem->item_qty;
    //             $item->soldQty += $thisItem->free_qty;
    //             $item->soldRate += $thisItem->item_total;
    //             foreach($purchasedItemDebitNotes as $note) {
    //                 $item->soldQty -= $note->quantity;
    //             }
    //         }

    //         if($value_type == "average") {
    //             if(count($soldItem) > 0){
    //                 $item->soldRate = $item->soldRate / count($soldItem);
    //             }

    //             $value = $item->opening_stock_value;
    //             $qty = $item->opening_stock;
    //             // calculating closing balance for "average" value type
    //             foreach($purchasedItem as $thisItem) {
    //                 $value += $thisItem->item_total;
    //                 $qty += $thisItem->qty;
    //             }

    //             foreach($soldItem as $thisItem){
    //                 $qty -= $thisItem->qty;
    //             }

    //             $item->average_closing_rate = $qty > 0 ? $value / $qty : 0;
    //             $item->average_closing_qty = $qty;
    //         }

    //         /*-----------Managed Inventory----------------------*/
    //         // $managed_inventories = auth()->user()->profile->inventory_type != "without_inventory" ? auth()->user()->managedInventories()->where('item_id', $item->id)->whereBetween('value_updated_on', [$from_date, $to_date])->get() : [];
    //         /*-----------Managed Inventory----------------------*/

    //         // foreach($managed_inventories as $item){
    //         //     $item->purchasedQty += $thisItem->qty;
    //         //     $item->purchasedRate += $thisItem->price;
    //         // }
    //         // return $soldItem;

    //         // $debitNotes = DebitNote::where('item_id', $item->id)->whereBetween('created_at', [$from_date, $to_date])->get();
    //         // foreach($debitNotes as $note){

    //         //     if($note->type == 'sale'){
    //         //         $item->soldQty += $note->quantity_difference;
    //         //     }

    //         //     if($note->type == 'purchase'){
    //         //         $item->purchasedQty -= $note->quantity_difference;
    //         //     }
                
    //         // }

    //         // $creditNotes = CreditNote::where('item_id', $item->id)->whereBetween('created_at', [$from_date, $to_date])->get();
    //         // foreach ($creditNotes as $note) {
    //         //     if ($note->type == 'sale') {
    //         //         $item->soldQty -= $note->quantity_difference;
    //         //     }

    //         //     if ($note->type == 'purchase') {
    //         //         $item->purchasedQty += $note->quantity_difference;
    //         //     }
    //         // }
 
    //         // $standard_price = 0;
    //         // if( isset($value_type) && $value_type == 'standard' ) {
    //         //     if($request->has('price')){
    //         //         $standard_price = $request->has('price');
    //         //     }
    //         // }

    //         // $item->opening_stock = $this->calculate_item_opening_balance($from_date, $item, $value_type, $price_type, $standard_price);
    //         // $check_array;
    //         // if( auth()->user()->profile->gp_percent_on_sale_value ){
    //         //     $returned_array = $this->get_data_using_fifo_or_lifo($from_date, $to_date, $item, $value_type, $price_type, $standard_price, auth()->user()->profile->gp_percent_on_sale_value, true);

    //         //     // $check_array = $returned_array;

    //         //     $item->closing_value = $returned_array['closing_value'];

    //         //     $item->item_sequence = json_encode($returned_array['items']);
    //         //     $item->price_sequence = json_encode($returned_array['prices']);
    //         // }
    //         // else if( isset($value_type) && $value_type == 'average' ) {
    //         //     $returned_array = $this->get_average_data($item, $from_date, $to_date);

    //         //     $item->closing_value = $returned_array['closing_value'];

    //         //     $item->item_sequence = json_encode($returned_array['items']);
    //         //     $item->price_sequence = json_encode($returned_array['prices']);
    //         // } 
    //         // else if( isset($value_type) && $value_type == 'standard' ) {
    //         //     $returned_array = $this->get_standard_data($item, $from_date, $to_date, $standard_price);

    //         //     $item->closing_value = $returned_array['closing_value'];

    //         //     $item->item_sequence = json_encode($returned_array['items']);
    //         //     $item->price_sequence = json_encode($returned_array['prices']);
    //         // }
    //         // else {
    //         //     $returned_array = $this->get_data_using_fifo_or_lifo($from_date, $to_date, $item, $value_type, $price_type, $standard_price);

    //         //     $item->closing_value = $returned_array['closing_value'];

    //         //     $item->item_sequence = json_encode($returned_array['items']);
    //         //     $item->price_sequence = json_encode($returned_array['prices']);
    //         // }

    //         // get_fifo_data();
    //         // get_lifo_data();
    //         // get_average_data();
    //         // get_standard_data();
    //     }

    //     // return $check_array;

    //     // return $items;

    //     return view('report.item', compact('items', 'from_date', 'to_date'));
    // }

    private function get_average_data($item, $from_date, $to_date) {
        $sold_items = array();
        $sold_item_prices = array();

        $sold_items_fifo = array();
        $sold_item_prices_fifo = array();
        $sold_items_lifo = array();
        $sold_item_prices_lifo = array();

        $purchased_items_fifo = array();
        $purchased_item_prices_fifo = array();
        $purchased_items_lifo = array();
        $purchased_item_prices_lifo = array();

        if(! is_null($item) ) {
            $invoice_items = Invoice_Item::where("item_id", $item->id)->whereBetween('sold_on', [$from_date, $to_date])->get();

            foreach( $invoice_items as $sold_item ){
                array_push( $sold_items, $sold_item->item_qty );
                array_push( $sold_item_prices, $sold_item->item_price );
            }

            $sold_items_fifo = $sold_items;
            $sold_item_prices_fifo = $sold_item_prices;

            $average_closing_value_fifo = 0;

            $purchase_fifo_items = Purchase::where('item_id', $item->id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on', 'asc')->get();

            array_push( $purchased_items_fifo, $item->opening_stock );
            array_push( $purchased_item_prices_fifo, $item->opening_stock_rate );


            foreach( $purchase_fifo_items as $purchase_item ){
                array_push( $purchased_items_fifo, $purchase_item->qty );
                array_push( $purchased_item_prices_fifo, $purchase_item->price );
            }

            // making price variable global
            $price = 0;
            if( count($purchased_item_prices_fifo) > 0){
                $price = array_sum( $purchased_item_prices_fifo ) / count( $purchased_item_prices_fifo );
                $average_closing_value_fifo = array_sum($purchased_items_fifo) * $price;
            } else{
                $price = 0;
                $average_closing_value_fifo = 0;
            }

            $array_to_return = ['closing_value' => $average_closing_value_fifo, 'items' => $purchased_items_fifo, 'prices' => $price];

            return $array_to_return;  

            // Dont know if this is required
                // for ($i = 0; $i < $sold_fifo_count; $i++) {
                //     $sold_item_average_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                //     $sold_item_average_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                // }

                // $gross_profit_average_fifo = $sold_item_average_totals_fifo - $sold_item_average_calculated_totals_fifo;
            // Dont know if this is required

            // return $average_closing_value_fifo;
        }
        
        // if item is null then return 0;
        return ['closing_value' => null, 'items' => null, 'prices' => 0];
    }

    private function get_standard_data($item, $from_date, $to_date, $standard_price_value) {
        $sold_items = array();
        $sold_item_prices = array();

        $sold_items_fifo = array();
        $sold_item_prices_fifo = array();
        $sold_items_lifo = array();
        $sold_item_prices_lifo = array();

        $purchased_items_fifo = array();
        $purchased_item_prices_fifo = array();
        $purchased_items_lifo = array();
        $purchased_item_prices_lifo = array();

        if(! is_null($item) ) {
            $invoice_items = Invoice_Item::where("item_id", $item->id)->whereBetween('sold_on', [$from_date, $to_date])->get();

            foreach( $invoice_items as $sold_item ){
                array_push( $sold_items, $sold_item->item_qty );
                array_push( $sold_item_prices, $sold_item->item_price );
            }

            $sold_items_fifo = $sold_items;
            $sold_item_prices_fifo = $sold_item_prices;

            $standard_closing_value_fifo = 0;

            $purchase_fifo_items = Purchase::where('item_id', $item->id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on', 'asc')->get();

            array_push( $purchased_items_fifo, $item->opening_stock );
            array_push( $purchased_item_prices_fifo, $item->opening_stock_rate );


            foreach( $purchase_fifo_items as $purchase_item ){
                array_push( $purchased_items_fifo, $purchase_item->qty );
                array_push( $purchased_item_prices_fifo, $purchase_item->price );
            }

            $price = $standard_price_value;

            $standard_closing_value_fifo = array_sum($purchased_items_fifo) * $price;

            // Dont know if this is required
                // for ($i = 0; $i < $sold_fifo_count; $i++) {

                //     $sold_item_standard_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                //     $sold_item_standard_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                // }

                // $gross_profit_standard_fifo = $sold_item_standard_totals_fifo - $sold_item_standard_calculated_totals_fifo;
            // Dont know if this is required

            return $standard_closing_value_fifo;
        } 
        
        // if item is null then return 0;
        return ['closing_value' => null, 'items' => null, 'prices' => 0];
    }

    public function calculate_item_opening_balance($till_date, $item = null, $value_type = 'fifo', $price_type = 'normal', $standard_price_value = 0, $gp_percent = 0, $lump_sump = false)
    {
        if( is_array($value_type) ){

			$lifo = in_array('lifo', $value_type);
            $fifo = in_array('fifo', $value_type);

        } else {
            $fifo = true;
            $lifo = false;
            $lump_sump = false;
        }

        if(! is_null($item) ) {
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

            $invoice_items = Invoice_Item::where("item_id", $item->id)->where('sold_on', '<', $till_date)->get();

            foreach( $invoice_items as $sold_item ){
                $sale_total_qty = $sold_item->item_qty + $sold_item->free_qty;
                array_push( $sold_items, $sale_total_qty );
                array_push( $sold_item_prices, $sold_item->item_price );
            }

            $sold_items_fifo = $sold_items;
            $sold_item_prices_fifo = $sold_item_prices;

            $sold_items_lifo = $sold_items;
            $sold_item_prices_lifo = $sold_item_prices;


            $purchase_fifo_items = Purchase::where('item_id', $item->id)->where('bought_on', '<', $till_date)->orderBy('bought_on', 'asc')->get();
            $purchase_lifo_items = Purchase::where('item_id', $item->id)->where('bought_on', '<', $till_date)->orderBy('bought_on', 'desc')->get();
            

            if ( auth()->user()->profile->inventory_type == "without_inventory" ) {
                array_push( $purchased_items_fifo, $item->opening_stock );
                array_push( $purchased_item_prices_fifo, $item->opening_stock_rate );
            } else {
                array_push( $purchased_items_fifo, 1 );
                array_push( $purchased_item_prices_fifo, $item->opening_stock_value );
            }

            foreach( $purchase_fifo_items as $purchase_item ){
                $purchase_total_qty = $purchase_item->qty + $purchase_item->free_qty;
                array_push( $purchased_items_fifo, $purchase_total_qty );
                array_push( $purchased_item_prices_fifo, $purchase_item->price );
            }

            foreach( $purchase_lifo_items as $purchase_item ){
                $purchase_total_qty = $purchase_item->qty + $purchase_item->free_qty;
                array_push( $purchased_items_lifo, $purchase_total_qty );
                array_push( $purchased_item_prices_lifo, $purchase_item->price );
            }

            if ( auth()->user()->profile->inventory_type == "without_inventory" ) {
                array_push($purchased_items_lifo, $item->opening_stock);
                array_push($purchased_item_prices_lifo, $item->opening_stock_rate);
            } else {
                array_push($purchased_items_lifo, 1);
                array_push($purchased_item_prices_lifo, $item->opening_stock_value);
            }

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

            if($lump_sump){
                $purchase_total = array_sum($purchased_items_fifo);
                $sale_total = array_sum($sold_items_fifo);
                $gp_total = $sale_total * $gp_percent / 100;

                return $purchase_total + $gp_total - $sale_total;
            }

            if($fifo){
                for( $i=0; $i<$sold_fifo_count; $i++ ){
                    if( $sold_items_fifo[$i] == 0 )
                        continue;
    
                    for( $j=0; $j<$purchase_fifo_count; $j++ ){
    
    
                        if( $sold_items_fifo[$i]  == 0){
                            continue 2;
                        }
    
                        if($purchased_items_fifo[$j] == 0){
                            continue;
                        }
    
    
                        if( $sold_items_fifo[$i] > $purchased_items_fifo[$j] )
                        {
                            $gross_profit_fifo += ($purchased_items_fifo[$j] * $sold_item_prices_fifo[$i]) - ( $purchased_items_fifo[$j] * $purchased_item_prices_fifo[$j]);
    
    
                            $sold_items_fifo[$i] = $sold_items_fifo[$i] - $purchased_items_fifo[$j];
                            $purchased_items_fifo[$j] = 0;
    
                        }
    
                        if( $purchased_items_fifo[$j] >= $sold_items_fifo[$i] ){
    
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
    
                if( is_array($price_type) ){
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

                if( $standard ) {
                    $price = $standard_price_value;

                    $standard_closing_value_fifo = array_sum($purchased_items_fifo) * $price;

                    for ($i = 0; $i < $sold_fifo_count; $i++) {

                        $sold_item_standard_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                        $sold_item_standard_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                    }

                    $gross_profit_standard_fifo = $sold_item_standard_totals_fifo - $sold_item_standard_calculated_totals_fifo;
                }
                
                if ( $average ) {
                    if( count($purchased_item_prices_fifo) > 0){
                        $price = array_sum( $purchased_item_prices_fifo ) / count( $purchased_item_prices_fifo );
                        $average_closing_value_fifo = array_sum($purchased_items_fifo) * $price;
                    } else{
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

            if($lifo){
                for( $i=0; $i<$sold_lifo_count; $i++ ){
                    if( $sold_items_lifo[$i] == 0 )
                        continue;
    
                    for( $j=0; $j<$purchase_lifo_count; $j++ ){
    
    
                        if( $sold_items_lifo[$i]  == 0){
                            continue 2;
                        }
    
                        if($purchased_items_lifo[$j] == 0){
                            continue;
                        }
    
    
                        if( $sold_items_lifo[$i] > $purchased_items_lifo[$j] )
                        {
                            $gross_profit_lifo += ($purchased_items_lifo[$j] * $sold_item_prices_lifo[$i]) - ( $purchased_items_lifo[$j] * $purchased_item_prices_lifo[$j]);
    
    
                            $sold_items_lifo[$i] = $sold_items_lifo[$i] - $purchased_items_lifo[$j];
                            $purchased_items_lifo[$j] = 0;
    
                        }
    
                        if( $purchased_items_lifo[$j] >= $sold_items_lifo[$i] ){
    
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
    
                if( is_array($price_type) ){
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

                if( $standard ) {
                    $price = $standard_price_value;

                    $standard_closing_value_lifo = array_sum($purchased_items_lifo) * $price;

                    for ($i = 0; $i < $sold_lifo_count; $i++) {

                        $sold_item_standard_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                        $sold_item_standard_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                    }

                    $gross_profit_standard_lifo = $sold_item_standard_totals_lifo - $sold_item_standard_calculated_totals_lifo;
                }
                
                if ( $average ) {
                    if( count($purchased_item_prices_lifo) > 0){
                        $price = array_sum( $purchased_item_prices_lifo ) / count( $purchased_item_prices_lifo );
                        $average_closing_value_lifo = array_sum($purchased_items_lifo) * $price;
                    } else{
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

    private function get_data_using_fifo_or_lifo($from_date, $to_date, $item = null, $value_type = 'fifo', $price_type = 'normal', $standard_price_value = 0, $gp_percent = 0, $lump_sump = false)
    {

        if( is_array($value_type) ){

			$lifo = in_array('lifo', $value_type);
            $fifo = in_array('fifo', $value_type);

        } else {
            $fifo = true;
            $lifo = false;
            // $lump_sump = false;
        }

        if(! is_null($item) ) {
            $sold_items = array();
            $sold_item_prices = array();

            $sold_items_fifo = array();
            $sold_item_prices_fifo = array();
            $sold_items_lifo = array();
            $sold_item_prices_lifo = array();

            $purchased_items_fifo = array();
            $purchased_item_prices_fifo = array();
            $purchased_items_lifo = array();
            $purchased_item_prices_lifo = array();

            $invoice_items = Invoice_Item::where("item_id", $item->id)->whereBetween('sold_on', [$from_date, $to_date])->get();

            foreach( $invoice_items as $sold_item ){
                $sale_total_qty = $sold_item->item_qty + $sold_item->free_qty;
                if ( !$lump_sump ) {
                    array_push( $sold_items, $sale_total_qty );
                } else {
                    array_push( $sold_items, 1 );
                }
                array_push( $sold_item_prices, $sold_item->item_price );
            }

            $sold_items_fifo = $sold_items;
            $sold_item_prices_fifo = $sold_item_prices;

            $sold_items_lifo = $sold_items;
            $sold_item_prices_lifo = $sold_item_prices;


            $purchase_fifo_items = Purchase::where('item_id', $item->id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on', 'asc')->get();
            $purchase_lifo_items = Purchase::where('item_id', $item->id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on', 'desc')->get();

            // return $purchase_fifo_items;
            // return $purchase_lifo_items;
            

            if ( !$lump_sump ) {
                array_push( $purchased_items_fifo, $item->opening_stock );
                array_push( $purchased_item_prices_fifo, $item->opening_stock_rate );
            } else {
                array_push($purchased_items_fifo, 1);
                array_push($purchased_item_prices_fifo, $item->opening_stock_value);
            }

            foreach( $purchase_fifo_items as $purchase_item ) {
                $purchase_total_qty = $purchase_item->qty + $purchase_item->free_qty;
                if ( !$lump_sump ) {
                    array_push( $purchased_items_fifo, $purchase_total_qty );
                } else {
                    array_push( $purchased_items_fifo, 1 );
                }
                array_push( $purchased_item_prices_fifo, $purchase_item->price );
            }

            foreach( $purchase_lifo_items as $purchase_item ){
                $purchase_total_qty = $purchase_item->qty + $purchase_item->free_qty;
                if ( !$lump_sump ) {
                    array_push( $purchased_items_lifo, $purchase_total_qty );
                } else {
                    array_push( $purchased_items_lifo, 1 );
                }
                array_push( $purchased_item_prices_lifo, $purchase_item->price );
            }

            if ( !$lump_sump ) {
                array_push($purchased_items_lifo, $item->opening_stock);
                array_push($purchased_item_prices_lifo, $item->opening_stock_rate);
            } else {
                array_push($purchased_items_lifo, 1);
                array_push($purchased_item_prices_lifo, $item->opening_stock_value);
            }

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

            if($lump_sump){
                $purchase_total = array_sum($purchased_item_prices_fifo);
                $sale_total = array_sum($sold_item_prices);
                $gp_total = $sale_total * $gp_percent / 100;

                $closing_value = $purchase_total + $gp_total - $sale_total;

                $array_to_return = ['closing_value' => $closing_value, 'items' => $purchased_items_fifo, 'prices' => $gp_total];

                return $array_to_return;
            }

            if($fifo){
                for( $i=0; $i<$sold_fifo_count; $i++ ){
                    if( $sold_items_fifo[$i] == 0 )
                        continue;
    
                    for( $j=0; $j<$purchase_fifo_count; $j++ ){
    
    
                        if( $sold_items_fifo[$i]  == 0){
                            continue 2;
                        }
    
                        if($purchased_items_fifo[$j] == 0){
                            continue;
                        }
    
    
                        if( $sold_items_fifo[$i] > $purchased_items_fifo[$j] )
                        {
                            $gross_profit_fifo += ($purchased_items_fifo[$j] * $sold_item_prices_fifo[$i]) - ( $purchased_items_fifo[$j] * $purchased_item_prices_fifo[$j]);
    
    
                            $sold_items_fifo[$i] = $sold_items_fifo[$i] - $purchased_items_fifo[$j];
                            $purchased_items_fifo[$j] = 0;
    
                        }
    
                        if( $purchased_items_fifo[$j] >= $sold_items_fifo[$i] ){
    
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
    
                // if( is_array($price_type) ){
                //     $standard = in_array('standard', $price_type);
                //     $average = in_array('average', $price_type);
                //     $normal = in_array('normal', $price_type);
                // } else {
                    $standard = false;
                    $average = false;
                    $normal = true;
                // }

                $sold_item_standard_totals_fifo = 0;
                $sold_item_average_totals_fifo = 0;
                $sold_item_standard_calculated_totals_fifo = 0;
                $sold_item_average_calculated_totals_fifo = 0;

                // if( $standard ) {
                //     $price = $standard_price_value;

                //     $standard_closing_value_fifo = array_sum($purchased_items_fifo) * $price;

                //     for ($i = 0; $i < $sold_fifo_count; $i++) {

                //         $sold_item_standard_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                //         $sold_item_standard_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                //     }

                //     $gross_profit_standard_fifo = $sold_item_standard_totals_fifo - $sold_item_standard_calculated_totals_fifo;
                // }
                
                // if ( $average ) {
                //     if( count($purchased_item_prices_fifo) > 0){
                //         $price = array_sum( $purchased_item_prices_fifo ) / count( $purchased_item_prices_fifo );
                //         $average_closing_value_fifo = array_sum($purchased_items_fifo) * $price;
                //     } else{
                //         $price = 0;
                //         $average_closing_value_fifo = 0;
                //     }

                //     for ($i = 0; $i < $sold_fifo_count; $i++) {
                //         $sold_item_average_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                //         $sold_item_average_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                //     }

                //     $gross_profit_average_fifo = $sold_item_average_totals_fifo - $sold_item_average_calculated_totals_fifo;
                // }
    
                // $item->value_amount_fifo = $total_value_fifo;
                // $item->gross_profit_fifo = $gross_profit_fifo;

                $array_to_return = ['closing_value' => $closing_value_fifo, 'items' => $purchased_items_fifo, 'prices' => $purchased_item_prices_fifo];
                
                // earlier was sending only closing value 
                // return $closing_value_fifo;

                // now returning closing, fifo items and fifo prices;
                return $array_to_return;
            }

            //----------------------------Lifo starts

            if($lifo){
                for( $i=0; $i<$sold_lifo_count; $i++ ){
                    if( $sold_items_lifo[$i] == 0 )
                        continue;
    
                    for( $j=0; $j<$purchase_lifo_count; $j++ ){
    
    
                        if( $sold_items_lifo[$i]  == 0){
                            continue 2;
                        }
    
                        if($purchased_items_lifo[$j] == 0){
                            continue;
                        }
    
    
                        if( $sold_items_lifo[$i] > $purchased_items_lifo[$j] )
                        {
                            $gross_profit_lifo += ($purchased_items_lifo[$j] * $sold_item_prices_lifo[$i]) - ( $purchased_items_lifo[$j] * $purchased_item_prices_lifo[$j]);
    
    
                            $sold_items_lifo[$i] = $sold_items_lifo[$i] - $purchased_items_lifo[$j];
                            $purchased_items_lifo[$j] = 0;
    
                        }
    
                        if( $purchased_items_lifo[$j] >= $sold_items_lifo[$i] ){
    
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
    
                // if( is_array($price_type) ){
                //     $standard = in_array('standard', $price_type);
                //     $average = in_array('average', $price_type);
                //     $normal = in_array('normal', $price_type);
                // } else {
                    $standard = false;
                    $average = false;
                    $normal = true;
                // }
    
                $sold_item_standard_totals_lifo = 0;
                $sold_item_average_totals_lifo = 0;
                $sold_item_standard_calculated_totals_lifo = 0;
                $sold_item_average_calculated_totals_lifo = 0;

                // if( $standard ) {
                //     $price = $standard_price_value;

                //     $standard_closing_value_lifo = array_sum($purchased_items_lifo) * $price;

                //     for ($i = 0; $i < $sold_lifo_count; $i++) {

                //         $sold_item_standard_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                //         $sold_item_standard_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                //     }

                //     $gross_profit_standard_lifo = $sold_item_standard_totals_lifo - $sold_item_standard_calculated_totals_lifo;
                // }
                
                // if ( $average ) {
                //     if( count($purchased_item_prices_lifo) > 0){
                //         $price = array_sum( $purchased_item_prices_lifo ) / count( $purchased_item_prices_lifo );
                //         $average_closing_value_lifo = array_sum($purchased_items_lifo) * $price;
                //     } else{
                //         $price = 0;
                //         $average_closing_value_lifo = 0;
                //     }

                //     for ($i = 0; $i < $sold_lifo_count; $i++) {
                //         $sold_item_average_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                //         $sold_item_average_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                //     }

                //     $gross_profit_average_lifo = $sold_item_average_totals_lifo - $sold_item_average_calculated_totals_lifo;
                // }
    
                
    
                // $item->value_amount_lifo = $total_value_lifo;
                // $item->gross_profit_lifo = $gross_profit_lifo
                
                $array_to_return = ['closing_value' => $closing_value_lifo, 'items' => $purchased_items_lifo, 'prices' => $purchased_item_prices_lifo];

                // return $closing_value_lifo;

                return $array_to_return;
            }

            //-----------------------------Lifo ends
            
        } else {
            return ['closing_value' => null, 'items' => null, 'prices' => 0];
        }
    }

    public function item_value(Request $request) {

        if( isset( $request->item ) ){
            $items = Item::where('user_id', Auth::user()->id)->where('name', $request->item)->get();
        } 
        else if( isset( $request->from_date ) && isset( $request->to_date ) ){
            $items = Item::where("user_id", Auth::user()->id)->whereBetween( 'created_at', [ $request->from_date, $request->to_date ] )->get();
        }
        else {
            $items = Item::where("user_id", Auth::user()->id)->get();
        }

        // foreach( $items as $item ) {
        //     $purchases = Purchase::where('item_id', $item->id)->get();

        //     $this_amount = 0;

        //     foreach( $purchases as $purchase ) {
        //         $this_amount += $purchase->price * $item->qty;
        //     }

        //     $item->value_amount = $this_amount;

        // }

        foreach( $items as $item ){
            $sold_items = array();
            $sold_item_prices = array();

            $purchased_items = array();
            $purchased_item_prices = array();

            $invoice_items = Invoice_Item::where("item_id", $item->id)->get();

            foreach( $invoice_items as $sold_item ){
                array_push( $sold_items, $sold_item->item_qty );
                array_push( $sold_item_prices, $sold_item->item_price );
            }

            $purchase_items = Purchase::where('item_id', $item->id)->get();

            foreach( $purchase_items as $purchase_item ){
                array_push( $purchased_items, $purchase_item->qty );
                array_push( $purchased_item_prices, $purchase_item->price );
            }

            $sold_count = count($sold_items);
            $purchase_count = count($purchased_items);
            $total = 0;

            for( $i=0; $i<$sold_count; $i++ ){
                if( $sold_items[$i] == 0 )
                    continue;

                for( $j=0; $j<$purchase_count; $j++ ){

                    // echo "S = " . $sold_items[$i] . "<br/>";
                    // echo "P = " . $purchased_items[$j] . "<br/>";

                    if( $sold_items[$i]  == 0 )
                        continue 2;

                    if( $purchased_items[$j] == 0 )
                        continue;

                    if( $sold_items[$i] > $purchased_items[$j] )
                    {
                        $s1 = $purchased_items[$j];

                        $value = ($s1 * $sold_item_prices[$i]) - ($s1 * $purchased_item_prices[$j]);

                        // echo $s1 . " x " . $sold_item_prices[$i] . " - " . $s1 . " x " . $purchased_item_prices[$j] . " = " .$value . "<br/>";

                        $total += $value;

                        $sold_items[$i] = $sold_items[$i] - $purchased_items[$j];
                        $purchased_items[$j] = 0;

                    }

                    if( $purchased_items[$j] >= $sold_items[$i] ){
                        
                        $value = ( $sold_items[$i] * $sold_item_prices[$i] ) - ( $sold_items[$i] * $purchased_item_prices[$j] );

                        // echo $sold_items[$i] . " x " . $sold_item_prices[$i] . " - " . $sold_items[$i] . " x " . $purchased_item_prices[$j] . " = " . $value . "<br/>";

                        $total += $value;

                        $purchased_items[$j] = $purchased_items[$j] - $sold_items[$i];
                        $sold_items[$i] = 0;
                    }

                    // echo "Inner " . $total . "<br/>";
                }
            }

            $item->value_amount = $total;

        }

        return view('item.item_value', compact('items'));        
    }

    public function calculated_value_of_inventory() {

        //---------------------------------------------------------------------------------------------
        // $items = Item::where("user_id", Auth::user()->id)->get();

        $sold_items = array();
        $sold_item_prices = array();

        $purchased_items = array();
        $purchased_item_prices = array();


        // foreach( $items as $item ){
        
        // }

        $invoice_items = Invoice_Item::where("item_id", 1)->get();

        foreach( $invoice_items as $sold_item ){
            array_push( $sold_items, $sold_item->item_qty );
            array_push( $sold_item_prices, $sold_item->item_price );
        }

        $purchase_items = Purchase::where('item_id', 1)->get();

        foreach( $purchase_items as $purchase_item ){
            array_push( $purchased_items, $purchase_item->qty );
            array_push( $purchased_item_prices, $purchase_item->price );
        }

        echo "<pre>";
        echo "Sold Qty ";
        print_r( $sold_items );
        echo "Sold Price ";
        print_r( $sold_item_prices );
        echo "Purchased Qty ";
        print_r( $purchased_items );
        echo "Purchased Price ";
        print_r( $purchased_item_prices );
        echo "Sold Qty: " . array_sum( $sold_items );
        echo "<br/>";
        echo "Purchased Qty: " . array_sum( $purchased_items );
        echo "</pre>";



        $sold_count = count( $sold_items );
        $purchase_count = count( $purchased_items );
        $total = 0;

        for( $i=0; $i<$sold_count; $i++ ){
            if( $sold_items[$i] == 0 )
                continue;

            for( $j=0; $j<$purchase_count; $j++ ){

                // echo "S = " . $sold_items[$i] . "<br/>";
                // echo "P = " . $purchased_items[$j] . "<br/>";

                if( $sold_items[$i]  == 0 )
                    continue 2;

                if( $purchased_items[$j] == 0 )
                    continue;

                if( $sold_items[$i] > $purchased_items[$j] )
                {
                    $s1 = $purchased_items[$j];

                    $value = ($s1 * $sold_item_prices[$i]) - ($s1 * $purchased_item_prices[$j]);

                    // echo $s1 . " x " . $sold_item_prices[$i] . " - " . $s1 . " x " . $purchased_item_prices[$j] . " = " .$value . "<br/>";

                    $total += $value;

                    $sold_items[$i] = $sold_items[$i] - $purchased_items[$j];
                    $purchased_items[$j] = 0;

                }

                if( $purchased_items[$j] >= $sold_items[$i] ){
                    
                    $value = ( $sold_items[$i] * $sold_item_prices[$i] ) - ( $sold_items[$i] * $purchased_item_prices[$j] );

                    // echo $sold_items[$i] . " x " . $sold_item_prices[$i] . " - " . $sold_items[$i] . " x " . $purchased_item_prices[$j] . " = " . $value . "<br/>";

                    $total += $value;

                    $purchased_items[$j] = $purchased_items[$j] - $sold_items[$i];
                    $sold_items[$i] = 0;
                }

                // echo "Inner " . $total . "<br/>";
            }
        }

        // echo "<br/>";
        echo $total;
    }

    public function manage_inventory() {
        $items = Item::where("user_id", Auth::user()->id)->get();
        $units = MeasuringUnit::all();

        return view("item.manage_inventory", compact('items', 'units'));
    }

    public function post_manage_inventory(Request $request) {
        $item = Item::find($request->item);

        $managed_inventory = new ManagedInventory;

        if ($item->alternate_measuring_unit != null && $item->alternate_measuring_unit == $request->measuring_unit) {
            if($request->has('count')){
                $qty_count = $item->conversion_of_alternate_to_base_unit_value * $request->count;
            } else {
                $qty_count = 0;
            }
            
            if($request->has('rate')){
                $rate = $request->rate * $item->conversion_of_compound_to_alternate_unit_value;;
            } else {
                $rate = 0;
            }
        }
        else if ($item->compound_measuring_unit != null && $item->compound_measuring_unit == $request->measuring_unit) {
            if($request->has('count')){
                $qty_count = $item->conversion_of_alternate_to_base_unit_value * $item->conversion_of_compound_to_alternate_unit_value * $request->count;
            } else {
                $qty_count = 0;
            }
            
            if($request->has('rate')){
                $rate = $request->rate * ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);                
            } else {
                $rate = 0;
            }
        }
        else if ($item->measuring_unit != null && $item->measuring_unit == $request->measuring_unit) {
            if($request->has('count')){
                $qty_count = $request->count;
            } else {
                $qty_count = 0;
            }
            
            if($request->has('rate')){
                $rate = $request->rate;
            } else {
                $rate = 0;
            }
        }
        //this(down) case shall not occur 
        else {
            $qty_count = 0;
            $rate = 0;
        }

        $item->qty = $qty_count;

        $managed_inventory->measuring_unit = $request->measuring_unit;
        $managed_inventory->qty = $qty_count;
        // $managed_inventory->rate = $rate;
        // $managed_inventory->value = $request->value;
        $managed_inventory->value_updated_on = date('Y-m-d', strtotime( str_replace('/', '-', $request->update_date)));
        $managed_inventory->reason = $request->reason;
        $managed_inventory->value_updation_note = $request->note;
        $managed_inventory->item_id = $item->id;

        $managed_inventory->save();

        if ($item->save()) {
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }

    }

    public function search_item_by_name( Request $request ) 
    {

        $items = User::find(auth()->user()->id)->items()->where('name', 'like', $request->item.'%')->get();

        return response()->json( $items );
    }

    public function item_report(Request $request)
    {

        if( isset( $request->item ) ){
            $items = Item::where('user_id', Auth::user()->id)->where('name', $request->item)->get();
        } else {
            $items = Item::where('user_id', Auth::user()->id)->get();
        }

        foreach($items as $item){
            $group = Group::find($item->group_id);

            $item->group_name = $group->name;
        }

        return view('report.item', compact('items'));
    }

    public function item_value_report(Request $request) {

        if(  $request->has('item') ){
            $items = Item::where('user_id', Auth::user()->id)->where('name', $request->item)->get();
        } 
        else if( $request->has('from_date')  && $request->has('to_date') ){

            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));

            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));

            $items = Item::where("user_id", Auth::user()->id)->whereBetween( 'created_at', [ $from_date, $to_date ] )->get();
        }
        else {
            $items = Item::where("user_id", Auth::user()->id)->get();
        }

        // if( $request->has('value_type') ){

        //     if( $request->value_type == 'lifo' ){
        //         $order = 'desc';
        //     } else {
        //         $order = 'asc';
        //     }

        // } else {
        //     $order = 'asc';
        // }

        if( $request->has('value_type') ){

			$value_type = $request->value_type;

			$lifo = array_search('lifo', $value_type);
			$fifo = array_search('fifo', $value_type);

			if(!is_bool($fifo)){
				$fifo+=1;
			}

			if(!is_bool($lifo)){
				$lifo+=1;
			}

        } else {
            $fifo = 1;
            $lifo = 1;
        }

        foreach( $items as $item ) {
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

            foreach( $invoice_items as $sold_item ){
                array_push( $sold_items, $sold_item->item_qty );
                array_push( $sold_item_prices, $sold_item->item_price );
            }

            $sold_items_fifo = $sold_items;
            $sold_item_prices_fifo = $sold_item_prices;

            $sold_items_lifo = $sold_items;
            $sold_item_prices_lifo = $sold_item_prices;


            $purchase_fifo_items = Purchase::where('item_id', $item->id)->orderBy('bought_on', 'asc')->get();
            $purchase_lifo_items = Purchase::where('item_id', $item->id)->orderBy('bought_on', 'desc')->get();
            

            // if( $order == 'asc'){
                array_push( $purchased_items_fifo, $item->opening_stock );
                array_push( $purchased_item_prices_fifo, $item->opening_stock_rate );
            // }

            foreach( $purchase_fifo_items as $purchase_item ){
                array_push( $purchased_items_fifo, $purchase_item->qty );
                array_push( $purchased_item_prices_fifo, $purchase_item->price );
            }

            foreach( $purchase_lifo_items as $purchase_item ){
                array_push( $purchased_items_lifo, $purchase_item->qty );
                array_push( $purchased_item_prices_lifo, $purchase_item->price );
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


            if($fifo){
                for( $i=0; $i<$sold_fifo_count; $i++ ){
                    if( $sold_items_fifo[$i] == 0 )
                        continue;
    
                    for( $j=0; $j<$purchase_fifo_count; $j++ ){
    
    
                        if( $sold_items_fifo[$i]  == 0){
                            continue 2;
                        }
    
                        if($purchased_items_fifo[$j] == 0){
                            continue;
                        }
    
    
                        if( $sold_items_fifo[$i] > $purchased_items_fifo[$j] )
                        {
                            $gross_profit_fifo += ($purchased_items_fifo[$j] * $sold_item_prices_fifo[$i]) - ( $purchased_items_fifo[$j] * $purchased_item_prices_fifo[$j]);
    
    
                            $sold_items_fifo[$i] = $sold_items_fifo[$i] - $purchased_items_fifo[$j];
                            $purchased_items_fifo[$j] = 0;
    
                        }
    
                        if( $purchased_items_fifo[$j] >= $sold_items_fifo[$i] ){
    
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
    
    
                if( $request->has('price_type') ){
    
                    $price_type = $request->price_type;
    
                    $standard = array_search('standard', $price_type);
                    $average = array_search('average', $price_type);
                    $normal = array_search('normal', $price_type);
    
                    if (!is_bool($standard)) {
                        $standard += 1;
                    }
    
                    if (!is_bool($average)) {
                        $average += 1;
                    }
    
                    if (!is_bool($normal)) {
                        $normal += 1;
                    }
    
                    $sold_item_standard_totals_fifo = 0;
                    $sold_item_average_totals_fifo = 0;
                    $sold_item_standard_calculated_totals_fifo = 0;
                    $sold_item_average_calculated_totals_fifo = 0;
    
                    if( $standard ) {
                        $price = $request->price_value;
    
                        $standard_closing_value_fifo = array_sum($purchased_items_fifo) * $price;
    
                        for ($i = 0; $i < $sold_fifo_count; $i++) {
    
                            $sold_item_standard_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                            $sold_item_standard_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                        }
    
                        $gross_profit_standard_fifo = $sold_item_standard_totals_fifo - $sold_item_standard_calculated_totals_fifo;
                    }
                    
                    if ( $average ) {
                        if( count($purchased_item_prices_fifo) > 0){
                            $price = array_sum( $purchased_item_prices_fifo ) / count( $purchased_item_prices_fifo );
                            $average_closing_value_fifo = array_sum($purchased_items_fifo) * $price;
                        } else{
                            $price = 0;
                            $average_closing_value_fifo = 0;
                        }
    
                        for ($i = 0; $i < $sold_fifo_count; $i++) {
                            $sold_item_average_totals_fifo += ($sold_items_copy_fifo[$i] * $sold_item_prices_fifo[$i]);
                            $sold_item_average_calculated_totals_fifo += ($sold_items_copy_fifo[$i] * $price);
                        }
    
                        // echo 'sold_item_average_totals' . $sold_item_average_totals;
                        // echo "<br/>";
                        // echo 'sold_item_average_calculated_totals' . $sold_item_average_calculated_totals;
    
                        $gross_profit_average_fifo = $sold_item_average_totals_fifo - $sold_item_average_calculated_totals_fifo;
                    }
    
                }
    
                $item->value_amount_fifo = $total_value_fifo;
    
                $item->closing_value_fifo = $closing_value_fifo;
                $item->gross_profit_fifo = $gross_profit_fifo;
    
                if( isset($standard_closing_value_fifo) ){
                    $item->standard_closing_value_fifo = $standard_closing_value_fifo;
                } else {
                    $item->standard_closing_value_fifo = 'NA';
                }
    
                if( isset($gross_profit_standard_fifo) ){
                    $item->gross_profit_standard_fifo = $gross_profit_standard_fifo;
                } else {
                    $item->gross_profit_standard_fifo = 'NA';
                }
    
                if( isset($average_closing_value_fifo) ){
                    $item->average_closing_value_fifo = $average_closing_value_fifo;
                } else {
                    $item->average_closing_value_fifo = 'NA';
                }
    
                if( isset($gross_profit_average_fifo) ){
                    $item->gross_profit_average_fifo = $gross_profit_average_fifo;
                } else {
                    $item->gross_profit_average_fifo = 'NA';
                }
            }

            //----------------------------Lifo starts

            if($lifo){
                for( $i=0; $i<$sold_lifo_count; $i++ ){
                    if( $sold_items_lifo[$i] == 0 )
                        continue;
    
                    for( $j=0; $j<$purchase_lifo_count; $j++ ){
    
    
                        if( $sold_items_lifo[$i]  == 0){
                            continue 2;
                        }
    
                        if($purchased_items_lifo[$j] == 0){
                            continue;
                        }
    
    
                        if( $sold_items_lifo[$i] > $purchased_items_lifo[$j] )
                        {
                            $gross_profit_lifo += ($purchased_items_lifo[$j] * $sold_item_prices_lifo[$i]) - ( $purchased_items_lifo[$j] * $purchased_item_prices_lifo[$j]);
    
    
                            $sold_items_lifo[$i] = $sold_items_lifo[$i] - $purchased_items_lifo[$j];
                            $purchased_items_lifo[$j] = 0;
    
                        }
    
                        if( $purchased_items_lifo[$j] >= $sold_items_lifo[$i] ){
    
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
    
    
                if( $request->has('price_type') ){
    
                    $price_type = $request->price_type;
    
                    $standard = array_search('standard', $price_type);
                    $average = array_search('average', $price_type);
                    $normal = array_search('normal', $price_type);
    
                    if (!is_bool($standard)) {
                        $standard += 1;
                    }
    
                    if (!is_bool($average)) {
                        $average += 1;
                    }
    
                    if (!is_bool($normal)) {
                        $normal += 1;
                    }
    
                    $sold_item_standard_totals_lifo = 0;
                    $sold_item_average_totals_lifo = 0;
                    $sold_item_standard_calculated_totals_lifo = 0;
                    $sold_item_average_calculated_totals_lifo = 0;
    
                    if( $standard ) {
                        $price = $request->price_value;
    
                        $standard_closing_value_lifo = array_sum($purchased_items_lifo) * $price;
    
                        for ($i = 0; $i < $sold_lifo_count; $i++) {
    
                            $sold_item_standard_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                            $sold_item_standard_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                        }
    
                        $gross_profit_standard_lifo = $sold_item_standard_totals_lifo - $sold_item_standard_calculated_totals_lifo;
                    }
                    
                    if ( $average ) {
                        if( count($purchased_item_prices_lifo) > 0){
                            $price = array_sum( $purchased_item_prices_lifo ) / count( $purchased_item_prices_lifo );
                            $average_closing_value_lifo = array_sum($purchased_items_lifo) * $price;
                        } else{
                            $price = 0;
                            $average_closing_value_lifo = 0;
                        }
    
                        for ($i = 0; $i < $sold_lifo_count; $i++) {
                            $sold_item_average_totals_lifo += ($sold_items_copy_lifo[$i] * $sold_item_prices_lifo[$i]);
                            $sold_item_average_calculated_totals_lifo += ($sold_items_copy_lifo[$i] * $price);
                        }
    
                        // echo 'sold_item_average_totals' . $sold_item_average_totals;
                        // echo "<br/>";
                        // echo 'sold_item_average_calculated_totals' . $sold_item_average_calculated_totals;
    
                        $gross_profit_average_lifo = $sold_item_average_totals_lifo - $sold_item_average_calculated_totals_lifo;
                    }
    
                }
    
                $item->value_amount_lifo = $total_value_lifo;
    
                $item->closing_value_lifo = $closing_value_lifo;
                $item->gross_profit_lifo = $gross_profit_lifo;
    
                if( isset($standard_closing_value_lifo) ){
                    $item->standard_closing_value_lifo = $standard_closing_value_lifo;
                } else {
                    $item->standard_closing_value_lifo = 'NA';
                }
    
                if( isset($gross_profit_standard_lifo) ){
                    $item->gross_profit_standard_lifo = $gross_profit_standard_lifo;
                } else {
                    $item->gross_profit_standard_lifo = 'NA';
                }
    
                if( isset($average_closing_value_lifo) ){
                    $item->average_closing_value_lifo = $average_closing_value_lifo;
                } else {
                    $item->average_closing_value_lifo = 'NA';
                }
    
                if( isset($gross_profit_average_lifo) ){
                    $item->gross_profit_average_lifo = $gross_profit_average_lifo;
                } else {
                    $item->gross_profit_average_lifo = 'NA';
                }
            }

            //-----------------------------Lifo ends

            // $item->value_amount += $item->opening_stock_value;
            
        }

        
        // die();
        return view('report.item_value', compact('items'));

    }

    // public function item_value_report(Request $request) {

    //     if(  $request->has('item') ){
    //         $items = Item::where('user_id', Auth::user()->id)->where('name', $request->item)->get();
    //     } 
    //     else if( $request->has('from_date')  && $request->has('to_date') ){

    //         $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));

    //         $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));

    //         $items = Item::where("user_id", Auth::user()->id)->whereBetween( 'created_at', [ $from_date, $to_date ] )->get();
    //     }
    //     else {
    //         $items = Item::where("user_id", Auth::user()->id)->get();
    //     }

    //     // dd($items);

    //     if( $request->has('value_type') ){

    //         if( $request->value_type == 'lifo' ){
    //             $order = 'desc';
    //         } else {
    //             $order = 'asc';
    //         }

    //     } else {
    //         $order = 'asc';
    //     }

    //     foreach( $items as $item ) {
    //         $sold_items = array();
    //         $sold_item_prices = array();

    //         $purchased_items = array();
    //         $purchased_item_prices = array();

    //         $invoice_items = Invoice_Item::where("item_id", $item->id)->get();

    //         foreach( $invoice_items as $sold_item ){
    //             array_push( $sold_items, $sold_item->item_qty );
    //             array_push( $sold_item_prices, $sold_item->item_price );
    //         }

    //         $purchase_items = Purchase::where('item_id', $item->id)->orderBy('bought_on', $order)->get();

    //         foreach( $purchase_items as $purchase_item ){
    //             array_push( $purchased_items, $purchase_item->qty );
    //             array_push( $purchased_item_prices, $purchase_item->price );
    //         }

    //         $sold_count = count($sold_items);
    //         $purchase_count = count($purchased_items);
    //         $total = 0;

    //         // print_r($sold_items);
    //         // print_r($sold_item_prices);
    //         // print_r($purchased_items);
    //         // print_r($purchased_item_prices);

    //         // echo "sold".$sold_count. "<br/>";
    //         // echo "purchase".$purchase_count;

    //         // echo "<pre>";
    //         // echo "Sold Qty ";
    //         // print_r($sold_items);
    //         // echo "Sold Price ";
    //         // print_r($sold_item_prices);
    //         // echo "Purchased Qty ";
    //         // print_r($purchased_items);
    //         // echo "Purchased Price ";
    //         // print_r($purchased_item_prices);
    //         // echo "Sold Qty: " . array_sum($sold_items);
    //         // echo "<br/>";
    //         // echo "Purchased Qty: " . array_sum($purchased_items);
    //         // echo "</pre>";

    //         for( $i=0; $i<$sold_count; $i++ ){
    //             if( $sold_items[$i] == 0 )
    //                 continue;

    //             for( $j=0; $j<$purchase_count; $j++ ){

    //                 // echo "S = " . $sold_items[$i] . "<br/>";
    //                 // echo "P = " . $purchased_items[$j] . "<br/>";

    //                 if( $sold_items[$i]  == 0){
    //                     continue 2;
    //                 }

    //                 if($purchased_items[$j] == 0){
    //                     continue;
    //                 }


    //                 if( $sold_items[$i] > $purchased_items[$j] )
    //                 {
    //                     $s1 = $purchased_items[$j];

    //                     $value = ($s1 * $sold_item_prices[$i]) - ($s1 * $purchased_item_prices[$j]);

    //                     // echo $s1 . " x " . $sold_item_prices[$i] . " - " . $s1 . " x " . $purchased_item_prices[$j] . " = " .$value . "<br/>";

    //                     $total += $value;

    //                     $sold_items[$i] = $sold_items[$i] - $purchased_items[$j];
    //                     $purchased_items[$j] = 0;

    //                 }

    //                 if( $purchased_items[$j] >= $sold_items[$i] ){
                        
    //                     $value = ( $sold_items[$i] * $sold_item_prices[$i] ) - ( $sold_items[$i] * $purchased_item_prices[$j] );

    //                     // echo $sold_items[$i] . " x " . $sold_item_prices[$i] . " - " . $sold_items[$i] . " x " . $purchased_item_prices[$j] . " = " . $value . "<br/>";

    //                     $total += $value;

    //                     $purchased_items[$j] = $purchased_items[$j] - $sold_items[$i];
    //                     $sold_items[$i] = 0;
    //                 }

    //                 // echo "Inner " . $total . "<br/>";
    //             }
    //         }

    //         // $item->total_value = 0;

    //         // for( $i=0; $i<$purchase_count; $i++ ){
                
    //         //     if( isset( $request->price_type ) ){

    //         //         if(  $request->price_type == 'standard' ) {
    //         //             $price = $request->price_value;
    //         //         } else if ( $request->price_type = 'average' ) {
    //         //             $price = array_sum( $purchased_item_prices ) / count( $purchased_item_prices );
    //         //         }

    //         //     } else {

    //         //         $price = $purchased_item_prices[$i];

    //         //         if( $sold_item_prices[$i] > $purchased_item_prices[$i] ){
    //         //             $price = $purchased_item_prices[$j];
    //         //         } else {
    //         //             $price = $sold_item_prices[$i];
    //         //         }

    //         //     }

    //         // }

    //         // echo "<pre>";
    //         // echo "Sold Qty ";
    //         // print_r($sold_items);
    //         // echo "Sold Price ";
    //         // print_r($sold_item_prices);
    //         // echo "Purchased Qty ";
    //         // print_r($purchased_items);
    //         // echo "Purchased Price ";
    //         // print_r($purchased_item_prices);
    //         // echo "Sold Qty: " . array_sum($sold_items);
    //         // echo "<br/>";
    //         // echo "Purchased Qty: " . array_sum($purchased_items);
    //         // echo "</pre>";
    //         $total_value = 0;


    //         if( $request->has('price_type') ){

    //             if(  $request->price_type == 'standard' ) {
    //                 $price = $request->price_value;
    //             } else if ( $request->price_type = 'average' ) {
    //                 if( count($purchased_item_prices) > 0){
    //                     $price = array_sum( $purchased_item_prices ) / count( $purchased_item_prices );
    //                 }
    //             }

    //             for( $i=0; $i<$purchase_count; $i++ ){

    //                 $total_value += $purchased_items[$i] * $price;
    //             }

    //         } else {

    //             for( $i=0; $i<$purchase_count; $i++ ){
    //                 $all_zero = true;
                    
    //                 if( $purchased_items[$i] == 0 ){
    //                     continue;
    //                 }

    //                 for($j=0; $j<$sold_count; $j++ ){
    //                     if( $sold_items[$j] == 0 ){
    //                         continue;
    //                     } else {
    //                         $all_zero = false;
    //                     }
        
    //                     // echo 'purchase item ' . $purchased_items[$i] . 'sold item ' . $sold_item_prices[$j];

    //                     if( $sold_item_prices[$j] > $purchased_item_prices[$i] ){
    //                         $total_value += $purchased_items[$i] * $purchased_item_prices[$i];
    //                     } else if( $sold_item_prices[$j] < $purchased_item_prices[$i] ) {
    //                         $total_value += $purchased_items[$i] * $sold_item_prices[$j];
    //                     } else {
    //                         $total_value += $purchased_items[$i] * $purchased_item_prices[$i];
    //                     }
    //                 }

    //                 if( $all_zero ){
    //                     $total_value += $purchased_items[$i] * $purchased_item_prices[$i];
    //                 }

    //             }

    //         }

    //         $item->value_amount = $total_value;
            
    //     }

        
    //     // die();
    //     return view('report.item_value', compact('items'));

    // }



    public function get_import_to_table()
    {
        return view('item.import_to_table');
    }
    
    
    public function post_import_to_table(Request $request)
    {

        $this->validate($request, [
            'inventory_file' => 'required'
        ]);


        if ($request->hasFile('inventory_file')) {

            $path = $request->file('inventory_file')->getRealPath();

            $data = Excel::load($path)->get();

            if (!empty($data) && $data->count()) {
                foreach ($data->toArray() as $row) {
                    if (!empty($row)) {
                        $dataArray[] = [
                            'type' => $row['type'],
                            'name' => $row['name'],
                            'category' => $row['category'],
                            'qty' => $row['qty'],
                            'hsc_code' => $row['hsc_code'],
                            'sac_code' => $row['sac_code'],
                            'measuring_unit' => $row['measuring_unit'],
                            'gst' => $row['gst'],
                            'cess' => $row['cess'],
                            'manufacture' => $row['manufacture'],
                            'expiry' => $row['expiry'],
                            'batch' => $row['batch'],
                            'size' => $row['size'],
                            'item_under_rcm' => $row['item_under_rcm'],
                            'opening_stock' => $row['opening_stock'],
                            'opening_stock_date' => $row['opening_stock_date'],
                            'opening_stock_rate' => $row['opening_stock_rate'],
                            'opening_stock_value' => $row['opening_stock_value'],
                            'group_id' => $row['group_id'],
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s', time()),
                            'updated_at' => date('Y-m-d H:i:s', time()),
                        ];
                    }
                }
                if (!empty($dataArray)) {
                    Item::insert($dataArray);
                    return redirect()->back()->with('success', 'Data uploaded successfully');
                }
            }
        }
    }

    public function view_all_manage_inventory()
    {
        $inventories = User::find(Auth::user()->id)->managedInventories()->orderBy('value_updated_on', 'desc')->get();

        return view('item.view_manage_inventory', compact('inventories'));
    }

    public function edit_manage_inventory($id)
    {
        $managed_inventory = ManagedInventory::findOrFail($id);
        $items = Item::where("user_id", Auth::user()->id)->get();
        $units = MeasuringUnit::all();
        return view('item.edit_manage_inventory', compact('managed_inventory', 'items', 'units'));
    }

    public function update_manage_inventory(Request $request, $id)
    {
        $managed_inventory = ManagedInventory::findOrFail($id);
        $item = Item::findOrFail($request->item);

        if ($item->alternate_measuring_unit != null && $item->alternate_measuring_unit == $request->measuring_unit) {
            if($request->has('count')){
                $qty_count = $item->conversion_of_alternate_to_base_unit_value * $request->count;
            } else {
                $qty_count = 0;
            }
            
            if($request->has('rate')){
                $rate = $request->rate * $item->conversion_of_compound_to_alternate_unit_value;;
            } else {
                $rate = 0;
            }
        }
        else if ($item->compound_measuring_unit != null && $item->compound_measuring_unit == $request->measuring_unit) {
            if($request->has('count')){
                $qty_count = $item->conversion_of_alternate_to_base_unit_value * $item->conversion_of_compound_to_alternate_unit_value * $request->count;
            } else {
                $qty_count = 0;
            }
            
            if($request->has('rate')){
                $rate = $request->rate * ($item->conversion_of_compound_to_alternate_unit_value * $item->conversion_of_alternate_to_base_unit_value);                
            } else {
                $rate = 0;
            }
        }
        else if ($item->measuring_unit != null && $item->measuring_unit == $request->measuring_unit) {
            if($request->has('count')){
                $qty_count = $request->count;
            } else {
                $qty_count = 0;
            }
            
            if($request->has('rate')){
                $rate = $request->rate;
            } else {
                $rate = 0;
            }
        }
        //this(down) case shall not occur 
        else {
            $qty_count = 0;
            $rate = 0;
        }

        $managed_inventory->item_id = $request->item;
        $managed_inventory->qty = $qty_count;
        $managed_inventory->measuring_unit = $request->measuring_unit;
        $managed_inventory->rate = $rate;
        $managed_inventory->value = $request->value;
        $managed_inventory->value_updated_on = Carbon::createFromFormat('d/m/Y', $request->update_date)->format('Y-m-d');
        $managed_inventory->reason = $request->reason;
        $managed_inventory->value_updation_note = $request->note;

        if($managed_inventory->save()){
            return redirect()->back()->with('success', 'Physical stock updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update physical stock');
        }
    }

    public function delete_manage_inventory($id)
    {
        $managed_inventory = ManagedInventory::findOrFail($id);
        if($managed_inventory->delete()){
            return redirect()->back()->with('success', 'Physical stock deleted successfully!');
        } else {
            return redirect()->back()->with('failure', 'Failed to delete physical stock!');
        }
    }

    public function single_item_report(Request $request, $id)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $user = User::findOrFail(auth()->user()->id);

        $foundItem = Item::findOrFail($id);
        $purchase_items = $user->purchaseItems()->where('item_id', $id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on')->get();
        $debit_notes = $user->debitNotes()->where('item_id', $id)->where('debit_notes.type', 'purchase')->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->orderBy('debit_notes.note_date')->get();
        
        $invoice_items = $user->invoiceItems()->where('item_id', $id)->whereBetween('sold_on', [$from_date, $to_date])->orderBy('sold_on')->get();
        $credit_notes = $user->creditNotes()->where('item_id', $id)->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->orderBy('credit_notes.note_date')->get();
        
        $managed_inventories = auth()->user()->profile->inventory_type != "without_inventory" ? $user->managedInventories()->where('item_id', $id)->whereBetween('value_updated_on', [$from_date, $to_date])->orderBy('value_updated_on')->get() : [];

        $combined_array = array();
        $opening_balance_array = array();
        $invoice_item_array = array();
        $purchase_item_array = array();
        $credit_note_array = array();
        $debit_note_array = array();
        $managed_inventory_array = array();


        if($foundItem) {
            $opening_balance_array[] = [
                'date' => Carbon::parse($foundItem->opening_stock_date)->format('Y-m-d'),
                'particulars' => 'Opening Balance',
                'voucher_type' => '',
                'voucher_no' => '',
                'rate' => $foundItem->opening_stock_rate,
                'quantity' => $foundItem->opening_stock,
                'value' => $foundItem->opening_stock_value,
                'type' => 'opening_balance'
            ];
        }

        foreach($invoice_items as $item){

            $foundItem = Item::findOrFail($item->item_id);

            // default for base unit
            // if($item->qty_type == $foundItem->measuring_unit) {
                $rate = $item->item_price;
            // }

            if($item->qty_type == "alternate") {
                $single_alt_value = $foundItem->conversion_of_alternate_to_base_unit_value / $foundItem->alternate_unit_input;
                $calculated_value = $foundItem->original_opening_stock * $single_alt_value;
                $rate = $item->item_total / $calculated_value;
            }

            if($item->qty_type == "compound") {
                $single_alt_value = $foundItem->conversion_of_alternate_to_base_unit_value / $foundItem->alternate_unit_input;

                $single_comp_value = $foundItem->conversion_of_compound_to_alternate_unit_value / $foundItem->compound_unit_input;

                $calculated_value = $foundItem->original_opening_stock * $single_alt_value * $single_comp_value;

                $rate = $item->item_total / $calculated_value;
            }

            $invoice_item_array[] = [
                'date' => Carbon::parse($item->invoice->invoice_date)->format('Y-m-d'),
                'particulars' => $item->party->name,
                'voucher_type' => 'Sale',
                'voucher_no' => $item->invoice->invoice_prefix.$item->invoice->invoice_no.$item->invoice->invoice_suffix,
                'rate' => $rate,
                'quantity' => $item->item_qty + $item->free_qty,
                'value' => $item->item_total,
                'type' => 'sale'
            ];

            // $invoice_item_array[] = [
            //     'date' => Carbon::parse($item->invoice->invoice_date)->format('Y-m-d'),
            //     'particulars' => $item->party->name,
            //     'voucher_type' => 'Sale (Free Qty)',
            //     'voucher_no' => $item->invoice->invoice_prefix.$item->invoice->invoice_no.$item->invoice->invoice_suffix,
            //     'quantity' => $item->free_qty,
            //     'value' => $item->item_total,
            //     'type' => 'sale'
            // ];
        }

        foreach($purchase_items as $item){ 

            $foundItem = Item::findOrFail($item->item_id);

            // default for base unit
            // if($item->qty_type == $foundItem->measuring_unit) {
                $rate = $item->item_price;
            // }

            if($item->qty_type == "alternate") {
                $single_alt_value = $foundItem->conversion_of_alternate_to_base_unit_value / $foundItem->alternate_unit_input;
                $calculated_value = $foundItem->original_opening_stock * $single_alt_value;
                $rate = $item->item_total / $calculated_value;
            }

            if($item->qty_type == "compound") {

                $single_alt_value = $foundItem->conversion_of_alternate_to_base_unit_value / $foundItem->alternate_unit_input;

                $single_comp_value = $foundItem->conversion_of_compound_to_alternate_unit_value / $foundItem->compound_unit_input;

                $calculated_value = $foundItem->original_opening_stock * $single_alt_value * $single_comp_value;

                $rate = $item->item_total / $calculated_value;
            }

            $purchase_item_array[] = [
                'date' => Carbon::parse($item->purchase->bill_date)->format('Y-m-d'),
                'particulars' => $item->party->name,
                'voucher_type' => 'Purchase',
                'voucher_no' => $item->purchase->bill_no,
                'rate' => $rate,
                'quantity' => $item->qty + $item->free_qty,
                'value' => $item->item_total,
                'type' => 'purchase'
            ];

            // $purchase_item_array[] = [
            //     'date' => Carbon::parse($item->purchase->bill_date)->format('Y-m-d'),
            //     'particulars' => $item->party->name,
            //     'voucher_type' => 'Purchase (Free Qty)',
            //     'voucher_no' => $item->purchase->bill_no,
            //     'quantity' => $item->free_qty,
            //     'value' => $item->item_total,
            //     'type' => 'purchase'
            // ];
        }

        foreach($credit_notes as $note){
            $credit_note_array[] = [
                'date' => Carbon::parse($note->note_date)->format('Y-m-d'),
                'particulars' => '',
                'voucher_type' => 'Credit Note',
                'voucher_no' => $note->note_no,
                'rate' => $note->price,
                'quantity' => $note->quantity,
                'value' => $note->quantity == 0 ? $note->price : $note->quantity * $note->price,
                'type' => 'credit_note'
            ];
        }

        foreach($debit_notes as $note) {
            $debit_note_array[] = [
                'date' => Carbon::parse($note->note_date)->format('Y-m-d'),
                'particulars' => '',
                'voucher_type' => 'Debit Note',
                'voucher_no' => $note->note_no,
                'rate' => $note->price,
                'quantity' => $note->quantity,
                'value' => $note->quantity == 0 ? $note->price : $note->quantity * $note->price,
                'type' => 'debit_note'
            ];
        }

        foreach($managed_inventories as $inventory) {
            $managed_inventory_array[] = [
                'date' => Carbon::parse($inventory->value_updated_on)->format('Y-m-d'),
                'particulars' => '',
                'voucher_type' => 'Managed Inventory',
                'voucher_no' => '',
                'rate' => '',
                'quantity' => $inventory->qty,
                'value' => $inventory->qty * $inventory->rate,
                'type' => 'managed_inventory'
            ];
        }

        $combined_array = array_merge(
            $invoice_item_array,
            $purchase_item_array,
            $credit_note_array,
            $debit_note_array,
            $managed_inventory_array
        );

        $this->array_sort_by_column($combined_array, 'date');

        $combined_array = array_merge(
            $opening_balance_array,
            $combined_array
        );

        // return $combined_array;

        // return $foundItem;

        return view('report.single_item_report', ['rows' => $combined_array, 'item' => $foundItem]);
    }

    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    public function generate_stock_summary(Request $request, $id)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
            $previous_from_date = date("Y-m-d",strtotime ( '-1 year' , strtotime ( $from_date ) )) ;
            $previous_to_date = date("Y-m-d", strtotime($to_date));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }
        
        $item = Item::findOrFail($id);
        $data = $this->calculate_stock_summary_and_return($request, $item->id, $from_date, $to_date);
        return view('report.single_item_stock_summary', compact('data'));
    }

    public function generate_stock_summary_detail(Request $request, $id)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }
        $item = Item::findOrFail($id);
        $data = $this->calculate_stock_summary_and_return($request, $item->id, $from_date, $to_date);
        return view('report.single_item_stock_summary_detail', compact('data'));
    }

    private function calculate_stock_item_opening_balance($item, $from_date, $to_date)
    {
        // dd($from_date, $to_date);
        // $from_date = date('Y-m-d', strtotime(str_replace('/', '-', '01/04/2022')));
        // $to_date = date('Y-m-d', strtotime(str_replace('/', '-', '31/03/2023')));

        $id = $item->id;
        $fifo = true;
        $purchase_items = auth()->user()->purchaseItems()->where('item_id', $id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on')->get();
        $invoice_items = auth()->user()->invoiceItems()->where('item_id', $id)->whereBetween('sold_on', [$from_date, $to_date])->orderBy('sold_on')->get();
        $credit_notes = auth()->user()->creditNotes()->where('item_id', $id)->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->orderBy('credit_notes.note_date')->get();
        $debit_notes = auth()->user()->debitNotes()->where('item_id', $id)->where('debit_notes.type', 'purchase')->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->orderBy('debit_notes.note_date')->get();
        // $physical_stock = auth()->user()->managedInventories()->where('item_id', $id)->orderBy('created_at')->get();


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
            //dd($item);
            $purchase = PurchaseRecord::findOrFail($item->purchase_id);
            $foundItem = Item::findOrFail($item->item_id);
            
            // dd($purchase);
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
           //dd($item);
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
        // dd($combined_array);
        $data = [];
        foreach($combined_array as $row) {
            if($row['transaction_type'] == 'receipt') {
                // dd($row);
                //pushing data (bought item to stock) at the end in the array
                array_push($balance_qty_array, $row['qty']);
                // dd($row['qty']);
                array_push($balance_rate_array, $row['rate']);
                array_push($balance_amount_array, $row['amount']);
            }
            //dd($balance_qty_array);

            if($row['transaction_type'] == 'issued') {
                $qty = $row['req_qty'];
                // dd($qty);
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
                // dd($qty_array);
                $row['rate'] = $rate_array;
                $row['amount'] = $amount_array;
            }
           // dd($balance_qty_array);
            $row['balance'] = ['qty' => $balance_qty_array, 'rate' => $balance_rate_array, 'amount' => $balance_amount_array];
            $data[] = $row;
        // dd($row);
        }
        
        $opening_value = 0;
        $opening_qty = 0;
        $opening_rate = 0;

        // foreach($data as $row){
        //     if($row['transaction_type'] == 'receipt' || $row['transaction_type']=='issued'){
        //         $opening_qty += ($row['qty'] + $row['req_qty']);
        //     }
        // }
        
        // dd($row['qty']);
        foreach($data as $row) {
            if($row['transaction_type'] == 'receipt') {
                $opening_qty += $row['qty'];
                // $opening_qty = $item->qty;
                $opening_value += $row['amount'];  
            }
            if($row['transaction_type'] == 'issued'){
                 //dd($opening_qty);
                foreach($row['qty'] as $qty) {
                    $opening_qty -= $qty; 
                   // dd($qty);
                }
                foreach($row['amount'] as $amount){
                    $opening_value -= $amount;
                    // dd($opening_value);
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

        // dd($opening_qty);
        return ['qty' => $opening_qty, 'rate' => $opening_rate, 'value' => $opening_value, 'date' => $to_date];
    }

    private function calculate_stock_summary_and_return($request, $id, $from_date, $to_date)
    {
        $fifo = isset($request->type) && $request->type == "lifo" ? false : true;
        $current_item = Item::findOrFail($id);
        
        $purchase_items = auth()->user()->purchaseItems()->where('item_id', $id)->whereBetween('bought_on', [$from_date, $to_date])->orderBy('bought_on')->get();
        $invoice_items = auth()->user()->invoiceItems()->where('item_id', $id)->whereBetween('sold_on', [$from_date, $to_date])->orderBy('sold_on')->get();
        $credit_notes = auth()->user()->creditNotes()->where('item_id', $id)->where('credit_notes.type', 'sale')->where('credit_notes.reason', 'sale_return')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->orderBy('credit_notes.note_date')->get();
        $debit_notes = auth()->user()->debitNotes()->where('item_id', $id)->where('debit_notes.type', 'purchase')->where('debit_notes.reason', 'purchase_return')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->orderBy('debit_notes.note_date')->get();
        // $physical_stock = auth()->user()->managedInventories()->where('item_id', $id)->orderBy('created_at')->get();

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
        // dd($current_item);
        $closing_balance_from_date = auth()->user()->profile->book_beginning_from;
        $closing_balance_to_date = \Carbon\Carbon::parse($from_date)->format('Y-m-d');
        //dd( $closing_balance_from_date, $closing_balance_to_date);
        $opening_stock = $this->calculate_stock_item_opening_balance($current_item, $closing_balance_from_date, $closing_balance_to_date);
    //    dd($opening_stock);
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
        //dd($opening_stock);

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
        // dd($item->qty);
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

    public function export_as_excel()
    {
        $itemArray = Item::where('user_id', auth()->user()->id)->get()->toArray();

        Excel::create('item', function ($excel) use ($itemArray) {
            $excel->sheet('Item List', function ($sheet) use ($itemArray) {
                $sheet->fromArray($itemArray);
            });
        })->export('xlsx');
    }

}
