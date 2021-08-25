<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();


Route::get('profile', 'UserController@profile')->name('user.profile');
Route::post('profile', 'UserController@store_profile')->name('user.profile.store');

Route::post('remove-logo', 'UserController@remove_logo')->name('user.profile.remove.logo');
Route::post('remove-authorized-signature', 'UserController@remove_signature')->name('user.profile.remove.authorized.signature');

Route::get('invoice-settings', 'UserController@invoice_setting')->name('invoice.setting');
Route::post('invoice-settings', 'UserController@save_invoice_setting')->name('save.invoice.setting');

Route::get('purchase-settings', 'UserController@purchase_setting')->name('purchase.setting');
Route::post('purchase-settings', 'UserController@save_purchase_setting')->name('save.purchase.setting');

Route::get('purchase-order-settings', 'UserController@purchase_order_setting')->name('purchase.order.setting');
Route::post('purchase-order-settings', 'UserController@save_purchase_order_setting')->name('save.purchase.order.setting');

Route::get('sale-order-settings', 'UserController@sale_order_setting')->name('sale.order.setting');
Route::post('sale-order-settings', 'UserController@save_sale_order_setting')->name('save.sale.order.setting');

Route::get('payment-settings', 'UserController@payment_setting')->name('payment.setting');
Route::post('payment-settings', 'UserController@save_payment_setting')->name('save.payment.setting');

Route::get('receipt-settings', 'UserController@receipt_setting')->name('receipt.setting');
Route::post('receipt-settings', 'UserController@save_receipt_setting')->name('save.receipt.setting');

Route::get('cash-withdraw-settings', 'UserController@cash_withdraw_setting')->name('cash.withdraw.setting');
Route::post('cash-withdraw-settings', 'UserController@save_cash_withdraw_setting')->name('save.cash.withdraw.setting');

Route::get('cash-deposit-settings', 'UserController@cash_deposit_setting')->name('cash.deposit.setting');
Route::post('cash-deposit-settings', 'UserController@save_cash_deposit_setting')->name('save.cash.deposit.setting');

Route::get('gst-payment-settings', 'UserController@gst_payment_setting')->name('gst.payment.setting');
Route::post('gst-payment-settings', 'UserController@save_gst_payment_setting')->name('save.gst.payment.setting');

Route::get('sale-select-option-settings', 'UserController@select_option_setting')->name('select.option.setting');
Route::post('sale-select-option-settings', 'UserController@save_select_option_setting')->name('save.select.option.setting');

Route::get('purchase-select-option-settings', 'UserController@purchase_select_option_setting')->name('purchase.select.option.setting');
Route::post('purchase-select-option-settings', 'UserController@save_purchase_select_option_setting')->name('save.purchase.select.option.setting');

Route::get('note-settings', 'UserController@note_setting')->name('note.setting');
Route::post('note-settings', 'UserController@save_note_setting')->name('save.note.setting');

Route::post('profile/round-off-setting', 'UserController@store_round_off_setting')->name('user.round.off.setting');

