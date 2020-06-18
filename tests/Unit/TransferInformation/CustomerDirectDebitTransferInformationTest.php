<?php

namespace Digitick\Sepa\Tests\Unit\TransferInformation;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use PHPUnit\Framework\TestCase;

/**
 * SEPA file generator.
 *
 * @copyright © Blage <www.blage.net> 2015
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

class CustomerDirectDebitTransferInformationTest extends TestCase
{
    /**
     * Tests whether the EndToEndId equals the name if no other identifier was supplied
     */
    public function testEndToEndIndentifierEqualsName()
    {
        $information = new CustomerDirectDebitTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $this->assertEquals('Their Corp', $information->getEndToEndIdentification());
    }

    /**
     * Tests whether the EndToEndId equals the supplied EndToEndId
     */
    public function testOptionalEndToEndIdentifier()
    {
        $information = new CustomerDirectDebitTransferInformation('100', 'DE12500105170648489890', 'Their Corp', 'MyEndToEndId');
        $this->assertEquals('MyEndToEndId', $information->getEndToEndIdentification());
    }

    public function testHasAmendmentReturnsTrueForAmendments()
    {
        $transferInformation = new CustomerDirectDebitTransferInformation(
            '100',
            'DE89370400440532013000',
            'Me'
        );
        $this->assertFalse($transferInformation->hasAmendments());

        $transferInformation->setAmendedDebtorAccount(true);
        $this->assertTrue($transferInformation->hasAmendments());

        $transferInformation->setAmendedDebtorAccount(false);
        $transferInformation->setOriginalDebtorIban('DE89370400440532013000');
        $this->assertTrue($transferInformation->hasAmendments());
    }

    public function testIntAsStringAreAccepted()
    {
        $transfer = new CustomerDirectDebitTransferInformation(
            '19',
            'IbanOfDebitor',
            'DebitorName'
        );

        $this->assertEquals(19, $transfer->getTransferAmount());
    }

    public function testIntAreAccepted()
    {
        $transfer = new CustomerDirectDebitTransferInformation(
            19,
            'IbanOfDebitor',
            'DebitorName'
        );

        $this->assertEquals(19, $transfer->getTransferAmount());
    }

    public function testFloatsAsStringAreAcceptedIfBcMathExtensionIsAvailable()
    {
        if (!function_exists('bcscale')) {
            $this->markTestSkipped('no bcmath extension available');
        }
        $transfer = new CustomerDirectDebitTransferInformation(
            '19.999',
            'IbanOfDebitor',
            'DebitorName'
        );

        $this->assertEquals(1999, $transfer->getTransferAmount());
    }

    public function testFloatsAreAcceptedIfBcMathExtensionIsAvailable()
    {
        if (!function_exists('bcscale')) {
            $this->markTestSkipped('no bcmath extension available');
        }
        $transfer = new CustomerDirectDebitTransferInformation(
            19.999,
            'IbanOfDebitor',
            'DebitorName'
        );

        $this->assertEquals(1999, $transfer->getTransferAmount());
    }

    public function testExceptionIsThrownIfBcMathExtensionIsNotAvailableAndInputIsFloat()
    {
        $this->expectException(InvalidArgumentException::class);

        if (function_exists('bcscale')) {
            $this->markTestSkipped('bcmath extension available, not possible to test exceptions');
        }
        $transfer = new CustomerDirectDebitTransferInformation(
            '19.999',
            'IbanOfDebitor',
            'DebitorName'
        );

        $this->assertEquals(1999, $transfer->getTransferAmount());
    }
}
