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
     *     id: int,
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

        if (isset($transferInformation['addressType'])) {
            $transfer->setAddressType($transferInformation['addressType']);
        }

        if (isset($transferInformation['department'])) {
            $transfer->setDepartment($transferInformation['department']);
        }

        if (isset($transferInformation['subDepartment'])) {
            $transfer->setSubDepartment($transferInformation['subDepartment']);
        }

        if (isset($transferInformation['floor'])) {
            $transfer->setFloor($transferInformation['floor']);
        }

        if (isset($transferInformation['postBox'])) {
            $transfer->setPostBox($transferInformation['postBox']);
        }

        if (isset($transferInformation['room'])) {
            $transfer->setRoom($transferInformation['room']);
        }

        if (isset($transferInformation['townLocationName'])) {
            $transfer->setTownLocationName($transferInformation['townLocationName']);
        }

        if (isset($transferInformation['districtName'])) {
            $transfer->setDistrictName($transferInformation['districtName']);
        }

        if (isset($transferInformation['countrySubDivision'])) {
            $transfer->setCountrySubDivision($transferInformation['countrySubDivision']);
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

        if (isset($transferInformation['buildingName'])) {
            $transfer->setBuildingName($transferInformation['buildingName']);
        }

        if (isset($transferInformation['buildingNumber'])) {
            $transfer->setBuildingNumber($transferInformation['buildingNumber']);
        }

        if (isset($transferInformation['debtorCountry'])) {
            $transfer->setCountry($transferInformation['debtorCountry']);
        }

        $this->payments[$paymentName]->addTransfer($transfer);

        return $transfer;
    }

}
