<?php

namespace App\Http\Middleware;

use Closure;
use App\UploadedBill;
use Illuminate\Support\Facades\Auth;

class SaleAndPurchaseDocumentCount
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

        if(Auth::check()){
            $sale_pending_document_count = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'sale')->where('status', 0)->orderBy('created_at', 'desc')->count();

            // dd($sale_pending_document_count);

            $purchase_pending_document_count = UploadedBill::where('user_id', Auth::user()->id)->where('type', 'purchase')->where('status', 0)->orderBy('created_at', 'desc')->count();

            view()->share('sale_pending_document_count', $sale_pending_document_count);
            view()->share('purchase_pending_document_count', $purchase_pending_document_count);
        }

        // dd(auth()->user());

        return $next($request);
    }
}
