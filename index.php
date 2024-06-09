<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

interface BinServiceInterface {
    public function lookup($bin);
}

interface ExchangeServiceInterface {
    public function getRate($currency);
}

class BinService implements BinServiceInterface {
    private $baseUrl;

    public function __construct() {
        $this->baseUrl = $_ENV['BINLIST_URL'];
    }

    public function lookup($bin) {
        $url = $this->baseUrl . $bin;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {


            throw new \Exception("Error fetching BIN results.");
        }
        return json_decode($response, true);
    }
}

class ExchangeService implements ExchangeServiceInterface {
    private $baseUrl;
    private $apiKey;

    public function __construct() {
        $this->baseUrl = $_ENV['EXCHANGE_RATES_URL'];
        $this->apiKey = $_ENV['API_KEY'];
    }

    public function getRate($currency) {
        $url = "{$this->baseUrl}?access_key={$this->apiKey}&symbols={$currency}";
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        $response = curl_exec($ch);
//        curl_close($ch);
//        if (!$response) {
//            throw new \Exception("Error fetching exchange rates.");
//        }
//        $data = json_decode($response, true);

        $data = '{"number":{},"scheme":"mastercard","type":"debit","brand":"Debit Mastercard","country":{"numeric":"440","alpha2":"LT","name":"Lithuania","emoji":"🇱🇹","currency":"EUR","latitude":56,"longitude":24},"bank":{"name":"Swedbank Ab"}}';

        return $data['rates'][$currency] ?? 0;
    }
}

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

// Main execution
try {
    $processor = new TransactionProcessor(new BinService(), new ExchangeService());
    $transactions = file($argv[1], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($transactions as $transaction) {
        echo $processor->processTransaction($transaction) . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
