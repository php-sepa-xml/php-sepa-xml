<?php

namespace Digitick\Sepa;

/**
 * SEPA file generator.
 *
 * ALPHA QUALITY SOFTWARE
 * Do NOT use in production environments!!!
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
 *
 * @author Jérémy Cambon
 * @author Ianaré Sévi
 * @author Vincent MOMIN
 * @author Sören Rohweder
 */

/**
 * SEPA payments file object.
 */
class CustomerCredit extends FileBlock implements TransferFile
{
    /**
     * @var GroupHeader
     */
    protected $groupHeader;

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
	 * @var \SimpleXMLElement
	 */
	protected $xml;

	/**
	 * @var array<\Digitick\Sepa\PaymentInfo>
	 */
	protected $payments;

	const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"></Document>';

	public function __construct(GroupHeader $groupHeader) {
        $this->groupHeader = $groupHeader;
		$this->xml = simplexml_load_string(self::INITIAL_STRING);
		$this->xml->addChild('CstmrCdtTrfInitn');
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
		return $this->controlSumCents;
	}

	/**
	 * Set the information for the "Payment Information" block.
	 * @param array $paymentInfo
	 * @return \Digitick\Sepa\PaymentInfo
	 */
	public function addPaymentInfo(array $paymentInfo)
	{
		$payment = new PaymentInfo($this);
		$payment->setInfo($paymentInfo);
		
		$this->payments[] = $payment;
		
		return $payment;
	}

	/**
	 * Update counters related to "Payment Information" blocks.
	 */
	protected function updatePaymentCounters()
	{
		$this->numberOfTransactions = 0;
		$this->controlSumCents = 0;
		
		foreach ($this->payments as $payment) {
			$this->numberOfTransactions += $payment->getNumberOfTransactions();
			$this->controlSumCents += $payment->getControlSumCents();
		}

        $this->groupHeader->setControlSumCents($this->controlSumCents);
        $this->groupHeader->setNumberOfTransactions($this->numberOfTransactions);
	}

	/**
	 * Generate the XML structure.
	 */
	protected function generateXml()
	{
		$this->updatePaymentCounters();

		// -- Group Header -- \\
        $this->groupHeader->generateXml($this->xml->CstmrCdtTrfInitn);

		// -- Payment Information --\\
		foreach ($this->payments as $payment) {
			$this->xml = $payment->generateXml($this->xml);
		}
	}

    /**
     * @return \Digitick\Sepa\GroupHeader
     */
    public function getGroupHeader() {
        return $this->groupHeader;
    }


}

