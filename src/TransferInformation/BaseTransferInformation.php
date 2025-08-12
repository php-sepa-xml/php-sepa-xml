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
use Digitick\Sepa\Util\Sanitizer;

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
     * Purpose code for the transaction.
     *
     * @var string|null
     */
    protected $purposeCode;

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
     * Type of address.
     *
     * @var string|null
     */
    protected string|null $addressType = null;

    /**
     * Name of the department.
     *
     * Maximum allowed length is 70 characters.
     *
     * @var string|null
     */
    protected string|null $department = null;

    /**
     * Name of the sub department.
     *
     * Maximum allowed length is 70 characters.
     *
     * @var string|null
     */
    protected string|null $subDepartment = null;

    /**
     * Name of the building.
     *
     * Maximum allowed length is 35 characters.
     *
     * @var string|null
     */
    protected string|null $buildingName = null;

    /**
     * Floor of the building.
     *
     * Maximum allowed length is 70 characters.
     *
     * @var string|null
     */
    protected string|null $floor = null;

    /**
     * Code of the post box.
     *
     * Maximum allowed length is 16 characters.
     *
     * @var string|null
     */
    protected string|null $postBox = null;

    /**
     * Name or number of the room.
     *
     * Maximum allowed length is 70 characters.
     *
     * @var string|null
     */
    protected string|null $room = null;

    /**
     * Name of the towns' location.
     *
     * Maximum allowed length is 35 characters.
     *
     * @var string|null
     */
    protected string|null $townLocationName = null;

    /**
     * Name of the district.
     *
     * Maximum allowed length is 35 characters.
     *
     * @var string|null
     */
    protected string|null $districtName = null;

    /**
     * Name of the country's subdivision.
     *
     * Maximum allowed length is 35 characters.
     *
     * @var string|null
     */
    protected string|null $countrySubDivision = null;

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
        $this->name = Sanitizer::sanitize($name);
        $this->EndToEndIdentification = Sanitizer::sanitize($identification);
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
        $this->EndToEndIdentification = Sanitizer::sanitize($EndToEndIdentification);
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
        $this->creditorReference = Sanitizer::sanitize($creditorReference);
    }

    public function getCreditorReference(): ?string
    {
        return $this->creditorReference;
    }

    public function setCreditorReferenceType(string $creditorReferenceType): void
    {
        $this->creditorReferenceType = Sanitizer::sanitize($creditorReferenceType);
    }

    public function getCreditorReferenceType(): ?string
    {
        return $this->creditorReferenceType;
    }

    public function setPurposeCode(string $purposeCode): void
    {
        $this->purposeCode = $purposeCode;
    }

    public function getPurposeCode(): ?string
    {
        return $this->purposeCode;
    }

    public function setRemittanceInformation(string $remittanceInformation): void
    {
        $this->remittanceInformation = Sanitizer::sanitize($remittanceInformation);
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

    /*
     *  postal address elements
     */

    /**
     * @return string|null
     */
    public function getAddressType(): ?string
    {
        return $this->addressType;
    }

    /**
     * @param string|null $addressType
     */
    public function setAddressType(?string $addressType): void
    {
        if (null === $addressType) {
            $this->department = null;
        } else {
            $this->department = StringHelper::sanitizeString($addressType);
        }
    }

    /**
     * @return string|null
     */
    public function getDepartment(): ?string
    {
        return $this->department;
    }

    /**
     * @param string|null $department
     */
    public function setDepartment(?string $department): void
    {
        if (null === $department) {
            $this->department = null;
        } else {
            $this->department = StringHelper::sanitizeString($department);
        }
    }

    /**
     * @return string|null
     */
    public function getSubDepartment(): ?string
    {
        return $this->subDepartment;
    }

    /**
     * @param string|null $subDepartment
     */
    public function setSubDepartment(?string $subDepartment): void
    {
        if (null === $subDepartment) {
            $this->subDepartment = null;
        } else {
            $this->subDepartment = StringHelper::sanitizeString($subDepartment);
        }
    }

    /**
     * @return string|null
     */
    public function getBuildingName(): ?string
    {
        return $this->buildingName;
    }

    /**
     * @param string|null $buildingName
     */
    public function setBuildingName(?string $buildingName): void
    {
        if (null === $buildingName) {
            $this->buildingName = null;
        } else {
            $this->buildingName = StringHelper::sanitizeString($buildingName);
        }
    }

    /**
     * @return string|null
     */
    public function getFloor(): ?string
    {
        return $this->floor;
    }

    /**
     * @param string|null $floor
     */
    public function setFloor(?string $floor): void
    {
        if (null === $floor) {
            $this->floor = null;
        } else {
            $this->floor = StringHelper::sanitizeString($floor);
        }
    }

    /**
     * @return string|null
     */
    public function getPostBox(): ?string
    {
        return $this->postBox;
    }

    /**
     * @param string|null $postBox
     */
    public function setPostBox(?string $postBox): void
    {
        if (null === $postBox) {
            $this->postBox = null;
        } else {
            $this->postBox = StringHelper::sanitizeString($postBox);
        }
    }

    /**
     * @return string|null
     */
    public function getRoom(): ?string
    {
        return $this->room;
    }

    /**
     * @param string|null $room
     */
    public function setRoom(?string $room): void
    {
        if (null === $room) {
            $this->room = null;
        } else {
            $this->room = StringHelper::sanitizeString($room);
        }
    }

    /**
     * @return string|null
     */
    public function getTownLocationName(): ?string
    {
        return $this->townLocationName;
    }

    /**
     * @param string|null $townLocationName
     */
    public function setTownLocationName(?string $townLocationName): void
    {
        if (null === $townLocationName) {
            $this->townLocationName = null;
        } else {
            $this->townLocationName = StringHelper::sanitizeString($townLocationName);
        }
    }

    /**
     * @return string|null
     */
    public function getDistrictName(): ?string
    {
        return $this->districtName;
    }

    /**
     * @param string|null $districtName
     */
    public function setDistrictName(?string $districtName): void
    {
        if (null === $districtName) {
            $this->districtName = null;
        } else {
            $this->districtName = StringHelper::sanitizeString($districtName);
        }
    }

    /**
     * @return string|null
     */
    public function getCountrySubDivision(): ?string
    {
        return $this->countrySubDivision;
    }

    /**
     * @param string|null $countrySubDivision
     */
    public function setCountrySubDivision(?string $countrySubDivision): void
    {
        if (null === $countrySubDivision) {
            $this->countrySubDivision = null;
        } else {
            $this->countrySubDivision = StringHelper::sanitizeString($countrySubDivision);
        }
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
            $this->townName = Sanitizer::sanitize($townName);
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
            $this->postCode = Sanitizer::sanitize($postCode);
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
            $this->streetName = Sanitizer::sanitize($streetName);
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
            $this->buildingNumber = Sanitizer::sanitize($buildingNumber);
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

    /**
     * Wrapper for the getCreditorName() and getDebitorName()
     * @return string
     */
    public function getCreditorOrDebitorName(): string
    {
        return $this->name;
    }
}
