<?php

/**
 * Generate a SEPA transfer file.
 *
 * ALPHA QUALITY SOFTWARE
 * Do NOT use in production environments!!!
 *
 * @copyright © Digitick <www.digitick.net> 2012
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
 * 
 * @author Jérémy Cambon
 * @author Ianaré Sévi
 * @author Vincent MOMIN
 */

/**
 * SEPA payments file object.
 */
class SepaTransferFile
{
	/**
	 * @var boolean If true, the transaction will never be executed.
	 */
	public $isTest = false;
	/**
	 * @var string Unambiguously identify the message.
	 */
	public $messageIdentification;
	/**
	 * @var string Debtor's name.
	 */
	public $debtorName;
	/**
	 * @var string Debtor's account IBAN. 
	 */
	public $debtorAccountIBAN;
	/**
	 * @var string Debtor's account bank BIC code.
	 */
	public $debtorAgentBIC;
	/**
	 * @var string Debtor's account ISO currency code.
	 */
	public $debtorAccountCurrency = 'EUR';
	/**
	 * @var string Payment sender's name.
	 */
	public $initiatingPartyName;
	/**
	 * @var string Payment sender's ID (for example: the tax ID).
	 */
	public $initiatingPartyId;
	/**
	 * @var string Unambiguously identify the payment.
	 */
	public $paymentInfoId;
	/**
	 * @var string Purpose of the transaction(s).
	 */
	public $categoryPurposeCode;

	/**
	 * @var integer
	 */
	protected $headerControlSumCents = 0;
	/**
	 * @var integer
	 */
	protected $paymentControlSumCents = 0;
	/**
	 * @var SepaCreditTransfer[] 
	 */
	protected $creditTransfers = array();
	/**
	 * @var integer Number of payment transactions.
	 */
	protected $numberOfTransactions = 0;
	/**
	 * @var string Payment method.
	 */
	protected $paymentMethod = 'TRF';
	/**
	 * @var string Local service instrument code.
	 */
	protected $localInstrumentCode = 'CORE';
	/**
	 * @var SimpleXMLElement
	 */
	protected $xml;

	const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"></Document>';
	
	public function __construct()
	{
		$this->xml = simplexml_load_string(self::INITIAL_STRING);
		$this->xml->addChild('CstmrCdtTrfInitn');
	}
	
	/**
	 * Set the payment method.
	 * @param string $method
	 * @throws Exception
	 */
	public function setPaymentMethod($method)
	{
		$method = strtoupper($method);
		if (!in_array($method, array('CHK', 'TRF', 'TRA'))) {
			throw new Exception("Invalid Payment Method: $method");
		}
		$this->paymentMethod = $method;
	}
	
	/**
	 * Set the local service instrument code.
	 * @param string $code
	 * @throws Exception
	 */
	public function setLocalInstrumentCode($code)
	{
		$code = strtoupper($code);
		if (!in_array($code, array('CORE', 'B2B'))) {
			throw new Exception("Invalid Local Instrument Code: $code");
		}
		$this->localInstrumentCode = $code;
	}

	/**
	 * Return the XML string.
	 * @return string
	 */
	public function asXML()
	{
		$this->generateXml();
		return $this->xml->asXML();
	}

	/**
	 * Output the XML string to the screen.
	 */
	public function outputXML()
	{
		$this->generateXml();
		header('Content-type: text/xml');
		echo $this->xml->asXML();
	}
        
        /**
	 * Download the XML string into XML File
	 */
        public function downloadXML()
	{
		$this->generateXml();
		header("Content-type: text/xml");
		header("Content-disposition: attachment; filename=sepa_" .date("dmY-His").".xml");
		echo $this->xml->asXML();
		exit();
        }
	/**
	 * Get the header control sum in cents.
	 * @return integer
	 */
	public function getHeaderControlSumCents()
	{
		return $this->headerControlSumCents;
	}

	/**
	 * Get the payment control sum in cents.
	 * @return integer
	 */
	public function getPaymentControlSumCents()
	{
		return $this->paymentControlSumCents;
	}
	
	/**
	 * Add a credit transfer transaction.
	 * @param array $transferInfo
	 */
	public function addCreditTransfer(array $transferInfo)
	{
		$transfer = new SepaCreditTransfer;
		$transfer->id = $transferInfo['CreditorPaymentId'];
		$transfer->endToEndId = $this->messageIdentification . '/' . $this->numberOfTransactions;
		$transfer->currency = $transferInfo['CreditorPaymentCurrency'];
		$transfer->setAmount($transferInfo['CreditorPaymentAmount']);
		$transfer->creditorBIC = $transferInfo['CreditorBIC'];
		$transfer->creditorName = $transferInfo['CreditorName'];
		$transfer->creditorAccountIBAN = $transferInfo['CreditorAccountIBAN'];
		$transfer->remittanceInformation = $transferInfo['RemittanceInformation'];
		$this->creditTransfers[] = $transfer;
		$this->numberOfTransactions++;
		$this->headerControlSumCents += $transfer->getAmountCents();
		$this->paymentControlSumCents += $transfer->getAmountCents();
	}

