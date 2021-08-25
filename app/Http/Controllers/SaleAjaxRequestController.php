<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleAjaxRequestController extends Controller
{
    public function check_invoice_date_validation(Request $request)
    {
        $invoice_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->invoice_date)));

        if (auth()->user()->profile->financial_year_from > $invoice_date) {
            return response()->json(['success' => false, 'message' => 'Invoice date should be between current financial year']);
        }

        if (auth()->user()->profile->financial_year_to < $invoice_date) {
            return response()->json(['success' => false, 'message' => 'Invoice date should be between current financial year']);
        }

        return response()->json(['success' => true]);
    }

    public function check_due_date_validation(Request $request)
    {
        $due_date = date('Y-m-d', strtotime(str_replace('/', '-', $request->due_date)));

        if (auth()->user()->profile->financial_year_from > $due_date) {
            return response()->json(['success' => false, 'message' => 'Due date should be between current financial year']);
        }

        if (auth()->user()->profile->financial_year_to < $due_date) {
            return response()->json(['success' => false, 'message' => 'Due date should be between current financial year']);
        }

        return response()->json(['success' => true]);
    }

    
}
