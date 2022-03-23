<?php

namespace App\Services\ApiFootball;

use App\Jobs\UpdateOdds;
use App\Models\Fixtures;
use App\Traits\HasSettings;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixtureService extends BaseService
{
    use HasSettings;

    //public vars
    public $start_date;

    public $end_date;

    public $show_date;

    public $is_updating;

    //private vars
    private $_now;

    private $_utime;

    private $_today;

    private $_yesterday;

    private $_tomorrow;

    private $_match_duration = (120 * 60); //seconds (120 min)

    //date list
    private $_date_list;

    private $_date_list_links;

    private $_date_list_count = 4;

    //matches
    private $_matches;

    private $_matches_cache_lifetime = DAYSEC; //seconds (1 day)

    //sync cache
    private $_sync_cache = 'fs-sync-%s';

    private $_sync_interval = 30 * 60; //seconds (30 min)

    private $_sync_interval_future = 3 * 60 * 60; //seconds (3 hrs)

    private $_sync_cache_life = DAYSEC; //seconds (1 day)

    //log update
    private bool $_log_update = false;

    protected $suffix = 'fixtures';

    protected string $date;

    public function __construct($date)
    {
        $this->date = $date;
        $this->setDate($date);
    }

    public function setDate($date)
    {
        $this->_now = $now = x_utime();
        $this->_utime = $utime = x_utime($date, 1, 'Y-m-d');
        $this->_today = x_utime(null, 1);
        $this->_yesterday = $this->_today - DAYSEC;
        $this->_tomorrow = $this->_today + DAYSEC;
        $this->_date_list = null;
        $this->_date_list_links = null;
        $this->_matches = [];
        $this->start_date = x_udate($now - (DAYSEC * 30), 'Y-m-d'); //now - 30 days
        $this->end_date = x_udate($now + (DAYSEC * 7), 'Y-m-d'); //now + 7 days
        $this->show_date = x_udate($utime, 'Y-m-d'); //show date

        return $this;
    }

    /**
     * @throws GuzzleException
     * @throws \Nette\Utils\JsonException
     */
    public function data(Request $request): LengthAwarePaginator
    {
        $params = ['date' => $this->date, 'timezone' => 'Africa/Nairobi'];
        $verb = Str::singular($this->suffix);
        $data = [];
        $cacheKey = md5((string) json_encode($this->date. '_matches'));
        $response = Cache::remember($cacheKey, 3600, $this->getData($this->suffix, $params));
        foreach ($response['response'] as $key=>$res) {
            array_key_exists($verb, $res) ? array_push(
                $data,
                ['fixture_id' => $res[$verb]['id'],
                    'fixture_date' => x_udate($res[$verb]['date']),
                    'league_id' => $res['league']['id'],
                    'league' => $res['league']['name'],
                    'country' => $res['league']['country'],
                    'home_team' => $res['teams']['home']['name'],
                    'away_team' => $res['teams']['away']['name'],
                    'status' => $res[$verb]['status']['short'],
                    'status_long' => $res[$verb]['status']['long'],
                    'results' => $res['goals']['home'] > $res['goals']['away'] ? 1 : 2,
                    ]
            ) : '';
        }
        if (Fixtures::upsert($data, 'fixture_id')) {
            UpdateOdds::dispatch($this->date, $data);
        }
        $top = $request->get('top') ?? null;
        $occurrence = $request->get('occurrence') ?? null;
        $league = $request->get('league') ?? null;
        $betting = $request->get('league') ?? null;
        $tip = $request->get('tip') ?? null;
        $play = $request->get('play') ?? null;
        $query = Fixtures::where(DB::raw('DATE(fixture_date)'), $this->date);
        if (isset($league) && $league && $league != '-1') {
            $query->where('league_id', (int) $league);
        }

        if (isset($top) && $top && $top != '-1') {
            $query->limit($top);
        }

        return $query->orderBy('fixture_date')->paginate(30);
    }

    public function getDateListLinks()
    {
        //instance cached
        if (x_is_list($items = $this->_date_list_links, 0)) {
            return $items;
        }

        //get date list
        $date_list = $this->getDateList();

        //set date links
        $date_links = [];
        foreach ($date_list as $time) {
            $day = x_udate($time, 'D');
            $date = x_udate($time, 'Y-m-d');
            if ($time == $this->_yesterday) {
                $text = 'Yesterday';
            } elseif ($time == $this->_tomorrow) {
                $text = 'Tomorrow';
            } elseif ($time == $this->_today) {
                $text = 'Today';
            } else {
                $text = "$day $date";
            }
            $date_links[] = [
                'time' => $time,
                'day' => $day,
                'day_date' => "$day $date",
                'date' => $date,
                'text' => $text,
                'active' => $this->_utime == $time,
            ];
        }

        //result - date links (instance cache)
        return $this->_date_list_links = $date_links;
    }

    public function getDateList()
    {
        //instance cached
        if (x_is_list($items = $this->_date_list, 0)) {
            return $items;
        }

        //set vars
        $utime = $this->_utime;
        $today = $this->_today;
        $count = $this->_date_list_count;

        //set dates before & after utime (array[$count - 1] * 2);
        $before = [];
        $after = [];
        for ($i = 0; $i < $count; $i ++) {
            $n = DAYSEC * ($i + 1);
            $before[] = $utime - $n;
            $after[] = $utime + $n;
        }

        //date list array - merged (before, utime, after)
        $date_list = array_merge(array_reverse($before), [$utime], $after);

        //date list limit to $count items - bias today
        //if utime is in the future
        if ($utime >= $today) {
            //get utime index in date list
            $x = array_search($utime, $date_list);

            //if utime index and yesterday index difference is within date list count and yesterday index comes before utime index
            if (($i = array_search($this->_yesterday, $date_list)) !== false && abs($i - $x) < $count) {
                //set date list slice - offset yesterday index, count
                $date_list = array_slice($date_list, $i, $count);
            }

            //if utime index and today index difference is within date list count and today index comes before utime index
            elseif (($i = array_search($today, $date_list)) !== false && abs($i - $x) < $count) {
                //set date list slice - offset today index, count
                $date_list = array_slice($date_list, $i, $count);
            }

            //set date list slice - offset index = past (utime index - (limit $count + 1)), $count
            else {
                $date_list = array_slice($date_list, $x - $count + 1, $count);
            }
        }

        //if utime is in the past
        else {
            //get utime index in date list
            $x = array_search($utime, $date_list);

            //set date list slice - offset utime index, count
            $date_list = array_slice($date_list, $x, $count);
        }

        //result - date list (instance cache)
        return $this->_date_list = $date_list;
    }
}
