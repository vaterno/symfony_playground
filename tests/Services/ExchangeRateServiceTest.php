<?php

namespace App\Tests\Services;

use Symfony\Contracts\Cache\CacheInterface;
use App\Services\ExchangeRate\ExchangeRateService;
use App\Services\ExchangeRate\DTO\ExchangeListDto;
use App\Services\Currency\Dictionary\CurrencyDictionary;
use App\Services\ExchangeRate\Providers\OpenProviderExchangeRatesProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExchangeRateServiceTest extends KernelTestCase
{
    public function testGetListOfRatesSuccess()
    {
        $data = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $data['0']['timestamp'],
            $data['0']['baseCurrency'],
            $data['0']['rates'],
        );

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn($exchangeListDto);
        $openProviderExchangeRatesProvider = $this->createMock(OpenProviderExchangeRatesProvider::class);

        $exchangeRateService = new ExchangeRateService(
            $openProviderExchangeRatesProvider,
            $cache
        );
        $exchangeListDto = $exchangeRateService->getListOfRates(CurrencyDictionary::USD);

        $this->assertInstanceOf(ExchangeListDto::class, $exchangeListDto);
        $this->assertEquals($data['0']['baseCurrency'], $exchangeListDto->baseCurrency);
        $this->assertEquals($data['0']['timestamp'], $exchangeListDto->timestamp);
        $this->assertEquals($data['0']['rates'], $exchangeListDto->rates);
    }

    protected function getExchangeListData()
    {
        return [
            [
                'timestamp' => time(),
                'baseCurrency' => CurrencyDictionary::USD->name,
                'rates' => [
                    CurrencyDictionary::EUR->name => 1.20,
                    CurrencyDictionary::UAH->name => 0.35,
                    CurrencyDictionary::PLN->name => 0.89,
                ]
            ],
        ];
    }
}
