@extends('layouts.master')

@section('page-title', x_isset_b($title) ? $title : 'Error!')

@section('page-content')
<section class="bg-light flex-grow py-3">
	<div class="container">
		<div class="card mx-auto col-sm-9 col-md-6 col-lg-4 p-0" style="max-width:350px;">
			
			<!-- card body -->
			<div class="card-body text-center">
				
				<!-- icon -->
				<div class="icon-wrapper p-4">
					<x-controls.fa class="{{ $icon_class ?? 'text-warning' }}" style="width:50px;height:50px" icon="{{ $icon ?? 'exclamation-triangle' }}" />
				</div>
				
				<!-- title -->
				<h5 class="text-uppercase">{{ $title ?? 'Error!' }}</h5>
				
				<!-- message -->
				@if(x_isset_b($message))
				<p class="{{ $text_class ?? 'text-danger' }}">{{ $message }}</p>
				@endif
				
				<!-- messages -->
				@if (isset($errors) && is_array($errors))
				@foreach (array_values($errors) as $msg)
				<p class="{{ $text_class ?? 'text-danger' }}">{{ implode('<br>', array_values(x_arr($msg))) }}</p>
				@endforeach
				@endif
				
			</div>
			
			<!-- card-footer (info) -->
			<div class="card-footer text-center">
				<small class="text-muted">
					@if(x_isset_b($info))
					{{ $info }}
					@else
					You can <a href="javascript:close()">close</a> this window.
					@endif
				</small>
			</div>
		</div>
	</div>
</section>
@endsection
