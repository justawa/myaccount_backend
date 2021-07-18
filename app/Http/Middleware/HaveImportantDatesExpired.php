<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class HaveImportantDatesExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if( Carbon::parse( Carbon::now() )->format('Y-m-d') > auth()->user()->profile->book_ending_on || Carbon::parse( Carbon::now() )->format('Y-m-d') > auth()->user()->profile->financial_year_to ){
        //     return redirect()->route('user.profile')->with('failure', 'Please update Books and Financial Year Dates');
        // }
        // return $next($request);


        if( Carbon::parse()->format('Y-m-d') <= auth()->user()->profile->financial_year_to ){
            return $next($request);
        }

        return redirect()->route('user.profile')->with('failure', 'Please update Books and Financial Year Dates');
    }
}
