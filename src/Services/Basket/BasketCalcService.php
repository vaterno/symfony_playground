<?php

namespace App\Services\Basket;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Services\Basket\Exceptions\ExchangeRateDoesntExistsException;
use App\Services\Basket\Exceptions\ExchangeRateDoesntSetException;
use App\Services\Basket\Exceptions\ExistsMultiCurrencyException;
use App\Services\ExchangeRate\DTO\ExchangeListDto;

class BasketCalcService
{
    public function calc(Basket $basket, ?ExchangeListDto $exchangeRatesDto = null): Basket
    {
        if (
            $basket->isMultiCurrency() &&
            !empty($basket->getItems())
        ) {
            if (empty($exchangeRatesDto)) {
                throw new ExchangeRateDoesntSetException();
            }

            $basket = $this->transformToBaseBasketCurrency($basket, $exchangeRatesDto);
        }

        $this->calcPrice($basket);

        return $basket;
    }

    /**
     * Calc and set price for basket
     *
     * @param Basket $basket
     * @return Basket
     * @throws ExistsMultiCurrencyException
     */
    public function calcPrice(Basket $basket): Basket
    {
        if ($basket->isMultiCurrency()) {
            throw new ExistsMultiCurrencyException();
        }

        if (empty($basket->getItems())) {
            $basket->setPrice(0);
            return $basket;
        }

        $totalPrice = 0;
        /** @var BasketItem $basketItem */
        foreach ($basket->getItems() as $basketItem) {
            $price = $basketItem->getPrice() * $basketItem->getQuantity();
            $totalPrice += $price;
        }

        $basket->setPrice($totalPrice);

        return $basket;
    }

    /**
     * Goes through basket items and changes them to basket currency
     *
     * @param Basket $basket
     * @param ExchangeListDto $exchangeListDto
     * @return Basket|null
     * @throws Exceptions\WrongBasketItemTypeException
     * @throws ExchangeRateDoesntExistsException
     */
    public function transformToBaseBasketCurrency(Basket $basket, ExchangeListDto $exchangeListDto): ?Basket
    {
        if (
            !$basket->isMultiCurrency() ||
            empty($basket->getItems())
        ) {
            return $basket;
        }

        if (empty($exchangeListDto->rates)) {
            return null;
        }

        $newBasket = clone $basket;
        $basketItems = $newBasket->getItems();
        $newBasket->setItems([]);

        $temps = [];
        /** @var BasketItem $basketItem */
        foreach ($basketItems as $basketItem) {
            $newBasketItem = clone $basketItem;

            if ($newBasketItem->getCurrency()->name === $newBasket->getCurrency()->name) {
                $temps[] = $newBasketItem;
                continue;
            }

            $exchangeRate = $exchangeListDto->getExchangeRateByCurrency($newBasketItem->getCurrency());

            if ($exchangeRate === null) {
                throw new ExchangeRateDoesntExistsException();
            }

            $newBasketItem
                ->setPrice($newBasketItem->getPrice() / $exchangeRate['rate'])
                ->setCurrency($newBasket->getCurrency());

            $temps[] = $newBasketItem;
        }

        $newBasket->setItems($temps);

        return $newBasket;
    }
}
