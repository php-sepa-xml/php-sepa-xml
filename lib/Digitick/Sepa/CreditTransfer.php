<?php

namespace Digitick\Sepa;

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
 * SEPA file "Credit Transfer Transaction Information" block.
 */
class CreditTransfer extends FileBlock
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
	 * @var string ISO currency code
	 */
	protected $currency;
	/**
	 * @var integer Transfer amount in cents.
	 */
	protected $amountCents = 0;

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
	
	/**
	 * Set the debtor's account currency code.
	 * @param string $code currency ISO code
	 * @throws Exception
	 */
	public function setCurrency($code)
	{
		$this->currency = $this->validateCurrency($code);
	}
	
	/**
	 * DO NOT CALL THIS FUNCTION DIRECTLY!
	 * 
	 * @param \SimpleXMLElement $xml
	 * @return \SimpleXMLElement
	 */
	public function generateXml(\SimpleXMLElement $xml)
	{
		// -- Credit Transfer Transaction Information --\\
		
		$amount = $this->intToCurrency($this->getAmountCents());

		$CdtTrfTxInf = $xml->addChild('CdtTrfTxInf');
		$PmtId = $CdtTrfTxInf->addChild('PmtId');
		$PmtId->addChild('InstrId', $this->id);
		$PmtId->addChild('EndToEndId', $this->endToEndId);
		$CdtTrfTxInf->addChild('Amt')->addChild('InstdAmt', $amount)->addAttribute('Ccy', $this->currency);
		$CdtTrfTxInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->creditorBIC);
		$CdtTrfTxInf->addChild('Cdtr')->addChild('Nm', htmlentities($this->creditorName));
		$CdtTrfTxInf->addChild('CdtrAcct')->addChild('Id')->addChild('IBAN', $this->creditorAccountIBAN);
		$CdtTrfTxInf->addChild('RmtInf')->addChild('Ustrd', $this->remittanceInformation);
		
		return $xml;
	}
}
