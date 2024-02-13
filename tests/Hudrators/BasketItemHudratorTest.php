<?php

namespace App\Tests\Hudrators;

use App\Entity\BasketItem;
use PHPUnit\Framework\TestCase;
use App\Services\Basket\Hudrators\BasketItemHudrate;

class BasketItemHudratorTest extends TestCase
{
    public function testHudrateFromArraySuccess()
    {
        $data = $this->getRequestData();

        /** @var BasketItem[] $basketItems */
        $basketItems = BasketItemHudrate::hudrateFromArray($data);
        $item = $basketItems['0'];

        $this->assertInstanceOf(BasketItem::class, $item);
        $this->assertEquals($item->getCurrency()->name, $data['0']['currency']);
        $this->assertEquals($item->getPrice(), $data['0']['price']);
        $this->assertEquals($item->getQuantity(), $data['0']['quantity']);
    }

    protected function getRequestData(): array
    {
        return [
            [
                'currency' => 'EUR',
                'price' => 12.39,
                'quantity' => 1,
            ],
        ];
    }
}
