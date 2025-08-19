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

interface TransferInformationInterface
{
    public function accept(DomBuilderInterface $domBuilder): void;

    public function getTransferAmount(): int;

    public function getEndToEndIdentification(): string;

    public function getUUID(): string;

    public function getInstructionId(): ?string;

    public function getLocalInstrumentProprietary(): ?string;

    public function getLocalInstrumentCode(): ?string;

    public function getCreditorReferenceType(): ?string;

    public function getCategoryPurposeCode(): ?string;

    public function getCreditorReference(): ?string;

    public function getCurrency(): string;

    public function getCountry(): ?string;

    public function getBic(): ?string;

    public function getIban(): string;

    public function getPurposeCode(): ?string;

    public function getRemittanceInformation(): ?string;

    public function getCreditorOrDebitorName(): string;

    public function getTownName(): ?string;

    public function getStreetName(): ?string;

    public function getBuildingNumber(): ?string;

    public function getFloorNumber(): ?string;

    public function getPostCode(): ?string;
}