Route::group(['middleware' => ['datesInProfile', 'datesExpired']], function () {

    Route::get('/home', 'DashboardController@index')->name('home');

    Route::get('cash-in-hand', 'CashController@cash_in_hand')->name('cash.in.hand');
    Route::post('cash-in-hand', 'CashController@post_cash_in_hand')->name('post.cash.in.hand');

    Route::get('cash-deposit', 'CashController@cash_deposit')->name('cash.deposit');
    Route::post('cash-deposit', 'CashController@post_cash_deposit')->name('post.cash.deposit');
    Route::get('view-cash-deposit', 'CashController@view_cash_deposit')->name('view.cash.deposit');
    Route::post('update-cash-deposit-status/{id}', 'CashController@update_cash_deposit_status')->name('update.cash.deposit.status');

    Route::get('cash-withdraw', 'CashController@cash_withdraw')->name('cash.withdraw');
    Route::post('cash-withdraw', 'CashController@post_cash_withdraw')->name('post.cash.withdraw');
    Route::get('view-cash-withdraw', 'CashController@view_cash_withdraw')->name('view.cash.withdraw');
    Route::post('update-cash-withdraw-status/{id}', 'CashController@update_cash_withdraw_status')->name('update.cash.withdraw.status');

    Route::get('bank-to-bank-transfer', 'BankController@bank_to_bank_transfer')->name('bank.to.bank.transfer');
    Route::post('bank-to-bank-transfer', 'BankController@post_bank_to_bank_transfer')->name('post.bank.to.bank.transfer');
    Route::get('view-bank-to-bank-transfer', 'BankController@view_bank_to_bank_transfer')->name('view.bank.to.bank.transfer');
    Route::get('bank-to-bank-transfer/{id}/edit', 'BankController@edit_bank_to_bank_transfer')->name('edit.bank.to.bank.transfer');
    Route::put('bank-to-bank-transfer/{id}/edit', 'BankController@update_bank_to_bank_transfer')->name('update.bank.to.bank.transfer');
    Route::post('update-bank-to-bank-transfer-status/{id}', 'BankController@update_bank_to_bank_transfer_status')->name('update.bank.to.bank.transfer.status');

    Route::get('view-contra', 'CashController@view_contra')->name('view.contra');


    Route::get('day-book', 'DayBookController@generate_day_book')->name('day.book');

    Route::get('sales-register', 'SaleController@generate_sales_register')->name('sales.register');

    Route::resource('group', 'GroupController');

    Route::resource('item', 'ItemController');

    Route::get('export-items', 'ItemController@export_as_excel')->name('export.item.to.excel');

    Route::get('stock-summary-report', 'ItemController@inventory_report')->name('item.report');

    Route::get('item-value', 'ItemController@item_value')->name('item.value');

    Route::resource('party', 'PartyController');

    Route::get('export-parties', 'PartyController@export_as_excel')->name('export.party.to.excel');

    Route::get('insurance');

    Route::resource('sale', 'SaleController', [
        'except' => [
            'edit', 'update', 'destroy'
        ]
    ]);

    Route::resource('transporter', 'TransporterController', ['except' => [
        'edit', 'update', 'destroy'
    ]]);

    Route::resource('bank', 'BankController', ['except' => [
        'destroy'
    ]]);

    Route::get('edit/opening-balance/bank/{id}', 'BankController@edit_form_opening_balance')->name('edit.bank.opening.balance');
    Route::put('opening-balance/bank/{id}', 'BankController@update_opening_balance')->name('update.bank.opening.balance');

    Route::resource('insurance', 'InsuranceController', ['except' => [
        'edit', 'update', 'destroy'
    ]]);

    Route::resource('purchase', 'PurchaseController');

    Route::get('purchase/filter/date', 'PurchaseController@filter_by_date')->name('purchase.filter.by.date');

    Route::get('purchase/filter/party', 'PurchaseController@filter_by_party')->name('purchase.filter.by.party');

    Route::get('purchase/filter/bill', 'PurchaseController@filter_by_bill')->name('purchase.filter.by.bill');

    Route::get('purchase/bill/{bill_no}', 'PurchaseController@show_purchase_bill')->name('show.purchase.bill');

    /** ----------------------------------------------------------------------------------------- */

    Route::get('/create/purchase/order', 'PurchaseController@create_purchase_order')->name('purchase.order');

    Route::post('/store/purchase/order', 'PurchaseController@store_purchase_order')->name('store.purchase.order');

    Route::get('/view/purchase-order/all', 'PurchaseController@view_all_purchase_order')->name('view.all.purchase.order');

    Route::get('/view/purchase/order/{purchase_order_no}', 'PurchaseController@view_purchase_order')->name('view.purchase.order');

    Route::get('/print/purchase/order/{purchase_order_no}', 'PdfController@print_purchase_order')->name('print.purchase.order');

    Route::patch('update-purchase-order-status/{purchase_order}', 'PurchaseController@update_purchase_order_status')->name('update.purchase.order.status');

    Route::get('/purchase/create/{purchase_order_no}', 'PurchaseController@create_purchase_from_order')->name('create.purchase.from.order');

    Route::get('/purchase/edit/order/{purchase_order_no}', 'PurchaseController@edit_purchase_order')->name('edit.purchase.order');

    Route::post('/purchase/update/order', 'PurchaseController@update_purchase_order')->name('update.purchase.order');

    Route::post('/purchase/update/order/remains/{purchase_order_no}', 'PurchaseController@update_purchase_order_remains')->name('update.purchase.order.remains');

    Route::post('/purchase/order/row/store', 'PurchaseController@store_purchase_order_single_row')->name('store.purchase.order.single.row');

    Route::post('find-purchase-order-no', 'PurchaseController@find_purchase_order_no')->name('api.search.purchase.order.name');

    Route::post('find-party-name', 'PartyController@find_party_name')->name('api.search.party.name');

    Route::post('find-invoice-no', 'SaleController@find_invoice_no')->name('api.search.invoice.no');

    /** ----------------------------------------------------------------------------------------- */

    Route::get('/create/sale/order', 'SaleController@create_sale_order')->name('sale.order');

    Route::post('/store/sale/order', 'SaleController@store_sale_order')->name('store.sale.order');

    Route::get('/view/sale-order/all', 'SaleController@view_all_sale_order')->name('view.all.sale.order');

    Route::get('/view/sale/order/{sale_order_no}', 'SaleController@view_sale_order')->name('view.sale.order');

    Route::get('/print/sale/order/{sale_order_no}', 'PdfController@print_sale_order')->name('print.sale.order');

    Route::get('/sale/create/{sale_order_no}', 'SaleController@create_sale_from_order')->name('create.sale.from.order');

    Route::patch('update-sale-order-status/{sale_order}', 'SaleController@update_sale_order_status')->name('update.sale.order.status');

    Route::get('/sale/edit/order/{sale_order_no}', 'SaleController@edit_sale_order')->name('edit.sale.order');

    Route::post('/sale/update/order', 'SaleController@update_sale_order')->name('update.sale.order');

    Route::post('/sale/update/order/remains/{sale_order_no}', 'SaleController@update_sale_order_remains')->name('update.sale.order.remains');

    Route::post('/sale/order/row/store', 'SaleController@store_sale_order_single_row')->name('store.sale.order.single.row');

    Route::post('suggest-sale-order-no', 'SaleController@suggest_sale_order_no')->name('api.search.new.sale.order.name');

    Route::post('find-sale-order-no', 'SaleController@find_sale_order_no')->name('api.search.sale.order.name');

    /** ----------------------------------------------------------------------------------------- */

    Route::get('ledger', 'LedgerController@index')->name('view.ledger');

    Route::get('tax/purchase/{purchase_id}', 'TaxController@calculate_purchase_tax')->name('show.tax.purchase');

    Route::get('tax/purchase', 'TaxController@all_purchase_taxes')->name('all.tax.purchase');

    Route::get('impersonate', 'ImpersonateUser@show_all_users')->name('show.all.impersonatable');

    Route::get('impersonate/user/{id}', 'ImpersonateUser@impersonate_user')->name('impersonate.user');

    Route::get('leave/impersonation', 'ImpersonateUser@leave_impersonation')->name('leave.impersonation');

    Route::get('find-purchases-by-party', 'PurchaseController@find_purchase_by_party')->name('find.purchase.by.party');
    Route::post('find-purchases-by-party', 'PurchaseController@post_find_purchase_by_party')->name('post.find.purchase.by.party');
    Route::get('get-bill-info/{bill}/party/{party}', 'PurchaseController@get_purchase_bill')->name('get.purchase.bill');
    Route::post('add-purchase-pending-payment', 'PurchaseController@post_pending_payment')->name('post.pending.payment');
    Route::post('update-purchase-pending-payment/{id}', 'PurchaseController@update_pending_payable_detail')->name('update.pending.payable.detail');
    Route::post('update-purchase-party-pending-payment/{id}', 'PurchaseController@update_party_pending_payable_detail')->name('update.party.pending.payable.detail');

    Route::get('find-invoices-by-party', 'SaleController@find_invoice_by_party')->name('find.invoice.by.party');
    Route::post('find-invoices-by-party', 'SaleController@post_find_invoice_by_party')->name('post.find.invoice.by.party');
    Route::get('get-invoice-info/{invoice}/party/{party}', 'SaleController@get_sale_invoice')->name('get.sale.invoice');
    Route::post('add-sale-pending-payment', 'SaleController@post_pending_payment')->name('post.sale.pending.payment');
    Route::post('update-sale-pending-payment/{id}', 'SaleController@update_pending_receivable_detail')->name('update.pending.receivable.detail');
    Route::post('update-sale-party-pending-payment/{id}', 'SaleController@update_party_pending_receivable_detail')->name('update.party.pending.receivable.detail');

    Route::get('view-pending-receivable', 'SaleController@view_pending_receivable')->name('view.pending.receivable');
    Route::get('post-view-pending-receivable', 'SaleController@get_pending_receivable')->name('get.pending.receivable');
    Route::post('update-sale-pending-receivable-status/{id}', 'SaleController@update_sale_pending_receivable_status')->name('update.sale.pending.receivable.status');
    Route::post('update-party-sale-pending-receivable-status/{id}', 'SaleController@update_party_sale_pending_receivable_status')->name('update.party.sale.pending.receivable.status');
    Route::get('view-pending-receivable-detail/{id}', 'SaleController@view_pending_receivable_detail')->name('view.pending.receivable.detail');
    Route::get('view-party-pending-receivable-detail/{id}', 'SaleController@view_party_pending_receivable_detail')->name('view.party.pending.receivable.detail');

    Route::get('view-pending-payable', 'PurchaseController@view_pending_payable')->name('view.pending.payable');
    Route::get('post-view-pending-payable', 'PurchaseController@get_pending_payable')->name('get.pending.payable');
    Route::post('update-purchase-pending-payable-status/{id}', 'PurchaseController@update_purchase_pending_payable_status')->name('update.purchase.pending.payable.status');
    Route::post('update-party-purchase-pending-payable-status/{id}', 'PurchaseController@update_party_purchase_pending_payable_status')->name('update.party.purchase.pending.payable.status');
    Route::get('view-pending-payable-detail/{id}', 'PurchaseController@view_pending_payable_detail')->name('view.pending.payable.detail');
    Route::get('view-party-pending-payable-detail/{id}', 'PurchaseController@view_party_pending_payable_detail')->name('view.party.pending.payable.detail');

    Route::post('find-party-detail', 'PartyController@post_find_party_detail')->name('post.find.party.details');

    Route::post('fetch-item-by-barcode', 'SaleController@post_fetch_item_by_barcode')->name('post.fetch.item.by.barcode');

    Route::post('fetch-party-billing-address', 'PartyController@post_fetch_party_billing_address')->name('post.fetch.party.billing.address');

    Route::get('credit-or-debit-purchase-note', 'PurchaseController@get_note_view')->name('purchase.note');
    Route::get('bill-detail-debit-note/{bill_no}', 'PurchaseController@bill_detail_debit_note')->name('bill.detail.debit.note');
    Route::post('bill-create-debit-note/{bill_no}', 'PurchaseController@bill_create_debit_note')->name('bill.create.debit.note');
    Route::get('bill-detail-credit-note/{bill_no}', 'PurchaseController@bill_detail_credit_note')->name('bill.detail.credit.note');
    Route::post('bill-create-credit-note/{bill_no}', 'PurchaseController@bill_create_credit_note')->name('bill.create.credit.note');
    Route::put('edit-purchase-qty', 'PurchaseController@edit_purchase_qty')->name('edit.purchase.qty');
    Route::post('get-row-by-bill', 'PurchaseController@get_row_by_bill')->name('get.row.by.bill');
    Route::post('create-update-purchase-debit-note', 'PurchaseController@create_or_update_debit_note')->name('purchase.create.or.update.debit.note');
    Route::post('create-update-purchase-credit-note', 'PurchaseController@create_or_update_credit_note')->name('purchase.create.or.update.credit.note');

    Route::post('delete-purchase-credit-note', 'PurchaseController@delete_credit_note')->name('purchase.delete.credit.note');
    Route::post('delete-purchase-debit-note', 'PurchaseController@delete_debit_note')->name('purchase.delete.debit.note');

    Route::put('update-purchase-bill-note', 'PurchaseController@update_purchase_bill_note')->name('update.purchase.bill.note');

    Route::get('purchase-bill-note/{bill_no}', 'PurchaseController@purchase_bill_note')->name('purchase.bill.note');


    //here
    Route::get('purchase-debit-note-edit/{note_no}', 'PurchaseController@edit_debit_note')->name('purchase.debit.note.edit');

    Route::get('purchase-credit-note-edit/{note_no}', 'PurchaseController@edit_credit_note')->name('purchase.credit.note.edit');

    Route::get('show/purchase-credit-note/{note_no}', 'PurchaseController@show_credit_note')->name('show.purchase.credit.note');

    Route::get('show/purchase-debit-note/{note_no}', 'PurchaseController@show_debit_note')->name('show.purchase.debit.note');

    Route::post('update-purchase-credit-note-item', 'PurchaseController@update_credit_note_item')->name('update.purchase.credit.note.item');
    Route::post('update-purchase-credit-note', 'PurchaseController@update_credit_note')->name('update.purchase.credit.note');

    Route::post('update-purchase-debit-note-item', 'PurchaseController@update_debit_note_item')->name('update.purchase.debit.note.item');
    Route::post('update-purchase-debit-note', 'PurchaseController@update_debit_note')->name('update.purchase.debit.note');



    Route::get('sale-debit-note-edit/{note_no}', 'SaleController@edit_debit_note')->name('sale.debit.note.edit');

    Route::get('sale-credit-note-edit/{note_no}', 'SaleController@edit_credit_note')->name('sale.credit.note.edit');

    Route::get('show/sale-credit-note/{note_no}', 'SaleController@show_credit_note')->name('show.sale.credit.note');

    Route::get('show/sale-debit-note/{note_no}', 'SaleController@show_debit_note')->name('show.sale.debit.note');

    Route::post('update-sale-credit-note-item', 'SaleController@update_credit_note_item')->name('update.sale.credit.note.item');
    Route::post('update-sale-credit-note', 'SaleController@update_credit_note')->name('update.sale.credit.note');

    Route::post('update-sale-debit-note-item', 'SaleController@update_debit_note_item')->name('update.sale.debit.note.item');
    Route::post('update-sale-debit-note', 'SaleController@update_debit_note')->name('update.sale.debit.note');

    /** ----------------------------------------------------------------------------------------- */

    Route::get('purchase-bill-regular/{bill_no}', 'PurchaseController@bill_type_regular')->name('purchase.bill.type.regular');
    Route::get('purchase-bill-cancel/{bill_no}', 'PurchaseController@bill_type_cancel')->name('purchase.bill.type.cancel');

    /** ----------------------------------------------------------------------------------------- */

    Route::get('sale-bill-regular/{id}', 'SaleController@bill_type_regular')->name('sale.bill.type.regular');
    Route::get('sale-bill-cancel/{id}', 'SaleController@bill_type_cancel')->name('sale.bill.type.cancel');

    /** ----------------------------------------------------------------------------------------- */

    Route::get('credit-or-debit-sale-note', 'SaleController@get_note_view')->name('sale.note');
    Route::get('invoice-detail-credit-note/{invoice_id}', 'SaleController@invoice_detail_credit_note')->name('invoice.detail.credit.note');
    Route::post('invoice-create-credit-note/{invoice_id}', 'SaleController@invoice_create_credit_note')->name('invoice.create.credit.note');
    Route::get('invoice-detail-debit-note/{invoice_id}', 'SaleController@invoice_detail_debit_note')->name('invoice.detail.debit.note');
    Route::post('invoice-create-debit-note/{invoice_id}', 'SaleController@invoice_create_debit_note')->name('invoice.create.debit.note');
    Route::put('edit-sale-qty', 'SaleController@edit_sale_qty')->name('edit.invoice.qty');
    Route::post('get-row-by-invoice', 'SaleController@get_row_by_invoice')->name('get.row.by.invoice');
    Route::post('create-update-sale-credit-note', 'SaleController@create_or_update_credit_note')->name('sale.create.or.update.credit.note');
    Route::post('create-update-sale-debit-note', 'SaleController@create_or_update_debit_note')->name('sale.create.or.update.debit.note');
    Route::get('list-invoice-debit-note/{invoice_id}', 'SaleController@list_invoice_debit_note')->name('list.invoice.debit.note');
    Route::get('list-invoice-credit-note/{invoice_id}', 'SaleController@list_invoice_credit_note')->name('list.invoice.credit.note');

    Route::get('list-bill-debit-note/{bill_id}', 'PurchaseController@list_bill_debit_note')->name('list.bill.debit.note');
    Route::get('list-bill-credit-note/{bill_id}', 'PurchaseController@list_bill_credit_note')->name('list.bill.credit.note');

    Route::post('delete-sale-credit-note', 'SaleController@delete_credit_note')->name('sale.delete.credit.note');
    Route::post('delete-sale-debit-note', 'SaleController@delete_debit_note')->name('sale.delete.debit.note');

    Route::put('update-sale-bill-note', 'SaleController@update_sale_bill_note')->name('update.sale.bill.note');

    Route::get('sale-bill-note/{bill_no}', 'SaleController@sale_bill_note')->name('sale.bill.note');


    Route::get('expense', 'ExpenseController@show_form')->name('show.expense.form');
    Route::post('expense', 'ExpenseController@store_expense')->name('store.expense');

    Route::get('income', 'IncomeController@show_form')->name('show.income.form');
    Route::post('income', 'IncomeController@store_income')->name('store.income');

    Route::post('add-commission-to-bill', 'PurchaseController@add_commission_to_bill')->name('add.commission.to.bill');
    Route::post('add-commission-to-invoice', 'SaleController@add_commission_to_invoice')->name('add.commission.to.invoice');
    Route::post('commision-to-all', 'SaleController@add_commission_to_all')->name('commision.to.all');


    Route::get('purchase-report', 'PurchaseController@purchase_report')->name('purchase.report');
    Route::get('sale-report', 'SaleController@sale_report')->name('sale.report');

    Route::get('b2b-purchase', 'PurchaseController@b2b_purchase')->name('b2b.purchase');
    Route::get('b2b-sale', 'SaleController@b2b_sale')->name('b2b.sale');
    // Route::impersonate();

    Route::get('measuring-unit', 'MeasuringunitController@create')->name('measuringunit.create');
    Route::post('measuring-unit', 'MeasuringunitController@store')->name('measuringunit.store');

    //--------------------------------------------------------------------------

    Route::get('hsn-sale-report', 'ReportController@hsn_sale')->name('hsn.sale.report');

    Route::get('hsn-purchase-report', 'ReportController@hsn_purchase')->name('hsn.purchase.report');

    Route::get('tax-paid-report', 'ReportController@tax_on_purchase')->name('tax.paid.report');

    Route::get('tax-collected-report', 'ReportController@tax_on_sale')->name('tax.collected.report');

    Route::get('debtor-report', 'ReportController@debtor_report')->name('debtor.report');

    Route::get('creditor-report', 'ReportController@creditor_report')->name('creditor.report');

    Route::post('add-pending-payment-to-party-sale', 'SaleController@add_pending_payment_to_party')->name('add.pending.payment.to.party.sale');

    Route::post('add-pending-payment-to-party-purchase', 'PurchaseController@add_pending_payment_to_party')->name('add.pending.payment.to.party.purchase');

    Route::get('manage-inventory', 'ItemController@manage_inventory')->name('manage.inventory');

    Route::post('manage-inventory', 'ItemController@post_manage_inventory')->name('post.manage.inventory');

    Route::get('manage-inventory/view-all', 'ItemController@view_all_manage_inventory')->name('view.manage.inventory');

    Route::get('manage-inventory/edit/{id}', 'ItemController@edit_manage_inventory')->name('manage.inventory.edit');
    Route::put('manage-inventory/edit/{id}', 'ItemController@update_manage_inventory')->name('manage.inventory.update');

    Route::delete('manage-inventory/delete/{id}', 'ItemController@delete_manage_inventory')->name('manage.inventory.delete');


    Route::get('document/sale', 'DocumentController@get_sale_document')->name('sale.document');

    Route::get('document/purchase', 'DocumentController@get_purchase_document')->name('purchase.document');

    Route::get('document/other', 'DocumentController@get_other_document')->name('other.document');

    Route::get('document/bank-statement', 'DocumentController@get_bank_statement_document')->name('bank.statement.document');

    Route::get('document/expense', 'DocumentController@get_expense_document')->name('expense.document');

    Route::get('document/income', 'DocumentController@get_income_document')->name('income.document');

    Route::get('document/payments-voucher', 'DocumentController@get_payments_voucher')->name('payments.voucher.document');

    Route::get('document/receipt-voucher', 'DocumentController@get_receipt_voucher')->name('receipt.voucher.document');

    Route::get('document/cash-withdrawn-from-bank', 'DocumentController@get_cash_withdrawn_voucher')->name('cash.withdrawn.document');

    Route::get('document/cash-deposit-in-bank', 'DocumentController@get_cash_deposit_voucher')->name('cash.deposit.document');

    Route::post('document/change/status', 'DocumentController@change_status')->name('api.change.document.status');

    Route::get('gst-report', 'GSTController@get_gst_report')->name('gst.report');

    Route::get('gst-purchase-report', 'GSTController@get_gst_purchase_report')->name('gst.purchase.report');

    Route::get('gst-input-report', 'GSTController@get_gst_input_report')->name('gst.input.report');

    Route::get('gst-output-report', 'GSTController@get_gst_output_report')->name('gst.output.report');

    Route::get('sale-reference-name-report', 'ReportController@sale_reference_name_report')->name('sale.reference.name.report');

    Route::get('purchase-reference-name-report', 'ReportController@purchase_reference_name_report')->name('purchase.reference.name.report');

    Route::get('item-wise-report', 'ReportController@item_wise_report')->name('item.wise.report');

    Route::get('gst-ledger', 'GSTController@show_gst_ledger')->name('gst.ledger');
    Route::get('calculated-gst-ledger', 'GSTController@show_calculated_gst_ledger')->name('gst.calculated.ledger');
    Route::get('gst-computation', 'GSTController@show_gst_computation')->name('gst.computation');
    Route::post('gst-computation', 'GSTController@post_gst_computation')->name('post.gst.computation');
    Route::get('gst-paid-in-cash', 'GSTController@gst_paid_in_cash')->name('gst.paid.in.cash');
    Route::post('gst-paid-in-cash', 'GSTController@post_gst_paid_in_cash')->name('post.gst.paid.in.cash');
    Route::get('gst-setoff', 'GSTController@gst_setoff')->name('gst.setoff');
    Route::get('find-gst-setoff', 'GSTController@show_setoff_dates')->name('find.gst.setoff');
    Route::get('show-gst-setoff', 'GSTController@show_gst_setoff')->name('show.gst.setoff');
    Route::get('advance-payment/{id}/edit', 'GSTController@edit_advance_payment')->name('edit.advance.payment');
    Route::put('advance-payment/{id}/edit', 'GSTController@update_advance_payment')->name('update.advance.payment');

    Route::post('gst-setoff', 'GSTController@post_gst_setoff')->name('post.gst.setoff');

    Route::get('ineligible-reversal-of-input', 'GSTController@ineligible_reversal_of_input')->name('gst.reversal.of.input');
    Route::post('ineligible-reversal-of-input', 'GSTController@post_ineligible_reversal_of_input')->name('post.gst.reversal.of.input');

    Route::get('find-ineligible-reversal-of-input', 'GSTController@show_ineligible_reversal_of_input_dates')->name('find.gst.reversal.of.input');
    Route::get('show-ineligible-reversal-of-input', 'GSTController@show_ineligible_reversal_of_input')->name('show.gst.reversal.of.input');

    Route::get('gst-composition', 'GSTController@gst_composition')->name('gst.composition');

    Route::post('gst-payable', 'GSTController@post_gst_payable')->name('gst.payable');
    Route::post('gst-to-be-paid-in-cash', 'GSTController@post_gst_to_be_paid_in_cash')->name('gst.to.be.paid.in.cash');


    Route::get('cash-book', 'CashController@generate_cash_book')->name('cash.book');

    Route::get('bank-book/{bank}', 'BankController@generate_bank_book')->name('bank.book');

    Route::post('search-party-by-name', 'PartyController@search_party_by_name')->name('api.search.party.by.name');

    Route::post('search-item-by-name', 'ItemController@search_item_by_name')->name('api.search.item.by.name');

    Route::get('upload-bank-statement', 'DocumentController@get_upload_bank_statement_document')->name('get.upload.bank.statement.document');

    Route::post('upload-bank-statement', 'DocumentController@post_upload_bank_statement_document')->name('post.upload.bank.statement.document');

    Route::get('additional-documents', 'DocumentController@get_additional_document')->name('get.additional.document');

    Route::get('add-additional-documents', 'DocumentController@add_additional_document')->name('add.additional.document');

    Route::post('add-additional-documents', 'DocumentController@post_additional_document')->name('post.additional.document');

    Route::delete('additional-documents/{id}', 'DocumentController@delete_additional_document')->name('delete.additional.document');

    Route::post('share-document-mail/{id}', 'DocumentController@share_document_mail')->name('share.document.mail');


    Route::get('import-banks', 'BankController@get_import_to_table')->name('get.import.bank');
    Route::post('import-banks', 'BankController@post_import_to_table')->name('post.import.bank');

    Route::get('import-parties', 'PartyController@get_import_to_table')->name('get.import.party');
    Route::post('import-parties', 'PartyController@post_import_to_table')->name('post.import.party');

    Route::get('import-inventories', 'ItemController@get_import_to_table')->name('get.import.inventory');
    Route::post('import-inventories', 'ItemController@post_import_to_table')->name('post.import.inventory');

    Route::post('import-groups', 'GroupController@post_import_to_table')->name('post.import.group');

    Route::get('party-report', 'PartyController@party_report')->name('party.report');
    Route::get('purchase-data-report', 'PurchaseController@purchase_data_report')->name('purchase.data.report');
    Route::get('items-report', 'ItemController@item_report')->name('items.report');
    Route::get('pending-payment-report', 'SaleController@pending_payment_report')->name('pending.payment.report');
    Route::get('item-value-report', 'ItemController@item_value_report')->name('item.value.report');
    Route::get('credit-debit-note-report', 'ReportController@credit_debit_note_report')->name('credit.debit.note.report');

    Route::get('amount-in-bank-report', 'ReportController@amount_in_bank_report')->name('amount.in.bank.report');
    Route::get('amount-as-cash-report', 'ReportController@amount_as_cash_report')->name('amount.as.cash.report');
    Route::get('cash-in-hand-report', 'ReportController@cash_in_hand_report')->name('cash.in.hand.report');
    Route::get('cash-deposit-report', 'ReportController@cash_deposit_report')->name('cash.deposit.report');
    Route::get('cash-withdraw-report', 'ReportController@cash_withdraw_report')->name('cash.withdraw.report');

    /**-------------------------------------------------------------------------------------------------------- */

    Route::get('sale/all/invoices', 'SaleController@show_all_invoices')->name('sale.user.invoices');

    Route::get('sale/edit/invoice/{id}', 'SaleController@edit_invoice_form')->name('edit.invoice.form');

    Route::post('sale/update/invoice/item', 'SaleController@update_invoice_item')->name('update.invoice.item.form');

    Route::post('sale/update/invoice/{invoice}', 'SaleController@update_invoice')->name('update.invoice.form');

    // invoice individual column update
    Route::post('sale/invoice/column', 'SaleController@update_invoice_individual_column')->name('update.invoice.column');   

    /**--------------------------- */

    Route::get('purchase/all/bills', 'PurchaseController@show_all_bills')->name('purchase.user.bills');

    Route::get('purchase/edit/bill/{bill_no}', 'PurchaseController@edit_bill_form')->name('edit.bill.form');

    Route::post('purchase/update/bill/item', 'PurchaseController@update_bill_item')->name('update.bill.item.form');

    Route::post('purchase/update/bill/{bill}', 'PurchaseController@update_bill')->name('update.bill.form');


    // bill individual column update
    Route::post('purchase/bill/column', 'PurchaseController@update_bill_individual_column')->name('update.bill.column');        


    /**---------------------------------------------------------------- */

    Route::get('show/bank/all', 'BankController@bank_all')->name('bank.all');

    /** ------------------sale pending-------------------------------------------------------------------------- */

    Route::get('edit/sale-pending-payment/{id}', 'SaleController@edit_sale_pending_payment_form')->name('edit.sale.pending.payment');

    Route::put('update/sale-pending-payment/{id}', 'SaleController@update_sale_pending_payment')->name('update.sale.pending.payment');

    Route::put('cancel-sale-payment/{id}', 'SaleController@cancel_sale_payment')->name('cancel.sale.payment');

    /**----------------purchase pending--------------------- */

    Route::get('edit/purchase-pending-payment/{id}', 'PurchaseController@edit_purchase_pending_payment_form')->name('edit.purchase.pending.payment');

    Route::put('update/purchase-pending-payment/{id}', 'PurchaseController@update_purchase_pending_payment')->name('update.purchase.pending.payment');

    Route::put('cancel-purchase-payment/{id}', 'PurchaseController@cancel_purchase_payment')->name('cancel.purchase.payment');

    /**-----------------sale party pending------------------- */

    Route::get('edit/sale-party-pending-payment/{id}', 'SaleController@edit_sale_party_pending_payment_form')->name('edit.sale.party.pending.payment');

    Route::put('update/sale-party-pending-payment/{id}', 'SaleController@update_sale_party_pending_payment')->name('update.sale.party.pending.payment');

    Route::put('cancel-sale-party-payment/{id}', 'SaleController@cancel_sale_party_payment')->name('cancel.sale.party.payment');

    /**-------------------purchase party pending----------------- */

    Route::get('edit/purchase-party-pending-payment/{id}', 'PurchaseController@edit_purchase_party_pending_payment_form')->name('edit.purchase.party.pending.payment');

    Route::put('update/purchase-party-pending-payment/{id}', 'PurchaseController@update_purchase_party_pending_payment')->name('update.purchase.party.pending.payment');

    Route::put('cancel-purchase-party-payment/{id}', 'PurchaseController@cancel_purchase_party_payment')->name('cancel.purchase.party.payment');

    /**-------------------cash withdraw----------------- */

    Route::get('edit/cash-withdraw/{id}', 'CashController@edit_cash_withdraw_form')->name('edit.cash.withdraw');

    Route::put('update/cash-withdraw/{id}', 'CashController@update_cash_withdraw')->name('update.cash.withdraw');

    /**-------------------cash deposit----------------- */

    Route::get('edit/cash-deposit/{id}', 'CashController@edit_cash_deposit_form')->name('edit.cash.deposit');

    Route::put('update/cash-deposit/{id}', 'CashController@update_cash_deposit')->name('update.cash.deposit');

    /**-------------------cash deposit----------------- */

    Route::get('sales-account', 'SaleController@sales_account')->name('sale.account');

    Route::post('export-sales-account', 'SaleController@export_sales_account')->name('export.sale.account');

    Route::get('purchases-account', 'PurchaseController@purchases_account')->name('purchase.account');

    Route::post('export-purchases-account', 'PurchaseController@export_purchases_account')->name('export.purchase.account');


    Route::post('cash-ledger-balance', 'GSTController@save_cash_ledger_balance')->name('save.cash.ledger.balance');

    Route::put('cash-ledger-balance', 'GSTController@update_cash_ledger_balance')->name('edit.cash.ledger.balance');

    Route::post('credit-ledger-balance', 'GSTController@save_credit_ledger_balance')->name('save.credit.ledger.balance');

    Route::put('credit-ledger-balance', 'GSTController@update_credit_ledger_balance')->name('edit.credit.ledger.balance');

    Route::post('liability-ledger-balance', 'GSTController@save_liability_ledger_balance')->name('save.liability.ledger.balance');

    Route::put('liability-ledger-balance/{id}', 'GSTController@update_liability_ledger_balance')->name('update.liability.ledger.balance');


    Route::get('sale-gst-report', 'SaleController@sale_gst_report')->name('sale.gst.report');
    Route::get('export-sale-gst-report', 'SaleController@export_sale_gst_report')->name('export.sale.gst.report');

    Route::get('purchase-gst-report', 'PurchaseController@purchase_gst_report')->name('purchase.gst.report');
    Route::get('export-purchase-gst-report', 'PurchaseController@export_purchase_gst_report')->name('export.purchase.gst.report');

    Route::get('ewaybill/provide-details', 'EwaybillController@provide_details_form')->name('ewaybill.provide.details.form');
    Route::post('ewaybill/provide-details', 'EwaybillController@post_provide_details_form')->name('post.ewaybill.provide.details.form');
    Route::get('ewaybills/create', 'EwaybillController@create')->name('eway.bill.create');
    Route::get('ewaybills/all', 'EwaybillController@index')->name('eway.bill.all');
    Route::post('ewaybills/{ewaybill_no}', 'EwaybillController@cancel_eway')->name('eway.bill.cancel');
    Route::get('ewaybills/{id}/show', 'EwaybillController@show')->name('eway.bill.show');
    Route::patch('ewaybills/{id}/status/update', 'EwaybillController@update')->name('eway.bill.update');

    Route::post('transporter-detail', 'EwaybillController@save_invoice_transport_detail')->name('save.invoice.transport.detail');
    Route::post('additional-charges', 'EwaybillController@save_invoice_additional_charges')->name('save.invoice.additional.charges');
});

