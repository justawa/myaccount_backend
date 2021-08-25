<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<!-- CSRF Token -->
    	<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>@yield('title')</title>
		<!-- Styles -->
		<link href="{{ asset('css/app.css') }}" rel="stylesheet">
		<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

		{{-- Font Awesome --}}
		<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
		<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css">
		<style>
			body {
				font-family: 'Poppins', sans-serif;
				letter-spacing: 0.06em;
			}

			.container {
				width: 100%;
			}

			input[type="checkbox"][readonly] {
				pointer-events: none;
			}

			.modal .nav-tabs a[aria-expanded="true"]::before, .modal .nav-tabs a[aria-expanded="false"]::before {
				content: '';
			}

			.modal .nav-tabs a[aria-expanded="true"] {
				color: #3097D1;
				background: transparent;
			}

			.modal .nav-tabs a[aria-expanded="true"]:hover {
				background-color: #eeeeee;
			}

			@media only screen and (min-device-width : 320px) and (max-device-width : 768px) {

				.table th {
					font-size: 10px;
				}

				#dynamic-body td {
					table-layout: fixed;
					padding: 0px;
					text-align: center;
				}

				#dynamic-body input {
					padding: 1px;
				}
			}

			.auto{
				display: none;
				width: 100%;
				background-color: #fff;
				box-shadow: 0 0 10px #ccc;
				max-height: 250px;
				overflow-y: scroll;
			}

			.auto div{
				padding: 10px;
				border-bottom: 1px solid #ccc;
				cursor: pointer;
			}

			.auto div:hover{
				background-color: #f7f7f7;
			}

			.auto div:last-child{
				border-bottom: none;
			}

			.autosaleorder{
				display: none;
				width: 100%;
				background-color: #fff;
				box-shadow: 0 0 10px #ccc;
				max-height: 250px;
				overflow-y: scroll;
			}

			.autosaleorder div{
				padding: 10px;
				border-bottom: 1px solid #ccc;
				cursor: pointer;
			}

			.autosaleorder div:hover{
				background-color: #f7f7f7;
			}

			.autosaleorder > a {
				color: #636b6f
			}

			.autosaleorder > a:last-child > div{
				border-bottom: none;
			}

			.autopurchaseorder{
				display: none;
				width: 100%;
				background-color: #fff;
				box-shadow: 0 0 10px #ccc;
				max-height: 250px;
				overflow-y: scroll;
			}

			.autopurchaseorder div{
				padding: 10px;
				border-bottom: 1px solid #ccc;
				cursor: pointer;
			}

			.autopurchaseorder div:hover{
				background-color: #f7f7f7;
			}

			.autopurchaseorder > a {
				color: #636b6f
			}

			.autopurchaseorder > a:last-child > div{
				border-bottom: none;
			}

			.active{
				display: block;
				z-index: 9;
			}

			.edit-price, .edit-gst, .edit-qty {
				font-size: 12px;
				padding-top: 0;
			}

			.hidePrint{
				display: none;
			}

			/* progress bar */
			.progress-bar {
				height: 35px;
				width: 100%;
				border: 2px solid #000;
				background: white;
			}

			.progress-bar-fill {
				height: 100%;
				width: 0%;
				background: #c39247;
				display: flex;
				align-items: center;
				transition: width 0.25s;
			}

			.progress-bar-text {
				margin-left: 16px;
				font-weight: bold;
				color: black;
			}

			/* .scrollable-content */
			.scrollable-sidebar {
				-ms-overflow-style: none;
    			scrollbar-width: none;
			}

			/* .scrollable-content::-webkit-scrollbar */
			.scrollable-sidebar::-webkit-scrollbar {
  				display: none;
			}

		</style>

		<link rel="stylesheet" href="{{ asset('css/print.css') }}" media="print">
	</head>
	<body>
		@yield('draggable')
		<div class="wrapper">
			<!-- Sidebar Holder -->
			<nav class="scrollable-sidebar" id="sidebar" @yield('sidebar-active')>
				<div class="sidebar-header">
					<img src="{{ asset('images/my-account-logo.png') }}" style="display: block; margin: 0 auto; height: 100px;" />
				</div>
				<hr style="border-bottom: 1px solid #000; margin: 0;"/>
				{{-- <ul class="list-unstyled components">
				</ul> --}}

				<ul class="list-unstyled components">
					{{-- <p class="text-center">Sidebar Menus</p> --}}
					<li>
						{{-- <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false">Dashboard</a>
						<ul class="collapse list-unstyled" id="homeSubmenu">
							<li><a href="#">Home 1</a></li>
							<li><a href="#">Home 2</a></li>
							<li><a href="#">Home 3</a></li>
						</ul> --}}
						<a href="{{ route('home') }}"><i class="fa fa-tachometer" aria-hidden="true"></i>&nbsp;Dashboard</a>
					</li>
					<li>
						<a href="#inventorySubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-archive" aria-hidden="true"></i>&nbsp;Items</a>
						<ul class="collapse list-unstyled" id="inventorySubmenu">
							<li><a href="{{ route('group.create') }}">Add New Group</a></li>
							<li><a href="{{ route('group.index') }}">View all Groups</a></li>
							{{-- <li><a href="{{ route('measuringunit.create') }}">Add New Unit</a></li> --}}
							<li><a href="{{ route('item.create') }}">Add New Item</a></li>
							<li><a href="{{ route('item.index') }}">View Items</a></li>
							{{-- <li><a href="{{ route('item.value') }}">Value of Inventory</a></li> --}}
							{{-- @if(auth()->user()->profile->inventory_type != "without_inventory")
							<li><a href="{{ route('view.manage.inventory') }}">View All Physical Stock</a></li>
							<li><a href="{{ route('manage.inventory') }}">Manage Physical Stock</a></li>
							@endif --}}
							{{-- <li><a href="{{ route('get.import.inventory') }}">Import File</a></li> --}}
							{{-- {{ route('get.import.inventory') }} --}}
						</ul>
					</li>
					<li>
						<a href="#partySubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-user-circle-o" aria-hidden="true"></i>&nbsp;Parties</a>
						<ul class="collapse list-unstyled" id="partySubmenu">
							<li><a href="{{ route('party.create') }}">Add New Party</a></li>
							<li><a href="{{ route('party.index') }}">View Party Details</a></li>
							{{-- <li><a href="{{ route('get.import.party') }}">Import File</a></li> --}}
							{{-- {{ route('get.import.party') }} --}}
						</ul>
					</li>
					<li>
						<a href="#saleSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-shopping-bag" aria-hidden="true"></i>&nbsp;Sales</a>
						<ul class="collapse list-unstyled" id="saleSubmenu">
							<li><a href="{{ route('sale.create') }}">Create Sale/Invoice</a></li>
							<li><a href="{{ route('sale.index') }}">View Sale/Invoice</a></li>
							{{-- <li><a href="{{ route('sale.user.invoices') }}">View all Invoices</a></li> --}}
							<li><a href="{{ route('sale.order') }}">Create Sale Order</a></li>
							<li><a href="{{ route('view.all.sale.order') }}">View all Sale Orders</a></li>
							<li><a href="{{ route('sale.note') }}">Credit/Debit Note</a></li>
							<li><a href="{{ route('find.invoice.by.party') }}">Pending Receivable</a></li>
							{{-- <li><a href="{{ route('sale.report') }}">Sale Report</a></li> --}}
							<li><a href="{{ route('view.pending.receivable') }}">View Pending Receivable</a></li>
						</ul>
					</li>
					<li>
						<a href="#purchaseSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-shopping-basket" aria-hidden="true"></i>&nbsp;Purchases</a>
						<ul class="collapse list-unstyled" id="purchaseSubmenu">
							<li><a href="{{ route('purchase.create') }}">Create Purchase</a></li>
							<li><a href="{{ route('purchase.index') }}">View Purchase</a></li>
							<li><a href="{{ route('purchase.user.bills') }}">View all Bills</a></li>
							<li><a href="{{ route('purchase.order') }}">Create Purchase Order</a></li>
							<li><a href="{{ route('view.all.purchase.order') }}">View all Purchase Orders</a></li>
							{{-- <li><a href="{{ route('purchase.filter.by.date') }}">Filter Purchase by Date</a></li> --}}
							<li><a href="{{ route('purchase.note') }}">Credit/Debit Note</a></li>
							<li><a href="{{ route('find.purchase.by.party') }}">Pending Payable</a></li>
							{{-- <li><a href="{{ route('purchase.report') }}">Purchase Report</a></li> --}}
							{{-- <li><a href="{{ route('purchase.filter.by.party') }}">Filter Purchase by Party Name</a></li>
							<li><a href="{{ route('purchase.filter.by.bill') }}">Filter Purchase by Bill</a></li> --}}
							{{-- <li><a href="{{ route('all.tax.purchase') }}">View Taxes</a></li> --}}
							<li><a href="{{ route('view.pending.payable') }}">View Pending Payable</a></li>
						</ul>
					</li>
					<li>
						<a href="#cashSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-user-circle-o" aria-hidden="true"></i>&nbsp;Cash/Bank</a>
						<ul class="collapse list-unstyled" id="cashSubmenu">
							<li><a href="{{ route('cash.book') }}">Cash Book</a></li>
							<li><a href="{{ route('cash.withdraw') }}">Cash Withdrawn from Bank</a></li>
							<li><a href="{{ route('cash.deposit') }}">Cash Deposit in Bank</a></li>
							<li><a href="{{ route('bank.all') }}">Bank Book</a></li>
							<li><a href="{{ route('bank.to.bank.transfer') }}">Bank to Bank transfer</a></li>
							{{-- <li><a href="{{ route('cash.in.hand') }}">Cash in Hand</a></li>							 --}}

							{{-- <li><a href="{{ route('view.cash.deposit') }}">View Cash Deposit</a></li>
							<li><a href="{{ route('view.cash.withdraw') }}">View Cash Withdraw</a></li>
							<li><a href="{{ route('view.bank.to.bank.transfer') }}">View Bank to Bank transfer</a></li> --}}
							<li><a href="{{ route('view.contra') }}">View Contra</a></li>
						</ul>
					</li>
					{{-- <li>
						<a href="#ledgerSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-user-circle-o" aria-hidden="true"></i>&nbsp;Cash/Bank Ledger</a>
						<ul class="collapse list-unstyled" id="ledgerSubmenu">
							<li><a href="{{ route('amount.in.bank.report') }}">Bank Report</a></li>
							<li><a href="{{ route('amount.as.cash.report') }}">Cash Report</a></li>
							<li><a href="{{ route('cash.in.hand.report') }}">Cash in hand Report</a></li>
							<li><a href="{{ route('cash.deposit.report') }}">Cash Deposit Report</a></li>
							<li><a href="{{ route('cash.withdraw.report') }}">Cash Withdraw Report</a></li>
						</ul>
					</li> --}}
					
					<li>
						<a href="#bankSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-credit-card-alt" aria-hidden="true"></i>&nbsp;Bank</a>
						<ul class="collapse list-unstyled" id="bankSubmenu">
							<li><a href="{{ route('bank.create') }}">Add Bank</a></li>
							<li><a href="{{ route('bank.index') }}">View Bank Details</a></li>
							{{-- <li><a href="{{ route('get.import.bank') }}">Import File</a></li> --}}
							{{-- {{ route('get.import.bank') }} --}}
						</ul>
					</li>
					{{-- <li>
						<a href="{{ route('insurance.create') }}"><i class="fa fa-university" aria-hidden="true"></i>&nbsp;Add Insurance</a>
					</li>
					<li>
						<a href="{{ route('transporter.create') }}"><i class="fa fa-truck" aria-hidden="true"></i>&nbsp;Add Transporter</a>
					</li> --}}
					
					{{-- <li>
						<a href="{{ route('show.expense.form') }}"><i class="fa fa-usd" aria-hidden="true"></i>&nbsp;Expenses</a>
					</li>
					<li>
						<a href="{{ route('show.income.form') }}"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;Incomes</a>
					</li> --}}
					<li>
						<a href="#ewaybillSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-sticky-note" aria-hidden="true"></i>&nbsp;E-way Bill</a>
						<ul class="collapse list-unstyled" id="ewaybillSubmenu">
							<li><a href="{{ route('ewaybill.provide.details.form') }}">Provide Ewaybill Details</a></li>
							@if(auth()->user()->ewaybillDetail)
							<li><a href="{{ route('eway.bill.create') }}">Create E-way Bill</a></li>
							<li><a href="{{ route('eway.bill.all') }}">View E-way Bill</a></li>
							@endif
						</ul>
					</li>
					<li>
						<a href="#mydocumentSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-folder-open" aria-hidden="true"></i>&nbsp;Legal Documents</a>
						<ul class="collapse list-unstyled" id="mydocumentSubmenu">
							<li><a href="{{ route('get.additional.document') }}">View Documents</a></li>
							<li><a href="{{ route('add.additional.document') }}">Add Document</a></li>
						</ul>
					</li>
					<li>
						<a href="#documentSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-folder-open" aria-hidden="true"></i>&nbsp;Documents</a>
						<ul class="collapse list-unstyled" id="documentSubmenu">
							<li><a href="{{ route('sale.document') }}">Sale ({{ $sale_pending_document_count ? $sale_pending_document_count : 0 }})</a></li>
							<li><a href="{{ route('purchase.document') }}">Purchase ({{ $purchase_pending_document_count ? $purchase_pending_document_count : 0 }})</a></li>
							<li><a href="{{ route('other.document') }}">Other Document</a></li>
							{{-- <li><a href="#">Expense</a></li> --}}
							{{-- {{ route('expense.document') }} --}}
							{{-- <li><a href="#">Income</a></li> --}}
							{{-- {{ route('income.document') }} --}}
							<li><a href="{{ route('bank.statement.document') }}">Bank Statement</a></li>
							<li><a href="{{ route('get.upload.bank.statement.document') }}">Bank Statement Import File</a></li>
							{{-- <li><a href="#">Payments Voucher</a></li> --}}
							{{-- {{ route('payments.voucher.document') }} --}}
							{{-- <li><a href="#">Receipt Voucher</a></li> --}}
							{{-- {{ route('receipt.voucher.document') }} --}}
							<li><a href="{{ route('cash.withdrawn.document') }}">Cash withdrawl voucher from Bank</a></li>
							{{-- {{ route('cash.withdrawn.document') }} --}}
							<li><a href="{{ route('cash.deposit.document') }}">Cash deposit in Bank voucher</a></li>
							{{-- {{ route('cash.deposit.document') }} --}}
						</ul>
					</li>
					{{-- <li>
						<a href="#gstSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-percent" aria-hidden="true"></i>&nbsp;GST</a>
						<ul class="collapse list-unstyled" id="gstSubmenu">
							
							@if( auth()->user()->profile->registered != 0 )
							<li><a href="{{ route('gst.calculated.ledger') }}">Calculated GST Ledger</a></li>
							<li><a href="{{ route('gst.setoff') }}?month={{ \Carbon\Carbon::now()->format('m') }}&year={{ \Carbon\Carbon::now()->format('Y') }}">GST Setoff</a></li>
							<li><a href="{{ route('find.gst.setoff') }}">Show GST Setoff</a></li>
							@endif
							<li><a href="{{ route('gst.reversal.of.input') }}">Ineligible/Reversal of ITC</a></li>
							<li><a href="{{ route('find.gst.reversal.of.input') }}">Show Ineligible/Reversal of ITC</a></li>
						</ul>
					</li> --}}
					<!-- kept outside just for commenting -->
					{{-- <li><a href="{{ route('gst.computation') }}">GST Computation</a></li> --}}
					{{-- <li><a href="{{ route('gst.ledger') }}">GST Ledger</a></li> --}}
					{{-- <li><a href="{{ route('gst.paid.in.cash') }}">GST Payment</a></li> --}}
					{{-- <li><a href="{{ route('gst.composition') }}">GST Composition</a></li> --}}
					<li>
						<a href="#reportSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-line-chart" aria-hidden="true"></i>&nbsp;Reports</a>
						<ul class="collapse list-unstyled" id="reportSubmenu">
							<li><a href="{{ route('excel.export.index') }}">Export Single Excel</a></li>
							{{-- <li><a href="{{ route('sale.index') }}">Sale Report</a></li> --}}
							<li><a href="{{ route('sale.account') }}">Sales Account</a></li>
							@if( auth()->user()->profile->registered != 0 )
							<li><a href="{{ route('sale.gst.report') }}">Sale GST Report</a></li>
							@endif
							{{-- <li><a href="{{ route('purchase.index') }}">Purchase Report</a></li> --}}
							<li><a href="{{ route('purchase.account') }}">Purchases Account</a></li>
							<li><a href="{{ route('purchase.gst.report') }}">Purchase GST Report</a></li>
							<li><a href="{{ route('item.report') }}">Stock Summary</a></li>
							{{-- <li><a href="{{ route('sales.register') }}">Sales Register</a></li> --}}
							{{-- <li><a href="{{ route('day.book') }}">Day Book</a></li> --}}
							{{-- <li><a href="{{ route('b2b.sale') }}">GST Return</a></li> --}}
							<li><a href="{{ route('debtor.report') }}">Debtor</a></li>
							<li><a href="{{ route('creditor.report') }}">Creditor</a></li>
							{{-- <li><a href="{{ route('b2b.purchase') }}">B2B Purchase</a></li> --}}
							{{-- <li><a href="{{ route('b2b.sale') }}">B2B Sale</a></li> --}}
							{{-- <li><a href="{{ route('hsn.purchase.report') }}">HSN Purchase Report</a></li> --}}
							{{-- <li><a href="{{ route('hsn.sale.report') }}">HSN Sale Report</a></li> --}}
							{{-- <li><a href="{{ route('tax.paid.report') }}">Tax Paid Report</a></li> --}}
							{{-- <li><a href="{{ route('tax.collected.report') }}">Tax Collected Report</a></li> --}}
							{{-- <li><a href="{{ route('gst.report') }}">GST Sale Report</a></li> --}}
							{{-- <li><a href="{{ route('gst.purchase.report') }}">GST Purchase Report</a></li> --}}
							{{-- <li><a href="{{ route('party.report') }}">Party Report</a></li> --}}
							{{-- <li><a href="{{ route('purchase.data.report') }}">Purchase Report</a></li> --}}
							{{-- <li><a href="{{ route('items.report') }}">Item Report</a></li> --}}
							{{-- <li><a href="{{ route('pending.payment.report') }}">Pending Payment Report</a></li> --}}
							{{-- <li><a href="{{ route('item.wise.report') }}">Item Wise Report</a></li> --}}
							{{-- <li><a href="{{ route('item.value.report') }}">Item Value Report</a></li> --}}
							{{-- <li><a href="{{ route('credit.debit.note.report') }}">Credit Debit Note Report</a></li> --}}
							{{-- <li><a href="{{ route('gst.input.report') }}">GST Input</a></li>
							<li><a href="{{ route('gst.output.report') }}">GST Output</a></li> --}}
							<li><a href="{{ route('sale.reference.name.report') }}">Sale Reference Name</a></li>
							<li><a href="{{ route('purchase.reference.name.report') }}">Purchase Reference Name</a></li>
						</ul>
					</li>
					{{-- <li>
						<a href="#"><i class="fa fa-file-text" aria-hidden="true"></i>&nbsp;Taxes</a>
					</li> --}}
					
					<li>
						<a href="#"><i class="fa fa-tachometer" aria-hidden="true"></i>&nbsp;About</a>
					</li>
					<li>
						<a href="#"><i class="fa fa-paper-plane" aria-hidden="true"></i>&nbsp;Subscription</a>
					</li>
				</ul>

				{{-- <ul class="list-unstyled CTAs"> --}}
					{{-- <li> --}}
						{{-- <a class="article" href="{{ route('logout') }}"
							onclick="event.preventDefault();
							document.getElementById('logout-form').submit();">
							LOG OUT
						</a> --}}

						{{-- <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
							{{ csrf_field() }}
						</form> --}}
					{{-- </li> --}}
				{{-- </ul> --}}

				{{-- <ul class="list-unstyled CTAs">
					<li style="text-align: center">
						Helpline 1234567890
					</li>
				</ul> --}}
			</nav>

			<!-- Page Content Holder -->
			<div class="scrollable-content" id="content" style="max-height: 100vh; overflow-y:scroll; padding-top: 0">
				<div class="row" style="position: sticky; top: 0;z-index: 999;">
					<nav class="navbar navbar-default" @yield('header_style')>
						<div class="container-fluid">
							<div style="display: flex; justify-content: space-between; align-items: center;">
								<div class="navbar-header" style="flex-grow: 1">
									{{-- <button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn btn-sidebar-collapse @yield('sidebar-autoopen')" style="background: #333;">
										<i class="glyphicon glyphicon-align-left"></i>
										<span>Toggle Sidebar</span>
									</button> --}}
									@if( auth()->user()->profile->name != null )
										<h3 class="text-left text-uppercase" style="margin: 10px 0 0 0;">{{ auth()->user()->profile->name }}</h3>
										<p>{{ "(". \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from)->format('Y') . "-" . \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to)->format('y') . ")" }}</p>
									@endif
								</div>
								{{-- <div style="display: inline-block; width: 75%;">
									<h2 style="text-align: center">company_logo</h2>
								</div> --}}
								<div>Helpline 1234567890</div>
								<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
									{{-- style="float: right;" --}}
									
									<ul class="nav navbar-nav navbar-right">
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->name }} <span class="caret"></a>
											<ul class="dropdown-menu" role="menu">
												@if(Auth::user()->id == 1)
													@if (session('impersonated_by'))
														<li><a href="{{ route('leave.impersonation') }}">Leave Impersonation</a></li>
													@else
														<li><a href="{{ route('show.all.impersonatable') }}">Impersonate User</a></li>
													@endif
													<li class="divider"></li>
												@endif
												<li>
													<a href="{{ route('user.profile') }}">Profile</a>
												</li>
												<li>
													{{-- <button class="btn btn-link" id="btn_round_off_setting">Round Off Settings</button> --}}
													<a href="javascript:void(0)" id="btn_round_off_setting">Round Off Settings</a>
												</li>
												<li>
													<a href="{{ route('invoice.setting') }}">Invoice Settings</a>
												</li>
												{{-- <li>
													<a href="{{ route('purchase.setting') }}">Purchase Settings</a>
												</li> --}}
												<li>
													<a href="{{ route('purchase.order.setting') }}">Purchase Order Settings</a>
												</li>
												<li>
													<a href="{{ route('sale.order.setting') }}">Sale Order Settings</a>
												</li>
												<li>
													<a href="{{ route('payment.setting') }}">Payment Settings</a>
												</li>
												<li>
													<a href="{{ route('receipt.setting') }}">Receipt Settings</a>
												</li>
												<li>
													<a href="{{ route('cash.deposit.setting') }}">Contra Settings</a>
												</li>
												{{-- <li>
													<a href="{{ route('gst.payment.setting') }}">GST Payment Settings</a>
												</li> --}}
												<li>
													<a href="{{ route('note.setting') }}">Note Settings</a>
												</li>
												<li>
													<a href="{{ route('select.option.setting') }}">Sale More Settings</a>
												</li>
												<li>
													<a href="{{ route('purchase.select.option.setting') }}">Purchase More Settings</a>
												</li>
												<li>
													<a href="{{ route('logout') }}"
														onclick="event.preventDefault();
														document.getElementById('logout-form').submit();">
														Logout
													</a>
		
													<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
														{{ csrf_field() }}
													</form>
												</li>
											</ul>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</nav>
				</div>
				@yield('content')
			</div>

		</div>
		<!-- Wrapper end -->

		<div class="modal" id="opening_balance_modal">
			<div class="modal-dialog">
					<div class="modal-content">
							<div class="modal-header">
									<button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title">Add/Update Opening Balance</h4>
							</div>
							<div class="modal-body">
									<form method="POST" action="{{ route('post.cash.in.hand') }}">
											{{ csrf_field() }}
											<div class="row">
													<div class="col-md-4">
															<div class="form-group">
																	<label>Opening Balance </label>
																	<input type="text" class="form-control" placeholder="Opening Balance" name="opening_balance"  @if( isset( $cash_in_hand->opening_balance ) ) value="{{ $cash_in_hand->opening_balance }}" @endif required />
															</div>
													</div>
													<div class="col-md-4">
															<div class="form-group">
																	<label>Opening Balance Type</label>
																	<select class="form-control" name="balance_type" required >
																			<option @if( isset( $cash_in_hand->balance_type ) ) @if($cash_in_hand->balance_type == 'debitor') selected="selected" @endif @endif value="debitor">Debit</option>
																			<option @if( isset( $cash_in_hand->balance_type ) ) @if($cash_in_hand->balance_type == 'creditor') selected="selected" @endif @endif value="creditor">Credit</option>
																	</select>
															</div>
													</div>
													<div class="col-md-4">
															<div class="form-group">
																	<label>Opening Balance Date</label>
																	<input type="text" name="balance_date" id="balance_date" class="form-control custom_date" placeholder="DD/MM/YYYY" @if( isset($cash_in_hand->balance_date) ) value="{{ \Carbon\Carbon::parse($cash_in_hand->balance_date)->format('d/m/Y') }}" @endif @if( isset($cash_in_hand->balance_date) && \Carbon\Carbon::parse($cash_in_hand->balance_date) >= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_from) && \Carbon\Carbon::parse($cash_in_hand->balance_date) <= \Carbon\Carbon::parse(auth()->user()->profile->financial_year_to) ) readonly @endif required />
															</div>
													</div>
											</div>

											<div class="form-group">
													<label>Narration</label>
													<textarea class="form-control" placeholder="Narration" name="narration"> @if( isset($cash_in_hand->narration) ) {{ $cash_in_hand->narration }} @endif</textarea>
											</div>
											<button type="submit" class="btn btn-success" >Submit</button>
									</form>
							</div>
					</div>
			</div>
		</div>


		<div class="modal" id="custom-messenger">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close closeCustomModal" data-dismiss="modal" aria-label="Close">
							<i class="fa fa-window-close" aria-hidden="true"></i>
						</button>
						<h5 class="modal-title">Message</h5>
					</div>
					<div class="modal-body">
						<p id="customMsg"></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger closeCustomModal">Close</button>
					</div>
				</div>
			</div>
		</div>
		

		<div class="modal" id="modal_round_off_setting">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Round Off Settings</h4>
					</div>
					<div class="modal-body">
						<form method="POST" action="{{ route('user.round.off.setting') }}">
							{{ csrf_field() }}
							{{-- <div class="form-group">
								<button type="button" class="btn btn-success" id="add_account">Add Account +</button>
							</div>
							<div class="form-group" id="account_selection_block" style="display: none">
								<select class="form-control" id="account_selection">
									<option disabled selected>Select Account</option>
									<option value="purchase">Purchase</option>
									<option value="sale">Sale</option>
								</select>
							</div> --}}
							<div class="form-group">
								<label class="radio-inline"><input type="radio" id="transaction_type_purchase" name="transaction_type" value="purchase">Purchase</label>
								<label class="radio-inline"><input type="radio" id="transaction_type_sale" name="transaction_type" value="sale">Sale</label>
							</div>
							
							<div class="purchase_block" style="display: none;">
								
								<div class="form-group">
									<label class="radio-inline"><input type="radio" name="purchase_type" value="indirect_expense" @if(auth()->user()->roundOffSetting->purchase_type != null && auth()->user()->roundOffSetting->purchase_type == 'indirect_expense') checked @endif>Indirect Expense</label>
									<label class="radio-inline"><input type="radio" name="purchase_type" value="indirect_income" @if(auth()->user()->roundOffSetting->purchase_type != null && auth()->user()->roundOffSetting->purchase_type == 'indirect_income') checked @endif >Indirect Income</label>
								</div>
				
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" name="purchase_round_off_item[]" value="item_amount" @if(auth()->user()->roundOffSetting->purchase_item_amount != null && auth()->user()->roundOffSetting->purchase_item_amount == 'yes') checked @endif>Item Amount</label>
									</div>
									<div class="checkbox">
										<label><input type="checkbox" name="purchase_round_off_item[]" value="gst_amount" @if(auth()->user()->roundOffSetting->purchase_gst_amount != null && auth()->user()->roundOffSetting->purchase_gst_amount == 'yes') checked @endif>GST Amount</label>
									</div>
									<div class="checkbox">
										<label><input type="checkbox" name="purchase_round_off_item[]" value="total_amount" @if(auth()->user()->roundOffSetting->purchase_total_amount != null && auth()->user()->roundOffSetting->purchase_total_amount == 'yes') checked @endif>Total Amount</label>
									</div>
									{{-- <div class="checkbox">
										<label><input type="checkbox" value="item_price">Item Price</label>
									</div> --}}
								</div>
				
								<div class="form-group">
									<label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="manual" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'manual') checked @endif />Manual</label>
									<label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="normal" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'normal') checked @endif>Normal</label>
									<label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="upward" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'upward') checked @endif>Upward</label>
									<label class="radio-inline"><input type="radio" name="purchase_round_off_to" value="downward" @if(auth()->user()->roundOffSetting->purchase_round_off_to != null && auth()->user()->roundOffSetting->purchase_round_off_to == 'downward') checked @endif>Downward</label>
								</div>
				
								<div class="form-group" id="purchase_upward_to_block" style="display: none;">
									<label for="purchase_upward_to">To Place (upward)</label>
									<input type="text" name="purchase_upward_to" id="purchase_upward_to" class="form-control" placeholder="To place" value="2" readonly />
								</div>
								<div class="form-group" id="purchase_downward_to_block" style="display: none;">
									<label for="purchase_downward_to">To Place (downward)</label>
									<input type="text" name="purchase_downward_to" id="purchase_downward_to" class="form-control" placeholder="To place" value="2" readonly />
								</div>
							</div>

							<div class="sale_block" style="display: none;">
								
								<div class="form-group">
									<label class="radio-inline"><input type="radio" name="sale_type" value="indirect_expense" @if(auth()->user()->roundOffSetting->sale_type != null && auth()->user()->roundOffSetting->sale_type == 'indirect_expense') checked @endif>Indirect Expense</label>
									<label class="radio-inline"><input type="radio" name="sale_type" value="indirect_income" @if(auth()->user()->roundOffSetting->sale_type != null && auth()->user()->roundOffSetting->sale_type == 'indirect_income') checked @endif >Indirect Income</label>
								</div>
				
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" name="sale_round_off_item[]" value="item_amount" @if(auth()->user()->roundOffSetting->sale_item_amount != null && auth()->user()->roundOffSetting->sale_item_amount == 'yes') checked @endif>Item Amount</label>
									</div>
									<div class="checkbox">
										<label><input type="checkbox" name="sale_round_off_item[]" value="gst_amount" @if(auth()->user()->roundOffSetting->sale_gst_amount != null && auth()->user()->roundOffSetting->sale_gst_amount == 'yes') checked @endif>GST Amount</label>
									</div>
									<div class="checkbox">
										<label><input type="checkbox" name="sale_round_off_item[]" value="total_amount" @if(auth()->user()->roundOffSetting->sale_total_amount != null && auth()->user()->roundOffSetting->sale_total_amount == 'yes') checked @endif>Total Amount</label>
									</div>
									{{-- <div class="checkbox">
										<label><input type="checkbox" value="item_price">Item Price</label>
									</div> --}}
								</div>
				
								<div class="form-group">
									<label class="radio-inline"><input type="radio" name="sale_round_off_to" value="manual" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'manual') checked @endif />Manual</label>
									<label class="radio-inline"><input type="radio" name="sale_round_off_to" value="normal" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'normal') checked @endif>Normal</label>
									<label class="radio-inline"><input type="radio" name="sale_round_off_to" value="upward" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'upward') checked @endif>Upward</label>
									<label class="radio-inline"><input type="radio" name="sale_round_off_to" value="downward" @if(auth()->user()->roundOffSetting->sale_round_off_to != null && auth()->user()->roundOffSetting->sale_round_off_to == 'downward') checked @endif>Downward</label>
								</div>
				
								<div class="form-group" id="sale_upward_to_block" style="display: none;">
									<label for="sale_upward_to">To Place (upward)</label>
									<input type="text" name="sale_upward_to" id="sale_upward_to" class="form-control" placeholder="To place" value="2" readonly />
								</div>
								<div class="form-group" id="sale_downward_to_block" style="display: none;">
									<label for="sale_downward_to">To Place (downward)</label>
									<input type="text" name="sale_downward_to" id="sale_downward_to" class="form-control" placeholder="To place" value="2" readonly />
								</div>
							</div>

							<div class="row form-group">
								<div class="col-md-6 col-md-offset-4">
									<button type="submit" class="btn btn-success">Save Settings</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>


		<!-- Scripts -->

		<script src="{{ asset('js/app.js') }}"></script>
		<script src="{{ asset('js/date.js') }}"></script>
		<script src="{{ asset('js/dashboard.js') }}"></script>
		<script src="{{ asset('js/printThis/printThis.js') }}"></script>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

		@yield('conflicting_scripts')


		<script>
			jQuery(function($) {
				$.noConflict();
			});
		</script>

		<script>
			$(document).ready(function (){
				$('.custom_date').dateFormat({
					format: 'xx/xx/xxxx',
				});
			});
			
		</script>

		@yield('trigger_autoclose_sidebar')

		<script>
			function checkStartingNo(starting_no, route)
			{
				// console.log("inside fun", starting_no)
				const user_id = '{{ auth()->user()->id }}';

				$.ajax({
					type: 'post',
					url: route,
					data: {
						"starting_no": starting_no,
						"user_id": user_id
					},
					success: function(response){
						$(".save-settings").attr('disabled', false);
						$("#starting_no_error_msg").text('');
					},
					error: function(err){
						if(err.status == 400){
							$("#starting_no_error_msg").text(err.responseJSON.errors);
							$(".save-settings").attr('disabled', true);
						}
					}
				})
			}
		</script>

		@yield('scripts')

		<script>
			function show_custom_alert(message, textColor="#777777") {
				// console.log(message);
				var custom_modal = document.getElementById('custom-messenger');
				var close_modal0 = document.getElementsByClassName("closeCustomModal")[0];
				var close_modal1 = document.getElementsByClassName("closeCustomModal")[1];
				var custom_message = document.getElementById('customMsg');
				var customMessageTracked = '';
				console.log(custom_message);
				if(Array.isArray(message)){
					customMessageTracked = "<ul style='color: red;'>";
					for(let i=0; i<message.length; i++){
						customMessageTracked += "<li>"+ message[i] +"</li>";
					}
					customMessageTracked += "</ul>";
				} else {

					customMessageTracked = `<span style="color: ${textColor}">${message}</span>`;
				}

				custom_message.innerHTML = customMessageTracked;

            	custom_modal.style.display = 'block';

				// When the user clicks on (x), close the modal
				close_modal0.onclick = function() {
					custom_modal.style.display = 'none';
				}

				// When the user clicks on close button, close the modal
				close_modal1.onclick = function() {
					custom_modal.style.display = 'none';
				}

				// When the user clicks anywhere outside of the modal, close it
				window.onclick = function(event) {
					if (event.target == custom_modal) {
						custom_modal.style.display = 'none';
					}
				}
			}
		</script>

		<script>
			$(document).ready(function() {
				$('input[type="text"]').on("keypress", function(e) {

					var inputKeyCode = e.keyCode ? e.keyCode : e.which;

					var allowDash = $(this).attr('data-allowDash') == 'true' ? true : false;

					if (inputKeyCode != null && !allowDash) {
						if (inputKeyCode == 45) e.preventDefault();
					}
				});
			});
		</script>

		<script>
			$(".alpha-only").on("keypress", function(e){
				console.log('alpha-only');
				if (window.event) {
					var charCode = window.event.keyCode;
				}
				else if (e) {
					var charCode = e.which;
				}
				else { return true; }
				if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 32)
					return true;
				else
					return false;
			});

			$(".num-only").on("keypress", function(e){
				console.log('num-only');
				evt = (e) ? e : window.event;
				var charCode = (evt.which) ? evt.which : evt.keyCode;
				if (charCode > 31 && (charCode < 48 || charCode > 57)) {
					return false;
				}
				return true;
			});

			function blockSpecialCharacters(e) {
				let key = e.key;
				let keyCharCode = key.charCodeAt(0);

				// 0-9
				if(keyCharCode >= 48 && keyCharCode <= 57) {
					return key;
				}
				// A-Z
				if(keyCharCode >= 65 && keyCharCode <= 90) {
					return key;
				}
				// a-z
				if(keyCharCode >= 97 && keyCharCode <= 122) {
					return key;
				}

				return false;
			}
		</script>

		<script>
			// Round setting modal
			$(document).ready(function(){

				$('#btn_round_off_setting').on("click", function() {
					$('#modal_round_off_setting').modal("show");
				});

				$("#add_account").on("click", function(){
					$("#account_selection_block").show();
				});

				$(document).on("change", "#account_selection", function(){
					console.log($(this).val());

					if( $(this).val() == 'sale' ){
						$(".sale_block").show();
						$("#transaction_type_sale").attr("checked", true);
					} else {
						$(".sale_block").hide();
						$("#transaction_type_sale").attr("checked", false);
					}

					if( $(this).val() == 'purchase' ){
						$(".purchase_block").show();
						$("#transaction_type_purchase").attr("checked", true);
					} else {
						$(".purchase_block").hide();
						$("#transaction_type_purchase").attr("checked", false);
					}
				});

				$('input[name="round_off_to"]').on("change", function(){
					if( $(this).is(":checked") ){

						if( $(this).val() == "upward" ){
							$("#upward_to_block").show();
						} else {
							$("#upward_to_block").hide();
						}

						if( $(this).val() == "downward" ){
							$("#downward_to_block").show();
						} else {
							$("#downward_to_block").hide();
						}

					}

				});

				$('input[name="transaction_type"]').on("change", function(){
					if($(this).val() == 'sale'){
						$(".sale_block").show();
					} else {
						$(".sale_block").hide();
					}

					if($(this).val() == 'purchase'){
						$(".purchase_block").show();
					} else {
					$(".purchase_block").hide(); 
					}
				});

				$('input[name="inventory_type"]').on("change", function() {
					if($(this).val() == 'with_inventory') {
						$("#with_inventory_block").show();
					} else {
						$("#with_inventory_block").hide();
					}
				});
				
			});
		</script>

		@if (session('success'))

			@php
				$custom_message = session('success');
				echo "<script>show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> $custom_message</span>`)</script>";
			@endphp

		@endif

		@if (session('failure'))

			@php
				$custom_message = session('failure');
				echo "<script>show_custom_alert(`<span style=\"color: red\"> $custom_message</span>`)</script>";
			@endphp

		@endif

		@if( $errors->any() )
			@php $data = '<ul>' @endphp
			@foreach($errors->all() as $error)
				@php
					$data .= '<li>'. $error .'</li>'
				@endphp
			@endforeach
			@php $data .= '</ul>' @endphp

			@php
				echo "<script>show_custom_alert(`<span style=\"color: red\">$data</span>`)</script>";
			@endphp		
		@endif

		<script>
			function validateDate(date, validation_err_block, validation_block_type, btn_to_disable, btn_type) {
				$.ajax({
					type: 'get',
					url: "{{ route('validate.financial.date') }}",
					data: {
						"date": date,
					},
					success: function(response){
						if(!response){
							$(validation_block_type+validation_err_block).text('Please provide date within the current financial year');
							$(btn_type+btn_to_disable).attr('disabled', true);
						} else {
							$(validation_block_type+validation_err_block).text('');
							$(btn_type+btn_to_disable).attr('disabled', false);
						}
					}
				});
			}


			function validateIfNameUnique(url, name, btn_type, btn_to_disable, validation_block_type, validation_err_block) {
				$.ajax({
					type: "POST",
					url: url,
					data: {
						"_token": "{{ csrf_token() }}",
						"name": name
					},
					success: function(response) {
						if(!response){
							$(validation_block_type+validation_err_block).text('This name already exits. Please provide unique name');
							$(btn_type+btn_to_disable).attr('disabled', true);
						} else {
							$(validation_block_type+validation_err_block).text('');
							$(btn_type+btn_to_disable).attr('disabled', false);
						}
					}
				})
			}

			function validateTwoDates(date1, date2, btn_type, btn_to_disable, validation_block_type, validation_err_block, msg='Please provide valid date') {
				$.ajax({
					type: 'get',
					url: "{{ route('validate.two.date') }}",
					data: {
						"validate_against_date": date1,
						"validate_date": date2
					},
					success: function(response){
						if(!response){
							$(validation_block_type+validation_err_block).text(msg);
							$(btn_type+btn_to_disable).attr('disabled', true);
						} else {
							$(validation_block_type+validation_err_block).text('');
							$(btn_type+btn_to_disable).attr('disabled', false);
						}
					}
				});
			}
		</script>

	</body>
</html>
