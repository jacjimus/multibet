<?php

namespace App\Services\Fstats;

use App\Models\Fstats\FsMatch;
use App\Models\Fstats\FsWdw;
use Exception;

class Footystats extends FsSession
{
    //fs session vars
    protected $_cache = 'footystats';

    //private vars
    private $_home_url = 'https://footystats.org/';

    private $_form_types = ['last5', 'home_away'];

    private $_matches_url = 'https://footystats.org/?date=%s&sort=time&form=%s';

    private $_login_url = 'https://footystats.org/login?log_me_in=1';

    private $_wdw_url = 'https://footystats.org/ajax_wdw.php';

    private $_session_key = 'cartalyst_sentinel';

    private $_credentials;

    private $_auth = false;

    //set credentials
    public function getCredentials()
    {
        //init credentials
        if (!x_is_assoc($credentials = $this->_credentials)) {
            //get settings
            $settings = app()->make('SettingsService')->getSettings();
            if (!x_has($settings, 'footystats-uid', 'footystats-username', 'footystats-password')) {
                throw new Exception('Failed to get footystats credential settings!');
            }

            //set credentials
            $this->_credentials = $credentials = [
                'uid' => $settings['footystats-uid'],
                'username' => $settings['footystats-username'],
                'password' => $settings['footystats-password'],
            ];
        }

        //result - credentials ['username' => '', 'password' => '']
        return $credentials;
    }