Route::group(['middlewareGroups' => 'web'], function () {

    Route::post('add-item-extra-info', 'PurchaseAjaxController@add_item_extra_info')->name('api.add.item.extra.info');

    Route::post('find-item-extra-info', 'PurchaseAjaxController@find_item_extra_info')->name('api.find.item.extra.info');

    Route::post('add-short-rate-info', 'PurchaseAjaxController@add_short_rate_info')->name('api.add.short.rate.info');

    Route::post('remove-all-extra-item-data', 'PurchaseAjaxController@remove_all_extra_item_data')->name('api.remove.all.extra.item.data');

    Route::post('add-additional-charges', 'PurchaseAjaxController@add_additional_charges')->name('api.add.additional.charges');

    Route::post('add-transporter-details', 'PurchaseAjaxController@add_transporter_details')->name('api.add.transporter.details');

    Route::post('add-item-cess', 'PurchaseAjaxController@add_item_cess')->name('api.add.item.cess');

    Route::post('search-item-by-keyword', 'PurchaseAjaxController@search_item_by_keyword')->name('api.search.item.by.keyword');

    Route::post('invoice-date-validation', 'SaleAjaxRequestController@check_invoice_date_validation')->name('api.invoice.date.validation');

    Route::post('due-date-validation', 'SaleAjaxRequestController@check_due_date_validation')->name('api.due.date.validation');

    Route::post('bill-date-validation', 'PurchaseAjaxController@check_bill_date_validation')->name('api.bill.date.validation');
});

