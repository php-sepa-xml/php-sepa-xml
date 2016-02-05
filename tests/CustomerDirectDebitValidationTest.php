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

namespace tests;

use PhpSepaXml\DomBuilder\CustomerCreditTransferDomBuilder;
use PhpSepaXml\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use PhpSepaXml\Exception\InvalidTransferFileConfiguration;
use PhpSepaXml\GroupHeader;
use PhpSepaXml\PaymentInformation;
use PhpSepaXml\TransferFile\CustomerDirectDebitTransferFile;
use PhpSepaXml\TransferInformation\CustomerDirectDebitTransferInformation;

class CustomerDirectDebitValidationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testSanity($schema)
    {
        $this->dom->load(__DIR__ . '/' . $schema . '.xml');
        $validated = $this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and one transaction.
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentSingleTrans($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setMandateSignDate(new \DateTime('16.08.2013'));
        $transfer->setMandateId('ABCDE');
        $transfer->setRemittanceInformation('Transaction Description');

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

        $validated = $this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * @expectedException \PhpSepaXml\Exception\InvalidTransferFileConfiguration
     * @expectedExceptionMessage Payment must contain a SequenceType
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testValidationFailureSeqType($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation('2', 'FI1350001540000056', 'Their Corp');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
    }

    /**
     * @expectedException \PhpSepaXml\Exception\InvalidTransferFileConfiguration
     * @expectedExceptionMessage Payment must contain a CreditorSchemeId
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testValidationFailureCreditorId($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation('2', 'FI1350001540000056', 'Their Corp');

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
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testUmlautConversion($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Only A-Z without äöüßÄÖÜ initiatingPartyName');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'Only A-Z without äöüßÄÖÜ creditorName');
        $payment->setDueDate(new \DateTime('20.11.2012'));
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('Only A-Z without äöüßÄÖÜ creditorSchemeId');

        $transfer = new CustomerDirectDebitTransferInformation('2', 'FI1350001540000056', 'Only A-Z without äöüßÄÖÜ debtorName');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Only A-Z without äöüßÄÖÜ remittanceInformation');
        $transfer->setMandateSignDate(new \DateTime());
        $transfer->setMandateId('Only A-Z without äöüßÄÖÜ mandateId');
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
        $testNode = $xpathDoc->query('//sepa:InitgPty/sepa:Nm');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe initiatingPartyName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:Cdtr/sepa:Nm');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:EndToEndId');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe debtorName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:Dbtr/sepa:Nm');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe debtorName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:Ustrd');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe remittanceInformation', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:MndtId');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe mandateId', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:CdtrSchmeId//sepa:PrvtId//sepa:Id');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorSchemeId', $testNode->item(0)->textContent);
    }

    /**
     * @return array
     */
    public function provideSchema()
    {
        return array(
            array('pain.008.001.02'),
            array('pain.008.002.02'),
            array('pain.008.003.02')
        );
    }
}
