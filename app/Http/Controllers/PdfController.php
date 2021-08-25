<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use PDF;

use App\Bank;
use App\Invoice;
use App\Invoice_Item;
use App\Item;
use App\Party;
use App\Purchase;
use App\PurchaseRecord;
use App\PurchaseOrder;
use App\SaleOrder;
use App\User;
use App\UserProfile;
// use Barryvdh\DomPDF\PDF as BarryvdhPDF;

class PdfController extends Controller
{
    public function print_invoice(Request $request, $id) 
    {
        $bank = null;

        if($request->filled('user_id')){
            $user = User::findOrFail($request->user_id);
        } else {
            $user = auth()->user();
        }
        $user_profile = UserProfile::where('user_id', $user->id)->first();

        $invoice = Invoice::findOrFail($id);
        if($invoice->bank_id != null) {
            $bank = Bank::findOrFail($invoice->bank_id);
        }
        $invoice_items = Invoice_Item::where('invoice_id', $id)->get();
        $party = Invoice::findorFail($id)->party;

        $hsn_data = [];
        $temp_data = [];
        foreach ($invoice_items as $data) {
            $item = Item::find($data->item_id);
            
            $data->info = $item;

            if($item->hsc_code) {
                if(isset($temp_data["hsn-total-".$item->hsc_code])) {
                    $temp_data["hsn-total-".$item->hsc_code] += $data->item_total;
                    $temp_data["gst-total-".$item->hsc_code] += ($data->gst + $data->rcm_gst);
                } else {
                    $temp_data["hsn-total-".$item->hsc_code] = $data->item_total;
                    $temp_data["gst-total-".$item->hsc_code] = ($data->gst + $data->rcm_gst);
                }
                
                $hsn_data[$item->hsc_code] = [$item->hsc_code => ["code" => $item->hsc_code, "rate" => $item->gst, "gst_amount" => $temp_data["gst-total-".$item->hsc_code], "taxable_value" => $temp_data["hsn-total-".$item->hsc_code]]];
            } else {
                if(isset($temp_data["hsn-total-".$item->sac_code])) {
                    $temp_data["hsn-total-".$item->sac_code] += $data->item_total;
                    $temp_data["gst-total-".$item->sac_code] += ($data->gst + $data->rcm_gst);
                } else {
                    $temp_data["hsn-total-".$item->sac_code] = $data->item_total;
                    $temp_data["gst-total-".$item->sac_code] = ($data->gst + $data->rcm_gst);
                }
                $hsn_data[$item->hsc_code] = [$item->sac_code => ["code" => $item->sac_code, "rate" => $item->gst, "gst_amount" => $temp_data["gst-total-".$item->sac_code], "taxable_value" => $temp_data["hsn-total-".$item->sac_code]]];
            }

        }

        // return $hsn_data;

        // $similar_code_items = [];
        // foreach($invoice_items as $data) {
        //     $item = Item::find($data->item_id);

        //     $similar_code_items[$item->info->hsc_code]->
        // }

        // return $party;

        // $pdf = PDF::loadView('pdf.invoice', compact('invoice', 'party', 'user_profile', 'invoice_items', 'bank'));
        // return $pdf->stream();

        // if($user_profile->invoice_template == 1){
            // return view('pdf.normal_invoice', compact('invoice', 'party', 'user', 'user_profile', 'invoice_items', 'bank'));

            // $pdf = PDF::loadView('pdf.normal_invoice', compact('invoice', 'party', 'user_profile', 'invoice_items', 'bank'));
            // return $pdf->stream();
        // } else {
            return view('pdf.hsn_sac_invoice', compact('invoice', 'party', 'user', 'user_profile', 'invoice_items', 'bank', 'hsn_data'));

            // $pdf = PDF::loadView('pdf.hsn_sac_invoice', compact('invoice', 'party', 'user_profile', 'invoice_items', 'bank'));
            // return $pdf->stream();
        // }

    }

    public function print_bill(Request $request, $id) 
    {
        $bank = null;

        if($request->filled('user_id')){
            $user = User::findOrFail($request->user_id);
        } else {
            $user = auth()->user();
        }

        $user_profile = UserProfile::where('user_id', $user->id)->first();

        $bill = PurchaseRecord::findOrFail($id);
        if($bill->bank_id != null) {
            $bank = Bank::findOrFail($bill->bank_id);
        }
        $bill_items = Purchase::where('purchase_id', $id)->get();
        $party = $bill->party;

        foreach ($bill_items as $data) {
            $item = Item::find($data->item_id);
            
            $data->info = $item;
        }

        // return $party;

        // $pdf = PDF::loadView('pdf.bill', compact('bill', 'party', 'user_profile', 'bill_items'));
        // return $pdf->stream();

        return view('pdf.bill', compact('bill', 'party', 'user', 'user_profile', 'bill_items', 'bank'));
    }

    public function print_sale_order($token)
    {
        $records = SaleOrder::where('token', $token)->where('user_id', auth()->user()->id)->get();
        $user_profile = auth()->user()->profile;
        $party_name = '';
        $order_token = '';

        foreach ($records as $record) {
            $party = Party::find($record->party_id);
            $item = Item::find($record->item_id);

            $party_name = $party->name;
            $record->item_name = $item->name;

            $order_token = $record->token;
        }

        // $pdf = PDF::loadView('pdf.sale_order', compact('records', 'party_name', 'order_token', 'user_profile'));
        // return $pdf->stream();
        

        return view('pdf.sale_order', compact('records', 'party', 'order_token', 'user_profile'));
    }

