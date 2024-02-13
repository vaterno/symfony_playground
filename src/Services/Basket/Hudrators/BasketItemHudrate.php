<?php

namespace App\Services\Basket\Hudrators;

use App\Entity\BasketItem;
use App\Services\Currency\Dictionary\CurrencyDictionary;

class BasketItemHudrate
{
    /**
     * @param array $items
     * @return BasketItem[]
     */
    public static function hudrateFromArray(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = new BasketItem(
                CurrencyDictionary::getByName($item['currency']),
                $item['price'],
                $item['quantity']
            );
        }

        return $result;
    }
}
