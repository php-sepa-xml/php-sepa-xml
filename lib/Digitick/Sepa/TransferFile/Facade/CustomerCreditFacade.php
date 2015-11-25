<?php

namespace Digitick\Sepa\TransferFile\Facade;

use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;

/**
 * Class CustomerCreditFacade
 */
class CustomerCreditFacade extends BaseCustomerTransferFileFacade
{

    /**
     * @param string $paymentName
     * @param array  $paymentInformation
     *
     * @throws InvalidArgumentException
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
            $paymentInformation['debtorAccountIBAN'],
            $paymentInformation['debtorAgentBIC'],
            $paymentInformation['debtorName']
        );

        $this->payments[$paymentName] = $payment;

        return $payment;
    }

    /**
     * @param string $paymentName
     * @param array  $transferInformation
     *
     * @throws InvalidArgumentException
     *
     * @return CustomerCreditTransferInformation
     */
    public function addTransfer($paymentName, array $transferInformation)
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
        $transfer->setBic($transferInformation['creditorBic']);
        $transfer->setRemittanceInformation($transferInformation['remittanceInformation']);

        if (isset($transferInformation['endToEndId'])) {
            $transfer->setEndToEndIdentification($transferInformation['endToEndId']);
        } else {
            $transfer->setEndToEndIdentification(
                $this->payments[$paymentName]->getId() . count($this->payments[$paymentName]->getTransfers())
            );
        }

        $this->payments[$paymentName]->addTransfer($transfer);

        return $transfer;
    }

}
