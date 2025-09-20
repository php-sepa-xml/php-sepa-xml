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
use Digitick\Sepa\Tests\TestCase;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\Util\MessageFormat;

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
        $this->schema = 'pain.008.002.02';
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    protected function createDirectDebitXpathObject(int $amount, string $schema): \DOMXPath
    {
        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', $schema);

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

        $domDoc = new \DOMDocument('1.0', 'UTF-8');
        $domDoc->loadXML($xml);
        $directDebitXpath = new \DOMXPath($domDoc);
        $directDebitXpath->registerNamespace('sepa', "urn:iso:std:iso:20022:tech:xsd:{$schema}");

        return $directDebitXpath;
    }

    /**
     * @dataProvider provideSchema
     */
    public function testValidSumIsCalculatedCorrectly(string $schema): void
    {
        $directDebitXpath = $this->createDirectDebitXpathObject(1999, $schema);
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
     * @dataProvider provideSchema
     */
    public function testFloatSumIsCalculatedCorrectly(string $schema): void
    {
        $directDebitXpath = $this->createDirectDebitXpathObject(1999, $schema);
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
     * @dataProvider provideSchema
     */
    public function testFloatSumIsCalculatedCorrectlyWithNonEnglishLocale(string $schema): void
    {
        $result = setlocale(LC_ALL, 'es_ES.UTF-8', 'es_ES@UTF-8', 'spanish');

        if ($result == false) {
            $this->markTestSkipped('spanish locale is not available');
        }

        $directDebitXpath = $this->createDirectDebitXpathObject(1999, $schema);
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
     * Test creation of file via Factory and Facade against the supported XSDs
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
                'floorNumber' => '12',
                'instructionId' => 'Instruction Identification',
            ]
        );

        $this->dom->loadXML($directDebit->asXML());
        $this->assertValidSchema($this->dom, $schema);
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
                'floorNumber' => '12',
                'instructionId' => 'Instruction Identification',
            ]
        );

        $this->dom->loadXML($directDebit->asXML());
        $this->assertValidSchema($this->dom, $schema);
    }

    public static function provideSchema(): iterable
    {
        return [
            'pain.008.001.02' => ['pain.008.001.02'],
            'pain.008.001.04' => ['pain.008.001.04'],
            'pain.008.001.05' => ['pain.008.001.05'],
            'pain.008.001.06' => ['pain.008.001.06'],
            'pain.008.001.07' => ['pain.008.001.07'],
            'pain.008.001.08' => ['pain.008.001.08'],
            'pain.008.001.09' => ['pain.008.001.09'],
            'pain.008.001.10' => ['pain.008.001.10'],
            'pain.008.001.11' => ['pain.008.001.11'],
            'pain.008.002.02' => ['pain.008.002.02'],
            'pain.008.003.02' => ['pain.008.003.02']
        ];
    }

    /**
     * @dataProvider provideSchemaWithFullAddresses
     */
    public function testAddTransferWithAddress(string $painFormat): void
    {
        $messageFormat = new MessageFormat($painFormat);
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
            'floorNumber'           => '12'
        ]);

        // Test the Transfer Object:
        $transfer = $directDebit->getPaymentInfo('firstPayment')->getTransfers()[0];

        $this->assertSame('CH', $transfer->getCountry());
        $this->assertSame('8245', $transfer->getPostCode());
        $this->assertSame('Feuerthalen', $transfer->getTownName());
        $this->assertSame('Example Street', $transfer->getStreetName());
        $this->assertSame('25', $transfer->getBuildingNumber());
        $this->assertSame('12', $transfer->getFloorNumber());

        // Test the generated XML
        $this->dom->loadXML($directDebit->asXML());

        $xpath = new \DOMXPath($this->dom);
        $xpath->registerNamespace('ns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));
        $postalAddressNode = $xpath->evaluate('/ns:Document/ns:CstmrDrctDbtInitn/ns:PmtInf/ns:DrctDbtTxInf/ns:Dbtr/ns:PstlAdr')->item(0);

        $this->assertNull($xpath->evaluate('./ns:AdrLine', $postalAddressNode)->item(0));
        $this->assertSame('CH', $xpath->evaluate('./ns:Ctry', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('8245', $xpath->evaluate('./ns:PstCd', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Feuerthalen', $xpath->evaluate('./ns:TwnNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('Example Street', $xpath->evaluate('./ns:StrtNm', $postalAddressNode)->item(0)->textContent);
        $this->assertSame('25', $xpath->evaluate('./ns:BldgNb', $postalAddressNode)->item(0)->textContent);
        if ($messageFormat->getVariant() == 1 && $messageFormat->getVersion() >= 8 ) {
            $this->assertSame('12', $xpath->evaluate('./ns:Flr', $postalAddressNode)->item(0)->textContent);
        }
    }

    public static function provideSchemaWithFullAddresses(): iterable
    {
        return [
            'pain.008.001.03' => ['pain.008.001.03'],
            'pain.008.001.04' => ['pain.008.001.04'],
            'pain.008.001.05' => ['pain.008.001.05'],
            'pain.008.001.06' => ['pain.008.001.06'],
            'pain.008.001.07' => ['pain.008.001.07'],
            'pain.008.001.08' => ['pain.008.001.08'],
            'pain.008.001.09' => ['pain.008.001.09'],
            'pain.008.001.10' => ['pain.008.001.10'],
            'pain.008.001.11' => ['pain.008.001.11'],
        ];
    }
}
