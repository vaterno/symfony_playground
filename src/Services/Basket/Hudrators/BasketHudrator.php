<?php

namespace App\Services\Basket\Hudrators;

use App\Entity\Basket;
use App\Services\Currency\Dictionary\CurrencyDictionary;

class BasketHudrator
{
    public static function hudrateFromArray(array $data): ?Basket
    {
        if (
            empty($data['currency']) ||
            !CurrencyDictionary::getByName($data['currency'])
        ) {
            return null;
        }

        $basketItems = [];
        if (!empty($data['items'])) {
            $basketItems = BasketItemHudrate::hudrateFromArray($data['items']);
        }

        return new Basket(
            CurrencyDictionary::getByName($data['currency']),
            $basketItems
        );
    }
}
