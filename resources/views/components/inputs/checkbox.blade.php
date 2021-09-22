@props(['wrapperClass', 'labelClass', 'id', 'name', 'text', 'invalid', 'disabled', 'readonly'])

@php
$id = x_isset_b($id) ? trim($id) : 'inputs-checkbox-' . (x_isset_b($name) ? trim($name) : rand(1000, 9999));
$input_class = 'custom-control-input pointer';
if (x_isset_b($invalid)) $input_class .= ' is-invalid';
$attrs = ['class' => $input_class, 'type' => 'checkbox'];
if (x_isset_b($name)) $attrs['name'] = $name;
if (x_isset_b($disabled) || x_isset_b($readonly)) $attrs['disabled'] = true;
$input_attrs = $attributes->merge($attrs);
@endphp

<div class="inputs-checkbox custom-control custom-checkbox d-flex flex-nowrap {{ $wrapperClass ?? '' }}">
	<input id="{{ $id }}" {{ $input_attrs }}>
	<label for="{{ $id }}" class="custom-control-label pointer {{ $labelClass ?? '' }}">
		@if (x_isset_b($text))
		{!! $text !!}
		@elseif (x_isset_b($slot))
		{{ $slot }}
		@endif
	</label>
</div>
