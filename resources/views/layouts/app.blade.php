<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Just Consult</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.06em;
        }

        .navbar-default .navbar-nav > li > a, .navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus{
            color: #fff;
        }

        .flex-container {
            display: -webkit-box;
            display: -moz-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 65vh;
        }

        #footer-widgets .footer-widget a, #footer-widgets .footer-widget li a, #footer-widgets .footer-widget li a:hover {
            color: #bbbbbb;
        }

        .footer-widget {
            width: 19.145%;
            box-sizing: border-box;
        }

        .footer-widget {
            margin-right: 5.5%;
            margin-bottom: 3.5%;
        }

        .footer-widget {
            float: left;
            color: #fff;
        }

        .footer-widget p, .footer-widget ul li a {
            color: #bbbbbb;
        }

        #main-footer .footer-widget h4 {
            color: #c39247;
        }
        #main-footer .footer-widget h4 {
            color: #c39247;
            font-size: 17px;
            margin-bottom: 10px;
        }

        #footer-city .city h4 {
            color: #c39247;
            font-size: 17px;
            margin-bottom: 10px;
        }

        #footer-city p {
            color: #bbbbbb;
        }

        #footer-info {
            float: left;
            padding-bottom: 10px;
            color: #666;
            text-align: left;
        }

        #footer-info, #footer-info a {
            color: #bbbbbb;
        }

        .et_pb_gutters3.et_pb_footer_columns4 .footer-widget {
            width: 20.875%;
        }

        #footer-widgets {
            padding: 6% 0 0;
        }

        #footer-city {
            margin-bottom: 3%;
        }

        #main-footer {
            background-color: #000000;
        }
        #main-footer {
            background-image: url('http://best-lawyers.net/myaccountant/wp-content/uploads/2020/01/business-accountant-footer.jpg');
        }

        .footer-widget .fwidget:last-child {
            margin-bottom: 0!important;
        }
        
        #footer-info {
            float: left;
            padding-bottom: 10px;
            color: #666;
        }

        #footer-info {
            float: left;
            padding-bottom: 10px;
            color: #666;
        }
        #footer-city{
            margin-bottom: 3%;
        }
        #footer-city p{color:#bbbbbb;}
        #menu-item-256{padding-right:10px!important;}
        #menu-item-256 a{
            background: #27bd47;
            color: #fff;
            padding: 8px 20px!important;
            margin-left: 0px;
            font-weight: 100;
            border-radius: 50px;
            border: 3px solid #2da947;
        }
        #menu-item-257 a{
            background: #27bd47;
            color: #fff;
            padding: 8px 20px!important;
            margin-left: 0px;
            font-weight: 100;
            border-radius: 50px;
            border: 3px solid #2da947;
        }

        .et-search-form, .et_mobile_menu, .footer-widget li::before, .nav li ul, blockquote {
            border-color: #c39247;
        }

        @media screen and (max-width: 479px) {
            .flex-container {
                height: 95vh;
            }
            .navbar-nav {
                margin: 20px -15px;
            }
            .navbar-default .navbar-collapse, .navbar-default .navbar-form {
                border-color: #313232;
            }
            .navbar-collapse {
                border-top: 0px solid transparent;
                -webkit-box-shadow: inset 0 0px 0 rgba(255, 255, 255, 0.1);
                box-shadow: inset 0 0px 0 rgba(255, 255, 255, 0.1);
            }
            .navbar-nav li a {
                background-color: #fff;
                color: #333 !important;
                text-align: center;
                margin: 10px auto;
                border-radius: 2px;
            }

            .navbar-nav li a:hover {
                background-color: #c39247 !important;
            }
        }
    </style>

    <link rel="stylesheet" href="http://best-lawyers.net/myaccountant/wp-content/themes/Divi/style.css" type="text/css" media="all" />
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top"  style="background-color: rgba(0,0,0,0.8); padding: 18px;">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="http://best-lawyers.net/myaccountant/wp-content/uploads/2019/12/my-account-logo1.png" alt="My Accountant" id="logo" style="height: 130px; width: auto; margin-top: -30px;">
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu" style="color: #fff">
                                    <li>
                                        <a href="{{ route('home') }}">Dashboard</a>
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
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
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
        </div>

        <div class="flex-container">
            @yield('content')
        </div>

        <footer id="main-footer">
				
        <div class="container">
            <div id="footer-widgets" class="clearfix">
                <div class="footer-widget"><div id="text-2" class="fwidget et_pb_widget widget_text"><h4 class="title">SERVICES</h4>			<div class="textwidget"><p>ACCOUNTS<br>
        INVENTORY MANAGEMENT<br>
        TAXATION<br>
        COMPLIANCE<br>
        TRADEMARK/COPYRIGHT<br>
        BUSINESS CONSULTANCY</p>
        </div>
                </div> <!-- end .fwidget --></div> <!-- end .footer-widget --><div class="footer-widget"><div id="nav_menu-3" class="fwidget et_pb_widget widget_nav_menu"><div class="menu-fm-menu-container"><ul id="menu-fm-menu" class="menu"><li id="menu-item-499" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-10 current_page_item menu-item-499"><a href="http://best-lawyers.net/myaccountant/" aria-current="page">Home</a></li>
        <li id="menu-item-500" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-500"><a href="http://best-lawyers.net/myaccountant/about/">ABOUT</a></li>
        <li id="menu-item-501" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-501"><a href="http://best-lawyers.net/myaccountant/blog/">BLOG</a></li>
        <li id="menu-item-502" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-502"><a href="http://best-lawyers.net/myaccountant/software/">SOFTWARE</a></li>
        </ul></div></div> <!-- end .fwidget --></div> <!-- end .footer-widget --><div class="footer-widget"><div id="nav_menu-5" class="fwidget et_pb_widget widget_nav_menu"><div class="menu-tm-menu-container"><ul id="menu-tm-menu" class="menu"><li id="menu-item-505" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-505"><a href="http://best-lawyers.net/myaccountant/terms-and-conditions/">Terms and Conditions</a></li>
        <li id="menu-item-506" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-506"><a href="http://best-lawyers.net/myaccountant/privacy-policy-2/">Privacy Policy</a></li>
        </ul></div></div> <!-- end .fwidget --></div> <!-- end .footer-widget --><div class="footer-widget"><div id="text-5" class="fwidget et_pb_widget widget_text"><h4 class="title">CONTACT US</h4>			<div class="textwidget"><p><a href="mailto:info@themyaccountant.com">info@themyaccountant.com</a></p>
        </div>
                </div> <!-- end .fwidget --></div> <!-- end .footer-widget -->    </div> <!-- #footer-widgets -->
        </div>    <!-- .container -->

                    <div id="footer-city">
                <div class="container clearfix">
                        <div class="city">
        <h4 class="title">Supporting Cities</h4>
                        <p>  Delhi | Mumbai | Punjab | J &amp; k</p>
                        </div>
                </div>
            </div>
                        <div id="footer-bottom">
                            <div class="container clearfix">
                        {{-- <ul class="et-social-icons">

            <li class="et-social-icon et-social-facebook">
                <a href="#" class="icon">
                    <span>Facebook</span>
                </a>
            </li>
            <li class="et-social-icon et-social-twitter">
                <a href="#" class="icon">
                    <span>Twitter</span>
                </a>
            </li>
            <li class="et-social-icon et-social-google-plus">
                <a href="#" class="icon">
                    <span>Google</span>
                </a>
            </li>

        </ul> --}}
        <div id="footer-info">Copyright © My Accountant {{ \Carbon\Carbon::now()->format('Y') }}. All Rights Reserved. <br>
        Design by Local SEO</div>					</div>	<!-- .container -->
                        </div>
                    </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('scripts')
</body>
</html>
