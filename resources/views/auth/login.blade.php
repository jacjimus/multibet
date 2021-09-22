@extends('layouts.page')

@section('page-title', config('app.name') . ' | ' . trans('auth.login-title'))

@section('page-slot')

<!-- login form -->
<x-controls.form
	id="login-form"
	action="/api/login"
	method="post"
	success-busy
	class="card mx-auto col-sm-9 col-md-6 col-lg-4 {{ isset($_GET['rdr']) ? 'mt-4' : 'mt-5' }} p-0"
	style="max-width: 350px;">

	<!-- card body -->
	<div class="card-body">

		<!-- title -->
		<x-controls.form-group class="text-center text-muted mb-4">
			<h5 class="text-uppercase">@lang('auth.login-title')</h5>
		</x-controls.form-group>

		<!-- alert slot -->
		<x-controls.form-group class="alert-slot" />

		<!-- username -->


		<!-- password -->
		<x-controls.form-group>
			<x-inputs.input
				id="password"
				name="password"
				type="password"
				required
				clear
				toggle-password
				autocomplete="current-password"
				:placeholder="trans('Password')"
				icon-left="key"
			/>
		</x-controls.form-group>

		<!-- remember -->
		<x-controls.form-group>
			<x-inputs.checkbox name="remember" :text="trans('Remember Me')" />
		</x-controls.form-group>

		<!-- submit -->
		<x-controls.form-group>
			<button class="btn btn-primary btn-block" type="submit">
				<x-controls.fa class="mr-2" icon="unlock" />
				@lang('auth.login-btn')
			</button>

		</x-controls.form-group>


	</div>
	<!-- /card body -->

</x-controls.form>
@endsection
