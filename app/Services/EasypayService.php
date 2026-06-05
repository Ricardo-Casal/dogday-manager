<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EasypayService
{
    private bool $isMock;
    private string $baseUrl;
    private string $accountId;
    private string $apiKey;

    public function __construct()
    {
        $this->accountId = config('services.easypay.account_id', '');
        $this->apiKey    = config('services.easypay.api_key', '');
        $this->baseUrl   = config('services.easypay.sandbox', true)
            ? 'https://api.test.easypay.pt/2.0'
            : 'https://api.prod.easypay.pt/2.0';

        $this->isMock = empty($this->accountId) || empty($this->apiKey);
    }

    public function createMBWayPayment(string $phone, float $amount, string $description): array
    {
        if ($this->isMock) {
            return [
                'success'    => true,
                'easypay_id' => 'mock-' . Str::uuid(),
                'status'     => 'pending',
                'mock'       => true,
            ];
        }

        // Normalise phone: strip spaces/dashes, ensure +351 prefix
        $phone = preg_replace('/\s+|-/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+351' . ltrim($phone, '0');
        }

        $payload = [
            'type'     => 'sale',
            'value'    => $amount,
            'method'   => 'MBW',
            'customer' => ['phone' => $phone],
            'capture'  => ['descriptive' => $description],
        ];

        if (!$this->isSandbox()) {
            $payload['notification'] = ['url' => $this->notificationUrl()];
        }

        $response = Http::withHeaders($this->headers())->post("{$this->baseUrl}/single", $payload);

        if ($response->successful() && $response->json('status') === 'ok') {
            return [
                'success'    => true,
                'easypay_id' => $response->json('id'),
                'status'     => 'pending',
            ];
        }

        $msg = is_array($response->json('message')) ? implode(', ', $response->json('message')) : ($response->json('message') ?? 'Erro ao criar pagamento MBWay');
        return ['success' => false, 'error' => $msg];
    }

    public function resendMBWay(string $easypayId): bool
    {
        if ($this->isMock) {
            return true;
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/single/{$easypayId}/capture");

        return $response->successful();
    }

    public function createMultibancoPayment(float $amount, string $description): array
    {
        if ($this->isMock) {
            return [
                'success'      => true,
                'easypay_id'   => 'mock-' . Str::uuid(),
                'mb_entity'    => '11249',
                'mb_reference' => implode(' ', str_split(str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT), 3)),
                'mock'         => true,
            ];
        }

        $payload = [
            'type'    => 'sale',
            'value'   => $amount,
            'method'  => 'MB',
            'capture' => ['descriptive' => $description],
        ];

        if (!$this->isSandbox()) {
            $payload['notification'] = ['url' => $this->notificationUrl()];
        }

        $response = Http::withHeaders($this->headers())->post("{$this->baseUrl}/single", $payload);

        if ($response->successful() && $response->json('status') === 'ok') {
            return [
                'success'      => true,
                'easypay_id'   => $response->json('id'),
                'mb_entity'    => $response->json('method.entity'),
                'mb_reference' => $response->json('method.reference'),
            ];
        }

        $msg = is_array($response->json('message')) ? implode(', ', $response->json('message')) : ($response->json('message') ?? 'Erro ao criar referência Multibanco');
        return ['success' => false, 'error' => $msg];
    }

    private function headers(): array
    {
        return [
            'AccountId'    => $this->accountId,
            'ApiKey'       => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    private function isSandbox(): bool
    {
        return (bool) config('services.easypay.sandbox', true);
    }

    private function notificationUrl(): string
    {
        return rtrim(config('app.url'), '/') . '/webhooks/easypay';
    }
}
