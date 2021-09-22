<!-- matches-table -->
<div class="matches-table shadow mt-3 min-h300 bg-dark">
	@if (!is_null($fs_fetch))
	<div id="fs-fetch-status" class="bg-primary text-center text-dark small p-1">
		<x-controls.fa class="fa-spin" icon="spinner" />
		<span class="ml-2">
			@if ($fs_fetch[0] == 1)
			@lang('Algorithm is fetching records... (This might take up to 20 seconds)')
			@else
			@lang('Analysing records...')
			@endif
		</span>
	</div>
	@endif
	<div class="table-wrapper">
		{{--<table id="fs-matches-table" data-date="{{ $fs_show_date }}">
			<thead>
				<tr>
					<th class="text-nowrap text-center">@lang('Date/Time')</th>
					<th class="text-nowrap text-center">@lang('League')</th>
					<th class="text-nowrap text-center">@lang('Home team')</th>
					<th class="text-nowrap text-center">@lang('Form')</th>
					<th class="text-nowrap text-center">@lang('Away team')</th>
					<th class="text-nowrap text-center">@lang('Form')</th>
					<th class="text-nowrap text-center">@lang('Form diff.')</th>
					<th class="text-nowrap text-center">@lang('Odds')</th>
				</tr>
			</thead>
			<tbody>
				@if (!empty($fs_match_list))
				@foreach ($fs_match_list as $item)
				<!-- match -->
				<tr data-item="@json($item)" data-fs-date="{{ $item['date'] }}"  data-fs-time-text="{{ $item['time_text'] }}">

					<!-- date -->
					<td class="text-nowrap text-center">{{ $item['time_text'] }}</td>
                    <td class="text-nowrap text-left"><a href="http://footystats.org{{ $item['league_url'] }}" target="_blank">{{ $item['league_name'] }}</a></td>

					<!-- fixture -->
                    <td class="text-left">{{ $item['home_name'] }}</td>

                    <td class="text-nowrap text-center {{$item['home_form_last5'] * 5 }}">{{ $item['home_form_last5'] * 5 }}</td>


                    <td class="text-left">{{ $item['away_name'] }}</td>
                    <td class="text-nowrap text-center {{$item['away_form_last5'] * 5 }}">{{ $item['away_form_last5'] * 5 }}</td>

					<!-- odds -->
					<td class="text-nowrap text-center ">{{ abs($item['home_form_last5'] * 5  - $item['away_form_last5'] * 5) }}</td>
                    <!-- odds -->
                    <td class="text-nowrap text-center odds-from-{{ $item['odds_from'] }}">
                        {{ $item['odds_tips'][$item['win_tip']] ?? $item['odds_tips'] }}
                    </td>


                </tr>
				@endforeach
				@endif
			</tbody>
		</table>--}}

        <table id="fs-matches-table" data-date="{{ $fs_show_date }}"></table>
        <br />
	</div>
	<div id="fs-matches-table-alert"></div>
</div>
@section('page-scripts')
    <script>
        $(function () {
            createAdvancedFilteringGrid();
        });

        function createAdvancedFilteringGrid() {
            $("#fs-matches-table").igGrid({
                autoGenerateColumns: false,
                columns: [
                    { headerText: "Match ID", key: "id", dataType: "string", hidden: true },
                    { headerText: "Date/Time", key: "time_text", dataType: "date", width: "10%" },
                    { headerText: "League", key: "league_name", dataType: "string",  width: "15%" },
                    { headerText: "Home team", key: "home_name", dataType: "string", width: "15%" },
                    { headerText: "Form", key: "home_form_last5", dataType: "number", width: "10%" },
                    { headerText: "Away team", key: "away_name", dataType: "string", width: "15%" },
                    { headerText: "Form", key: "away_form_last5", dataType: "number", width: "10%" },
                    { headerText: "Form diff.", key: "form_diff_last5", dataType: "number", width: "10%" },
                    { headerText: "Odds", key: "odds_max", dataType: "number", width: "10%" }
                ],
                dataSource: {!! json_encode($fs_match_list) !!},
                renderCheckboxes: true,
                responseDataKey: "results",
                features: [
                    {
                        name: "Filtering",
                        type: "local",
                        mode: "advanced",
                        filterDialogContainment: "window"
                    },
                    {
                        name: "Paging",
                        type: "local",
                        pageSize: 30
                    }
                ]
            });
        }
    </script>

    @endsection
