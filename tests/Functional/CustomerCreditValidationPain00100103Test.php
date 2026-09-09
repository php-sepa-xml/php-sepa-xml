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
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\Tests\XPathAssertions;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use PHPUnit\Framework\TestCase;

/**
 * Various schema validation tests.
 */
class CustomerCreditValidationPain00100103Test extends TestCase
{
    use XPathAssertions;

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp(): void
    {
        $this->schema = XSD_DIR . 'pain.001.001.03.xsd';
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     */
    public function testSanity(): void
    {
        $this->dom->load(XML_DIR . 'pain.001.001.03.xml');
        $this->assertTrue($this->dom->schemaValidate($this->schema));
    }

    /**
     * Test a transfer file with one payment and one transaction.
     *
     * @param array{batchBooking?: bool, bic: string, localInstrumentProprietary?: string, localInstrumentCode?: string, transactionCategoryPurposeCode?: string, transactionLocalInstrumentProprietary?: string, transactionLocalInstrumentCode?: string, transactionServiceLevelCode?: string} $scenario
     *
     * @dataProvider scenarios
     */
    public function testSinglePaymentSingleTransWithMoreInfo(array $scenario): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $groupHeader->setInitiatingPartyId('XXXXXXXXXX');
        $groupHeader->setIssuer('Issuing Party');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        if ($scenario['bic'] !== '') {
            $transfer->setBic($scenario['bic']);
        }
        $transfer->setPurposeCode('SALA');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());
        if (isset($scenario['transactionLocalInstrumentProprietary'])) {
            $transfer->setLocalInstrumentProprietary($scenario['transactionLocalInstrumentProprietary']);
        } elseif (isset($scenario['transactionLocalInstrumentCode'])) {
            $transfer->setLocalInstrumentCode($scenario['transactionLocalInstrumentCode']);
        }
        if (isset($scenario['transactionServiceLevelCode'])) {
            $transfer->setServiceLevelCode($scenario['transactionServiceLevelCode']);
        }
        if (isset($scenario['transactionCategoryPurposeCode'])) {
            $transfer->setCategoryPurposeCode($scenario['transactionCategoryPurposeCode']);
        }
        $transfer->setStreetName('Straat creditor 1');
        $transfer->setPostCode('9999');
        $transfer->setTownName('XX Plaats creditor');
        $transfer->setCountry('NL');
        $transfer->setPostalAddress(['Straat creditor 1', '9999 XX Plaats creditor']);

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        if (isset($scenario['batchBooking'])) {
            $payment->setBatchBooking($scenario['batchBooking']);
        }
        $payment->setValidPaymentMethods(['TRANSFER']);
        $payment->setPaymentMethod('TRANSFER');
        if (isset($scenario['localInstrumentProprietary'])) {
            $payment->setLocalInstrumentProprietary($scenario['localInstrumentProprietary']);
        } elseif (isset($scenario['localInstrumentCode'])) {
            $payment->setLocalInstrumentCode($scenario['localInstrumentCode']);
        }
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder('pain.001.001.03');
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);

        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');

        $this->assertEquals('SALA', self::xpathText($xpathDoc, '//sepa:Purp/sepa:Cd'));

        if (isset($scenario['transactionCategoryPurposeCode'])) {
            $this->assertEquals('SUPP', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:PmtTpInf/sepa:CtgyPurp/sepa:Cd'));
        }

        if (isset($scenario['transactionLocalInstrumentProprietary'])) {
            $this->assertEquals($scenario['transactionLocalInstrumentProprietary'], self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:PmtTpInf/sepa:LclInstrm/sepa:Prtry'));
        } elseif (isset($scenario['transactionLocalInstrumentCode'])) {
            $this->assertEquals($scenario['transactionLocalInstrumentCode'], self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:PmtTpInf/sepa:LclInstrm/sepa:Cd'));
        }

        if (isset($scenario['transactionServiceLevelCode'])) {
            $this->assertEquals($scenario['transactionServiceLevelCode'], self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:PmtTpInf/sepa:SvcLvl/sepa:Cd'));
        } elseif (!isset($scenario['transactionCategoryPurposeCode']) && !isset($scenario['transactionLocalInstrumentProprietary']) && !isset($scenario['transactionLocalInstrumentCode'])) {
            $this->assertSame(0, self::xpathQuery($xpathDoc, '//sepa:CdtTrfTxInf/sepa:PmtTpInf')->length);
        }

        if (isset($scenario['localInstrumentProprietary'])) {
            $this->assertEquals($scenario['localInstrumentProprietary'], self::xpathText($xpathDoc, '//sepa:PmtInf/sepa:PmtTpInf/sepa:LclInstrm/sepa:Prtry'));
        } elseif (isset($scenario['localInstrumentCode'])) {
            $this->assertEquals($scenario['localInstrumentCode'], self::xpathText($xpathDoc, '//sepa:PmtInf/sepa:PmtTpInf/sepa:LclInstrm/sepa:Cd'));
        }

        $this->assertEquals('Straat creditor 1', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:Cdtr/sepa:PstlAdr/sepa:StrtNm'));
        $this->assertEquals('9999', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:Cdtr/sepa:PstlAdr/sepa:PstCd'));
        $this->assertEquals('XX Plaats creditor', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:Cdtr/sepa:PstlAdr/sepa:TwnNm'));
        $this->assertEquals('NL', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:Cdtr/sepa:PstlAdr/sepa:Ctry'));

        $this->assertEquals('Straat creditor 1', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:Cdtr/sepa:PstlAdr/sepa:AdrLine'));
        $this->assertEquals('9999 XX Plaats creditor', self::xpathText($xpathDoc, '//sepa:CdtTrfTxInf/sepa:Cdtr/sepa:PstlAdr/sepa:AdrLine', null, 1));
    }

    /**
     * @return iterable<array{array{batchBooking?: bool, bic: string, localInstrumentProprietary?: string, localInstrumentCode?: string, transactionCategoryPurposeCode?: string, transactionLocalInstrumentProprietary?: string, transactionLocalInstrumentCode?: string, transactionServiceLevelCode?: string}}>
     */
    public static function scenarios(): iterable
    {
        return [
            [
                [
                    'batchBooking' => true,
                    'bic' => 'OKOYFIHH',
                    'localInstrumentProprietary' => 'CBI',
                ],
            ],
            [
                [
                    'batchBooking' => true,
                    'bic' => 'OKOYFIHH',
                    'transactionCategoryPurposeCode' => 'SUPP',
                    'transactionLocalInstrumentProprietary' => 'CBIX',
                    'localInstrumentProprietary' => 'CBI',
                ],
            ],
            [
                [
                    'batchBooking' => false,
                    'bic' => '',
                    'transactionCategoryPurposeCode' => 'SUPP',
                    'transactionLocalInstrumentCode' => 'B2B',
                    'localInstrumentCode' => 'CORE',
                ],
            ],
            [
                [
                    'batchBooking' => false,
                    'bic' => '',
                    'transactionCategoryPurposeCode' => 'SUPP',
                    'transactionLocalInstrumentCode' => 'B2B',
                    'localInstrumentCode' => 'CORE',
                    'transactionServiceLevelCode' => 'SEPA',
                ],
            ],
        ];
    }
}
