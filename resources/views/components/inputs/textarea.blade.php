@props(['invalid', 'disabled', 'readonly', 'value'])

@php
$input_class = 'inputs-textarea form-control';
if (x_isset_b($invalid)) $input_class .= ' is-invalid';
$attrs = ['class' => $input_class];
if (x_isset_b($readonly)) $attrs['readonly'] = true;
if (x_isset_b($disabled)) $attrs['disabled'] = true;
$input_attrs = $attributes->merge($attrs);
@endphp

<textarea {{ $input_attrs }}>{{ x_isset_b($value) ? $value : $slot }}</textarea>
