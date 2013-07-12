<?php

namespace Digitick\Sepa\TransferInformation;

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

class CustomerDirectDebitTransferInformation extends BaseTransferInformation {

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
     * @param string $amount
     * @param string $iban
     * @param string $name
     */
    function __construct($amount, $iban, $name) {
        parent::__construct($amount, $iban, $name);
        // FIXME broken implementation find suitable IDs
        $this->EndToEndIdentification = $name;
    }

    /**
     * @param \DateTime $finalCollectionDate
     */
    public function setFinalCollectionDate($finalCollectionDate) {
        $this->finalCollectionDate = $finalCollectionDate;
    }

    /**
     * @return \DateTime
     */
    public function getFinalCollectionDate() {
        return $this->finalCollectionDate;
    }

    /**
     * @param string $mandateId
     */
    public function setMandateId($mandateId) {
        $this->mandateId = $mandateId;
    }

    /**
     * @return string
     */
    public function getMandateId() {
        return $this->mandateId;
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
     * @return string
     */
    public function getDebitorName() {
        return $this->name;
    }


}