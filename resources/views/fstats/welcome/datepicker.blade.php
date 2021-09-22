<x-controls.link
	id="fs-datepicker"
	href="javascript:"
	:data-date="$fs_show_date"
	:title="trans('Calendar')"
	icon="calendar" />

{{-- datepicker css --}}
@section('page-head')
	@parent
	@if (stripos($__env->yieldContent('page-head'), 'bootstrap-datepicker.min.css') === false)
		<x-controls.css :href="asset('assets/css/lib/bootstrap-datepicker.min.css')" />
	@endif
	<x-controls.css :href="asset('assets/css/fstats/datepicker.css')" />
@endsection

{{-- datepicker js --}}
@section('page-scripts')
	@parent
	@if (stripos($__env->yieldContent('page-scripts'), 'bootstrap-datepicker.min.js') === false)
		<x-controls.js :src="asset('assets/js/lib/bootstrap-datepicker.min.js')" />
	@endif
	<x-controls.js :src="asset('assets/js/fstats/datepicker.js')" />
@endsection
