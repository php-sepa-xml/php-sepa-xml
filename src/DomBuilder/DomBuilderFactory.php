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

namespace Digitick\Sepa\DomBuilder;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\TransferFile\TransferFileInterface;

class DomBuilderFactory
{
    /**
     * @throws InvalidArgumentException
     **/
    public static function createDomBuilder(TransferFileInterface $transferFile, string $painFormat = '', $withSchemaLocation = true): DomBuilderInterface
    {
        $transferFileClass = get_class($transferFile);
        switch ($transferFileClass) {
            case 'Digitick\Sepa\TransferFile\CustomerCreditTransferFile':
                $domBuilder = $painFormat ? new CustomerCreditTransferDomBuilder($painFormat, $withSchemaLocation) : new CustomerCreditTransferDomBuilder();
                $transferFile->accept($domBuilder);
                break;
            case 'Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile':
                $domBuilder = $painFormat ? new CustomerDirectDebitTransferDomBuilder($painFormat, $withSchemaLocation) : new CustomerDirectDebitTransferDomBuilder();
                $transferFile->accept($domBuilder);
                break;
            default:
                throw new InvalidArgumentException('The given object is not a valid Transferfile: ' . $transferFileClass);
        }

        return $domBuilder;
    }
}
