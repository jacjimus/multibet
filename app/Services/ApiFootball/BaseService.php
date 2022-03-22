<?php

namespace App\Services\ApiFootball;

use App\Traits\HasSettings;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\JsonResponse;

//day in seconds
if (!defined('DAYSEC')) {
    define('DAYSEC', 24 * 60 * 60);
}

class BaseService
{
    use HasSettings;

    protected $baseUrl = 'https://v3.football.api-sports.io/';

    public const APISPORTKEY = 'x-apisports-key';

    public const APISPORTSKEYVALUE = 'e2eec22b2691d9aea7882a03f53c2bb4';

    /**
     * @param $suffix
     * @param array $params
     *
     * @return JsonResponse|\Illuminate\Http\Response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getData($suffix, array $params = []): Closure
    {
        return function () use ($suffix, $params) {
            $client = new Client();
            $headers = [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    self::APISPORTKEY => self::APISPORTSKEYVALUE
                ],
            ];
            $query = http_build_query($params);

            $response = $client->request('GET', sprintf('%s%s?%s', $this->baseUrl, $suffix, $query), $headers);

            return json_decode($response->getBody()->getContents(), true);
        };
    }

    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @param $status
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $status): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $status);
    }
}
