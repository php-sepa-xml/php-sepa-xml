<?php

/**
 * Generate a SEPA transfer file.
 * 
 * ALPHA QUALITY SOFTWARE
 * Do NOT use in production environments!!!
 * 
 * @copyright © Digitick <www.digitick.net> 2012
 * @license GNU Lesser General Public License v3.0
 * @author Jérémy Cambon
 * @author Ianaré Sévi
 */

/**
 * SEPA file object.
 * 
 * TODO: inherit from PHP XML object?
 */
class SepaTransferFile
{
	public $messageIdentification;
	public $debtorName;
	public $debtorAccountIBAN;
	public $debtorAgentBIC;
	public $initiatingPartyName;
	public $paymentInfoId;
	
	public $headerControlSum = 0;
	public $paymentControlSum = 0;
	
	public $paymentMethod = 'TRF';
	public $paymentTypeInfoCode = 'SEPA';
	public $chargeBearer = 'SLEV';
	public $debtorAccountCurrency = 'EUR';
	
	protected $creditorList = array();
	protected $numberOfTransactions = 0;


	public function addCreditor(array $creditor)
	{
		$creditor['CreditorPaymentId']	= $this->messageIdentification.'/'.$this->numberOfTransactions;
		$this->creditorList[] = $creditor;
		$this->numberOfTransactions++;
	}

	public function generateXml(array $data)
	{
		$datetime = new DateTime();		
		$params = array(
			'CreationDateTime' => $datetime->format('Y-m-d\TH:i:s'),
			'HeaderControlSum' => $this->headerControlSum,
			'InitiatingPartyName' => $this->initiatingPartyName,
			'PaymentInfoId' => $this->paymentInfoId,
			'PaymentMethod' => $this->paymentMethod,
			'PaymentControlSum' => $this->paymentControlSum,
			'PaymentTypeInfoCode' => $this->paymentTypeInfoCode,
			'RequestedExecutionDate' => $datetime->format('Y-m-d'),
			'DebtorName' => $this->debtorName,
			'DebtorAccountIBAN' => $this->debtorAccountIBAN,
			'DebtorAccountCurrency' => $this->debtorAccountCurrency,
			'DebtorAgentBIC' => $this->debtorAgentBIC,
			'ChargeBearer' => $this->chargeBearer,
			'CreditorList' => $this->creditorList,
		);

		foreach ($params as $f => $v) {
			if (isset($data[$f]))
				$params[$f] = $data[$f];
		}
		
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"></Document>');

		$GrpHdr = $xml->addChild('CstmrCdtTrfInitn')->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageIdentification);
		$GrpHdr->addChild('CreDtTm', $params['CreationDateTime']);
		$GrpHdr->addChild('NbOfTxs', $this->numberOfTransactions);
		$GrpHdr->addChild('CtrlSum', $params['HeaderControlSum']);
		$GrpHdr->addChild('InitgPty')->addChild('Nm', $params['InitiatingPartyName']);
		
		$PmtInf = $xml->CstmrCdtTrfInitn->addChild('PmtInf');
		$PmtInf->addChild('PmtInfId', $params['PaymentInfoId']);
		$PmtInf->addChild('PmtMtd', $params['PaymentMethod']);
		$PmtInf->addChild('NbOfTxs', $this->numberOfTransactions);
		$PmtInf->addChild('CtrlSum', $params['PaymentControlSum']);
		$PmtInf->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', $params['PaymentTypeInfoCode']);
		$PmtInf->addChild('ReqdExctnDt', $params['RequestedExecutionDate']);
		$PmtInf->addChild('Dbtr')->addChild('Nm', $params['DebtorName']);
		
		$DbtrAcct = $PmtInf->addChild('DbtrAcct');
		$DbtrAcct->addChild('Id')->addChild('IBAN', $params['DebtorAccountIBAN']);
		$DbtrAcct->addChild('Ccy', $params['DebtorAccountCurrency']);
	
		$PmtInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $params['DebtorAgentBIC']);
		$PmtInf->addChild('ChrgBr', $params['ChargeBearer']);
		
		foreach($params['CreditorList'] as $rData) {
			$CdtTrfTxInf = $PmtInf->addChild('CdtTrfTxInf');
			$PmtId = $CdtTrfTxInf->addChild('PmtId');
			$PmtId->addChild('InstrId', $rData['CreditorPaymentId']);
			$PmtId->addChild('EndToEndId', $rData['CreditorPaymentEndToEndId']);
			$CdtTrfTxInf->addChild('Amt')->addChild('InstdAmt', $rData['CreditorPaymentAmount'])->addAttribute('Ccy', $rData['CreditorPaymentCurrency']);
			$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $rData['CreditorBIC']);
			$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', $rData['CreditorName']);
			$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $rData['CreditorAccountIBAN']);
			$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $rData['RemittanceInformation']);
		}

		return $xml->asXML();
	}

}
