<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\TransactionProcessor;
use App\Services\BinService;
use App\Services\ExchangeService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Main execution
try {
    $processor = new TransactionProcessor(new BinService(), new ExchangeService());
    $transactions = file($argv[1] ?? 'input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($transactions as $transaction) {
        echo $processor->processTransaction($transaction) . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
