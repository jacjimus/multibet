<!-- matches -->
<section id="fs-matches" style="background-image: url('{{ asset('assets/images/fstats/back.jpg?v=' . config('app.build')) }}');">
	<div class="container">
		@include('fstats.welcome.date-links')
		@include('fstats.welcome.matches-table')
	</div>
</section>
<!-- /matches -->

{{-- matches css --}}
@section('page-head')
	@parent
	<x-controls.css :href="asset('assets/css/fstats/matches.css')" />
@endsection

{{-- matches js --}}
@section('page-scripts')
	@parent
	<x-controls.js :src="asset('assets/js/fstats/matches.js')" />
@endsection
