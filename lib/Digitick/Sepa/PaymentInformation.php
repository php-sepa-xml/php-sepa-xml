<?php

namespace Digitick\Sepa;
use Digitick\Sepa\DomBuilder\DomBuilderInterface;
use Digitick\Sepa\Exception\InvalidPaymentMethodException;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;

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

class PaymentInformation {

    const S_FIRST = 'FRST';
    const S_RECURRING = 'RCUR';
    const S_ONEOFF = 'OOFF';
    const S_FINAL = 'FNAL';

    /**
     * @var string Unambiguously identify the payment.
     */
    public $id;

    /**
     * @var string Purpose of the transaction(s).
     */
    public $categoryPurposeCode;

    /**
     * @var string Debtor's name.
     */
    public $originName;

    /**
     * @var string Debtor's account IBAN.
     */
    public $originAccountIBAN;

    /**
     * @var string Debtor's account bank BIC code.
     */
    public $originAgentBIC;

    /**
     * @var string Debtor's account ISO currency code.
     */
    protected $originAccountCurrency;

    /**
     * @var string Payment method.
     */
    protected $paymentMethod;

    /**
     * @var string Local service instrument code.
     */
    protected $localInstrumentCode;

    /**
     * Date of payment execution
     *
     * @var \DateTime
     */
    protected $dueDate;

    /**
     * @var integer
     */
    protected $controlSumCents = 0;

    /**
     * @var integer Number of payment transactions.
     */
    protected $numberOfTransactions = 0;

    /**
     * @var array<TransferInformationInterface>
     */
    protected $transfers;

    /**
     * Valid Payment Methods set by the TransferFile
     *
     * @var
     */
    protected $validPaymentMethods;

    /**
     * @var string
     */
    protected $creditorId;

    /**
     * @var
     */
    protected $sequenceType;

    /**
     * @param string $id
     * @param string $originAccountIBAN This is your IBAN
     * @param string $originAgentBIC This is your BIC
     * @param string $originName This is your Name
     */
    function __construct($id, $originAccountIBAN, $originAgentBIC, $originName, $originAccountCurrency = 'EUR') {
        $this->id = $id;
        $this->originAccountIBAN = $originAccountIBAN;
        $this->originAgentBIC = $originAgentBIC;
        $this->originName = $originName;
        $this->originAccountCurrency = $originAccountCurrency;
        $this->dueDate = new \DateTime();
    }


    /**
     * @param TransferInformationInterface $transfer
     */
    public function addTransfer(TransferInformationInterface $transfer) {
        $this->transfers[] = $transfer;
        $this->numberOfTransactions++;
        $this->controlSumCents += $transfer->getTransferAmount();
    }

    /**
     * @return array
     */
    public function getTransfers() {
        return $this->transfers;
    }

    /**
     * The domBuilder accept this Object
     *
     * @param DomBuilderInterface $domBuilder
     */
    public function accept(DomBuilderInterface $domBuilder) {
        $domBuilder->visitPaymentInformation($this);
        /** @var $transfer TransferInformationInterface */
        foreach ($this->getTransfers() as $transfer) {
            $transfer->accept($domBuilder);
        }
    }

    /**
     * Set the payment method.
     * @param string $method
     * @throws \Digitick\Sepa\Exception\InvalidPaymentMethodException
     */
    public function setPaymentMethod($method) {
        $method = strtoupper($method);
        if (!in_array($method, $this->validPaymentMethods)) {
            throw new InvalidArgumentException("Invalid Payment Method: $method, must be one of " . implode(',', $this->validPaymentMethods));
        }
        $this->paymentMethod = $method;
    }

    /**
     * @param string $localInstrumentCode
     */
    public function setLocalInstrumentCode($localInstrumentCode) {
        $localInstrumentCode = strtoupper($localInstrumentCode);
        if (!in_array($localInstrumentCode, array('B2B', 'CORE'))) {
            throw new InvalidArgumentException("Invalid Local Instrument Code: $localInstrumentCode");
        }
        $this->localInstrumentCode = $localInstrumentCode;
    }

    /**
     * @param mixed $validPaymentMethods
     */
    public function setValidPaymentMethods($validPaymentMethods) {
        $this->validPaymentMethods = $validPaymentMethods;
    }

    /**
     * @param string $categoryPurposeCode
     */
    public function setCategoryPurposeCode($categoryPurposeCode) {
        $this->categoryPurposeCode = $categoryPurposeCode;
    }

    /**
     * @return string
     */
    public function getCategoryPurposeCode() {
        return $this->categoryPurposeCode;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate($dueDate) {
        $this->dueDate = $dueDate;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate() {
        return $this->dueDate->format('Y-m-d');
    }

    /**
     * @param \DateTime $mandateSignDate
     */
    public function setMandateSignDate($mandateSignDate) {
        $this->mandateSignDate = $mandateSignDate;
    }

    /**
     * @return \DateTime
     */
    public function getMandateSignDate() {
        return $this->mandateSignDate;
    }

    /**
     * @param string $originName
     */
    public function setOriginName($originName) {
        $this->originName = $originName;
    }

    /**
     * @return string
     */
    public function getOriginName() {
        return $this->originName;
    }

    /**
     * @param string $originAgentBIC
     */
    public function setOriginAgentBIC($originAgentBIC) {
        $this->originAgentBIC = $originAgentBIC;
    }

    /**
     * @return string
     */
    public function getOriginAgentBIC() {
        return $this->originAgentBIC;
    }

    /**
     * @param string $originAccountIBAN
     */
    public function setOriginAccountIBAN($originAccountIBAN) {
        $this->originAccountIBAN = $originAccountIBAN;
    }

    /**
     * @return string
     */
    public function getOriginAccountIBAN() {
        return $this->originAccountIBAN;
    }

    /**
     * @param string $originAccountCurrency
     */
    public function setOriginAccountCurrency($originAccountCurrency) {
        $this->originAccountCurrency = $originAccountCurrency;
    }

    /**
     * @return string
     */
    public function getOriginAccountCurrency() {
        return $this->originAccountCurrency;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getControlSumCents() {
        return $this->controlSumCents;
    }

    /**
     * @return string
     */
    public function getLocalInstrumentCode() {
        return $this->localInstrumentCode;
    }

    /**
     * @return int
     */
    public function getNumberOfTransactions() {
        return $this->numberOfTransactions;
    }

    /**
     * @return string
     */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    /**
     * @param string $creditorSchemeId
     */
    public function setCreditorId($creditorSchemeId) {
        $this->creditorId = $creditorSchemeId;
    }

    /**
     * @return string
     */
    public function getCreditorId() {
        return $this->creditorId;
    }

    /**
     * @param mixed $sequenceType
     */
    public function setSequenceType($sequenceType) {
        $this->sequenceType = $sequenceType;
    }

    /**
     * @return mixed
     */
    public function getSequenceType() {
        return $this->sequenceType;
    }

}