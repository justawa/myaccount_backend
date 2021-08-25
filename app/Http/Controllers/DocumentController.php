<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ShareDocument;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use Auth;

use App\UploadedBill;
use App\UploadedDocument;
use App\UploadBankStatement;
use App\CashWithdrawDocument;
use App\CashDepositDocument;

class DocumentController extends Controller
{
    public function get_sale_document(Request $request) {

        // Carbon::now()->format('m');
        // $now = Carbon::now();
        // echo $now->year;
        // echo $now->month;
        // echo $now->weekOfYear;

        if(auth()->user()->profile->financial_year_from && auth()->user()->profile->financial_year_to){

            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;

            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->whereBetween('created_at', [$from_date, $to_date])->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->whereBetween('created_at', [$from_date, $to_date])->where('status', 1)->orderBy('created_at', 'desc')->get();
        }

        if ($request->month == null && $request->year == null) {
            $month = "All";
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('status', 1)->orderBy('created_at', 'desc')->get();
        } 
        
        else if ($request->month == null){
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('year', $request->year)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('year', $request->year)->where('status', 1)->orderBy('created_at', 'desc')->get();
        } else if ($request->year == null) {
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('month', $request->month)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('month', $request->month)->where('status', 1)->orderBy('created_at', 'desc')->get();
        } else {
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('month', $request->month)->where('year', $request->year)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('month', $request->month)->where('year', $request->year)->where('status', 1)->orderBy('created_at', 'desc')->get();
        }

        // return $uploaded_bills;

        foreach ($uploaded_bills as $bill) {
            switch($bill->month){
                case 1: $bill->month = "Jan"; break;
                case 2: $bill->month = "Feb"; break;
                case 3: $bill->month = "Mar"; break;
                case 4: $bill->month = "Apr"; break;
                case 5: $bill->month = "May"; break;
                case 6: $bill->month = "Jun"; break;
                case 7: $bill->month = "Jul"; break;
                case 8: $bill->month = "Aug"; break;
                case 9: $bill->month = "Sep"; break;
                case 10: $bill->month = "Oct"; break;
                case 11: $bill->month = "Nov"; break;
                case 12: $bill->month = "Dec"; break;
            }
        }

        if($request->month != null){
            switch($request->month){
                case 1: $month = "Jan"; break;
                case 2: $month = "Feb"; break;
                case 3: $month = "Mar"; break;
                case 4: $month = "Apr"; break;
                case 5: $month = "May"; break;
                case 6: $month = "Jun"; break;
                case 7: $month = "Jul"; break;
                case 8: $month = "Aug"; break;
                case 9: $month = "Sep"; break;
                case 10: $month = "Oct"; break;
                case 11: $month = "Nov"; break;
                case 12: $month = "Dec"; break;
            }
        } else {
            $month = 'All';
        }

        // return $uploaded_bill;
        $total_count = count($uploaded_bills);

        // View::share('sale_document_count', $total_count);

        return view('document.sale', compact('uploaded_bills', 'uploaded_bills_completed','month', 'total_count'));
    }

