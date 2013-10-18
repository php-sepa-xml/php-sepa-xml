<?php

namespace Digitick\Sepa\TransferFile\Factory;
use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;

/**
 * SEPA file generator.
 *
 * @copyright © Blage <www.blage.net> 2013
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

class TransferFileFacadeFactory {

    /**
     * @param string $uniqueMessageIdentification
     * @param string $initiatingPartyName
     *
     * @return CustomerDirectDebitFacade
     */
    public static function createDirectDebit($uniqueMessageIdentification, $initiatingPartyName) {
        $groupHeader = new GroupHeader($uniqueMessageIdentification, $initiatingPartyName);
        $directDebitTransferFile = new CustomerDirectDebitTransferFile($groupHeader);
        $domBuilder = new CustomerDirectDebitTransferDomBuilder();
        return new CustomerDirectDebitFacade($directDebitTransferFile, $domBuilder);
    }

    /**
     * @param $uniqueMessageIdentification
     * @param $initiatingPartyName
     *
     * @return CustomerCreditFacade
     */
    public static function createCustomerCredit($uniqueMessageIdentification, $initiatingPartyName) {
        $groupHeader = new GroupHeader($uniqueMessageIdentification, $initiatingPartyName);
        $directDebitTransferFile = new CustomerCreditTransferFile($groupHeader);
        $domBuilder = new CustomerCreditTransferDomBuilder();
        return new CustomerCreditFacade($directDebitTransferFile, $domBuilder);
    }
}