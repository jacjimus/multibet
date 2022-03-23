
<!------ Include the above in your HEAD tag ---------->
<div class="container">
    <div class="row">
        <div id="filter-panel" class="collapse filter-panel">
            <div class="panel panel-default">
                <div class="panel-body">
                    {!! Form::open(['name' => 'search' , 'method' => 'GET', 'class'=>'form-inline', 'role'=>"form", 'style'=> 'background: indianred; padding: 20px;'])   !!}
                    {!! Form::hidden('fs_date' , $fs_show_date) !!}
                    <div class="row" style="padding: 20px;">
                        <div class="form-group">
                            <label class="filter-col" style="margin-right:0;" for="top">Find: </label>
                            &nbsp;{!! Form::select('top' ,['-1' => 'All' , '10' => 'Top 10', '100' => 'Top 100' , '1000'=>'Top 1,000'] , old('top'), ['class' => 'form-control']) !!}&nbsp;

                        </div>
                        <div class="form-group">
                            <label class="filter-col" style="margin-right:0;" for="league">&nbsp;teams from &nbsp;</label>
                        <select name="league" id="league" class="form-control">
                            <option value="-1">All leagues</option>
                            @foreach($leagues AS $l)
                                <option value="{!! $l->league_id !!} ">{!! $l->name !!} ({!! $l->country !!})</option>
                                @endforeach
                        </select>
                        </div> <!-- form group [rows] -->
                    </div>
                    <div class="row" style="padding: 20px;">
                        <div class="form-group">
                            <label class="filter-col" style="margin-right:0;" for="date"> having more than </label>
                            &nbsp;{!! Form::select('occurrence'  ,['100' => '100%',  '80' => '80%', '50' => '50%', '10' => '10%','0' => '0%',
                                      ], old('occurrence'), ['class' => 'form-control' , 'style' => 'text-align: right;']) !!} &nbsp;

                        </div><!-- form group [search] -->
                        <div class="form-group">
                            <label class="filter-col" style="margin-right:0;" for="date"> % occurrence in </label>
                            &nbsp; {!! Form::select('games'  , ['-1' => 'All games' , '5' => 'Last 5' , '10' => 'Last 10'], old('games'), ['class' => 'form-control']) !!}&nbsp;

                        </div> <!-- form group [order by] -->

                            <div class="form-group">
                                <label class="filter-col" style="margin-right:0;" for="date"> for betting game </label>
                                &nbsp; {!! Form::select('betting'  , ['double' => 'Double chance' ], old('betting'), ['class' => 'form-control']) !!}&nbsp;

                            </div>
                    </div>
                    <div class="row" style="padding: 20px;">
                            <div class="form-group">
                                <label class="filter-col" style="margin-right:0;" for="date"> tip is </label>
                                &nbsp; {!! Form::select('tip'  , ['12' => '1 or 2', '1x'=>'1 or X', '2x' => '2 or X' ], old('tip'), ['class' => 'form-control']) !!}&nbsp;

                            </div>
                            <div class="form-group">
                                <label class="filter-col" style="margin-right:0;" for="date"> when they are playing  </label>
                                &nbsp; {!! Form::select('play'  , ['home' => 'at Home', 'away'=>'Away', 'any' => 'Home + Away' ], old('play'), ['class' => 'form-control']) !!}&nbsp;

                            </div>
                        </div>
                        <div class="form-group" style="padding-left: 200px">

                            <button type="submit" class="btn btn-success filter-col">
                                <span class="glyphicon glyphicon-search"></span> Search
                            </button>
                        </div>
                   {!! Form::close() !!}
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filter-panel">
            <span class="glyphicon glyphicon-cog"></span> Filter matches
        </button>
    </div>
</div>

{{-- datepicker css --}}
@section('page-head')
	@parent
	@if (stripos($__env->yieldContent('page-head'), 'bootstrap-datepicker.min.css') === false)
		<x-controls.css :href="asset('assets/css/lib/bootstrap-datepicker.min.css')" />
	@endif
	<x-controls.css :href="asset('assets/css/fstats/datepicker.css')" />
@endsection

{{-- datepicker js --}}
@section('page-scripts')
	@parent
	@if (stripos($__env->yieldContent('page-scripts'), 'bootstrap-datepicker.min.js') === false)
		<x-controls.js :src="asset('assets/js/lib/bootstrap-datepicker.min.js')" />
	@endif
	<x-controls.js :src="asset('assets/js/fstats/datepicker.js')" />
@endsection
