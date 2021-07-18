<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Expense;
use App\Party;

class ExpenseController extends Controller
{
    public function show_form(){
        $parties = Party::where('user_id', Auth::user()->id)->get();
        return view('expense.show_form', compact('parties'));
    }

    public function store_expense(Request $request){
        $expense = new Expense;

        $expense->party_id = $request->party;
        $expense->bill_no = $request->bill;
        $expense->tax_info = $request->taxed;
        $expense->tds = $request->tds_percentage;
        $expense->gst_eligible = $request->gst_eligible;
        $expense->gst = $request->gst_percentage;

        if ($expense->save()) {
            return redirect()->back()->with('success', 'Expense saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add expense');
        }
    }
}
