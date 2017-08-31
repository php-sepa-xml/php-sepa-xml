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

namespace Digitick\Sepa\TransferFile\Facade;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;

class CustomerDirectDebitFacade extends BaseCustomerTransferFileFacade
{
    /**
     * @param $paymentName
     * @param array $paymentInformation
     *     - id
     *     - creditorName
     *     - creditorAccountIBAN
     *     - creditorAgentBIC
     *     - seqType
     *     - creditorId
     *     - [dueDate] if not set: now + 5 days
     *
     * @throws \Digitick\Sepa\Exception\InvalidArgumentException
     *
     * @return PaymentInformation
     */
    public function addPaymentInfo($paymentName, array $paymentInformation)
    {
        if (isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf('Payment with the name %s already exists', $paymentName));
        }
        $payment = new PaymentInformation(
            $paymentInformation['id'],
            $paymentInformation['creditorAccountIBAN'],
            $paymentInformation['creditorAgentBIC'],
            $paymentInformation['creditorName']
        );
        $payment->setSequenceType($paymentInformation['seqType']);
        $payment->setCreditorId($paymentInformation['creditorId']);
        if (isset($paymentInformation['localInstrumentCode'])) {
            $payment->setLocalInstrumentCode($paymentInformation['localInstrumentCode']);
        }
        if (isset($paymentInformation['dueDate'])) {
            if ($paymentInformation['dueDate'] instanceof \DateTime) {
                $payment->setDueDate($paymentInformation['dueDate']);
            } else {
                $payment->setDueDate(new \DateTime($paymentInformation['dueDate']));
            }
        } else {
            $payment->setDueDate(new \DateTime(date('Y-m-d', strtotime('now + 5 days'))));
        }

        $this->payments[$paymentName] = $payment;

        return $payment;
    }

    /**
     * @param $paymentName
     * @param array $transferInformation
     *      - amount
     *      - debtorIban
     *      - debtorBic
     *      - debtorName
     *      - debtorMandate
     *      - debtorMandateSignDate
     *      - remittanceInformation
     *      - [endToEndId]
     *      - [amendments]
     *
     * @throws \Digitick\Sepa\Exception\InvalidArgumentException
     *
     * @return TransferInformationInterface
     */
    public function addTransfer($paymentName, array $transferInformation)
    {
        if (!isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf(
                'Payment with the name %s does not exists, create one first with addPaymentInfo',
                $paymentName
            ));
        }
        $transfer = new CustomerDirectDebitTransferInformation(
            $transferInformation['amount'],
            $transferInformation['debtorIban'],
            $transferInformation['debtorName']
        );
        $transfer->setBic($transferInformation['debtorBic']);
        $transfer->setMandateId($transferInformation['debtorMandate']);
        if ($transferInformation['debtorMandateSignDate'] instanceof \DateTime) {
            $transfer->setMandateSignDate($transferInformation['debtorMandateSignDate']);
        } else {
            $transfer->setMandateSignDate(new \DateTime($transferInformation['debtorMandateSignDate']));
        }
        $transfer->setRemittanceInformation($transferInformation['remittanceInformation']);
        if (isset($transferInformation['endToEndId'])) {
            $transfer->setEndToEndIdentification($transferInformation['endToEndId']);
        } else {
            $transfer->setEndToEndIdentification(
                $this->payments[$paymentName]->getId() . count($this->payments[$paymentName]->getTransfers())
            );
        }
        if (isset($transferInformation['originalMandateId'])) {
            $transfer->setOriginalMandateId($transferInformation['originalMandateId']);
        }
        if (isset($transferInformation['originalDebtorIban'])) {
            $transfer->setOriginalDebtorIban($transferInformation['originalDebtorIban']);
        }
        if (isset($transferInformation['amendedDebtorAgent'])) {
            $transfer->setAmendedDebtorAgent((bool)$transferInformation['amendedDebtorAgent']);
        }

        $this->payments[$paymentName]->addTransfer($transfer);

        return $transfer;
    }
}
