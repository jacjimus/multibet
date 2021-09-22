@extends('layouts.page')

@section('page-title', config('app.name') . ' | ' . trans('auth.register-title'))

@section('page-slot')
<x-controls.form
	id="register-form"
	action="/api/register"
	method="post"
	class="card mx-auto col-sm-9 col-md-6 col-lg-4 mt-5 p-0"
	style="max-width:550px;">
	
	<!-- card body -->
	<div class="card-body">
		
		<!-- title -->
		<x-controls.form-group class="text-center text-muted mb-4">
			<h5 class="text-uppercase">@lang('auth.register-title')</h5>
		</x-controls.form-group>
		
		<!-- alert slot -->
		<x-controls.form-group class="alert-slot" />
		
		<!-- name -->
		<x-controls.form-group class="row" label-class="col-md-3" slot-class="col">
			<x-slot name="label">@lang('Full Name')</x-slot>
			<x-inputs.input
				id="name"
				name="name"
				required
				clear
				autocomplete="name"
				:placeholder="trans('Full Name')" />
		</x-controls.form-group>
		
		<!-- email -->
		<x-controls.form-group class="row" label-class="col-md-3" slot-class="col">
			<x-slot name="label">@lang('Email Address')</x-slot>
			<x-inputs.input
				type="email"
				id="email"
				name="email"
				clear
				autocomplete="email"
				:placeholder="trans('Email Address')" />
		</x-controls.form-group>
		
		<!-- phone number -->
		<x-controls.form-group class="row" label-class="col-md-3" slot-class="col">
			<x-slot name="label">@lang('Phone Number')</x-slot>
			<x-inputs.input
				type="tel"
				id="phone_number"
				name="phone_number"
				clear
				autocomplete="tel"
				:placeholder="trans('Phone Number')" />
			<input type="hidden" name="phone_region" value="{{ config('app.region', 'US') }}">
		</x-controls.form-group>
		
		<!-- password -->
		<x-controls.form-group class="row" label-class="col-md-3" slot-class="col">
			<x-slot name="label">@lang('Password')</x-slot>
			<x-inputs.input
				type="password"
				id="password"
				name="password"
				clear
				required
				toggle-password
				autocomplete="new-password"
				:placeholder="trans('Password')" />
			<small class="text-muted">@lang('auth.password-hint')</small>
		</x-controls.form-group>
		
		<!-- password confirmation -->
		<x-controls.form-group class="row" label-class="col-md-3" slot-class="col">
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
		
		<!-- agreement -->
		<x-controls.form-group class="row text-muted small agreement" slot-class="col offset-md-3">
			{!! trans('auth.agreement', [
				'action' => 'registering',
				'terms' => url('terms-of-use'),
				'privacy' => url('privacy-policy'),
			]) !!}
		</x-controls.form-group>
		
		<!-- submit -->
		<x-controls.form-group class="text-center">
			<button class="btn btn-primary min-w200" type="submit">
				@lang('auth.register-btn')
			</button>
		</x-controls.form-group>
		
		<!-- oauth -->
		<x-controls.form-group class="text-center">
			<p class="cross-text text-muted">@lang('or sign in with')</p>
			
			<!-- oauth - google -->
			<button class="btn btn-social btn-google text-white" type="button" data-oauth="google" title="Google">
				<x-controls.fa type="fab" icon="google" />
			</button>
			{{--
			<!-- oauth - twitter -->
			<button class="btn btn-social btn-twitter text-white ml-2" type="button" data-oauth="twitter" title="Twitter">
				<x-controls.fa type="fab" icon="twitter" />
			</button>
			
			<!-- oauth - facebook -->
			<button class="btn btn-social btn-facebook text-white ml-2" type="button" data-oauth="facebook" title="Facebook">
				<x-controls.fa type="fab" icon="facebook-f" />
			</button>
			--}}
		</x-controls.form-group>
		<!-- /oauth -->
	
	</div>
	<!-- /card body -->
	
</x-controls.form>
@endsection
