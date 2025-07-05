<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ErgastClient
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri'        => 'https://ergast.com/api/f1/',
            'timeout'         => 10,
            'connect_timeout' => 5,
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],

            'proxy' => [
              'http'  => 'socks5h://127.0.0.1:1080',
              'https' => 'socks5h://127.0.0.1:1080',
            ],
        ]);
    }

    /**
     * Fetch data from an endpoint, e.g. "2021/results".
     * Caches for 6 hours to avoid rate limits.
     */
    public function fetch(string $path): array
    {
        return Cache::remember("ergast.{$path}", now()->addHours(6), function () use ($path) {
            $response = $this->http->get("{$path}.json");
            $json = json_decode($response->getBody(), true);
            return $json['MRData'];
        });
    }
}
