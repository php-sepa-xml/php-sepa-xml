<?php

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use PHPUnit\Framework\TestCase;

/**
 * Asserts the wire shape produced by BaseDomBuilder::getStructuredRemittanceElement
 * (creditor-reference path). The SCT/SDD XSDs permit arbitrary free-text under
 * RmtInf/Ustrd, so a regression in this path would slip past schema validation
 * but get rejected downstream by bank validators.
 *
 * Shape asserted:
 *   RmtInf
 *     Strd
 *       CdtrRefInf
 *         Tp
 *           CdOrPrtry
 *             Cd = 'SCOR'
 *           [Issr = creditorReferenceType]   (optional)
 *         Ref = creditorReference
 */
class StructuredRemittanceTest extends TestCase
{
    private const SCT_PAIN = 'pain.001.001.09';
    private const SDD_PAIN = 'pain.008.001.02';

    public function testSCTEmitsScorStructuredRemittanceWithoutIssuer(): void
    {
        $xpath = $this->sctXpath(function (CustomerCreditTransferInformation $t): void {
            $t->setCreditorReference('RF81123453');
        });

        $this->assertSame(1, $xpath->query('//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf')->length);
        $this->assertSame(
            'SCOR',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Tp/ns:CdOrPrtry/ns:Cd)')
        );
        $this->assertSame(
            'RF81123453',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Ref)')
        );
        $this->assertSame(
            0,
            $xpath->query('//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Tp/ns:Issr')->length,
            'Issr must be omitted when creditorReferenceType is not set'
        );
    }

    public function testSCTEmitsScorStructuredRemittanceWithIssuer(): void
    {
        $xpath = $this->sctXpath(function (CustomerCreditTransferInformation $t): void {
            $t->setCreditorReference('RF81123453');
            $t->setCreditorReferenceType('ISO-11649');
        });

        $this->assertSame(
            'ISO-11649',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Tp/ns:Issr)')
        );
    }

    public function testSCTUnstructuredRemittanceDoesNotEmitStrd(): void
    {
        $xpath = $this->sctXpath(function (CustomerCreditTransferInformation $t): void {
            $t->setRemittanceInformation('Invoice 42');
        });

        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd')->length);
        $this->assertSame(
            'Invoice 42',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:RmtInf/ns:Ustrd)')
        );
    }

    public function testSCTCreditorReferenceTakesPrecedenceOverRemittanceInformation(): void
    {
        // The DomBuilder prefers the structured path when a creditorReference
        // is set, even if remittanceInformation is also populated.
        $xpath = $this->sctXpath(function (CustomerCreditTransferInformation $t): void {
            $t->setCreditorReference('RF81123453');
            $t->setRemittanceInformation('Should be ignored');
        });

        $this->assertSame(1, $xpath->query('//ns:CdtTrfTxInf/ns:RmtInf/ns:Strd')->length);
        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:RmtInf/ns:Ustrd')->length);
    }

    public function testSDDEmitsScorStructuredRemittanceWithoutIssuer(): void
    {
        $xpath = $this->sddXpath(function (CustomerDirectDebitTransferInformation $t): void {
            $t->setCreditorReference('RF81123453');
        });

        $this->assertSame(
            'SCOR',
            $xpath->evaluate('string(//ns:DrctDbtTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Tp/ns:CdOrPrtry/ns:Cd)')
        );
        $this->assertSame(
            'RF81123453',
            $xpath->evaluate('string(//ns:DrctDbtTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Ref)')
        );
        $this->assertSame(
            0,
            $xpath->query('//ns:DrctDbtTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Tp/ns:Issr')->length
        );
    }

    public function testSDDEmitsScorStructuredRemittanceWithIssuer(): void
    {
        $xpath = $this->sddXpath(function (CustomerDirectDebitTransferInformation $t): void {
            $t->setCreditorReference('RF81123453');
            $t->setCreditorReferenceType('ISO-11649');
        });

        $this->assertSame(
            'ISO-11649',
            $xpath->evaluate('string(//ns:DrctDbtTxInf/ns:RmtInf/ns:Strd/ns:CdtrRefInf/ns:Tp/ns:Issr)')
        );
    }

    public function testSDDUnstructuredRemittanceDoesNotEmitStrd(): void
    {
        $xpath = $this->sddXpath(function (CustomerDirectDebitTransferInformation $t): void {
            $t->setRemittanceInformation('Invoice 42');
        });

        $this->assertSame(0, $xpath->query('//ns:DrctDbtTxInf/ns:RmtInf/ns:Strd')->length);
        $this->assertSame(
            'Invoice 42',
            $xpath->evaluate('string(//ns:DrctDbtTxInf/ns:RmtInf/ns:Ustrd)')
        );
    }

    private function sctXpath(callable $configureTransfer): \DOMXPath
    {
        $builder = new CustomerCreditTransferDomBuilder(self::SCT_PAIN);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setBic('DEUTDEFF');
        $configureTransfer($transfer);
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $this->xpath($builder->asXml(), self::SCT_PAIN);
    }

    private function sddXpath(callable $configureTransfer): \DOMXPath
    {
        $builder = new CustomerDirectDebitTransferDomBuilder(self::SDD_PAIN);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerDirectDebitTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('DE67ZZZ00000123456');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerDirectDebitTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setBic('DEUTDEFF');
        $transfer->setMandateId('M1');
        $transfer->setMandateSignDate(new \DateTimeImmutable('2022-05-15'));
        $configureTransfer($transfer);
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $this->xpath($builder->asXml(), self::SDD_PAIN);
    }

    private function xpath(string $xml, string $painFormat): \DOMXPath
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));

        return $xpath;
    }
}
