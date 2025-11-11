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

namespace Digitick\Sepa\DomBuilder;

use \DOMElement;
use \DOMDocument;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;
use Digitick\Sepa\Util\MessageFormat;

abstract class BaseDomBuilder implements DomBuilderInterface
{
    /** @var DOMDocument $doc */
    protected $doc;

    /** @var DOMElement $root */
    protected $root;

    /** @var DOMElement|null $currentTransfer */
    protected $currentTransfer;

    /** @var DOMELement|null $currentPayment */
    protected $currentPayment;

    /** @var null|MessageFormat $messageFormat */
    protected $messageFormat = null;

    /**
     * @param string $painFormat
     * @param bool $withSchemaLocation define if xsi:schemaLocation attribute is added to root
     * @throws \DOMException
     */
    public function __construct(string $painFormat, bool $withSchemaLocation = true)
    {
        $this->messageFormat = new MessageFormat($painFormat);
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $this->root = $this->doc->createElement('Document');

        $this->setXmlns($this->messageFormat->getMessageName());

        $this->root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $this->setSchemaLocation($this->messageFormat->getMessageName(), $withSchemaLocation);

        $this->doc->appendChild($this->root);
    }

    private function setXmlns(string $messageName): void
    {
        if (filter_var($messageName, FILTER_VALIDATE_URL)) {
            $this->root->setAttribute('xmlns', sprintf('%s', $messageName));
        } else {
            $this->root->setAttribute('xmlns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $messageName));
        }
    }

    private function setSchemaLocation(string $messageName, bool $withSchemaLocation=true): void
    {
        if ($withSchemaLocation) {
            if (filter_var($messageName, FILTER_VALIDATE_URL)) {
                $messageName = substr($messageName, strrpos($messageName, '/')+1, (strrpos($messageName, '.') -1) - strrpos($messageName, '/'));

                $this->root->setAttribute('xsi:schemaLocation', "urn:iso:std:iso:20022:tech:xsd:$messageName $messageName.xsd");
            } else {
                $this->root->setAttribute('xsi:schemaLocation', "urn:iso:std:iso:20022:tech:xsd:$messageName $messageName.xsd");
            }
	    }
    }

    protected function createElement(string $name, ?string $value = null): DOMElement
    {
        if ($value !== null) {
            $elm = $this->doc->createElement($name);
            $elm->appendChild($this->doc->createTextNode($value));

            return $elm;
        } else {
            return $this->doc->createElement($name);
        }
    }

    public function asXml(): string
    {
        return $this->doc->saveXML();
    }

    public function asDoc(): DomDocument
    {
        return $this->doc;
    }

    /**
     * Format an integer as a monetary value.
     */
    protected function intToCurrency(int $amount): string
    {
        return sprintf('%01.2F', ($amount / 100));
    }

    /**
     * Add GroupHeader Information to the document
     */
    public function visitGroupHeader(GroupHeader $groupHeader): void
    {
        $groupHeaderTag = $this->doc->createElement('GrpHdr');
        $messageId = $this->createElement('MsgId', $groupHeader->getMessageIdentification());
        $groupHeaderTag->appendChild($messageId);
        $creationDateTime = $this->createElement(
            'CreDtTm',
            $groupHeader->getCreationDateTime()->format($groupHeader->getCreationDateTimeFormat())
        );
        $groupHeaderTag->appendChild($creationDateTime);
        $groupHeaderTag->appendChild($this->createElement('NbOfTxs', (string) $groupHeader->getNumberOfTransactions()));
        $groupHeaderTag->appendChild(
            $this->createElement('CtrlSum', $this->intToCurrency($groupHeader->getControlSumCents()))
        );

        $initiatingParty = $this->createElement('InitgPty');
        $initiatingPartyName = $this->createElement('Nm', $groupHeader->getInitiatingPartyName());
        $initiatingParty->appendChild($initiatingPartyName);
        if ($groupHeader->getInitiatingPartyId() !== null) {
            $id = $this->createElement('Id', $groupHeader->getInitiatingPartyId());
            $initiatingParty->appendChild($id);
        }
        $groupHeaderTag->appendChild($initiatingParty);
        $this->currentTransfer->appendChild($groupHeaderTag);
    }

    protected function getFinancialInstitutionElement(?string $bic): DOMElement
    {
        $finInstitution = $this->createElement('FinInstnId');

        if ($bic === null) {
            /*
             * Use alternative identifier when BIC is not available
             * Per ISO 20022: <Othr> can be used for alternative identification.
             * We use this to circumvent some banks' strict BIC validation.
             */
            $other = $this->createElement('Othr');
            $id = $this->createElement('Id', 'NOTPROVIDED');
            $other->appendChild($id);
            $finInstitution->appendChild($other);
        } elseif (
            ($this->messageFormat->isDirectDebit() && $this->messageFormat->getVariant() == '1' && $this->messageFormat->getVersion() >= 3)
            ||
            ($this->messageFormat->isCreditTransfer() && $this->messageFormat->getVariant() == '1' && $this->messageFormat->getVersion() >= 4)
        ) {
            $finInstitution->appendChild($this->createElement('BICFI', $bic));
        } else {
            $finInstitution->appendChild($this->createElement('BIC', $bic));
        }

        return $finInstitution;
    }

    public function getIbanElement(string $iban): DOMElement
    {
        $id = $this->createElement('Id');
        $id->appendChild($this->createElement('IBAN', $iban));

        return $id;
    }

    /**
     * Create remittance element with un-structured message.
     */
    public function getRemittenceElement(string $message): DOMElement
    {
        $remittanceInformation = $this->createElement('RmtInf');
        $remittanceInformation->appendChild($this->createElement('Ustrd', $message));

        return $remittanceInformation;
    }

    /**
     * Create remittance element with structured creditor reference.
     */
    public function getStructuredRemittanceElement(TransferInformationInterface $transactionInformation): DOMElement
    {
        $creditorReference = $transactionInformation->getCreditorReference();
        $remittanceInformation = $this->createElement('RmtInf');

        $structured = $this->createElement('Strd');
        $creditorReferenceInformation = $this->createElement('CdtrRefInf');

        $tp = $this->createElement('Tp');
        $CdOrPrtry = $this->createElement('CdOrPrtry');
        $CdOrPrtry->appendChild($this->createElement('Cd', 'SCOR'));
        $tp->appendChild($CdOrPrtry);

        if ($transactionInformation->getCreditorReferenceType() != null) {
            $issuer = $this->createElement('Issr', $transactionInformation->getCreditorReferenceType());
            $tp->appendChild($issuer);
        }

        $reference = $this->createElement('Ref', $creditorReference);

        $creditorReferenceInformation->appendChild($tp);
        $creditorReferenceInformation->appendChild($reference);
        $structured->appendChild($creditorReferenceInformation);
        $remittanceInformation->appendChild($structured);

        return $remittanceInformation;
    }
}
