@props(['label', 'labelClass', 'slotClass'])

<div {{ $attributes->merge(['class' => 'controls-form-group form-group' ]) }}>
	@if (x_isset_b($label))
	<label<?php if (x_isset_b($labelClass)) echo ' class="' . $labelClass . '"'; ?>>{{ $label }}</label>
	@endif
	@if (x_isset_b($slotClass))
	<div class="{{ $slotClass }}">
		{{ $slot ?? '' }}
	</div>
	@else
	{{ $slot ?? '' }}
	@endif
</div>
