@if (isset($href))
<link {{ $attributes->merge(['rel' => 'stylesheet', 'type' => 'text/css', 'href' => $href]) }}>
@else
<style type="text/css">
	{{ $slot }}
</style>
@endif
