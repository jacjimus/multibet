<!-- form -->
<form {{ $attributes->merge(['class' => 'controls-form']) }}>
{{ $slot }}
</form>
<!-- /form -->

{{-- form js --}}
@if (stripos($__env->yieldContent('page-scripts'), $tmp = 'assets/js/components/controls/form.js') === false)
@section('page-scripts')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
