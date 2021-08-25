<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('uniqueInvoice', function ($attribute, $value, $parameters, $validator) {
            $party_id = array_get($validator->getData(), $parameters[0], null);

            $rows = DB::table('invoices')
                ->where('invoice_no', $value)
                ->get();

            foreach( $rows as $row ){
                if( $row->party_id == $party_id && Carbon::now()->format('Y') === Carbon::parse($row->invoice_date)->format('Y') ){
                    return false;
                }
            }
            
            return true;
        }, 'Please provide unique Invoice no for current financial year');

        Validator::extend('uniqueBillForParty', function ($attribute, $value, $parameters, $validator) {
            $party_id = array_get($validator->getData(), $parameters[0], null);
            $user = auth()->user() ?? DB::table('users')->where('id', $parameters[1])->first();

            $rows = DB::table('purchase_records')
                ->where('bill_no', $value)
                ->get();

            foreach($rows as $row) {
                if(  $row->party_id == $party_id && $user->profile->financial_year_from <= $row->bill_date && $user->profile->financial_year_to >= $row->bill_date ) {
                    return false;
                }
            }

            return true;
        }, 'Please provide unique Bill no for selected party in the current financial year');

        Validator::extend('validOpeningDate', function ($attribute, $value, $parameters, $validator) {

            if ( Carbon::createFromFormat('d/m/Y', $value)->between(Carbon::parse(auth()->user()->profile->financial_year_from), Carbon::parse(auth()->user()->profile->financial_year_to) ) ) {
                return true;
            } else {
                return false;
            }

        }, 'Please provide valid opening balance date');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
