<?php

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use PHPUnit\Framework\TestCase;

class CustomerCreditTransferDomBuilderTest extends TestCase
{
    public function testCustomerCreditTransferDomWithDefaults(): void
    {
        $groupHeader = new GroupHeader('TEST_PYMT', 'Test Company Inc.');

        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $paymentInformation = new PaymentInformation('RAND001', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Test Company Inc.');
        $transferFile->addPaymentInformation($paymentInformation);

        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.03');

        $transfer = new CustomerCreditTransferInformation(1234, 'DE88500105173441451911', 'Jürgen Bosch', 'XYZ');
        $transfer->setRemittanceInformation('For my lovely colleague, Nerd');

        $paymentInformation->addTransfer($transfer);
        $transferFile->addPaymentInformation($paymentInformation);

        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($paymentInformation);
        $builder->visitTransferInformation($transfer);

        $xml = $builder->asXml();

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');

        $this->assertSame('TEST_PYMT', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:GrpHdr/ns:MsgId')->item(0)->textContent);
        $this->assertSame(1, (int) $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:GrpHdr/ns:NbOfTxs')->item(0)->textContent);
        $this->assertSame('SEPA', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:PmtTpInf/ns:SvcLvl/ns:Cd')->item(0)->textContent);
        $this->assertSame('SLEV', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:ChrgBr')->item(0)->textContent);
        $this->assertSame('For my lovely colleague, Nerd', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:RmtInf/ns:Ustrd')->item(0)->textContent);
        $this->assertSame('Ccy', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:Amt/ns:InstdAmt')->item(0)->attributes->item(0)->name);
        $this->assertSame('EUR', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:Amt/ns:InstdAmt')->item(0)->attributes->item(0)->value);

    }

    public function testWeCanCreateAUkPaymentFile() {
        $groupHeader = new GroupHeader('TEST_PYMT', 'Test Company Inc.');

        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $paymentInformation = new PaymentInformation('RAND001', 'GB29NWBK60161331926819', 'NWBKGB2LXXX', 'Test Company Inc.');
        $paymentInformation->setChargeBearer();
        $paymentInformation->setServiceLevelCode('NURG');

        $transferFile->addPaymentInformation($paymentInformation);

        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.03');

        $transfer = new CustomerCreditTransferInformation(1234, 'GB29NWBK60161331926819', 'Jürgen Bosch', 'XYZ');
        $transfer->setRemittanceInformation('For my lovely colleague, Nerd');
        $transfer->setCurrency('GBP');

        $paymentInformation->addTransfer($transfer);
        $transferFile->addPaymentInformation($paymentInformation);

        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($paymentInformation);
        $builder->visitTransferInformation($transfer);

        $xml = $builder->asXml();

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');

        $this->assertSame('TEST_PYMT', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:GrpHdr/ns:MsgId')->item(0)->textContent);
        $this->assertSame(1, (int) $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:GrpHdr/ns:NbOfTxs')->item(0)->textContent);
        $this->assertSame('NURG', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:PmtTpInf/ns:SvcLvl/ns:Cd')->item(0)->textContent);
        $this->assertNull($xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:ChrgBr')->item(0));
        $this->assertSame('For my lovely colleague, Nerd', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:RmtInf/ns:Ustrd')->item(0)->textContent);
        $this->assertSame('Ccy', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:Amt/ns:InstdAmt')->item(0)->attributes->item(0)->name);
        $this->assertSame('GBP', $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:Amt/ns:InstdAmt')->item(0)->attributes->item(0)->value);
    }
}
