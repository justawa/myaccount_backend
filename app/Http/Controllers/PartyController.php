<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Auth;
use Excel;

use App\Party;
use App\State;
use App\Invoice;
use App\PurchaseRecord;

class PartyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if( isset( $request->party ) ){
            $parties = Party::where('user_id', Auth::user()->id)->where('name', $request->party)->get();
        } else {
            $parties = Party::where('user_id', Auth::user()->id)->get();
        }


        // return $parties;

        return view('party.index', compact('parties'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $states = State::all();
        return view('party.create', compact('states'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // return $request->all();

        $this->validate($request, [
            'business_place' => 'required',
            'opening_balance_as_on' => 'validOpeningDate|nullable',
        ]);

        $party = new Party;

        $party->contact_person_name = $request->contact_person_name;
        $party->name = $request->name;
        $party->email = $request->email;
        $party->registered = $request->status_of_registration;
        if( $request->status_of_registration == 1 || $request->status_of_registration == 3 || $request->status_of_registration == 4){
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

        if (isset($request->checkbox_communication_address) && $request->checkbox_communication_address == "1" ) {
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
        
        // if (Carbon::parse(auth()->user()->profile->financial_year_from) > Carbon::parse($party->opening_balance_as_on)) {
        //     return redirect()->back()->with('failure', 'Please provide valid opening balance date');
        // }

        // if (Carbon::parse(auth()->user()->profile->financial_year_to) < Carbon::parse($party->opening_balance_as_on)) {
        //     return redirect()->back()->with('failure', 'Please provide valid opening balance date');
        // }

        $party->balance_type = $request->balance_type;

        $party->terms_and_condition = $request->terms_and_condition;

        $party->user_id = Auth::user()->id;

        if ($party->save()) {
            return redirect()->back()->with('success', 'Party added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add party');
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
        $party = Party::findOrFail($id);

        return view('party.show', compact('party'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $party = Party::findOrFail($id);
        $states = State::all();

        return view('party.edit', compact('party', 'states'));
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

        // return $request->all();
        $this->validate($request, [
            'business_place' => 'required',
            'opening_balance_as_on' => 'validOpeningDate|nullable',
        ]);

        $party = Party::findOrFail($id);

        // $party->contact_person_name = $request->contact_person_name;
        // $party->name = $request->name;
        // $party->registered = $request->is_registered;
        // if ($request->is_registered == 1) {
        //     $party->gst = $request->gst;
        // }
        // $party->phone = $request->phone;
        //
        // $party->communication_address = $request->address;
        // $party->communication_state = $request->state;
        // $party->communication_city = $request->city;
        // $party->communication_pincode = $request->pincode;
        //
        // if( isset($request->checkbox_shipping_address) && $request->checkbox_shipping_address == "1" ) {
        //     $party->shipping_address = $request->address;
        //     $party->shipping_state = $request->state;
        //     $party->shipping_city = $request->city;
        //     $party->shipping_pincode = $request->pincode;
        // } else {
        //     $party->shipping_address = $request->shipping_address;
        //     $party->shipping_state = $request->shipping_state;
        //     $party->shipping_city = $request->shipping_city;
        //     $party->shipping_pincode = $request->shipping_pincode;
        // }
        //
        // if ( isset($request->checkbox_billing_address) && $request->checkbox_billing_address == "1" ) {
        //     $party->billing_address = $request->address;
        //     $party->billing_state = $request->state;
        //     $party->billing_city = $request->city;
        //     $party->billing_pincode = $request->pincode;
        // } else {
        //     $party->billing_address = $request->billing_address;
        //     $party->billing_state = $request->billing_state;
        //     $party->billing_city = $request->billing_city;
        //     $party->billing_pincode = $request->billing_pincode;
        // }
        //
        // $party->opening_balance = $request->opening_balance;
        // $time = strtotime($request->opening_balance_as_on);
        // $formatedDate = date('Y-m-d', $time);
        // $party->away_from_us_distance = $request->away_distance;
        // $party->opening_balance_as_on = $formatedDate;

// ------------------

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
        $party->communication_address = $request->address;
        $party->communication_state = $request->state;
        $party->communication_city = $request->city;
        $party->communication_pincode = $request->pincode;

        if (isset($request->checkbox_shipping_address) && $request->checkbox_shipping_address == "1") {
            $party->shipping_address = $request->address;
            $party->shipping_state = $request->state;
            $party->shipping_city = $request->city;
            $party->shipping_pincode = $request->pincode;
        } else {
            $party->shipping_address = $request->shipping_address;
            $party->shipping_state = $request->shipping_state;
            $party->shipping_city = $request->shipping_city;
            $party->shipping_pincode = $request->shipping_pincode;
        }

        if (isset($request->checkbox_billing_address) && $request->checkbox_billing_address == "1") {
            $party->billing_address = $request->address;
            $party->billing_state = $request->state;
            $party->billing_city = $request->city;
            $party->billing_pincode = $request->pincode;
        } else {
            $party->billing_address = $request->billing_address;
            $party->billing_state = $request->billing_state;
            $party->billing_city = $request->billing_city;
            $party->billing_pincode = $request->billing_pincode;
        }

        $party->opening_balance = $request->opening_balance;
        $time = strtotime(str_replace('/', '-', $request->opening_balance_as_on));
        $formatedDate = date('Y-m-d', $time);

        if($request->has('edit_applicable_date')){
            $party->edit_applicable_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->edit_applicable_date)));
        } else {
            $party->edit_applicable_date = Carbon::Now()->format('Y-m-d');
        }

        $party->away_from_us_distance = $request->away_distance;
        $party->opening_balance_as_on = $formatedDate;
        $party->balance_type = $request->balance_type;

        $party->terms_and_condition = $request->terms_and_condition;

        if ($party->save()) {
            return redirect(route('party.index'))->with('success', 'Party updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update party');
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
        $party = Party::findOrFail($id);

        if ($party->delete()) {
            return redirect()->back()->with('success', 'Party deleted successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to delete party');
        }
    }

    public function post_fetch_party_billing_address(Request $request){
        $party = Party::find($request->party);

        return response()->json($party);
    }

    public function post_find_party_detail( Request $request ) {
        $party = Party::find($request->selected_party);

        return response()->json($party);
    }

    public function search_party_by_name( Request $request ) {

        $parties = Party::where('name', 'like', $request->party.'%')->get();

        return response()->json($parties);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function party_report(Request $request)
    {

        if( isset( $request->party ) ){
            $parties = Party::where('user_id', Auth::user()->id)->where('name', $request->party)->get();
        } else {
            $parties = Party::where('user_id', Auth::user()->id)->get();
        }


        // return $parties;

        return view('report.party', compact('parties'));
    }

    public function get_import_to_table()
    {
        return view('party.import_to_table');
    }

    public function post_import_to_table(Request $request)
    {

        $this->validate($request, [
            'party_file' => 'required'
        ]);


        if ($request->hasFile('party_file')) {

            $path = $request->file('party_file')->getRealPath();

            $data = Excel::load($path)->get();

            if (!empty($data) && $data->count()) {
                foreach ($data->toArray() as $row) {
                    if (!empty($row)) {
                        $dataArray[] = [
                            'contact_person_name' => $row['contact_person_name'],
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'registered' => $row['registered'],
                            'gst' => $row['gst'],
                            'is_operator' => $row['is_operator'],
                            'tds_income_tax' => $row['tds_income_tax'],
                            'tds_gst' => $row['tds_gst'],
                            'tcs_income_tax' => $row['tcs_income_tax'],
                            'tcs_gst' => $row['tcs_gst'],
                            'phone' => $row['phone'],
                            'business_place' => $row['business_place'],
                            'reverse_charge' => $row['reverse_charge'],
                            'communication_address' => $row['communication_address'],
                            'communication_city' => $row['communication_city'],
                            'communication_state' => $row['communication_state'],
                            'communication_pincode' => $row['communication_pincode'],
                            'shipping_address' => $row['shipping_address'],
                            'shipping_city' => $row['shipping_city'],
                            'shipping_state' => $row['shipping_state'],
                            'shipping_pincode' => $row['shipping_pincode'],
                            'billing_address' => $row['billing_address'],
                            'billing_city' => $row['billing_city'],
                            'billing_state' => $row['billing_state'],
                            'billing_pincode' => $row['billing_pincode'],
                            'opening_balance' => $row['opening_balance'],
                            'opening_balance_as_on' => $row['opening_balance_as_on'],
                            'balance_type' => $row['balance_type'],
                            'status_of_registration' => $row['status_of_registration'],
                            'terms_and_condition' => $row['terms_and_condition'],
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s', time()),
                            'updated_at' => date('Y-m-d H:i:s', time()),
                        ];
                    }
                }
                if (!empty($dataArray)) {
                    Party::insert($dataArray);
                    return redirect()->back()->with('success', 'Data uploaded successfully');
                }
            }
        }
    }

    public function single_party_report($id)
    {
        $party = Party::findOrFail($id);

        $invoices = Invoice::where('party_id', $party->id)->get();
        $purchases = PurchaseRecord::where('party_id', $party->id)->get();

        $invoice_array = array();
        $purchase_array = array();

        foreach($invoices as $invoice){
            $invoice_array[] = [
                'date' => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
                'particulars' => $party->name,
                'voucher_type' => 'Sales',
                'voucher_no' => $invoice->invoice_prefix.$invoice->invoice_no.$invoice->invoice_suffix,
                'value' => $invoice->item_total_amount,
                'type' => 'sale'
            ];
        }

        foreach($purchases as $purchase){
            $purchase_array[] = [
                'date' => Carbon::parse($purchase->bill_date)->format('Y-m-d'),
                'particulars' => $party->name,
                'voucher_type' => 'Purchases',
                'voucher_no' => $purchase->bill_no,
                'value' => $purchase->item_total_amount,
                'type' => 'purchase'
            ];
        }

        $combined_array = array_merge(
            $invoice_array,
            $purchase_array
        );

        $this->array_sort_by_column($combined_array, 'date');

        return view('party.single_party_report', ['rows' => $combined_array]);
    }

    private function array_sort_by_column(&$array, $column, $direction = SORT_ASC)
    {
        $reference_array = array();

        foreach ($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }

    public function export_as_excel()
    {
        $partyArray = Party::where('user_id', auth()->user()->id)->get()->toArray();

        Excel::create('party', function ($excel) use ($partyArray) {
            $excel->sheet('Party List', function ($sheet) use ($partyArray) {
                $sheet->fromArray($partyArray);
            });
        })->export('xlsx');
    }

    public function find_party_name(Request $request)
    {
        $parties = Party::where('user_id', Auth::user()->id)->where('name', 'like', $request->q.'%')->get();
        return response()->json($parties);
    }
}