    public function get_purchase_document(Request $request) {

        if(auth()->user()->profile->financial_year_from && auth()->user()->profile->financial_year_to){

            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;

            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->whereBetween('created_at', [$from_date, $to_date])->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->whereBetween('created_at', [$from_date, $to_date])->where('status', 1)->orderBy('created_at', 'desc')->get();
        }
        if ($request->month == null && $request->year == null) {
            $month = "All";
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('status', 1)->orderBy('created_at', 'desc')->get();
        }
        else if ( $request->month == null ){
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('year', $request->year)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('year', $request->year)->where('status', 1)->orderBy('created_at', 'desc')->get();
        }
        else if ( $request->year == null ) {
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('month', $request->month)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('month', $request->month)->where('status', 1)->orderBy('created_at', 'desc')->get();
        } else {
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('month', $request->month)->where('year', $request->year)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('month', $request->month)->where('year', $request->year)->where('status', 1)->orderBy('created_at', 'desc')->get();

            switch($request->month){
                case 1: $month = "Jan"; break;
                case 2: $month = "Feb"; break;
                case 3: $month = "Mar"; break;
                case 4: $month = "Apr"; break;
                case 5: $month = "May"; break;
                case 6: $month = "Jun"; break;
                case 7: $month = "Jul"; break;
                case 8: $month = "Aug"; break;
                case 9: $month = "Sep"; break;
                case 10: $month = "Oct"; break;
                case 11: $month = "Nov"; break;
                case 12: $month = "Dec"; break;
            }

        }


        foreach ($uploaded_bills as $bill) {
            switch($bill->month){
                case 1: $bill->month = "Jan"; break;
                case 2: $bill->month = "Feb"; break;
                case 3: $bill->month = "Mar"; break;
                case 4: $bill->month = "Apr"; break;
                case 5: $bill->month = "May"; break;
                case 6: $bill->month = "Jun"; break;
                case 7: $bill->month = "Jul"; break;
                case 8: $bill->month = "Aug"; break;
                case 9: $bill->month = "Sep"; break;
                case 10: $bill->month = "Oct"; break;
                case 11: $bill->month = "Nov"; break;
                case 12: $bill->month = "Dec"; break;
            }
        }

        // return $uploaded_bill;
        $total_count = count($uploaded_bills);

        return view('document.purchase', compact('uploaded_bills', 'uploaded_bills_completed','month', 'total_count'));
    }

    public function get_bank_statement_document(Request $request)
    {

        if(auth()->user()->profile->financial_year_from && auth()->user()->profile->financial_year_to){

            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;

            $uploaded_statements = UploadBankStatement::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->orderBy('created_at', 'desc')->get();
        }
        if ($request->month == null && $request->year == null) {
            $month = "All";
            $uploaded_statements = UploadBankStatement::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        } 
        else if ($request->month == null){
             $uploaded_statements = UploadBankStatement::where('user_id', Auth::user()->id)->where('year', $request->year)->orderBy('created_at', 'desc')->get();
        } else if ($request->year == null) {
            $uploaded_statements = UploadBankStatement::where('user_id', Auth::user()->id)->where('month', $request->month)->orderBy('created_at', 'desc')->get();
        } else {
            $uploaded_statements = UploadBankStatement::where('user_id', Auth::user()->id)->where('month',$request->month)->where('year', $request->year)->orderBy('created_at', 'desc')->get();

            switch($request->month){
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
        }

        foreach ( $uploaded_statements as $statement ) {
            switch ($statement->month){
                case 1:
                    $statement->month = "Jan";
                    break;
                case 2:
                    $statement->month = "Feb";
                    break;
                case 3:
                    $statement->month = "Mar";
                    break;
                case 4:
                    $statement->month = "Apr";
                    break;
                case 5:
                    $statement->month = "May";
                    break;
                case 6:
                    $statement->month = "Jun";
                    break;
                case 7:
                    $statement->month = "Jul";
                    break;
                case 8:
                    $statement->month = "Aug";
                    break;
                case 9:
                    $statement->month = "Sep";
                    break;
                case 10:
                    $statement->month = "Oct";
                    break;
                case 11:
                    $statement->month = "Nov";
                    break;
                case 12:
                    $statement->month = "Dec";
                    break;
            }
        }

        // return $uploaded_bill;

        $total_count = count( $uploaded_statements );

        return view('document.bank_statement', compact( 'uploaded_statements', 'month', 'total_count'));
    }

    public function get_other_document(Request $request) 
    {

        if(auth()->user()->profile->financial_year_from && auth()->user()->profile->financial_year_to){

            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;

            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->whereBetween('created_at', [$from_date, $to_date])->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->whereBetween('created_at', [$from_date, $to_date])->where('status', 1)->orderBy('created_at', 'desc')->get();
        }
        if ($request->month == null && $request->year == null) {
            $month = "All";
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('status', 1)->orderBy('created_at', 'desc')->get();
        } 
        else if ($request->month == null){
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('year', $request->year)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('year', $request->year)->where('status', 1)->orderBy('created_at', 'desc')->get();
        } else if ($request->year == null) {
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('month', $request->month)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('month', $request->month)->where('status', 1)->orderBy('created_at', 'desc')->get();
        } else {
            $uploaded_bills = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('month', $request->month)->where('year', $request->year)->where('status', 0)->orderBy('created_at', 'desc')->get();

            $uploaded_bills_completed = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'other')->where('month', $request->month)->where('year', $request->year)->where('status', 1)->orderBy('created_at', 'desc')->get();
        }

        // return $uploaded_bills;

        foreach ($uploaded_bills as $bill) {
            switch($bill->month){
                case 1: $bill->month = "Jan"; break;
                case 2: $bill->month = "Feb"; break;
                case 3: $bill->month = "Mar"; break;
                case 4: $bill->month = "Apr"; break;
                case 5: $bill->month = "May"; break;
                case 6: $bill->month = "Jun"; break;
                case 7: $bill->month = "Jul"; break;
                case 8: $bill->month = "Aug"; break;
                case 9: $bill->month = "Sep"; break;
                case 10: $bill->month = "Oct"; break;
                case 11: $bill->month = "Nov"; break;
                case 12: $bill->month = "Dec"; break;
            }
        }

        if($request->month != null){
            switch($request->month){
                case 1: $month = "Jan"; break;
                case 2: $month = "Feb"; break;
                case 3: $month = "Mar"; break;
                case 4: $month = "Apr"; break;
                case 5: $month = "May"; break;
                case 6: $month = "Jun"; break;
                case 7: $month = "Jul"; break;
                case 8: $month = "Aug"; break;
                case 9: $month = "Sep"; break;
                case 10: $month = "Oct"; break;
                case 11: $month = "Nov"; break;
                case 12: $month = "Dec"; break;
            }
        }else {
            $month = 'All';
        }

        // return $uploaded_bill;
        $total_count = count($uploaded_bills);

        // View::share('sale_document_count', $total_count);

        return view('document.other', compact('uploaded_bills', 'uploaded_bills_completed','month', 'total_count'));
    }

