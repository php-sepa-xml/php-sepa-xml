<?php

/**
 * SEPA file generator.
 *
 * @copyright © Digitick <www.digitick.net> 2012-2013
 * @copyright © Blage <www.blage.net> 2013
 * @license GNU Lesser General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Lesser Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Digitick\Sepa\Tests\Functional;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\Tests\XPathAssertions;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\Util\MessageFormat;
use PHPUnit\Framework\TestCase;

/**
 * Various schema validation tests.
 */
class CustomerCreditValidationTest extends TestCase
{
    use XPathAssertions;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     *
     * @dataProvider provideSchema
     */
    public function testSanity(string $schema): void
    {
        $this->dom->load(XML_DIR . $schema . '.xml');
        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');

        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and one transaction.
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentSingleTrans(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(['TRANSFER']);
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and several transactions.
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentMultiTrans(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $transfer = new CustomerCreditTransferInformation(500000, 'BE30001216371411', 'GHI Semiconductors');
        $transfer->setBic('DDDDBEBB');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test that a transferfile without Payments throws understandable exception
     *
     * @dataProvider provideSchema
     */
    public function testInvalidTransferFileThrowsException(string $schema): void
    {
        $this->expectException(InvalidTransferFileConfiguration::class);

        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
    }

    /**
     * Test correct calulation of controlsum and transaction count
     *
     * @dataProvider provideSchema
     */
    public function testControlSumAndTransactionCount(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $transfer = new CustomerCreditTransferInformation(500000, 'BE30001216371411', 'GHI Semiconductors');
        $transfer->setBic('DDDDBEBB');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        $this->assertEquals(2, self::xpathText($xpathDoc, '//sepa:NbOfTxs'));
        $this->assertEquals('5000.02', self::xpathText($xpathDoc, '//sepa:CtrlSum'));
    }

    /**
     * Test the payment informations in the xml
     *
     * @dataProvider provideSchema
     */
    public function testPaymentMetaData(string $schema): void
    {
        $messageFormat = new MessageFormat($schema);

        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setDueDate(new \DateTime('20.11.2012'));
        $payment->setInstructionPriority('NORM');

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        // Date is correctly coded
        if ($messageFormat->isCreditTransfer() && $messageFormat->getVariant() == 1 && $messageFormat->getVersion() >= 8) {
            $this->assertEquals('2012-11-20', self::xpathText($xpathDoc, '//sepa:ReqdExctnDt/sepa:Dt'));
        } else {
            $this->assertEquals('2012-11-20', self::xpathText($xpathDoc, '//sepa:ReqdExctnDt'));
        }

        //Payment method is set
        $this->assertEquals('TRF', self::xpathText($xpathDoc, '//sepa:PmtMtd'));
        //Originating IBAN
        $this->assertEquals('FR1420041010050500013M02606', self::xpathText($xpathDoc, '//sepa:DbtrAcct/sepa:Id/sepa:IBAN'));
        //Originating BIC
        if ($messageFormat->isCreditTransfer() && $messageFormat->getVariant() == '1' && $messageFormat->getVersion() >= 4) {
            $this->assertEquals('PSSTFRPPMON', self::xpathText($xpathDoc, '//sepa:DbtrAgt/sepa:FinInstnId/sepa:BICFI'));
        } else {
            $this->assertEquals('PSSTFRPPMON', self::xpathText($xpathDoc, '//sepa:DbtrAgt/sepa:FinInstnId/sepa:BIC'));
        }

        //Originating Name
        $this->assertEquals('My Corp', self::xpathText($xpathDoc, '//sepa:Dbtr/sepa:Nm'));
    }

    /**
     * Test the payment informations in the xml
     *
     * @param array{string|null, string|string[]} $address
     *
     * @dataProvider provideAddressTests
     */
    public function testCreditorAddressGeneration(array $address): void
    {
        $schema = "pain.001.001.03"; // Addresses are only supported using this pain format.

        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setDueDate(new \DateTime('20.11.2012'));

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');

        $country = $address[0];
        $addressLines = $address[1];
        if (null !== $country) {
            $transfer->setCountry($country);
        }
        $transfer->setPostalAddress($addressLines);
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        // Creditor country is correctly added:
        if (null === $country) {
            // Without a country, no <Ctry> node may be emitted at all.
            $this->assertNull(self::xpathQuery($xpathDoc, '//sepa:Cdtr/sepa:PstlAdr/sepa:Ctry')->item(0));
        } else {
            $this->assertEquals($country, self::xpathText($xpathDoc, '//sepa:Cdtr/sepa:PstlAdr/sepa:Ctry'));
        }

        // $addressLines could be string instead of array. Ensure array for easier testing.
        if (!is_array($addressLines)) {
            $addressLines = [$addressLines];
        }

        // check that all address lines exist and match the expected inputs.
        for ($index = 0; $index < count($addressLines); $index++) {
            $this->assertEquals($addressLines[$index], self::xpathText($xpathDoc, '//sepa:Cdtr/sepa:PstlAdr/sepa:AdrLine', null, $index));
        }
    }

    /**
     * Test a transfer file with several payments, several transactions each.
     *
     * @dataProvider provideSchema
     */
    public function testMultiPaymentMultiTrans(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $payment1 = new PaymentInformation('account settlement', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer1 = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer1->setBic('OKOYFIHH');
        $transfer1->setRemittanceInformation('Transaction description');
        $payment1->addTransfer($transfer1);

        $transfer2 = new CustomerCreditTransferInformation(500000, 'BE30001216371411', 'GHI Semiconductors');
        $transfer2->setBic('DDDDBEBB');
        $transfer2->setRemittanceInformation('Transaction description');
        $payment1->addTransfer($transfer2);

        $sepaFile->addPaymentInformation($payment1);

        $payment2 = new PaymentInformation('account settlement', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer1 = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer1->setBic('OKOYFIHH');
        $transfer1->setRemittanceInformation('Transaction description');
        $payment2->addTransfer($transfer1);

        $transfer2 = new CustomerCreditTransferInformation(500000, 'BE30001216371411', 'GHI Semiconductors');
        $transfer2->setBic('DDDDBEBB');
        $transfer2->setRemittanceInformation('Transaction description');
        $payment2->addTransfer($transfer2);

        $sepaFile->addPaymentInformation($payment2);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();

        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        $this->assertEquals(4, self::xpathText($xpathDoc, '//sepa:NbOfTxs'));
        $this->assertEquals(2, self::xpathText($xpathDoc, '//sepa:NbOfTxs', null, 1));
        $this->assertEquals(2, self::xpathText($xpathDoc, '//sepa:NbOfTxs', null, 2));
        $this->assertEquals('10000.04', self::xpathText($xpathDoc, '//sepa:CtrlSum'));
    }

    /**
     * Test the payment informations in the xml
     *
     * @dataProvider provideSchema
     */
    public function testUmlautConversion(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Only A-Z without äöüßÄÖÜ initiatingPartyName');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'Only A-Z without äöüßÄÖÜ debtorName');
        $payment->setDueDate(new \DateTime('20.11.2012'));
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('Only A-Z without äöüßÄÖÜ creditorSchemeId');

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Only A-Z without äöüßÄÖÜ creditorName');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Only A-Z without äöüßÄÖÜ remittanceInformation');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);
        // Date is correctly coded
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe initiatingPartyName', self::xpathText($xpathDoc, '//sepa:InitgPty/sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorName', self::xpathText($xpathDoc, '//sepa:Cdtr/sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorName', self::xpathText($xpathDoc, '//sepa:EndToEndId'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe debtorName', self::xpathText($xpathDoc, '//sepa:Dbtr/sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe remittanceInformation', self::xpathText($xpathDoc, '//sepa:Ustrd'));
    }

    /**
     * Test a transfer file using other date format.
     * There are different representations possible for IsoDateTime:
     * http://www.swift.com/assets/corporates/documents/business_areas/ebam_standards_mx/business/x68910b9357eed3cf49770d42b07d70f1.htm
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentOtherCreationDateTimeFormat(string $schema): void
    {
        $dateTimeFormat = 'Y-m-d\TH:i:s.000P';

        $dateTime = new \DateTime();
        $groupHeader = new GroupHeader('transferID', 'Me');
        $groupHeader->setCreationDateTimeFormat($dateTimeFormat);
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(['TRANSFER']);
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        $this->assertEquals($dateTime->format($dateTimeFormat), self::xpathText($xpathDoc, '//sepa:CreDtTm'), 'CreDtTm should have the specified format: ' . $dateTimeFormat);
    }

    /**
     * Test a transfer file with one payment without remittance information
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentSingleTransWithoutRemitttanceInformation(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(['TRANSFER']);
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment without remittance information
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentSingleTransWithStructuredCreditorReference(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setCreditorReference('RF81123453');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(['TRANSFER']);
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        $this->assertEquals(0, self::xpathQuery($xpathDoc, '//sepa:Ustrd')->length, 'RmtInf should not contain Ustrd when Strd is present.');

        $this->assertEquals(1, self::xpathQuery($xpathDoc, '//sepa:Strd')->length, 'Missing structured creditor reference Strd.');

        $this->assertEquals('RF81123453', self::xpathText($xpathDoc, '//sepa:Strd/sepa:CdtrRefInf/sepa:Ref'));
    }

    /**
     * Test a transfer file with one payment and one transaction with purpose code.
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentSingleTransactionWithPurposeCode(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setPurposeCode('SALA');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(['TRANSFER']);
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);
        $this->assertEquals('SALA', self::xpathText($xpathDoc, '//sepa:Purp/sepa:Cd'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideSchema(): iterable
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
            'pain.001.002.03' => ['pain.001.002.03'],
            'pain.001.003.03' => ['pain.001.003.03'],
        ];
    }

    //@TODO: Add more address fields, test with and without address line
    /**
     * @return iterable<string, array{array{string|null, string|string[]}}>
     */
    public static function provideAddressTests(): iterable
    {
        return [
            'country with multiple address lines' => [['CH', ['Teststreet 1', '21345 Somewhere']]],
            'country with single address line' => [['DE', ['Teststreet 2']]],
            'country with address line as string' => [['NL', '21456 Rightthere']],
            'country without address lines' => [['NL', []]],
            'address lines without country' => [[null, ['Teststreet 3', '21345 Somewhere']]],
        ];
    }
}
