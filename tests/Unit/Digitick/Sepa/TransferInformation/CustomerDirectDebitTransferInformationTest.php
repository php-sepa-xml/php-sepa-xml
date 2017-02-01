<?php
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

class CustomerDirectDebitTransferInformationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function hasAmendmentReturnsTrueForAmendments()
    {
        $transferInformation = new \Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation(
            100,
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
}
