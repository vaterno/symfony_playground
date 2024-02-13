<?php

namespace App\Services\ExchangeRate;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Services\ExchangeRate\DTO\ExchangeListDto;
use App\Services\Currency\Dictionary\CurrencyDictionary;
use App\Services\ExchangeRate\Providers\ExchangeRateProviderInterface;

class ExchangeRateService
{
    public function __construct(
        protected ExchangeRateProviderInterface $exchangeRate,
        protected CacheInterface $cache
    ) {
    }

    public function getListOfRates(CurrencyDictionary $currency, int $cacheTime = 3600): ?ExchangeListDto
    {
        $exchangeRate = $this->exchangeRate;
        $cacheName = 'ExchangeRateService::getListOfRates:' . $currency->name;

        $exchangeRateDto = $this->cache->get($cacheName, function (ItemInterface $item) use($cacheTime, $exchangeRate, $currency) {
            $cacheTime = (empty($cacheTime) || $cacheTime <= 0) ? 1 : $cacheTime;
            $item->expiresAfter($cacheTime);
            $latestRates = $exchangeRate->getLatestExchangeRates($currency->name);

            if (
                empty($latestRates) ||
                empty($latestRates->rates)
            ) {
                return null;
            }

            return $latestRates;
        });

        return $exchangeRateDto;
    }
}
