<?php

Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', route('home'));
});

Breadcrumbs::register('cash-book', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Cash Book', route('cash.book'));
});

Breadcrumbs::register('cash-withdraw', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Cash Withdraw', route('cash.withdraw'));
});

Breadcrumbs::register('cash-deposit', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Cash Deposit', route('cash.deposit'));
});

Breadcrumbs::register('show-bank-all', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Show All Bank', route('bank.all'));
});

Breadcrumbs::register('bank-book', function ($breadcrumbs, $bank_id) {
    $bank = App\Bank::find($bank_id);
    $breadcrumbs->parent('show-bank-all');
    $breadcrumbs->push($bank->name, route('bank.book', $bank->id));
});

Breadcrumbs::register('cash-in-hand', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Cash In Hand', route('cash.in.hand'));
});

Breadcrumbs::register('create-party', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Add Party', route('party.create'));
});

Breadcrumbs::register('party', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Parties', route('party.index'));
});

Breadcrumbs::register('party-edit', function ($breadcrumbs, $party_id) {
    $party = App\Party::find($party_id);
    $breadcrumbs->parent('party');
    $breadcrumbs->push($party->name, route('party.edit', $party_id));
});

Breadcrumbs::register('create-bank', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Add Bank', route('bank.create'));
});

Breadcrumbs::register('bank', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Banks', route('bank.index'));
});

Breadcrumbs::register('bank-edit', function ($breadcrumbs, $bank_id) {
    $bank = App\Bank::find($bank_id);
    $breadcrumbs->parent('bank');
    $breadcrumbs->push($bank->name, route('bank.edit', $bank_id));
});

Breadcrumbs::register('create-group', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Add Group', route('group.create'));
});

Breadcrumbs::register('group', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Groups', route('group.index'));
});

Breadcrumbs::register('group-edit', function ($breadcrumbs, $group_id) {
    $group = App\Group::find($group_id);
    $breadcrumbs->parent('group');
    $breadcrumbs->push($group->name, route('group.edit', $group_id));
});

Breadcrumbs::register('create-item', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Add Item', route('item.create'));
});

Breadcrumbs::register('item', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Items', route('item.index'));
});

Breadcrumbs::register('manage-inventory', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Manage Physical Stock', route('manage.inventory'));
});

Breadcrumbs::register('create-invoice', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Create Invoice', route('sale.create'));
});

Breadcrumbs::register('sale', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Sales', route('sale.index'));
});

Breadcrumbs::register('show-all-invoices', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('All Invoices', route('sale.user.invoices'));
});

Breadcrumbs::register('edit-invoice', function ($breadcrumbs, $invoice_id) {
    $invoice = App\Invoice::find($invoice_id);
    $breadcrumbs->parent('show-all-invoices');
    $invoice_no = $invoice->invoice_no;

    if( $invoice->invoice_prefix != null ) {
        $invoice_no = $invoice->invoice_prefix . $invoice_no;
    }

    if( $invoice->invoice_suffix != null ) {
        $invoice_no = $invoice_no . $invoice->invoice_suffix;
    }

    $breadcrumbs->push($invoice_no, route('edit.invoice.form', $invoice_id));
});

Breadcrumbs::register('create-sale-order', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Create Sale Order', route('sale.order'));
});

Breadcrumbs::register('sale-order', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Sale Orders', route('view.all.sale.order'));
});

Breadcrumbs::register('view-sale-order', function ($breadcrumbs, $sale_order_no) {
    $breadcrumbs->parent('sale-order');
    $breadcrumbs->push($sale_order_no, route('view.sale.order', $sale_order_no));
});

Breadcrumbs::register('edit-sale-order', function ($breadcrumbs, $sale_order_no) {
    $breadcrumbs->parent('sale-order');
    $breadcrumbs->push($sale_order_no, route('edit.sale.order', $sale_order_no));
});

Breadcrumbs::register('create-invoice-from-sale-order', function ($breadcrumbs, $sale_order_no) {
    $breadcrumbs->parent('view-sale-order', $sale_order_no);
    $breadcrumbs->push('Create Invoice', route('create.sale.from.order', $sale_order_no));
});

Breadcrumbs::register('credit-or-debit-sale-note', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Credit/Debit Sale Note', route('sale.note'));
});

Breadcrumbs::register('invoice-detail-credit-note', function ($breadcrumbs, $invoice_id) {
    $invoice = App\Invoice::find($invoice_id);
    $breadcrumbs->parent('credit-or-debit-sale-note');
    $breadcrumbs->push($invoice->invoice_no, route('invoice.detail.credit.note', $invoice_id));
});

Breadcrumbs::register('invoice-detail-debit-note', function ($breadcrumbs, $invoice_id) {
    $invoice = App\Invoice::find($invoice_id);
    $breadcrumbs->parent('credit-or-debit-sale-note');
    $breadcrumbs->push($invoice->invoice_no, route('invoice.detail.debit.note', $invoice_id));
});

Breadcrumbs::register('find-invoices-by-party', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Pending Receivables', route('find.invoice.by.party'));
});

Breadcrumbs::register('get-invoice-info', function ($breadcrumbs, $invoice_id) {
    $invoice = App\Invoice::find($invoice_id);
    $breadcrumbs->parent('find-invoices-by-party');
    $breadcrumbs->push($invoice->invoice_no, route('get.sale.invoice', [$invoice_id, $invoice->party_id]));
});

Breadcrumbs::register('edit-sale-pending-payment', function ($breadcrumbs, $sale_remaining_amount_id) {
    $remaining_amount = App\SaleRemainingAmount::find($sale_remaining_amount_id);
    $breadcrumbs->parent('get-invoice-info', $remaining_amount->invoice_id);
    $breadcrumbs->push('Edit Pending Payment', route('edit.sale.pending.payment', $sale_remaining_amount_id));
});

