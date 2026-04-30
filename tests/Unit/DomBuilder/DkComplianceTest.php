<?php

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use PHPUnit\Framework\TestCase;

/**
 * Covers the opt-in compliance flags for environments like the German DK
 * specification (issue #233):
 *
 *  - setOmitGroupHeaderControlSum(true) suppresses <CtrlSum> under <GrpHdr>.
 *    DK forbids CtrlSum at the group-header level; it must only appear
 *    under PmtInf.
 *
 *  - setOmitAgentElementIfBicMissing(true) omits the <CdtrAgt>/<DbtrAgt>
 *    wrapper when no BIC is available, instead of emitting the
 *    <Othr><Id>NOTPROVIDED</Id></Othr> fallback that DK rejects.
 *
 * The defaults remain the pre-existing behaviour so that this change is
 * fully backwards compatible. Regression coverage for the defaults lives
 * in FinancialInstitutionElementTest.
 */
class DkComplianceTest extends TestCase
{
    private const SCT_PAIN = 'pain.001.001.03';
    private const SDD_PAIN = 'pain.008.001.02';

    // ---------- CtrlSum on GroupHeader --------------------------------------

    public function testGroupHeaderCtrlSumPresentByDefault(): void
    {
        $xpath = $this->renderSct(function ($builder) {
            // no flag
        });

        $this->assertSame(1, $xpath->query('//ns:GrpHdr/ns:CtrlSum')->length);
    }

    public function testSctOmitsGrpHdrCtrlSumWhenFlagSet(): void
    {
        $xpath = $this->renderSct(function (CustomerCreditTransferDomBuilder $builder) {
            $builder->setOmitGroupHeaderControlSum(true);
        });

        $this->assertSame(0, $xpath->query('//ns:GrpHdr/ns:CtrlSum')->length);
        // PmtInf/CtrlSum must still appear — CtrlSum is valid there.
        $this->assertSame(1, $xpath->query('//ns:PmtInf/ns:CtrlSum')->length);
    }

    public function testSddOmitsGrpHdrCtrlSumWhenFlagSet(): void
    {
        $xpath = $this->renderSdd(function (CustomerDirectDebitTransferDomBuilder $builder) {
            $builder->setOmitGroupHeaderControlSum(true);
        });

        $this->assertSame(0, $xpath->query('//ns:GrpHdr/ns:CtrlSum')->length);
        $this->assertSame(1, $xpath->query('//ns:PmtInf/ns:CtrlSum')->length);
    }

    // ---------- Agent element on missing BIC --------------------------------

    public function testSctOmitsCdtrAgtAtTransactionLevelWhenBicMissingAndFlagSet(): void
    {
        $xpath = $this->renderSct(function (CustomerCreditTransferDomBuilder $builder) {
            $builder->setOmitAgentElementIfBicMissing(true);
        }, /* transferBic */ null);

        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt')->length);
    }

    public function testSctOmitsDbtrAgtAtPaymentLevelWhenBicMissingAndFlagSet(): void
    {
        $xpath = $this->renderSct(function (CustomerCreditTransferDomBuilder $builder) {
            $builder->setOmitAgentElementIfBicMissing(true);
        }, /* transferBic */ 'DEUTDEFF', /* originBic */ null);

        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:DbtrAgt')->length);
    }

