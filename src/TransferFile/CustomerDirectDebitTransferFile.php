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

namespace PhpSepaXml\TransferFile;

use PhpSepaXml\Exception\InvalidTransferFileConfiguration;
use PhpSepaXml\Exception\InvalidTransferTypeException;
use PhpSepaXml\PaymentInformation;
use PhpSepaXml\TransferInformation\CustomerDirectDebitTransferInformation;

class CustomerDirectDebitTransferFile extends BaseTransferFile
{
    /**
     * @param PaymentInformation $paymentInformation
     */
    public function addPaymentInformation(PaymentInformation $paymentInformation)
    {
        $paymentInformation->setValidPaymentMethods(array('DD'));
        $paymentInformation->setPaymentMethod('DD');
        parent::addPaymentInformation($paymentInformation);
    }

    /**
     * validate the transferfile
     *
     * @throws \PhpSepaXml\Exception\InvalidTransferTypeException
     */
    public function validate()
    {
        parent::validate();
        /** @var $payment PaymentInformation */
        foreach ($this->paymentInformations as $payment) {
            if ((string)$payment->getSequenceType() === '') {
                throw new InvalidTransferFileConfiguration('Payment must contain a SequenceType');
            }
            if ((string)$payment->getCreditorId() === '') {
                throw new InvalidTransferFileConfiguration('Payment must contain a CreditorSchemeId');
            }
            foreach ($payment->getTransfers() as $transfer) {
                if (!$transfer instanceof CustomerDirectDebitTransferInformation) {
                    throw new InvalidTransferTypeException('Transfers must be of type \PhpSepaXml\TransferInformation\CustomerDirectDebitTransferInformation instead of: ' . get_class(
                        $transfer
                    ));
                }
            }
        }
    }
}
