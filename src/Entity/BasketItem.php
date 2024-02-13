<?php

namespace App\Entity;

use App\Services\Currency\Dictionary\CurrencyDictionary;

class BasketItem
{
    protected CurrencyDictionary $currency;
    protected float $price;
    protected int $quantity;

    public function __construct(
        CurrencyDictionary $currency,
        float $price,
        int $quantity,
    ) {
        $this->setPrice($price)
            ->setCurrency($currency)
            ->setQuantity($quantity);
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = round($price, 2);
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'currency' => $this->getCurrency()->name,
            'price' => $this->getPrice(),
            'quantity' => $this->getQuantity(),
        ];
    }
}
