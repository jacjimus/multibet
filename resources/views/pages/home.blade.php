@extends('layouts.page')

@section('page-title', config('app.name') . ' | Home')

@section('page-slot')
<div class="row mt-3">
	<div class="col col-md-4">
		<div class="card">
			<div class="card-body">
				<h2>Menu</h2>
			</div>
		</div>
	</div>
	<div class="col col-md-8">
		<div class="card">
			<div class="card-body">
				<h2>Home</h2>
			</div>
		</div>
	</div>
</div>

@endsection
