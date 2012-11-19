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
 * Base class.
 */
abstract class SepaFileSection
{
	/**
	 * Format an integer as a monetary value.
	 */
	protected function intToCurrency($amount)
	{
		return sprintf("%01.2f", ($amount / 100));
	}
}

/**
 * SEPA payments file object.
 */
class SepaTransferFile extends SepaFileSection
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
	 * @var string Payment sender's name.
	 */
	public $initiatingPartyName;
	/**
	 * @var string Payment sender's ID (for example: the tax ID).
	 */
	public $initiatingPartyId;
	/**
	 * @var string Purpose of the transaction(s).
	 */
	public $categoryPurposeCode;
	/**
	 * @var string NOT USED - reserve for future.
	 */
	public $grouping;

	/**
	 * @var integer Sum of all transactions in all payments regardless of currency.
	 */
	protected $controlSumCents = 0;
	/**
	 * @var integer Number of payment transactions.
	 */
	protected $numberOfTransactions = 0;
	/**
	 * @var SimpleXMLElement
	 */
	protected $xml;
	/**
	 * @var SepaPaymentInfo
	 */
	protected $payment;

	const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"></Document>';

	public function __construct()
	{
		$this->xml = simplexml_load_string(self::INITIAL_STRING);
		$this->xml->addChild('CstmrCdtTrfInitn');
		$this->payment = new SepaPaymentInfo;
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
		header('Content-disposition: attachment; filename=sepa_' . date('dmY-His') . '.xml');
		echo $this->xml->asXML();
		exit();
	}

	/**
	 * Get the header control sum in cents.
	 * @return integer
	 */
	public function getHeaderControlSumCents()
	{
		return $this->controlSumCents;
	}

	/**
	 * Get the payment control sum in cents.
	 * @return integer
	 */
	public function getPaymentControlSumCents()
	{
		return $this->payment->controlSumCents;
	}
	
	/**
	 * Set the information for the "Payment Information" block.
	 * @param array $paymentInfo
	 */
	public function setPaymentInfo(array $paymentInfo)
	{
		$values = array(
			'id', 'categoryPurposeCode', 'debtorName', 'debtorAccountIBAN',
			'debtorAgentBIC', 'debtorAccountCurrency'
		);
		foreach($values as $name) {
			$this->payment->$name = $paymentInfo[$name];
		}
		$this->payment->setLocalInstrumentCode($paymentInfo['localInstrumentCode']);
		$this->payment->setPaymentMethod($paymentInfo['paymentMethod']);
	}

	/**
	 * Add a "Credit Transfer Transaction Information" block to the payment.
	 * @param array $transferInfo
	 */
	public function addCreditTransfer(array $transferInfo)
	{
		$transfer = new SepaCreditTransfer;
		$values = array(
			'id', 'currency', 'amount', 'creditorBIC', 'creditorName',
			'creditorAccountIBAN', 'remittanceInformation'
		);
		foreach($values as $name) {
			$transfer->$name = $transferInfo[$name];
		}
		$this->payment->addCreditTransfer($transfer);
		
		$this->numberOfTransactions += $this->payment->getNumberOfTransactions();
		$this->controlSumCents += $this->payment->getControlSumCents();
	}

	/**
	 * Generate the XML structure.
	 */
	protected function generateXml()
	{
		$datetime = new DateTime();
		$creationDateTime = $datetime->format('Y-m-d\TH:i:s');

		// -- Group Header -- \\

		$GrpHdr = $this->xml->CstmrCdtTrfInitn->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageIdentification);
		$GrpHdr->addChild('CreDtTm', $creationDateTime);
		if ($this->isTest) {
			$GrpHdr->addChild('Authstn')->addChild('Prtry', 'TEST');
		}
		$GrpHdr->addChild('NbOfTxs', $this->numberOfTransactions);
		$GrpHdr->addChild('CtrlSum', $this->intToCurrency($this->controlSumCents));
		$GrpHdr->addChild('InitgPty')->addChild('Nm', $this->initiatingPartyName);
		if (isset($this->initiatingPartyId))
			$GrpHdr->addChild('InitgPty')->addChild('Id', $this->initiatingPartyId);

		// -- Payment Information --\\
		$this->xml = $this->payment->generateXml($this->xml);
	}
}

/**
 * SEPA file "Payment Information" block.
 */
class SepaPaymentInfo extends SepaFileSection
{
	/**
	 * @var string Unambiguously identify the payment.
	 */
	public $id;
	/**
	 * @var string Purpose of the transaction(s).
	 */
	public $categoryPurposeCode;
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
	 * @var string Payment method.
	 */
	protected $paymentMethod = 'TRF';
	/**
	 * @var string Local service instrument code.
	 */
	protected $localInstrumentCode;
	/**
	 * @var integer
	 */
	protected $controlSumCents = 0;
	/**
	 * @var integer Number of payment transactions.
	 */
	protected $numberOfTransactions = 0;
	/**
	 * @var SepaCreditTransfer[]
	 */
	protected $creditTransfers = array();

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
	 * @return integer
	 */
	public function getNumberOfTransactions()
	{
		return $this->numberOfTransactions;
	}

	/**
	 * @return integer
	 */
	public function getControlSumCents()
	{
		return $this->controlSumCents;
	}

	/**
	 * Add a credit transfer transaction.
	 * @param array $transferInfo
	 */
	public function addCreditTransfer(SepaCreditTransfer $transfer)
	{
		$transfer->endToEndId = $this->messageIdentification . '/' . $this->numberOfTransactions;
		$this->creditTransfers[] = $transfer;
		$this->numberOfTransactions++;
		$this->controlSumCents += $transfer->getAmountCents();
	}

	/**
	 * DO NOT CALL THIS FUNCTION DIRECTLY!
	 * 
	 * @param SimpleXMLElement $xml
	 * @return SimpleXMLElement
	 */
	public function generateXml(SimpleXMLElement $xml)
	{
		$requestedExecutionDate = $datetime->format('Y-m-d');
		
		// -- Payment Information --\\

		$PmtInf = $xml->CstmrCdtTrfInitn->addChild('PmtInf');
		$PmtInf->addChild('PmtInfId', $this->id);
		if (isset($this->categoryPurposeCode))
			$PmtInf->addChild('CtgyPurp')->addChild('Cd', $this->categoryPurposeCode);

		$PmtInf->addChild('PmtMtd', $this->paymentMethod);
		$PmtInf->addChild('NbOfTxs', $this->numberOfTransactions);
		$PmtInf->addChild('CtrlSum', $this->intToCurrency($this->controlSumCents));
		$PmtInf->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', 'SEPA');
		if ($this->localInstrumentCode)
			$PmtInf->PmtTpInf->addChild('LclInstr')->addChild('Cd', $this->localInstrumentCode);
		
		$PmtInf->addChild('ReqdExctnDt', $requestedExecutionDate);
		$PmtInf->addChild('Dbtr')->addChild('Nm', $this->debtorName);

		$DbtrAcct = $PmtInf->addChild('DbtrAcct');
		$DbtrAcct->addChild('Id')->addChild('IBAN', $this->debtorAccountIBAN);
		$DbtrAcct->addChild('Ccy', $this->debtorAccountCurrency);

		$PmtInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->debtorAgentBIC);
		$PmtInf->addChild('ChrgBr', 'SLEV');

		// -- Credit Transfer Transaction Information --\\

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
		return $xml;
	}

}

/**
 * SEPA file "Credit Transfer Transaction Information" block.
 */
class SepaCreditTransfer extends SepaFileSection
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
			$amount = (integer) ($amount * 100);

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

