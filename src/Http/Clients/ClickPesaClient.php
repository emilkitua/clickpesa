<?php

namespace EmilKitua\ClickPesa\Http\Clients;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class ClickPesaClient
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = Config::get('clickpesa.client_id');
        $this->clientSecret = Config::get('clickpesa.client_secret');
        $this->baseUrl = rtrim(Config::get('clickpesa.base_url'), '/');
    }

    protected function getToken(): string
    {
        if (Cache::has('clickpesa_access_token')) {
            return Cache::get('clickpesa_access_token');
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->baseUrl}/oauth/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw new \Exception("ClickPesa authentication failed: " . $response->body());
        }

        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;

        Cache::put('clickpesa_access_token', $token, now()->addSeconds($expiresIn - 60));

        return $token;
    }

    protected function client()
    {
        return Http::withToken($this->getToken())->baseUrl($this->baseUrl);
    }

    public function post(string $uri, array $data = [])
    {
        return $this->client()->post($uri, $data);
    }

    public function get(string $uri, array $query = [])
    {
        return $this->client()->get($uri, $query);
    }
}
