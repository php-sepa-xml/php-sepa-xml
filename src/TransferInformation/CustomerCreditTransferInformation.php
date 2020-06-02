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

namespace Digitick\Sepa\TransferInformation;

class CustomerCreditTransferInformation extends BaseTransferInformation
{
    /**
     * @param string $amount
     * @param string $iban
     * @param string $name
     * @param string $identification
     */
    public function __construct($amount, $iban, $name, $identification = null)
    {
        parent::__construct($amount, $iban, $name);

        if (null === $identification) {
            $identification = $name;
        }

        $this->setEndToEndIdentification($identification);
    }

    /**
     * @return string
     */
    public function getCreditorName()
    {
        return $this->name;
    }
}
