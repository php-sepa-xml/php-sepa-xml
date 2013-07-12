<?php

namespace Digitick\Sepa\TransferInformation;
use Digitick\Sepa\DomBuilder\DomBuilderInterface;

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

class BaseTransferInformation implements TransferInformationInterface{

    /**
     * Account Identifier
     *
     * @var string
     */
    protected $iban;

    /**
     * Financial Institution Identifier;
     *
     * @var string
     */
    protected $bic;

    /**
     * Must be between 0.01 and 999999999.99
     *
     * @var string
     */
    protected $transferAmount;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $instructionId;

    /**
     * @var string
     */
    protected $EndToEndIdentification;

    /**
     * @var
     */
    protected $currency = 'EUR';

    /**
     * Purpose of this transaction
     *
     * @var string
     */
    protected $remittanceInformation;

    /**
     * @param string $amount
     * @param string $iban
     * @param string $name
     */
    public function __construct($amount, $iban, $name) {
        $this->transferAmount = (float)$amount * 100;
        $this->iban = $iban;
        $this->name = $name;
    }

    /**
     * @param DomBuilderInterface $domBuilder
     */
    public function accept(DomBuilderInterface $domBuilder) {
        $domBuilder->visitTransferInformation($this);
    }

    /**
     * @return mixed
     */
    public function getTransferAmount() {
        return $this->transferAmount;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $EndToEndIdentification
     */
    public function setEndToEndIdentification($EndToEndIdentification) {
        $this->EndToEndIdentification = $EndToEndIdentification;
    }

    /**
     * @return string
     */
    public function getEndToEndIdentification() {
        return $this->EndToEndIdentification;
    }

    /**
     * @param string $instructionId
     */
    public function setInstructionId($instructionId) {
        $this->instructionId = $instructionId;
    }

    /**
     * @return string
     */
    public function getInstructionId() {
        return $this->instructionId;
    }


    /**
     * @param string $iban
     */
    public function setIban($iban) {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getIban() {
        return $this->iban;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic) {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBic() {
        return $this->bic;
    }

    /**
     * @param string $remittanceInformation
     */
    public function setRemittanceInformation($remittanceInformation) {
        $this->remittanceInformation = $remittanceInformation;
    }

    /**
     * @return string
     */
    public function getRemittanceInformation() {
        return $this->remittanceInformation;
    }

}