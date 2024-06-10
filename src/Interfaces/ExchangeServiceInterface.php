<?php

namespace App\Interfaces;

interface ExchangeServiceInterface {
    public function getRate($currency);
}
