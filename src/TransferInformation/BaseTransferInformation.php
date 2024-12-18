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

namespace Digitick\Sepa\TransferInformation;

use Digitick\Sepa\DomBuilder\DomBuilderInterface;
use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\Util\StringHelper;

class BaseTransferInformation implements TransferInformationInterface
{
    /**
     * Account Identifier
     *
     * @var string
     */
    protected $iban;

    /**
     * Financial Institution Identifier;
     *
     * @var string|null
     */
    protected $bic;

    /**
     * Amount in cents; must be between 1 and 99999999999
     *
     * @var int
     */
    protected $transferAmount;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $instructionId;

    /**
     * @var string
     */
    protected $EndToEndIdentification;

    /**
     * @var string
     */
    protected $currency = 'EUR';

    /**
     * Purpose of this transaction
     *
     * @var string|null
     */
    protected $remittanceInformation;

    /**
     * Structured creditor reference type.
     *
     * @var string|null
     */
    protected $creditorReferenceType;

    /**
     * Structured creditor reference.
     *
     * @var string|null
     */
    protected $creditorReference;

    /**
     * Nation with its own government.
     *
     * The code is checked against the list of country names obtained from the
     * United Nations (ISO 3166, Alpha-2 code).
     *
     * @var string|null
     */
    protected $country;

    /**
     * Name of a built-up area, with defined boundaries, and a local government.
     *
     * Maximum allowed length is 35 characters.
     *
     * @var string|null
     */
    protected $townName;

    /**
     * Identifier consisting of a group of letters and/or numbers that is added
     * to a postal address to assist the sorting of mail.
     *
     * Maximum allowed length is 16 characters.
     *
     * @var string|null
     */
    protected $postCode;

    /**
     * Name of a street or thoroughfare.
     *
     * Maximum allowed length is 70 characters.
     *
     * @var string|null
     */
    protected $streetName;

    /**
     * Number that identifies the position of a building on a street.
     *
     * Maximum allowed length is 16 characters.
     *
     * @var string|null
     */
    protected $buildingNumber;

    /**
     * Information that locates and identifies a specific address, as defined
     * by postal services, presented in free format text.
     *
     * Maximum allowed length is 70 characters.
     *
     * @var string|string[]|null
     */
    protected $postalAddress;

    /**
     * @param int $amount amount in cents
     */
    public function __construct(int $amount, string $iban, string $name, ?string $identification = null)
    {
        if (null === $identification) {
            $identification = $name;
        }

        $this->transferAmount = $amount;
        $this->iban = $iban;
        $this->name = StringHelper::sanitizeString($name);
        $this->EndToEndIdentification = StringHelper::sanitizeString($identification);
    }

    public function accept(DomBuilderInterface $domBuilder): void
    {
        $domBuilder->visitTransferInformation($this);
    }

    public function getTransferAmount(): int
    {
        return $this->transferAmount;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setEndToEndIdentification(string $EndToEndIdentification): void
    {
        $this->EndToEndIdentification = StringHelper::sanitizeString($EndToEndIdentification);
    }

    public function getEndToEndIdentification(): string
    {
        return $this->EndToEndIdentification;
    }

    public function setInstructionId(string $instructionId): void
    {
        $this->instructionId = $instructionId;
    }

    public function getInstructionId(): ?string
    {
        return $this->instructionId;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setBic(string $bic): void
    {
        $this->bic = $bic;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setCreditorReference(string $creditorReference): void
    {
        $this->creditorReference = StringHelper::sanitizeString($creditorReference);
    }

    public function getCreditorReference(): ?string
    {
        return $this->creditorReference;
    }

    public function setCreditorReferenceType(string $creditorReferenceType): void
    {
        $this->creditorReferenceType = StringHelper::sanitizeString($creditorReferenceType);
    }

    public function getCreditorReferenceType(): ?string
    {
        return $this->creditorReferenceType;
    }

    public function setRemittanceInformation(string $remittanceInformation): void
    {
        $this->remittanceInformation = StringHelper::sanitizeString($remittanceInformation);
    }

    public function getRemittanceInformation(): ?string
    {
        return $this->remittanceInformation;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * Get the name of the town where the creditor/debtor is located
     *
     * Maximum allowed length is 35 characters.
     *
     * @return string|null
     */
    public function getTownName(): ?string
    {
        return $this->townName;
    }

    /**
     * Set the name of the town where the creditor/debtor is located.
     *
     * @param string|null $townName Maximum allowed length is 35 characters.
     */
    public function setTownName(?string $townName): void
    {
        if (null === $townName) {
            $this->townName = null;
        } else {
            $this->townName = StringHelper::sanitizeString($townName);
        }
    }

    /**
     * Get the post code where the creditor/debtor is located.
     *
     * @return string|null
     */
    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    /**
     * Set the post code where the creditor/debtor is located.
     *
     * @param string|null $postCode Maximum allowed length is 16 characters.
     */
    public function setPostCode(?string $postCode): void
    {
        if (null === $postCode) {
            $this->postCode = null;
        } else {
            $this->postCode = StringHelper::sanitizeString($postCode);
        }
    }

    /**
     * Get the street name where the creditor/debtor is located.
     *
     * @return string|null
     */
    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    /**
     * Set the street name where the creditor/debtor is located.
     *
     * @param string|null $streetName Maximum allowed length is 70 characters.
     */
    public function setStreetName(?string $streetName): void
    {
        if (null === $streetName) {
            $this->streetName = null;
        } else {
            $this->streetName = StringHelper::sanitizeString($streetName);
        }
    }

    /**
     * Get the number that identifies the position of the building on the street
     * where the creditor/debtor is located.
     *
     * @return string|null
     */
    public function getBuildingNumber(): ?string
    {
        return $this->buildingNumber;
    }

    /**
     * Set the number that identifies the position of the building on the street
     * where the creditor/debtor is located.
     *
     * @param string|null $buildingNumber Maximum allowed length is 16 characters.
     */
    public function setBuildingNumber(?string $buildingNumber): void
    {
        if (null === $buildingNumber) {
            $this->buildingNumber = null;
        } else {
            $this->buildingNumber = StringHelper::sanitizeString($buildingNumber);
        }
    }

    /**
     * @return string|string[]|null
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * @param string|string[] $postalAddress
     */
    public function setPostalAddress($postalAddress): void
    {
        $this->postalAddress = $postalAddress;
    }
}