    public function change_status ( Request $request ) {

        $uploaded_bill = UploadedBill::find($request->row_id);

        // return response()->json($uploaded_bill);

        $uploaded_bill->status = 1;

        if( $uploaded_bill->save() ) {
            echo "success";
        } else {
            echo "failure";
        }
    }

    public function get_upload_bank_statement_document( ) {
        return view('document.upload_bank_statement');
    }

    public function post_upload_bank_statement_document( Request $request ) 
    {
        $this->validate($request, [
            'bank_statement' => 'required'
        ]);

        if( $request->hasFile('bank_statement') ){

            $path = Storage::disk('public')->putFile('bank_statement', $request->file('bank_statement'));
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $upload_statement = new UploadBankStatement;

            $upload_statement->user_id = Auth::user()->id;

            $upload_statement->month = $request->month;
            $upload_statement->year = $request->year;

            $upload_statement->file_path = $path;
            $upload_statement->file_ext = $ext;

            if ( $upload_statement->save() ) {
                return response()->json(['success' => 'Statement uploaded successfully']);
            } else {
                return response()->json(['failure' => 'Failed to upload statement']);
            }

        }

    }

    public function get_expense_document()
    {
        return view('document.expense');
    }

    public function get_income_document()
    {
        return view('document.income');
    }

    public function get_payments_voucher()
    {
        return view('document.payments_voucher');
    }

    public function get_receipt_voucher()
    {
        return view('document.receipt_voucher');
    }

    public function get_cash_withdrawn_voucher()
    {

        $cash_withdraw_documents = CashWithdrawDocument::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        
        return view('document.cash_withdrawn_voucher', compact('cash_withdraw_documents'));
    }

    public function get_cash_deposit_voucher()
    {
        $cash_deposit_documents = CashDepositDocument::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();

        return view('document.cash_deposit_voucher', compact('cash_deposit_documents'));
    }

