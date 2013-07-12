<?php

namespace Digitick\Sepa\TransferFile;
use Digitick\Sepa\DomBuilder\DomBuilderInterface;
use Digitick\Sepa\Exception\InvalidTransferFileConfiguration;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;

/**
 * SEPA file generator.
 *
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

abstract class BaseTransferFile implements TransferFileInterface {

    /**
     * @var GroupHeader
     */
    protected $groupHeader;

    /**
     * @var array<PaymentInformation>
     */
    protected $paymentInformations;

    /**
     * @param GroupHeader $groupHeader
     */
    public function __construct(GroupHeader $groupHeader) {
        $this->groupHeader = $groupHeader;
    }

    /**
     * @return GroupHeader
     */
    public function getGroupHeader() {
        return $this->groupHeader;
    }

    /**
     * @param PaymentInformation $paymentInformation
     */
    public function addPaymentInformation(PaymentInformation $paymentInformation) {
        $numberOfTransactions = $this->getGroupHeader()->getNumberOfTransactions() + $paymentInformation->getNumberOfTransactions();
        $transactionTotal = $this->getGroupHeader()->getControlSumCents() + $paymentInformation->getControlSumCents();
        $this->groupHeader->setNumberOfTransactions($numberOfTransactions);
        $this->groupHeader->setControlSumCents($transactionTotal);
        $this->paymentInformations[] = $paymentInformation;
    }

    /**
     * @param DomBuilderInterface $domBuilder
     */
    public function accept(DomBuilderInterface $domBuilder) {
        $this->validate();
        $domBuilder->visitTransferFile($this);
        $this->groupHeader->accept($domBuilder);
        /** @var $paymentInformation PaymentInformation */
        foreach($this->paymentInformations as $paymentInformation) {
            $paymentInformation->accept($domBuilder);
        }
    }

    /**
     * update the group header with transaction informations collected
     * by paymentinformation
     */
    public function validate() {
        if(count($this->paymentInformations) === 0) {
            throw new InvalidTransferFileConfiguration('No paymentinformations available, add paymentInformation via addPaymentInformation()');
        }
    }

}