
<!-- matches-table -->
<div class="matches-table shadow mt-3 min-h300 bg-dark">
	@if (!is_null($fs_fetch ?? ''))
	<div id="fs-fetch-status" class="bg-primary text-center text-dark small p-1">
		<x-controls.fa class="fa-spin" icon="spinner" />
		<span class="ml-2">
			@if ($fs_fetch ?? null)
			@lang('Algorithm is fetching records... (This might take up to 20 seconds)')
			@else
			@lang('Analysing records...')
			@endif
		</span>
	</div>
	@endif
	<div class="table-wrapper">
            <table id="fs-matches-table" data-date="{{ $fs_show_date }}" style="width:100%">
			<thead>
            <tr>
                <td colspan="10">

                    <div class="navigation" style="float:right">
                        {{ $fs_match_list->appends($_GET)->links("pagination::bootstrap-4") }}
                    </div>
                </td>

            </tr>
				<tr>
					<th class="text-nowrap text-center">@lang('FID')</th>
					<th class="text-nowrap text-center">@lang('Date')</th>
					<th class="text-nowrap text-center">@lang('Time')</th>
					<th class="text-nowrap text-center">@lang('Match')</th>
					<th class="text-nowrap text-center">@lang('League')</th>
					<th class="text-nowrap text-center">@lang('Country')</th>
					<th class="text-nowrap text-center">@lang('Status')</th>
					<th class="text-nowrap text-center">@lang('HT odds')</th>
					<th class="text-nowrap text-center">@lang('Draw odds')</th>
					<th class="text-nowrap text-center">@lang('AT odds')</th>
				</tr>


			</thead>
			<tbody>
				@if (!empty($fs_match_list))
				@foreach ($fs_match_list as $key=>$item)
				<!-- match -->
				<d data-item="@json($item)" data-fs-date="{{ $item['fixture_date'] }}" >
                    <tr>
					<!-- date -->
					<td class="text-nowrap text-center">{{ $item['fixture_id'] }}</td>
					<td class="text-nowrap text-center">{{ Carbon\Carbon::create($item['fixture_date'])->format('d M, Y') }}</td>
					<td class="text-nowrap" data-fs-time-text="{{ Carbon\Carbon::create($item['fixture_date'])->format('G:i A') }}">
                        {{ Carbon\Carbon::create($item['fixture_date'])->format('G:i A') }}
                    </td>

					<!-- fixture -->
                    <td class="text-left">{{ $item['home_team'] }} <small>vs</small> {{ $item['away_team'] }}</td>

                    <td class="text-nowrap text-left"> {{ $item['league'] }}</td>
                        <td class="text-nowrap text-left"> {{ $item['country'] }}</td>
                        <td class="text-nowrap text-left">
                            <span class="badge badge-{!! getBadge($item['status']) !!}" title="{{ $item['status_long'] }}">{{$item['status']}}</span>
                        </td>

                        <td class="text-nowrap text-right">{{ $item['home_team_odds'] }}</td>
                        <td class="text-nowrap text-right">{{ $item['draw_odds'] }}</td>
                        <td class="text-nowrap text-right">{{ $item['away_team_odds'] }}</td>

                </tr>

				@endforeach
                <tr>
                    <td colspan="10">
                        <div class="navigation" style="float:right">
                            {{ $fs_match_list->appends($_GET)->links("pagination::bootstrap-4") }}
                        </div>
                    </td>

                </tr>

				@endif
			</tbody>
		</table>
	</div>
	<div id="fs-matches-table-alert"></div>
</div>
   @php
    function getBadge($st) {
    switch ($st) {
    	case 'FT':
    		return 'success';
    		break;
        case '1H':
    		return 'primary';
    		break;

        case '2H':
    		return 'secondary';
    		break;

        case 'HT':
    		return 'dark';
    		break;

       case 'PST':
       	return 'warning';
    		break;

       case 'CANC':
       	return 'danger';
    		break;

        default:
    		return 'info';
    		break;

    }
 }

@endphp


<!-- /matches-table -->
