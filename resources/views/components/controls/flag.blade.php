<{{ $tag ?? 'i' }} {{ $attributes->merge(['class' => 'controls-flag icon fflag fflag-' . $icon . (isset($size) ? $size : 'sm') ]) }}></{{ $tag ?? 'i' }}>

{{-- freakflags css --}}
@if (stripos($__env->yieldContent('page-head'), 'freakflags/style.css') === false)
@section('page-head')
	@parent
	<x-controls.css :href="asset('assets/css/lib/freakflags/style.css')" />
@endsection
@endif
