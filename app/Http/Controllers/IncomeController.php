<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Income;
use App\Party;

class IncomeController extends Controller
{
    public function show_form()
    {
        $parties = Party::where('user_id', Auth::user()->id)->get();
        return view('income.show_form', compact('parties'));
    }

    public function store_income(Request $request)
    {
        $income = new Income;

        $income->party_id = $request->party;
        // $income->bill_no = $request->bill;
        // $income->tax_info = $request->tax_info;
        // $income->tds = $request->tds_percentage;
        // $income->gst_eligible = $request->gst_eligible;
        // $income->gst = $request->gst_percentage;

        $income->amount = $request->income_amount;

        if ($income->save()) {
            return redirect()->back()->with('success', 'Income saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add Income');
        }
    }
}
