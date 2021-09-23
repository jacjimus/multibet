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
		<table id="fs-matches-table" data-date="{{ $fs_show_date }}">
			<thead>
				<tr>
					<th class="text-nowrap text-center">@lang('Date/Time')</th>
					<th class="text-nowrap text-center">@lang('Home team')</th>
					<th class="text-nowrap text-center">@lang('Form')</th>
					<th class="text-nowrap text-center">@lang('Away team')</th>
					<th class="text-nowrap text-center">@lang('Form')</th>
					<th class="text-nowrap text-center">@lang('Form diff.')</th>
					<th class="text-nowrap text-center">@lang('Odds')</th>
				</tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th  class="text-nowrap text-right">
                         <x-inputs.input
                            id="form-diff"
                            name="form_diff"
                            type="text"
                            :maxlength="2"
                            :size="2"
                            style="text-align: right"
                            />
                    </th>
                    <th class="text-nowrap text-center">
                        <x-inputs.input
                            id="odds"
                            name="odds"
                            type="text"
                            :maxlength="4"
                            :size="4"
                            style="text-align: right"
                        />

                    </th>

                </tr>
			</thead>
			<tbody>
				@if (!empty($fs_match_list))
				@foreach ($fs_match_list as $item)
				<!-- match -->
				<tr data-item="@json($item)" data-fs-date="{{ $item['date'] }}"  data-fs-time-text="{{ $item['time_text'] }}">

					<!-- date -->
					<td class="text-nowrap text-center">{{ $item['time_text'] }}</td>

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
		</table>
	</div>
	<div id="fs-matches-table-alert"></div>
</div>
<!-- /matches-table -->
