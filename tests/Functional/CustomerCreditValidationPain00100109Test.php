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

namespace Digitick\Sepa\Tests\Functional;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use PHPUnit\Framework\TestCase;

/**
 * Various schema validation tests.
 */
class CustomerCreditValidationPain00100109Test extends TestCase
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp(): void
    {
        $this->schema = __DIR__ . "/../fixtures/pain.001.001.09.xsd";
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     */
    public function testSanity(): void
    {
        $this->dom->load(__DIR__ . '/../fixtures/pain.001.001.09.xml');
        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and one transaction.
     *
     * @dataProvider scenarios
     */
    public function testSinglePaymentSingleTransWithMoreInfo(array $scenario): void
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $groupHeader->setInitiatingPartyId('XXXXXXXXXX');
        $groupHeader->setIssuer('Issuing Party');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(2, 'FI1350001540000056', 'Their Corp');
        if ($scenario['bic'] !== '') {
            $transfer->setBic($scenario['bic']);
        }
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());
        $transfer->setInstructionId(uniqid());
        $addressDetailValues = [];
        foreach ($scenario['postalAddress'] as $k => $s) {
            if ($s == 'setCountry') {
                $addressDetailValues[$k] = 'DE';
            } else {
                $addressDetailValues[$k] = substr($s, 0, 5) . "abc1";
            }
            $transfer->{$s}($addressDetailValues[$k]);
        }
        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        if (isset($scenario['batchBooking'])) {
            $payment->setBatchBooking($scenario['batchBooking']);
        }
        $payment->setDueDate(new \DateTime('20.11.2012'));
        $payment->setValidPaymentMethods(array('TRANSFER'));
        $payment->setPaymentMethod('TRANSFER');
        $payment->setCategoryPurposeCode('SALA');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder('pain.001.001.09');
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);
        $xpathDoc = new \DOMXPath($this->dom);
        $xpathDoc->registerNamespace('sepa', 'urn:iso:std:iso:20022:tech:xsd:' . 'pain.001.001.09');
        $postalTest = $xpathDoc->query('//sepa:Dt');
        $this->assertEquals('2012-11-20', $postalTest->item(0)->textContent);
        foreach ($addressDetailValues as $k => $s) {
            $postalTest = $xpathDoc->query('//sepa:' . $k);
            $this->assertEquals($s, $postalTest->item(0)->textContent);
        }
        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);
    }

    public function scenarios(): iterable
    {
        $postalCodes = ['Dept', 'SubDept', 'StrtNm', 'BldgNb', 'BldgNm', 'Flr', 'PstBx', 'Room', 'PstCd', 'TwnNm', 'TwnLctnNm', 'DstrctNm', 'CtrySubDvsn', 'Ctry'];
        $postalAddress = ['setDepartment', 'setSubDepartment', 'setStreetName', 'SetBuildingNumber', 'setBuildingName', 'setFloor', 'setPostBox', 'setRoom', 'setPostCode', 'setTownName', 'setTownLocationName', 'setDistrictName', 'setCountrySubDivision', 'setCountry'];
        return array(
            array(
                array(
                    'batchBooking' => true,
                    'bic' => 'OKOYFIHH',
                    'postalAddress' => array_combine($postalCodes, $postalAddress)
                )
            ),
            array(
                array(
                    'batchBooking' => false,
                    'bic' => '',
                    'postalAddress' => array_combine($postalCodes, $postalAddress)
                )
            ),
        );
    }
}
