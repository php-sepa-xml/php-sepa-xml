<?php

namespace Digitick\Sepa\TransferFile\Facade;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;

/**
 * Class CustomerCreditFacade
 */
class CustomerCreditFacade extends BaseCustomerTransferFileFacade
{

    /**
     * @param array{
     *             id: string,
     *             debtorName: string,
     *             debtorAccountIBAN: string,
     *             debtorAgentBIC?: string,
     *             dueDate?: string|\DateTime,
     *             batchBooking?: bool
     *             } $paymentInformation
     *
     * @throws InvalidArgumentException
     */
    public function addPaymentInfo(string $paymentName, array $paymentInformation): PaymentInformation
    {
        if (isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf('Payment with the name %s already exists', $paymentName));
        }

        $originAgentBic = (isset ($paymentInformation['debtorAgentBIC'])) ? $paymentInformation['debtorAgentBIC'] : NULL;
        $payment = new PaymentInformation(
            $paymentInformation['id'],
            $paymentInformation['debtorAccountIBAN'],
            $originAgentBic,
            $paymentInformation['debtorName']
        );

        if (isset($paymentInformation['batchBooking'])) {
            $payment->setBatchBooking($paymentInformation['batchBooking']);
        }

        $payment->setDueDate($this->createDueDateFromPaymentInformation($paymentInformation));

        $this->payments[$paymentName] = $payment;

        return $payment;
    }

    /**
     * @param array{
     *             amount: int,
     *             creditorIban: string,
     *             creditorName: string,
     *             creditorBic?: string,
     *             creditorReference?: string,
     *             remittanceInformation: string,
     *             endToEndId?: string,
     *             instructionId?: string
     *             } $transferInformation
     *
     * @throws InvalidArgumentException
     *
     * @return CustomerCreditTransferInformation
     */
    public function addTransfer(string $paymentName, array $transferInformation): TransferInformationInterface
    {
        if (!isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf(
                'Payment with the name %s does not exists, create one first with addPaymentInfo',
                $paymentName
            ));
        }

        $transfer = new CustomerCreditTransferInformation(
            $transferInformation['amount'],
            $transferInformation['creditorIban'],
            $transferInformation['creditorName']
        );

        if (isset($transferInformation['creditorBic'])) {
            $transfer->setBic($transferInformation['creditorBic']);
        }

        if (isset($transferInformation['creditorReference'])) {
            $transfer->setCreditorReference($transferInformation['creditorReference']);
        } else {
            $transfer->setRemittanceInformation($transferInformation['remittanceInformation']);
        }

        if (isset($transferInformation['creditorReferenceType'])) {
            $transfer->setCreditorReferenceType($transferInformation['creditorReferenceType']);
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

        $this->payments[$paymentName]->addTransfer($transfer);

        return $transfer;
    }

}
