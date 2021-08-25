<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidOpeningDateServiceProvider extends ServiceProvider
{
    private $format = 'd/m/Y';
    private $toFormat = 'Y-m-d';
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Validator::extend('validOpeningDate', function ($attribute, $value, $parameters, $validator) {
        //     if(auth()->check()){
        //         if ( Carbon::parse(auth()->user()->profile->financial_year_from)->format($this->toFormat) > Carbon::createFromFormat($this->format,$value)->format($this->toFormat) && 
        //             Carbon::parse(auth()->user()->profile->financial_year_to)->format($this->toFormat) < Carbon::createFromFormat($this->format, $value)->format($this->toFormat) ) {
        //             return false;
        //         } else {
        //             return true;
        //         }
        //     }
        //     return false;
        // });

        // Validator::replacer('validOpeningDate', function ($message, $attribute, $rule, $parameters) {
        //     return str_replace($message, "Please provide valid opening balance date", $message);
        // });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
