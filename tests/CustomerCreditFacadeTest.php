<?php

namespace Tests;

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

/**
 * Class CustomerCreditFacadeTest
 */
class CustomerCreditFacadeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->schema = __DIR__ . "/pain.001.003.03.xsd";
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Test creation of file via Factory and Facade
     */
    public function testValidFileCreationWithFacade()
    {
        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me');
        $credit->addPaymentInfo(
            'firstPayment',
            array(
                'id' => 'firstPayment',
                'debtorName' => 'My Company',
                'debtorAccountIBAN' => 'FI1350001540000056',
                'debtorAgentBIC' => 'PSSTFRPPMON'
            )
        );
        $credit->addTransfer(
            'firstPayment',
            array(
                'amount' => '500',
                'creditorIban' => 'FI1350001540000056',
                'creditorBic' => 'OKOYFIHH',
                'creditorName' => 'Their Company',
                'remittanceInformation' => 'Purpose of this credit'
            )
        );

        $this->dom->loadXML($credit->asXML());
        $this->assertTrue($this->dom->schemaValidate($this->schema));
    }
}