Route::post("other-than-reverse-charge", 'GSTController@post_other_than_reverse_charge')->name('post.other.than.reverse.charge');

Route::post("reverse-charge", 'GSTController@post_reverse_charge')->name('post.reverse.charge');

Route::post("liability-charge", 'GSTController@post_liability_charge')->name('post.liability.charge');

//------------------------------------------------------

Route::post("add-cash-advance-payment", 'GSTController@add_cash_advance_payment')->name('post.advance.cash.payment');

Route::post("add-credit-advance-payment", 'GSTController@add_credit_advance_payment')->name('post.advance.credit.payment');

Route::post("add-liability-advance-payment", 'GSTController@add_liability_advance_payment')->name('post.advance.liability.payment');


Route::get('print-invoice/{invoice}', 'PdfController@print_invoice')->name('print.invoice');

Route::get('print-bill/{bill}', 'PdfController@print_bill')->name('print.bill');

Route::get('print-sale-credit-note/{note_no}', 'PdfController@print_sale_credit_note')->name('print.sale.credit.note');

Route::get('print-sale-debit-note/{note_no}', 'PdfController@print_sale_debit_note')->name('print.sale.debit.note');

Route::get('print-purchase-credit-note/{note_no}', 'PdfController@print_purchase_credit_note')->name('print.purchase.credit.note');

