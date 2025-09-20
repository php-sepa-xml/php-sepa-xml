<?php

namespace Digitick\Sepa\Tests\Unit\TransferFile\Facade;

use Digitick\Sepa\Tests\TestCase;
use \DomDocument;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

/**
 * Class CustomerCreditFacadeTest
 */
class CustomerCreditFacadeTest extends TestCase
{
    /**
     * Test creation of file via Factory and Facade
     *
     * @dataProvider schemaProvider
     */
    public function testValidFileCreationWithFacade(string $schema): void
    {
        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', $schema);
        $paymentInformation = $credit->addPaymentInfo(
            'firstPayment',
            [
                'id' => 'firstPayment',
                'debtorName' => 'My Company',
                'debtorAccountIBAN' => 'FI1350001540000056',
                'debtorAgentBIC' => 'PSSTFRPPMON'
            ]
        );
        $paymentInformation->setBatchBooking(true);

        $credit->addTransfer(
            'firstPayment',
            [
                'amount' => 500,
                'creditorIban' => 'FI1350001540000056',
                'creditorBic' => 'OKOYFIHH',
                'creditorName' => 'Their Company',
                'remittanceInformation' => 'Purpose of this credit',
                'instructionId' => 'Instruction Identification',
            ]
        );

        $xml = $credit->asXML();
        $this->assertInstanceOf(DomDocument::class, $credit->asDOC());

        $domDoc = new DOMDocument('1.0', 'UTF-8');
        $domDoc->loadXML($xml);
        $this->assertValidSchema($domDoc, $schema);

    }

    /**
     * Test creation of file via Factory and Facade
     *
     * @dataProvider schemaProviderEmptyBic
     */
    public function testValidFileCreationWithFacadeWithoutDebtorBic(string $schema): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        $credit = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', $schema);
        $paymentInformation = $credit->addPaymentInfo(
            'firstPayment',
            [
                'id' => 'firstPayment',
                'debtorName' => 'My Company',
                'debtorAccountIBAN' => 'FI1350001540000056'
            ]
        );
        $paymentInformation->setBatchBooking(true);

        $credit->addTransfer(
            'firstPayment',
            [
                'amount' => 500,
                'creditorIban' => 'FI1350001540000056',
                'creditorName' => 'Their Company',
                'remittanceInformation' => 'Purpose of this credit',
                'instructionId' => 'Instruction Identification',
            ]
        );

        $dom->loadXML($credit->asXML());
        $this->assertValidSchema($dom, $schema);
    }

    public static function schemaProvider(): iterable
    {
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
            'pain.001.001.04' => ['pain.001.001.04'],
            'pain.001.001.05' => ['pain.001.001.05'],
            'pain.001.001.06' => ['pain.001.001.06'],
            'pain.001.001.07' => ['pain.001.001.07'],
            'pain.001.001.08' => ['pain.001.001.08'],
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.12' => ['pain.001.001.12'],
            'pain.001.002.03' => ['pain.001.002.03'],
            'pain.001.003.03' => ['pain.001.003.03']
        ];
    }

    public static function schemaProviderEmptyBic(): iterable
    {
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
            'pain.001.001.04' => ['pain.001.001.04'],
            'pain.001.001.05' => ['pain.001.001.05'],
            'pain.001.001.06' => ['pain.001.001.06'],
            'pain.001.001.07' => ['pain.001.001.07'],
            'pain.001.001.08' => ['pain.001.001.08'],
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.11' => ['pain.001.001.12'],
            'pain.001.003.03' => ['pain.001.003.03']
        ];
    }
}
