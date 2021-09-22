@extends('layouts.page')

@section('page-title', config('app.name') . ' | ' . trans('auth.password-reset-title'))

@section('page-slot')
<x-controls.form
	id="password-reset-form"
	action="/api/password/reset"
	method="post"
	class="card mx-auto col-sm-9 col-md-6 col-lg-4 mt-5 p-0"
	style="max-width:400px;">
	
	<!-- card body -->
	<div class="card-body">
		
		<!-- title -->
		<x-controls.form-group class="text-center text-muted mb-4">
			<h5 class="text-uppercase">@lang('auth.password-reset-title')</h5>
		</x-controls.form-group>
		
		<!-- intro -->
		<p class="text-muted">@lang('auth.password-reset-intro')</p>
		
		<!-- alert slot -->
		<x-controls.form-group class="alert-slot" />
		
		<!-- hidden -->
		<input type="hidden" id="token" name="token" value="{{ $token ?? '' }}">
		<input type="hidden" id="email" name="email" value="{{ $email ?? '' }}">
		
		<!-- password -->
		<x-controls.form-group>
			<x-slot name="label">@lang('New Password')</x-slot>
			<x-inputs.input
				type="password"
				id="password"
				name="password"
				clear
				required
				toggle-password
				autocomplete="new-password"
				:placeholder="trans('New Password')" />
			<small class="text-muted">@lang('auth.password-hint')</small>
		</x-controls.form-group>
		
		<!-- password confirmation -->
		<x-controls.form-group>
			<x-slot name="label">@lang('Confirm Password')</x-slot>
			<x-inputs.input
				type="password"
				id="password_confirmation"
				name="password_confirmation"
				clear
				required
				toggle-password
				autocomplete="new-password"
				:placeholder="trans('Confirm Password')" />
		</x-controls.form-group>
		
		<!-- submit -->
		<x-controls.form-group>
			<button class="btn btn-primary btn-block" type="submit">
				@lang('auth.password-reset-btn')
			</button>
		</x-controls.form-group>
	
	</div>
	<!-- /card body -->

</x-controls.form>
@endsection
