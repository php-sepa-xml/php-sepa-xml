<?php

namespace Tests;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;

/**
 * Various schema validation tests.
 */
class CustomerCreditValidationTest extends \PHPUnit_Framework_TestCase
{
	protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;
	
	protected function setUp()
	{
		$this->schema = __DIR__ . "/pain.001.002.03.xsd";
		$this->dom = new \DOMDocument('1.0', 'UTF-8');
	} 

	/**
	 * Sanity check: test reference file with XSD.
	 */
	public function testSanity()
	{
		$this->dom->load(__DIR__ . '/pain.001.002.03.xml');
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}

	/**
	 * Test a transfer file with one payment and one transaction.
	 */
	public function testSinglePaymentSingleTrans()
	{

        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction Description');

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
		$payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder();
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
		$this->dom->loadXML($xml);
		
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}

	/**
	 * Test a transfer file with one payment and several transactions.
	 */
	public function testSinglePaymentMultiTrans()
	{
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $transfer = new CustomerCreditTransferInformation('5000.00', 'BE30001216371411', 'GHI Semiconductors');
        $transfer->setBic('DDDDBEBB');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder();
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}

    /**
     * Test that a transferfile without Payments throws understandable exception
     * @expectedException \Digitick\Sepa\Exception\InvalidTransferFileConfiguration
     */
    public function testInvalidTransferFileThrowsException() {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $domBuilder = new CustomerCreditTransferDomBuilder();
        $sepaFile->accept($domBuilder);
    }


    /**
     * Test correct calulation of controlsum and transaction count
     */
    public function testControlSumAndTransactionCount() {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $transfer = new CustomerCreditTransferInformation('5000.00', 'BE30001216371411', 'GHI Semiconductors');
        $transfer->setBic('DDDDBEBB');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder();
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:pain.001.002.03');
        $numberOfTxs = $xpathDoc->query('//sepa:NbOfTxs');
        $this->assertEquals(2, $numberOfTxs->item(0)->textContent);
        $ctrlSum = $xpathDoc->query('//sepa:CtrlSum');
        $this->assertEquals('5000.02', $ctrlSum->item(0)->textContent);
    }

    /**
     * Test the payment informations in the xml
     */
    public function testPaymentMetaData() {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setDueDate(new \DateTime('20.11.2012'));

        $transfer = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction description');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder();
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpathDoc = new \DOMXPath($doc);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:pain.001.002.03');
        // Date is correctly coded
        $executionDate = $xpathDoc->query('//sepa:ReqdExctnDt');
        $this->assertEquals('2012-11-20', $executionDate->item(0)->textContent);
        //Payment method is set
        $paymentMethod = $xpathDoc->query('//sepa:PmtMtd');
        $this->assertEquals('TRF', $paymentMethod->item(0)->textContent);
        //Originiating IBAN
        $originIban = $xpathDoc->query('//sepa:DbtrAcct/sepa:Id/sepa:IBAN');
        $this->assertEquals('FR1420041010050500013M02606', $originIban->item(0)->textContent);
        //Originiating BIC
        $originBic = $xpathDoc->query('//sepa:DbtrAgt/sepa:FinInstnId/sepa:BIC');
        $this->assertEquals('PSSTFRPPMON', $originBic->item(0)->textContent);
        //Originiating Name
        $originName = $xpathDoc->query('//sepa:Dbtr/sepa:Nm');
        $this->assertEquals('My Corp', $originName->item(0)->textContent);
    }

	/**
	 * Test a transfer file with several payments, several transactions each.
	 */
	public function testMultiPaymentMultiTrans()
	{
        $groupHeader = new GroupHeader('transferID', 'Me');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $payment1 = new PaymentInformation('account settlement', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer1 = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer1->setBic('OKOYFIHH');
        $transfer1->setRemittanceInformation('Transaction description');
        $payment1->addTransfer($transfer1);

        $transfer2 = new CustomerCreditTransferInformation('5000.00', 'BE30001216371411', 'GHI Semiconductors');
        $transfer2->setBic('DDDDBEBB');
        $transfer2->setRemittanceInformation('Transaction description');
        $payment1->addTransfer($transfer2);

        $sepaFile->addPaymentInformation($payment1);

        $payment2 = new PaymentInformation('account settlement', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');

        $transfer1 = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer1->setBic('OKOYFIHH');
        $transfer1->setRemittanceInformation('Transaction description');
        $payment2->addTransfer($transfer1);

        $transfer2 = new CustomerCreditTransferInformation('5000.00', 'BE30001216371411', 'GHI Semiconductors');
        $transfer2->setBic('DDDDBEBB');
        $transfer2->setRemittanceInformation('Transaction description');
        $payment2->addTransfer($transfer2);

        $sepaFile->addPaymentInformation($payment2);

        $domBuilder = new CustomerCreditTransferDomBuilder();
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();

		$this->dom->loadXML($xml);
		
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:pain.001.002.03');
        $numberOfTxs = $xpathDoc->query('//sepa:NbOfTxs');
        $this->assertEquals(4, $numberOfTxs->item(0)->textContent);
        $this->assertEquals(2, $numberOfTxs->item(1)->textContent);
        $this->assertEquals(2, $numberOfTxs->item(2)->textContent);
        $ctrlSum = $xpathDoc->query('//sepa:CtrlSum');
        $this->assertEquals('10000.04', $ctrlSum->item(0)->textContent);

	}
}

/*$payment2 = $sepaFile->addPaymentInfo(array(
                                           'id'                    => 'Payment Info ID',
                                           'debtorName'            => 'My Corp',
                                           'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
                                           'debtorAgentBIC'        => 'PSSTFRPPMON'
                                      ));
$payment2->addCreditTransfer(array(
                                  'id'                    => 'Id shown in bank statement',
                                  'currency'              => 'EUR',
                                  'amount'                => '0.02',
                                  'creditorName'          => 'Their Corp',
                                  'creditorAccountIBAN'   => 'FI1350001540000056',
                                  'creditorBIC'           => 'OKOYFIHH',
                                  'remittanceInformation' => 'Transaction description',
                             ));*/