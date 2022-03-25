<!-- cookies consent -->
<style>
#cookies-consent {
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    min-height: 100px;
}
</style>
<section id="cookies-consent" class="d-none bg-primary p-0">
	<div class="container">
		<div class="p-4">
			<h5>Cookies</h5>
			<p>This site uses cookies to offer you a better browsing experience. Find out more on how we use cookies and how you can change your settings.</p>
			<div class="flex flex-row flex-nowrap text-right">
				<button id="cookies-consent-accept" class="btn btn-dark text-uppercase mr-2 mt-2 text-nowrap" type="button">I accept cookies</button>
				<a href="{{ url('privacy-policy/#cookies') }}" class="btn btn-danger text-uppercase mt-2">I refuse cookies</a>
			</div>
		</div>
	</div>
</section>
<!-- /cookies consent -->

{{-- cookies-consent js --}}
@if (stripos($__env->yieldContent('page-scripts.sh'), ($tmp = 'assets/js/components/cookies-consent.js')) === false)
@section('page-scripts.sh')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
