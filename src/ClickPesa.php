<?php

namespace EmilKitua\ClickPesa;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ClickPesa
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected string $authUrl;

    public function __construct()
    {
        $this->clientId = Config::get('clickpesa.client_id');
        $this->clientSecret = Config::get('clickpesa.client_secret');
        $this->baseUrl = rtrim(Config::get('clickpesa.base_url'), '/');
        $this->authUrl = $this->baseUrl . '/oauth/token';
    }

    protected function authenticate(): string
    {
        if (Cache::has('clickpesa_token')) {
            return Cache::get('clickpesa_token');
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->post($this->authUrl, [
                'grant_type' => 'client_credentials',
            ]);

        $token = $response['token'];
        Cache::put('clickpesa_token', $token, 360);

        return $token;
    }

    protected function client()
    {
        return Http::withToken($this->authenticate())->baseUrl($this->baseUrl);
    }

    public function initiateUSSD(array $payload): array
    {
        $response = $this->client()->post('/ussd-checkout', $payload);
        return $response->json();
    }

    public function queryStatus(string $transactionId): array
    {
        $response = $this->client()->get("/payments/{$transactionId}");
        return $response->json();
    }

    public function initiateCardPayment(array $payload): array
    {
        $response = $this->client()->post('/card-checkout', $payload);
        return $response->json();
    }

    public function getBalance(): array
    {
        $response = $this->client()->get('/wallet/balance');
        return $response->json();
    }
}
