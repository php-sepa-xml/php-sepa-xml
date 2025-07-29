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
     * @param string $paymentName
     * @param array $paymentInformation
     *
     * @struct $paymentInformation {
     *    @type string $id
     *    @type string $creditorName
     *    @type string $creditorAccountIBAN
     *    @type string $creditorAgentBIC
     *    @type string $seqType
     *    @type string $creditorId
     *    @type string $localInstrumentCode
     *    @type boolean $batchBooking
     *    @type string|\DateTime $dueDate
     * }
     *
     * @throws InvalidArgumentException
     * @return PaymentInformation
     */
    public function addPaymentInfo(string $paymentName, array $paymentInformation): PaymentInformation
    {
        if (isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf('Payment with the name %s already exists', $paymentName));
        }
        $creditorAgentBIC = $paymentInformation['creditorAgentBIC'] ?? null;
        $payment = new PaymentInformation(
            $paymentInformation['id'],
            $paymentInformation['creditorAccountIBAN'],
            $creditorAgentBIC,
            $paymentInformation['creditorName']
        );
        $payment->setSequenceType($paymentInformation['seqType']);
        $payment->setCreditorId($paymentInformation['creditorId']);
        if (isset($paymentInformation['localInstrumentCode'])) {
            $payment->setLocalInstrumentCode($paymentInformation['localInstrumentCode']);
        }
        $payment->setDueDate($this->createDueDateFromPaymentInformation($paymentInformation, '+5 day'));
        if (isset($paymentInformation['batchBooking'])) {
            $payment->setBatchBooking($paymentInformation['batchBooking']);
        }
        $this->payments[$paymentName] = $payment;

        return $payment;
    }

    /**
     * @param string $paymentName
     * @param array $transferInformation
     *
     * @struct $transferInformation {
     *    @type string $amount
     *    @type string $debtorIban
     *    @type string $debtorBic
     *    @type string $debtorMandate
     *    @type string|\DateTime $debtorMandateSignDate
     *    @type string $remittanceInformation
     *    @type string $creditorReference
     *    @type string $endToEndId
     *    @type string $originalMandateId
     *    @type string $originalDebtorIban
     *    @type string $amendedDebtorAccount
     *    @type string $postCode
     *    @type string $townName
     *    @type string $streetName
     *    @type string $buildingNumber
     *    @type string $debtorCountry
     *    @type string $debtorAdrLine
     * }
     * @throws InvalidArgumentException
     * @return CustomerDirectDebitTransferInformation
     */
    public function addTransfer(string $paymentName, array $transferInformation): TransferInformationInterface
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

        if (isset($transferInformation['debtorBic'])) {
            $transfer->setBic($transferInformation['debtorBic']);
        }

        $transfer->setMandateId($transferInformation['debtorMandate']);
        if ($transferInformation['debtorMandateSignDate'] instanceof \DateTime) {
            $transfer->setMandateSignDate($transferInformation['debtorMandateSignDate']);
        } else {
            $transfer->setMandateSignDate(new \DateTime($transferInformation['debtorMandateSignDate']));
        }

        if (isset($transferInformation['creditorReference'])) {
            $transfer->setCreditorReference($transferInformation['creditorReference']);
        } else {
            $transfer->setRemittanceInformation($transferInformation['remittanceInformation']);
        }

        if (isset($transferInformation['endToEndId'])) {
            $transfer->setEndToEndIdentification($transferInformation['endToEndId']);
        } else {
            $transfer->setEndToEndIdentification(
                $this->payments[$paymentName]->getId() . count($this->payments[$paymentName]->getTransfers())
            );
        }

        if (isset($transferInformation['instructionId'])) {
            $transfer->setInstructionId($transferInformation['instructionId']);
        }

        if (isset($transferInformation['originalMandateId'])) {
            $transfer->setOriginalMandateId($transferInformation['originalMandateId']);
        }
        if (isset($transferInformation['originalDebtorIban'])) {
            $transfer->setOriginalDebtorIban($transferInformation['originalDebtorIban']);
        }
        if (isset($transferInformation['amendedDebtorAccount'])) {
            $transfer->setAmendedDebtorAccount((bool) $transferInformation['amendedDebtorAccount']);
        }

        if (isset($transferInformation['postCode'])) {
            $transfer->setPostCode($transferInformation['postCode']);
        }

        if (isset($transferInformation['townName'])) {
            $transfer->setTownName($transferInformation['townName']);
        }

        if (isset($transferInformation['streetName'])) {
            $transfer->setStreetName($transferInformation['streetName']);
        }

        if (isset($transferInformation['buildingNumber'])) {
            $transfer->setBuildingNumber($transferInformation['buildingNumber']);
        }

        if (isset($transferInformation['debtorCountry'])) {
            $transfer->setCountry($transferInformation['debtorCountry']);
        }
        if (isset($transferInformation['debtorAdrLine'])) {
            $transfer->setPostalAddress($transferInformation['debtorAdrLine']);
        }
        $this->payments[$paymentName]->addTransfer($transfer);

        return $transfer;
    }
}
