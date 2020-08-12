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

namespace Digitick\Sepa;

use Digitick\Sepa\DomBuilder\DomBuilderInterface;
use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;
use Digitick\Sepa\Util\StringHelper;

class PaymentInformation
{
    /**
     * The first drawn from several recurring debits
     */
    const S_FIRST = 'FRST';

    /**
     * A recurring direct debit in a number of direct debits
     */
    const S_RECURRING = 'RCUR';

    /**
     * A one time non-recurring debit
     */
    const S_ONEOFF = 'OOFF';

    /**
     * The last direct debit in a series of recurring debits
     */
    const S_FINAL = 'FNAL';

    /**
     * @var string Unambiguously identify the payment.
     */
    public $id;

    /**
     * @var string|null Purpose of the transaction(s).
     */
    public $categoryPurposeCode;

    /**
     * @var string Debtor's name.
     */
    public $originName;

    /**
     * Unique identification of an organisation, as assigned by an institution, using an identification scheme.
     *
     * @var string|null
     */
    public $originBankPartyIdentification;

    /**
     * Name of the identification scheme, in a coded form as published in an external list. 1-4 characters.
     *
     * @var string|null
     */
    public $originBankPartyIdentificationScheme;

    /**
     * @var string Debtor's account IBAN.
     */
    public $originAccountIBAN;

    /**
     * @var string|null Debtor's account bank BIC code.
     */
    public $originAgentBIC;

    /**
     * @var string Debtor's account ISO currency code.
     */
    protected $originAccountCurrency;

    /**
     * @var string|null Payment method.
     */
    protected $paymentMethod;

    /**
     * @var string|null Local service instrument code.
     */
    protected $localInstrumentCode;

    /**
     * Date of payment execution
     *
     * @var \DateTime
     */
    protected $dueDate;

    /**
     * @var string|null Instruction priority.
     */
    protected $instructionPriority;

    /**
     * @var int
     */
    protected $controlSumCents = 0;

    /**
     * @var int Number of payment transactions.
     */
    protected $numberOfTransactions = 0;

    /**
     * @var TransferInformationInterface[]
     */
    protected $transfers = array();

    /**
     * Valid Payment Methods set by the TransferFile
     *
     * @var string[]
     */
    protected $validPaymentMethods = array();

    /**
     * @var string|null
     */
    protected $creditorId;

    /**
     * @var string|null
     */
    protected $sequenceType;

    /**
     * Should the bank book multiple transaction as a batch
     *
     * @var bool|null
     */
    protected $batchBooking;

    /**
     * @var \DateTime|null
     */
    protected $mandateSignDate;

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d';

    public function __construct(string $id, string $originAccountIBAN, ?string $originAgentBIC, string $originName, string $originAccountCurrency = 'EUR')
    {
        $this->id = $id;
        $this->originAccountIBAN = $originAccountIBAN;
        $this->originAgentBIC = $originAgentBIC;
        $this->originName = StringHelper::sanitizeString($originName);
        $this->originAccountCurrency = $originAccountCurrency;
        $this->dueDate = new \DateTime();
    }


    public function addTransfer(TransferInformationInterface $transfer): void
    {
        $this->transfers[] = $transfer;
        $this->numberOfTransactions++;
        $this->controlSumCents += $transfer->getTransferAmount();
    }

    /**
     * @return TransferInformationInterface[]
     */
    public function getTransfers(): array
    {
        return $this->transfers;
    }

