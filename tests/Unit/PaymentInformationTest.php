<?php
/**
 * Date: 01.09.17
 * copyright 2017 blage.net Sören Rohweder
 */

namespace Digitick\Sepa\Tests\Unit;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
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

    public function testSetPaymentMethodThrowsWhenValidMethodsEmpty(): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');

        // validPaymentMethods defaults to [], so every value is rejected
        // until the owning TransferFile configures the allow-list.
        $this->expectException(InvalidArgumentException::class);
        $pi->setPaymentMethod('TRF');
    }

    public function testSetPaymentMethodThrowsForMethodOutsideAllowList(): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');
        $pi->setValidPaymentMethods(['TRF']);

        $this->expectException(InvalidArgumentException::class);
        $pi->setPaymentMethod('DD');
    }

    public function testSetPaymentMethodAcceptsAllowedValueAndNormalisesCase(): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');
        $pi->setValidPaymentMethods(['TRF']);

        $pi->setPaymentMethod('trf');

        $this->assertSame('TRF', $pi->getPaymentMethod());
    }

    /**
     * @dataProvider invalidLocalInstrumentCodeProvider
     */
    public function testSetLocalInstrumentCodeThrowsForInvalid(string $code): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');

        $this->expectException(InvalidArgumentException::class);
        $pi->setLocalInstrumentCode($code);
    }

    public static function invalidLocalInstrumentCodeProvider(): iterable
    {
        return [
            'empty'       => [''],
            'unknown'     => ['XYZ'],
            'typo'        => ['CORE1'],
            'close-match' => ['B2C'],
        ];
    }

    /**
     * @dataProvider validLocalInstrumentCodeProvider
     */
    public function testSetLocalInstrumentCodeAcceptsValidValuesCaseInsensitively(string $input, string $stored): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');
        $pi->setLocalInstrumentCode($input);

        $this->assertSame($stored, $pi->getLocalInstrumentCode());
    }

    public static function validLocalInstrumentCodeProvider(): iterable
    {
        return [
            'B2B'        => ['B2B', 'B2B'],
            'CORE'       => ['CORE', 'CORE'],
            'COR1'       => ['COR1', 'COR1'],
            'lower b2b'  => ['b2b', 'B2B'],
            'lower core' => ['core', 'CORE'],
        ];
    }

    public function testSetInstructionPriorityThrowsForInvalid(): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');

        $this->expectException(InvalidArgumentException::class);
        $pi->setInstructionPriority('URGENT');
    }

    /**
     * @dataProvider validInstructionPriorityProvider
     */
    public function testSetInstructionPriorityAcceptsValidValuesCaseInsensitively(string $input, string $stored): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');
        $pi->setInstructionPriority($input);

        $this->assertSame($stored, $pi->getInstructionPriority());
    }

    public static function validInstructionPriorityProvider(): iterable
    {
        return [
            'NORM'       => ['NORM', 'NORM'],
            'HIGH'       => ['HIGH', 'HIGH'],
            'lower norm' => ['norm', 'NORM'],
            'lower high' => ['high', 'HIGH'],
        ];
    }

    public function testAddTransferAccumulatesNumberOfTransactionsAndControlSum(): void
    {
        $pi = new PaymentInformation('1', 'DE12', 'BIC', 'Jon Doe');

        $this->assertSame(0, $pi->getNumberOfTransactions());
        $this->assertSame(0, $pi->getControlSumCents());

        $pi->addTransfer(new CustomerCreditTransferInformation(100, 'DE12', 'A'));
        $pi->addTransfer(new CustomerCreditTransferInformation(250, 'DE12', 'B'));
        $pi->addTransfer(new CustomerCreditTransferInformation(7, 'DE12', 'C'));

        $this->assertSame(3, $pi->getNumberOfTransactions());
        $this->assertSame(357, $pi->getControlSumCents());
        $this->assertCount(3, $pi->getTransfers());
    }
}
