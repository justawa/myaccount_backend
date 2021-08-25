<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;

use App\DutiesAndTaxes;
use App\Purchase;
use App\User;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculate_purchase_tax($purchase_id)
    {
        $purchase = Purchase::findOrFail($purchase_id);

        return view('tax.calculate_purchase_tax', compact('purchase'));
    }

    public function all_purchase_taxes()
    {

        $purchases = User::find(Auth::user()->id)->purchases;
        
        return view('tax.all_purchase_taxes', compact('purchases'));
    }

    
}
