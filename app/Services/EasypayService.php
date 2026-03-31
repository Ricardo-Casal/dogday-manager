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

        $response = Http::withHeaders($this->headers())->post("{$this->baseUrl}/mbway/v1/charge", [
            'method'      => ['type' => 'MBW', 'phone' => "351#{$phone}"],
            'type'        => 'sale',
            'value'       => $amount,
            'description' => $description,
            'capture'     => ['descriptive' => $description],
        ]);

        if ($response->successful()) {
            return [
                'success'    => true,
                'easypay_id' => $response->json('id'),
                'status'     => $response->json('status'),
            ];
        }

        return ['success' => false, 'error' => $response->json('message', 'Erro ao criar pagamento MBWay')];
    }

    public function resendMBWay(string $easypayId): bool
    {
        if ($this->isMock) {
            return true;
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/mbway/v1/charge/{$easypayId}/resend");

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

        $response = Http::withHeaders($this->headers())->post("{$this->baseUrl}/payment", [
            'method'      => ['type' => 'MB'],
            'type'        => 'sale',
            'value'       => $amount,
            'description' => $description,
            'capture'     => ['descriptive' => $description],
        ]);

        if ($response->successful()) {
            return [
                'success'      => true,
                'easypay_id'   => $response->json('id'),
                'mb_entity'    => $response->json('method.entity'),
                'mb_reference' => $response->json('method.reference'),
            ];
        }

        return ['success' => false, 'error' => $response->json('message', 'Erro ao criar referência Multibanco')];
    }

    private function headers(): array
    {
        return [
            'AccountId' => $this->accountId,
            'ApiKey'    => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }
}