    //get request headers
    public function getHeaders()
    {
        //headers default
        $headers = [
            'Host' => 'footystats.org',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0',
        ];

        //cookie header
        if (x_is_assoc($session = $this->getSession())) {
            $cookie = [];
            $cookie_keys = ['cartalyst_sentinel', 'country', 'dashboard_form', 'dashboard_sort', 'PHPSESSID', 'tz'];
            foreach ($session as $key => $value) {
                if (in_array($key, $cookie_keys)) {
                    $cookie[$key] = $value;
                }
            }
            if (!empty($cookie) && !array_key_exists('tz', $cookie)) {
                $cookie['tz'] = 'Asia/Baghdad';
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

    //nauthentication failure
    public function authFail()
    {
        //delete session
        $this->deleteSession();

        //throw auth error
        throw new Exception('Session authentication failed!');
    }

    //check authentication
    public function isAuth(string $body=null, bool $login=true)
    {
        //check authentication from body content
        if (x_is_string($body, 1)) {
            x_dump(' - checking authentication...');

            //get credentials
            $credentials = $this->getCredentials();
            if (!strlen($uid = x_trim(x_array_get('uid', $credentials)))) {
                throw new Exception('Credentials uid is undefined!');
            }
            if (!strlen($username = x_trim(x_array_get('username', $credentials)))) {
                throw new Exception('Credentials username is undefined!');
            }

            //authenticated search terms
            $search = [
                sprintf('href="/u/%s"', $uid),
                sprintf('alt="%s"', $username),
                '<a href="/logout">',
            ];

            //check if all terms exist
            $not_found = false;
            foreach ($search as $term) {
                if (strpos($body, $term) === false) {
                    x_dump(' - isAuth search missing term: (' . $term . ')');
                    $not_found = true;

                    break;
                }
            }

            //set authenticated
            if (!$not_found) {
                $this->_auth = true;
                x_dump(' - authenticated.');
            } else {
                $this->_auth = false;
            }
        }

        //login if no body content
        elseif ($login) {
            $this->login();
        }

        //result - authentication
        return $this->_auth;
    }

    //login authentication
    public function login()
    {
        //url
        $url = $this->_login_url;
        x_dump(' - login: ' . $url);

        //get credentials
        $credentials = $this->getCredentials();
        if (!strlen($username = x_trim(x_array_get('username', $credentials)))) {
            throw new Exception('Credentials username is undefined!');
        }
        if (!strlen($password = x_trim(x_array_get('password', $credentials)))) {
            throw new Exception('Credentials password is undefined!');
        }

        //login request
        $data = [
            'username' => $username,
            'password' => $password,
            'redirect' => '',
        ];
        $response = $this->getRequestService()->request(
            $url,
            $data,
            $this->getHeaders(),
            $is_post=true,
            $cached=false,
            $save_body=false,
            $trim_body=true,
        );

        //get response cookie data
        if (x_is_assoc($cookie = $this->parseCookie($response['set_cookie']))) {
            //check if cookie session key exists - cartalyst_sentinel
            if (x_has_key($cookie, $tmp = $this->_session_key) && x_is_string($cookie[$tmp], 1)) {
                $this->updateSession($response); //update session
                $this->_auth = true; //login success
                x_dump(' - login successful.');
            }
        }

        //throw exception unsuccessful login
        if (!$this->_auth && !$this->isAuth($response['body'], 0)) {
            throw new Exception('Login failed!');
        }

        //result - authentication
        return $this->_auth;
    }

    //get page html
    public function getPage(
        string $url,
        array $data=null,
        bool $cached=false,
        bool $is_post=false,
        bool $no_auth=false,
        bool $is_login_retry=false
    ) {
        //request page
        x_dump(" - getting page: $url");
        $response = $this->getRequestService()->request(
            $url,
            $data,
            $this->getHeaders(),
            $is_post,
            $cached,
            $save_body=false,
            $trim_body=true,
        );

        //check authentication
        if (!$no_auth && !$this->isAuth($response['body'])) {
            if (!$is_login_retry) {
                x_dump(' - session not authenticated (logging in and retrying).');

                //login and retry
                if ($this->login()) {
                    return $this->getPage($url, $data, $cached, $is_post, $no_auth, 1);
                }
            }

            //auth failure
            return $this->authFail();
        }

        //update session
        if (!$no_auth) {
            $this->updateSession($response);
        }

        //result - response
        return $response;
    }

    //fetch matches (update: 0=create only (ignore existing), 1=update scores only, 2=full update)
    public function fetchMatches(int $update=0, $date=null, string $date_parse_format='Y-m-d')
    {
        //fetch vars
        $update = is_integer($update) && $update >= 0 && $update <= 2 ? $update : 0;
        $utime = x_utime($date, 1, 'Y-m-d');
        $date = x_udate($utime, 'Y-m-d');
        $ntime = x_utime(null, 1);

        //update mode
        $tnow = x_utime();
        $mtime = $ntime + (12 * 60 * 60);
        $mcount = FsMatch::where('date', $utime)->count();
        $ignore_create = $utime < $ntime && $mcount || $utime == $ntime && $tnow >= $mtime && $mcount;

        //full update - future/no records
        if (!$mcount || $utime > $ntime) {
            $update = 2;
        }

        //fetch - form-type
        $_fetch_data = function ($form_type=0) use (&$update, &$matches_count, &$utime, &$ignore_create, &$date) {
            //form
            $form_types = $this->_form_types;
            $form_type = is_integer($form_type) && $form_type >= 0 && $form_type < count($form_types) ? $form_type : 0;
            $form = $form_types[$form_type];

            //output
            x_dump(" - fetch matches $utime: date=$date, update=$update, form=$form");

            //request
            $url = sprintf($this->_matches_url, $utime, $form);

            //response
            $response = $this->getPage($url, null, !$update);

            //auth check
            if (!$this->_auth) {
                return $this->authFail();
            }

            //parse response
            return $this->fetchMatchesParse($response, $update, $utime, $ignore_create, $form);
        };

        //call fetch - form_type=0
        $_fetch_data(0);

        //call fetch - form_type=1 (mode=0:new only|2:full update)
        if (in_array($update, [0, 2])) {
            $_fetch_data(1);
        }

        //output - done
        x_dump(' - fetch matches done.', '');
    }

    //fetch matches - parse response
    public function fetchMatchesParse($response, $update, $utime, $ignore_create, $form)
    {
        //parse response
        $matches_count = 0;
        $len = strlen($body = trim($response['body']));

        //output
        x_dump(" - parse response... (body length=$len, form=$form, update=$update)");

        //leagues
        $tmp = preg_quote("<div class='league'>", '/');
        $pattern = '/' . sprintf('%s(.+?(?=%s))', $tmp, $tmp) . '/s';
        preg_replace_callback($pattern, function ($league_matches) use (&$update, &$matches_count, &$utime, &$ignore_create, &$form) {
            //league
            $league_match = $league_matches[1];
            $league = [];

            //league info
            $league_url = null;
            preg_match_all("/href=(\"|')([^\"|']+)(\"|')/s", $league_match, $tmp_match, PREG_OFFSET_CAPTURE);
            if (
                is_array($tmp_match) && count($tmp_match) > 2
                && is_array($tmp_match = $tmp_match[2]) && count($tmp_match)
                && is_array($tmp_match = $tmp_match[0]) && count($tmp_match) > 1
            ) {
                $league['url'] = $tmp_match[0];
                if (($pos = stripos($league_match, '</a>', $i = $tmp_match[1])) !== false) {
                    $tmp = substr($league_match, $i, $pos + 4);
                    if (preg_match("/<p class='bold small-fu'>([^<]+)<\/p>/", $tmp, $tmp_match)) {
                        $tmp_match = $tmp_match[1];
                        if (($p = strpos($tmp_match, '-')) !== false) {
                            $league['country'] = trim(substr($tmp_match, 0, $p));
                            $league['name'] = trim(substr($tmp_match, $p + 1));
                        }
                    } elseif (preg_match_all('/>([^<]+)</', $tmp, $tmp_match)) {
                        if (count($tmp_match) >= 2 && count($tmp_match[1]) >= 2) {
                            $league['country'] = trim($tmp_match[1][0], '- ');
                            $league['name'] = trim($tmp_match[1][1]);
                        }
                    }
                }
            }

            //league games
            $games = [];
            $tmp = $tmp = preg_quote("<li class='match-stats", '/');
            $pattern = '/' . sprintf('%s(.+?(?=%s))', $tmp, $tmp) . '/s';
            preg_replace_callback($pattern, function ($game_matches) use (&$games) {
                //game
                $game_match = $game_matches[0];
                $game = [
                    'home' => [],
                    'away' => [],
                ];

                //match all method
                $__match_all = function (string $pattern, int $i=2) use (&$game_match) {
                    preg_match_all($pattern, $game_match, $tmp_matches);

                    return is_array($tmp_matches) && count($tmp_matches) > $i ? $tmp_matches[$i] : null;
                };

                //team urls
                $team_urls = $__match_all("/href=(\"|')([^\"|']+)(\"|')/s");
                if (is_array($team_urls)) {
                    if (count($team_urls) > 3) {
                        $game['league_url'] = $team_urls[0];
                        $game['h2h_url'] = trim(preg_replace('/#[^#]*$/', '', $team_urls[2]));
                        $game['home']['url'] = $team_urls[1];
                        $game['away']['url'] = $team_urls[3];
                    } else {
                        $game['h2h_url'] = trim(preg_replace('/#[^#]*$/', '', $team_urls[1]));
                        $game['home']['url'] = $team_urls[0];
                        $game['away']['url'] = $team_urls[2];
                    }
                }

                //team ids
                $team_ids = $__match_all("/data-team-id=(\"|')([^\"|']+)(\"|')/s");
                if (is_array($team_ids) && count($team_ids) > 1) {
                    $game['home']['id'] = (int) $team_ids[0];
                    $game['away']['id'] = (int) $team_ids[1];
                }

                //comp_ids
                $comp_ids = $__match_all("/data-comp-id=(\"|')([^\"|']+)(\"|')/s");
                if (is_array($comp_ids) && count($comp_ids) > 1) {
                    $game['comp_id'] = (int) $comp_ids[0];
                }

                //team names
                $team_names = $__match_all('/data-team-id[^>]*>([^<]*)</s', 1);
                if (is_array($team_names) && count($team_names) > 1) {
                    $game['home']['name'] = $team_names[0];
                    $game['away']['name'] = $team_names[1];
                }

                //team scores
                $team_scores = $__match_all('/ft-score[^>]*>([^<]*)</s', 1);
                if (
                    x_is_list($team_scores, 0)
                    && count($team_scores = x_split('-', $team_scores[0])) > 1
                ) {
                    $game['home']['score'] = trim($team_scores[0]);
                    $game['away']['score'] = trim($team_scores[1]);
                }

                //team forms
                $team_forms = $__match_all('/form-box[^>]*>([^<]*)</s', 1);
                if (
                    x_is_list($team_forms, 0)
                    && count($team_forms) > 1
                    && is_numeric(trim($team_forms[0]))
                    && is_numeric(trim($team_forms[1]))
                ) {
                    $game['home']['form'] = (float) $team_forms[0];
                    $game['away']['form'] = (float) $team_forms[1];
                }

                //match time
                $match_time = $__match_all("/data-match-time=(\"|')([^\"|']+)(\"|')/s");
                if (
                    x_is_list($match_time, 0)
                    && ($match_time = (int) $match_time[0]) > 0
                    && strlen($match_time) == 10
                ) {
                    $game['time'] = $match_time;
                }

                //buffer game
                $games[] = $game;
            }, $league_match);

            //ignore if no league name or games available
            if (!(
                isset($league['name']) && ($league_name = trim($league['name']))
                && isset($league['country']) && ($country = trim($league['country']))
            )) {
                //output
                x_dump(' - ignored: league name/country undefined.');

                return;
            }

            //ignore no games
            if (!($count_games = count($games))) {
                //output
                x_dump(sprintf(' - ignored: no league games. (%s - %s)', $league_name, $country));

                return;
            }

            //output
            x_dump('', sprintf(' - League: %s - %s (%s)', $league_name, $country, $count_games));

            //saving - league matches
            foreach ($games as $i => $game) {
                //index number
                $num = ($i + 1) . '/' . $count_games;

                //match data
                $league_url = trim(x_array_get('league_url', $game));
                $time = (int) x_array_get('time', $game);
                $comp_id = (int) x_array_get('comp_id', $game);
                $h2h_url = trim(x_array_get('h2h_url', $game));
                $home_id = (int) x_array_get('home.id', $game);
                $home_url = trim(x_array_get('home.url', $game));
                $home_name = trim(x_array_get('home.name', $game));
                $home_score = is_numeric($tmp = x_array_get('home.score', $game)) ? (int) $tmp : null;
                $home_form = (float) x_array_get('home.form', $game);
                $away_id = (int) x_array_get('away.id', $game);
                $away_url = trim(x_array_get('away.url', $game));
                $away_name = trim(x_array_get('away.name', $game));
                $away_score = is_numeric($tmp = x_array_get('away.score', $game)) ? (int) $tmp : null;
                $away_form = (float) x_array_get('away.form', $game);

                //skip required data validation fail
                if (!(
                    $time > 0
                    && strlen("$time") == 10
                    && strlen($h2h_url)
                    && strlen($home_name)
                    && strlen($away_name)
                )) {
                    //output
                    x_dump($game, sprintf(' - skip: (%s) match data validation failed.', $num));

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

                //existing match
                $existing = FsMatch::where('date', $utime)
                //-> where('time', $time)
               ->where('h2h_url', $h2h_url)
               ->orderBy('id', 'asc')
               ->first();

                //count match
                $matches_count += 1;

                //debug (for output)
                $match_num = sprintf('[%d] (%s)', $matches_count, $num);
                $match_num = str_pad($match_num, strlen('[0000] (00/00)'));
                $debug = sprintf('%s %s - %s / %s', $match_num, x_udate($time, 'Y-m-d H:i'), $home_name, $away_name);
                x_dump(" - $debug");

                //existing match updates
                if ($existing) {

                    //skip existing
                    if ($update == 0) {
                        //output
                        x_dump(sprintf(' - %s: skip existing', $debug));

                        //next
                        continue;
                    }

                    //update forms
                    /*
                    $existing_home_form = (int) $existing->{"home_form_$form"};
                    $existing_away_form = (int) $existing->{"away_form_$form"};
                    if (
                        ($home_form || $away_form)
                        && !$existing_home_form
                        && !$existing_away_form
                    ){
                        //update
                        $existing->update([
                            "home_form_$form" => $home_form,
                            "away_form_$form" => $away_form,
                        ]);

                        //output
                        x_dump(sprintf(
                            ' - %s: updated form [%s - %s] > [%s - %s]',
                            $debug,
                            $existing_home_form,
                            $existing_away_form,
                            $home_form,
                            $away_form
                        ));

                        //next
                        if ($update == 2) continue;
                    }
                    */

                    //update scores only
                    $existing_home_score = (int) $existing->home_score;
                    $existing_away_score = (int) $existing->away_score;
                    if (
                        $update == 1
                        && is_integer($home_score)
                        && is_integer($away_score)
                        && !($existing_home_score == $home_score && $existing_away_score == $away_score)
                    ) {
                        //update
                        $existing->update([
                            'home_score' => $home_score,
                            'away_score' => $away_score,
                        ]);

                        //output
                        x_dump(sprintf(
                            ' - %s: updated scores [%s - %s] > [%s - %s]',
                            $debug,
                            $existing_home_score,
                            $existing_away_score,
                            $home_score,
                            $away_score
                        ));

                        //next
                        continue;
                    }
                }

                //creating|updating
                if (!$existing || $existing && $update == 2) {
                    //data
                    $data = [
                        'date' => $utime,
                        'league_name' => $league_name,
                        'league_url' => $league_url,
                        'country' => $country,
                        'comp_id' => $comp_id,
                        'h2h_url' => $h2h_url,
                        'time' => $time,
                        'home_id' => $home_id,
                        'home_url' => $home_url,
                        'home_name' => $home_name,
                        "home_form_$form" => $home_form,
                        'home_score' => $home_score,
                        'away_id' => $away_id,
                        'away_url' => $away_url,
                        'away_name' => $away_name,
                        "away_form_$form" => $away_form,
                        'away_score' => $away_score,
                    ];
                    //if ($form != 'last5') dd($data);

                    //update
                    try {
                        if ($existing) {
                            $existing->update($data);
                        } else {
                            //ignore create
                            if ($ignore_create) {
                                x_dump(sprintf(' - %s: ignore create', $debug));

                                continue;
                            }

                            //create
                            $res = FsMatch::create($data);
                        }
                    } catch (\Exception $e) {
                        dd($e->getMessage(), method_exists($e, 'errors') ? $e->errors() : null, $data);
                    }
                    //if ($form != 'last5') dd($existing ? $existing->toArray() : $res->toArray());

                    //output
                    x_dump(sprintf(' - %s: %s', $debug, $existing ? 'updated' : 'created'));
                }
            }
        }, $body);

        //output - done
        x_dump(" - parse done. (count=$matches_count, form=$form)", '');
    }

    //fetch wdw ($update: 0 = create only, 1 = scores only, 2 = update)
    public function fetchWdw(int $update=0, $date=null, string $date_parse_format='Y-m-d')
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
            ' - fetch matches win-draw-win: %s (%s)',
            x_udate($utime, $date_parse_format),
            $update_modes[$update]
        ));

        //open session
        $this->getPage($this->_home_url, null, !$update);

        //auth failure
        if (!$this->_auth) {
            return $this->authFail();
        }

        //fetch matches
        $url = $this->_wdw_url;
        $data = [
            'data1' => $utime,
            'data2' => '',
            'data3' => 'x',
        ];
        $response = $this->getPage($url, $data, !$update, 1, 1);

        //parse response
        $games = [];
        $len = strlen($body = trim($response['body']));

        //output
        x_dump(sprintf(' - parsing matches win-draw-win... (%s)', $len));

        //games
        $tmp = preg_quote("<tr><td class='wdw-match'>", '/');
        $pattern = '/' . sprintf('%s(.+?(?=%s))', $tmp, $tmp) . '/s';
        preg_replace_callback($pattern, function ($matches) use (&$games) {
            $game_match = $matches[0];
            $game = [];

            //fixture & h2h_url
            $h2h_url = '';
            $fixture = '';
            if (preg_match("/href='([^']*)'[^<>]*><span[^<>]*><\/span>([^<>]*)<\/a>/s", $game_match, $tmp_match)) {
                $game['h2h_url'] = $h2h_url = trim(preg_replace('/#[^#]*$/', '', $tmp_match[1]));
                $game['fixture'] = $fixture = trim($tmp_match[2]);
            }

            //set game win percentage
            $home_win = 0;
            $draw_win = 0;
            $away_win = 0;
            preg_match_all("/%;'>([0-9\.]+)%<\/td>/s", $game_match, $tmp_matches);
            if (
                is_array($tmp_matches)
                && count($tmp_matches) > 1
                && is_array($tmp_matches = $tmp_matches[1])
                && count($tmp_matches) > 2
                && is_numeric($tmp_matches[0])
                && is_numeric($tmp_matches[1])
                && is_numeric($tmp_matches[2])
            ) {
                $home_win = round((float) $tmp_matches[0], 2);
                $draw_win = round((float) $tmp_matches[1], 2);
                $away_win = round((float) $tmp_matches[2], 2);
            }
            $game['home_win'] = $home_win;
            $game['draw_win'] = $draw_win;
            $game['away_win'] = $away_win;

            //set game odds
            $home_odds = 0;
            $draw_odds = 0;
            $away_odds = 0;
            preg_match_all("/>([0-9\.]+)<\/li>/s", $game_match, $tmp_matches);
            if (
                is_array($tmp_matches)
                && count($tmp_matches) > 1
                && is_array($tmp_matches = $tmp_matches[1])
                && count($tmp_matches) > 2
                && is_numeric($tmp_matches[0])
                && is_numeric($tmp_matches[1])
                && is_numeric($tmp_matches[2])
            ) {
                $home_odds = round((float) $tmp_matches[0], 2);
                $draw_odds = round((float) $tmp_matches[1], 2);
                $away_odds = round((float) $tmp_matches[2], 2);
            }
            $game['home_odds'] = $home_odds;
            $game['draw_odds'] = $draw_odds;
            $game['away_odds'] = $away_odds;

            //buffer game with required data
            if (
                strlen($h2h_url)
                && strlen($fixture)
                && ($home_win > 0 || $away_win > 0 || $draw_win > 0)
            ) {
                $games[] = $game;
            }
        }, $body);

        //games count
        $count_games = count($games);

        //output
        x_dump('', sprintf(' - Save Win-Draw-Win Matches: (%s)', $count_games));

        //save games
        foreach ($games as $i => $game) {
            //index number
            $num = ($i + 1) . '/' . $count_games;

            //match data
            $h2h_url = trim(x_array_get('h2h_url', $game));
            $fixture = trim(x_array_get('fixture', $game));
            $home_win = (float) x_array_get('home_win', $game);
            $draw_win = (float) x_array_get('draw_win', $game);
            $away_win = (float) x_array_get('away_win', $game);
            $home_odds = (float) x_array_get('home_odds', $game);
            $draw_odds = (float) x_array_get('draw_odds', $game);
            $away_odds = (float) x_array_get('away_odds', $game);

            //skip required data validation fail
            if (!(
                strlen($h2h_url)
                && strlen($fixture)
            )) {
                //output
                x_dump($game, ' - skip: (%s) match data validation failed.', $num);

                //next
                continue;
            }

            //match data
            $data = [
                'date' => $utime,
                'h2h_url' => $h2h_url,
                'fixture' => $fixture,
                'home_win' => $home_win,
                'draw_win' => $draw_win,
                'away_win' => $away_win,
                'home_odds' => $home_odds,
                'draw_odds' => $draw_odds,
                'away_odds' => $away_odds,
            ];

            //existing match
            $existing = FsWdw::where('date', $utime)
           ->where('h2h_url', $h2h_url)
           ->first();

            //count match
            $match_num = str_pad("($num)", strlen('(000/000)'));

            //debug
            $debug = sprintf('%s - %s', $match_num, $fixture);

            //skip existing
            if ($existing && $update == 0) {
                //correlate fs match
                if (!$existing->fs_match_id && ($fs_match = $this->correlateFsWdwMatch($data))) {
                    //update existing
                    $existing->fs_match_id = $fs_match->id;
                    $existing->save();

                    //update fs match
                    $fs_match->fs_wdw_id = $existing->id;
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
                if (!$fs_match_id && ($fs_match = $this->correlateFsWdwMatch($data))) {
                    $data['fs_match_id'] = $fs_match_id = $fs_match->id;
                }

                //update
                if ($existing) {
                    $fs_wdw = $existing;
                    $fs_wdw->update($data);
                }

                //create
                else {
                    $fs_wdw = FsWdw::create($data);
                }

                //update fs match
                if ($fs_match) {
                    $fs_match->fs_wdw_id = $fs_wdw->id;
                    $fs_match->saveQuietly();
                }

                //output
                x_dump(trim(sprintf(' - %s: %s %s', $debug, $existing ? 'updated' : 'created', $fs_match_id ? " [$fs_match_id]" : '')));
            }
        }

        //output - done
        x_dump(" - fetch done. ($count_games matches)", '');
    }

    //correlate fs-wdw match
    public function correlateFsWdwMatch($match)
    {
        //match vars
        $date = $match['date'];
        $h2h_url = $match['h2h_url'];

        //get similar matches
        $fs_matches = FsMatch::whereNull('fs_wdw_id')
       ->where('h2h_url', $h2h_url)
       ->where(function ($query) use (&$date) {
           $query->where('date', $date);
           $query->orWhere(function ($query) use (&$date) {
               $query->where('time', '>=', $date);
               $query->where('time', '<', $date + (24 * 60 * 60));
           });
       })
       ->orderBy('time', 'asc')
       ->get();

        //if no fs matches found - return null
        if (!count($fs_matches)) {
            return null;
        }

        //set most similar
        $similar = null;
        foreach ($fs_matches as $fs_match) {
            //set similar
            $similar = $fs_match;

            //break if dates match
            if ((int) $fs_match->date == (int) $date) {
                break;
            }
        }

        //result - similar fs match correlation
        return $similar;
    }

    //correlate fs-wdw matches
    public function correlateFsWdwMatches()
    {
        //get uncorrelated matches
        $matches = FsWdw::whereNull('fs_match_id')
       ->orderBy('date', 'desc')
       ->orderBy('id', 'desc')
       ->get();

        //if no results - return
        if (!($count = count($matches))) {
            //output
            x_dump(' - No un-correlated fs-wdw matches found.');

            //return
            return;
        }

        //output
        x_dump(' - correlating fs-wdw - footystats matches (' . $count . ').');

        //correlate matches
        $corr_count = 0;
        foreach ($matches as $i => $fs_wdw) {
            //index number
            $num = ($i + 1) . '/' . $count;

            //debug
            $match_num = str_pad("($num)", strlen('(000/000)'));
            $debug = sprintf('%s - %s', $match_num, $fs_wdw->fixture);

            //get similar fs match
            if ($fs_match = $this->correlateFsWdwMatch($fs_wdw->toArray())) {
                //correlation
                $corr_count += 1;

                //update
                $fs_wdw->update([
                    'fs_match_id' => $fs_match->id,
                ]);

                //update fs match - fs_wdw_id (quietly)
                $fs_match->fs_wdw_id = $fs_wdw->id;
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
