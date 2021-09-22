@extends('layouts.page')

@section('page-title', config('app.name') . ' | Premium Account')

@section('page-slot')
<?php if (isset($_GET['rdr'])){ ?>
<!-- premium -->
<div class="premium-acc card mx-auto col-sm-9 col-md-6 col-lg-4 mt-3 p-0" style="max-width: 500px;">
	<div class="card-body bg-dark text-white">
		<p>Why Premium?</p>
		<span class="d-block"><i class="text-primary fas fa-star"></i> High odds three way football predictions.</span>
		<span class="d-block"><i class="text-primary fas fa-star"></i> Our premium subscribers get our highly accurate straight win football predictions.</span>
		<span class="d-block"><i class="text-primary fas fa-star"></i> Sportpesa mega jackpot prediction every weekend .You will love it.</span>

		<h2 style="margin-top:20px;margin-top:10px;">Ksh 299.00<small>/month</small></h2>

		<span class="d-block"><i class="text-primary fas fa-star"></i> Join thousands of bettors who profit from our predictions daily.</span>
	</div>
</div>
<?php } ?>

<!-- checkout -->
<form action="javascript:" id="checkout-form" class="card mx-auto col-sm-9 col-md-6 col-lg-4 mt-5 p-0" style="max-width: 400px;">

	<!-- card body -->
	<div class="card-body">

		<!-- title -->
		<x-controls.form-group class="text-center text-muted mb-4">
			<h5 class="text-uppercase">Premium Checkout</h5>
		</x-controls.form-group>

		<!-- intro -->
		<p class="text-muted">Click on make payment button to initiate automatic MPESA request.</p>

		<!-- alert slot -->
		<x-controls.form-group class="alert-slot" />

		<!-- amount -->
		<x-controls.form-group>
			<x-slot name="label">@lang('Monthly Payment')</x-slot>
			<x-inputs.input
				id="amount"
				name="amount"
				readonly
				icon-left="money-bill"
				:value="299">
				<x-slot name="append">
					<div class="input-group-text">
						KES
					</div>
				</x-slot>
			</x-inputs.input>
		</x-controls.form-group>

		<!-- amount -->
		<x-controls.form-group>
			<x-slot name="label">@lang('Enter MPESA Phone Number')</x-slot>
			<x-inputs.input
				id="phone_number"
				name="phone_number"
				icon-left="phone-alt"
				clear
				placeholder="MPESA Phone Number"
				:value="$app_user->phone_number" />
		</x-controls.form-group>

		<!-- payment -->
		<x-controls.form-group>
			<button id="make-payment" class="btn btn-primary btn-block" type="submit">
				<x-controls.fa class="mr-2" icon="credit-card" />
				@lang('Make Payment')
			</button>
		</x-controls.form-group>
	</div>
	<!-- /card body -->

</form>
@endsection

{{-- checkout js --}}
@if (stripos($__env->yieldContent('page-scripts'), $tmp = 'assets/js/components/checkout.js') === false)
@section('page-scripts')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
