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

			.active{
				display: block;
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
						<a href="#userSubmenu" data-toggle="collapse" aria-expanded="false">User</a>
						<ul class="collapse list-unstyled" id="userSubmenu">
							<li><a href="{{ route('user.view') }}">View all Users</a></li>
						</ul>
					</li>
					<li>
						<a href="#gstSubmenu" data-toggle="collapse" aria-expanded="false">GST</a>
						<ul class="collapse list-unstyled" id="gstSubmenu">
							<li><a href="{{ route('create.gst') }}">Add new GST</a></li>
							<li><a href="{{ route('show.gst') }}">View all GSTs</a></li>
						</ul>
					</li>
					{{-- <li>
						<a href="#notificationSubmenu" data-toggle="collapse" aria-expanded="false">Notification</a>
						<ul class="collapse list-unstyled" id="notificationSubmenu">
							<li><a href="{{ route('notification.index') }}">Send Notification</a></li>
						</ul>
					</li> --}}
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

				<nav class="navbar navbar-default">
					<div class="container-fluid">
						<div class="navbar-header">
							<button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn" style="background: #6D7FCC;">
								<i class="glyphicon glyphicon-align-left"></i>
								<span>Toggle Sidebar</span>
							</button>
						</div>

						<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
							<ul class="nav navbar-nav navbar-right">
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::guard('admin')->user()->name }} <span class="caret"></a>
									<ul class="dropdown-menu" role="menu">
										<li>
											<a href="{{ route('admin.logout') }}"
												onclick="event.preventDefault();
												document.getElementById('logout-form').submit();">
												Logout
											</a>

											<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
												{{ csrf_field() }}
											</form>
										</li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</nav>
				@if (session('success'))
					<div class="alert alert-success">
						{{ session('success') }}
					</div>
				@endif

				@if (session('failure'))
					<div class="alert alert-danger">
						{{ session('failure') }}
					</div>
				@endif

				@yield('content')
			  </div>
			  
		</div>
		<!-- Wrapper end -->

		<!-- Scripts -->
		<script src="{{ asset('js/app.js') }}"></script>
		<script src="{{ asset('js/dashboard.js') }}"></script>
    	@yield('scripts')
	</body>
</html>