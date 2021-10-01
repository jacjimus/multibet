<?php

namespace App\Services\Fstats;

use App\Models\Fstats\FsMatch;
use App\Models\Fstats\FsWdw;
use App\Models\Fstats\SpMatch;
use App\Traits\HasSettings;
use Illuminate\Support\Facades\DB;

//day in seconds
if (!defined('DAYSEC')) {
    define('DAYSEC', 24 * 60 * 60);
}

//FsMatches
class FsMatches
{
    //traits
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

    private $_date_list_count = 6;

    //matches
    private $_matches;

    private $_matches_cache_lifetime = DAYSEC; //seconds (1 day)

    //sync cache
    private $_sync_cache = 'fs-sync-%s';

    private $_sync_interval = 30 * 60; //seconds (30 min)

    private $_sync_interval_future = 3 * 60 * 60; //seconds (3 hrs)

    private $_sync_cache_life = DAYSEC; //seconds (1 day)

    //log update
    private $_log_update = false;

    //construct
    public function __construct($date=null)
    {
        $this->setDate($date);
    }

    //set date
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

    //get date list
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

    //get date list links
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

    //get matches
    public function getMatches(bool $filter=true, bool $cache_matches=false, $diff='', $odd = '')
    {
        //set vars
        $utime = $this->_utime;
        $today = $this->_today;
        $now = $this->_now;

        if ((abs(time() - $utime)/(60 * 60 * 24)) > 30) {
            return [];
        }

        $cache = md5('fs-matches-' . $utime . '-' . ($filter ? 1 : 0));

        //cached
        if ($cache_matches) {
            if (isset($this->_matches[$cache]) && x_is_list($items = $this->_matches[$cache], 0)) {
                return $items;
            }
            if (x_is_list($items = x_cache_get($cache), 0)) {
                return $items;
            }
        } else {
            x_cache_delete($cache);
        }

        //matches fetch
        $matches = $this->fetchMatches($diff, $odd);

        //matches filter
        if ($filter && x_is_list($matches, 0)) {
            $matches = array_values(array_filter($matches, function ($item) {
                $odds_min = isset($item['odds_tips']) && x_is_assoc($item['odds_tips']) ? min(array_values($item['odds_tips'])) : 0;
                $odds_max = isset($item['odds_tips']) && x_is_assoc($item['odds_tips']) ? max(array_values($item['odds_tips'])) : 0;
                $wdw_max = isset($item['win_max']) ? $item['win_max'] : 0;
                $form_diff_last5 = isset($item['form_diff_last5']) ? $item['form_diff_last5'] : 0;
                $form_diff_home_away = isset($item['form_diff_home_away']) ? $item['form_diff_home_away'] : 0;
                $home_form_last5 = isset($item['home_form_last5']) ? $item['home_form_last5'] : 0;
                $home_form_home_away = isset($item['home_form_home_away']) ? $item['home_form_home_away'] : 0;
                $away_form_last5 = isset($item['away_form_last5']) ? $item['away_form_last5'] : 0;
                $away_form_home_away = isset($item['away_form_home_away']) ? $item['away_form_home_away'] : 0;
                $form_min_last5 = min([$home_form_last5, $away_form_last5]);
                $form_max_last5 = max([$home_form_last5, $away_form_last5]);
                $form_min_home_away = min([$home_form_home_away, $away_form_home_away]);
                $form_max_home_away = max([$home_form_home_away, $away_form_home_away]);

                //edit filters using the variables above
                return $form_diff_last5 >= 0
                    && $form_diff_home_away >= 0
                    && $wdw_max >= 0
                    && $odds_min >= 0 && $odds_min <= 100
                    && !($home_form_last5 == 0 && $away_form_last5 == 0)
                    ; //dont remove this semicollon
            }));
        }

        //matches cache
        if (x_is_list($matches, 0)) {
            $this->_matches[$cache] = $matches;
            x_cache_set($cache, $matches, $this->_matches_cache_lifetime);
        }

        //result - matches
        return $matches;
    }

