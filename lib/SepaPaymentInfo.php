<?php

/**
 * SEPA file generator.
 *
 * @copyright © Digitick <www.digitick.net> 2012-2013
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

/**
 * SEPA file "Payment Information" block.
 */
class SepaPaymentInfo extends SepaFileBlock
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
	protected $debtorAccountCurrency = 'EUR';
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
	 * @var SepaTransferFile
	 */
	protected $transferFile;
	
	/**
	 * Constructor.
	 * @param SepaTransferFile $transferFile
	 */
	public function __construct(SepaTransferFile $transferFile)
	{
		$this->setTransferFile($transferFile);
	}

	/**
	 * Set the information for this "Payment Information" block.
	 * @param array $paymentInfo
	 */
	public function setInfo(array $paymentInfo)
	{
		$values = array(
			'id', 'categoryPurposeCode', 'debtorName', 'debtorAccountIBAN',
			'debtorAgentBIC', 'debtorAccountCurrency'
		);
		foreach ($values as $name) {
			if (isset($paymentInfo[$name]))
				$this->$name = $paymentInfo[$name];
		}
		if (isset($paymentInfo['localInstrumentCode']))
			$this->setLocalInstrumentCode($paymentInfo['localInstrumentCode']);
		
		if (isset($paymentInfo['paymentMethod']))
			$this->setPaymentMethod($paymentInfo['paymentMethod']);
		
		if (isset($paymentInfo['debtorAccountCurrency']))
			$this->setDebtorAccountCurrency($paymentInfo['debtorAccountCurrency']);
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
		if (!in_array($code, array('CORE', 'B2B','COR1'))) {
			throw new Exception("Invalid Local Instrument Code: $code");
		}
		$this->localInstrumentCode = $code;
	}
	
	/**
	 * Set the debtor's account currency code.
	 * @param string $code currency ISO code
	 * @throws Exception
	 */
	public function setDebtorAccountCurrency($code)
	{
		$this->debtorAccountCurrency = $this->validateCurrency($code);
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
	 * Set the transfer file.
	 * @param SepaTransferFile $transferFile
	 */
	public function setTransferFile(SepaTransferFile $transferFile)
	{
		$this->transferFile = $transferFile;
	}

	/**
	 * Add a credit transfer transaction.
	 * @param array $transferInfo
	 */
	public function addCreditTransfer(array $transferInfo)
	{
		$transfer = new SepaCreditTransfer();
		$values = array(
			'id', 'creditorBIC', 'creditorName',
			'creditorAccountIBAN', 'remittanceInformation'
		);
		foreach ($values as $name) {
			if (isset($transferInfo[$name]))
				$transfer->$name = $transferInfo[$name];
		}
		if (isset($transferInfo['amount']))
			$transfer->setAmount($transferInfo['amount']);
		
		if (isset($transferInfo['currency']))
			$transfer->setCurrency($transferInfo['currency']);
		
		$transfer->endToEndId = $this->transferFile->messageIdentification . '/' . $this->getNumberOfTransactions();

		$this->creditTransfers[] = $transfer;
		$this->numberOfTransactions++;
		$this->controlSumCents += $transfer->getAmountCents();
	}

	/**
	 * DO NOT CALL THIS FUNCTION DIRECTLY!
	 *
	 * Generate the XML structure for this "Payment Info" block.
	 * 
	 * @param SimpleXMLElement $xml
	 * @return SimpleXMLElement
	 */
	public function generateXml(SimpleXMLElement $xml)
	{
		$datetime = new DateTime();
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
			$PmtInf->PmtTpInf->addChild('LclInstrm')->addChild('Cd', $this->localInstrumentCode);
		
		$PmtInf->addChild('ReqdExctnDt', $requestedExecutionDate);
		$PmtInf->addChild('Dbtr')->addChild('Nm', htmlentities($this->debtorName));

		$DbtrAcct = $PmtInf->addChild('DbtrAcct');
		$DbtrAcct->addChild('Id')->addChild('IBAN', $this->debtorAccountIBAN);
		$DbtrAcct->addChild('Ccy', $this->debtorAccountCurrency);

		$PmtInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->debtorAgentBIC);
		$PmtInf->addChild('ChrgBr', 'SLEV');

		// -- Credit Transfer Transaction Information --\\

		foreach ($this->creditTransfers as $transfer) {
			$PmtInf = $transfer->generateXml($PmtInf);
		}
		return $xml;
	}

}
