@props(['wrapperClass', 'iconLeft', 'btnClass', 'prepend', 'clear', 'append', 'iconRight', 'togglePassword', 'readonly', 'invalid'])

@php
$input_class = 'form-control flex-fill';
if ($clear = x_isset_b($clear)) $input_class .= ' input-clear';
if ($togglePassword = x_isset_b($togglePassword)) $input_class .= ' toggle-password';
if (x_isset_b($invalid)) $input_class .= ' is-invalid';
$attrs = ['class' => $input_class, 'type' => 'text'];
if (x_isset_b($readonly)) $attrs['readonly'] = true;
if (x_isset_b($disabled)) $attrs['disabled'] = true;
$input_attrs = $attributes->merge($attrs);
@endphp

<div class="inputs-input input-group d-flex flex-nowrap {{ $wrapperClass ?? '' }}">
	@if (x_isset_b($iconLeft) || x_isset_b($prepend))
	<div class="input-group-prepend">
		@if (x_isset_b($iconLeft))
		<div class="input-group-text">
			<x-controls.fa :icon="$iconLeft" />
		</div>
		@endif
		@if (x_isset_b($prepend))
		<?php echo $prepend; ?>
		@endif
	</div>
	@endif
	<input {{ $input_attrs }} />
	@if (x_isset_b($iconRight) || x_isset_b($append) || $clear || $togglePassword)
	<div class="input-group-append">
		@if ($clear)
		<button class="btn {{ $btnClass ?? 'btn-outline-secondary' }} input-clear-btn d-none" type="button" title="@lang('Clear')">
			<x-controls.fa icon="times" />
		</button>
		@endif
		@if ($togglePassword)
		<button class="btn {{ $btnClass ?? 'btn-outline-secondary' }} toggle-password-btn d-none" type="button" title-hide="@lang('Hide Password')" title-show="@lang('Show Password')">
			<x-controls.fa class="password-hidden" icon="eye-slash" />
			<x-controls.fa class="password-showing d-none" icon="eye" />
		</button>
		@endif
		@if (x_isset_b($iconRight))
		<div class="input-group-text">
			<x-controls.fa :icon="$iconRight" />
		</div>
		@endif
		@if (x_isset_b($append))
		<?php echo $append ?>
		@endif
	</div>
	@endif
</div>

{{-- inputs.input js --}}
@if (stripos($__env->yieldContent('page-scripts.sh'), $tmp = 'assets/js/components/inputs/input.js') === false)
@section('page-scripts.sh')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
