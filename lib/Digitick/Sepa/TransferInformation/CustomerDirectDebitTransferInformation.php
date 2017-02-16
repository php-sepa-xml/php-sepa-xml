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

use Digitick\Sepa\Util\StringHelper;

class CustomerDirectDebitTransferInformation extends BaseTransferInformation
{
    /**
     * @var string
     */
    protected $mandateId;

    /**
     * @var \DateTime
     */
    protected $mandateSignDate;

    /**
     * @var \DateTime
     */
    protected $finalCollectionDate;

    /**
     * @var bool
     */
    protected $amendedDebtorAgent = false;

    /**
     * @var string|null
     */
    protected $originalDebtorIban = null;

    /**
     * @var string|null
     */
    protected $originalMandateId = null;

    /**
     * @param string $amount
     * @param string $iban
     * @param string $name
     * @param string $identification
     */
    public function __construct($amount, $iban, $name, $identification = null)
    {
        parent::__construct($amount, $iban, $name);

        if (null === $identification) {
            $identification = $name;
        }

        $this->setEndToEndIdentification($identification);
    }

    /**
     * @return boolean
     */
    public function hasAmendments()
    {
        return $this->amendedDebtorAgent
            || $this->originalDebtorIban !== null
            || $this->originalMandateId !== null;
    }

    /**
     * @return boolean
     */
    public function hasAmendedDebtorAgent()
    {
        return $this->amendedDebtorAgent;
    }

    /**
     * @param bool $status
     */
    public function setAmendedDebtorAgent($status)
    {
        $this->amendedDebtorAgent = $status;
    }

    /**
     * @param \DateTime $finalCollectionDate
     */
    public function setFinalCollectionDate($finalCollectionDate)
    {
        $this->finalCollectionDate = $finalCollectionDate;
    }

    /**
     * @return \DateTime
     */
    public function getFinalCollectionDate()
    {
        return $this->finalCollectionDate;
    }

    /**
     * @param string $originalDebtorIban
     */
    public function setOriginalDebtorIban($originalDebtorIban)
    {
        $this->originalDebtorIban = $originalDebtorIban;
    }

    /**
     * @return string|null
     */
    public function getOriginalDebtorIban()
    {
        return $this->originalDebtorIban;
    }

    /**
     * @param string $originalMandateId
     */
    public function setOriginalMandateId($originalMandateId)
    {
        $this->originalMandateId = StringHelper::sanitizeString($originalMandateId);
    }

    /**
     * @return string|null
     */
    public function getOriginalMandateId()
    {
        return $this->originalMandateId;
    }

    /**
     * @param string $mandateId
     */
    public function setMandateId($mandateId)
    {
        $this->mandateId = StringHelper::sanitizeString($mandateId);
    }

    /**
     * @return string
     */
    public function getMandateId()
    {
        return $this->mandateId;
    }

    /**
     * @param \DateTime $mandateSignDate
     */
    public function setMandateSignDate($mandateSignDate)
    {
        $this->mandateSignDate = $mandateSignDate;
    }

    /**
     * @return \DateTime
     */
    public function getMandateSignDate()
    {
        return $this->mandateSignDate;
    }

    /**
     * @return string
     */
    public function getDebitorName()
    {
        return $this->name;
    }
}
