<?php

namespace App\Requests;

use App\Services\Currency\Dictionary\CurrencyDictionary;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class BasketCalcCostRequest extends AbstractValidationRequest
{
    protected function getValidationRules(): ?ValidationRules
    {
        $currenciesISO = implode(',', array_keys(CurrencyDictionary::getAll()));

        return new ValidationRules([
            'request' => [
                'items.*.currency' => 'required|string|in:' . $currenciesISO,
                'items.*.price' => 'required|float',
                'items.*.quantity' => 'required|int',
                'checkoutCurrency' => 'required|string|in:' . $currenciesISO,
            ],
        ]);
    }

    public function getData(): array
    {
        $data = $this->request->getPayload()->all();

        return [
            'items' => $data['items'],
            'currency' => $data['checkoutCurrency'],
        ];
    }
}
