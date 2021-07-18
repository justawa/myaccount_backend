<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class isUserActive
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
        if( Auth::check() ){
            if( Auth::user()->status == 1 ){
                return $next($request);
            } else {
                Auth::logout();
                return redirect('/login')->with('failure', 'Your account is inactive or has been suspended.');
            }
        }
        
        return redirect('/login');
    }
}
