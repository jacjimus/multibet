<{{ $tag ?? 'i' }} {{ $attributes->merge(['class' => 'controls-fa icon ' . (!preg_match('/fa(s|b|r)/i', $icon = trim($icon)) ? (isset($type) ? $type : 'fas') . ' ' : '') . (stripos($icon, 'fa-') === false ? 'fa-' : '') . $icon ])->filter(function($value, $key){ return $key != 'icon'; }) }}></{{ $tag ?? 'i' }}>

{{-- fontawesome js --}}
@if (stripos($__env->yieldContent('page-head'), 'lib/use.fontawesome.all.js') === false)
@section('page-head')
	@parent
	<x-controls.js :src="asset('assets/js/lib/use.fontawesome.all.js')" />
@endsection
@endif
