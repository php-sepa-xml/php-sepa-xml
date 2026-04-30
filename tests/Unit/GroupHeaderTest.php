<?php

namespace Digitick\Sepa\Tests\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use Digitick\Sepa\GroupHeader;
use PHPUnit\Framework\TestCase;

class GroupHeaderTest extends TestCase
{
    public function testConstructorStoresMessageIdentificationAndInitiatingPartyName(): void
    {
        $gh = new GroupHeader('MSG-42', 'Acme Corp');

        $this->assertSame('MSG-42', $gh->getMessageIdentification());
        $this->assertSame('Acme Corp', $gh->getInitiatingPartyName());
    }

    public function testIsTestDefaultsToFalse(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');

        $this->assertFalse($gh->getIsTest());
    }

    public function testIsTestFlagHonouredByConstructor(): void
    {
        $gh = new GroupHeader('MSG', 'Acme', true);

        $this->assertTrue($gh->getIsTest());
    }

    public function testSetIsTestOverridesConstructorValue(): void
    {
        $gh = new GroupHeader('MSG', 'Acme', false);
        $gh->setIsTest(true);

        $this->assertTrue($gh->getIsTest());
    }

    public function testCreationDateTimeIsDateTimeImmutable(): void
    {
        $before = new DateTimeImmutable();
        $gh = new GroupHeader('MSG', 'Acme');
        $after = new DateTimeImmutable();

        $created = $gh->getCreationDateTime();

        $this->assertInstanceOf(DateTimeImmutable::class, $created);
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $created->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $created->getTimestamp());
    }

    public function testCreationDateTimeFormatDefaultsToRfc3339(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');

        $this->assertSame(DateTimeInterface::RFC3339, $gh->getCreationDateTimeFormat());
    }

    public function testCreationDateTimeFormatIsOverridable(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');
        $gh->setCreationDateTimeFormat('Y-m-d\TH:i:s.v\Z');

        $this->assertSame('Y-m-d\TH:i:s.v\Z', $gh->getCreationDateTimeFormat());
    }

    public function testInitiatingPartyNameIsSanitizedOnConstruction(): void
    {
        // The default sanitizer strips characters outside the SEPA-allowed
        // ASCII set and transliterates common accents.
        $gh = new GroupHeader('MSG', 'Müller & Söhne');

        $this->assertSame('Mueller   Soehne', $gh->getInitiatingPartyName());
    }

    public function testSetInitiatingPartyNameSanitizes(): void
    {
        $gh = new GroupHeader('MSG', 'Placeholder');
        $gh->setInitiatingPartyName('Jörg Händel');

        $this->assertSame('Joerg Haendel', $gh->getInitiatingPartyName());
    }

    public function testSetInitiatingPartyIdentificationSchemeSanitizes(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');
        $gh->setInitiatingPartyIdentificationScheme('SEPA&');

        $this->assertSame('SEPA ', $gh->getInitiatingPartyIdentificationScheme());
    }

    public function testInitiatingPartyIdIsNullByDefault(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');

        $this->assertNull($gh->getInitiatingPartyId());
    }

    public function testSetInitiatingPartyIdStoresAsIs(): void
    {
        // setInitiatingPartyId does not run through the sanitizer; it is
        // expected to be a controlled identifier.
        $gh = new GroupHeader('MSG', 'Acme');
        $gh->setInitiatingPartyId('DE67ZZZ00000123456');

        $this->assertSame('DE67ZZZ00000123456', $gh->getInitiatingPartyId());
    }

    public function testIssuerDefaultsToNull(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');

        $this->assertNull($gh->getIssuer());
    }

    public function testSetIssuer(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');
        $gh->setIssuer('Some Bank');

        $this->assertSame('Some Bank', $gh->getIssuer());
    }

    public function testTransactionCountersDefaultToZero(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');

        $this->assertSame(0, $gh->getNumberOfTransactions());
        $this->assertSame(0, $gh->getControlSumCents());
    }

    public function testTransactionCountersAreSettable(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');
        $gh->setNumberOfTransactions(7);
        $gh->setControlSumCents(12345);

        $this->assertSame(7, $gh->getNumberOfTransactions());
        $this->assertSame(12345, $gh->getControlSumCents());
    }

    public function testSetMessageIdentificationOverride(): void
    {
        $gh = new GroupHeader('MSG', 'Acme');
        $gh->setMessageIdentification('NEW-ID');

        $this->assertSame('NEW-ID', $gh->getMessageIdentification());
    }
}
