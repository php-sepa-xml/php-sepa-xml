<?php

namespace Digitick\Sepa\TransferFile\Facade;

use DateTimeInterface;
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
     * @param string $paymentName
     * @param array{
     *     id: string,
     *     debtorName: string,
     *     debtorAccountIBAN: string,
     *     debtorAgentBIC?: string,
     *     dueDate?: string|DateTimeInterface,
     *     batchBooking?: bool
     * } $paymentInformation
     * @throws InvalidArgumentException
     * @return PaymentInformation
     */
    public function addPaymentInfo(string $paymentName, array $paymentInformation): PaymentInformation
    {
        if (isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf('Payment with the name %s already exists', $paymentName));
        }

        $originAgentBic = $paymentInformation['debtorAgentBIC'] ?? null;
        $payment = new PaymentInformation(
            $paymentInformation['id'],
            $paymentInformation['debtorAccountIBAN'],
            $originAgentBic,
            $paymentInformation['debtorName']
        );
        $payment->setBatchBooking($paymentInformation['batchBooking'] ?? false);
        $payment->setDueDate($this->createDueDateFromPaymentInformation($paymentInformation));

        $this->payments[$paymentName] = $payment;

        return $payment;
    }

    /**
     * @param string $paymentName
     * @param array{
     *     amount: int,
     *     creditorIban: string,
     *     creditorName: string,
     *     creditorBic?: string,
     *     creditorReference?: string,
     *     creditorReferenceType?: string,
     *     remittanceInformation: string,
     *     endToEndId?: string,
     *     instructionId?: string
     * } $transferInformation
     * @return CustomerCreditTransferInformation
     * @throws InvalidArgumentException
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
