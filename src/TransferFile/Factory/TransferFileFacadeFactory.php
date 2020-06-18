<?php
/**
 * SEPA file generator.
 *
 * @copyright © Digitick <www.digitick.net> 2012-2013
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

namespace Digitick\Sepa\TransferFile\Factory;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\Facade\CustomerCreditFacade;
use Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;

class TransferFileFacadeFactory
{
    /**
     * @param string $uniqueMessageIdentification Maximum length: 35. Reference Number of the bulk.
     *                                            Part of the duplication check (unique daily reference).
     *                                            The first 8 or 11 characters of <Msgld> must match the BIC of the
     *                                            Instructing Agent. The rest of the field can be freely defined.
     */
    public static function createDirectDebit(string $uniqueMessageIdentification, string $initiatingPartyName, string $painFormat = 'pain.008.002.02'): CustomerDirectDebitFacade
    {
        $groupHeader = new GroupHeader($uniqueMessageIdentification, $initiatingPartyName);

        return self::createDirectDebitWithGroupHeader($groupHeader, $painFormat);
    }

    public static function createDirectDebitWithGroupHeader(GroupHeader $groupHeader, string $painFormat = 'pain.008.002.02'): CustomerDirectDebitFacade
    {
        return new CustomerDirectDebitFacade(new CustomerDirectDebitTransferFile($groupHeader), new CustomerDirectDebitTransferDomBuilder($painFormat));
    }

    public static function createCustomerCredit(string $uniqueMessageIdentification, string $initiatingPartyName, string $painFormat = 'pain.001.002.03'): CustomerCreditFacade
    {
        $groupHeader = new GroupHeader($uniqueMessageIdentification, $initiatingPartyName);

        return self::createCustomerCreditWithGroupHeader($groupHeader, $painFormat);
    }

    public static function createCustomerCreditWithGroupHeader(GroupHeader $groupHeader, string $painFormat = 'pain.001.002.03'): CustomerCreditFacade
    {
        return new CustomerCreditFacade(new CustomerCreditTransferFile($groupHeader), new CustomerCreditTransferDomBuilder($painFormat));
    }
}
