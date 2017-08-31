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

use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

class CustomerDirectDebitFacadeTest extends \PHPUnit_Framework_TestCase
{
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp()
    {
        $this->schema = __DIR__ . "/pain.008.002.02.xsd";
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Test creation of file via Factory and Facade
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testValidFileCreationWithFacade($schema)
    {
        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', $schema);
        $paymentInformation = $directDebit->addPaymentInfo(
            'firstPayment',
            array(
                'id' => 'firstPayment',
                'creditorName' => 'My Company',
                'creditorAccountIBAN' => 'FI1350001540000056',
                'creditorAgentBIC' => 'PSSTFRPPMON',
                'seqType' => PaymentInformation::S_ONEOFF,
                'creditorId' => 'DE21WVM1234567890'
            )
        );
        $paymentInformation->setBatchBooking(true);

        $directDebit->addTransfer(
            'firstPayment',
            array(
                'amount' => '500',
                'debtorIban' => 'FI1350001540000056',
                'debtorBic' => 'OKOYFIHH',
                'debtorName' => 'Their Company',
                'debtorMandate' => 'AB12345',
                'debtorMandateSignDate' => '13.10.2012',
                'remittanceInformation' => 'Purpose of this direct debit'
            )
        );

        $this->dom->loadXML($directDebit->asXML());
        $this->assertTrue($this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd'));
    }


    /**
     * Test creation of file via Factory and Facade
     *
     * @param string $schema
     *
     * @dataProvider provideSchema
     */
    public function testValidFileCreationWithFacadeWithoutBic($schema)
    {
        if ($schema === 'pain.008.002.02') {
            $this->markTestSkipped('Will fail for this schema as the BIC is required');
        }

        $directDebit = TransferFileFacadeFactory::createDirectDebit('test123', 'Me', $schema);
        $paymentInformation = $directDebit->addPaymentInfo(
            'firstPayment',
            array(
                'id' => 'firstPayment',
                'creditorName' => 'My Company',
                'creditorAccountIBAN' => 'FI1350001540000056',
                'seqType' => PaymentInformation::S_ONEOFF,
                'creditorId' => 'DE21WVM1234567890'
            )
        );
        $paymentInformation->setBatchBooking(true);

        $directDebit->addTransfer(
            'firstPayment',
            array(
                'amount' => '500',
                'debtorIban' => 'FI1350001540000056',
                'debtorName' => 'Their Company',
                'debtorMandate' => 'AB12345',
                'debtorMandateSignDate' => '13.10.2012',
                'creditorReference' => 'RF81123453'
            )
        );

        $this->dom->loadXML($directDebit->asXML());
        $this->assertTrue($this->dom->schemaValidate(__DIR__ . '/' . $schema . '.xsd'));
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