Breadcrumbs::register('create-purchase', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Create Purchase', route('purchase.create'));
});

Breadcrumbs::register('purchase', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Purchases', route('purchase.index'));
});

Breadcrumbs::register('all-bills', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('All Bills', route('purchase.user.bills'));
});

Breadcrumbs::register('edit-bill', function ($breadcrumbs, $purchase_id) {
    $purchase = App\PurchaseRecord::find($purchase_id);
    $breadcrumbs->parent('all-bills');
    $breadcrumbs->push($purchase->bill_no, route('edit.bill.form', $purchase_id));
});

Breadcrumbs::register('create-purchase-order', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Create Purchase Order', route('purchase.order'));
});

Breadcrumbs::register('purchase-order', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Purchase Orders', route('view.all.purchase.order'));
});

Breadcrumbs::register('view-purchase-order', function ($breadcrumbs, $purchase_order_no) {
    $breadcrumbs->parent('purchase-order');
    $breadcrumbs->push($purchase_order_no, route('view.purchase.order', $purchase_order_no));
});

Breadcrumbs::register('edit-purchase-order', function ($breadcrumbs, $purchase_order_no) {
    $breadcrumbs->parent('purchase-order');
    $breadcrumbs->push($purchase_order_no, route('edit.purchase.order', $purchase_order_no));
});

Breadcrumbs::register('create-purchase-from-purchase-order', function ($breadcrumbs, $purchase_order_no) {
    $breadcrumbs->parent('view-purchase-order', $purchase_order_no);
    $breadcrumbs->push('Create Purchase', route('create.sale.from.order', $purchase_order_no));
});

Breadcrumbs::register('credit-or-debit-purchase-note', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Credit/Debit Purchase Note', route('purchase.note'));
});

Breadcrumbs::register('purchase-detail-credit-note', function ($breadcrumbs, $purchase_id) {
    $purchase = App\PurchaseRecord::find($purchase_id);
    $breadcrumbs->parent('credit-or-debit-purchase-note');
    $breadcrumbs->push($purchase->bill_no, route('bill.detail.debit.note', $purchase_id));
});

Breadcrumbs::register('purchase-detail-debit-note', function ($breadcrumbs, $purchase_id) {
    $purchase = App\PurchaseRecord::find($purchase_id);
    $breadcrumbs->parent('credit-or-debit-purchase-note');
    $breadcrumbs->push($purchase->bill_no, route('invoice.detail.debit.note', $purchase_id));
});

Breadcrumbs::register('find-purchases-by-party', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Pending Payments', route('find.purchase.by.party'));
});

Breadcrumbs::register('get-bill-info', function ($breadcrumbs, $purchase_id, $party_id) {
    $purchase = App\PurchaseRecord::find($purchase_id);
    $breadcrumbs->parent('find-purchases-by-party');
    $breadcrumbs->push($purchase->bill_no, route('get.purchase.bill', [$purchase_id, $party_id]));
});

Breadcrumbs::register('sale-document', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Sale Documents', route('sale.document'));
});

Breadcrumbs::register('purchase-document', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Purchase Documents', route('purchase.document'));
});

Breadcrumbs::register('other-document', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Other Documents', route('other.document'));
});

Breadcrumbs::register('bank-statement-document', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Bank Statements', route('bank.statement.document'));
});

Breadcrumbs::register('upload-bank-statement', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Upload Bank Statement', route('get.upload.bank.statement.document'));
});

Breadcrumbs::register('cash-withdrawn-from-bank', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Cash Withdrawn from Bank', route('cash.withdrawn.document'));
});

Breadcrumbs::register('cash-deposit-in-bank', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Cash Deposit in Bank', route('cash.deposit.document'));
});

Breadcrumbs::register('stock-summary', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Stock Summary Report', route('item.report'));
});

Breadcrumbs::register('gst-return', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('GST Return Report', route('b2b.sale'));
});

Breadcrumbs::register('debtor-report', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Debtor Report', route('debtor.report'));
});

Breadcrumbs::register('creditor-report', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Creditor Report', route('creditor.report'));
});

Breadcrumbs::register('gst-input-report', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('GST Input Report', route('gst.input.report'));
});

Breadcrumbs::register('gst-output-report', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('GST Output Report', route('gst.output.report'));
});

Breadcrumbs::register('gst-computation', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('GST Computation', route('gst.computation'));
});

Breadcrumbs::register('gst-ledger', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('GST Ledger', route('gst.ledger'));
});

Breadcrumbs::register('gst-paid-in-cash', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('GST Paid in Cash', route('gst.paid.in.cash'));
});

Breadcrumbs::register('edit-sale-party-pending-payment', function ($breadcrumbs, $row_id) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Edit Sale Party Pending Payment', route('edit.sale.party.pending.payment', $row_id));
});

Breadcrumbs::register('edit-purchase-party-pending-payment', function ($breadcrumbs, $row_id) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Edit Purchase Party Pending Payment', route('edit.purchase.party.pending.payment', $row_id));
});

Breadcrumbs::register('edit-cash-deposit', function ($breadcrumbs, $row_id) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Edit Cash Deposit', route('edit.cash.deposit', $row_id));
});

Breadcrumbs::register('edit-cash-withdraw', function ($breadcrumbs, $row_id) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Edit Cash Withdraw', route('edit.cash.withdraw', $row_id));
});

Breadcrumbs::register('day-book', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Day Book', route('day.book'));
});

Breadcrumbs::register('sales-account', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Sales Account', route('sale.account'));
});

Breadcrumbs::register('purchases-account', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Purchases Account', route('purchase.account'));
});