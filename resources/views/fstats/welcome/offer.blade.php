<!-- offer -->
<style>
section#offer .x-premium-box {
    color: var(--white);
}
section#offer .x-premium-box > p {
    margin: 0 0 10px;
}
section#offer .x-price-box {
    border-radius: 5px;
    background-color: var(--black-7);
    padding: 20px;
}
section#offer .x-price-flex {
    display: flex;
    flex-direction: row;
    margin-bottom: 10px;
}
section#offer .x-price-flex .x-prices {
    flex-grow: 1;
}
section#offer .x-price-flex .x-prices > .x-crossed {
    margin: 0;
    color: var(--light);
    text-decoration: line-through;
}
section#offer .x-price-flex .x-prices > .x-current {
    margin: 0;
    font-size: 1.5rem;
}
section#offer .x-price-flex .x-save {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<section id="offer" class="page-section bg-dark text-white">
	<div class="container">
		<div class="row">
			<div class="col col-md-7">
				<h2 class="section-heading text-uppercase text-yellow">@lang('fstats.max-profits')</h2>
				<p>@lang('fstats.welcome-premium')</p>
			</div>
			<div class="col">
				<div class="x-premium-box">
					<p class="x-offer-country">
						<x-controls.flag icon="KE" />
						@lang('fstats.offer-country', ['country' => 'Kenya'])
					</p>
					<div class="x-price-box">
						<div class="x-price-flex">
							<div class="x-prices">
								<p class="x-crossed">@lang('fstats.offer-original') <small>@lang('fstats.pm')</small></p>
								<p class="x-current">@lang('fstats.offer-current') <small>@lang('fstats.pm')</small></p>
							</div>
							<div class="x-save">
								<p class="text-success">@lang('fstats.offer-save')</p>
							</div>
						</div>
						<a class="btn btn-primary btn-lg btn-block text-dark" href="{{ url('/register') }}">
							@lang('fstats.join-premium')
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- /offer -->
