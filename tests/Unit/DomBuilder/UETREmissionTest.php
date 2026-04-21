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
 * Guards against regression of the version-gated UETR element emission rules:
 *  - SCT: emitted for variant 1, version >= 9.
 *  - SDD: emitted for variant 1, version >= 8.
 */
class UETREmissionTest extends TestCase
{
    private const SAMPLE_UUID = '550e8400-e29b-41d4-a716-446655440000';

    /**
     * @dataProvider sctUetrPresentProvider
     */
    public function testUETRPresentForSCTVariant1V9Plus(string $painFormat): void
    {
        $xml = $this->buildSctXml($painFormat);
        $xpath = $this->xpath($xml, $painFormat);

        $this->assertSame(
            self::SAMPLE_UUID,
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:PmtId/ns:UETR)'),
            'UETR must be emitted for ' . $painFormat
        );
    }

    /**
     * @dataProvider sctUetrAbsentProvider
     */
    public function testUETRAbsentForSCTOlderVersionsOrVariants(string $painFormat): void
    {
        $xml = $this->buildSctXml($painFormat);
        $xpath = $this->xpath($xml, $painFormat);

        $this->assertSame(
            0,
            $xpath->query('//ns:CdtTrfTxInf/ns:PmtId/ns:UETR')->length,
            'UETR must not be emitted for ' . $painFormat
        );
    }

    /**
     * @dataProvider sddUetrPresentProvider
     */
    public function testUETRPresentForSDDVariant1V8Plus(string $painFormat): void
    {
        $xml = $this->buildSddXml($painFormat);
        $xpath = $this->xpath($xml, $painFormat);

        $this->assertSame(
            self::SAMPLE_UUID,
            $xpath->evaluate('string(//ns:DrctDbtTxInf/ns:PmtId/ns:UETR)'),
            'UETR must be emitted for ' . $painFormat
        );
    }

    /**
     * @dataProvider sddUetrAbsentProvider
     */
    public function testUETRAbsentForSDDOlderVersionsOrVariants(string $painFormat): void
    {
        $xml = $this->buildSddXml($painFormat);
        $xpath = $this->xpath($xml, $painFormat);

        $this->assertSame(
            0,
            $xpath->query('//ns:DrctDbtTxInf/ns:PmtId/ns:UETR')->length,
            'UETR must not be emitted for ' . $painFormat
        );
    }

    public static function sctUetrPresentProvider(): iterable
    {
        return [
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.12' => ['pain.001.001.12'],
        ];
    }

    public static function sctUetrAbsentProvider(): iterable
    {
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
            'pain.001.001.05' => ['pain.001.001.05'],
            'pain.001.001.08' => ['pain.001.001.08'],
            // Off-variant formats (variant 2 or 3) must never emit UETR.
            'pain.001.002.03' => ['pain.001.002.03'],
            'pain.001.003.03' => ['pain.001.003.03'],
        ];
    }

    public static function sddUetrPresentProvider(): iterable
    {
        return [
            'pain.008.001.08' => ['pain.008.001.08'],
            'pain.008.001.09' => ['pain.008.001.09'],
            'pain.008.001.10' => ['pain.008.001.10'],
            'pain.008.001.11' => ['pain.008.001.11'],
        ];
    }

    public static function sddUetrAbsentProvider(): iterable
    {
        return [
            'pain.008.001.02' => ['pain.008.001.02'],
            'pain.008.001.05' => ['pain.008.001.05'],
            'pain.008.001.07' => ['pain.008.001.07'],
            // Off-variant must never emit UETR.
            'pain.008.002.02' => ['pain.008.002.02'],
            'pain.008.003.02' => ['pain.008.003.02'],
        ];
    }

    private function buildSctXml(string $painFormat): string
    {
        $builder = new CustomerCreditTransferDomBuilder($painFormat);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setBic('DEUTDEFF');
        $transfer->setUUID(self::SAMPLE_UUID);
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $builder->asXml();
    }

    private function buildSddXml(string $painFormat): string
    {
        $builder = new CustomerDirectDebitTransferDomBuilder($painFormat);

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
        $transfer->setUUID(self::SAMPLE_UUID);
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $builder->asXml();
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
