<?php

namespace App\Services\Fstats;

use App\Models\Fstats\FsMatch;
use App\Models\Fstats\SpCorrelation;
use App\Models\Fstats\SpMatch;

class Sportpesa extends FsSession
{
    //fs session vars
    protected $_cache = 'sportpesa';

    protected $_cache_tags = ['sportpesa', 'fs'];

    //private vars
    private $_pag_count = 100;

    private $_loops_max = 5;

    private $_fetch_url;

    private $_match_similarity_min = 0.4;

    private $_min_similarity_teams = 0.5;

    private $_min_similarity_avg = 0.5;

    private $_min_keys_avg = 0.4;

    private $_correlate_cache = [];

    //construct
    public function __construct()
    {
        //set fetch_url
        $this->_fetch_url = 'https://www.ke.sportpesa.com/api/upcoming/games?'
        . 'type=prematch'
        . '&sportId=1'
        . '&section=upcoming'
        . '&markets_layout=multiple'
        . '&o=leagues'
        . '&pag_count={pag_count}'
        . '&pag_min={pag_min}'
        . '&from={from}'
        . '&to={to}';
    }

    //get request headers
    public function getHeaders()
    {
        //headers default
        $headers = [
            'Host' => 'www.ke.sportpesa.com',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate, br',
            'DNT' => 1,
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => 1
        ];

        //cookie header
        if (x_is_assoc($session = $this->getSession())) {
            $cookie = [
                '__utmzzses' => '1',
                'device_view' => 'full',
                'initialTrafficSource' => 'utmcsr=(direct)|utmcmd=(none)|utmccn=(not set)',
                'spkessid' => '',
                'visited' => '1',
            ];
            if ($spkessid = x_array_get('spkessid', $session)) {
                $cookie['spkessid'] = $spkessid;
            }
            if (!empty($cookie)) {
                $cookies = [];
                foreach ($cookie as $key => $value) {
                    $cookies[] = sprintf('%s=%s', $key, urlencode($value));
                }
                $headers['Cookie'] = implode('; ', $cookies);
            }
        }

        //result - headers
        return $headers;
    }

    //similarity
    private function similarity($a, $b)
    {
        $a = strtolower(x_tstr($a));
        $b = strtolower(x_tstr($b));
        similar_text($a, $b, $s1);
        $s1 = $s1/100;
        similar_text($b, $a, $s2);
        $s2 = $s2/100;

        return round(($s1 + $s2)/2, 2);
    }