    //fetch matches
    public function fetchMatches($diff = '', $odd = '')
    {
        //set vars
        $now = $this->_now;
        $today = $this->_today;
        $utime = $this->_utime;
        $key = sprintf($this->_sync_cache, $utime);
        $builder = FsMatch::where('fstats_fs_matches.date', $utime);

        if ($odd) {
            $odd_arr = explode('-', $odd);
            $builder->join('fstats_fs_wdws', 'fstats_fs_wdws.fs_match_id', 'fstats_fs_matches.id');
            if (count($odd_arr) > 1) {
                $builder->whereBetween(DB::raw('IF(away_form_last5 > home_form_last5, fstats_fs_wdws.away_odds, fstats_fs_wdws.home_odds)'), $odd_arr)->orderBy('time', 'asc');
            } elseif (count($odd_arr) == 1) {
                $builder->where(DB::raw('IF(away_form_last5 > home_form_last5, fstats_fs_wdws.away_odds, fstats_fs_wdws.home_odds)'), '>=', $odd_arr[0])->orderBy('time', 'asc');
            }
        }
        if ($diff) {
            $builder->where(DB::raw('ROUND(ABS(away_form_last5 - home_form_last5) * 5, 0)'), '>=', $diff);
        }
        //matches query
        $query = $builder->orderBy('time', 'asc');

        //matches update
        $sync = (int) x_cache_get($key);
        x_cache_set($key, $now, $this->_sync_cache_life);
        $update = 0;
        if ($utime > $today && ($now - $sync) > $this->_sync_interval_future) {
            $update = 1;
        } elseif (($now - $sync) > $this->_sync_interval) {
            $update = 2;
        }
        if (request()->has('update')) {
            $update = 2;
        }
        if (!$query->count()) {
            $update = 1;
        }
        if ($update) {
            $this->runUpdate($update);
        }

        //matches buffer
        $matches = [];
        $fs_matches = $query->get();
        if (count($fs_matches)) {

            //set matches
            $seen = [];
            foreach ($fs_matches as $fs_match) {
                //match vars
                $fs_match_id = (int) $fs_match->id;
                $date = (int) $fs_match->date;
                $time = (int) $fs_match->time;
                $h2h_url = x_tstr($fs_match->h2h_url);
                $country = x_tstr($fs_match->country);
                $league_name = x_tstr($fs_match->league_name);
                $league_url = x_tstr($fs_match->league_url);
                $home_name = x_tstr($fs_match->home_name);
                $away_name = x_tstr($fs_match->away_name);
                //$home_form = round((float) $fs_match->home_form, 2);
                $home_form_last5 = round((float) $fs_match->home_form_last5 * 5, 2);
                $home_form_home_away = round((float) $fs_match->home_form_home_away, 2);
                //$away_form = round((float) $fs_match->away_form, 2);
                $away_form_last5 = round((float) $fs_match->away_form_last5 * 5, 2);
                $away_form_home_away = round((float) $fs_match->away_form_home_away, 2);
                //$form_diff = round(abs($away_form - $home_form), 2);
                $form_diff_last5 = round(abs($home_form_last5 - $away_form_last5), 2);
                $form_diff_home_away = round(abs($home_form_home_away - $away_form_home_away), 2);
                if (in_array($h2h_url, $seen)) {
                    continue;
                }
                $seen[] = $h2h_url;

                //match status
                $status = 'past';
                if ($time > $now) {
                    $status = 'upcoming';
                } elseif ($time <= $now && ($now - $time) <= $this->_match_duration) {
                    $status = 'live';
                }

                //match score
                $home_score = is_numeric($tmp = $fs_match->home_score) ? (int) $tmp : null;
                $away_score = is_numeric($tmp = $fs_match->away_score) ? (int) $tmp : null;
                $score_from = is_integer($home_score) || is_integer($away_score) ? 'fs' : null;

                //match win-draw-win
                $fs_wdw_id = null;
                $home_win = 0;
                $away_win = 0;
                $draw_win = 0;
                $win_max = 0;
                $win_tips = null;
                $win_tip = null;
                $home_odds = 0;
                $away_odds = 0;
                $draw_odds = 0;
                $odds_from = null;

                //setup match win-draw-win
                $fs_wdw = ($tmp = $fs_match->id) ? FsWdw::where('fs_match_id', $tmp)->first() : null;
                if ($fs_wdw) {
                    //set vars
                    $fs_wdw_id = (int) $fs_wdw->id;
                    $home_win = round((float) $fs_wdw->home_win, 2);
                    $away_win = round((float) $fs_wdw->away_win, 2);
                    $draw_win = round((float) $fs_wdw->draw_win, 2);
                    $home_odds = round((float) $fs_wdw->home_odds, 2);
                    $away_odds = round((float) $fs_wdw->away_odds, 2);
                    $draw_odds = round((float) $fs_wdw->draw_odds, 2);
                    $odds_from = 'fs';

                    //set win tips
                    $win_tips = [
                        '1' => $home_win,
                        '2' => $away_win,
                        'X' => $draw_win,
                    ];
                    $win_max = max(array_values($win_tips));
                    if (($tmp = array_search($win_max, $win_tips)) !== false) {
                        $win_tip = $tmp;
                    }
                }

                //win tip highest form home_away
                if ($home_form_home_away > $away_form_home_away) {
                    $win_tip = '1';
                } elseif ($away_form_home_away > $home_form_home_away) {
                    $win_tip = '2';
                }

                //match sportpesa data
                $sp_match_id = null;
                $sp_time_mismatch = null;
                $sp_match = ($tmp = $fs_match->sp_match_id) ? SpMatch::find($tmp) : null;
                if ($sp_match = $fs_match->fstats_sp_match) {
                    //set vars
                    $sp_match_id = (int) $sp_match->id;
                    $sp_time = (int) $sp_match->time;
                    if ($sp_time != $time) {
                        $sp_time_mismatch = $sp_time;
                    }

                    //use sp values
                    $country = x_tstr($sp_match->country);
                    $league_name = x_tstr($sp_match->league_name);
                    $home_name = x_tstr($sp_match->home_name);
                    $away_name = x_tstr($sp_match->away_name);

                    //use sp odds
                    $home_odds = round((float) $sp_match->home_odds, 2);
                    $away_odds = round((float) $sp_match->away_odds, 2);
                    $draw_odds = round((float) $sp_match->draw_odds, 2);
                    $odds_from = 'sp';

                    //use sp scores if undefined
                    if (!(is_integer($home_score) || is_integer($away_score))) {
                        $tmp_home_score = is_numeric($tmp = $sp_match->home_score) ? (int) $tmp : null;
                        $tmp_away_score = is_numeric($tmp = $sp_match->away_score) ? (int) $tmp : null;
                        if (is_integer($tmp_home_score) || is_integer($tmp_away_score)) {
                            $home_score = $tmp_home_score;
                            $away_score = $tmp_away_score;
                            $score_from = 'sp';
                        }
                    }
                }

                //match odds tip
                $odds_max = null;
                $odds_tips = null;
                $odds_tip = null;
                if ($home_odds > 0 || $away_odds > 0 || $draw_odds > 0) {
                    $odds_tips = [
                        '1' => $home_odds,
                        '2' => $away_odds,
                        'X' => $draw_odds,
                    ];
                    $odds_max = max(array_values($odds_tips));
                    if (($tmp = array_search($odds_max, $odds_tips)) !== false) {
                        $odds_tip = $tmp;
                    }
                }

                //score & outcome
                $score = null;
                $outcome = null;
                if (is_integer($home_score) || is_integer($away_score)) {
                    $home_score = (int) $home_score;
                    $away_score = (int) $away_score;
                    $score = sprintf('%s - %s', $home_score, $away_score);
                    $outcome = $home_score == $away_score ? 'X' : ($home_score > $away_score ? '1' : '2');
                }

                //match data
                $match = [
                    'away_form_home_away' => $away_form_home_away,
                    'away_form_last5' => $away_form_last5,
                    'away_name' => $away_name,
                    'away_odds' => $away_odds,
                    'away_score' => $away_score,
                    'away_win' => $away_win,
                    'country' => $country,
                    'date' => $utime,
                    'draw_odds' => $draw_odds,
                    'draw_win' => $draw_win,
                    'form_diff_home_away' => $form_diff_home_away,
                    'form_diff_last5' => $form_diff_last5,
                    'fs_match_id' => $fs_match_id,
                    'fs_wdw_id' => $fs_wdw_id,
                    'h2h_url' => $h2h_url,
                    'home_form_home_away' => $home_form_home_away,
                    'home_form_last5' => $home_form_last5,
                    'home_name' => $home_name,
                    'home_odds' => $home_odds,
                    'home_score' => $home_score,
                    'home_win' => $home_win,
                    'league_name' => $league_name,
                    'league_url' => $league_url,
                    'odds_from' => $odds_from,
                    'odds_max' => $odds_max,
                    'odds_tip' => $odds_tip,
                    'odds_tips' => $odds_tips,
                    'outcome' => $outcome,
                    'score' => $score,
                    'score_from' => $score_from,
                    'sp_match_id' => $sp_match_id,
                    'sp_time_mismatch' => $sp_time_mismatch,
                    'status' => $status,
                    'time' => $time,
                    'time_text' => x_udate($time, 'd-m, H:i'),
                    'win_max' => $win_max,
                    'win_tip' => $win_tip,
                    'win_tips' => $win_tips,
                ];

                //add match
                $matches[] = $match;
            }
        }

        //get un-correlated footystats win-draw-win matches
        $fs_wdw_matches = FsWdw::whereNull('fs_match_id')
        -> where('date', $utime)
        -> orderBy('id', 'asc')
        -> get();

        //adding un-correlated footystats win-draw-win matches
        if (count($fs_wdw_matches)) {
            //set matches
            foreach ($fs_wdw_matches as $fs_wdw) {
                //match vars
                $fs_wdw_id = (int) $fs_wdw->id;
                $date = (int) $fs_wdw->date;
                $fixture = x_tstr($fs_wdw->fixture);
                $home_name = null;
                $away_name = null;
                if (count($arr = x_split(' vs ', $fixture, $c, 1, 1)) == 2) {
                    $home_name = $arr[0];
                    $away_name = $arr[1];
                }
                $home_odds = round((float) $fs_wdw->home_odds, 2);
                $away_odds = round((float) $fs_wdw->away_odds, 2);
                $draw_odds = round((float) $fs_wdw->draw_odds, 2);
                $odds_from = 'fs';

                //match odds tip
                $odds_max = null;
                $odds_tips = null;
                $odds_tip = null;
                if ($home_odds > 0 || $away_odds > 0 || $draw_odds > 0) {
                    $odds_tips = [
                        '1' => $home_odds,
                        '2' => $away_odds,
                        'X' => $draw_odds,
                    ];
                    $odds_max = max(array_values($odds_tips));
                }
                if (($tmp = array_search($odds_max, $odds_tips)) !== false) {
                    $odds_tip = $tmp;
                }

                //match win-draw-win
                $home_win = round((float) $fs_wdw->home_win, 2);
                $away_win = round((float) $fs_wdw->away_win, 2);
                $draw_win = round((float) $fs_wdw->draw_win, 2);

                //set win tips
                $win_tips = [
                    '1' => $home_win,
                    '2' => $away_win,
                    'X' => $draw_win,
                ];
                $win_max = max(array_values($win_tips));
                if (($tmp = array_search($win_max, $win_tips)) !== false) {
                    $win_tip = $tmp;
                }

                //match data
                $match = [
                    'date' => $utime,
                    'time_text' => null,
                    'fs_wdw_id' => $fs_wdw_id,
                    'home_name' => $home_name,
                    'away_name' => $away_name,
                    'home_win' => $home_win,
                    'away_win' => $away_win,
                    'draw_win' => $draw_win,
                    'win_max' => $win_max,
                    'win_tips' => $win_tips,
                    'win_tip' => $win_tip,
                    'home_odds' => $home_odds,
                    'away_odds' => $away_odds,
                    'draw_odds' => $draw_odds,
                    'odds_from' => $odds_from,
                    'odds_max' => $odds_max,
                    'odds_tips' => $odds_tips,
                    'odds_tip' => $odds_tip,
                ];

                //add match
                $matches[] = $match;
            }
        }

        //get un-correlated sportpesa matches
        $sp_matches = SpMatch::whereNull('fs_match_id')
        -> where('date', $utime)
        -> orderBy('time', 'asc')
        -> get();

        //adding un-correlated sportpesa matches
        if (count($sp_matches)) {
            //set matches
            foreach ($sp_matches as $sp_match) {
                //match vars
                $sp_match_id = (int) $sp_match->id;
                $date = (int) $sp_match->date;
                $time = (int) $sp_match->time;
                $country = x_tstr($sp_match->country);
                $league_name = x_tstr($sp_match->league_name);
                $home_name = x_tstr($sp_match->home_name);
                $away_name = x_tstr($sp_match->away_name);
                $home_odds = round((float) $sp_match->home_odds, 2);
                $away_odds = round((float) $sp_match->away_odds, 2);
                $draw_odds = round((float) $sp_match->draw_odds, 2);
                $odds_from = 'sp';

                //match status
                $status = 'past';
                if ($time > $now) {
                    $status = 'upcoming';
                } elseif ($time <= $now && ($now - $time) <= $this->_match_duration) {
                    $status = 'live';
                }

                //match score & outcome
                $score = null;
                $outcome = null;
                $home_score = is_numeric($tmp = $sp_match->home_score) ? (int) $tmp : null;
                $away_score = is_numeric($tmp = $sp_match->away_score) ? (int) $tmp : null;
                if (is_integer($home_score) || is_integer($away_score)) {
                    $score_from = 'sp';
                    $home_score = (int) $home_score;
                    $away_score = (int) $away_score;
                    $score = sprintf('%s - %s', $home_score, $away_score);
                    $outcome = $home_score == $away_score ? 'X' : ($away_score > $away_score ? '1' : '2');
                }

                //match odds tip
                $odds_max = null;
                $odds_tips = null;
                $odds_tip = null;
                if ($home_odds > 0 || $away_odds > 0 || $draw_odds > 0) {
                    $odds_tips = [
                        '1' => $home_odds,
                        '2' => $away_odds,
                        'X' => $draw_odds,
                    ];
                    $odds_max = max(array_values($odds_tips));
                    if (($tmp = array_search($odds_max, $odds_tips)) !== false) {
                        $odds_tip = $tmp;
                    }
                }

                //match data
                $match = [
                    'date' => $utime,
                    'sp_match_id' => $sp_match_id,
                    'time' => $time,
                    'time_text' => x_udate($time, 'd-m, H:i'),
                    'country' => $country,
                    'league_name' => $league_name,
                    'home_name' => $home_name,
                    'away_name' => $away_name,
                    'status' => $status,
                    'home_score' => $home_score,
                    'away_score' => $away_score,
                    'score_from' => $score_from,
                    'score' => $score,
                    'outcome' => $outcome,
                    'home_odds' => $home_odds,
                    'away_odds' => $away_odds,
                    'draw_odds' => $draw_odds,
                    'odds_from' => $odds_from,
                    'odds_max' => $odds_max,
                    'odds_tips' => $odds_tips,
                    'odds_tip' => $odds_tip,
                ];

                //add match
                $matches[] = $match;
            }
        }

        //result - matches
        return $matches;
    }

    //run update ($mode: 1=all --update=1, 2=fs --update=1)
    public function runUpdate($mode=1)
    {
        //no auto updates on local
        if (!request()->has('update') && config('app.env') == 'local') {
            return;
        }

        //update time
        $utime = $this->_utime;

        //ignore updating
        if (x_cache_get("fs-fetch-$utime")) {
            return 1;
        }

        //run update command
        $date = x_udate($utime, 'Y-m-d');
        $cmd = "fs:fetch all --update 1 --date $date";
        if ($mode == 2) {
            $cmd = "fs:fetch fs --update 1 --date $date";
        }
        $pid = x_worker($cmd);

        //cache updating
        x_cache_set("fs-fetch-$utime", [$mode, $pid, $utime, x_utime()]);
    }
}
