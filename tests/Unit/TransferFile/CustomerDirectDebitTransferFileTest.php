<?php

namespace Digitick\Sepa\Tests\Unit\TransferFile;

use DateTimeImmutable;
use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\Exception\InvalidTransferTypeException;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use PHPUnit\Framework\TestCase;

class CustomerDirectDebitTransferFileTest extends TestCase
{
    public function testAddPaymentInformationForcesDdPaymentMethod(): void
    {
        $file = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();

        $file->addPaymentInformation($payment);

        $this->assertSame('DD', $payment->getPaymentMethod());
    }

    public function testAddPaymentInformationAccumulatesGroupHeaderCounters(): void
    {
        $header = new GroupHeader('MSG', 'Acme');
        $file = new CustomerDirectDebitTransferFile($header);

        $payment = $this->newValidPayment();
        $payment->addTransfer($this->newValidTransfer(100));
        $payment->addTransfer($this->newValidTransfer(250));

        $file->addPaymentInformation($payment);

        $this->assertSame(2, $header->getNumberOfTransactions());
        $this->assertSame(350, $header->getControlSumCents());
    }

    public function testValidateThrowsWhenNoPaymentInformationAdded(): void
    {
        $file = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Acme'));

        $this->expectException(InvalidTransferFileConfiguration::class);
        $file->validate();
    }

    public function testValidateThrowsWhenSequenceTypeMissing(): void
    {
        $file = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();
        // Clear the field the factory helper pre-sets.
        $payment->setSequenceType('');
        $file->addPaymentInformation($payment);

        $this->expectException(InvalidTransferFileConfiguration::class);
        $this->expectExceptionMessage('SequenceType');
        $file->validate();
    }

    public function testValidateThrowsWhenCreditorIdMissing(): void
    {
        $file = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = new PaymentInformation('pay1', 'DE12', 'BIC', 'Origin');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        // Deliberately omit setCreditorId().
        $file->addPaymentInformation($payment);

        $this->expectException(InvalidTransferFileConfiguration::class);
        $this->expectExceptionMessage('CreditorSchemeId');
        $file->validate();
    }

    public function testValidateThrowsWhenTransferIsWrongType(): void
    {
        $file = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12', 'Alice'));
        $file->addPaymentInformation($payment);

        $this->expectException(InvalidTransferTypeException::class);
        $file->validate();
    }

    public function testValidateAcceptsValidFile(): void
    {
        $file = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Acme'));
        $payment = $this->newValidPayment();
        $payment->addTransfer($this->newValidTransfer(100));
        $file->addPaymentInformation($payment);

        $file->validate();
        $this->addToAssertionCount(1);
    }

    public function testGetGroupHeaderReturnsInjectedInstance(): void
    {
        $header = new GroupHeader('MSG', 'Acme');
        $file = new CustomerDirectDebitTransferFile($header);

        $this->assertSame($header, $file->getGroupHeader());
    }

    private function newValidPayment(): PaymentInformation
    {
        $payment = new PaymentInformation('pay1', 'DE12', 'BIC', 'Origin');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('DE67ZZZ00000123456');

        return $payment;
    }

    private function newValidTransfer(int $amount): CustomerDirectDebitTransferInformation
    {
        $transfer = new CustomerDirectDebitTransferInformation($amount, 'DE12', 'Debtor');
        $transfer->setMandateId('M1');
        $transfer->setMandateSignDate(new DateTimeImmutable('2022-05-15'));

        return $transfer;
    }
}