    //fetch matches ($update: 0 = create only, 1 = scores only, 2 = update)
    public function fetchMatches(int $update=0, $date=null, string $date_parse_format='Y-m-d')
    {
        //fetch mode & timestamp
        $update_modes = ['create only', 'create or update only scores', 'create or update'];
        $update = is_integer($update) && $update >= 0 && $update < count($update_modes) ? $update : 0;
        $utime = x_utime($date, 1, $date_parse_format);
        $ntime = x_utime(null, 1);
        if ($utime > $ntime) {
            $update = 2;
        }

        //output
        x_dump(sprintf(
            ' - fetch matches: %s (%s)',
            x_udate($utime, $date_parse_format),
            $update_modes[$update]
        ));

        //setup url template
        $url_temp = str_replace(
            ['{pag_count}', '{from}', '{to}'],
            [$this->_pag_count, $utime, $utime + 86399],
            $this->_fetch_url
        );

        //fetch data & save
        $loops = 1;
        $pag_min = 1;
        $matches_count = 0;
        while ($loops <= $this->_loops_max) {
            $loops ++;

            //url
            $url = str_replace('{pag_min}', $pag_min, $url_temp);
            $pag_min_prev = $pag_min;
            $pag_min += $this->_pag_count;

            //output
            x_dump('', ' - fetching: ' . $url);

            //fetch request
            $response = $this->getRequestService()->request(
                $url,
                $data=null,
                $this->getHeaders(),
                $is_post=false,
                $cached=true,
                $save_body=false,
                $trim_body=true,
            );

            //parse response (json)
            $items = json_decode($response['body'], 1);

            //break fetch loop if no items
            if (!(is_array($items) && count($items))) {
                //output
                x_dump(' - response empty. (end fetch)', '');

                //break
                break;
            }

            //count items
            $count_items = count($items);

            //output
            x_dump('', sprintf(' - Results: %s - %s (%s)', $pag_min_prev, $pag_min - 1, $count_items));

            //get matches
            foreach ($items as $i => $item) {
                //index number
                $num = ($i + 1) . '/' . $count_items;

                //match vars
                $league_name = x_tstr(x_array_get('competition.name', $item));
                $country = x_tstr(x_array_get('country.name', $item));
                $comp_id = (int) x_array_get('competition.id', $item);
                $match_id = (int) x_array_get('id', $item);
                $sms_id = (int) x_array_get('smsId', $item);

                //match time
                $time = (int) x_array_get('dateTimestamp', $item);
                if (strlen("$time") >= 13) {
                    $time = (int) ($time / 1000);
                }

                //match teams
                $home_id = (int) x_array_get('competitors.0.id', $item);
                $home_name = x_tstr(x_array_get('competitors.0.name', $item));
                $away_id = (int) x_array_get('competitors.1.id', $item);
                $away_name = x_tstr(x_array_get('competitors.1.name', $item));

                //match odds
                $home_odds = null;
                $away_odds = null;
                $draw_odds = null;
                if (is_array($markets = x_array_get('markets', $item))) {
                    foreach ($markets as $market) {
                        if (strpos(strtolower(x_array_get('name', $market)), '3 way') === false) {
                            continue;
                        }
                        $home_odds = round((float) x_array_get('selections.0.odds', $market), 2);
                        $draw_odds = round((float) x_array_get('selections.1.odds', $market), 2);
                        $away_odds = round((float) x_array_get('selections.2.odds', $market), 2);

                        break;
                    }
                }

                //skip required data validation fail
                if (!(
                    $time > 0
                    && strlen("$time") == 10
                    && strlen($home_name)
                    && strlen($away_name)
                )) {
                    //output
                    x_dump($data, ' - skip: (%s) match data validation failed.', $num);

                    //next
                    continue;
                }

                //skip fetch date/time mismatch
                $d_time = x_udate($time, 'Y-m-d');
                $d_utime = x_udate($utime, 'Y-m-d');
                if ($d_time != $d_utime) {
                    //output
                    x_dump($game, sprintf(
                        ' - skip: (%s) fetch date/time mismatch ["%s" - "%s"].',
                        $num,
                        $d_time,
                        $d_utime
                    ));

                    //next
                    continue;
                }

                //match data
                $data = [
                    'date' => $utime,
                    'league_name' => $league_name,
                    'country' => $country,
                    'comp_id' => $comp_id,
                    'match_id' => $match_id,
                    'sms_id' => $sms_id,
                    'time' => $time,
                    'home_id' => $home_id,
                    'home_name' => $home_name,
                    'home_odds' => $home_odds,
                    'away_id' => $away_id,
                    'away_name' => $away_name,
                    'away_odds' => $away_odds,
                    'draw_odds' => $draw_odds,
                ];

                //existing match
                $existing = SpMatch::where('date', $utime)
                -> where('time', $time)
                -> where('home_name', $home_name)
                -> where('away_name', $away_name)
                -> first();

                //count match
                $matches_count += 1;

                //debug
                $match_num = sprintf('[%d] (%s)', $matches_count, $num);
                $match_num = str_pad($match_num, strlen('[0000] (00/00)'));
                $debug = sprintf('%s %s - %s / %s', $match_num, x_udate($time, 'Y-m-d H:i'), $home_name, $away_name);

                //skip existing
                if ($existing && $update == 0) {
                    //correlate fs match
                    if (!$existing->fs_match_id && ($fs_match = $this->correlateFsMatch($data))) {
                        //update existing
                        $existing->fs_match_id = $fs_match->id;
                        $existing->save();

                        //update fs match
                        $fs_match->sp_match_id = $existing->id;
                        $fs_match->saveQuietly();

                        //output
                        x_dump(sprintf(' - %s: existing update [%s]', $debug, $fs_match->id));
                    }

                    //output
                    x_dump(sprintf(' - %s: skip existing', $debug));

                    //next
                    continue;
                }

                //creating|updating
                if (!$existing || $existing && $update == 2) {
                    //correlate fs match
                    $fs_match = null;
                    $fs_match_id = $existing ? $existing->fs_match_id : null;
                    if (!$fs_match_id && ($fs_match = $this->correlateFsMatch($data))) {
                        $data['fs_match_id'] = $fs_match_id = $fs_match->id;
                    }

                    //update
                    if ($existing) {
                        $sp_match = $existing;
                        $sp_match->update($data);
                    }

                    //create
                    else {
                        $sp_match = SpMatch::create($data);
                    }

                    //update fs match
                    if ($fs_match) {
                        $fs_match->sp_match_id = $sp_match->id;
                        $fs_match->saveQuietly();
                    }

                    //output
                    x_dump(trim(sprintf(' - %s: %s %s', $debug, $existing ? 'updated' : 'created', $fs_match_id ? " [$fs_match_id]" : '')));
                }
            }
        }

        //output - done
        x_dump(" - fetch done. ($matches_count matches)", '');
    }

