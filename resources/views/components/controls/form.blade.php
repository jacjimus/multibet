<!-- form -->
<form {{ $attributes->merge(['class' => 'controls-form']) }}>
{{ $slot }}
</form>
<!-- /form -->

{{-- form js --}}
@if (stripos($__env->yieldContent('page-scripts.sh'), $tmp = 'assets/js/components/controls/form.js') === false)
@section('page-scripts.sh')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
