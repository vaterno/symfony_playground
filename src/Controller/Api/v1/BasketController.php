<?php

namespace App\Controller\Api\v1;

use App\Entity\Basket;
use App\Requests\BasketCalcCostRequest;
use App\Services\Basket\BasketCalcService;
use App\Services\ExchangeRate\ExchangeRateService;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\Basket\Hudrators\BasketHudrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BasketController extends AbstractController
{
    #[Route('/api/v1/basket/calc-cost', methods: ['POST'])]
    public function getCalcCost(
        BasketCalcCostRequest $basketCalcCostRequest,
        ExchangeRateService $exchangeRateService,
        BasketCalcService $basketCalcService,
    ) {
        try {
            /** @var Basket|null $basket */
            $basket = BasketHudrator::hudrateFromArray($basketCalcCostRequest->getData());

            $exchangeRatesDto = ($basket->isMultiCurrency() && !empty($basket->getItems()))
                ? $exchangeRateService->getListOfRates($basket->getCurrency())
                : null;
            $basket = $basketCalcService->calc($basket, $exchangeRatesDto);

            return $this->json([
                'checkoutPrice' => $basket->getPrice(),
                'checkoutCurrency' => $basket->getCurrency()->name,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => true,
                'messages' => [
                    $e->getMessage(),
                ],
            ]);
        }
    }
}
