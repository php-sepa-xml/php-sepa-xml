<?php

namespace Digitick\Sepa\Tests\Unit\TransferInformation;

use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CustomerCreditTransferInformationTest extends TestCase
{
    /**
     * Tests whether the EndToEndId equals the name if no other identifier was supplied
     */
    public function testEndToEndIndentifierEqualsName(): void
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $this->assertEquals('Their Corp', $information->getEndToEndIdentification());
    }

    public function testHasUniqueIdentifier(): void
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $this->assertNotEmpty($information->getUUID());
        $this->assertTrue(Uuid::isValid($information->getUUID()));
    }

    public function testCustomUniqueIdentifier(): void
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $uuid = Uuid::uuid4();
        $information->setUUID($uuid);

        $this->assertSame((string) $uuid, $information->getUUID());
        $this->assertTrue(Uuid::isValid($information->getUUID()));
    }

    /**
     * Tests whether the EndToEndId equals the supplied EndToEndId
     */
    public function testOptionalEndToEndIdentifier(): void
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp', 'MyEndToEndId');
        $this->assertEquals('MyEndToEndId', $information->getEndToEndIdentification());
    }

    public function testIntAreAccepted(): void
    {
        $transfer = new CustomerCreditTransferInformation(
            19,
            'IbanOfDebitor',
            'DebitorName'
        );

        $this->assertEquals(19, $transfer->getTransferAmount());
    }
}
