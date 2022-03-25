@extends('components.controls.input')

@section('control-attrs')
@endsection

@section('control')
<input
	class="form-control flex-fill {{ isset($readonly) && $readonly == 'plaintext' ? 'form-control-plaintext' : '' }} {{ $inputClass ?? '' }}"
	@isset($id)
	id="{{ $id }}"
	@endisset
	@isset($name)
	name="{{ $name }}"
	@endisset
	type="number"
	@isset($placeholder)
	placeholder="{{ $placeholder }}"
	@endisset
	@isset($autocomplete)
	autocomplete="{{ $autocomplete }}"
	@endisset
	@isset($autofocus)
	autofocus="true"
	@endisset
	@isset($readonly)
	readonly="readonly"
	@endisset
	@isset($required)
	required="required"
	@endisset
	@isset($disabled)
	disabled="disabled"
	@endisset
	@section('input-attrs')
	@show
	/>
@endsection

@if (!x_has_yield('page-scripts.sh', 'controls/input.js'))
@section('page-scripts.sh')
	@parent
	<script src="{{ asset('assets/js/components/controls/number.js') }}"></script>
@stop
@endif