    //correlate match
    private function correlateFsMatch($match)
    {
        //match vars
        $date = $match['date'];
        $time = $match['time'];
        $league_name = $match['league_name'];
        $country = $match['country'];
        $home_name = $match['home_name'];
        $away_name = $match['away_name'];

        //correlation keys
        $keys = ['league_name', 'country', 'home_name', 'away_name'];

        //set correlations
        $corrs = [];
        foreach ($keys as $key) {
            $type = in_array($key, ['home_name', 'away_name']) ? 'team' : $key;
            $corrs[$key] = SpCorrelation::where('type', $type)
            -> where('sp_name', $$key)
            -> get();
        }

        //correlations - set query or where & correlation names
        $or_where = [];
        $corr_names = [];
        if (count($corrs)) {
            foreach ($corrs as $key => $items) {
                if (count($items)) {
                    $seen = []; //seen fs names
                    foreach ($items as $item) {
                        //unique fs name
                        if (($fs_name = trim($item->fs_name)) && !in_array($fs_name, $seen)) {
                            //buffer - correlation names => similarity
                            if (!(isset($corr_names[$key]) && is_array($corr_names[$key]))) {
                                $corr_names[$key] = [];
                            }
                            $sim = round((float) $item->similarity, 2);

                            //set correlation name with higher similarity
                            if (!isset($corr_names[$key][$fs_name]) || $corr_names[$key][$fs_name] < $sim) {
                                $corr_names[$key][$fs_name] = $sim;
                            }

                            //buffer - or where
                            $or_where[] = [$key, $fs_name];

                            //add fs name to seen
                            $seen[] = $fs_name;
                        }
                    }
                }
            }
        }

        //similar query - fs matches (date)
        $query = FsMatch::whereNull('sp_match_id')
        -> where('date', $date);

        //similar query set or where - time, correlations
        $query->where(function ($query) use (&$time, &$date, &$or_where) {
            //time
            $query->where('time', $time);

            //time (date <= time >= date + 24 hours)
            $query->orWhere(function ($query) use (&$date) {
                $query->where('time', '>=', $date);
                $query->where('time', '<', $date + (24 * 60 * 60));
            });

            //correlations
            if (!empty($or_where)) {
                foreach ($or_where as $where) {
                    $query->orWhere(...$where);
                }
            }
        });

        //if no similar matches - fetch all un-correlated
        if (!count($fs_matches = $query->get())) {
            $fs_matches = FsMatch::whereNull('sp_match_id')
            -> where(function ($query) use (&$date) {
                $query->where('time', '>=', $date);
                $query->where('time', '<', $date + (24 * 60 * 60));
            })
            -> get();
        }

        //no un-correlated fs matches found - return null
        if (!count($fs_matches)) {
            return null;
        }

        //method - check correlation similarity
        $__get_corr_similarity = function ($key, $name) use (&$corr_names) {
            return isset($corr_names[$key]) && x_has_key($tmp = $corr_names[$key], $name) ? $tmp[$name] : 0;
        };

        //set similar matches
        $similar_matches = [];
        foreach ($fs_matches as $fs_match) {
            //check time similarity - ignore mismatch
            $similarity['time'] = (int) $fs_match->time == (int) $time ? 1 : 0;
            if (!$similarity['time']) {
                continue;
            }

            //set similarity
            $similarity = [];
            foreach ($keys as $key) {
                $fs_name = trim($fs_match->$key);
                $sp_name = trim($$key);

                //get similarity
                if (!($sim = $__get_corr_similarity($key, $fs_name))) {
                    $sim = $this->similarity($sp_name, $fs_name);
                }

                #debug
                $similarity['get_txt'] ="'$sp_name' - '$fs_name' = $sim";

                //set similarity
                $similarity[$key] = round($sim, 2);
            }

            //similarity - date
            $similarity['date'] = (int) $fs_match->date == (int) $date ? 1 : 0;

            //calculate similarity average - (match, keys)
            $sum = 0;
            $count = 0;
            $teams_sum = 0;
            $teams_count = 0;
            foreach ($similarity as $key => $sim) {
                if (!is_numeric($sim)) {
                    continue;
                }
                $sum += $sim;
                $count += 1;
                if (in_array($key, ['home_name', 'away_name'])) {
                    $teams_sum += $sim;
                    $teams_count += 1;
                }
            }
            $avg = round($sum/$count, 2);
            $teams_avg = round($teams_sum/$teams_count, 2);

            //add similar match
            if ($avg >= $this->_min_similarity_avg && $teams_avg >= $this->_min_similarity_teams) {
                $similar_matches[] = [
                    'avg' => $avg,
                    'teams_avg' => $teams_avg,
                    'fs_match' => $fs_match,
                    'similarity' => $similarity,
                ];
            }
        }

        //if no similar matches found - return null
        if (empty($similar_matches)) {
            return null;
        }

        //sort similar matches by average similarity (avg) desc
        $sort_column = array_column($similar_matches, 'avg');
        array_multisort($sort_column, SORT_DESC, $similar_matches);

        //get most similar
        $similar = null;
        foreach ($similar_matches as $similar_match) {
            //set similar
            if (!$similar) {
                $similar = $similar_match;

                continue; //next
            }

            //matching avg
            if ($similar_match['avg'] == $similar['avg']) {
                //choose greater teams_avg
                if ($similar_match['teams_avg'] > $similar['teams_avg']) {
                    $similar = $similar_match;
                }

                continue; //next
            }

            //break if lower avg
            else {
                break;
            }
        }

        //if no similar - return null
        if (!$similar) {
            return null;
        }

        //similar fs match
        $fs_match = $similar['fs_match'];
        $sim_avg = $similar['avg'];
        $teams_avg = $similar['teams_avg'];

        //save correlations
        foreach ($keys as $key) {
            //correlation values
            $type = in_array($key, ['home_name', 'away_name']) ? 'team' : $key;
            $sp_name = trim($$key);
            $fs_name = trim($fs_match->$key);
            $sim = $similar['similarity'][$key];

            //ignore low similarity
            if (!($sim >= 0.5 || $sim_avg >= 0.6)) {
                continue;
            }

            //ignore existing correlation
            $corr_exists = SpCorrelation::where('type', $type)
            -> where('sp_name', $sp_name)
            -> where('sp_name', $fs_name)
            -> where('similarity', $sim)
            -> where('sim_avg', $sim_avg)
            -> where('teams_avg', $teams_avg)
            -> exists();
            if ($corr_exists) {
                continue;
            }

            //create correlation
            $corr = SpCorrelation::create([
                'type' => $type,
                'sp_name' => $sp_name,
                'fs_name' => $fs_name,
                'similarity' => $sim,
                'sim_avg' => $sim_avg,
                'teams_avg' => $teams_avg,
            ]);

            //output
            //x_dump(sprintf(' - created sp-correlation %s [%d]: "%s" > "%s" (%s, %s)', $type, $corr->id, $sp_name, $fs_name, $sim, $sim_avg));
        }

        //result - similar fs match
        return $fs_match;
    }

