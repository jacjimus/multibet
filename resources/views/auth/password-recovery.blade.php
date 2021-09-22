@extends('layouts.page')

@section('page-title', config('app.name') . ' | ' . trans('auth.password-recovery-title'))

@section('page-slot')
<x-controls.form
	id="password-recovery-form"
	action="/api/password/email"
	method="post"
	class="card mx-auto col-sm-9 col-md-6 col-lg-4 mt-5 p-0"
	style="max-width:400px;">
	
	<!-- card body -->
	<div class="card-body">
		
		<!-- title -->
		<x-controls.form-group class="text-center text-muted mb-4">
			<h5 class="text-uppercase">@lang('auth.password-recovery-title')</h5>
		</x-controls.form-group>
		
		<!-- intro -->
		<p class="text-muted">@lang('auth.password-recovery-intro')</p>
		
		<!-- alert slot -->
		<x-controls.form-group class="alert-slot" />
		
		<!-- email -->
		<x-controls.form-group>
			<x-inputs.input
				type="email"
				id="email"
				name="email"
				clear
				autocomplete="email"
				:placeholder="trans('Email Address')"
				icon-left="at" />
		</x-controls.form-group>
		
		<!-- submit -->
		<x-controls.form-group>
			<button class="btn btn-primary btn-block" type="submit">
				@lang('auth.password-recovery-btn')
			</button>
		</x-controls.form-group>
	
	</div>
	<!-- /card body -->

</x-controls.form>
@endsection
