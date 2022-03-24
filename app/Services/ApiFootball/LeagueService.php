<?php

namespace App\Services\ApiFootball;

use App\Models\Leagues;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LeagueService extends BaseService
{
    private $season;

    private $current = true;

    protected $suffix = 'leagues';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Nette\Utils\JsonException
     */
    public function data(): Collection
    {
        $params = [

        ];
        $verb = $verb = Str::singular($this->suffix);
        $data = [];
        $cacheKey = md5((string) json_encode(date('Y')));

        $response = Cache::remember($cacheKey, 3600, $this->getData($this->suffix, $params), true);

        foreach ($response['response'] as $key=>$res) {
            array_key_exists(Str::singular($this->suffix), $res) ? array_push(
                $data,
                ['league_id' => $res[$verb]['id'], 'name' => $res[$verb]['name'], 'country' => $res['country']['name']]
            ) : '';
        }
        Leagues::upsert($data, 'league_id');

        return Leagues::select('name', 'country', 'league_id')->orderBy('name')->get();
    }
}
