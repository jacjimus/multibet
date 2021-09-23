<?php

namespace App\Http\Middleware;

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
     */
    public function handle($request, Closure $next)
    {
        //matches date
        $date = $request->has('date') && ($tmp = trim($request->date)) ? $tmp : null;
        $form_diff = $request->get('diff') ?? null;
        $odds = $request->get('odds') ?? null;

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
        if ($premium_limit && ($utime > $now && $user_premium['bal'] < 1)) {
            return redirect('/premium?rdr=' . base64_encode(url()->previous()));
        }

        //fs fetch status check
        if ($request->has('fetch-status')) {
            return x_res_json([
            'status' => x_cache_get("fs-fetch-$utime"),
        ]);
        }

        //fs matches
        $fs_matches = new \App\Services\Fstats\FsMatches($date);
        $fs_match_list = $fs_matches->getMatches(1, 0, $form_diff, $odds);
        $fs_date_links = $fs_matches->getDateListLinks();
        $fs_fetch = x_cache_get("fs-fetch-$utime");

        //view share
        $share = [
            'fs_show_date' => $fs_matches->show_date,
            'fs_date_links' => $fs_date_links,
            'fs_match_list' => $fs_match_list,
            'form_diff' => $form_diff,
            'odds' => $odds,
            'fs_fetch' => $fs_fetch,
        ];
        foreach ($share as $key => $val) {
            view()->share($key, $val);
        }

        //fs matches table only
        if ($request->has('fetch-table')) {
            return x_res_view('fstats.welcome.matches-table');
        }

        //next
        return $next($request);
    }
}
