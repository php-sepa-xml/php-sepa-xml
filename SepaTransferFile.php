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
 * @author Vincent MOMIN
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

	const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"></Document>';

	public function addCreditor(array $creditor)
	{
		$creditor['CreditorPaymentId']	= $this->messageIdentification.'/'.$this->numberOfTransactions;
		$this->creditorList[] = $creditor;
		$this->numberOfTransactions++;
	}

	public function generateXml()
	{
		$datetime = new DateTime();
		$creationDateTime = $datetime->format('Y-m-d\TH:i:s');
		$requestedExecutionDate = $datetime->format('Y-m-d');
		
		$xml = simplexml_load_string(self::INITIAL_STRING);

		$GrpHdr = $xml->addChild('CstmrCdtTrfInitn')->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageIdentification);
		$GrpHdr->addChild('CreDtTm', $creationDateTime);
		$GrpHdr->addChild('NbOfTxs', $this->numberOfTransactions);
		$GrpHdr->addChild('CtrlSum', $this->headerControlSum);
		$GrpHdr->addChild('InitgPty')->addChild('Nm', $this->initiatingPartyName);

		$PmtInf = $xml->CstmrCdtTrfInitn->addChild('PmtInf');
		$PmtInf->addChild('PmtInfId', $this->paymentInfoId);
		$PmtInf->addChild('PmtMtd', $this->paymentMethod);
		$PmtInf->addChild('NbOfTxs', $this->numberOfTransactions);
		$PmtInf->addChild('CtrlSum', $this->paymentControlSum);
		$PmtInf->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', $this->paymentTypeInfoCode);
		$PmtInf->addChild('ReqdExctnDt', $requestedExecutionDate);
		$PmtInf->addChild('Dbtr')->addChild('Nm', $this->debtorName);

		$DbtrAcct = $PmtInf->addChild('DbtrAcct');
		$DbtrAcct->addChild('Id')->addChild('IBAN', $this->debtorAccountIBAN);
		$DbtrAcct->addChild('Ccy', $this->debtorAccountCurrency);

		$PmtInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->debtorAgentBIC);
		$PmtInf->addChild('ChrgBr', $this->chargeBearer);

		foreach($this->creditorList as $creditor) {
			$CdtTrfTxInf = $PmtInf->addChild('CdtTrfTxInf');
			$PmtId = $CdtTrfTxInf->addChild('PmtId');
			$PmtId->addChild('InstrId', $creditor['CreditorPaymentId']);
			$PmtId->addChild('EndToEndId', $creditor['CreditorPaymentEndToEndId']);
			$CdtTrfTxInf->addChild('Amt')->addChild('InstdAmt', $creditor['CreditorPaymentAmount'])->addAttribute('Ccy', $creditor['CreditorPaymentCurrency']);
			$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $creditor['CreditorBIC']);
			$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', $creditor['CreditorName']);
			$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $creditor['CreditorAccountIBAN']);
			$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $creditor['RemittanceInformation']);
		}

		return $xml->asXML();
	}
        
        public function outputXML()
        {
            header('Content-type: text/xml');
            echo $this->generateXml();
        }

}
