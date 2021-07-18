<?php

namespace App\Http\Middleware;

use Closure;

class AreImportantDatesSetupInProfile
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
        if( auth()->user()->profile->book_beginning_from != null && auth()->user()->profile->financial_year_from != null && auth()->user()->profile->financial_year_to != null ){
            return $next($request);
        }
        
        return redirect()->route('user.profile')->with('failure', 'Please provide Books and Financial Year Dates');
    }
}
