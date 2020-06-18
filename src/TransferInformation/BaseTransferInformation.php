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
     * @var string
     */
    protected $currency = 'EUR';

    /**
     * Purpose of this transaction
     *
     * @var string
     */
    protected $remittanceInformation;

    /**
     * Structured creditor reference type.
     *
     * @var string
     */
    protected $creditorReferenceType;

    /**
     * Structured creditor reference.
     *
     * @var string
     */
    protected $creditorReference;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string|array
     */
    protected $postalAddress;

    /**
     * @param string|int|float $amount If int is provided, the amount should be in cents
     *                                 If float is provided, the amount will be multiply by 100
     *                                 If string is provided, it depends on the value
     * @param string $iban
     * @param string $name
     *
     * @throws InvalidArgumentException
     */
    public function __construct($amount, $iban, $name)
    {
        $amount += 0;
        if (is_float($amount)) {
            if (!function_exists('bcscale')) {
                throw new InvalidArgumentException('Using floats for amount is only possible with bcmath enabled');
            }
            bcscale(2);
            $amount = (integer)bcmul(sprintf('%01.4F', $amount), '100');
        }
        $this->transferAmount = $amount;
        $this->iban = $iban;
        $this->name = StringHelper::sanitizeString($name);
    }

    /**
     * @param DomBuilderInterface $domBuilder
     */
    public function accept(DomBuilderInterface $domBuilder)
    {
        $domBuilder->visitTransferInformation($this);
    }

    /**
     * @return mixed
     */
    public function getTransferAmount()
    {
        return $this->transferAmount;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $EndToEndIdentification
     */
    public function setEndToEndIdentification($EndToEndIdentification)
    {
        $this->EndToEndIdentification = StringHelper::sanitizeString($EndToEndIdentification);
    }

    /**
     * @return string
     */
    public function getEndToEndIdentification()
    {
        return $this->EndToEndIdentification;
    }

    /**
     * @param string $instructionId
     */
    public function setInstructionId($instructionId)
    {
        $this->instructionId = $instructionId;
    }

    /**
     * @return string
     */
    public function getInstructionId()
    {
        return $this->instructionId;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $creditorReference
     */
    public function setCreditorReference($creditorReference)
    {
        $this->creditorReference = StringHelper::sanitizeString($creditorReference);
    }

    /**
     * @return string
     */
    public function getCreditorReference()
    {
        return $this->creditorReference;
    }

    /**
     * @param string $creditorReferenceType
     */
    public function setCreditorReferenceType($creditorReferenceType)
    {
        $this->creditorReferenceType = StringHelper::sanitizeString($creditorReferenceType);
    }

    /**
     * @return string
     */
    public function getCreditorReferenceType()
    {
        return $this->creditorReferenceType;
    }


    /**
     * @param string $remittanceInformation
     */
    public function setRemittanceInformation($remittanceInformation)
    {
        $this->remittanceInformation = StringHelper::sanitizeString($remittanceInformation);
    }

    /**
     * @return string
     */
    public function getRemittanceInformation()
    {
        return $this->remittanceInformation;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return array|string
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * @param array|string $postalAddress
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;
    }
}
