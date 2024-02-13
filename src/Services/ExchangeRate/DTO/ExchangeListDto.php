<?php

namespace App\Services\ExchangeRate\DTO;

use App\Services\Currency\Dictionary\CurrencyDictionary;

class ExchangeListDto
{
    public function __construct(
        public readonly int $timestamp,
        public readonly string $baseCurrency,
        public readonly array $rates
    ) {
    }

    public function getExchangeRateByCurrency(CurrencyDictionary $currency): ?array
    {
        if (empty($this->rates[$currency->name])) {
            return null;
        }

        return [
            'currency' => $currency->name,
            'rate' => $this->rates[$currency->name],
        ];
    }
}
