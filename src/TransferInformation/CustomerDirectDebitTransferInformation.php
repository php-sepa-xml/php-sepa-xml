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
     * @var string|null
     */
    protected $mandateId;

    /**
     * @var \DateTime|null
     */
    protected $mandateSignDate;

    /**
     * @var \DateTime|null
     */
    protected $finalCollectionDate;

    /**
     * @var bool
     */
    protected $amendedDebtorAccount = false;

    /**
     * @var string|null
     */
    protected $originalDebtorIban;

    /**
     * @var string|null
     */
    protected $originalMandateId;

    public function hasAmendments(): bool
    {
        return $this->amendedDebtorAccount
            || $this->originalDebtorIban !== null
            || $this->originalMandateId !== null;
    }

    public function hasAmendedDebtorAccount(): bool
    {
        return $this->amendedDebtorAccount;
    }

    public function setAmendedDebtorAccount(bool $status): void
    {
        $this->amendedDebtorAccount = $status;
    }

    public function setFinalCollectionDate(\DateTime $finalCollectionDate): void
    {
        $this->finalCollectionDate = $finalCollectionDate;
    }

    public function getFinalCollectionDate(): ?\DateTime
    {
        return $this->finalCollectionDate;
    }

    public function setOriginalDebtorIban(string $originalDebtorIban): void
    {
        $this->originalDebtorIban = $originalDebtorIban;
    }

    public function getOriginalDebtorIban(): ?string
    {
        return $this->originalDebtorIban;
    }

    public function setOriginalMandateId(string $originalMandateId): void
    {
        $this->originalMandateId = StringHelper::sanitizeString($originalMandateId);
    }

    public function getOriginalMandateId(): ?string
    {
        return $this->originalMandateId;
    }

    public function setMandateId(string $mandateId): void
    {
        $this->mandateId = StringHelper::sanitizeString($mandateId);
    }

    public function getMandateId(): ?string
    {
        return $this->mandateId;
    }

    public function setMandateSignDate(\DateTime $mandateSignDate): void
    {
        $this->mandateSignDate = $mandateSignDate;
    }

    public function getMandateSignDate(): ?\DateTime
    {
        return $this->mandateSignDate;
    }

    public function getDebitorName(): string
    {
        return $this->name;
    }
}
