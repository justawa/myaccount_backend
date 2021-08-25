<?php

namespace App\Http\Controllers;

use Auth;
use Config;
use DB;
use Illuminate\Http\Request;
use App\EwaybillApi\EwaybillApi as Ewayapi;

use App\AdditionalCharge;
use App\EwaybillDetail;
use App\Ewaybill;
use App\Invoice;
use App\TransporterDetail;
use App\User;

class EwaybillController extends Controller
{

    private $gstin, $username, $ewbpwd;
    
    public function create()
    {
        $invoices = User::findOrFail(auth()->user()->id)->invoices()->get();
        $transporters = User::findOrFail(Auth::user()->id)->transporters()->get();

        return view('ewaybill.create', compact('invoices', 'transporters'));
    }

    public function save_invoice_transport_detail(Request $request)
    {

        $this->validate($request, [
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

        $invoice = Invoice::findOrFail($request->invoice_id);

        // $transporter_detail = new TransporterDetail;

        // $transporter_detail->invoice_id = $request->invoice_id;
        // $transporter_detail->transporter = $request->transporter;
        // $transporter_detail->transporter_name = $request->transporter_name;
        // $transporter_detail->transporter_doc_no = $request->transporter_doc_no;
        // $transporter_detail->transport_doc_date = $request->transport_doc_date;
        // $transporter_detail->transport_mode = $request->transport_mode;
        // $transporter_detail->transport_distance = $request->transport_distance;
        // $transporter_detail->vehicle_type = $request->vehicle_type;
        // $transporter_detail->vehicle_number = $request->vehicle_number;
        // $transporter_detail->delivery_date = $request->delivery_date;
        

        // if($transporter_detail->save()){
        //     return redirect()->back()->with('success', 'Details added successfully');
        // } else {
        //     return redirect()->back()->with('failure', 'Failed to add data');
        // }

        // $client = new \GuzzleHttp\Client();
        // $accesstoken_endpoint = Config::get('ewaybill.urls.ACCESSTOKEN.test');
        // $accesstoken_action = Config::get('ewaybill.actions.ACCESSTOKEN', 'ACCESSTOKEN');
        // $aspid = Config::get('ewaybill.private.aspid');
        // $password = Config::get('ewaybill.private.password');
        // $gstin = Config::get('ewaybill.private.gstin');
        // $username = Config::get('ewaybill.private.username');
        // $ewbpwd = Config::get('ewaybill.private.ewbpwd');

        // $response_accessToken = $client->request('GET', $accesstoken_endpoint, ['query' => [
        //         'action' => $accesstoken_action, 
        //         'aspid' => $aspid,
        //         'password' => $password,
        //         'gstin' => $gstin,
        //         'username' => $username,
        //         'ewbpwd' => $ewbpwd
        //     ]
        // ]);

        // // url will be: http://my.domain.com/test.php?key1=5&key2=ABC;

        // $statusCode = $response_accessToken->getStatusCode();
        // $content = $response_accessToken->getBody();
    
        // $content = json_decode($content, true);

        // $geneway_endpoint = Config::get('ewaybill.urls.GENEWAYBILL.test');
        // $geneway_action = Config::get('ewaybill.actions.GENEWAYBILL', 'GENEWAYBILL');
        // $auth_token = $content['authtoken'];

        // $dataArray = [
        //     "supplyType" => "O",
        //     "subSupplyType" => "1",
        //     "subSupplyDesc" => " ",
        //     "docType" => "INV",
        //     "docNo" => "888-8",
        //     "docDate" => "15/12/2017",
        //     "fromGstin" => "05AAACG1625Q1ZK",
        //     "fromTrdName" => "welton",
        //     "fromAddr1" => "2ND CROSS NO 59  19  A",
        //     "fromAddr2" => "GROUND FLOOR OSBORNE ROAD",
        //     "fromPlace" => "FRAZER TOWN",
        //     "fromPincode" => 263652,
        //     "actFromStateCode" => 05,
        //     "fromStateCode" => 05,
        //     "toGstin" => "02EHFPS5910D2Z0",
        //     "toTrdName" => "sthuthya",
        //     "toAddr1" => "Shree Nilaya",
        //     "toAddr2" => "Dasarahosahalli",
        //     "toPlace" => "Beml Nagar",
        //     "toPincode" => 110024,
        //     "actToStateCode" => 07,
        //     "toStateCode" => 07,
        //     "transactionType" => 4,
        //     "dispatchFromGSTIN" => "29AAAAA1303P1ZV",
        //     "dispatchFromTradeName" => "ABC Traders",
        //     "shipToGSTIN" => "29ALSPR1722R1Z3",
        //     "shipToTradeName" => "XYZ Traders",
        //     "otherValue" => -100,
        //     "totalValue" => 56099,
        //     "cgstValue" => 0,
        //     "sgstValue" => 0,
        //     "igstValue" => 300.67,
        //     "cessValue" => 400.56,
        //     "cessNonAdvolValue" => 400,
        //     "totInvValue" => 68358,
        //     "transporterId" => "",
        //     "transporterName" => "",
        //     "transDocNo" => "",
        //     "transMode" => "1",
        //     "transDistance" => "66",
        //     "transDocDate" => "",
        //     "vehicleNo" => "PVC1234",
        //     "vehicleType" => "R",
        //     "itemList" => [
        //         [
        //             "productName" => "Wheat",
        //             "productDesc" => "Wheat",
        //             "hsnCode" => 1001,
        //             "quantity" => 4,
        //             "qtyUnit" => "BOX",
        //             "cgstRate" => 0,
        //             "sgstRate" => 0,
        //             "igstRate" => 3,
        //             "cessRate" => 0,
        //             "cessNonAdvol" => 0,
        //             "taxableAmount" => 56099
        //         ]
        //     ]
        // ];

        // $response_genEwayBill = $client->request('POST', $geneway_endpoint, [
        //     'query' =>  [
        //         'action' => $geneway_action, 
        //         'aspid' => $aspid,
        //         'password' => $password,
        //         'gstin' => $gstin,
        //         'username' => $username,
        //         'ewbpwd' => $ewbpwd,
        //         'authtoken' => $auth_token
        //     ],
        //     'json' => $dataArray
        // ]);

        // return $response_genEwayBill;




        $accesstoken_endpoint = Config::get('ewaybill.urls.ACCESSTOKEN.production');
        //print_r($accesstoken_endpoint); exit;
        $geneway_endpoint = Config::get('ewaybill.urls.GENEWAYBILL.production');
        $this->gstin = auth()->user()->ewaybillDetail ? auth()->user()->ewaybillDetail->gst : null;
        $this->username = auth()->user()->ewaybillDetail ? auth()->user()->ewaybillDetail->username : null;
        $this->ewbpwd = auth()->user()->ewaybillDetail ? auth()->user()->ewaybillDetail->password : null;

        $itemList = array();

        foreach($invoice->invoice_items as $invItem){
            $invoiceItem = array();

            $strToRemoveIndex = strpos($invItem->item_measuring_unit,"(");
            
            if($strToRemoveIndex){
                $qtyUnit = trim(substr($invItem->item_measuring_unit, 0, $strToRemoveIndex));
            }else {
                $qtyUnit = $invItem->item_measuring_unit;
            }

            $invoiceItem["productName"] = $invItem->name;
            $invoiceItem["productDesc"] = $invItem->name;
            $invoiceItem["hsnCode"] = 1001 ?? $invItem->hsc_code;
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
        
        $fromStateCode = (auth()->user()->profile->communication_state > 9) ? auth()->user()->profile->communication_state : "0".auth()->user()->profile->communication_state;
        // print_r($fromStateCode); exit;
        $toGstin = ($invoice->party->registered !=0 && $invoice->party->gst != null) ? $invoice->party->gst : 'URP';
        // print_r($toGstin); exit;
        $toStateCode = ($invoice->party->shipping_state > 9) ? $invoice->party->shipping_state : "0".$invoice->party->shipping_state;
        //print_r($toStateCode); exit;
        $transporterName = $request->transporter_name ?? '';
        $transporterDocNo = $request->transporter_doc_no ?? '';
        $transportMode = $request->transport_mode ?? '1';
        $transportDistance = $request->transport_distance ?? '68';
        $transportDocDate = $request->transport_doc_date ?? '';
        $vehicleNumber = $request->vehicle_number;
        // print_r($vehicleNumber); exit;
        $vehicleType = (isset($request->vehicle_type) && !empty($request->vehicle_type)) ? strtoupper($request->vehicle_type) : 'R';

        try{
            $authToken = Ewayapi::getAuthToken($accesstoken_endpoint, $this->gstin, $this->username, $this->ewbpwd);
        } catch(\Exception $e) {
            return redirect()->back()->with('failure', 'Failed to generate token - ' . $e->getMessage());
        }

        $dataArray = [
            "supplyType" => "O",
            "subSupplyType" => "1",
            "subSupplyDesc" => "",
            "docType" => "INV",
            "docNo" => $docNo ?? "708241/5451164",
            "docDate" => \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') ?? "02/11/2020",
            "fromGstin" => $fromGstin ?? "34AACCC1596Q002",
            "fromTrdName" => auth()->user()->profile->name ?? "welton",
            "fromAddr1" => auth()->user()->profile->communication_address ?? "2ND CROSS NO 59  19  A",
            "fromAddr2" => "" ?? "GROUND FLOOR OSBORNE ROAD",
            "fromPlace" => auth()->user()->profile->communication_city ?? "FRAZER TOWN",
            "fromPincode" => auth()->user()->profile->communication_pincode ?? 605001,
            "actFromStateCode" => $fromStateCode ?? "34",
            "fromStateCode" => $fromStateCode ?? "34",
            "toGstin" => $toGstin ?? "29AWGPV7107B1Z1",
            "toTrdName" => $invoice->party->name ?? "sthuthya",
            "toAddr1" => $invoice->party->shipping_address ?? "Shree Nilaya",
            "toAddr2" => "" ?? "Dasarahosahalli",
            "toPlace" => $invoice->party->shipping_city ?? "Beml Nagar",
            "toPincode" => $invoice->party->shipping_pincode ?? 562160,
            "actToStateCode" => $toStateCode ?? "29",
            "toStateCode" => $toStateCode ?? "29",
            "transactionType" => 4,
            // "dispatchFromGSTIN" => $fromGstin ?? "29AAAAA1303P1ZV",
            // "dispatchFromTradeName" => auth()->user()->profile->name ?? "ABC Traders",
            // "shipToGSTIN" => $toGstin ?? "29ALSPR1722R1Z3",
            // "shipToTradeName" => $invoice->party->name ?? "XYZ Traders",
            "otherValue" => 0 ?? -100 ,
            "totalValue" => $invoice->item_total_amount ?? 56099,
            "cgstValue" => $invoice->cgst ?? 0,
            "sgstValue" => $invoice->sgst ?? 0,
            "igstValue" => $invoice->igst ?? 300.67,
            "cessValue" => $invoice->cess ?? 400.56,
            "cessNonAdvolValue" => 400,
            "totInvValue" => $invoice->total_amount ?? 68358,
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
            $ewaybill->user_id = auth()->user()->id;

        
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
                    return redirect()->back()->with('success', 'Details added successfully and Eway bill created successfully');
                } else {
                    DB::rollback();
                    return redirect()->back()->with('failure', 'Failed to add data');
                }
            }
        } catch(\Exception $e) {
            DB::rollBack(); 
            $split_error = explode("response:", $e->getMessage());
            $split_error1 = explode("240:", $split_error[1]);
            $errorMsg = str_replace('\r\n"}}', '', $split_error1[1]);
            return redirect()->back()->with('failure', $errorMsg); // ' Failed to generate ewaybill'
        }
    }

    public function save_invoice_additional_charges(Request $request)
    {
        $additional_charges = new AdditionalCharge;
        $additional_charges->invoice_id = $request->invoice_id;
        $additional_charges->labour_charge = $request->labour_charge;
        $additional_charges->transport_charge = $request->transport_charge;
        $additional_charges->insurance_charge = $request->insurance_charge;
        $additional_charges->gst_percentage = $request->gst_percentage;
        $additional_charges->calculated_gst_charge = $request->calculated_gst_charge;

        if($additional_charges->save()){
            return redirect()->back()->with('success', 'Charges added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add data');
        }
    }

    public function show($id)
    {
        $ewaybill = Ewaybill::findOrFail($id);

        return view('ewaybill.show', compact('ewaybill'));
    }

    public function update(Request $request, $id)
    {
        $ewaybill = Ewaybill::findOrFail($id);
        $ewaybill->status = $request->status;

        if($ewaybill->save()){
            return redirect()->back()->with('success', 'Status updated successfully');
        } else {
            return redirect()->back()->with('success', 'Failed to update status');
        }
    }

    public function index(Request $request)
    {
        if (isset($request->from_date) && isset($request->to_date)) {
            $from = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));

            $ewaybills = auth()->user()->ewaybills()->whereBetween('created_on', [$from, $to])->get();
        }
        else{
            $ewaybills = auth()->user()->ewaybills()->get();
        }

