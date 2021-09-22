<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class RequestService
{
    //vars
    public $service_name = 'request-service';

    public $cache_lifetime = 60 * 60 * 24 * 2; //2 days (seconds)

    public $timeout = 60; //(seconds)

    public $save_body_path;

    public $response;

    //construct
    public function __construct()
    {
        $this->save_body_path = storage_path('app/request-service/%s.html');
    }

    //get save response body path
    public function getSaveBodyPath(string $key=null)
    {
        $path = trim($this->save_body_path);
        if (strpos($path, '%s') !== false && x_is_string($key, 1)) {
            $path = sprintf($path, $key); //path template - replace key
        }

        return $path;
    }

    //save response body
    public function saveResponseBody(string $key, string $body)
    {
        $path = $this->getSaveBodyPath($key);
        x_dump(' - response body saved: ' . $key);

        return x_file_put($path, $body);
    }

    //send request
    public function request(
        string $url,
        array $data=null,
        array $headers=null,
        bool $is_post=false,
        bool $cached=false,
        bool $save_body=false,
        bool $trim_body=false
    ) {
        //request key
        $key = md5(json_encode([$url, $data, $is_post, $headers]));
        x_dump(" - request: $key");

        //cached
        if ($cached && ($response = x_cache_get($key))) {
            //save response body
            if ($save_body) {
                $body = $response['body'];
                //dd($body);
                if ($trim_body) {
                    $body = trim($body);
                }
                $this->saveResponseBody($key, $body);
            }

            //result - cached response
            x_dump(sprintf(' - cached response (%d).', $response['length']));

            return $response;
        }

        //delete cache if no longer cached
        elseif (!$cached && x_cache_has($key)) {
            x_cache_delete($key);
        }

        //Http response
        $response = $this->response = Http::withHeaders($headers);
        if ($this->timeout > 0) {
            $response->timeout($this->timeout);
        }

        //send request
        x_dump(' - sending request...');
        x_dump(sprintf(' - %s %s', $is_post ? 'post' : 'get', $url));
        $response = $is_post ? $response->asForm()->post($url, $data) : $response->get($url, $data);
        if ($response->failed()) {
            x_dump(sprintf(' - request %s error.', $response->serverError() ? 'server' : ($response->clientError() ? 'client' : 'unknown')));
            $response->throw();

            throw new Exception('Request failed! (This shouldnt be thrown)');
        }

        //response body
        $body = $response->body();
        if ($trim_body) {
            $body = trim($body);
        }
        $len = strlen($body);
        x_dump(sprintf(' - request successful (%d).', $len));

        //save response body
        if ($save_body) {
            $this->saveResponseBody($key, $body);
        }

        //response result
        $result = [
            'key' => $key,
            'body' => $body,
            'length' => $len,
            'set_cookie' => $response->header('Set-Cookie'),
        ];

        //cache result
        if ($cached) {
            x_cache_set($key, $result, $this->cache_lifetime);
            x_dump(' - response cached.');
        }

        //result
        return $result;
    }
}
