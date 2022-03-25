@props(['menu', 'guestMenu', 'authMenu'])

<!-- navbar -->
<nav {{ $attributes->merge(['id' => 'navbar', 'class' => 'navbar navbar-expand-lg navbar-dark fixed-top' ]) }}>
	<div class="container">

		<!-- brand -->
		<x-controls.link class="navbar-brand" scroll-to="#top" href="{{ url('/') == url()->current() ? 'javascript:' : '/' }}">
			<x-controls.img :src="asset(config('app.logo'))" :alt="config('app.name')" />
		</x-controls.link>

		<!-- toggler -->
		<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#menu" aria-expanded="false">
			@lang('Menu')
			<x-controls.fa icon="bars" class="ml-1" />
		</button>

		<!-- collapse -->
		<div class="collapse navbar-collapse" id="menu">
			<ul class="navbar-nav ml-auto">

				<!-- menu -->
				{{ $menu ?? '' }}

				@guest
				<!-- guest -->
				{{ $guestMenu ?? '' }}



				<!-- guest - login -->
				<li class="nav-item">
					<x-controls.link class="nav-link" :href="url('login')" :text="trans('auth.login-nav')" />
				</li>
				<!-- /guest -->
				@endguest

				@auth
				<!-- auth -->
				{{ $authMenu ?? '' }}

				{{--
				<!-- auth - home -->
				<li class="nav-item">
					<x-controls.link class="nav-link" :href="url('home')" :text="trans('Home')" />
				</li>

				<!-- auth - notifications -->
				<li class="nav-item">
					<x-controls.link class="nav-link icon-badge" href="#" open-notifications :title="trans('Notifications')">
						<x-controls.fa icon="bell" />
						<span class="badge">100</span>
					</x-controls.link>
				</li>

				<!-- auth - inbox -->
				<li class="nav-item">
					<x-controls.link class="nav-link icon-badge" href="#" x-open="inbox" :title="trans('Inbox')">
						<x-controls.fa icon="envelope" />
						<span class="badge">100</span>
					</x-controls.link>
				</li>
				--}}

				<!-- auth - user -->
				<li class="nav-item dropdown navbar-user">

					<!-- user - avatar -->
					<x-controls.link class="nav-link dropdown-toggle p-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" data-user="{{ $app_user->id }}">
						<span class="mr-2 d-none d-lg-inline">{{ $app_user->name }}</span>
						@if($tmp = x_tstr($app_user->getAvatar()))
						<x-controls.img class="avatar" :src="$tmp" :alt="$app_user->name" />
						@else
						<x-controls.img class="avatar" :src="asset('assets/images/avatar-placeholder.png')" :alt="$app_user->name" />
						@endif
						<span class="ml-2 d-inline d-lg-none">{{ $app_user->name }}</span>
					</x-controls.link>

					<!-- user - menu -->
					<ul class="dropdown-menu">
						<!-- premium -->

						<li class="dropdown-divider"></li>

						{{--
						<!-- profile -->
						<li>
							<x-controls.link class="dropdown-item" :href="url('profile')">
								<x-controls.fa icon="user" />
								@lang('Profile')
							</x-controls.link>
						</li>

						<!-- settings -->
						<li>
							<x-controls.link class="dropdown-item" :href="url('settings')" :text="trans('Settings')" icon="cog" />
						</li>
						<li class="dropdown-divider"></li>
						--}}
						<!-- logout -->
						<li>
							<x-controls.link class="dropdown-item" data-logout :href="url('logout')">
								<x-controls.fa icon="lock" />
								@lang('Logout')
							</x-controls.link>
						</li>
					</ul>
				</li>
				<!-- /auth -->
				@endauth
			</ul>
		</div>
		<!-- /collapse -->
	</div>
</nav>
<!-- /navbar -->

{{-- navbar css --}}
@if (stripos($__env->yieldContent('page-head'), ($tmp = 'assets/css/components/page/navbar.css')) === false)
@section('page-head')
	@parent
	<x-controls.css :href="asset($tmp)" />
@endsection
@endif

{{-- navbar js --}}
@if (stripos($__env->yieldContent('page-scripts.sh'), ($tmp = 'assets/js/components/page/navbar.js')) === false)
@section('page-scripts.sh')
	@parent
	<x-controls.js :src="asset($tmp)" />
@endsection
@endif
