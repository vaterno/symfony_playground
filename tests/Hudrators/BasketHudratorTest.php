<?php

namespace App\Tests\Hudrators;

use App\Entity\Basket;
use App\Entity\BasketItem;
use PHPUnit\Framework\TestCase;
use App\Services\Basket\Hudrators\BasketHudrator;

class BasketHudratorTest extends TestCase
{
    public function testHudrateFromArraySuccess()
    {
        $requestData = $this->getRequestData();

        /** @var Basket $basket */
        $basket = BasketHudrator::hudrateFromArray($requestData);
        /** @var BasketItem $item */
        $item = $basket->getItems()['0'];

        $this->assertInstanceOf(Basket::class, $basket);
        $this->assertInstanceOf(BasketItem::class, $item);
        $this->assertEquals($requestData['currency'], $basket->getCurrency()->name);
        $this->assertNull($basket->getPrice());
    }

    protected function getRequestData(): array
    {
        return [
            'items' => [
                [
                    'currency' => 'EUR',
                    'price' => 12.39,
                    'quantity' => 1,
                ],
            ],
            'currency' => 'USD',
        ];
    }
}
