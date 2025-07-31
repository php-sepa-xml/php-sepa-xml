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

namespace Digitick\Sepa\Tests\Unit\TransferFile\Facade;

use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use PHPUnit\Framework\TestCase;

class CustomerDirectDebitFacadeTest extends TestCase
{
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
        $this->schema = __DIR__ . "/../../../fixtures/pain.008.002.02.xsd";
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    protected function createDirectDebitXpathObject(int $amount): \DOMXPath
    {
        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me');

        // create a payment, it's possible to create multiple payments,
        // "firstPayment" is the identifier for the transactions
        $directDebit->addPaymentInfo(
            'firstPayment',
            [
                'id' => 'firstPayment',
                'creditorName' => 'My Company',
                'creditorAccountIBAN' => 'FI1350001540000056',
                'creditorAgentBIC' => 'PSSTFRPPMON',
                'seqType' => PaymentInformation::S_ONEOFF,
                'creditorId' => 'DE21WVM1234567890',
                'localInstrumentCode' => 'B2B',
            ]
        );
        // Add a Single Transaction to the named payment
        $directDebit->addTransfer(
            'firstPayment',
            [
                'amount' => $amount,
                'debtorIban' => 'FI1350001540000056',
                'debtorBic' => 'OKOYFIHH',
                'debtorName' => 'Their Company',
                'debtorMandate' => 'AB12345',
                'debtorMandateSignDate' => '13.10.2012',
                'remittanceInformation' => 'Purpose of this direct debit'
            ]
        );
        // Retrieve the resulting XML
        $xml = $directDebit->asXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $directDebitXpath = new \DOMXPath($doc);
        $directDebitXpath->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:pain.008.002.02');

        return $directDebitXpath;
    }

    public function testValidSumIsCalculatedCorrectly(): void
    {
        $directDebitXpath = $this->createDirectDebitXpathObject(1999);
        $controlSum = $directDebitXpath->query('//sepa:GrpHdr/sepa:CtrlSum');
        $this->assertEquals('19.99', $controlSum->item(0)->textContent, 'GroupHeader ControlSum should be 19.99');

        $controlSum = $directDebitXpath->query('//sepa:PmtInf/sepa:CtrlSum');
        $this->assertEquals(
            '19.99',
            $controlSum->item(0)->textContent,
            'PaymentInformation ControlSum should be 19.99'
        );
        $controlSum = $directDebitXpath->query('//sepa:DrctDbtTxInf/sepa:InstdAmt');
        $this->assertEquals(
            '19.99',
            $controlSum->item(0)->textContent,
            'DirectDebitTransferInformation InstructedAmount should be 19.99'
        );
    }

    public function testFloatSumIsCalculatedCorrectly(): void
    {
        $directDebitXpath = $this->createDirectDebitXpathObject(1999);
        $controlSum = $directDebitXpath->query('//sepa:GrpHdr/sepa:CtrlSum');
        $this->assertEquals('19.99', $controlSum->item(0)->textContent, 'GroupHeader ControlSum should be 19.99');

        $controlSum = $directDebitXpath->query('//sepa:PmtInf/sepa:CtrlSum');
        $this->assertEquals(
            '19.99',
            $controlSum->item(0)->textContent,
            'PaymentInformation ControlSum should be 19.99'
        );
        $controlSum = $directDebitXpath->query('//sepa:DrctDbtTxInf/sepa:InstdAmt');
        $this->assertEquals(
            '19.99',
            $controlSum->item(0)->textContent,
            'DirectDebitTransferInformation InstructedAmount should be 19.99'
        );
    }

    public function testFloatSumIsCalculatedCorrectlyWithNonEnglishLocale(): void
    {
        $result = setlocale(LC_ALL, 'es_ES.UTF-8', 'es_ES@UTF-8', 'spanish');

        if ($result == false) {
            $this->markTestSkipped('spanish locale is not available');
        }

        $directDebitXpath = $this->createDirectDebitXpathObject(1999);
        $controlSum = $directDebitXpath->query('//sepa:GrpHdr/sepa:CtrlSum');
        $this->assertEquals('19.99', $controlSum->item(0)->textContent, 'GroupHeader ControlSum should be 19.99');

        $controlSum = $directDebitXpath->query('//sepa:PmtInf/sepa:CtrlSum');
        $this->assertEquals(
            '19.99',
            $controlSum->item(0)->textContent,
            'PaymentInformation ControlSum should be 19.99'
        );
        $controlSum = $directDebitXpath->query('//sepa:DrctDbtTxInf/sepa:InstdAmt');
        $this->assertEquals(
            '19.99',
            $controlSum->item(0)->textContent,
            'DirectDebitTransferInformation InstructedAmount should be 19.99'
        );
    }

