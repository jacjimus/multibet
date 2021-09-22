<!-- banner -->
<style>
.x-banner {
    background-color: var(--dark);
    color: var(--white);
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.x-banner .carousel-item {
	height: 150px;
}
.x-banner .carousel-item img {
	display: block;
	height: 100%;
	width: 100%;
    object-fit: cover;
	object-position: bottom;
}
</style>
<div id="slide" class="x-banner x-shadow carousel slide" data-ride="carousel" data-interval="4000">
	<ol class="carousel-indicators">
		<li data-target="#slide" data-slide-to="0" class="active"></li>
		<li data-target="#slide" data-slide-to="1"></li>
		<li data-target="#slide" data-slide-to="2"></li>
	</ol>
	<div class="carousel-inner">
		<div class="carousel-item active">
			<x-controls.img class="d-block w-100" :src="asset('assets/images/fstats/banners/1.png')" />
			<div class="carousel-caption">
				<h5 class="text-uppercase">Experience consistent wins with free daily predictions</h5>
			</div>
		</div>
		<div class="carousel-item">
			<x-controls.img class="d-block w-100" :src="asset('assets/images/fstats/banners/2.png')" />
			<div class="carousel-caption">
				<h5 class="text-uppercase">Access predictions for the next five days today</h5>
			</div>
		</div>
		<div class="carousel-item">
			<x-controls.img class="d-block w-100" :src="asset('assets/images/fstats/banners/3.png')" />
			<div class="carousel-caption">
				<h5 class="text-uppercase">Combine games over multiple days for larger odds</h5>
			</div>
		</div>
	</div>
	<a class="carousel-control-prev" href="#slide" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">@lang('Previous')</span>
	</a>
	<a class="carousel-control-next" href="#slide" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">@lang('Next')</span>
	</a>
</div>
<!-- /banner -->
