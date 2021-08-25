<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('validate-invoice-no', 'SaleController@validate_sale_invoice_no')->name('api.validate.invoiceno');
Route::post('validate-sale-order-no', 'SaleController@validate_sale_order_voucher_no')->name('api.validate.saleorderno');
Route::post('validate-sale-party-payment-voucher-no', 'SaleController@validate_sale_party_payment_voucher_no')->name('api.validate.sale.party.payment.voucherno');
Route::post('validate-sale-payment-voucher-no', 'SaleController@validate_sale_payment_voucher_no')->name('api.validate.sale.payment.voucherno');

Route::post('validate-bill-no', 'PurchaseController@validateBillNo')->name('api.validate.billno');
Route::post('validate-purchase-order-no', 'PurchaseController@validate_purchase_order_voucher_no')->name('api.validate.purchaseorderno');
Route::post('validate-purchase-party-payment-voucher-no', 'PurchaseController@validate_purchase_party_payment_voucher_no')->name('api.validate.purchase.party.payment.voucherno');
Route::post('validate-purchase-payment-voucher-no', 'PurchaseController@validate_purchase_payment_voucher_no')->name('api.validate.purchase.payment.voucherno');

Route::post('validate-cash-withdraw-voucher-no', 'CashController@validate_cash_withdraw_voucher_no')->name('api.validate.cash.withdraw.voucherno');
Route::post('validate-cash-deposit-voucher-no', 'CashController@validate_cash_deposit_voucher_no')->name('api.validate.cash.deposit.voucherno');

Route::post('validate-gst-payment-voucher-no', 'GSTController@validate_gst_payment_voucher_no')->name('api.validate.gst.payment.voucherno');

Route::get('/fetch-item', 'ItemController@fetch_item')->name('api.fetch.item');


Route::post('validate-invoice-starting-no', 'UserController@validate_invoice_starting_no')->name('api.validate.invoice.startingno');
Route::post('validate-purchase-order-starting-no', 'UserController@validate_purchase_order_starting_no')->name('api.validate.purchase.order.startingno');
Route::post('validate-sale-order-starting-no', 'UserController@validate_sale_order_starting_no')->name('api.validate.sale.order.startingno');
Route::post('validate-payment-starting-no', 'UserController@validate_payment_starting_no')->name('api.validate.payment.startingno');
Route::post('validate-receipt-starting-no', 'UserController@validate_receipt_starting_no')->name('api.validate.receipt.startingno');
Route::post('validate-contra-starting-no', 'UserController@validate_contra_starting_no')->name('api.validate.contra.startingno');
Route::post('validate-gst-payment-starting-no', 'UserController@validate_gst_payment_starting_no')->name('api.validate.gst.payment.startingno');
Route::post('validate-note-starting-no', 'UserController@validate_note_starting_no')->name('api.validate.note.startingno');

Route::post('validate-sale-credit-note-no', 'SaleController@unique_invoice_credit_note_no')->name('api.sale.creditnote.validate.noteno');
Route::post('validate-sale-debit-note-no', 'SaleController@unique_invoice_debit_note_no')->name('api.sale.debitnote.validate.noteno');

Route::post('validate-purchase-credit-note-no', 'PurchaseController@unique_bill_credit_note_no')->name('api.purchase.creditnote.validate.noteno');
Route::post('validate-purchase-debit-note-no', 'PurchaseController@unique_bill_debit_note_no')->name('api.purchase.debitnote.validate.noteno');


Route::post('/login', 'Api\MobileApiController@login');

Route::get('/items-inventory-report', 'Api\MobileApiController@items_report');

Route::get('/sales-report', 'Api\MobileApiController@sales_report');

Route::get('/purchases-report', 'Api\MobileApiController@purchases_report');

Route::get('/b2b-purchases-report', 'Api\MobileApiController@b2b_purchases');

Route::get('/b2b-sales-report', 'Api\MobileApiController@b2b_sales');

Route::get('/debtor-report', 'Api\MobileApiController@debtor_report');

Route::get('/creditor-report', 'Api\MobileApiController@creditor_report');

// -----------------------------------------------------


Route::get('get-party', 'Api\MobileApiController@get_party');

Route::get('get-party-data', 'Api\MobileApiController@get_party_data');

Route::get('get-bill-by-party', 'Api\MobileApiController@get_bill_by_party');

Route::get('get-purchase-amounts', 'Api\MobileApiController@get_purchase_amounts');

Route::get('get-invoice-by-party', 'Api\MobileApiController@get_invoice_by_party');

