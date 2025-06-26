<?php

namespace EmilKitua\ClickPesa;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

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
        $this->authUrl = $this->baseUrl . '/generate-token';
    }
    
    protected function authenticate(): string
    {
        if (Cache::has('clickpesa_token')) {
            return Cache::get('clickpesa_token');
        }

        $response = Http::withHeaders([
            'api-key'   => $this->clientSecret,
            'client-id' => $this->clientId,
        ])->post($this->authUrl);

        // Log full response
        Log::info('ClickPesa Auth Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->status() === 401) {
            throw new \Exception('Unauthorized: Invalid client credentials.');
        }

        if ($response->status() === 403) {
            throw new \Exception('Forbidden: Invalid or expired API key.');
        }

        if (!$response->successful()) {
            throw new \Exception('ClickPesa authentication failed: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['success']) || !$data['success'] || !isset($data['token'])) {
            throw new \Exception('Invalid response from ClickPesa: ' . $response->body());
        }

        $token = $data['token']; // Already includes "Bearer"

        // Cache for 600 minutes (or adjust if API says otherwise)
        Cache::put('clickpesa_token', $token, now()->addMinutes(600));

        return $token;
    }

    protected function client()
    {
        return Http::withToken($this->authenticate())->baseUrl($this->baseUrl);
    }

    public function initiateUSSD(array $payload): array
    {
        $response = $this->client()->post('/payments/ussd-checkout', $payload);
        return $response->json();
    }

    public function queryStatus(string $transactionId): array
    {
        $response = $this->client()->get("/payments/{$transactionId}");
        return $response->json();
    }

    public function initiateCardPayment(array $payload): array
    {
        $response = $this->client()->post('/payments/card-checkout', $payload);
        return $response->json();
    }

    public function getBalance(): array
    {
        $response = $this->client()->get('/payments/wallet/balance');
        return $response->json();
    }
}
