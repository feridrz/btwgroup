<?php

namespace App;

use App\Interfaces\BinServiceInterface;
use App\Interfaces\ExchangeServiceInterface;

class TransactionProcessor {
    private $binService;
    private $exchangeService;

    public function __construct(BinServiceInterface $binService, ExchangeServiceInterface $exchangeService) {
        $this->binService = $binService;
        $this->exchangeService = $exchangeService;
    }

    public function processTransaction($transaction) {
        $values = json_decode($transaction, true);

        $binResults = $this->binService->lookup($values['bin']);
        $isEu = $this->isEuCountry($binResults['country']['alpha2']);
        $rate = ($values['currency'] == 'EUR') ? 1 : $this->exchangeService->getRate($values['currency']);
        $amount = $values['amount'] / $rate;
        $commissionRate = ($isEu ? 0.01 : 0.02);

        return round($amount * $commissionRate, 2);
    }

    private function isEuCountry($countryCode) {
        $euCountries = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'];
        return in_array($countryCode, $euCountries);
    }
}
