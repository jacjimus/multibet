@extends('layouts.page')

@section('page-meta')
	<x-page.meta meta-page />
@endsection

@section('page-navbar')
	<x-page.navbar class="trans">

	</x-page.navbar>
@endsection

@section('page-content')
	@include('fstats.welcome.matches')

@endsection