Route::get('print-purchase-debit-note/{note_no}', 'PdfController@print_purchase_debit_note')->name('print.purchase.debit.note');

Route::get('export-all-in-on-excel', 'SingleExcelExportController@index')->name('excel.export.index');

Route::get('export-single-excel', 'SingleExcelExportController@export_all_excel')->name('export.excel');

Route::get('item/{item}/report', 'ItemController@single_item_report')->name('single.item.report');

Route::get('item/{item}/stock-summary', 'ItemController@generate_stock_summary')->name('single.item.stock.summary');

Route::get('item/{item}/stock-summary-detail', 'ItemController@generate_stock_summary_detail')->name('single.item.stock.summary.detail');

Route::get('party/{party}/report', 'PartyController@single_party_report')->name('single.party.report');


Route::post('send-mail-invoice/{invoice}', 'SaleController@send_mail_to_invoice_holder')->name('send.mail.invoice');


Route::get('validate-two-dates', function () {

    $first_date = \Carbon\Carbon::createFromFormat('d/m/Y', request()->validate_against_date)->format('Y-m-d');
    $second_date = \Carbon\Carbon::createFromFormat('d/m/Y', request()->validate_date)->format('Y-m-d');

    $isDateValidated =  \Carbon\Carbon::parse($first_date) < \Carbon\Carbon::parse($second_date);

    return response()->json($isDateValidated);
})->name('validate.two.date');

