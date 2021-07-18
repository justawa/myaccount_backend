<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\AccountList;
use App\Ledger;
use App\User;

class LedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $invoices = User::find(Auth::user()->id)->invoices;

        if( count($invoices) > 0 ){
            foreach($invoices as $invoice){
                $ledgers = Ledger::where('invoice_id', $invoice->id)->get();
            }
    
            $accounts = $ledgers->groupBy('account');
    
            foreach($accounts as $key => $grouped_accounts){
                foreach($grouped_accounts as $grouped_account){
                    
                    $particular = AccountList::find($grouped_account->particular);
        
                    $grouped_account->particular_name = $particular->name;
                }
                $account = AccountList::findOrFail($key);
                $grouped_accounts->name = $account->name;
            }
        } else {
            $accounts = [];
        }

        // return $formatted_accounts;

        // return $accounts;

        // return;
        return view('ledger.index', compact('accounts'));
    }
}
