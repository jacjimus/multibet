<!-- footer -->
<style>
footer#footer {
	background-color: var(--black);
	color: var(--white);
    text-align: center;
    font-size: 0.9rem;
    font-family: var(--font-family-montserrat);
    padding: 3rem 0 2rem;
}
footer#footer a {
	color: inherit;
}
</style>
<footer id="footer">
	<div class="container">
		<div class="row align-items-center">
			<!-- Copyright -->
			<div class="col-lg-4 text-lg-left">
				@if (isset($copyright) && strlen($copyright = trim($copyright)))
				<?php echo $copyright; ?>
				@else
				@lang('Copyright') &copy; {{ date('Y') }} {{ config('app.name') }}<br>@lang('All rights reserved.')
				@endif
			</div>

			</div>


			</div>
		</div>
	</div>
</footer>
<!-- /footer -->