    /**
     * The domBuilder accept this Object
     */
    public function accept(DomBuilderInterface $domBuilder): void
    {
        $domBuilder->visitPaymentInformation($this);

        foreach ($this->getTransfers() as $transfer) {
            $transfer->accept($domBuilder);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setPaymentMethod(string $method): void
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->validPaymentMethods)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid Payment Method: %s, must be one of %s',
                $method,
                implode(',', $this->validPaymentMethods)
            ));
        }
        $this->paymentMethod = $method;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setLocalInstrumentCode(string $localInstrumentCode): void
    {
        $localInstrumentCode = strtoupper($localInstrumentCode);
        if (!in_array($localInstrumentCode, array('B2B', 'CORE', 'COR1'))) {
            throw new InvalidArgumentException("Invalid Local Instrument Code: $localInstrumentCode");
        }
        $this->localInstrumentCode = $localInstrumentCode;
    }

    /**
     * @param string[] $validPaymentMethods
     */
    public function setValidPaymentMethods(array $validPaymentMethods): void
    {
        $this->validPaymentMethods = $validPaymentMethods;
    }

    public function setCategoryPurposeCode(string $categoryPurposeCode): void
    {
        $this->categoryPurposeCode = $categoryPurposeCode;
    }

    public function getCategoryPurposeCode(): ?string
    {
        return $this->categoryPurposeCode;
    }

    public function setDueDate(\DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getDueDate(): string
    {
        return $this->dueDate->format($this->dateFormat);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setInstructionPriority(string $instructionPriority): void
    {
        $instructionPriority = strtoupper($instructionPriority);
        if (!in_array($instructionPriority, array('NORM', 'HIGH'))) {
            throw new InvalidArgumentException("Invalid Instruction Priority: $instructionPriority");
        }
        $this->instructionPriority = $instructionPriority;
    }

    public function getInstructionPriority(): ?string
    {
        return $this->instructionPriority;
    }

    public function setMandateSignDate(\DateTime $mandateSignDate): void
    {
        $this->mandateSignDate = $mandateSignDate;
    }

    public function getMandateSignDate(): ?\DateTime
    {
        return $this->mandateSignDate;
    }

    public function setOriginName(string $originName): void
    {
        $this->originName = StringHelper::sanitizeString($originName);
    }

    public function getOriginName(): string
    {
        return $this->originName;
    }

    public function setOriginBankPartyIdentification(string $id): void
    {
        $this->originBankPartyIdentification = StringHelper::sanitizeString($id);
    }

    public function getOriginBankPartyIdentification(): ?string
    {
        return $this->originBankPartyIdentification;
    }

    public function setOriginBankPartyIdentificationScheme(string $scheme): void
    {
        $this->originBankPartyIdentificationScheme = StringHelper::sanitizeString($scheme);
    }

    public function getOriginBankPartyIdentificationScheme(): ?string
    {
        return $this->originBankPartyIdentificationScheme;
    }

    public function setOriginAgentBIC(string $originAgentBIC): void
    {
        $this->originAgentBIC = $originAgentBIC;
    }

    public function getOriginAgentBIC(): ?string
    {
        return $this->originAgentBIC;
    }

    public function setOriginAccountIBAN(string $originAccountIBAN): void
    {
        $this->originAccountIBAN = $originAccountIBAN;
    }

    public function getOriginAccountIBAN(): string
    {
        return $this->originAccountIBAN;
    }

    public function setOriginAccountCurrency(string $originAccountCurrency): void
    {
        $this->originAccountCurrency = $originAccountCurrency;
    }

    public function getOriginAccountCurrency(): string
    {
        return $this->originAccountCurrency;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getControlSumCents(): int
    {
        return $this->controlSumCents;
    }

    public function getLocalInstrumentCode(): ?string
    {
        return $this->localInstrumentCode;
    }

    public function getNumberOfTransactions(): int
    {
        return $this->numberOfTransactions;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setCreditorId(string $creditorSchemeId): void
    {
        $this->creditorId = StringHelper::sanitizeString($creditorSchemeId);
    }

    public function getCreditorId(): ?string
    {
        return $this->creditorId;
    }

    public function setSequenceType(string $sequenceType): void
    {
        $this->sequenceType = $sequenceType;
    }

    public function getSequenceType(): ?string
    {
        return $this->sequenceType;
    }

    public function setBatchBooking(bool $batchBooking): void
    {
        $this->batchBooking = $batchBooking;
    }

    public function getBatchBooking(): ?bool
    {
        return $this->batchBooking;
    }

    public function setDueDateFormat(string $format): void
    {
        $this->dateFormat = $format;
    }
}