    /**
     * Test creation of file via Factory and Facade
     *
     * @dataProvider provideSchema
     */
    public function testValidFileCreationWithFacade(string $schema): void
    {
        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', $schema);
        $paymentInformation = $directDebit->addPaymentInfo(
            'firstPayment',
            [
                'id' => 'firstPayment',
                'creditorName' => 'My Company',
                'creditorAccountIBAN' => 'FI1350001540000056',
                'creditorAgentBIC' => 'PSSTFRPPMON',
                'seqType' => PaymentInformation::S_ONEOFF,
                'creditorId' => 'DE21WVM1234567890'
            ]
        );
        $paymentInformation->setBatchBooking(true);

        $directDebit->addTransfer(
            'firstPayment',
            [
                'amount' => 500,
                'debtorIban' => 'FI1350001540000056',
                'debtorBic' => 'OKOYFIHH',
                'debtorName' => 'Their Company',
                'debtorMandate' => 'AB12345',
                'debtorMandateSignDate' => '13.10.2012',
                'remittanceInformation' => 'Purpose of this direct debit',
                'debtorCountry' => 'DE',
                'debtorAdrLine' => 'Some Address',
                'streetName' => 'Some Street',
                'buildingNumber' => '5',
                'postCode' => '01001',
                'townName' => 'Dresden',
                'instructionId' => 'Instruction Identification',
            ]
        );

        $this->dom->loadXML($directDebit->asXML());
        $this->assertTrue($this->dom->schemaValidate(__DIR__ . '/../../../fixtures/' . $schema . '.xsd'));
    }

    /**
     * Test creation of file via Factory and Facade
     *
     * @dataProvider provideSchema
     */
    public function testValidFileCreationWithFacadeWithoutBic(string $schema): void
    {
        if ($schema === 'pain.008.002.02') {
            $this->markTestSkipped('Will fail for this schema as the BIC is required');
        }

        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', $schema);
        $paymentInformation = $directDebit->addPaymentInfo(
            'firstPayment',
            [
                'id' => 'firstPayment',
                'creditorName' => 'My Company',
                'creditorAccountIBAN' => 'FI1350001540000056',
                'seqType' => PaymentInformation::S_ONEOFF,
                'creditorId' => 'DE21WVM1234567890'
            ]
        );
        $paymentInformation->setBatchBooking(true);

        $directDebit->addTransfer(
            'firstPayment',
            [
                'amount' => 500,
                'debtorIban' => 'FI1350001540000056',
                'debtorName' => 'Their Company',
                'debtorMandate' => 'AB12345',
                'debtorMandateSignDate' => '13.10.2012',
                'creditorReference' => 'RF81123453',
                'debtorCountry' => 'DE',
                'debtorAdrLine' => 'Some Address',
                'streetName' => 'Some Street',
                'buildingNumber' => '5',
                'postCode' => '01001',
                'townName' => 'Dresden',
                'instructionId' => 'Instruction Identification',
            ]
        );

        $this->dom->loadXML($directDebit->asXML());
        $this->assertTrue($this->dom->schemaValidate(__DIR__ . '/../../../fixtures/' . $schema . '.xsd'));
    }

    public static function provideSchema(): iterable
    {
        return [
            ['pain.008.001.02'],
            ['pain.008.002.02'],
            ['pain.008.003.02'],
            ['pain.008.001.08']
        ];
    }

    /**
     * @dataProvider provideSchemaWithFullAddresses
     */
    public function testAddTransferWithAddress(string $painFormat): void
    {
        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', $painFormat);

        // create a payment, it's possible to create multiple payments,
        // "firstPayment" is the identifier for the transactions
        $directDebit->addPaymentInfo('firstPayment', [
            'id' => 'firstPayment',
            'creditorName' => 'My Company',
            'creditorAccountIBAN' => 'DE78500105172337771347',
            'creditorAgentBIC' => 'WELADE3LXXX',
            'seqType' => PaymentInformation::S_ONEOFF,
            'creditorId' => 'DE21WVM1234567890',
        ]);

        // Add a Single Transaction to the named payment
        $directDebit->addTransfer('firstPayment', [
            'amount'                => 1499,
            'debtorIban'            => 'CH6089144731137988786',
            'debtorBic'             => 'CRESCHZZXXX',
            'debtorName'            => 'John Doe',
            'debtorMandate'         => 'AB12345',
            'debtorMandateSignDate' => '2022-05-23',
            'remittanceInformation' => 'Purpose of this direct debit',
            'debtorCountry'         => 'CH',
            'postCode'              => '8245',
            'townName'              => 'Feuerthalen',
            'streetName'            => 'Example Street',
            'buildingNumber'        => '25',
        ]);

        // Test action
        $payment = $directDebit->getPaymentInfo('firstPayment');
        $transfer = $payment->getTransfers()[0];

        $this->assertSame('CH', $transfer->getCountry());
        $this->assertSame('8245', $transfer->getPostCode());
        $this->assertSame('Feuerthalen', $transfer->getTownName());
        $this->assertSame('Example Street', $transfer->getStreetName());
        $this->assertSame('25', $transfer->getBuildingNumber());

        $this->dom->loadXML($directDebit->asXML());

        $itemValue = function (string $node): ?string {
            if ($item = $this->dom->getElementsByTagName($node)->item(0)) {
                return $item->textContent;
            }

            return null;
        };

        self::assertNull($itemValue('AdrLine'), 'expected <AdrLine> do not exist');
        self::assertSame('25', $itemValue('BldgNb'), 'expected <BldgNb>25</BldgNb>');
        self::assertSame('8245', $itemValue('PstCd'), 'expected <PstCd>8245</PstCd>');
        self::assertSame('CH', $itemValue('Ctry'), 'expected <Ctry>CH</Ctry>');
        self::assertSame('Example Street', $itemValue('StrtNm'), 'expected <StrtNm>Example Street</StrtNm>');
        self::assertSame('Feuerthalen', $itemValue('TwnNm'), 'expected <TwnNm>Feuerthalen</TwnNm>');
    }

    public function provideSchemaWithFullAddresses(): iterable
    {
        return [
            'pain.008.001.02' => ['pain.008.001.02'],
            'pain.008.001.08' => ['pain.008.001.08']
        ];
    }
}
