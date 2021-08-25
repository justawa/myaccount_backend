<?php

namespace App\Http\Controllers;

use App\Invoice_Item;
use App\Item;
use App\State;
use Auth;
use Excel;
use Illuminate\Http\Request;

use App\User;

class SingleExcelExportController extends Controller
{

    public function index()
    {
        return view('singleexcelexport.index');
    }

    public function export_all_excel(Request $request)
    {
        $b2bArray = array();
        $b2clArray = array();
        $b2csArray = array();
        $cdnrArray = array();
        $cdnurArray = array();
        $expArray = array();
        $atArray = array();
        $atadjArray = array();
        $exempArray = array();
        $hsnArray = array();
        $docsArray = array();

        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->to_date)));
        } else {
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;
        }

        $sales = User::findOrFail(Auth::user()->id)->invoices()->whereBetween('invoice_date', [$from_date, $to_date])->get();

        foreach ($sales as $sale) {

            $place_of_supply = State::find($sale->party->business_place);

            $b2bArray[$sale->id]['GSTIN/UIN of Receipt'] = $sale->party->gst;
            $b2bArray[$sale->id]['Invoice No'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $b2bArray[$sale->id]['Invoice date'] = $sale->invoice_date;
            $b2bArray[$sale->id]['Invoice Value'] = $sale->total_amount;
            $b2bArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $b2bArray[$sale->id]['Reverse Charge'] = $sale->party->reverse_charge;
            $b2bArray[$sale->id]['Invoice Type'] = $sale->type_of_bill;
            $b2bArray[$sale->id]['E-commerce GST IN'] = '';
            $b2bArray[$sale->id]['Rate'] = $sale->gst;
            $b2bArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;
            $b2bArray[$sale->id]['Cess Amount'] = $sale->cess;
        }

        foreach ($sales as $sale) {

            $place_of_supply = State::find($sale->party->business_place);

            $b2clArray[$sale->id]['Invoice No'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $b2clArray[$sale->id]['Invoice date'] = $sale->invoice_date;
            $b2clArray[$sale->id]['Invoice Value'] = $sale->total_amount;
            $b2clArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $b2clArray[$sale->id]['Rate'] = $sale->gst;
            $b2clArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;
            $b2clArray[$sale->id]['Cess Amount'] = $sale->cess;
            $b2clArray[$sale->id]['E-commerce GST IN'] = '';
        }

        foreach($sales as $sale) {

            $place_of_supply = State::find($sale->party->business_place);

            $b2csArray[$sale->id]['Type'] = $sale->type_of_bill;
            $b2csArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $b2csArray[$sale->id]['Rate'] = $sale->gst;
            $b2csArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;
            $b2csArray[$sale->id]['Cess Amount'] = $sale->cess;
            $b2csArray[$sale->id]['E-commerce GST IN'] = '';
        }

        foreach ($sales as $sale) {

            $place_of_supply = State::find($sale->party->business_place);

            $cdnrArray[$sale->id]['GST In/UIN of Receipt'] = $sale->party->gst;
            $cdnrArray[$sale->id]['Invoice/Advance Receipt Number'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $cdnrArray[$sale->id]['Invoice/Advance Receipt date'] = $sale->invoice_date;
            $cdnrArray[$sale->id]['Note/Refund Voucher Number'] = '';
            $cdnrArray[$sale->id]['Note/Refund Voucher date'] = '';
            $cdnrArray[$sale->id]['Document Type'] = '';
            $cdnrArray[$sale->id]['Reason For Issuing document'] = '';
            $cdnrArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $cdnrArray[$sale->id]['Note/Refund Voucher Value'] = '';
            $cdnrArray[$sale->id]['Rate'] = $sale->gst;
            $cdnrArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;;
            $cdnrArray[$sale->id]['Cess Amount'] = $sale->cess;
            $cdnrArray[$sale->id]['Pre GST'] = '';
        }

        foreach ($sales as $sale) {

            $place_of_supply = State::find($sale->party->business_place);

            $cdnurArray[$sale->id]['UR Type'] = '';
            $cdnurArray[$sale->id]['Note/Refund Voucher Number'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $cdnurArray[$sale->id]['Note/Refund Voucher date'] = $sale->invoice_date;
            $cdnurArray[$sale->id]['Document Type'] = '';
            $cdnurArray[$sale->id]['Invoice/Advance Receipt Number'] = '';
            $cdnurArray[$sale->id]['Invoice/Advance Receipt date'] = '';
            $cdnurArray[$sale->id]['Reason For Issuing document'] = '';
            $cdnurArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $cdnurArray[$sale->id]['Note/Refund Voucher Value'] = '';
            $cdnurArray[$sale->id]['Rate'] = $sale->gst;
            $cdnurArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;;
            $cdnurArray[$sale->id]['Cess Amount'] = $sale->cess;
            $cdnurArray[$sale->id]['Pre GST'] = '';
        }

        foreach($sales as $sale) {
            $expArray[$sale->id]['Export Type'] = '';
            $expArray[$sale->id]['Invoice Number'] = $sale->invoice_prefix . $sale->invoice_no . $sale->invoice_suffix;
            $expArray[$sale->id]['Invoice date'] = $sale->invoice_date;
            $expArray[$sale->id]['Invoice Value'] = $sale->total_amount;
            $expArray[$sale->id]['Port Code'] = '';
            $expArray[$sale->id]['Shipping Bill Number'] = '';
            $expArray[$sale->id]['Shipping Bill Date'] = '';
            $expArray[$sale->id]['Rate'] = $sale->gst;
            $expArray[$sale->id]['Taxable Value'] = $sale->item_total_amount;
        }

        foreach( $sales as $sale ) {

            $place_of_supply = State::find($sale->party->business_place);

            $atArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $atArray[$sale->id]['Rate'] = $sale->gst;
            $atArray[$sale->id]['Gross Advance Received'] = '';
            $atArray[$sale->id]['Cess Amount'] = $sale->cess;
        }

        foreach( $sales as $sale ) {

            $place_of_supply = State::find($sale->party->business_place);

            $atadjArray[$sale->id]['Place Of Supply'] = $place_of_supply->name;
            $atadjArray[$sale->id]['Rate'] = $sale->gst;
            $atadjArray[$sale->id]['Gross Advance Received'] = '';
            $atadjArray[$sale->id]['Cess Amount'] = $sale->cess;
        }

        foreach( $sales as $sale ){
            $exempArray[$sale->id]['Description'] = '';
            $exempArray[$sale->id]['Nil Rated Supplies'] = '';
            $exempArray[$sale->id]['Exempted(other than nil rated/non GST supply)'] = '';
            $exempArray[$sale->id]['Non-GST supplies'] = '';
        }

        $items = Item::where('user_id', Auth::user()->id)->get();

        foreach ($items as $item) {
            $invoice_items = Invoice_Item::where('item_id', $item->id)->get();

            $total_gst = 0;
            $total_qty = 0;
            $total_value = 0;
            $taxable_value = 0;
            $integrated_tax_value = 0;
            $central_tax_value = 0;
            $state_tax_value = 0;
            $cess_amount = 0;

            foreach ($invoice_items as $per_item) {
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

            $hsnArray[$item->id]['HSN'] = $item->hsc_code;
            $hsnArray[$item->id]['Description'] = $item->name;
            $hsnArray[$item->id]['UQC'] = $item->measuring_unit;
            $hsnArray[$item->id]['Total Quantity'] = $item->total_qty_per_item;
            $hsnArray[$item->id]['Total Value'] = $item->total_value_per_item;
            $hsnArray[$item->id]['Taxable Value'] = $item->taxable_value_per_item;
            $hsnArray[$item->id]['Integrated Tax Amount'] = $item->integrated_tax_value_per_item;
            $hsnArray[$item->id]['Central Tax Amount'] = $item->central_tax_value_per_item;
            $hsnArray[$item->id]['State/UT Tax Amount'] = $item->state_tax_value_per_item;
            $hsnArray[$item->id]['Cess Amount'] = $item->cess_amount_per_item;
        }

        foreach($sales as $sale){
            $docsArray[$sale->id]['Nature of Document'] = '';
            $docsArray[$sale->id]['Sr. No. From'] = '';
            $docsArray[$sale->id]['Sr. No. To'] = '';
            $docsArray[$sale->id]['Total Number'] = '';
            $docsArray[$sale->id]['Cancelled'] = '';
        }

        // echo '<pre>';
        // print_r($docsArray);
        // die();

        Excel::create('GSTR-1', function ($excel) use ($b2bArray, $b2clArray, $b2csArray, $cdnrArray, $cdnurArray, $expArray, $atArray, $atadjArray, $exempArray, $hsnArray, $docsArray) {
            $excel->sheet('b2b', function ($sheet) use ($b2bArray) {
                $sheet->fromArray($b2bArray);
            });

            $excel->sheet('b2cl', function ($sheet) use ($b2clArray) {
                $sheet->fromArray($b2clArray);
            });

            $excel->sheet('b2cs', function ($sheet) use ($b2csArray) {
                $sheet->fromArray($b2csArray);
            });

            $excel->sheet('cdnr', function ($sheet) use ($cdnrArray) {
                $sheet->fromArray($cdnrArray);
            });

            $excel->sheet('cdnur', function ($sheet) use ($cdnurArray) {
                $sheet->fromArray($cdnurArray);
            });

            $excel->sheet('exp', function ($sheet) use ($expArray) {
                $sheet->fromArray($expArray);
            });

            $excel->sheet('at', function ($sheet) use ($atArray) {
                $sheet->fromArray($atArray);
            });

            $excel->sheet('atadj', function ($sheet) use ($atadjArray) {
                $sheet->fromArray($atadjArray);
            });

            $excel->sheet('exemp', function ($sheet) use ($exempArray) {
                $sheet->fromArray($exempArray);
            });

            $excel->sheet('hsn', function ($sheet) use ($hsnArray) {
                $sheet->fromArray($hsnArray);
            });

            $excel->sheet('docs', function ($sheet) use ($docsArray) {
                $sheet->fromArray($docsArray);
            });
        })->export('xlsx');


    }
}
