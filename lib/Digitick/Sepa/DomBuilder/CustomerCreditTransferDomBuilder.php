<?php

namespace Digitick\Sepa\DomBuilder;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\TransferFileInterface;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\TransferInformationInterface;

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

class CustomerCreditTransferDomBuilder extends BaseDomBuilder {

    function __construct() {
        parent::__construct(CustomerCreditTransferFile::PAIN_FORMAT);
    }


    /**
     * Build the root of the document
     *
     * @param TransferFileInterface $transferFile
     * @return mixed
     */
    public function visitTransferFile(TransferFileInterface $transferFile) {
        $this->currentTransfer = $this->doc->createElement('CstmrCdtTrfInitn');
        $this->root->appendChild($this->currentTransfer);
    }

    /**
     * Crawl PaymentInformation containing the Transactions
     *
     * @param PaymentInformation $paymentInformation
     * @return mixed
     */
    public function visitPaymentInformation(PaymentInformation $paymentInformation) {
        $this->currentPayment = $this->createElement('PmtInf');
        $this->currentPayment->appendChild($this->createElement('PmtInfId', $paymentInformation->getId()));
        if ($paymentInformation->getCategoryPurposeCode()) {
            $categoryPurpose = $this->createElement('CtgyPurp');
            $categoryPurpose->appendChild($this->createElement('Cd', $paymentInformation->getCategoryPurposeCode()));
            $this->currentPayment->appendChild($categoryPurpose);
        }
        $this->currentPayment->appendChild($this->createElement('PmtMtd', $paymentInformation->getPaymentMethod()));
        $this->currentPayment->appendChild($this->createElement('NbOfTxs', $paymentInformation->getNumberOfTransactions()));

        $this->currentPayment->appendChild($this->createElement('CtrlSum', $this->intToCurrency($paymentInformation->getControlSumCents())));

        $paymentTypeInformation = $this->createElement('PmtTpInf');
        $serviceLevel = $this->createElement('SvcLvl');
        $serviceLevel->appendChild($this->createElement('Cd', 'SEPA'));
        $paymentTypeInformation->appendChild($serviceLevel);
        $this->currentPayment->appendChild($paymentTypeInformation);
        if ($paymentInformation->getLocalInstrumentCode()) {
            $localInstrument = $this->createElement('LclInstr');
            $localInstrument->appendChild($this->createElement('Cd', $paymentInformation->getLocalInstrumentCode()));
            $this->currentPayment->appendChild($localInstrument);
        }

        $this->currentPayment->appendChild($this->createElement('ReqdExctnDt', $paymentInformation->getDueDate()));
        $debtor = $this->createElement('Dbtr');
        $debtor->appendChild($this->createElement('Nm', htmlentities($paymentInformation->getOriginName())));
        $this->currentPayment->appendChild($debtor);

        $debtorAccount = $this->createElement('DbtrAcct');
        $id = $this->createElement('Id');
        $id->appendChild($this->createElement('IBAN', $paymentInformation->getOriginAccountIBAN()));
        $debtorAccount->appendChild($id);
        if($paymentInformation->getOriginAccountCurrency()) {
            $debtorAccount->appendChild($this->createElement('Ccy', $paymentInformation->getOriginAccountCurrency()));
        }
        $this->currentPayment->appendChild($debtorAccount);

        $debtorAgent = $this->createElement('DbtrAgt');
        $financialInstitutionId = $this->createElement('FinInstnId');
        $financialInstitutionId->appendChild($this->createElement('BIC', $paymentInformation->getOriginAgentBIC()));
        $debtorAgent->appendChild($financialInstitutionId);
        $this->currentPayment->appendChild($debtorAgent);

        $this->currentPayment->appendChild($this->createElement('ChrgBr','SLEV'));
        $this->currentTransfer->appendChild($this->currentPayment);
    }

    /**
     * Crawl Transactions
     *
     * @param TransferInformationInterface $transactionInformation
     * @return mixed
     */
    public function visitTransferInformation(TransferInformationInterface $transactionInformation) {
        /** @var $transactionInformation  CustomerCreditTransferInformation */
        $CdtTrfTxInf = $this->createElement('CdtTrfTxInf');

        // Payment ID 2.28
        $PmtId = $this->createElement('PmtId');
        $PmtId->appendChild($this->createElement('EndToEndId', $transactionInformation->getEndToEndIdentification()));
        $CdtTrfTxInf->appendChild($PmtId);

        // Amount 2.42
        $amount = $this->createElement('Amt');
        $instructedAmount = $this->createElement('InstdAmt', $this->intToCurrency($transactionInformation->getTransferAmount()));
        $instructedAmount->setAttribute('Ccy', $transactionInformation->getCurrency());
        $amount->appendChild($instructedAmount);
        $CdtTrfTxInf->appendChild($amount);

        //Creditor Agent 2.77
        $creditorAgent = $this->createElement('CdtrAgt');
        $financialInstitution = $this->createElement('FinInstnId');
        $financialInstitution->appendChild($this->createElement('BIC', $transactionInformation->getBic()));
        $creditorAgent->appendChild($financialInstitution);
        $CdtTrfTxInf->appendChild($creditorAgent);

        // Creditor 2.79
        $creditor = $this->createElement('Cdtr');
        $creditor->appendChild($this->createElement('Nm', htmlentities($transactionInformation->getCreditorName())));
        $CdtTrfTxInf->appendChild($creditor);

        // CreditorAccount 2.80
        $creditorAccount = $this->createElement('CdtrAcct');
        $id = $this->createElement('Id');
        $id->appendChild($this->createElement('IBAN', $transactionInformation->getIban()));
        $creditorAccount->appendChild($id);
        $CdtTrfTxInf->appendChild($creditorAccount);

        // remittance 2.98 2.99
        $remittanceInformation = $this->getRemittenceElement($transactionInformation->getRemittanceInformation());
        $CdtTrfTxInf->appendChild($remittanceInformation);

        $this->currentPayment->appendChild($CdtTrfTxInf);
    }


}