Route::get('validate-date', function () {

    $date = \Carbon\Carbon::createFromFormat('d/m/Y', request()->date)->format('Y-m-d');

    $isDateValidated =  \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from) <= \Carbon\Carbon::parse($date) && \Carbon\Carbon::parse($date) <= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to);

    return response()->json($isDateValidated);
})->name('validate.financial.date');

Route::post('validate-item-name', function () {
    $data = App\Item::where('name', request()->name)->where('user_id', auth()->user()->id)->get();

    if ($data->count() > 0) {
        return response()->json(false);
    } else {
        return response()->json(true);
    }
})->name('validate.item.name');

Route::post('validate-party-name', function () {
    $data = App\Party::where('name', request()->name)->where('user_id', auth()->user()->id)->get();

    if ($data->count() > 0) {
        return response()->json(false);
    } else {
        return response()->json(true);
    }
})->name('validate.party.name');

Route::post('validate-bank-name', function () {
    $data = App\Bank::where('name', request()->name)->where('user_id', auth()->user()->id)->get();

    if ($data->count() > 0) {
        return response()->json(false);
    } else {
        return response()->json(true);
    }
})->name('validate.bank.name');

Route::post('update-credit-note-status', function () {
    $notes = App\CreditNote::where('note_no', request()->note_no)->get();

    foreach ($notes as $note) {
        if (request()->type == 'CANCEL') {
            $note->status = 0;
        } else {
            $note->status = 1;
        }

        $note->save();
    }

    return redirect()->back();
});

