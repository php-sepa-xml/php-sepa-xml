<?php

namespace Digitick\Sepa\Tests\Unit\TransferInformation;

use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\Util\Sanitizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CustomerCreditTransferInformationTest extends TestCase
{
    protected function tearDown(): void
    {
        Sanitizer::resetSanitizer();
    }

    /**
     * Tests whether the EndToEndId equals the name if no other identifier was supplied
     */
    public function testEndToEndIndentifierEqualsName(): void
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $this->assertEquals('Their Corp', $information->getEndToEndIdentification());
    }

    public function testUniqueIdentifierNullByDefault(): void
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $this->assertNull($information->getUUID());
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

    public function testSetRemittanceInformationRunsSanitizer(): void
    {
        Sanitizer::setSanitizer(static fn (string $value): string => strtoupper($value));

        $transfer = new CustomerCreditTransferInformation(100, 'DE12500105170648489890', 'Their Corp');
        $transfer->setRemittanceInformation('invoice 42');

        $this->assertSame(
            'INVOICE 42',
            $transfer->getRemittanceInformation(),
            'setRemittanceInformation must route through Sanitizer::sanitize() so the global sanitization strategy applies uniformly'
        );
    }

    public function testSetCreditorReferenceRunsSanitizer(): void
    {
        Sanitizer::setSanitizer(static fn (string $value): string => strtoupper($value));

        $transfer = new CustomerCreditTransferInformation(100, 'DE12500105170648489890', 'Their Corp');
        $transfer->setCreditorReference('rf18-539007547034');

        $this->assertSame(
            'RF18-539007547034',
            $transfer->getCreditorReference(),
            'setCreditorReference must route through Sanitizer::sanitize() so the global sanitization strategy applies uniformly'
        );
    }
}
