<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\Util\MessageFormat;
use PHPUnit\Framework\TestCase;

class CustomerDirectDebitTransferDomBuilderTest extends TestCase
{
    /**
     * Test the XML generation of a direct debit transfer with structured address
     * data for pain.008.001.02
     * @dataProvider painProvider
     */
    public function testWithAddress(string $painFormat): void
    {
        $messageFormat = new MessageFormat($painFormat);

        $groupHeader = new GroupHeader('TEST_DEBTOR_ADRESS', 'Test Company Inc.');
        $groupHeader->setInitiatingPartyId('DE67ZZZ00000123456');

        $paymentInformation = new \Digitick\Sepa\PaymentInformation('RAND001', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Test Company Inc.');
        $paymentInformation->setCreditorId('DE67ZZZ00000123456');
        $paymentInformation->setSequenceType('FRST');

        $transferFile = new \Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile($groupHeader);
        $transferFile->addPaymentInformation($paymentInformation);

        $transactionInformation = new \Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation(1000, 'DE40500105174181777145', 'Max Musterman');
        $transactionInformation->setMandateId('TEST-MANDATE-1');
        $transactionInformation->setMandateSignDate(new \DateTime('2022-05-15'));
        $transactionInformation->setCountry('DE');
        $transactionInformation->setPostCode('60431');
        $transactionInformation->setTownName('Frankfurt am Main');
        $transactionInformation->setStreetName('Wilhelm-Epstein-Str.');
        $transactionInformation->setBuildingNumber('14');
        $transactionInformation->setFloorNumber('12');
        $transactionInformation->setUltimateDebtorName('Maximilian Musterman');

        $builder = new CustomerDirectDebitTransferDomBuilder($painFormat);
        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($paymentInformation);
        $builder->visitTransferInformation($transactionInformation);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        // Test xml schema validation
        $validated = $doc->schemaValidate(XSD_DIR . $painFormat .'.xsd');
        $this->assertTrue($validated);

        // Test contents
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', "urn:iso:std:iso:20022:tech:xsd:{$painFormat}");
        $postalAddressNode = $xpath->evaluate('/ns:Document/ns:CstmrDrctDbtInitn/ns:PmtInf/ns:DrctDbtTxInf/ns:Dbtr/ns:PstlAdr')->item(0);

        $this->assertNull($xpath->evaluate('./ns:AdrLine', $postalAddressNode)->item(0));
        $this->assertSame('DE', $xpath->evaluate('./ns:Ctry', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('60431', $xpath->evaluate('./ns:PstCd', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Frankfurt am Main', $xpath->evaluate('./ns:TwnNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Wilhelm-Epstein-Str.', $xpath->evaluate('./ns:StrtNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('14', $xpath->evaluate('./ns:BldgNb', $postalAddressNode)->item(0)->textContent);
        if ($messageFormat->getVariant() == 1 && $messageFormat->getVersion() >= 8) {
            $this->assertSame('12', $xpath->evaluate('./ns:Flr', $postalAddressNode)->item(0)->textContent);
        }

        // Check Ultimate Debtor name
        $transactionInfoNode = $xpath->evaluate('/ns:Document/ns:CstmrDrctDbtInitn/ns:PmtInf/ns:DrctDbtTxInf')->item(0);
        $this->assertSame('Maximilian Musterman', $xpath->evaluate('./ns:UltmtDbtr/ns:Nm', $transactionInfoNode)->item(0)->textContent);
    }

    public static function painProvider(): iterable
    {
        return [
            'pain.008.001.02' => ['pain.008.001.02'],
            'pain.008.001.03' => ['pain.008.001.03'],
            'pain.008.001.04' => ['pain.008.001.04'],
            'pain.008.001.05' => ['pain.008.001.05'],
            'pain.008.001.06' => ['pain.008.001.06'],
            'pain.008.001.07' => ['pain.008.001.07'],
            'pain.008.001.08' => ['pain.008.001.08'],
            'pain.008.001.09' => ['pain.008.001.09'],
            'pain.008.001.10' => ['pain.008.001.10'],
            'pain.008.001.11' => ['pain.008.001.11'],
        ];
    }

    /**
     * Test the XML generation of a direct debit transfer with BIC
     * for pain.008.001.10
     */
    public function testWithBicfiForPain00800110(): void
    {
        $groupHeader = new GroupHeader('TEST_DEBTOR_BIC', 'Test Company Inc.');
        $groupHeader->setInitiatingPartyId('DE67ZZZ00000123456');

        $paymentInformation = new \Digitick\Sepa\PaymentInformation('RAND001', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Test Company Inc.');
        $paymentInformation->setCreditorId('DE67ZZZ00000123456');
        $paymentInformation->setSequenceType('FRST');

        $transferFile = new \Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile($groupHeader);
        $transferFile->addPaymentInformation($paymentInformation);

        $transactionInformation = new \Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation(1000, 'DE40500105174181777145', 'Max Musterman');
        $transactionInformation->setMandateId('TEST-MANDATE-1');
        $transactionInformation->setMandateSignDate(new \DateTime('2022-05-15'));
        $transactionInformation->setBic('INGDDEFFXXX');

        $builder = new CustomerDirectDebitTransferDomBuilder('pain.008.001.10');
        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($paymentInformation);
        $builder->visitTransferInformation($transactionInformation);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());

        // Test xml schema validation
        $validated = $doc->schemaValidate(XSD_DIR . 'pain.008.001.10.xsd');
        $this->assertTrue($validated);

        // Test contents

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.10');

        $finInstnIdNode = $xpath->evaluate('/ns:Document/ns:CstmrDrctDbtInitn/ns:PmtInf/ns:DrctDbtTxInf/ns:DbtrAgt/ns:FinInstnId')->item(0);

        $this->assertSame('INGDDEFFXXX', $xpath->evaluate('./ns:BICFI', $finInstnIdNode)->item(0)->textContent);
    }

    public function testAmendedDebtorAccountEmitsSmndaOrgnlDbtrAcct(): void
    {
        $xpath = $this->renderWithAmendments(function ($transfer): void {
            $transfer->setAmendedDebtorAccount(true);
        });

        $this->assertSame(
            'true',
            $xpath->evaluate('string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInd)')
        );
        $this->assertSame(
            'SMNDA',
            $xpath->evaluate(
                'string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlDbtrAcct/ns:Id/ns:Othr/ns:Id)'
            )
        );
        $this->assertSame(
            0,
            $xpath->query('//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlMndtId')->length,
            'OrgnlMndtId must be absent when only the debtor account is amended'
        );
    }

    public function testOriginalMandateIdEmitsOrgnlMndtId(): void
    {
        $xpath = $this->renderWithAmendments(function ($transfer): void {
            $transfer->setOriginalMandateId('OLD-MANDATE-42');
        });

        $this->assertSame(
            'true',
            $xpath->evaluate('string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInd)')
        );
        $this->assertSame(
            'OLD-MANDATE-42',
            $xpath->evaluate('string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlMndtId)')
        );
        $this->assertSame(
            0,
            $xpath->query('//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlDbtrAcct')->length,
            'OrgnlDbtrAcct must be absent when only the mandate id is amended'
        );
    }

    public function testOriginalDebtorIbanAlsoTriggersSmndaOrgnlDbtrAcct(): void
    {
        // The builder emits the SMNDA sentinel when either amendedDebtorAccount
        // or originalDebtorIban is set — verify the latter path.
        $xpath = $this->renderWithAmendments(function ($transfer): void {
            $transfer->setOriginalDebtorIban('DE11520513735120710131');
        });

        $this->assertSame(
            'SMNDA',
            $xpath->evaluate(
                'string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlDbtrAcct/ns:Id/ns:Othr/ns:Id)'
            )
        );
    }

    public function testBothAmendmentsEmitBothNodes(): void
    {
        $xpath = $this->renderWithAmendments(function ($transfer): void {
            $transfer->setAmendedDebtorAccount(true);
            $transfer->setOriginalMandateId('OLD-MANDATE-42');
        });

        $this->assertSame(
            'SMNDA',
            $xpath->evaluate(
                'string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlDbtrAcct/ns:Id/ns:Othr/ns:Id)'
            )
        );
        $this->assertSame(
            'OLD-MANDATE-42',
            $xpath->evaluate('string(//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls/ns:OrgnlMndtId)')
        );
    }

    /**
     * @dataProvider sddStpVariantProvider
     */
    public function testVariant2And3SuppressStructuredAddressFields(string $painFormat): void
    {
        // pain.008.002.02 and pain.008.003.02 only allow Ctry and AdrLine
        // inside PstlAdr — structured fields must not be emitted.
        $builder = new \Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder($painFormat);

        $groupHeader = new \Digitick\Sepa\GroupHeader('MSG', 'Init');
        $transferFile = new \Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile($groupHeader);
        $payment = new \Digitick\Sepa\PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $payment->setSequenceType(\Digitick\Sepa\PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('DE67ZZZ00000123456');
        $transferFile->addPaymentInformation($payment);

        $transfer = new \Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation(
            100,
            'DE40500105174181777145',
            'Bob'
        );
        $transfer->setBic('DEUTDEFF');
        $transfer->setMandateId('M1');
        $transfer->setMandateSignDate(new \DateTimeImmutable('2022-05-15'));
        $transfer->setCountry('DE');
        $transfer->setPostalAddress('Some Street 123, 12345 Berlin');
        // These must be suppressed by the builder for variants 2 and 3
        $transfer->setStreetName('Wilhelm-Epstein-Str.');
        $transfer->setBuildingNumber('14');
        $transfer->setPostCode('60431');
        $transfer->setTownName('Frankfurt');
        $transfer->setFloorNumber('2');
        $payment->addTransfer($transfer);

        $transferFile->accept($builder);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());
        $this->assertTrue(
            $doc->schemaValidate(XSD_DIR . $painFormat . '.xsd'),
            'Emitted XML must validate against the variant 2/3 schema'
        );

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));

        $postalAddressNode = $xpath->evaluate('//ns:DrctDbtTxInf/ns:Dbtr/ns:PstlAdr')->item(0);
        $this->assertNotNull($postalAddressNode);

        $this->assertSame('DE', $xpath->evaluate('string(./ns:Ctry)', $postalAddressNode));
        $this->assertSame(
            'Some Street 123, 12345 Berlin',
            $xpath->evaluate('string(./ns:AdrLine)', $postalAddressNode)
        );
        $this->assertSame(0, $xpath->query('./ns:StrtNm', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:BldgNb', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:PstCd', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:TwnNm', $postalAddressNode)->length);
        $this->assertSame(0, $xpath->query('./ns:Flr', $postalAddressNode)->length);
    }

    public static function sddStpVariantProvider(): iterable
    {
        return [
            'pain.008.002.02 (STP)' => ['pain.008.002.02'],
            'pain.008.003.02 (EU STP)' => ['pain.008.003.02'],
        ];
    }

    public function testNoAmendmentsSuppressesAmdmntInd(): void
    {
        $xpath = $this->renderWithAmendments(function ($transfer): void {
            // deliberately set no amendments
        });

        $this->assertSame(
            0,
            $xpath->query('//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInd')->length
        );
        $this->assertSame(
            0,
            $xpath->query('//ns:DrctDbtTx/ns:MndtRltdInf/ns:AmdmntInfDtls')->length
        );
    }

    private function renderWithAmendments(callable $configureTransfer): \DOMXPath
    {
        $painFormat = 'pain.008.001.02';
        $builder = new \Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder($painFormat);

        $groupHeader = new \Digitick\Sepa\GroupHeader('MSG', 'Init');
        $transferFile = new \Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile($groupHeader);
        $payment = new \Digitick\Sepa\PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $payment->setSequenceType(\Digitick\Sepa\PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('DE67ZZZ00000123456');
        $transferFile->addPaymentInformation($payment);

        $transfer = new \Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation(
            100,
            'DE40500105174181777145',
            'Bob'
        );
        $transfer->setBic('DEUTDEFF');
        $transfer->setMandateId('M1');
        $transfer->setMandateSignDate(new \DateTimeImmutable('2022-05-15'));
        $configureTransfer($transfer);

        $payment->addTransfer($transfer);
        $transferFile->accept($builder);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($builder->asXml());
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));

        return $xpath;
    }
}