Route::post('update-credit-note-status/{note_no}', function ($note_no) {
    $notes = App\CreditNote::where('note_no', $note_no)->get();

    foreach ($notes as $note) {
        if (request()->type == 'CANCEL') {
            $note->status = 0;
        } else {
            $note->status = 1;
        }

        $note->save();
    }

    return redirect()->back();
})->name('update.credit.note.status');

Route::post('update-debit-note-status/{note_no}', function ($note_no) {
    $notes = App\DebitNote::where('note_no', $note_no)->get();

    foreach ($notes as $note) {
        if (request()->type == 'CANCEL') {
            $note->status = 0;
        } else {
            $note->status = 1;
        }

        $note->save();
    }

    return redirect()->back();
})->name('update.debit.note.status');


/**-------------------- Admin Routes -----------------------*/

Route::middleware(['admin.auth'])->group(function () {

    Route::get('create/gst', 'AdminController@create_gst')->name('create.gst');
    Route::post('store/gst', 'AdminController@store_gst')->name('store.gst');
    Route::get('gst', 'AdminController@show_gst')->name('show.gst');
    Route::get('edit/gst/{gst}', 'AdminController@edit_gst')->name('edit.gst');
    Route::put('gst/{id}', 'AdminController@update_gst')->name('update.gst');
    Route::delete('delete/gst/{id}', 'AdminController@delete_gst')->name('delete.gst');


    Route::get('view-users', 'AdminController@view_users')->name('user.view');
    Route::get('deactivate-user/{id}', 'AdminController@deactivate_user')->name('deactivate.user');
    Route::get('activate-user/{id}', 'AdminController@activate_user')->name('activate.user');

    Route::get('send-fcm-notification', 'PushNotificationController@show_notification_form')->name('notification.index');
    Route::post('send-fcm-notification', 'PushNotificationController@sendFCMPushNotification')->name('send.fcm.notification');
});

/*---------------- End Admin Routes -------------------------*/
