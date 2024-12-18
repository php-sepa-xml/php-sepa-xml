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

namespace Digitick\Sepa\TransferFile;

use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\Exception\InvalidTransferTypeException;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;

class CustomerCreditTransferFile extends BaseTransferFile
{
    public function addPaymentInformation(PaymentInformation $paymentInformation): void
    {
        $paymentInformation->setValidPaymentMethods(array('TRF'));
        $paymentInformation->setPaymentMethod('TRF');
        parent::addPaymentInformation($paymentInformation);
    }

    /**
     * Validate the transferfile
     *
     * @throws InvalidTransferFileConfiguration
     * @throws InvalidTransferTypeException
     */
    public function validate(): void
    {
        parent::validate();

        foreach ($this->paymentInformations as $payment) {
            if (count($payment->getTransfers()) === 0) {
                throw new InvalidTransferFileConfiguration('PaymentInformation must at least contain one payment');
            }
            foreach ($payment->getTransfers() as $transfer) {
                if (!$transfer instanceof CustomerCreditTransferInformation) {
                    throw new InvalidTransferTypeException(sprintf(
                        'Transfers must be of type CustomerCreditTransferInformation instead of: %s',
                        get_class($transfer)
                    ));
                }
            }
        }
    }
}
