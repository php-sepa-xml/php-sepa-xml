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
 * Exercises the three branches of BaseDomBuilder::getFinancialInstitutionElement:
 *  - legacy <BIC>
 *  - <BICFI> (SCT variant 1 v>=4, SDD variant 1 v>=3)
 *  - <Othr><Id>NOTPROVIDED</Id></Othr> fallback when BIC is null
 *
 * Read it together with IMPROVEMENTS.md #4 and open issue #233: when the
 * NOTPROVIDED strategy becomes pluggable these tests stake out the current
 * behaviour.
 */
class FinancialInstitutionElementTest extends TestCase
{
    /**
     * @dataProvider sctLegacyBicProvider
     */
    public function testSCTEmitsLegacyBicForVariant1BeforeV4(string $painFormat): void
    {
        $xpath = $this->sctXpath($painFormat, 'DEUTDEFF');

        $this->assertSame(1, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BIC')->length);
        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BICFI')->length);
        $this->assertSame(
            'DEUTDEFF',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BIC)')
        );
    }

    /**
     * @dataProvider sctBicfiProvider
     */
    public function testSCTEmitsBicfiForVariant1V4Plus(string $painFormat): void
    {
        $xpath = $this->sctXpath($painFormat, 'DEUTDEFF');

        $this->assertSame(1, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BICFI')->length);
        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BIC')->length);
        $this->assertSame(
            'DEUTDEFF',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BICFI)')
        );
    }

    /**
     * @dataProvider sctLegacyBicProvider
     * @dataProvider sctBicfiProvider
     */
    public function testSCTFallsBackToNotProvidedWhenBicIsNull(string $painFormat): void
    {
        $xpath = $this->sctXpath($painFormat, null);

        $this->assertSame(
            'NOTPROVIDED',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:Othr/ns:Id)'),
            'NOTPROVIDED fallback expected for ' . $painFormat
        );
        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BIC')->length);
        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:BICFI')->length);
    }

    /**
     * @dataProvider sddLegacyBicProvider
     */
    public function testSDDEmitsLegacyBicForVariant1BeforeV3(string $painFormat): void
    {
        $xpath = $this->sddXpath($painFormat, 'DEUTDEFF');

        $this->assertSame(1, $xpath->query('//ns:DrctDbtTxInf/ns:DbtrAgt/ns:FinInstnId/ns:BIC')->length);
        $this->assertSame(0, $xpath->query('//ns:DrctDbtTxInf/ns:DbtrAgt/ns:FinInstnId/ns:BICFI')->length);
    }

    /**
     * @dataProvider sddBicfiProvider
     */
    public function testSDDEmitsBicfiForVariant1V3Plus(string $painFormat): void
    {
        $xpath = $this->sddXpath($painFormat, 'DEUTDEFF');

        $this->assertSame(1, $xpath->query('//ns:DrctDbtTxInf/ns:DbtrAgt/ns:FinInstnId/ns:BICFI')->length);
        $this->assertSame(0, $xpath->query('//ns:DrctDbtTxInf/ns:DbtrAgt/ns:FinInstnId/ns:BIC')->length);
    }

    /**
     * @dataProvider sddBicfiProvider
     */
    public function testSDDFallsBackToNotProvidedWhenBicIsNull(string $painFormat): void
    {
        $xpath = $this->sddXpath($painFormat, null);

        $this->assertSame(
            'NOTPROVIDED',
            $xpath->evaluate('string(//ns:DrctDbtTxInf/ns:DbtrAgt/ns:FinInstnId/ns:Othr/ns:Id)')
        );
    }

    public static function sctLegacyBicProvider(): iterable
    {
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
        ];
    }

    public static function sctBicfiProvider(): iterable
    {
        return [
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.12' => ['pain.001.001.12'],
        ];
    }

    public static function sddLegacyBicProvider(): iterable
    {
        return [
            'pain.008.001.02' => ['pain.008.001.02'],
        ];
    }

    public static function sddBicfiProvider(): iterable
    {
        return [
            'pain.008.001.09' => ['pain.008.001.09'],
            'pain.008.001.10' => ['pain.008.001.10'],
            'pain.008.001.11' => ['pain.008.001.11'],
        ];
    }

    private function sctXpath(string $painFormat, ?string $transferBic): \DOMXPath
    {
        $builder = new CustomerCreditTransferDomBuilder($painFormat);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        if ($transferBic !== null) {
            $transfer->setBic($transferBic);
        }
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $this->xpath($builder->asXml(), $painFormat);
    }

    private function sddXpath(string $painFormat, ?string $transferBic): \DOMXPath
    {
        $builder = new CustomerDirectDebitTransferDomBuilder($painFormat);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerDirectDebitTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('DE67ZZZ00000123456');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerDirectDebitTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setMandateId('M1');
        $transfer->setMandateSignDate(new \DateTimeImmutable('2022-05-15'));
        if ($transferBic !== null) {
            $transfer->setBic($transferBic);
        }
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $this->xpath($builder->asXml(), $painFormat);
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
