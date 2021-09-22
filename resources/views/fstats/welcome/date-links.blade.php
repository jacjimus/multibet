<!-- date-links -->
<div class="date-links table-wrapper shadow mt-3">
	<table>
		<tr>
			<!-- datepicker -->
			<td class="text-nowrap text-center">
				@include('fstats.welcome.datepicker')
			</td>

			<!-- date links -->
			@if (!empty($fs_date_links))
			@foreach ($fs_date_links as $item)
			<td class="text-nowrap text-center">
				<x-controls.link
				    :class="$item['active'] ? 'active' : ''"
				    href="javascript:"
				    :fs-matches="$item['date']"
				    title="{{ $item['day_date'] }}"
				    data-time="{{ $item['time'] }}"
				    :text="$item['text']" />
			</td>
			@endforeach
			@endif
		</tr>
	</table>
</div>
<!-- /date-links -->

{{-- date-links css --}}
@section('page-head')
	@parent
	<x-controls.css :href="asset('assets/css/fstats/date-links.css')" />
@endsection

