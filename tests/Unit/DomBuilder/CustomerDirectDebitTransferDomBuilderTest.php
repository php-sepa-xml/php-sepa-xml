<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\GroupHeader;

use PHPUnit\Framework\TestCase;

class CustomerDirectDebitTransferDomBuilderTest extends TestCase
{
    /**
     * Test the XML generation of a direct debit transfer with structured address
     * data for pain.008.001.02
     */
    public function testWithAddress(): void
    {
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

        $builder = new CustomerDirectDebitTransferDomBuilder('pain.008.001.02');
        $builder->visitTransferFile($transferFile);
        $builder->visitGroupHeader($groupHeader);
        $builder->visitPaymentInformation($paymentInformation);
        $builder->visitTransferInformation($transactionInformation);

        $xml = $builder->asXml();

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);

        // Test xml schema validation

        $validated = $doc->schemaValidate(__DIR__ . '/../../fixtures/pain.008.001.02.xsd');
        $this->assertTrue($validated);

        // Test contents

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        $postalAddressNode = $xpath->evaluate('/ns:Document/ns:CstmrDrctDbtInitn/ns:PmtInf/ns:DrctDbtTxInf/ns:Dbtr/ns:PstlAdr')->item(0);

        $this->assertSame('DE', $xpath->evaluate('./ns:Ctry', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('60431', $xpath->evaluate('./ns:PstCd', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Frankfurt am Main', $xpath->evaluate('./ns:TwnNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Wilhelm-Epstein-Str.', $xpath->evaluate('./ns:StrtNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('14', $xpath->evaluate('./ns:BldgNb', $postalAddressNode)->item(0)->textContent);
    }

}
