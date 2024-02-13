<?php

namespace App\Entity;

use App\Services\Basket\Exceptions\WrongBasketItemTypeException;
use App\Services\Currency\Dictionary\CurrencyDictionary;

class Basket
{
    /**
     * @var BasketItem[]
     */
    protected array $items = [];

    protected CurrencyDictionary $currency;

    protected ?float $totalPrice = null;

    public function __construct(
        CurrencyDictionary $currency,
        array $items,
        ?float $totalPrice = null
    ) {
        $this->setItems($items)
            ->setCurrency($currency)
            ->setPrice($totalPrice);
    }

    public function getCurrency(): CurrencyDictionary
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyDictionary $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return BasketItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $basketItems
     * @return $this
     *
     * @throws WrongBasketItemTypeException
     */
    public function setItems(array $basketItems): static
    {
        $this->items = [];

        foreach ($basketItems as $basketItem) {
            if (!$basketItem instanceof BasketItem) {
                throw new WrongBasketItemTypeException();
            }

            $this->items[] = $basketItem;
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setPrice(?float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    /**
     * Check if in basket exists multi currency
     *
     * @return bool
     */
    public function isMultiCurrency(): bool
    {
        /** @var BasketItem[] $basketItems */
        $basketItems = $this->getItems();

        foreach ($basketItems as $basketItem) {
            if ($basketItem->getCurrency()->name !== $this->getCurrency()->name) {
                return true;
            }
        }

        return false;
    }
}
