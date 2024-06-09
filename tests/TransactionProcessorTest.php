<?php

use PHPUnit\Framework\TestCase;
use App\TransactionProcessor;
use App\BinService;
use App\ExchangeService;

class TransactionProcessorTest extends TestCase {
    public function testProcessTransaction() {
        $binServiceMock = $this->createMock(BinService::class);
        $binServiceMock->method('lookup')->willReturn(['isEu' => true]);

        $exchangeServiceMock = $this->createMock(ExchangeService::class);
        $exchangeServiceMock->method('getRate')->willReturn(1.2);

        $processor = new TransactionProcessor($binServiceMock, $exchangeServiceMock);
        $result = $processor->processTransaction('{"bin":"45717360","amount":"100.00","currency":"EUR"}');
        $this->assertEquals(1.0, $result);
    }
}
