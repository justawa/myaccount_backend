<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use Auth;

use App\DutiesAndTaxes;
use App\Group;
use App\Item;
use App\Party;
use App\Purchase;
use App\PurchaseRecord;
use App\Purchase_Item;
use App\PurchaseLog;
use App\User;

class PurchaseAjaxController extends Controller
{
    public function add_item_extra_info(Request $request){

        // return $request->all();
        session(["item_extra" . $request->item_id => ['manufacture' => $request->manufacture, 'expiry' => $request->expiry, 'batch' => $request->batch, 'item_id' => $request->item_id, 'size' => $request->size, 'pieces' => $request->pieces]]);

        if(Session::has("item_extra" . $request->item_id)){
            echo "success";

            // $manufacture = session("item_extra" . $request->item_id . '.manufacture');
            // $expiry = session("item_extra" . $request->item_id . '.expiry');
            // $batch = session("item_extra" . $request->item_id . '.batch');

            // echo $manufacture . $batch . $expiry;
        } 
        else {
            echo "failure";
        }
        
    }

    public function find_item_extra_info(Request $request){

        // return $request->all();

        // print_r(Session::get("item_exta"));
        
        if ( Session::has("item_extra" . $request->item_id) ) {
            return response()->json( Session::get("item_exta" . $request->item_id) );
        } else {
            echo "failure";
        }

    }

    public function remove_all_extra_item_data(Request $request){
        if(Session::has("item_extra" . $request->item_id) && Session::has("item_cess." . $request->item_id)){
            Session::forget("item_extra" . $request->item_id);
            Session::forget("item_cess." . $request->item_id);

            echo "success";
        }
        else if (Session::has("item_extra" . $request->item_id)) {
            Session::forget("item_extra" . $request->item_id);

            echo "success";
        }
        else if (Session::has("item_cess." . $request->item_id)) {
            Session::forget("item_cess." . $request->item_id);

            echo "success";
        }
        else {
            echo "failure";
        }

    }


    public function add_short_rate_info(Request $request){
        
        session(["short_rate" . $request->item_id => ['gross_rate' => $request->gross_rate, 'short_rate' => $request->short_rate, 'net_rate' => $request->net_rate, 'item_id' => $request->item_id]]);

        if (Session::has("short_rate" . $request->item_id)) {
            echo "success";
            // echo json_encode(session("short_rate" . $request->item_id));
        } else {
            echo "failure";
        }

    }

    public function add_additional_charges(Request $request){

        session(["additional_charges" => ['labour_charge' => $request->labour_charge, 'transport_charge' => $request->transport_charge, 'insurance_charge' => $request->insurance_charge, 'gst_charged' => $request->gst_charged]]);

        if (Session::has("additional_charges")) {
            echo "success";
        } else {
            echo "failure";
        }

    }


    public function add_transporter_details(Request $request) {
        
        session(["transporter_details" => ['transporter_id' => $request->transporter_id, 'vehicle_type' => $request->vehicle_type, 'vehicle_number' => $request->vehicle_number, 'delivery_date' => $request->delivery_date]]);

        if (Session::has("transporter_details")) {
            echo "success";
        } else {
            echo "failure";
        }

    }


    public function add_item_cess(Request $request) {

        if ($request->cess_amount != '') {

            session(["item_cess." . $request->item_id => ['cess_amount' => $request->cess_amount, 'item_id' => $request->item_id]]);

            if( Session::has("item_cess." . $request->item_id) ) {
                echo "success";
            } else {
                echo "failure";
            }
        } else {
            echo "failure";
        }
    }

    
    public function search_item_by_keyword(Request $request) {
        $item = Item::where('name', 'like', $request->key_to_search . '%')->where('user_id', Auth::user()->id)->get();

        return response()->json($item);
    }


    public function check_bill_date_validation(Request $request)
    {
        $bill_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->bill_date)));

        if (auth()->user()->profile->financial_year_from > $bill_date) {
            return response()->json(['success' => false, 'message' => 'Bill date should be between current financial year']);
        }

        if (auth()->user()->profile->financial_year_to < $bill_date) {
            return response()->json(['success' => false, 'message' => 'Bill date should be between current financial year']);
        }

        return response()->json(['success' => true]);
    }

    
}
