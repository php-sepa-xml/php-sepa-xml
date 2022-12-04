<?php
/**
 * Date: 01.09.17
 * copyright 2017 blage.net Sören Rohweder
 */

namespace Digitick\Sepa\Tests\Unit;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\PaymentInformation;
use PHPUnit\Framework\TestCase;

class PaymentInformationTest extends TestCase
{
    public function testDateIsReturnedInDefaultFormat(): void
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setDueDate(new \DateTime('2017-08-31 12:13:14'));
        $this->assertEquals('2017-08-31', $pi->getDueDate());
    }

    public function testDateIsReturnedWithGivenFormat(): void
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setDueDate(new \DateTime('2017-08-31 12:13:14'));
        $pi->setDueDateFormat('d.m.Y');
        $this->assertEquals('31.08.2017', $pi->getDueDate());
    }

    public function testDefaultServiceLevelCodeIsSepa()
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $this->assertEquals('SEPA', $pi->getServiceLevelCode());
    }

    public function testWeCanChangeTheServiceLevelCodeToNotUrgent()
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setServiceLevelCode('NURG');
        $this->assertEquals('NURG', $pi->getServiceLevelCode());
    }

    public function testWeGetAnExceptionIfTheServiceLevelCodeIsIncorrect()
    {
        $this->expectException(InvalidArgumentException::class);
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setServiceLevelCode('JURG');
    }

    public function testDefaultChargeBearerIsSlev()
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $this->assertEquals('SLEV', $pi->getChargeBearer());
    }

    public function testWeCanChangeTheChargeBearer()
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setChargeBearer('SHAR');
        $this->assertEquals('SHAR', $pi->getChargeBearer());
    }

    public function testWeCanSetTheChargeBearerToNull()
    {
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setChargeBearer();
        $this->assertNull($pi->getChargeBearer());
    }

    public function testWeGetAnExceptionIfTheChargeBearerIsIncorrect()
    {
        $this->expectException(InvalidArgumentException::class);
        $pi = new PaymentInformation('1', 'DE121212121212121212', 'DE1212121212121212', 'Jon Doe');
        $pi->setChargeBearer('JURG');
    }
}
