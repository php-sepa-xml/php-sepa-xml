<?php

namespace Digitick\Sepa\Tests\Unit\TransferFile\Facade;

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerCreditFacadeTest
 */
class CustomerCreditFacadeTest extends TestCase
{
    /**
     * Test creation of file via Factory and Facade
     *
     * @param string $schema
     *
     * @dataProvider schemaProvider
     */
    public function testValidFileCreationWithFacade($schema)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', $schema);
        $paymentInformation = $credit->addPaymentInfo(
            'firstPayment',
            array(
                'id' => 'firstPayment',
                'debtorName' => 'My Company',
                'debtorAccountIBAN' => 'FI1350001540000056',
                'debtorAgentBIC' => 'PSSTFRPPMON'
            )
        );
        $paymentInformation->setBatchBooking(true);

        $credit->addTransfer(
            'firstPayment',
            array(
                'amount' => 500,
                'creditorIban' => 'FI1350001540000056',
                'creditorBic' => 'OKOYFIHH',
                'creditorName' => 'Their Company',
                'remittanceInformation' => 'Purpose of this credit'
            )
        );

        $dom->loadXML($credit->asXML());
        $this->assertTrue($dom->schemaValidate(__DIR__ . "/../../../fixtures/" . $schema . ".xsd"));
    }

    /**
     * Test creation of file via Factory and Facade
     *
     * @param string $schema
     *
     * @dataProvider schemaProviderEmptyBic
     */
    public function testValidFileCreationWithFacadeWithoutDebtorBic($schema)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', $schema);
        $paymentInformation = $credit->addPaymentInfo(
            'firstPayment',
            array(
                'id' => 'firstPayment',
                'debtorName' => 'My Company',
                'debtorAccountIBAN' => 'FI1350001540000056'
            )
        );
        $paymentInformation->setBatchBooking(true);

        $credit->addTransfer(
            'firstPayment',
            array(
                'amount' => 500,
                'creditorIban' => 'FI1350001540000056',
                'creditorName' => 'Their Company',
                'remittanceInformation' => 'Purpose of this credit'
            )
        );

        $dom->loadXML($credit->asXML());
        $this->assertTrue($dom->schemaValidate(__DIR__ . "/../../../fixtures/" . $schema . ".xsd"));
    }

    /**
     * @return array
     */
    public function schemaProvider()
    {
        return array(
            array("pain.001.001.03"),
            array("pain.001.002.03"),
            array("pain.001.003.03")
        );
    }

    /**
     * @return array
     */
    public function schemaProviderEmptyBic()
    {
        return array(
            array("pain.001.001.03"),
            array("pain.001.003.03")
        );
    }
}
