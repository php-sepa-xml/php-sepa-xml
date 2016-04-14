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

use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\TransferFileInterface;
use Digitick\Sepa\GroupHeader;

class CustomerDirectDebitTransferDomBuilder extends BaseDomBuilder
{

    public function __construct($painFormat = 'pain.008.002.02')
    {
        parent::__construct($painFormat);
    }

    /**
     * Build the root of the document
     *
     * @param TransferFileInterface $transferFile
     * @return mixed
     */
    public function visitTransferFile(TransferFileInterface $transferFile)
    {
        $this->currentTransfer = $this->doc->createElement('CstmrDrctDbtInitn');
        $this->root->appendChild($this->currentTransfer);
    }

    /**
     * Crawl PaymentInformation containing the Transactions
     *
     * @param PaymentInformation $paymentInformation
     * @return mixed
     */
    public function visitPaymentInformation(PaymentInformation $paymentInformation)
    {
        $this->currentPayment = $this->createElement('PmtInf');
        $this->currentPayment->appendChild($this->createElement('PmtInfId', $paymentInformation->getId()));
        $this->currentPayment->appendChild($this->createElement('PmtMtd', $paymentInformation->getPaymentMethod()));

        if ($paymentInformation->getBatchBooking() !== null) {
            $this->currentPayment->appendChild($this->createElement('BtchBookg', $paymentInformation->getBatchBooking()));
        }

        $this->currentPayment->appendChild(
            $this->createElement('NbOfTxs', $paymentInformation->getNumberOfTransactions())
        );

        $this->currentPayment->appendChild(
            $this->createElement('CtrlSum', $this->intToCurrency($paymentInformation->getControlSumCents()))
        );

        $paymentTypeInformation = $this->createElement('PmtTpInf');
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
        if ($paymentInformation->getOriginAgentBIC()) {
            $creditorAgent = $this->createElement('CdtrAgt');
            $creditorAgent->appendChild($this->getFinancialInstitutionElement($paymentInformation->getOriginAgentBIC()));
            $this->currentPayment->appendChild($creditorAgent);
        }

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
     *
     * @param TransferInformationInterface $transactionInformation
     * @return mixed
     */
    public function visitTransferInformation(TransferInformationInterface $transactionInformation)
    {
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
        $directDebitTransactionInformation->appendChild($debtor);

        $debtorAccount = $this->createElement('DbtrAcct');
        $debtorAccount->appendChild($this->getIbanElement($transactionInformation->getIban()));
        $directDebitTransactionInformation->appendChild($debtorAccount);

        $directDebitTransactionInformation->appendChild(
            $this->getRemittenceElement($transactionInformation->getRemittanceInformation())
        );

        if ($transactionInformation->hasAmendments()) {
            $amendmentIndicator = $this->createElement('AmdmntInd', 'true');
            $mandateRelatedInformation->appendChild($amendmentIndicator);

            $amendmentInformationDetails = $this->createElement('AmdmntInfDtls');

            if ($transactionInformation->hasAmendedDebtorAgent()) {
                $originalDebtorAgent = $this->createElement('OrgnlDbtrAgt');
                $financialInstitutionIdentification = $this->createElement('FinInstnId');
                $other = $this->createElement('Othr');
                // Same Mandate New Debtor Agent
                $id = $this->createElement('Id', 'SMNDA');

                $other->appendChild($id);
                $financialInstitutionIdentification->appendChild($other);
                $originalDebtorAgent->appendChild($financialInstitutionIdentification);
                $amendmentInformationDetails->appendChild($originalDebtorAgent);
            }

            if ($transactionInformation->getOriginalDebtorIban() !== null) {
                $originalDebtorAccount = $this->createElement('OrgnlDbtrAcct');
                $originalDebtorAccount->appendChild($this->getIbanElement($transactionInformation->getOriginalDebtorIban()));
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
     *
     * @param  GroupHeader $groupHeader
     * @return mixed
     */
    public function visitGroupHeader(GroupHeader $groupHeader)
    {
        parent::visitGroupHeader($groupHeader);

        if ($groupHeader->getInitiatingPartyId() !== null && $this->painFormat === 'pain.008.001.02') {
            $newId = $this->createElement('Id');
            $orgId = $this->createElement('OrgId');
            $othr  = $this->createElement('Othr');
            $othr->appendChild($this->createElement('Id', $groupHeader->getInitiatingPartyId()));
            $orgId->appendChild($othr);
            $newId->appendChild($orgId);

            $xpath = new \DOMXpath($this->doc);
            $items = $xpath->query('GrpHdr/InitgPty/Id', $this->currentTransfer);
            $oldId = $items->item(0);

            $oldId->parentNode->replaceChild($newId, $oldId);
        }
    }
}