    public function print_purchase_order($token)
    {
        $records = PurchaseOrder::where('token', $token)->where('user_id', auth()->user()->id)->get();
        $user_profile = auth()->user()->profile;
        $party_name = '';
        $order_token = '';

        foreach ($records as $record) {
            $party = Party::find($record->party_id);
            $item = Item::find($record->item_id);

            $party_name = $party->name;
            $record->item_name = $item->name;

            $order_token = $record->token;
        }

        // $pdf = PDF::loadView('pdf.purchase_order', compact('records', 'party_name', 'order_token', 'user_profile'));
        // return $pdf->stream();
        
        return view('pdf.purchase_order', compact('records', 'party', 'order_token', 'user_profile'));
    }

    public function print_sale_credit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $user_profile = auth()->user()->profile;
        $party = '';

        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $note_no)->where('credit_notes.type', 'sale')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->get();
        $note_date = '';
        foreach ($credit_notes as $note) {
            $item = Item::findOrFail($note->item_id);
            $note->item_name = $item->name ?? '';
            $note->item_gst = $item->gst ?? '';
            $note_date = $note->note_date;
            $party = Invoice::findorFail($note->invoice_id)->party;
        }

        $hsn_data = [];
        $temp_data = [];
        foreach ($credit_notes as $data) {
            $item = Item::find($note->item_id);
            
            $data->info = $item;

            if($item->hsc_code) {
                if(isset($temp_data["hsn-total-".$item->hsc_code])) {
                    $temp_data["hsn-total-".$item->hsc_code] += $data->item_total;
                    $temp_data["gst-total-".$item->hsc_code] += ($data->gst + $data->rcm_gst);
                } else {
                    $temp_data["hsn-total-".$item->hsc_code] = $data->item_total;
                    $temp_data["gst-total-".$item->hsc_code] = ($data->gst + $data->rcm_gst);
                }
                
                $hsn_data[$item->hsc_code] = [$item->hsc_code => ["code" => $item->hsc_code, "rate" => $item->gst, "gst_amount" => $temp_data["gst-total-".$item->hsc_code], "taxable_value" => $temp_data["hsn-total-".$item->hsc_code]]];
            } else {
                if(isset($temp_data["hsn-total-".$item->sac_code])) {
                    $temp_data["hsn-total-".$item->sac_code] += $data->item_total;
                    $temp_data["gst-total-".$item->sac_code] += ($data->gst + $data->rcm_gst);
                } else {
                    $temp_data["hsn-total-".$item->sac_code] = $data->item_total;
                    $temp_data["gst-total-".$item->sac_code] = ($data->gst + $data->rcm_gst);
                }
                $hsn_data[$item->hsc_code] = [$item->sac_code => ["code" => $item->sac_code, "rate" => $item->gst, "gst_amount" => $temp_data["gst-total-".$item->sac_code], "taxable_value" => $temp_data["hsn-total-".$item->sac_code]]];
            }

        }

        $invoice = Invoice::findOrFail($credit_notes->first()->invoice_id);

        return view('pdf.sale_credit_note', compact('note_no', 'credit_notes', 'note_date', 'user_profile', 'party', 'hsn_data', 'invoice'));
    }

    public function print_sale_debit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $user_profile = auth()->user()->profile;
        $party = '';

        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $note_no)->where('debit_notes.type', 'sale')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->get();
        $note_date = '';
        foreach ($debit_notes as $note) {
            $item = Item::findOrFail($note->item_id);
            $note->item_name = $item->name ?? '';
            $note->item_gst = $item->gst ?? '';
            $note_date = $note->note_date;
            $party = Invoice::findorFail($note->bill_no)->party;
        }

        $invoice = Invoice::findOrFail($debit_notes->first()->bill_no);

        return view('pdf.sale_debit_note', compact('note_no', 'debit_notes', 'note_date', 'user_profile', 'party'));
    }

    public function print_purchase_credit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $user_profile = auth()->user()->profile;
        $party = '';

        $credit_notes = User::find(auth()->user()->id)->creditNotes()->where('credit_notes.note_no', $note_no)->where('credit_notes.type', 'purchase')->whereBetween('credit_notes.note_date', [$from_date, $to_date])->get();
        $note_date = '';
        foreach ($credit_notes as $note) {
            $item = Item::findOrFail($note->item_id);
            $note->item_name = $item->name ?? '';
            $note->item_gst = $item->gst ?? '';
            $note_date = $note->note_date;
            $party = PurchaseRecord::findorFail($note->invoice_id)->party;
        }

        $bill = PurchaseRecord::findOrFail($credit_notes->first()->invoice_id);

        return view('pdf.purchase_credit_note', compact('note_no', 'credit_notes', 'note_date', 'user_profile', 'party'));
    }

    public function print_purchase_debit_note($note_no)
    {
        $from_date = auth()->user()->profile->financial_year_from;
        $to_date = auth()->user()->profile->financial_year_to;

        $user_profile = auth()->user()->profile;
        $party = '';

        $debit_notes = User::find(auth()->user()->id)->debitNotes()->where('debit_notes.note_no', $note_no)->where('debit_notes.type', 'purchase')->whereBetween('debit_notes.note_date', [$from_date, $to_date])->get();
        $note_date = '';
        foreach ($debit_notes as $note) {
            $item = Item::findOrFail($note->item_id);
            $note->item_name = $item->name ?? '';
            $note->item_gst = $item->gst ?? '';
            $note_date = $note->note_date;
            $party = PurchaseRecord::findorFail($note->bill_no)->party;
        }

        $bill = PurchaseRecord::findOrFail($debit_notes->first()->bill_no);

        return view('pdf.purchase_debit_note', compact('note_no', 'debit_notes', 'note_date', 'user_profile', 'party'));
    }
}
