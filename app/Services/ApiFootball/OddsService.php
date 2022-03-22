<?php

namespace App\Services\ApiFootball;

use App\Models\Fixtures;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OddsService extends BaseService
{
    protected $suffix = 'odds';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Nette\Utils\JsonException
     */
    public function updateOdds(string $date):void
    {
        $fixtures = Fixtures::where([[DB::raw('DATE(fixture_date)'), 'LIKE', $date], ['odds_updated' , '=' , 0]])->pluck('fixture_id')->toArray();
        $verb = 'fixture';
        $insert = [];
        foreach ($fixtures as $fix) {
            $params = [
                'fixture' => $fix,
                'bookmaker' => 8
            ];

            $cacheKey = md5((string) json_encode('fixture_' . $fix));
            $response = Cache::remember($cacheKey, 3600, $this->getData($this->suffix, $params));
            $res = !empty($response['response']) ? $response['response'][0] : false;
            $res ? Fixtures::upsert([
                        'fixture_id' => $res[$verb]['id'],
                        'fixture_date' => Carbon::create($res[$verb]['date'])->format('Y-m-d H:i'),
                        'home_team_odds' => $res['bookmakers'][0]['bets'][0]['values'][0]['odd'],
                        'draw_odds' => $res['bookmakers'][0]['bets'][0]['values'][1]['odd'],
                        'away_team_odds' => $res['bookmakers'][0]['bets'][0]['values'][2]['odd'],
                        'odds_updated' => 1,
                        'updated_at' => Carbon::create($res['update'])->format('Y-m-d H:i'),

                    ], 'fixture_id') : '';
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getOtherBookmaker($fix, $cacheKey): array
    {
        $int = 1;
        do {
            $params = [
                'fixture' => $fix,
                'bookmaker' => $int
            ];

            Cache::forget($cacheKey);
            $data = Cache::remember($cacheKey, 3600, $this->getData($this->suffix, $params));
            $int++;
        } while (empty($data['response']) && $int < 20);

        return $data;
    }
}
