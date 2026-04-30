<?php

namespace Digitick\Sepa\Tests\Unit\TransferFile\Factory;

use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Facade\CustomerCreditFacade;
use Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use PHPUnit\Framework\TestCase;

class TransferFileFacadeFactoryTest extends TestCase
{
    public function testCreateCustomerCreditReturnsCreditFacade(): void
    {
        $facade = TransferFileFacadeFactory::createCustomerCredit('MSG', 'Init', 'pain.001.001.09');

        $this->assertInstanceOf(CustomerCreditFacade::class, $facade);
    }

    public function testCreateCustomerCreditDefaultPainFormatProducesValidDocument(): void
    {
        $facade = TransferFileFacadeFactory::createCustomerCredit('MSG', 'Init');
        $facade->addPaymentInfo('p', [
            'id' => 'p',
            'debtorName' => 'Me',
            'debtorAccountIBAN' => 'DE88500105173441451911',
            'debtorAgentBIC' => 'DEUTDEFF',
        ]);
        $facade->addTransfer('p', [
            'amount' => 100,
            'creditorIban' => 'DE40500105174181777145',
            'creditorName' => 'Bob',
            'remittanceInformation' => 'x',
        ]);

        $xml = $facade->asXML();
        $this->assertStringContainsString('pain.001.001.09', $xml);
    }

    public function testCreateCustomerCreditWithGroupHeaderPreservesHeader(): void
    {
        $header = new GroupHeader('CUSTOM-ID', 'Company', true);
        $header->setIssuer('IssuerName');

        $facade = TransferFileFacadeFactory::createCustomerCreditWithGroupHeader($header, 'pain.001.001.09');
        $facade->addPaymentInfo('p', [
            'id' => 'p',
            'debtorName' => 'Me',
            'debtorAccountIBAN' => 'DE88500105173441451911',
            'debtorAgentBIC' => 'DEUTDEFF',
        ]);
        $facade->addTransfer('p', [
            'amount' => 100,
            'creditorIban' => 'DE40500105174181777145',
            'creditorName' => 'Bob',
            'remittanceInformation' => 'x',
        ]);

        $xml = $facade->asXML();
        $this->assertStringContainsString('<MsgId>CUSTOM-ID</MsgId>', $xml);
    }

    public function testCreateCustomerCreditWithGroupHeaderHonoursWithSchemaLocationFalse(): void
    {
        $header = new GroupHeader('MSG', 'Company');

        $facade = TransferFileFacadeFactory::createCustomerCreditWithGroupHeader(
            $header,
            'pain.001.001.09',
            false
        );
        $facade->addPaymentInfo('p', [
            'id' => 'p',
            'debtorName' => 'Me',
            'debtorAccountIBAN' => 'DE88500105173441451911',
            'debtorAgentBIC' => 'DEUTDEFF',
        ]);
        $facade->addTransfer('p', [
            'amount' => 100,
            'creditorIban' => 'DE40500105174181777145',
            'creditorName' => 'Bob',
            'remittanceInformation' => 'x',
        ]);

        $xml = $facade->asXML();
        $this->assertStringNotContainsString('xsi:schemaLocation', $xml);
    }

    public function testCreateDirectDebitReturnsDirectDebitFacade(): void
    {
        $facade = TransferFileFacadeFactory::createDirectDebit('MSG', 'Init', 'pain.008.001.02');

        $this->assertInstanceOf(CustomerDirectDebitFacade::class, $facade);
    }

    public function testCreateDirectDebitWithGroupHeaderPreservesHeader(): void
    {
        $header = new GroupHeader('CUSTOM-ID', 'Company');

        $facade = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.02');
        $facade->addPaymentInfo('p', [
            'id' => 'p',
            'creditorName' => 'Me',
            'creditorAccountIBAN' => 'DE88500105173441451911',
            'creditorAgentBIC' => 'DEUTDEFF',
            'seqType' => PaymentInformation::S_ONEOFF,
            'creditorId' => 'DE67ZZZ00000123456',
        ]);
        $facade->addTransfer('p', [
            'amount' => 100,
            'debtorIban' => 'DE40500105174181777145',
            'debtorBic' => 'DEUTDEFF',
            'debtorName' => 'Bob',
            'debtorMandate' => 'M1',
            'debtorMandateSignDate' => '2022-05-15',
            'remittanceInformation' => 'x',
        ]);

        $xml = $facade->asXML();
        $this->assertStringContainsString('<MsgId>CUSTOM-ID</MsgId>', $xml);
    }
}
