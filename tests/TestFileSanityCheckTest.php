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

namespace Digitick\Sepa\Tests;

use DateTime;
use Digitick\Sepa\DomBuilder\DomBuilderFactory;
use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;

class TestFileSanityCheckTest extends TestCase
{
    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp(): void
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Generates SDD files and tests them against their schema version
     * @dataProvider ddProvider
     * @param string $painFormat
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGenerateDirectDebitFiles(string $painFormat): void
    {
        $companyName = 'OurCorp';
        $originAccIban = 'BG87200500001234567890';
        $originAccBic = 'BANKBGFFXXX';
        $messageIdentification = 'OurMessageIdentification';
        $paymentInfoId = 'OurPaymentInfo';
        $creditorId = 'BG40ZZZ507778';

        $clientName = 'John Doe';
        $clientAccIban = 'DE87200500001234567890';
        $clientAccBic = 'BANKDEFFXXX';
        $endToEndId = 'SomethingUnique';
        $clientLabel = 'OurCorp Billing';
        $mandateId = 'SomeOtherUniqueThing';

        $groupHeader = new GroupHeader($messageIdentification, $companyName);
        $sepaFile = new CustomerDirectDebitTransferFile($groupHeader);

        $paymentInfo = new PaymentInformation(
            $paymentInfoId,
            $originAccIban,
            $originAccBic,
            $companyName
        );
        $paymentInfo->setCreditorId($creditorId);
        $paymentInfo->setSequenceType(PaymentInformation::S_ONEOFF);
        $paymentInfo->setOriginName($companyName);

        $transfer = new CustomerDirectDebitTransferInformation(1234, $clientAccIban, $clientName, $endToEndId);
        $transfer->setBic($clientAccBic);
        $transfer->setRemittanceInformation($clientLabel);
        $transfer->setMandateId($mandateId);
        $transfer->setMandateSignDate(new DateTime());
        $transfer->setFinalCollectionDate(new DateTime());
        $transfer->setPurposeCode('SALA');
        $transfer->setCountry('BG');
        $transfer->setPostCode('1000');
        $transfer->setTownName('Nowhere');
        $transfer->setStreetName('Some Street');
        $transfer->setBuildingNumber(12);
        $transfer->setFloorNumber(12);

        $paymentInfo->addTransfer($transfer);
        $sepaFile->addPaymentInformation($paymentInfo);

        /* Validate the generated XML against it's XSD: */
        $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, $painFormat);
        $this->dom->loadXML($domBuilder->asXml());
        $this->assertValidSchema($this->dom, $painFormat);
    }

    /**
     * Generates SDD files and tests them against their schema version
     * @dataProvider ctProvider
     * @param string $painFormat
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGenerateCreditTransferFiles(string $painFormat): void
    {
        $companyName = 'OurCorp';
        $originAccIban = 'BG87200500001234567890';
        $originAccBic = 'BANKBGFFXXX';
        $messageIdentification = 'OurMessageIdentification';
        $paymentInfoId = 'OurPaymentInfo';
        $creditorId = 'BG40ZZZ507778';

        $clientName = 'John Doe';
        $clientAccIban = 'DE87200500001234567890';
        $clientAccBic = 'BANKDEFFXXX';
        $endToEndId = 'SomethingUnique';

        $groupHeader = new GroupHeader($messageIdentification, $companyName);

        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        $paymentInfo = new PaymentInformation(
            $paymentInfoId,
            $originAccIban,
            $originAccBic,
            $companyName
        );
        $paymentInfo->setCreditorId($creditorId);
        $paymentInfo->setSequenceType(PaymentInformation::S_ONEOFF);
        $paymentInfo->setOriginName($companyName);

        $transfer = new CustomerCreditTransferInformation(1234, $clientAccIban, $clientName, $endToEndId);
        $transfer->setBic($clientAccBic);
        $transfer->setCreditorReference('CdtrRefInf-Ref');
        $transfer->setCreditorReferenceType('CdtrRefInf-Tp-Issr');

        $transfer->setCountry('BG');
        $transfer->setPostCode('1000');
        $transfer->setTownName('Nowhere');
        $transfer->setStreetName('Some Street');
        $transfer->setBuildingNumber(12);
        $transfer->setFloorNumber(12);

        $paymentInfo->addTransfer($transfer);
        $sepaFile->addPaymentInformation($paymentInfo);

        /* Validate the generated XML against it's XSD: */
        $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, $painFormat);
        $this->dom->loadXML($domBuilder->asXml());
        $this->assertValidSchema($this->dom, $painFormat);
    }

    /**
     * Sanity check: Validate reference files against XSDs.
     *
     * @dataProvider schemaVersionProvider
     */
    public function testSanityFiles(string $pain): void
    {
        $this->dom->load(XML_DIR . $pain . '.xml');
        $this->assertValidSchema($this->dom, $pain);
    }

    public static function schemaVersionProvider(): iterable
    {
        return array_merge(
            self::ctProvider(),
            self::ddProvider()
        );
    }

    public static function ddProvider(): array
    {
        return [
            'pain.008.001.02' => ['pain.008.001.02'],
            'pain.008.001.03' => ['pain.008.001.03'],
            'pain.008.001.04' => ['pain.008.001.04'],
            'pain.008.001.05' => ['pain.008.001.05'],
            'pain.008.001.06' => ['pain.008.001.06'],
            'pain.008.001.07' => ['pain.008.001.07'],
            'pain.008.001.08' => ['pain.008.001.08'],
            'pain.008.001.09' => ['pain.008.001.09'],
            'pain.008.001.10' => ['pain.008.001.10'],
            'pain.008.001.11' => ['pain.008.001.11'],
            //SEPA Variants:
            'pain.008.002.02' => ['pain.008.002.02'],
            'pain.008.003.02' => ['pain.008.003.02'],
        ];
    }

    public static function ctProvider(): array
    {
        return [
            'pain.001.001.03' => ['pain.001.001.03'],
            'pain.001.001.04' => ['pain.001.001.04'],
            'pain.001.001.05' => ['pain.001.001.05'],
            'pain.001.001.06' => ['pain.001.001.06'],
            'pain.001.001.07' => ['pain.001.001.07'],
            'pain.001.001.08' => ['pain.001.001.08'],
            'pain.001.001.09' => ['pain.001.001.09'],
            'pain.001.001.10' => ['pain.001.001.10'],
            'pain.001.001.12' => ['pain.001.001.12'],
            //SEPA Variants:
            'pain.001.002.03' => ['pain.001.002.03'],
            'pain.001.003.03' => ['pain.001.003.03'],
        ];
    }
}
