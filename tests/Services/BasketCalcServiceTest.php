<?php

namespace App\Tests\Services;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Services\Basket\Exceptions\ExistsMultiCurrencyException;
use PHPUnit\Framework\TestCase;
use App\Services\Basket\BasketCalcService;
use App\Services\Basket\Hudrators\BasketHudrator;
use App\Services\ExchangeRate\DTO\ExchangeListDto;
use App\Services\Currency\Dictionary\CurrencyDictionary;
use App\Services\Basket\Exceptions\ExchangeRateDoesntExistsException;

class BasketCalcServiceTest extends TestCase
{
    protected BasketCalcService $basketCalcService;

    public function setUp(): void
    {
        $this->basketCalcService = new BasketCalcService();
    }

    public function testTransformToBaseBasketCurrencyWithoutMultiCurrency()
    {
        $exchangeList = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $exchangeList['0']['timestamp'],
            $exchangeList['0']['baseCurrency'],
            $exchangeList['0']['rates'],
        );
        $requestData = $this->getRequestSameData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $transformedBasket = $this->basketCalcService->transformToBaseBasketCurrency($basket, $exchangeListDto);

        $this->assertEquals($basket->getCurrency(), $transformedBasket->getCurrency());
        $this->assertEquals($basket->getPrice(), $transformedBasket->getPrice());
        $this->assertEquals($basket->getItems(), $transformedBasket->getItems());
    }

    public function testTransformToBaseBasketCurrencyWithMultiCurrencyAndEmptyRates()
    {
        $exchangeList = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $exchangeList['0']['timestamp'],
            $exchangeList['0']['baseCurrency'],
            [],
        );
        $requestData = $this->getRequestData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $transformedBasket = $this->basketCalcService->transformToBaseBasketCurrency($basket, $exchangeListDto);

        $this->assertNull($transformedBasket);
    }

    public function testTransformToBaseBasketCurrencyToBaseWithNoExistsExchangeRateCurrencyIso()
    {
        $exchangeList = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $exchangeList['0']['timestamp'],
            $exchangeList['0']['baseCurrency'],
            ['AAAAAA' => 14.00],
        );
        $requestData = $this->getRequestData();


        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);

        $this->expectException(ExchangeRateDoesntExistsException::class);
        $this->basketCalcService->transformToBaseBasketCurrency($basket, $exchangeListDto);
    }

    public function testTransformToBaseBasketCurrencyToBase()
    {
        $exchangeList = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $exchangeList['0']['timestamp'],
            $exchangeList['0']['baseCurrency'],
            $exchangeList['0']['rates'],
        );
        $requestData = $this->getRequestData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $transformedBasket = $this->basketCalcService->transformToBaseBasketCurrency($basket, $exchangeListDto);

        /** @var BasketItem $item */
        foreach ($transformedBasket->getItems() as $item) {
            $this->assertEquals($transformedBasket->getCurrency()->name, $item->getCurrency()->name);
        }
    }

    public function testTransformToBaseBasketCurrencyCheckPriceOfBasketPrice()
    {
        $exchangeList = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $exchangeList['0']['timestamp'],
            $exchangeList['0']['baseCurrency'],
            $exchangeList['0']['rates'],
        );
        $requestData = $this->getRequestData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $transformedBasket = $this->basketCalcService->transformToBaseBasketCurrency($basket, $exchangeListDto);

        $basketItems = $basket->getItems();
        /** @var BasketItem $item */
        foreach ($transformedBasket->getItems() as $key => $item) {
            $notTransferBasketItem = $basketItems[$key];
            $exchangeRate = $exchangeListDto->getExchangeRateByCurrency($notTransferBasketItem->getCurrency());
            $notTransferBasketItemPrice = round($notTransferBasketItem->getPrice() / $exchangeRate['rate'], 2);

            $this->assertEquals($notTransferBasketItemPrice, $item->getPrice());
        }
    }

    public function testCalcPriceExistsMultiCurrencyException()
    {
        $requestData = $this->getRequestData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $this->expectException(ExistsMultiCurrencyException::class);
        $this->basketCalcService->calcPrice($basket);
    }

    public function testCalcPriceZeroPrice()
    {
        $requestData = $this->getRequestData();
        unset($requestData['items']);

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $this->basketCalcService->calcPrice($basket);

        $this->assertEquals(0, $basket->getPrice());
    }

    public function testCalcPrice()
    {
        $exchangeList = $this->getExchangeListData();
        $exchangeListDto = new ExchangeListDto(
            $exchangeList['0']['timestamp'],
            $exchangeList['0']['baseCurrency'],
            $exchangeList['0']['rates'],
        );
        $requestData = $this->getRequestData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        $transformedBasket = $this->basketCalcService->transformToBaseBasketCurrency($basket, $exchangeListDto);
        $clonedBasket = clone $transformedBasket;
        $this->basketCalcService->calc($transformedBasket);

        $totalPrice = 0;
        /** @var BasketItem $basketItem */
        foreach ($clonedBasket->getItems() as $basketItem) {
            $price = $basketItem->getPrice() * $basketItem->getQuantity();
            $totalPrice += $price;
        }

        $this->assertEquals($totalPrice, $transformedBasket->getPrice());
    }

    protected function getRequestSameData(): array
    {
        return [
            'items' => [
                [
                    'currency' => 'USD',
                    'price' => 12.39,
                    'quantity' => 1,
                ],
                [
                    'currency' => 'USD',
                    'price' => 28,
                    'quantity' => 2,
                ],
            ],
            'currency' => 'USD',
        ];
    }

    protected function getRequestData(): array
    {
        return [
            'items' => [
                [
                    'currency' => 'UAH',
                    'price' => 12.39,
                    'quantity' => 1,
                ],
                [
                    'currency' => 'EUR',
                    'price' => 28,
                    'quantity' => 2,
                ],
            ],
            'currency' => 'USD',
        ];
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
