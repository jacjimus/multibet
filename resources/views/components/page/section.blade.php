@if (!isset($key) || stripos($__env->yieldContent($name), $key) === false)
	@if (isset($replace))
		@section($name)
			{{ $slot }}
		@endsection
	@else
		@section($name)
			@parent
			{{ $slot }}
		@endsection
	@endif
@endif

