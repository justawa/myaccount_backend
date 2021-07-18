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
		<style>

			body {
				font-family: 'Poppins', sans-serif;
				letter-spacing: 0.06em;
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

			.autosaleorder div:last-child{
				border-bottom: none;
			}

			.active{
				display: block;
				position: absolute;
				z-index: 9;
			}

		</style>
	</head>
	<body>
		<div class="wrapper">
			<!-- Sidebar Holder -->
			<nav id="sidebar">
				<div class="sidebar-header">
					<img src="{{ asset('images/my-account-logo.png') }}" style="display: block; margin: 0 auto; height: 120px;" />
				</div>

				<ul class="list-unstyled components">
					<p class="text-center">Sidebar Menus</p>
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
						<a href="#cashSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-user-circle-o" aria-hidden="true"></i>&nbsp;Cash Transactions</a>
						<ul class="collapse list-unstyled" id="cashSubmenu">
							<li><a href="{{ route('cash.in.hand') }}">Cash in Hand</a></li>
							<li><a href="{{ route('cash.withdraw') }}">Cash Withdrawn</a></li>
							<li><a href="{{ route('cash.deposit') }}">Cash Deposit</a></li>
						</ul>
					</li>
					<li>
						<a href="#partySubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-user-circle-o" aria-hidden="true"></i>&nbsp;Party</a>
						<ul class="collapse list-unstyled" id="partySubmenu">
							<li><a href="{{ route('party.create') }}">Add New Party</a></li>
							<li><a href="{{ route('party.index') }}">View Parties</a></li>
						</ul>
					</li>
					<li>
						<a href="#ledgerSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-user-circle-o" aria-hidden="true"></i>&nbsp;Cash/Bank Ledger</a>
						<ul class="collapse list-unstyled" id="ledgerSubmenu">
							{{-- <li><a href="{{ route('amount.in.bank.report') }}">Bank Report</a></li>
							<li><a href="{{ route('amount.as.cash.report') }}">Cash Report</a></li> --}}
							<li><a href="{{ route('cash.in.hand.report') }}">Cash in hand Report</a></li>
							<li><a href="{{ route('cash.deposit.report') }}">Cash Deposit Report</a></li>
							<li><a href="{{ route('cash.withdraw.report') }}">Cash Withdraw Report</a></li>
						</ul>
					</li>
					<li>
						<a href="{{ route('bank.create') }}"><i class="fa fa-credit-card-alt" aria-hidden="true"></i>&nbsp;Add Bank</a>
					</li>
					<li>
						<a href="{{ route('insurance.create') }}"><i class="fa fa-university" aria-hidden="true"></i>&nbsp;Add Insurance</a>
					</li>
					<li>
						<a href="{{ route('transporter.create') }}"><i class="fa fa-truck" aria-hidden="true"></i>&nbsp;Add Transporter</a>
					</li>
					<li>
						<a href="#inventorySubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-archive" aria-hidden="true"></i>&nbsp;Inventory</a>
						<ul class="collapse list-unstyled" id="inventorySubmenu">
							<li><a href="{{ route('group.create') }}">Add New Group</a></li>
							{{-- <li><a href="{{ route('measuringunit.create') }}">Add New Unit</a></li> --}}
							<li><a href="{{ route('item.create') }}">Add New Item</a></li>
							<li><a href="{{ route('item.index') }}">View Items</a></li>
							{{-- <li><a href="{{ route('item.value') }}">Value of Inventory</a></li> --}}
							<li><a href="{{ route('manage.inventory') }}">Manage Inventory</a></li>
						</ul>
					</li>
					<li>
						<a href="#saleSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-shopping-bag" aria-hidden="true"></i>&nbsp;Sale</a>
						<ul class="collapse list-unstyled" id="saleSubmenu">
							<li><a href="{{ route('sale.create') }}">Create New Invoice</a></li>
							<li><a href="{{ route('find.invoice.by.party') }}">Pending Payment</a></li>
							<li><a href="{{ route('sale.index') }}">View all Invoices</a></li>
							<li><a href="{{ route('sale.order') }}">Create Sale Order</a></li>
							<li><a href="{{ route('view.all.sale.order') }}">View Sale Orders</a></li>
							<li><a href="{{ route('sale.note') }}">Credit/Debit Note</a></li>
							<li><a href="{{ route('sale.report') }}">Sale Report</a></li>
						</ul>
					</li>
					<li>
						<a href="#purchaseSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-shopping-basket" aria-hidden="true"></i>&nbsp;Purchase</a>
						<ul class="collapse list-unstyled" id="purchaseSubmenu">
							<li><a href="{{ route('purchase.create') }}">New Purchase</a></li>
							<li><a href="{{ route('find.purchase.by.party') }}">Pending Payment</a></li>
							<li><a href="{{ route('purchase.index') }}">View all Purchases</a></li>
							<li><a href="{{ route('purchase.order') }}">Create Purchase Order</a></li>
							<li><a href="{{ route('view.all.purchase.order') }}">View Purchase Orders</a></li>
							<li><a href="{{ route('purchase.filter.by.date') }}">Filter Purchase by Date</a></li>
							<li><a href="{{ route('purchase.note') }}">Credit/Debit Note</a></li>
							{{-- <li><a href="{{ route('purchase.report') }}">Purchase Report</a></li> --}}
							{{-- <li><a href="{{ route('purchase.filter.by.party') }}">Filter Purchase by Party Name</a></li>
							<li><a href="{{ route('purchase.filter.by.bill') }}">Filter Purchase by Bill</a></li> --}}
							{{-- <li><a href="{{ route('all.tax.purchase') }}">View Taxes</a></li> --}}
						</ul>
					</li>
					<li>
						<a href="{{ route('show.expense.form') }}"><i class="fa fa-usd" aria-hidden="true"></i>&nbsp;Expenses</a>
					</li>
					<li>
						<a href="{{ route('show.income.form') }}"><i class="fa fa-money" aria-hidden="true"></i>&nbsp;Incomes</a>
					</li>
					<li>
						<a href="#reportSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-line-chart" aria-hidden="true"></i>&nbsp;Report</a>
						<ul class="collapse list-unstyled" id="reportSubmenu">
							<li><a href="{{ route('item.report') }}">Inventory Report</a></li>
							<li><a href="{{ route('sale.index') }}">Sale Report</a></li>
							<li><a href="{{ route('purchase.index') }}">Purchase Report</a></li>
							<li><a href="{{ route('b2b.purchase') }}">B2B Purchase</a></li>
							<li><a href="{{ route('b2b.sale') }}">B2B Sale</a></li>
							<li><a href="{{ route('hsn.purchase.report') }}">HSN Purchase Report</a></li>
							<li><a href="{{ route('hsn.sale.report') }}">HSN Sale Report</a></li>
							<li><a href="{{ route('tax.paid.report') }}">Tax Paid Report</a></li>
							<li><a href="{{ route('tax.collected.report') }}">Tax Collected Report</a></li>
							<li><a href="{{ route('debtor.report') }}">Debtor Report</a></li>
							<li><a href="{{ route('creditor.report') }}">Creditor Report</a></li>
							<li><a href="{{ route('gst.report') }}">GST Sale Report</a></li>
							<li><a href="{{ route('gst.purchase.report') }}">GST Purchase Report</a></li>
							<li><a href="{{ route('party.report') }}">Party Report</a></li>
							<li><a href="{{ route('purchase.data.report') }}">Purchase Report</a></li>
							<li><a href="{{ route('items.report') }}">Item Report</a></li>
							<li><a href="{{ route('pending.payment.report') }}">Pending Payment Report</a></li>
							{{-- <li><a href="{{ route('item.value.report') }}">Item Value Report</a></li> --}}
							<li><a href="{{ route('credit.debit.note.report') }}">Credit Debit Note Report</a></li>
						</ul>
					</li>
					<li>
						<a href="#"><i class="fa fa-file-text" aria-hidden="true"></i>&nbsp;Taxes</a>
					</li>
					<li>
						<a href="#documentSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-folder-open" aria-hidden="true"></i>&nbsp;Documents</a>
						<ul class="collapse list-unstyled" id="documentSubmenu">
							<li><a href="{{ route('sale.document') }}">Sale @yield('count')</a></li>
							<li><a href="{{ route('purchase.document') }}">Purchase @yield('count')</a></li>
							<li><a href="{{ route('bank.statement.document') }}">Bank Statement @yield('count')</a></li>
						</ul>
					</li>
					<li>
						<a href="#gstSubmenu" data-toggle="collapse" aria-expanded="false"><i class="fa fa-folder-open" aria-hidden="true"></i>&nbsp;GST Return</a>
						<ul class="collapse list-unstyled" id="gstSubmenu">
							<li><a href="{{ route('gst.computation') }}">GST Computation</a></li>
							<li><a href="{{ route('gst.ledger') }}">GST Ledger</a></li>
							<li><a href="{{ route('gst.paid.in.cash') }}">GST Paid in Cash</a></li>
						</ul>
					</li>
				</ul>

				<ul class="list-unstyled CTAs">
					<li>
						<a class="article" href="{{ route('logout') }}"
							onclick="event.preventDefault();
							document.getElementById('logout-form').submit();">
							LOG OUT
						</a>

						<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
							{{ csrf_field() }}
						</form>
					</li>
				</ul>
			</nav>

			<!-- Page Content Holder -->
			<div id="content">

				<nav class="navbar navbar-default" @yield('header_style')>
					<div class="container-fluid">
						<div class="navbar-header">
							<button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn" style="background: #6D7FCC;">
								<i class="glyphicon glyphicon-align-left"></i>
								<span>Toggle Sidebar</span>
							</button>
						</div>
						{{-- <div style="display: inline-block; width: 75%;">
							@yield('company_logo')
						</div> --}}
						<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1" >
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
				</nav>
				@yield('content')
			  </div>

		</div>
		<!-- Wrapper end -->

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


		<!-- Scripts -->

		<script src="{{ asset('js/app.js') }}"></script>
		<script src="{{ asset('js/dashboard.js') }}"></script>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

		@yield('conflicting_scripts')


		<script>
			jQuery(function($) {
				$.noConflict();
			});
		</script>

		@yield('scripts')

		<script>
			function show_custom_alert(message) {
				// console.log(message);
				var custom_modal = document.getElementById('custom-messenger');
				var close_modal0 = document.getElementsByClassName("closeCustomModal")[0];
				var close_modal1 = document.getElementsByClassName("closeCustomModal")[1];
				var custom_message = document.getElementById('customMsg');
				custom_message.innerHTML = message;

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

		@if (session('success'))

			@php
				$custom_message = session('success');
				echo "<script>show_custom_alert(`<span style=\"color: green\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> $custom_message</span>`)</script>";
			@endphp

		@endif

		@if (session('failure'))

			@php
				$custom_message = session('failure');
				echo "<script>show_custom_alert(`<span style=\"color: red\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> $custom_message</span>`)</script>";
			@endphp

		@endif

		

	</body>
</html>