Route::get('get-sale-amounts', 'Api\MobileApiController@get_sale_amounts');

// -------------------------------------------------------------

Route::get('get-bank', 'Api\MobileApiController@get_bank');

Route::post('post-bank', 'Api\MobileApiController@post_bank');

// --------------------------------------------------------------

Route::post('upload-bill-images', 'Api\MobileApiController@upload_purchase_bill_images');

// --------------------------------------------------------------

Route::get('item-list', 'Api\MobileApiController@get_item_list');

Route::get('item-list-data', 'Api\MobileApiController@get_item_list_data');

Route::get('summary-data', 'Api\MobileApiController@get_summary_data');

Route::get('sold-data', 'Api\MobileApiController@get_sold_data');

Route::get('purchase-data', 'Api\MobileApiController@get_purchase_data');

Route::get('json-report-data', 'Api\MobileApiController@get_json_report_data');


//---------------------------------------------------------------------------------


Route::get('document', 'Api\MobileApiController@get_document');

Route::post('delete-document', 'Api\MobileApiController@delete_document');

// Route::get('document/purchase', 'Api\MobileApiController@get_purchase_document');

Route::post('cash-withdrawn', 'Api\MobileApiController@cash_withdrawn');

Route::post('cash-deposited', 'Api\MobileApiController@cash_deposited');

Route::post('purchase-pending-payment', 'Api\MobileApiController@purchase_pending_payment');

Route::post('sale-pending-payment', 'Api\MobileApiController@sale_pending_payment');

Route::post('sale-party-pending-payment', 'Api\MobileApiController@sale_party_pending_payment');

Route::post('purchase-party-pending-payment', 'Api\MobileApiController@purchase_party_pending_payment');

Route::get('gst-return', 'Api\MobileApiController@gst_return');

Route::get('home', 'Api\MobileApiController@combined_data');

Route::get('fetch-user-items', 'Api\MobileApiController@send_user_added_items');

Route::get('fetch-specific-item', 'Api\MobileApiController@send_specific_item_detail');

Route::post('save-invoice', 'Api\MobileApiController@store_to_invoice');

Route::post('save-bill', 'Api\MobileApiController@store_to_bill');

Route::get('get-unique-invoice-no', 'Api\MobileApiController@provide_unique_invoice_no');

Route::get('get-unique-bill-no', 'Api\MobileApiController@provide_unique_bill_no');

Route::get('get-transporters-detail', 'Api\MobileApiController@get_transporter_details');

Route::get('get-all-groups', 'Api\MobileApiController@get_all_groups');

Route::get('get-gst-list', 'Api\MobileApiController@get_gst_list');

Route::get('get-measuring-unit-list', 'Api\MobileApiController@get_measuring_unit_list');

Route::post('store-item', 'Api\MobileApiController@store_item');

Route::get('generate-cashbook', 'Api\MobileApiController@generate_cashbook');

Route::get('generate-bankbook', 'Api\MobileApiController@generate_bankbook');

Route::post('cash-in-hand', 'Api\MobileApiController@post_cash_in_hand');

Route::post('update-bank-opening-balance', 'Api\MobileApiController@update_opening_balance');

Route::get('debtor-report', 'Api\MobileApiController@debtor_report');

Route::get('creditor-report', 'Api\MobileApiController@creditor_report');

Route::get('view-party', 'Api\MobileApiController@view_party');

Route::get('view-item', 'Api\MobileApiController@view_item');

Route::get('view-item-data', 'Api\MobileApiController@view_item_data');

Route::get('stock-summary', 'Api\MobileApiController@stock_summary');

Route::post('store-party', 'Api\MobileApiController@store_party');

Route::get('get-state-list', 'Api\MobileApiController@get_state_list');

Route::get('get-invoices', 'Api\MobileApiController@get_invoices');

Route::get('get-bills', 'Api\MobileApiController@get_bills');

Route::get('app-summary-data', 'Api\MobileApiController@get_app_summary_data');

Route::get('sales-account', 'Api\MobileApiController@sales_account');

Route::get('purchases-account', 'Api\MobileApiController@purchases_account');

Route::get('get-additional-documents', 'Api\MobileApiController@get_additional_document');

Route::post('generate-ewaybill', 'Api\MobileApiController@save_invoice_transport_detail');

Route::post('cancel-ewaybill', 'Api\MobileApiController@cancel_eway');

Route::get('show-ewaybill', 'Api\MobileApiController@show_eway');

Route::get('profile', 'Api\MobileApiController@profile');
Route::post('profile', 'Api\MobileApiController@store_profile');
