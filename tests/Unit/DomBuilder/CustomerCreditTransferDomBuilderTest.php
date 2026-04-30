<?php

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\Util\MessageFormat;
use PHPUnit\Framework\TestCase;

class CustomerCreditTransferDomBuilderTest extends TestCase
{
    /**
     * @dataProvider painProvider
     */
    public function testSchemaValidationAcrossPainVersions(string $painFormat): void
    {
        $builder = $this->buildBasic($painFormat);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        $this->assertTrue($doc->schemaValidate(XSD_DIR . $painFormat . '.xsd'));
    }

    /**
     * @dataProvider painProviderV9Plus
     */
    public function testWithAddress(string $painFormat): void
    {
        $messageFormat = new MessageFormat($painFormat);

        $builder = new CustomerCreditTransferDomBuilder($painFormat);

        $groupHeader = new GroupHeader('TEST_MSG', 'Test Company Inc.');
        $groupHeader->setInitiatingPartyId('DE67ZZZ00000123456');

        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Test Company Inc.');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(1000, 'DE40500105174181777145', 'Max Musterman');
        $transfer->setCountry('DE');
        $transfer->setPostCode('60431');
        $transfer->setTownName('Frankfurt am Main');
        $transfer->setStreetName('Wilhelm-Epstein-Str.');
        $transfer->setBuildingNumber('14');
        $transfer->setFloorNumber('12');

        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($payment);
        $builder->visitTransferInformation($transfer);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        $this->assertTrue($doc->schemaValidate(XSD_DIR . $painFormat . '.xsd'));

        $xpath = $this->xpath($doc, $painFormat);
        $postalAddressNode = $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:Cdtr/ns:PstlAdr')->item(0);

        $this->assertNotNull($postalAddressNode);
        $this->assertSame('DE', $xpath->evaluate('./ns:Ctry', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('60431', $xpath->evaluate('./ns:PstCd', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Frankfurt am Main', $xpath->evaluate('./ns:TwnNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Wilhelm-Epstein-Str.', $xpath->evaluate('./ns:StrtNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('14', $xpath->evaluate('./ns:BldgNb', $postalAddressNode)->item(0)->textContent);

        // Flr only valid for variant 1, version >= 9.
        if ($messageFormat->getVariant() === 1 && $messageFormat->getVersion() >= 9) {
            $this->assertSame('12', $xpath->evaluate('./ns:Flr', $postalAddressNode)->item(0)->textContent);
        } else {
            $this->assertSame(0, $xpath->query('./ns:Flr', $postalAddressNode)->length);
        }
    }

    public function testReqdExctnDtIsStructuredForVariant1V8Plus(): void
    {
        // Variant 1, version >= 8: ReqdExctnDt must wrap the date in <Dt>.
        $builder = $this->buildBasic('pain.001.001.09');
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        $xpath = $this->xpath($doc, 'pain.001.001.09');

        $this->assertSame(1, $xpath->query('//ns:PmtInf/ns:ReqdExctnDt/ns:Dt')->length);
    }

    public function testReqdExctnDtIsFlatForOlderVariant1Versions(): void
    {
        // Before version 8: ReqdExctnDt carries the date directly.
        $builder = $this->buildBasic('pain.001.001.03');
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        $xpath = $this->xpath($doc, 'pain.001.001.03');

        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:ReqdExctnDt/ns:Dt')->length);
        $this->assertNotEmpty($xpath->evaluate('string(//ns:PmtInf/ns:ReqdExctnDt)'));
    }

    public function testOrgIdIsReplacedWhenInitiatingPartyIdSet(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.09');

        $groupHeader = new GroupHeader('MSG', 'Initiator');
        $groupHeader->setInitiatingPartyId('DE67ZZZ00000123456');
        $groupHeader->setInitiatingPartyIdentificationScheme('BANK');

        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $transferFile->addPaymentInformation($payment);
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12345', 'Bob'));

        $transferFile->accept($builder);

        $xpath = $this->xpath($this->asDoc($builder), 'pain.001.001.09');

        // After replacement the InitgPty/Id must contain OrgId/Othr/Id rather
        // than the default flat Id text node.
        $this->assertSame(
            'DE67ZZZ00000123456',
            $xpath->evaluate('string(//ns:GrpHdr/ns:InitgPty/ns:Id/ns:OrgId/ns:Othr/ns:Id)')
        );
        $this->assertSame(
            'BANK',
            $xpath->evaluate('string(//ns:GrpHdr/ns:InitgPty/ns:Id/ns:OrgId/ns:Othr/ns:SchmeNm/ns:Cd)')
        );
    }

    public function testCategoryPurposeCodeIsRendered(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.09');

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $payment->setCategoryPurposeCode('SALA');
        $transferFile->addPaymentInformation($payment);
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12345', 'Bob'));

        $transferFile->accept($builder);

        $xpath = $this->xpath($this->asDoc($builder), 'pain.001.001.09');

        $this->assertSame('SALA', $xpath->evaluate('string(//ns:PmtInf/ns:PmtTpInf/ns:CtgyPurp/ns:Cd)'));
    }

    public function testLocalInstrumentCodeRendered(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.09');

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $payment->setLocalInstrumentCode('CORE');
        $transferFile->addPaymentInformation($payment);
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12345', 'Bob'));

        $transferFile->accept($builder);

        $xpath = $this->xpath($this->asDoc($builder), 'pain.001.001.09');

        $this->assertSame('CORE', $xpath->evaluate('string(//ns:PmtInf/ns:PmtTpInf/ns:LclInstrm/ns:Cd)'));
        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:PmtTpInf/ns:LclInstrm/ns:Prtry')->length);
    }

    public function testLocalInstrumentProprietaryRenderedWhenCodeAbsent(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.09');

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $payment->setLocalInstrumentProprietary('LOCAL-STUFF');
        $transferFile->addPaymentInformation($payment);
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12345', 'Bob'));

        $transferFile->accept($builder);

        $xpath = $this->xpath($this->asDoc($builder), 'pain.001.001.09');

        $this->assertSame('LOCAL-STUFF', $xpath->evaluate('string(//ns:PmtInf/ns:PmtTpInf/ns:LclInstrm/ns:Prtry)'));
        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:PmtTpInf/ns:LclInstrm/ns:Cd')->length);
    }

    public function testInstructionPriorityIsRenderedForVariant1(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.09');

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $payment->setInstructionPriority('HIGH');
        $transferFile->addPaymentInformation($payment);
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12345', 'Bob'));

        $transferFile->accept($builder);

        $xpath = $this->xpath($this->asDoc($builder), 'pain.001.001.09');

        $this->assertSame('HIGH', $xpath->evaluate('string(//ns:PmtInf/ns:PmtTpInf/ns:InstrPrty)'));
    }

    /**
     * @dataProvider stpVariantProvider
     */
    public function testVariant2And3SuppressStructuredAddressFields(string $painFormat): void
    {
        // pain.001.002.03 (STP) and pain.001.003.03 (EU STP) only allow
        // Ctry and AdrLine inside PstlAdr — the structured fields must not
        // be emitted even if set on the TransferInformation, otherwise the
        // XSD rejects the document.
        $builder = new CustomerCreditTransferDomBuilder($painFormat);

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $transferFile->addPaymentInformation($payment);

        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setBic('DEUTDEFF');
        $transfer->setCountry('DE');
        $transfer->setPostalAddress('Some Street 123, 12345 Berlin');
        // Deliberately populate structured fields — builder must suppress them
        $transfer->setStreetName('Wilhelm-Epstein-Str.');
        $transfer->setBuildingNumber('14');
        $transfer->setPostCode('60431');
        $transfer->setTownName('Frankfurt');
        $transfer->setFloorNumber('2');
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        $doc = $this->asDoc($builder);
        $this->assertTrue(
            $doc->schemaValidate(XSD_DIR . $painFormat . '.xsd'),
            'Emitted XML must validate against the variant 2/3 schema'
        );

        $xpath = $this->xpath($doc, $painFormat);
        $postalAddressNode = $xpath->evaluate('//ns:CdtTrfTxInf/ns:Cdtr/ns:PstlAdr')->item(0);
        $this->assertNotNull($postalAddressNode);

        // Allowed: Ctry + AdrLine
        $this->assertSame('DE', $xpath->evaluate('string(./ns:Ctry)', $postalAddressNode));
        $this->assertSame(
            'Some Street 123, 12345 Berlin',
            $xpath->evaluate('string(./ns:AdrLine)', $postalAddressNode)
        );

        // Suppressed structured fields
        $this->assertSame(0, $xpath->query('./ns:StrtNm', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:BldgNb', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:PstCd', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:TwnNm', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:Flr', $postalAddressNode)->length);
    }

    public static function stpVariantProvider(): iterable
    {
        return [
            'pain.001.002.03 (STP)' => ['pain.001.002.03'],
            'pain.001.003.03 (EU STP)' => ['pain.001.003.03'],
        ];
    }

    public function testInstructionPriorityIsSuppressedForNonVariant1(): void
    {
        // Variant 2 (pain.001.002.03) does not carry InstrPrty under PmtTpInf.
        $builder = new CustomerCreditTransferDomBuilder('pain.001.002.03');

        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $payment->setInstructionPriority('HIGH');
        $transferFile->addPaymentInformation($payment);
        $payment->addTransfer(new CustomerCreditTransferInformation(100, 'DE12345', 'Bob'));

        $transferFile->accept($builder);

        $xpath = $this->xpath($this->asDoc($builder), 'pain.001.002.03');

        $this->assertSame(0, $xpath->query('//ns:PmtInf/ns:PmtTpInf/ns:InstrPrty')->length);
    }

    public static function painProvider(): iterable
    {
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
            'pain.001.001.04' => ['pain.001.001.04'],
            'pain.001.001.05' => ['pain.001.001.05'],
            'pain.001.001.06' => ['pain.001.001.06'],
            'pain.001.001.07' => ['pain.001.001.07'],
            'pain.001.001.08' => ['pain.001.001.08'],
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.12' => ['pain.001.001.12'],
        ];
    }

    public static function painProviderV9Plus(): iterable
    {
        // Versions where PstlAdr with structured fields is meaningful.
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
            'pain.001.001.04' => ['pain.001.001.04'],
            'pain.001.001.05' => ['pain.001.001.05'],
            'pain.001.001.06' => ['pain.001.001.06'],
            'pain.001.001.07' => ['pain.001.001.07'],
            'pain.001.001.08' => ['pain.001.001.08'],
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.12' => ['pain.001.001.12'],
        ];
    }

    private function buildBasic(string $painFormat): CustomerCreditTransferDomBuilder
    {
        $builder = new CustomerCreditTransferDomBuilder($painFormat);
        $groupHeader = new GroupHeader('MSG', 'Init');
        $transferFile = new CustomerCreditTransferFile($groupHeader);
        $payment = $this->newValidPayment();
        $transferFile->addPaymentInformation($payment);
        $transfer = new CustomerCreditTransferInformation(100, 'DE12345', 'Bob');
        $transfer->setBic('DEUTDEFF');
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        return $builder;
    }

    private function newValidPayment(): PaymentInformation
    {
        return new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
    }

    private function asDoc(CustomerCreditTransferDomBuilder $builder): \DOMDocument
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        return $doc;
    }

    private function xpath(\DOMDocument $doc, string $painFormat): \DOMXPath
    {
        $xp = new \DOMXPath($doc);
        $xp->registerNamespace('ns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));

        return $xp;
    }
}
