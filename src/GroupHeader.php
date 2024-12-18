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
use Digitick\Sepa\Util\StringHelper;

class GroupHeader
{
    /**
     * Whether this is a test Transaction
     *
     * @var bool
     */
    protected $isTest;

    /**
     * @var string Unambiguously identify the message.
     */
    protected $messageIdentification;

    /**
     * The initiating Party for this payment
     *
     * @var string|null
     */
    protected $initiatingPartyId;

    /**
     * Name of the identification scheme, in a coded form as published in an external list. 1-4 characters.
     *
     * @var string|null
     */
    public $initiatingPartyIdentificationScheme;

    /**
     * The Issuer.
     *
     * @var string|null
     */
    protected $issuer;

    /**
     * @var int
     */
    protected $numberOfTransactions = 0;

    /**
     * @var int
     */
    protected $controlSumCents = 0;

    /**
     * @var string
     */
    protected $initiatingPartyName;

    /**
     * @var \DateTime
     */
    protected $creationDateTime;

    /**
     * @var string
     */
    protected $creationDateTimeFormat = 'Y-m-d\TH:i:s\Z';

    /**
     * @param string $messageIdentification Maximum length: 35. Reference Number of the bulk.
     *                                      Part of the duplication check (unique daily reference).
     *                                      The first 8 or 11 characters of <Msgld> must match the BIC of the
     *                                      Instructing Agent. The rest of the field can be freely defined.
     */
    public function __construct(string $messageIdentification, string $initiatingPartyName, bool $isTest = false)
    {
        $this->messageIdentification = $messageIdentification;
        $this->isTest = $isTest;
        $this->initiatingPartyName = StringHelper::sanitizeString($initiatingPartyName);
        $this->creationDateTime = new \DateTime();
    }

    public function accept(DomBuilderInterface $domBuilder): void
    {
        $domBuilder->visitGroupHeader($this);
    }

    public function setControlSumCents(int $controlSumCents): void
    {
        $this->controlSumCents = $controlSumCents;
    }

    public function getControlSumCents(): int
    {
        return $this->controlSumCents;
    }

    public function setInitiatingPartyId(string $initiatingPartyId): void
    {
        $this->initiatingPartyId = $initiatingPartyId;
    }

    public function getInitiatingPartyId(): ?string
    {
        return $this->initiatingPartyId;
    }

    public function setInitiatingPartyIdentificationScheme(string $scheme): void
    {
        $this->initiatingPartyIdentificationScheme = StringHelper::sanitizeString($scheme);
    }

    public function getInitiatingPartyIdentificationScheme(): ?string
    {
        return $this->initiatingPartyIdentificationScheme;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(string $issuer): void
    {
        $this->issuer = $issuer;
    }

    public function setInitiatingPartyName(string $initiatingPartyName): void
    {
        $this->initiatingPartyName = StringHelper::sanitizeString($initiatingPartyName);
    }

    public function getInitiatingPartyName(): string
    {
        return $this->initiatingPartyName;
    }

    public function setIsTest(bool $isTest): void
    {
        $this->isTest = $isTest;
    }

    public function getIsTest(): bool
    {
        return $this->isTest;
    }

    public function setMessageIdentification(string $messageIdentification): void
    {
        $this->messageIdentification = $messageIdentification;
    }

    public function getMessageIdentification(): string
    {
        return $this->messageIdentification;
    }

    public function setNumberOfTransactions(int $numberOfTransactions): void
    {
        $this->numberOfTransactions = $numberOfTransactions;
    }

    public function getNumberOfTransactions(): int
    {
        return $this->numberOfTransactions;
    }

    public function getCreationDateTime(): \DateTime
    {
        return $this->creationDateTime;
    }

    public function setCreationDateTimeFormat(string $creationDateTimeFormat): void
    {
        $this->creationDateTimeFormat = $creationDateTimeFormat;
    }

    public function getCreationDateTimeFormat(): string
    {
        return $this->creationDateTimeFormat;
    }
}
