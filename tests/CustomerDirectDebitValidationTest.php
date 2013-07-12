<?php
namespace Tests;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;

/**
 * User: s.rohweder@blage.net
 * Date: 7/10/13
 * Time: 11:48 PM
 * License: MIT
 */

class CustomerDirectDebitValidationTest extends \PHPUnit_Framework_TestCase {
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp() {
        $this->schema = __DIR__ . "/pain.008.002.02.xsd";
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     */
    public function testSanity() {
        $this->dom->load(__DIR__ . '/pain.008.002.02.xml');
        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and one transaction.
     */
    public function testSinglePaymentSingleTrans()
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
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

        $domBuilder = new CustomerDirectDebitTransferDomBuilder();
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);
    }

    /**
     * @expectedException \Digitick\Sepa\Exception\InvalidTransferFileConfiguration
     * @expectedExceptionMessage Payment must contain a SequenceType
     */
    public function testValidationFailureSeqType() {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder();
        $sepaFile->accept($domBuilder);
    }

    /**
     * @expectedException \Digitick\Sepa\Exception\InvalidTransferFileConfiguration
     * @expectedExceptionMessage Payment must contain a CreditorSchemeId
     */
    public function testValidationFailureCreditorId() {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $transfer = new CustomerDirectDebitTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerDirectDebitTransferDomBuilder();
        $sepaFile->accept($domBuilder);
    }
}
