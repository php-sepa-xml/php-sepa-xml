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

use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\TransferFileInterface;
use Digitick\Sepa\GroupHeader;

class CustomerDirectDebitTransferDomBuilder extends BaseDomBuilder
{

    public function __construct(string $painFormat = 'pain.008.002.02', $withSchemaLocation = true)
    {
        parent::__construct($painFormat, $withSchemaLocation);
    }

    /**
     * Build the root of the document
     */
    public function visitTransferFile(TransferFileInterface $transferFile): void
    {
        $this->currentTransfer = $this->doc->createElement('CstmrDrctDbtInitn');
        $this->root->appendChild($this->currentTransfer);
    }

    /**
     * Crawl PaymentInformation containing the Transactions
     */
    public function visitPaymentInformation(PaymentInformation $paymentInformation): void
    {
        $this->currentPayment = $this->createElement('PmtInf');
        $this->currentPayment->appendChild($this->createElement('PmtInfId', $paymentInformation->getId()));
        $this->currentPayment->appendChild($this->createElement('PmtMtd', $paymentInformation->getPaymentMethod()));

        if ($paymentInformation->getBatchBooking() !== null) {
            $this->currentPayment->appendChild($this->createElement('BtchBookg', $paymentInformation->getBatchBooking() ? 'true' : 'false'));
        }

        $this->currentPayment->appendChild(
            $this->createElement('NbOfTxs', $paymentInformation->getNumberOfTransactions())
        );

        $this->currentPayment->appendChild(
            $this->createElement('CtrlSum', $this->intToCurrency($paymentInformation->getControlSumCents()))
        );

        $paymentTypeInformation = $this->createElement('PmtTpInf');
        if ($paymentInformation->getInstructionPriority() && $this->painFormat === 'pain.008.001.02') {
            $instructionPriority = $this->createElement('InstrPrty', $paymentInformation->getInstructionPriority());
            $paymentTypeInformation->appendChild($instructionPriority);
        }
        $serviceLevel = $this->createElement('SvcLvl');
        $serviceLevel->appendChild($this->createElement('Cd', 'SEPA'));
        $paymentTypeInformation->appendChild($serviceLevel);
        $this->currentPayment->appendChild($paymentTypeInformation);
        $localInstrument = $this->createElement('LclInstrm');
        if ($paymentInformation->getLocalInstrumentCode()) {
            $localInstrument->appendChild($this->createElement('Cd', $paymentInformation->getLocalInstrumentCode()));
        } else {
            $localInstrument->appendChild($this->createElement('Cd', 'CORE'));
        }
        $paymentTypeInformation->appendChild($localInstrument);

        $paymentTypeInformation->appendChild($this->createElement('SeqTp', $paymentInformation->getSequenceType()));

        $this->currentPayment->appendChild($this->createElement('ReqdColltnDt', $paymentInformation->getDueDate()));
        $creditor = $this->createElement('Cdtr');
        $creditor->appendChild($this->createElement('Nm', $paymentInformation->getOriginName()));
        $this->currentPayment->appendChild($creditor);

        // <CdtrAcct>
        $creditorAccount = $this->createElement('CdtrAcct');
        $id = $this->getIbanElement($paymentInformation->getOriginAccountIBAN());
        $creditorAccount->appendChild($id);
        $this->currentPayment->appendChild($creditorAccount);

        // <CdtrAgt>
        $creditorAgent = $this->createElement('CdtrAgt');
        $creditorAgent->appendChild($this->getFinancialInstitutionElement($paymentInformation->getOriginAgentBIC()));
        $this->currentPayment->appendChild($creditorAgent);

        $this->currentPayment->appendChild($this->createElement('ChrgBr', 'SLEV'));

        $creditorSchemeId = $this->createElement('CdtrSchmeId');
        $id = $this->createElement('Id');
        $privateId = $this->createElement('PrvtId');
        $other = $this->createElement('Othr');
        $other->appendChild($this->createElement('Id', $paymentInformation->getCreditorId()));
        $schemeName = $this->createElement('SchmeNm');
        $schemeName->appendChild($this->createElement('Prtry', 'SEPA'));
        $other->appendChild($schemeName);
        $privateId->appendChild($other);
        $id->appendChild($privateId);
        $creditorSchemeId->appendChild($id);
        $this->currentPayment->appendChild($creditorSchemeId);

        $this->currentTransfer->appendChild($this->currentPayment);
    }

