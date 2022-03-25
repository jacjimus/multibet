<?php

namespace App\Services\ApiFootball;

use App\Models\Fixtures;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class OddsService extends BaseService
{
    protected $suffix = 'odds';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Nette\Utils\JsonException
     */
    public function updateOdds(string $date):void
    {
        $fixtures = Fixtures::whereBetween('fixture_date', [Carbon::parse($date)->startOfDay(), Carbon::parse($date)->endOfDay()])
            ->where('odds_updated', 0)->pluck('fixture_id')->toArray();
        $verb = 'fixture';
        foreach ($fixtures as $fix) {
            $params = [
                'fixture' => $fix,
                'bookmaker' => 8,
                'bet' => 12
            ];

            $cacheKey = md5((string) json_encode('fixture_' . $fix));
            $response = Cache::remember($cacheKey, 3600, $this->getData($this->suffix, $params));
            $res = !empty($response['response']) ? $response['response'][0] : $this->getOtherBookmaker($fix, $cacheKey);
            if ($res) {
                $data = [
                    'fixture_date' => Carbon::create($res[$verb]['date'])->format('Y-m-d H:i'),
                    'home_draw_odds' => $res['bookmakers'][0]['bets'][0]['values'][0]['odd'],
                    'home_away_odds' => $res['bookmakers'][0]['bets'][0]['values'][1]['odd'],
                    'away_draw_odds' => $res['bookmakers'][0]['bets'][0]['values'][2]['odd'],
                    'odds_updated' => 1,
                    'updated_at' => Carbon::create($res['update'])->format('Y-m-d H:i'),

                ];

                Fixtures::where('fixture_id', $res[$verb]['id'])->update($data);
            }
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getOtherBookmaker($fix, $cacheKey): ?array
    {
        $int = 1;
        do {
            $params = [
                'fixture' => $fix,
                'bookmaker' => $int,
                'bet' => 12
            ];

            Cache::forget($cacheKey);
            $data = Cache::remember($cacheKey, 3600, $this->getData($this->suffix, $params));
            $int++;
        } while (empty($data['response']) && $int < 8);

        return $data['response'][0] ?? null;
    }
}