    public function testSctPreservesAgentElementsWhenBicPresentAndFlagSet(): void
    {
        $xpath = $this->renderSct(function (CustomerCreditTransferDomBuilder $builder) {
            $builder->setOmitAgentElementIfBicMissing(true);
        }, /* transferBic */ 'DEUTDEFF', /* originBic */ 'DEUTDEFF');

        // Flag is on but BICs are present — agent wrappers must remain.
        $this->assertSame(1, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt')->length);
        $this->assertSame(1, $xpath->query('//ns:PmtInf/ns:DbtrAgt')->length);
    }

    public function testSctEmitsNotProvidedWhenBicMissingAndFlagNotSet(): void
    {
        // Defaults must not change: existing NOTPROVIDED behaviour stays.
        $xpath = $this->renderSct(function ($builder) {
            // no flag
        }, /* transferBic */ null);

        $this->assertSame(1, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt')->length);
        $this->assertSame(
            'NOTPROVIDED',
            $xpath->evaluate('string(//ns:CdtTrfTxInf/ns:CdtrAgt/ns:FinInstnId/ns:Othr/ns:Id)')
        );
    }

    public function testSddOmitsDbtrAgtAtTransactionLevelWhenBicMissingAndFlagSet(): void
    {
        $xpath = $this->renderSdd(function (CustomerDirectDebitTransferDomBuilder $builder) {
            $builder->setOmitAgentElementIfBicMissing(true);
        }, /* transferBic */ null);

        $this->assertSame(0, $xpath->query('//ns:DrctDbtTxInf/ns:DbtrAgt')->length);
    }

    public function testSddOmitsCdtrAgtAtPaymentLevelWhenBicMissingAndFlagSet(): void
    {
        $xpath = $this->renderSdd(function (CustomerDirectDebitTransferDomBuilder $builder) {
            $builder->setOmitAgentElementIfBicMissing(true);
        }, /* transferBic */ 'DEUTDEFF', /* originBic */ null);

        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:CdtrAgt')->length);
    }

    // ---------- Integration: full DK-compliant pain.001.001.03 --------------

    public function testDkCompliantPain00100103FileHasExpectedStructure(): void
    {
        // DK intentionally diverges from the base ISO pain.001.001.03 XSD:
        //  - PmtInf/DbtrAgt is mandatory in the ISO XSD but DK requires it
        //    absent when no BIC is known, and validates via its own stricter
        //    profile schema rather than the base ISO one.
        // So we assert the structural DK requirements here rather than
        // base-XSD validation, which would necessarily fail for this shape.
        $builder = new CustomerCreditTransferDomBuilder(self::SCT_PAIN);
        $builder->setOmitGroupHeaderControlSum(true);
        $builder->setOmitAgentElementIfBicMissing(true);

        $groupHeader = new GroupHeader('DK-MSG-42', 'DK Corp');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', null, 'Origin');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'urn:iso:std:iso:20022:tech:xsd:' . self::SCT_PAIN);

        // DK-forbidden elements: absent
        $this->assertSame(0, $xpath->query('//ns:GrpHdr/ns:CtrlSum')->length);
        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:DbtrAgt')->length);
        $this->assertSame(0, $xpath->query('//ns:CdtTrfTxInf/ns:CdtrAgt')->length);

        // Required elements still present
        $this->assertSame(1, $xpath->query('//ns:GrpHdr/ns:MsgId')->length);
        $this->assertSame(1, $xpath->query('//ns:GrpHdr/ns:NbOfTxs')->length);
        $this->assertSame(1, $xpath->query('//ns:PmtInf/ns:CtrlSum')->length);
    }

    // ---------- Facade passthrough ------------------------------------------

    public function testFacadeExposesOmitGroupHeaderControlSum(): void
    {
        $facade = TransferFileFacadeFactory::createCustomerCredit('MSG', 'Me', self::SCT_PAIN);
        $facade->setOmitGroupHeaderControlSum(true);
        $facade->addPaymentInfo('p', [
            'id' => 'p',
            'debtorName' => 'Me',
            'debtorAccountIBAN' => 'DE88500105173441451911',
            'debtorAgentBIC' => 'DEUTDEFF',
        ]);
        $facade->addTransfer('p', [
            'amount' => 100,
            'creditorIban' => 'DE40500105174181777145',
            'creditorBic' => 'DEUTDEFF',
            'creditorName' => 'Bob',
            'remittanceInformation' => 'x',
        ]);

        $xml = $facade->asXML();
        $this->assertStringNotContainsString('<CtrlSum>', substr($xml, 0, strpos($xml, '<PmtInf>')));
    }

    public function testFacadeExposesOmitAgentElementIfBicMissing(): void
    {
        $facade = TransferFileFacadeFactory::createCustomerCredit('MSG', 'Me', self::SCT_PAIN);
        $facade->setOmitAgentElementIfBicMissing(true);
        $facade->addPaymentInfo('p', [
            'id' => 'p',
            'debtorName' => 'Me',
            'debtorAccountIBAN' => 'DE88500105173441451911',
            // no debtorAgentBIC
        ]);
        $facade->addTransfer('p', [
            'amount' => 100,
            'creditorIban' => 'DE40500105174181777145',
            'creditorName' => 'Bob',
            // no creditorBic
            'remittanceInformation' => 'x',
        ]);

        $xml = $facade->asXML();
        $this->assertStringNotContainsString('<DbtrAgt>', $xml);
        $this->assertStringNotContainsString('<CdtrAgt>', $xml);
    }

    // ---------- Helpers -----------------------------------------------------

    /**
     * @param callable(CustomerCreditTransferDomBuilder): void $configureBuilder
     */
    private function renderSct(
        callable $configureBuilder,
        ?string $transferBic = 'DEUTDEFF',
        ?string $originBic = 'DEUTDEFFXXX'
    ): \DOMXPath {
        $builder = new CustomerCreditTransferDomBuilder(self::SCT_PAIN);
        $configureBuilder($builder);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', $originBic, 'Origin');
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        if ($transferBic !== null) {
            $transfer->setBic($transferBic);
        }
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $this->xpath($builder->asXml(), self::SCT_PAIN);
    }

    /**
     * @param callable(CustomerDirectDebitTransferDomBuilder): void $configureBuilder
     */
    private function renderSdd(
        callable $configureBuilder,
        ?string $transferBic = 'DEUTDEFF',
        ?string $originBic = 'DEUTDEFFXXX'
    ): \DOMXPath {
        $builder = new CustomerDirectDebitTransferDomBuilder(self::SDD_PAIN);
        $configureBuilder($builder);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerDirectDebitTransferFile($groupHeader);
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', $originBic, 'Origin');
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
