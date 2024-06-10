<?php

use PHPUnit\Framework\TestCase;
use App\TransactionProcessor;
use App\Interfaces\BinServiceInterface;
use App\Interfaces\ExchangeServiceInterface;

class TransactionProcessorTest extends TestCase
{
    private $binServiceMock;
    private $exchangeServiceMock;
    private $processor;

    protected function setUp(): void
    {
        $this->binServiceMock = $this->createMock(BinServiceInterface::class);
        $this->exchangeServiceMock = $this->createMock(ExchangeServiceInterface::class);

        $this->processor = new TransactionProcessor($this->binServiceMock, $this->exchangeServiceMock);
    }

    public function testProcessTransaction()
    {
        // Setup test data
        $transaction = json_encode([
            'bin' => '123456',
            'amount' => 100,
            'currency' => 'USD'
        ]);

        $binResults = [
            'country' => ['alpha2' => 'US']
        ];

        $exchangeRate = 1.1;

        // Configure the mocks
        $this->binServiceMock
            ->method('lookup')
            ->willReturn($binResults);

        $this->exchangeServiceMock
            ->method('getRate')
            ->willReturn($exchangeRate);

        // Perform the test
        $result = $this->processor->processTransaction($transaction);

        // Check the result
        $expectedCommission = round((100 / 1.1) * 0.02, 2);
        $this->assertEquals($expectedCommission, $result);
    }
}

