@extends('layouts.page')

@section('page-title', config('app.name') . ' | Email Verification')

@section('page-slot')
<div class="card mx-auto col-sm-9 col-md-6 col-lg-4 mt-5 p-0" style="max-width:400px;">

	<!-- card body -->
	<div class="card-body">

		<!-- title -->
		<x-controls.form-group class="text-center text-muted mb-4">
			<h5 class="text-uppercase">Verifying</h5>
		</x-controls.form-group>

		<!-- verify alert -->
		<div id="verify-alert" class="text-center">
			<x-controls.fa class="text-muted fa-spin" icon="spinner" style="width:30px;height:30px;" />
			<p class="text-muted">Please wait...</p>
		</div>

		<div id="verify-login" class="mt-4 d-none">
			<a href="/login" class="btn btn-primary btn-block">
				<x-controls.fa class="mr-2" icon="user" />
				@lang('auth.login-btn')
			</a>
		</div>

	</div>
	<!-- /card body -->

</div>
@endsection

{{-- verify js --}}
@if (stripos($__env->yieldContent('page-scripts.sh'), $tmp = 'assets/js/components/verify.js') === false)
@section('page-scripts.sh')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