    /**
     * Crawl Transactions
     */
    public function visitTransferInformation(TransferInformationInterface $transactionInformation): void
    {
        if (!isset($this->currentPayment)) {
            throw new \LogicException('Payment information have to be added before any transaction informations can be added.');
        }

        if (!$transactionInformation instanceof CustomerDirectDebitTransferInformation) {
            throw new \InvalidArgumentException(sprintf(
                'Expected argument to be for type "%s", but "%s" given.',
                CustomerDirectDebitTransferInformation::class,
                get_class($transactionInformation)
            ));
        }

        /** @var  $transactionInformation CustomerDirectDebitTransferInformation */
        $directDebitTransactionInformation = $this->createElement('DrctDbtTxInf');

        $paymentId = $this->createElement('PmtId');
        $paymentId->appendChild(
            $this->createElement('EndToEndId', $transactionInformation->getEndToEndIdentification())
        );
        $directDebitTransactionInformation->appendChild($paymentId);

        $instructedAmount = $this->createElement(
            'InstdAmt',
            $this->intToCurrency($transactionInformation->getTransferAmount())
        );
        $instructedAmount->setAttribute('Ccy', $transactionInformation->getCurrency());
        $directDebitTransactionInformation->appendChild($instructedAmount);

        $directDebitTransaction = $this->createElement('DrctDbtTx');
        $mandateRelatedInformation = $this->createElement('MndtRltdInf');
        $directDebitTransaction->appendChild($mandateRelatedInformation);
        $mandateRelatedInformation->appendChild(
            $this->createElement('MndtId', $transactionInformation->getMandateId())
        );
        $mandateRelatedInformation->appendChild(
            $this->createElement('DtOfSgntr', $transactionInformation->getMandateSignDate()->format('Y-m-d'))
        );
        $directDebitTransactionInformation->appendChild($directDebitTransaction);

        // TODO add the possibility to add CreditorSchemeId on transfer level

        $debtorAgent = $this->createElement('DbtrAgt');
        $debtorAgent->appendChild($this->getFinancialInstitutionElement($transactionInformation->getBic()));
        $directDebitTransactionInformation->appendChild($debtorAgent);

        $debtor = $this->createElement('Dbtr');
        $debtor->appendChild($this->createElement('Nm', $transactionInformation->getDebitorName()));

        // Add address data to debtor node
        if (in_array($this->painFormat, array('pain.008.003.02', 'pain.008.001.02'))) {
            $postalAddress = $this->createElement('PstlAdr');

            // Th elements street number, building number, post code and town name
            // are not supported by 'pain.008.003.02'.
            if (in_array($this->painFormat, ['pain.008.001.02'])) {
                if (!empty($transactionInformation->getStreetName())) {
                    $postalAddress->appendChild($this->createElement('StrtNm', $transactionInformation->getStreetName()));
                }

                if (!empty($transactionInformation->getBuildingNumber())) {
                    $postalAddress->appendChild($this->createElement('BldgNb', $transactionInformation->getBuildingNumber()));
                }

                if (!empty($transactionInformation->getPostCode())) {
                    $postalAddress->appendChild($this->createElement('PstCd', $transactionInformation->getPostCode()));
                }

                if (!empty($transactionInformation->getTownName())) {
                    $postalAddress->appendChild($this->createElement('TwnNm', $transactionInformation->getTownName()));
                }
            }

            if ((bool)$transactionInformation->getCountry()) {
                $postalAddress->appendChild($this->createElement('Ctry', $transactionInformation->getCountry()));
            }
            if ((bool)$transactionInformation->getPostalAddress()) {
                $postalAddressData = $transactionInformation->getPostalAddress();
                if (is_array($postalAddressData)) {
                    foreach($postalAddressData as $postalAddressLine) {
                        $postalAddress->appendChild($this->createElement('AdrLine', $postalAddressLine));
                    }
                } else {
                    $postalAddress->appendChild($this->createElement('AdrLine', $postalAddressData));
                }
            }

            if ($postalAddress->childNodes->length > 0) {
                $debtor->appendChild($postalAddress);
            }
        }
        $directDebitTransactionInformation->appendChild($debtor);

        $debtorAccount = $this->createElement('DbtrAcct');
        $debtorAccount->appendChild($this->getIbanElement($transactionInformation->getIban()));
        $directDebitTransactionInformation->appendChild($debtorAccount);

        if (strlen((string)$transactionInformation->getCreditorReference()) > 0)
        {
            $directDebitTransactionInformation->appendChild(
                $this->getStructuredRemittanceElement($transactionInformation)
            );
        } elseif (strlen((string)$transactionInformation->getRemittanceInformation()) > 0) {
            $directDebitTransactionInformation->appendChild(
                $this->getRemittenceElement($transactionInformation->getRemittanceInformation())
            );
        }

        if ($transactionInformation->hasAmendments()) {
            $amendmentIndicator = $this->createElement('AmdmntInd', 'true');
            $mandateRelatedInformation->appendChild($amendmentIndicator);

            $amendmentInformationDetails = $this->createElement('AmdmntInfDtls');

            if ($transactionInformation->hasAmendedDebtorAccount() || $transactionInformation->getOriginalDebtorIban() !== null) {
                $originalDebtorAccount = $this->createElement('OrgnlDbtrAcct');
                $identification = $this->createElement('Id');
                $other = $this->createElement('Othr');
                // Same Mandate New Debtor Account
                $id = $this->createElement('Id', 'SMNDA');

                $other->appendChild($id);
                $identification->appendChild($other);
                $originalDebtorAccount->appendChild($identification);
                $amendmentInformationDetails->appendChild($originalDebtorAccount);
            }

            if ($transactionInformation->getOriginalMandateId() !== null) {
                $originalMandateId = $this->createElement('OrgnlMndtId', $transactionInformation->getOriginalMandateId());
                $amendmentInformationDetails->appendChild($originalMandateId);
            }

            $mandateRelatedInformation->appendChild($amendmentInformationDetails);
        }

        $this->currentPayment->appendChild($directDebitTransactionInformation);
    }

    /**
     * Add the specific OrgId element for the format 'pain.008.001.02'
     */
    public function visitGroupHeader(GroupHeader $groupHeader): void
    {
        parent::visitGroupHeader($groupHeader);

        if ($groupHeader->getInitiatingPartyId() !== null && in_array($this->painFormat , array('pain.008.001.02','pain.008.003.02'))) {
            $newId = $this->createElement('Id');
            $orgId = $this->createElement('OrgId');
            $othr  = $this->createElement('Othr');
            $othr->appendChild($this->createElement('Id', $groupHeader->getInitiatingPartyId()));

            if ($groupHeader->getIssuer()) {
                $othr->appendChild($this->createElement('Issr', $groupHeader->getIssuer()));
            }

            $orgId->appendChild($othr);
            $newId->appendChild($orgId);

            $xpath = new \DOMXpath($this->doc);
            $items = $xpath->query('GrpHdr/InitgPty/Id', $this->currentTransfer);
            $oldId = $items->item(0);

            $oldId->parentNode->replaceChild($newId, $oldId);
        }
    }
}
