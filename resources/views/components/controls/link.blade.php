@php
$link_attrs = $attributes->merge([
	'class' => 'controls-link ' . (url($href) == url()->current() ? 'current active' : ''),
])
-> filter(function($value, $key){
	return !in_array($key, ['icon', 'text']);
});
@endphp
<a {{ $link_attrs }}>
	@isset($icon)<x-controls.fa :icon="$icon" />@endisset
	@isset($text)<?php echo $text; ?>@endisset
	{{ $slot ?? '' }}
</a>
