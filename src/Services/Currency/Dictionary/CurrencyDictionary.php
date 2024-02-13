<?php

namespace App\Services\Currency\Dictionary;

enum CurrencyDictionary: string
{
    case EUR = 'Euro';
    case USD = 'United States Dollar';
    case UAH = 'Ukrainian Hryvnia';
    case PLN = 'Polish Zloty';
    case SGD = 'Singapore Dollar';
    case CHF = 'Swiss Franc';
    case AED = "United Arab Emirates Dirham";
    case AFN = "Afghan Afghani";
    case ALL = "Albanian Lek";
    case AMD = "Armenian Dram";
    case ANG = 'Netherlands Antillean Guilder';
    case CAD = 'Canadian Dollar';

    public static function getAll(): array
    {
        $result = [];
        $currencies = CurrencyDictionary::cases();

        foreach ($currencies as $currency) {
            $result[$currency->name] = $currency->value;
        }

        return $result;
    }

    public static function getByName(string $name): ?CurrencyDictionary
    {
        $currencies = CurrencyDictionary::cases();

        foreach ($currencies as $currency) {
            if ($currency->name === $name) {
                return $currency;
            }
        }

        return null;
    }
}
