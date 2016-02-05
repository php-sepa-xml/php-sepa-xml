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

namespace tests\Unit;

use PhpSepaXml\DomBuilder\DomBuilderFactory;
use PhpSepaXml\GroupHeader;
use PhpSepaXml\PaymentInformation;
use PhpSepaXml\TransferFile\CustomerCreditTransferFile;
use PhpSepaXml\TransferFile\CustomerDirectDebitTransferFile;
use PhpSepaXml\TransferInformation\CustomerCreditTransferInformation;
use PhpSepaXml\TransferInformation\CustomerDirectDebitTransferInformation;

class DomBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createReturnsCustomerCreditDomBuilderForCustomerCreditTransfer()
    {
        $groupHeader = new GroupHeader('123456788', 'Initiating Company');
        $paymentInformation = new PaymentInformation('12345', 'DE2112345678910111213141516', 'NOLANDEKI', 'Origin Company');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $transfer = new CustomerCreditTransferInformation(20, 'DE21098765432010203040506', 'Creditor Name');
        $paymentInformation->addTransfer($transfer);
        $sepaFile->addPaymentInformation($paymentInformation);

        $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile);
        $this->assertInstanceOf('\PhpSepaXml\DomBuilder\CustomerCreditTransferDomBuilder', $domBuilder);
    }

    /**
     * @test
     */
    public function createReturnsCustomerDebitDomBuilderForCustomerDebitTransfer()
    {
        $groupHeader = new GroupHeader('123456788', 'Initiating Company');
        $paymentInformation = new PaymentInformation('12345', 'DE2112345678910111213141516', 'NOLANDEKI', 'Origin Company');
        $paymentInformation->setSequenceType(PaymentInformation::S_ONEOFF);
        $paymentInformation->setCreditorId('NOLANDEKI');
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);
        $transfer = new CustomerDirectDebitTransferInformation(20, 'DE21098765432010203040506', 'Creditor Name');
        $transfer->setMandateId('MandateRef');
        $transfer->setMandateSignDate(new \DateTime());
        $paymentInformation->addTransfer($transfer);
        $sepaFile->addPaymentInformation($paymentInformation);

        $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile);
        $this->assertInstanceOf('\PhpSepaXml\DomBuilder\CustomerDirectDebitTransferDomBuilder', $domBuilder);
    }
}
