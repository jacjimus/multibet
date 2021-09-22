@extends('layouts.master')

@section('page-meta')
	<x-page.meta meta-page />
@endsection

@section('page-navbar')
	<x-page.navbar class="shrink" />
@endsection

@section('page-content')
	<section class="page-section flex-grow @yield('page-slot-class', 'bg-light')">
		<div class="container">
			<!-- page-slot -->
			@section('page-slot')
			@show
			<!-- /page-slot -->
		</div>
	</section>
@endsection

@section('page-footer')
	<x-page.footer /> 
@endsection