    //correlate saved matches
    public function correlateFsMatches()
    {
        //get uncorrelated matches
        $sp_matches = SpMatch::whereNull('fs_match_id')
        -> orderBy('time', 'desc')
        -> orderBy('id', 'desc')
        -> get();

        //if no results - return
        if (!($count = count($sp_matches))) {
            //output
            x_dump(' - No uncorrelated sportpesa matches found.');

            //return
            return;
        }

        //output
        x_dump(' - correlating sportpesa - footystats matches (' . $count . ').');

        //correlate matches
        $corr_count = 0;
        foreach ($sp_matches as $i => $sp_match) {
            //index number
            $num = ($i + 1) . '/' . $count;

            //debug
            $match_num = str_pad("($num)", strlen('(000/000)'));
            $debug = sprintf(
                '%s %s - %s / %s',
                $match_num,
                x_udate((int) $sp_match->time, 'Y-m-d H:i'),
                $sp_match->home_name,
                $sp_match->away_name
            );

            //get similar fs match correlation
            if ($fs_match = $this->correlateFsMatch($sp_match->toArray())) {
                //correlation
                $corr_count += 1;

                //update
                $sp_match->update([
                    'fs_match_id' => $fs_match->id,
                ]);

                //update fs match - sp_match_id (quietly)
                $fs_match->sp_match_id = $sp_match->id;
                $fs_match->saveQuietly();

                //output
                x_dump(sprintf(' - %s: updated [%d]', $debug, $fs_match->id));
            }

            //no correlation - output
            else {
                x_dump(sprintf(' - %s: no match', $debug));
            }
        }

        //output - done
        x_dump(" - correlation done. ($corr_count/$count matches)", '');
    }
}
