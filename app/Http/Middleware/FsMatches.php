<?php

namespace App\Http\Middleware;

use App\Services\ApiFootball\FixtureService;
use App\Services\ApiFootball\LeagueService;
use Closure;

class FsMatches
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Nette\Utils\JsonException
     */
    public function handle($request, Closure $next)
    {
        //matches date
        $date = $request->get('fs_date') ?? date('Y-m-d');

        //future date guard
        $us = app('UserService');
        $future_limit = 1;
        $now = x_utime();
        $utime = x_utime($date, 1);
        $user = $us->getUser();
        if ($future_limit && ($utime < $now && !$user)) {
            return redirect('/login?rdr=' . base64_encode(url()->previous()));
        }

        //check premium
        $premium_limit = 1;
        if ($user && $user->id == 1) {
            $premium_limit = 0;
        } //do not restrict root
        $user_premium = $user ? $us->getUserPremium($user->id) : [];
        $user_premium_bal = isset($user_premium['bal']) ? $user_premium['bal'] : 0;
        if ($user && strpos(url()->previous(), 'login') !== false && !$user_premium_bal) {
            return redirect('/premium');
        }

        //fs fetch status check
        if ($request->has('fetch-status')) {
            return x_res_json([
            'status' => x_cache_get("fs-fetch-$utime"),
        ]);
        }
        $fs_matches = new FixtureService($date);
        $fs_match_list = $fs_matches->data($request);
        $fs_fetch = x_cache_get("fs-fetch-$utime");
        $leagues = (new LeagueService())->data();
        $fs_date_links = $fs_matches->getDateListLinks();

        //view share
        $share = [
            'fs_show_date' => $fs_matches->show_date,
            'fs_date_links' => $fs_date_links,
            'fs_match_list' => $fs_match_list,
            'fs_fetch' => $fs_fetch,
            'leagues' => collect($leagues)->pluck('name', 'league_id'),
        ];
        foreach ($share as $key => $val) {
            view()->share($key, $val);
        }
        //fs matches table only
        if ($request->has('fetch-table')) {
            //return x_res_view('fstats.welcome.matches-table');
        }

        //next
        return $next($request);
    }
}
