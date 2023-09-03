<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\GroupHeader;

use PHPUnit\Framework\TestCase;

class CustomerCreditTransferDomBuilderTest extends TestCase
{
    /**
     * Test the XML generation of a direct debit transfer with Custom Creditor Id
     * data for pain.008.001.02
     */
    public function testWithCustomDebitorId(): void
    {
        $groupHeader = new GroupHeader('TEST_CREDITOR_ID', 'Test Company Inc.');
        $groupHeader->setInitiatingPartyId('DE67ZZZ00000123456');
        
        $transferFile = new \Digitick\Sepa\TransferFile\CustomerCreditTransferFile($groupHeader);

        

        $transactionInformation = new \Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation(1000, 'DE40500105174181777145', 'Max Musterman');
        //$transactionInformation->setMandateId('TEST-MANDATE-1');
        //$transactionInformation->setMandateSignDate(new \DateTime('2022-05-15'));
        $transactionInformation->setBic('BNKFRXXXXXX');
        $transactionInformation->setCustomId('creditor-custom-id');

        $paymentInformation = new \Digitick\Sepa\PaymentInformation('RAND001', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Test Company Inc.');
        $paymentInformation->setCreditorId('DE67ZZZ00000123456');
        $paymentInformation->setSequenceType('FRST');
        //$paymentInformation->setCustomId('creditor-custom-id');
        $transferFile->addPaymentInformation($paymentInformation);

        $paymentInformation->addTransfer($transactionInformation);

        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.03');
        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($paymentInformation);
        $builder->visitTransferInformation($transactionInformation);

        $xml = $builder->asXml();

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);

        // Test xml schema validation

        $validated = $doc->schemaValidate(__DIR__ . '/../../fixtures/pain.001.001.03.xsd');
        $this->assertTrue($validated);

        // Test contents

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');

        $creditorCustomIdNode = $xpath->evaluate('/ns:Document/ns:CstmrCdtTrfInitn/ns:PmtInf/ns:CdtTrfTxInf/ns:Cdtr/ns:Id/ns:PrvtId/ns:Othr/ns:Id')->item(0);

        $this->assertSame('creditor-custom-id', $creditorCustomIdNode->textContent);
    }

    /*

    // Create the initiating information

$transfer = new CustomerCreditTransferInformation(
    2, // Amount
    'FI1350001540000056', //IBAN of creditor
    'Their Corp' //Name of Creditor
);
$transfer->setBic('OKOYFIHH'); // Set the BIC explicitly
$transfer->setRemittanceInformation('Transaction Description');

// Create a PaymentInformation the Transfer belongs to
$payment = new PaymentInformation(
    'Payment Info ID',
    'FR1420041010050500013M02606', // IBAN the money is transferred from
    'PSSTFRPPMON',  // BIC
    'My Corp' // Debitor Name
);
// It's possible to add multiple Transfers in one Payment
$payment->addTransfer($transfer);

// It's possible to add multiple payments to one SEPA File
$sepaFile->addPaymentInformation($payment);

// Attach a dombuilder to the sepaFile to create the XML output
$domBuilder = DomBuilderFactory::createDomBuilder($sepaFile);

// Or if you want to use the format 'pain.001.001.03' instead
// $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, 'pain.001.001.03');

*/
}
