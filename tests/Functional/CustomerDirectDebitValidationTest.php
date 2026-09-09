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

use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\Tests\XPathAssertions;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use PHPUnit\Framework\TestCase;

class CustomerDirectDebitValidationTest extends TestCase
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
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setMandateSignDate(new \DateTime('16.08.2013'));
        $transfer->setMandateId('ABCDE');
        $transfer->setRemittanceInformation('Transaction Description');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setDueDate(new \DateTime('22.08.2013'));
        $payment->setInstructionPriority('NORM');
        $payment->setCreditorId('DE21WVM1234567890');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and one transaction.
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentSingleTransWithStructuredCreditorReference(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setMandateSignDate(new \DateTime('16.08.2013'));
        $transfer->setMandateId('ABCDE');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setCreditorReference('RF81123453');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setDueDate(new \DateTime('22.08.2013'));
        $payment->setCreditorId('DE21WVM1234567890');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $schema . '.xsd');
        $this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:'.$schema);

        $this->assertEquals(0, self::xpathQuery($xpathDoc, '//sepa:Ustrd')->length, 'RmtInf should not contain Ustrd when Strd is present.');

        $this->assertEquals(1, self::xpathQuery($xpathDoc, '//sepa:Strd')->length, 'Missing structured creditor reference Strd.');

        $this->assertEquals('RF81123453', self::xpathText($xpathDoc, '//sepa:Strd/sepa:CdtrRefInf/sepa:Ref'));
    }

    /**
     * @dataProvider provideSchema
     */
    public function testValidationFailureSeqType(string $schema): void
    {
        $this->expectException(InvalidTransferFileConfiguration::class);
        $this->expectExceptionMessage('Payment must contain a SequenceType');

        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation(2, 'FI1350001540000056', 'Their Corp');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
    }

    /**
     * @dataProvider provideSchema
     */
    public function testValidationFailureCreditorId(string $schema): void
    {
        $this->expectException(InvalidTransferFileConfiguration::class);
        $this->expectExceptionMessage('Payment must contain a CreditorSchemeId');

        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation(2, 'FI1350001540000056', 'Their Corp');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
    }

    /**
     * Test the payment informations in the xml
     *
     * @dataProvider provideSchema
     */
    public function testUmlautConversion(string $schema): void
    {
        $groupHeader = new GroupHeader('transferID', 'Only A-Z without äöüßÄÖÜ initiatingPartyName');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'Only A-Z without äöüßÄÖÜ creditorName');
        $payment->setDueDate(new \DateTime('20.11.2012'));
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('Only A-Z without äöüßÄÖÜ creditorSchemeId');

        $transfer = new CustomerDirectDebitTransferInformation(2, 'FI1350001540000056', 'Only A-Z without äöüßÄÖÜ debtorName');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Only A-Z without äöüßÄÖÜ remittanceInformation');
        $transfer->setMandateSignDate(new \DateTime());
        $transfer->setMandateId('Only A-Z without äöüßÄÖÜ mandateId');
        $transfer->setUltimateDebtorName('Only A-Z without äöüßÄÖÜ ultimateDebtorName');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);
        // Date is correctly coded
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe initiatingPartyName', self::xpathText($xpathDoc, '//sepa:InitgPty/sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorName', self::xpathText($xpathDoc, '//sepa:Cdtr/sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe debtorName', self::xpathText($xpathDoc, '//sepa:EndToEndId'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe debtorName', self::xpathText($xpathDoc, '//sepa:Dbtr/sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe remittanceInformation', self::xpathText($xpathDoc, '//sepa:Ustrd'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe mandateId', self::xpathText($xpathDoc, '//sepa:MndtId'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe ultimateDebtorName', self::xpathText($xpathDoc, '//sepa:UltmtDbtr//sepa:Nm'));
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorSchemeId', self::xpathText($xpathDoc, '//sepa:CdtrSchmeId//sepa:PrvtId//sepa:Id'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideSchema(): iterable
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
            'pain.008.002.02' => ['pain.008.002.02'],
            'pain.008.003.02' => ['pain.008.003.02'],
        ];
    }

    /**
     * Test a transfer file with one payment and one transaction.
     *
     * @param array{pain: string, batchBooking: bool, originAgentBic: string} $scenario
     *
     * @dataProvider scenarios
     */
    public function testDomBuilderAcceptsPainFormatAsConstructor(array $scenario): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setMandateSignDate(new \DateTime('16.08.2013'));
        $transfer->setMandateId('ABCDE');
        $transfer->setRemittanceInformation('Transaction Description');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        if ($scenario['originAgentBic'] !== '') {
            $payment->setOriginAgentBIC($scenario['originAgentBic']);
        }
        if ($scenario['batchBooking']) {
            $payment->setBatchBooking(true);
        }

        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setDueDate(new \DateTime('22.08.2013'));
        $payment->setCreditorId('DE21WVM1234567890');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($scenario['pain']);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(XSD_DIR . $scenario['pain'] . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * @return iterable<array{array{pain: string, batchBooking: bool, originAgentBic: string}}>
     */
    public static function scenarios(): iterable
    {
        $scenarios = [];
        foreach (['pain.008.001.02', 'pain.008.001.10', 'pain.008.002.02', 'pain.008.003.02'] as $pain) {
            $scenarios[] = [
                [
                    'pain' => $pain,
                    'batchBooking' => true,
                    'originAgentBic' => 'NOLADKIE',
                ],
            ];
            $scenarios[] = [
                [
                    'pain' => $pain,
                    'batchBooking' => false,
                    'originAgentBic' => '',
                ],
            ];
        }

        return $scenarios;
    }
}
