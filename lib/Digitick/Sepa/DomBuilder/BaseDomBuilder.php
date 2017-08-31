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

use Digitick\Sepa\GroupHeader;

abstract class BaseDomBuilder implements DomBuilderInterface
{
    /**
     * @var \DOMDocument
     */
    protected $doc;

    protected $root;

    /**
     * @var \DOMElement
     */
    protected $currentTransfer = null;

    /**
     * @var \DOMELement
     */
    protected $currentPayment = null;

    /**
     * @var string
     */
    protected $painFormat;

    /**
     * @param string $painFormat Supported format: 'pain.001.002.03', 'pain.001.001.03', 'pain.008.002.02', 'pain.008.001.02'
     * @param boolean $withSchemaLocation define if xsi:schemaLocation tag is added to root
     */
    public function __construct($painFormat, $withSchemaLocation = true)
    {
        $this->painFormat = $painFormat;
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $this->root = $this->doc->createElement('Document');
        $this->root->setAttribute('xmlns', sprintf('urn:iso:std:iso:20022:tech:xsd:%s', $painFormat));
        $this->root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        if ($withSchemaLocation) {
            $this->root->setAttribute('xsi:schemaLocation', "urn:iso:std:iso:20022:tech:xsd:$painFormat $painFormat.xsd");
        }

        $this->doc->appendChild($this->root);
    }

    /**
     * @param $name
     * @param null $value
     * @return \DOMElement
     */
    protected function createElement($name, $value = null)
    {
        if ($value !== null) {
            $elm = $this->doc->createElement($name);
            $elm->appendChild($this->doc->createTextNode($value));

            return $elm;
        } else {
            return $this->doc->createElement($name);
        }
    }

    /**
     * @return string
     */
    public function asXml()
    {
        return $this->doc->saveXML();
    }

    /**
     * Format an integer as a monetary value.
     */
    protected function intToCurrency($amount)
    {
        return sprintf('%01.2F', ($amount / 100));
    }

    /**
     * Add GroupHeader Information to the document
     *
     * @param GroupHeader $groupHeader
     * @return mixed
     */
    public function visitGroupHeader(GroupHeader $groupHeader)
    {
        $groupHeaderTag = $this->doc->createElement('GrpHdr');
        $messageId = $this->createElement('MsgId', $groupHeader->getMessageIdentification());
        $groupHeaderTag->appendChild($messageId);
        $creationDateTime = $this->createElement(
            'CreDtTm',
            $groupHeader->getCreationDateTime()->format($groupHeader->getCreationDateTimeFormat())
        );
        $groupHeaderTag->appendChild($creationDateTime);
        $groupHeaderTag->appendChild($this->createElement('NbOfTxs', $groupHeader->getNumberOfTransactions()));
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

    /**
     * @param string $bic
     * @return \DOMElement
     */
    protected function getFinancialInstitutionElement($bic)
    {
        $finInstitution = $this->createElement('FinInstnId');

        if (!$bic) {
            $other = $this->createElement('Othr');
            $id = $this->createElement('Id', 'NOTPROVIDED');
            $other->appendChild($id);
            $finInstitution->appendChild($other);
        } else {
            $finInstitution->appendChild($this->createElement('BIC', $bic));
        }

        return $finInstitution;
    }

    /**
     * @param string $iban
     * @return \DOMElement
     */
    public function getIbanElement($iban)
    {
        $id = $this->createElement('Id');
        $id->appendChild($this->createElement('IBAN', $iban));

        return $id;
    }

    /**
     * Create remittance element with un-structured message.
     *
     * @param string $message
     * @return \DOMElement
     */
    public function getRemittenceElement($message)
    {
        $remittanceInformation = $this->createElement('RmtInf');
        $remittanceInformation->appendChild($this->createElement('Ustrd', $message));

        return $remittanceInformation;
    }

    /**
     * Create remittance element with structured creditor reference.
     *
     * @param string $creditorReference
     * @return \DOMElement
     */
    public function getStructuredRemittanceElement($creditorReference = null)
    {
        $remittanceInformation = $this->createElement('RmtInf');

        $structured = $this->createElement('Strd');
        $creditorReferenceInformation = $this->createElement('CdtrRefInf');

        $tp = $this->createElement('Tp');
        $CdOrPrtry = $this->createElement('CdOrPrtry');
        $CdOrPrtry->appendChild($this->createElement('Cd', 'SCOR'));
        $tp->appendChild($CdOrPrtry);

        $reference = $this->createElement('Ref', $creditorReference);

        $creditorReferenceInformation->appendChild($tp);
        $creditorReferenceInformation->appendChild($reference);
        $structured->appendChild($creditorReferenceInformation);
        $remittanceInformation->appendChild($structured);

        return $remittanceInformation;
    }

}
