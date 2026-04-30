<?php

namespace Digitick\Sepa\Tests\Unit\TransferFile;

use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\Exception\InvalidTransferTypeException;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use PHPUnit\Framework\TestCase;

class CustomerCreditTransferFileTest extends TestCase
{
    public function testAddPaymentInformationForcesTrfPaymentMethod(): void
    {
        $file = new CustomerCreditTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();

        $file->addPaymentInformation($payment);

        $this->assertSame('TRF', $payment->getPaymentMethod());
    }

    public function testAddPaymentInformationAccumulatesGroupHeaderCounters(): void
    {
        $header = new GroupHeader('MSG', 'Acme');
        $file = new CustomerCreditTransferFile($header);

        $payment = $this->newValidPayment();
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12', 'A'));
        $payment->addTransfer(new CustomerCreditTransferInformation(250, 'DE12', 'B'));

        $file->addPaymentInformation($payment);

        $this->assertSame(2, $header->getNumberOfTransactions());
        $this->assertSame(350, $header->getControlSumCents());
    }

    public function testValidateThrowsWhenNoPaymentInformationAdded(): void
    {
        $file = new CustomerCreditTransferFile(new GroupHeader('MSG', 'Acme'));

        $this->expectException(InvalidTransferFileConfiguration::class);
        $file->validate();
    }

    public function testValidateThrowsWhenPaymentHasNoTransfers(): void
    {
        $file = new CustomerCreditTransferFile(new GroupHeader('MSG', 'Acme'));
        $file->addPaymentInformation($this->newValidPayment());

        $this->expectException(InvalidTransferFileConfiguration::class);
        $this->expectExceptionMessage('PaymentInformation must at least contain one payment');
        $file->validate();
    }

    public function testValidateThrowsWhenTransferIsWrongType(): void
    {
        $file = new CustomerCreditTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();
        $payment->addTransfer(new CustomerDirectDebitTransferInformation(100, 'DE12', 'Alice'));
        $file->addPaymentInformation($payment);

        $this->expectException(InvalidTransferTypeException::class);
        $file->validate();
    }

    public function testValidateAcceptsValidFile(): void
    {
        $file = new CustomerCreditTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12', 'Alice'));
        $file->addPaymentInformation($payment);

        $file->validate();
        $this->addToAssertionCount(1);
    }

    public function testGetGroupHeaderReturnsInjectedInstance(): void
    {
        $header = new GroupHeader('MSG', 'Acme');
        $file = new CustomerCreditTransferFile($header);

        $this->assertSame($header, $file->getGroupHeader());
    }

    private function newValidPayment(): PaymentInformation
    {
        return new PaymentInformation('pay1', 'DE12', 'BIC', 'Origin');
    }
}
