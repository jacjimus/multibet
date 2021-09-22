@props(['id', 'env'])

@php
$id = x_tstr(x_isset_b($id) ? $id : config('services.google.analytics_id'));
$env = x_isset_b($env) ? $env : 'production';
$show_analytics = config('app.env') == $env && $id != '';
@endphp

<!-- google analytics -->
@if ($show_analytics)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $id }}"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '{{ $id }}');
</script>
@endif
<!-- /google analytics -->