        return view('ewaybill.index', compact('ewaybills'));
    }

    public function cancel_eway(Request $request, $bill_no)
    {
        $accesstoken_endpoint = Config::get('ewaybill.urls.ACCESSTOKEN.production');
        $caneway_endpoint = Config::get('ewaybill.urls.EWAYBILL.production');
        $this->gstin = auth()->user()->ewaybillDetail ? auth()->user()->ewaybillDetail->gst : null;
        $this->username = auth()->user()->ewaybillDetail ? auth()->user()->ewaybillDetail->username : null;
        $this->ewbpwd = auth()->user()->ewaybillDetail ? auth()->user()->ewaybillDetail->password : null;

        try{
            $authToken = Ewayapi::getAuthToken($accesstoken_endpoint,  $this->gstin, $this->username, $this->ewbpwd);
        } catch(\Exception $e) {
            $split_error = explode("response:", $e->getMessage());
            $split_error1 = explode("240:", $split_error[1]);
            $errorMsg = str_replace('\r\n"}}', '', $split_error1[1]);
            return redirect()->back()->with('failure', 'Failed to generate token - ' . $errorMsg);
        }

        $dataArray = [
            "ewbNo" => $bill_no,
            "cancelRsnCode" => 2,
            "cancelRmrk" => "Cancelled the order"
        ];

        try {
            $response = Ewayapi::cancelEwayBill($caneway_endpoint, $this->gstin, $this->username, $this->ewbpwd, $authToken, $dataArray);

            if($response == null){
                return redirect()->back()->with('failure', 'Cannot cancel ewaybill');
            }

            $content = $response->getBody();
            $content = json_decode($content, true);

            $ewaybill = Ewaybill::where('bill_no', $bill_no)->first();
            $ewaybill->status = 0;
            $ewaybill->save();
        } catch(\Exception $e) {
            $split_error = explode("response:", $e->getMessage());
            $split_error1 = explode("240:", $split_error[1]);
            $errorMsg = str_replace('\r\n"}}', '', $split_error1[1]);
           return redirect()->back()->with('failure', $errorMsg);
        }

        return redirect()->back()->with('success', 'Successfully cancelled ewaybill');
    }

    public function provide_details_form()
    {
        return view('ewaybill.provide_details');
    }

    public function post_provide_details_form(Request $request)
    {
        // return $request->all();

        $this->validate($request, [
            'gst' => 'required',
            'username' => 'required',
            'password' => 'required'
        ]);

        $ewaybill_detail = EwaybillDetail::where('user_id', auth()->user()->id)->first();

        if(!$ewaybill_detail)
            $ewaybill_detail = new EwaybillDetail;

        $ewaybill_detail->gst = $request->gst;
        $ewaybill_detail->username = $request->username;
        $ewaybill_detail->password = $request->password;
        $ewaybill_detail->user_id = auth()->user()->id;

        if($ewaybill_detail->save()){
            return redirect()->back()->with('success', 'Details saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save details');
        }
    }
}
