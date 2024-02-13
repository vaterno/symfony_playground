<?php

namespace App\Tests\Entity;

use App\Entity\Basket;
use App\Services\Basket\Hudrators\BasketHudrator;
use PHPUnit\Framework\TestCase;

class BasketTest extends TestCase
{
    public function testIsMultiCurrency()
    {
        $data = $this->getData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($data);

        $this->assertEquals(true, $basket->isMultiCurrency());
    }

    public function testIsNotMultiCurrency()
    {
        $data = $this->getData();
        $data['items'] = array_map(function ($item) use($data) {
            $item['currency'] = $data['currency'];
            return $item;
        }, $data['items']);

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($data);

        $this->assertEquals(false, $basket->isMultiCurrency());
    }

    protected function getData(): array
    {
        return [
            'items' => [
                [
                    'currency' => 'EUR',
                    'price' => 12.39,
                    'quantity' => 1,
                ],
                [
                    'currency' => 'UAH',
                    'price' => 95.94,
                    'quantity' => 1,
                ],
                [
                    'currency' => 'PLN',
                    'price' => 95.20,
                    'quantity' => 1,
                ],
            ],
            'currency' => 'USD',
        ];
    }
}