    public function get_additional_document(Request $request) {


        // if(auth()->user()->profile->financial_year_from && auth()->user()->profile->financial_year_to){

        //     $from_date = auth()->user()->profile->financial_year_from;
        //     $to_date = auth()->user()->profile->financial_year_to;

        //     $uploaded_bills = UploadedDocument::where('user_id', Auth::user()->id)->whereBetween('created_at', [$from_date, $to_date])->orderBy('created_at', 'desc')->get();

        // }

        $type = "All";

        if($request->type){
            $type = $request->type;
        }

        $query = UploadedDocument::where('user_id', Auth::user()->id);

        if ($request->month == null && $request->year == null) {
            $month = "All";
            $from_date = auth()->user()->profile->financial_year_from;
            $to_date = auth()->user()->profile->financial_year_to;

            $query = $query->whereBetween('created_at', [$from_date, $to_date]);
        }
        else if ($request->month == null){
            $query = $query->where('year', $request->year);

        } else if ($request->year == null) {
            $query = $query->where('month', $request->month);

        } else {
            $query = $query->where('month', $request->month)->where('year', $request->year);

        }

        if($type != "All"){
            $query = $query->where('type', $type);
        }

        $uploaded_statements = $query->orderBy('created_at', 'desc')->get();

        // return $uploaded_bills;

        foreach ($uploaded_statements as $bill) {
            switch($bill->month){
                case 1: $bill->month = "Jan"; break;
                case 2: $bill->month = "Feb"; break;
                case 3: $bill->month = "Mar"; break;
                case 4: $bill->month = "Apr"; break;
                case 5: $bill->month = "May"; break;
                case 6: $bill->month = "Jun"; break;
                case 7: $bill->month = "Jul"; break;
                case 8: $bill->month = "Aug"; break;
                case 9: $bill->month = "Sep"; break;
                case 10: $bill->month = "Oct"; break;
                case 11: $bill->month = "Nov"; break;
                case 12: $bill->month = "Dec"; break;
            }
        }

        if($request->month != null){
            switch($request->month){
                case 1: $month = "Jan"; break;
                case 2: $month = "Feb"; break;
                case 3: $month = "Mar"; break;
                case 4: $month = "Apr"; break;
                case 5: $month = "May"; break;
                case 6: $month = "Jun"; break;
                case 7: $month = "Jul"; break;
                case 8: $month = "Aug"; break;
                case 9: $month = "Sep"; break;
                case 10: $month = "Oct"; break;
                case 11: $month = "Nov"; break;
                case 12: $month = "Dec"; break;
            }
        } else {
            $month = 'All';
        }

        // return $uploaded_bill;
        $total_count = count($uploaded_statements);

        // View::share('sale_document_count', $total_count);

        return view('document.extra', compact('uploaded_statements', 'type', 'month', 'total_count'));
    }

    public function add_additional_document()
    {
        return view('document.upload_extra');
    }

    public function post_additional_document(Request $request)
    {
        $this->validate($request, [
            'document' => 'required|max:10000'
        ]);
        // commented this validation because now they want to upload images as well
        //mimes:pdf

        if( $request->hasFile('document') ){

            $path = Storage::disk('public')->putFile('document', $request->file('document'));

            $upload_statement = new UploadedDocument;

            $upload_statement->user_id = Auth::user()->id;

            $upload_statement->month = $request->month;
            $upload_statement->year = $request->year;
            $upload_statement->type = $request->type;

            $upload_statement->file_path = $path;

            if ( $upload_statement->save() ) {
                return response()->json(['success' => 'Document uploaded successfully']);
            } else {
                return response()->json(['failure' => 'Failed to upload document']);
            }

        }
    }

    public function delete_additional_document(Request $request, $id)
    {
        $uploaded_doc = UploadedDocument::findOrFail($id);
        $uploaded_doc->delete();

        return redirect()->back()->with('success', 'Document deleted successfully');
    }

    public function share_document_mail(Request $request, UploadedDocument $document)
    {
        $this->validate($request, [
            'senders' => 'required'
        ]);

        $senders = array_map('trim', explode(",", $request->senders));
        Mail::to('nishant@justconsult.us')->cc($senders)->send(new ShareDocument($document));
    }
}
