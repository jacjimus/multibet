@if (isset($src))
<script {{ $attributes->merge(['type' => 'text/javascript', 'src' => $src]) }}></script>
@else
<script type="text/javascript">
	{{ $slot }}
</script>
@endif
