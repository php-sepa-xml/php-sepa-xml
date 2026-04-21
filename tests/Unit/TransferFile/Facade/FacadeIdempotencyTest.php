<?php

namespace Digitick\Sepa\Tests\Unit\TransferFile\Facade;

use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use PHPUnit\Framework\TestCase;

/**
 * Guards against regression of the "calling asXML()/asDOC() twice mutates state"
 * bug (see IMPROVEMENTS.md #3). Repeated rendering must be idempotent.
 */
class FacadeIdempotencyTest extends TestCase
{
    public function testCustomerCreditAsXmlIsIdempotent(): void
    {
        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', 'pain.001.001.09');
        $credit->addPaymentInfo('firstPayment', [
            'id' => 'firstPayment',
            'debtorName' => 'My Company',
            'debtorAccountIBAN' => 'FI1350001540000056',
            'debtorAgentBIC' => 'PSSTFRPPMON',
        ]);
        $credit->addTransfer('firstPayment', [
            'amount' => 500,
            'creditorIban' => 'FI1350001540000056',
            'creditorBic' => 'OKOYFIHH',
            'creditorName' => 'Their Company',
            'remittanceInformation' => 'Purpose of this credit',
        ]);

        $firstXml = $credit->asXML();
        $secondXml = $credit->asXML();

        $this->assertSame($firstXml, $secondXml, 'asXML() must produce identical output across repeated calls');

        $xpath = $this->xpath($secondXml, 'pain.001.001.09');

        $this->assertSame(
            '1',
            $xpath->evaluate('string(//sepa:GrpHdr/sepa:NbOfTxs)'),
            'GrpHdr/NbOfTxs must equal 1, not 2'
        );
        $this->assertSame(
            '5.00',
            $xpath->evaluate('string(//sepa:GrpHdr/sepa:CtrlSum)'),
            'GrpHdr/CtrlSum must equal 5.00, not 10.00'
        );
        $this->assertSame(
            1,
            $xpath->query('//sepa:CstmrCdtTrfInitn')->length,
            'Document must contain exactly one CstmrCdtTrfInitn element'
        );
        $this->assertSame(
            1,
            $xpath->query('//sepa:PmtInf')->length,
            'Document must contain exactly one PmtInf element'
        );
    }

    public function testCustomerCreditAsDocIsIdempotent(): void
    {
        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', 'pain.001.001.09');
        $credit->addPaymentInfo('firstPayment', [
            'id' => 'firstPayment',
            'debtorName' => 'My Company',
            'debtorAccountIBAN' => 'FI1350001540000056',
            'debtorAgentBIC' => 'PSSTFRPPMON',
        ]);
        $credit->addTransfer('firstPayment', [
            'amount' => 500,
            'creditorIban' => 'FI1350001540000056',
            'creditorBic' => 'OKOYFIHH',
            'creditorName' => 'Their Company',
            'remittanceInformation' => 'Purpose of this credit',
        ]);

        // Capture saveXML() immediately — both calls return the same underlying
        // DOMDocument instance, so capturing *after* the second call would mask
        // the bug by comparing the final state to itself.
        $firstXml = $credit->asDOC()->saveXML();
        $secondXml = $credit->asDOC()->saveXML();

        $this->assertSame(
            $firstXml,
            $secondXml,
            'asDOC() must produce identical output across repeated calls'
        );
    }

    public function testMixedAsXmlAsDocIsIdempotent(): void
    {
        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', 'pain.001.001.09');
        $credit->addPaymentInfo('firstPayment', [
            'id' => 'firstPayment',
            'debtorName' => 'My Company',
            'debtorAccountIBAN' => 'FI1350001540000056',
            'debtorAgentBIC' => 'PSSTFRPPMON',
        ]);
        $credit->addTransfer('firstPayment', [
            'amount' => 500,
            'creditorIban' => 'FI1350001540000056',
            'creditorBic' => 'OKOYFIHH',
            'creditorName' => 'Their Company',
            'remittanceInformation' => 'Purpose of this credit',
        ]);

        $xmlFromAsXml = $credit->asXML();
        $xmlFromAsDoc = $credit->asDOC()->saveXML();

        $this->assertSame($xmlFromAsXml, $xmlFromAsDoc, 'asXML() and asDOC() must agree on output');
    }

    public function testDirectDebitAsXmlIsIdempotent(): void
    {
        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', 'pain.008.001.02');
        $directDebit->addPaymentInfo('firstPayment', [
            'id' => 'firstPayment',
            'creditorName' => 'My Company',
            'creditorAccountIBAN' => 'FI1350001540000056',
            'creditorAgentBIC' => 'PSSTFRPPMON',
            'seqType' => PaymentInformation::S_ONEOFF,
            'creditorId' => 'DE21WVM1234567890',
        ]);
        $directDebit->addTransfer('firstPayment', [
            'amount' => 500,
            'debtorIban' => 'FI1350001540000056',
            'debtorBic' => 'OKOYFIHH',
            'debtorName' => 'Their Company',
            'debtorMandate' => 'AB12345',
            'debtorMandateSignDate' => '13.10.2012',
            'remittanceInformation' => 'Purpose of this direct debit',
        ]);

        $firstXml = $directDebit->asXML();
        $secondXml = $directDebit->asXML();

        $this->assertSame($firstXml, $secondXml, 'Direct debit asXML() must be idempotent');

        $xpath = $this->xpath($secondXml, 'pain.008.001.02');
        $this->assertSame('1', $xpath->evaluate('string(//sepa:GrpHdr/sepa:NbOfTxs)'));
        $this->assertSame('5.00', $xpath->evaluate('string(//sepa:GrpHdr/sepa:CtrlSum)'));
        $this->assertSame(1, $xpath->query('//sepa:CstmrDrctDbtInitn')->length);
        $this->assertSame(1, $xpath->query('//sepa:PmtInf')->length);
    }

    private function xpath(string $xml, string $painFormat): \DOMXPath
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('sepa', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));

        return $xpath;
    }
}
