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
use PhpSepaXml\GroupHeader;
use PhpSepaXml\PaymentInformation;
use PhpSepaXml\TransferFile\CustomerCreditTransferFile;
use PhpSepaXml\TransferInformation\CustomerCreditTransferInformation;

/**
 * Various schema validation tests.
 */
class CustomerCreditValidationTest extends \PHPUnit_Framework_TestCase
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
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(array('TRANSFER'));
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and several transactions.
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentMultiTrans($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $transfer = new CustomerCreditTransferInformation('500000', 'BE30001216371411', 'GHI Semiconductors');
        $transfer->setBic('DDDDBEBB');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd');
        $this->assertTrue($validated);
    }

    /**
     * Test that a transferfile without Payments throws understandable exception
     * @expectedException \PhpSepaXml\Exception\InvalidTransferFileConfiguration
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testInvalidTransferFileThrowsException($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
    }

    /**
     * Test correct calulation of controlsum and transaction count
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testControlSumAndTransactionCount($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $transfer = new CustomerCreditTransferInformation('500000', 'BE30001216371411', 'GHI Semiconductors');
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

        $numberOfTxs = $xpathDoc->query('//sepa:NbOfTxs');
        $this->assertEquals(2, $numberOfTxs->item(0)->textContent);
        $ctrlSum = $xpathDoc->query('//sepa:CtrlSum');
        $this->assertEquals('5000.02', $ctrlSum->item(0)->textContent);
    }

    /**
     * Test the payment informations in the xml
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testPaymentMetaData($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setDueDate(new \DateTime('20.11.2012'));

        $transfer = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
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
        $executionDate = $xpathDoc->query('//sepa:ReqdExctnDt');
        $this->assertEquals('2012-11-20', $executionDate->item(0)->textContent);
        //Payment method is set
        $paymentMethod = $xpathDoc->query('//sepa:PmtMtd');
        $this->assertEquals('TRF', $paymentMethod->item(0)->textContent);
        //Originating IBAN
        $originIban = $xpathDoc->query('//sepa:DbtrAcct/sepa:Id/sepa:IBAN');
        $this->assertEquals('FR1420041010050500013M02606', $originIban->item(0)->textContent);
        //Originating BIC
        $originBic = $xpathDoc->query('//sepa:DbtrAgt/sepa:FinInstnId/sepa:BIC');
        $this->assertEquals('PSSTFRPPMON', $originBic->item(0)->textContent);
        //Originating Name
        $originName = $xpathDoc->query('//sepa:Dbtr/sepa:Nm');
        $this->assertEquals('My Corp', $originName->item(0)->textContent);
    }

    /**
     * Test a transfer file with several payments, several transactions each.
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testMultiPaymentMultiTrans($schema)
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $payment1 = new PaymentInformation('account settlement', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer1 = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer1->setBic('OKOYFIHH');
        $transfer1->setRemittanceInformation('Transaction description');
        $payment1->addTransfer($transfer1);

        $transfer2 = new CustomerCreditTransferInformation('500000', 'BE30001216371411', 'GHI Semiconductors');
        $transfer2->setBic('DDDDBEBB');
        $transfer2->setRemittanceInformation('Transaction description');
        $payment1->addTransfer($transfer2);

        $sepaFile->addPaymentInformation($payment1);

        $payment2 = new PaymentInformation('account settlement', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer1 = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer1->setBic('OKOYFIHH');
        $transfer1->setRemittanceInformation('Transaction description');
        $payment2->addTransfer($transfer1);

        $transfer2 = new CustomerCreditTransferInformation('500000', 'BE30001216371411', 'GHI Semiconductors');
        $transfer2->setBic('DDDDBEBB');
        $transfer2->setRemittanceInformation('Transaction description');
        $payment2->addTransfer($transfer2);

        $sepaFile->addPaymentInformation($payment2);

        $domBuilder = new CustomerCreditTransferDomBuilder($schema);
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();

        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd');
        $this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . $schema);

        $numberOfTxs = $xpathDoc->query('//sepa:NbOfTxs');
        $this->assertEquals(4, $numberOfTxs->item(0)->textContent);
        $this->assertEquals(2, $numberOfTxs->item(1)->textContent);
        $this->assertEquals(2, $numberOfTxs->item(2)->textContent);
        $ctrlSum = $xpathDoc->query('//sepa:CtrlSum');
        $this->assertEquals('10000.04', $ctrlSum->item(0)->textContent);
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
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'Only A-Z without äöüßÄÖÜ debtorName');
        $payment->setDueDate(new \DateTime('20.11.2012'));
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->setCreditorId('Only A-Z without äöüßÄÖÜ creditorSchemeId');

        $transfer = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Only A-Z without äöüßÄÖÜ creditorName');
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
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:'.$schema);
        // Date is correctly coded
        $testNode = $xpathDoc->query('//sepa:InitgPty/sepa:Nm');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe initiatingPartyName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:Cdtr/sepa:Nm');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:EndToEndId');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe creditorName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:Dbtr/sepa:Nm');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe debtorName', $testNode->item(0)->textContent);
        $testNode = $xpathDoc->query('//sepa:Ustrd');
        $this->assertEquals('Only A-Z without aeoeuessAeOeUe remittanceInformation', $testNode->item(0)->textContent);
    }

    /**
     * Test a transfer file using other date format.
     * There are different representations possible for IsoDateTime:
     * http://www.swift.com/assets/corporates/documents/business_areas/ebam_standards_mx/business/x68910b9357eed3cf49770d42b07d70f1.htm
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testSinglePaymentOtherCreationDateTimeFormat($schema)
    {
        $dateTimeFormat = 'Y-m-d\TH:i:s.000P';

        $dateTime = new \DateTime();
        $groupHeader = new GroupHeader('transferID', 'Me');
        $groupHeader->setCreationDateTimeFormat($dateTimeFormat);
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation('2', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(array('TRANSFER'));
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
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:'.$schema);

        $testNode = $xpathDoc->query('//sepa:CreDtTm');
        $this->assertEquals($dateTime->format($dateTimeFormat), $testNode->item(0)->textContent, 'CreDtTm should have the specified format: ' . $dateTimeFormat);
    }

    /**
     * @return array
     */
    public function provideSchema()
    {
        return array(
            array("pain.001.001.03"),
            array("pain.001.002.03"),
            array("pain.001.003.03")
        );
    }

}
