<!DOCTYPE html>
<html lang="{{ $app_locale ?? 'en' }}">
    <head>

		<!-- page meta -->
		@section('page-meta')
		<x-page.meta />
        @show

		<!-- page title -->
		<title>@yield('page-title', config('app.name'))</title>

		<!-- page icon -->
		@section('page-icon')
		<link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
		<link rel="apple-touch-icon-precomposed" type="image/png" href="{{ asset('icon.png') }}">
        @show

		<!-- page head -->
		@section('page-head')
        <x-page.google-analytics />

		<!-- theme -->
        <x-controls.css href="https://fonts.googleapis.com/css?family=Montserrat:400,700" />
		<x-controls.css href="https://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic" />
		<x-controls.css href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" />
		<x-controls.css :href="asset('assets/css/lib/bootstrap-agency.css')" />
        <x-controls.css :href="asset('assets/css/app.css')" />
        <x-controls.css href="http://cdn-na.infragistics.com/igniteui/2021.1/latest/css/themes/infragistics/infragistics.theme.css" />
        <x-controls.css href="http://cdn-na.infragistics.com/igniteui/2021.1/latest/css/structure/infragistics.css" />

        <!-- lib -->
        <x-controls.js src="{{ asset('assets/js/lib/jquery-3.6.0.min.js') }}" />
		<x-controls.js src="{{ asset('assets/js/lib/jquery.easing.min.js') }}" />
		<x-controls.js src="{{ asset('assets/js/lib/bootstrap.min.js') }}" />
		<x-controls.js src="{{ asset('assets/js/lib/popper.min.js') }}" />
		<x-controls.js src="{{ asset('assets/js/lib/js.cookie.min.js') }}" />
		<x-controls.js src="{{ asset('assets/js/app.js') }}" />


        <x-controls.js src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js" />
		<x-controls.js src="http://cdn-na.infragistics.com/igniteui/2021.1/latest/js/infragistics.core.js" />
		<x-controls.js src="http://cdn-na.infragistics.com/igniteui/2021.1/latest/js/infragistics.lob.js" />


		<!-- inc -->
		@show
		<!-- /page head -->
	</head>
	<body id="@yield('page-id', 'top')">

		<!-- page layout -->
		<div class="page-layout">
			<!-- page navbar -->
			@section('page-navbar')
			@show

			<!-- page content -->
			@section('page-content')
			@show

			<!-- page footer -->
			@section('page-footer')
			@show
		</div>
		<!-- /page layout -->

		<!-- page scripts -->
		@section('page-scripts')
		<x-controls.js src="{{ asset('assets/js/components/session.js') }}" />
		<x-controls.js src="{{ asset('assets/js/components/request.js') }}" />
        @show

	</body>
</html>