	/**
	 * Generate the XML structure.
	 */
	protected function generateXml()
	{
		$datetime = new DateTime();
		$creationDateTime = $datetime->format('Y-m-d\TH:i:s');
		$requestedExecutionDate = $datetime->format('Y-m-d');

		// -- 1: Group Header -- \\
		
		$GrpHdr = $this->xml->CstmrCdtTrfInitn->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageIdentification);
		$GrpHdr->addChild('CreDtTm', $creationDateTime);
		if ($this->isTest) {
			$GrpHdr->addChild('Authstn')->addChild('Prtry', 'TEST');
		}
		$GrpHdr->addChild('NbOfTxs', $this->numberOfTransactions);
		$GrpHdr->addChild('CtrlSum', $this->intToCurrency($this->headerControlSumCents));
		$GrpHdr->addChild('InitgPty')->addChild('Nm', $this->initiatingPartyName);
		if (isset($this->initiatingPartyId))
			$GrpHdr->addChild('InitgPty')->addChild('Id', $this->initiatingPartyId);
		
		// -- 2: Payment Information --\\
		
		$PmtInf = $this->xml->CstmrCdtTrfInitn->addChild('PmtInf');
		$PmtInf->addChild('PmtInfId', $this->paymentInfoId);
		if (isset($this->categoryPurposeCode))
			$PmtInf->addChild('CtgyPurp')->addChild('Cd', $this->categoryPurposeCode);

		$PmtInf->addChild('PmtMtd', $this->paymentMethod);
		$PmtInf->addChild('NbOfTxs', $this->numberOfTransactions);
		$PmtInf->addChild('CtrlSum', $this->intToCurrency($this->paymentControlSumCents));
		$PmtInf->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', 'SEPA');
		$PmtInf->PmtTpInf->addChild('LclInstr')->addChild('Cd', $this->localInstrumentCode);
		$PmtInf->addChild('ReqdExctnDt', $requestedExecutionDate);
		$PmtInf->addChild('Dbtr')->addChild('Nm', $this->debtorName);

		$DbtrAcct = $PmtInf->addChild('DbtrAcct');
		$DbtrAcct->addChild('Id')->addChild('IBAN', $this->debtorAccountIBAN);
		$DbtrAcct->addChild('Ccy', $this->debtorAccountCurrency);

		$PmtInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->debtorAgentBIC);
		$PmtInf->addChild('ChrgBr', 'SLEV');
		
		// -- 3: Credit Transfer Transaction Information --\\

		foreach ($this->creditTransfers as $transfer) {
			$amount = $this->intToCurrency($transfer->getAmountCents());

			$CdtTrfTxInf = $PmtInf->addChild('CdtTrfTxInf');
			$PmtId = $CdtTrfTxInf->addChild('PmtId');
			$PmtId->addChild('InstrId', $transfer->id);
			$PmtId->addChild('EndToEndId', $transfer->endToEndId);
			$CdtTrfTxInf->addChild('Amt')->addChild('InstdAmt', $amount)->addAttribute('Ccy', $transfer->currency);
			$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $transfer->creditorBIC);
			$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', $transfer->creditorName);
			$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $transfer->creditorAccountIBAN);
			$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $transfer->remittanceInformation);
		}
	}

	/**
	 * Format an integer as a monetary value.
	 */
	protected function intToCurrency($amount)
	{
		return sprintf("%01.2f", ($amount/100));
	}
}

/**
 * SEPA Credit Transfer Transaction Information.
 */
class SepaCreditTransfer
{
	/**
	 * @var string Payment ID.
	 */
	public $id;
	/**
	 * @var string
	 */
	public $endToEndId;
	/**
	 * @var string ISO currency code
	 */
	public $currency;
	/**
	 * @var string Account bank's BIC
	 */
	public $creditorBIC;
	/**
	 * @var string Name
	 */
	public $creditorName;
	/**
	 * @var string account IBAN
	 */
	public $creditorAccountIBAN;
	/**
	 * @var string Remittance information.
	 */
	public $remittanceInformation;

	/**
	 * @var integer Transfer amount in cents.
	 */
	protected $amountCents;

	/**
	 * Set the transfer amount.
	 * @param mixed $amount
	 */
	public function setAmount($amount)
	{
		$amount += 0;
		if (is_float($amount))
			$amount = (integer)($amount * 100);

		$this->amountCents = $amount;
	}

	/**
	 * Get the transfer amount in cents.
	 * @return integer
	 */
	public function getAmountCents()
	{
		return $this->amountCents;
	}
}

