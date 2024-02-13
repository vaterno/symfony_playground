<?php

namespace App\Services\ExchangeRate\Providers;

use App\Services\ExchangeRate\DTO\ExchangeListDto;

interface ExchangeRateProviderInterface
{
    public function getLatestExchangeRates(string $currencyISO): ?ExchangeListDto;
}
