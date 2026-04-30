<?php

namespace Digitick\Sepa\Tests\Unit;

use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use Digitick\Sepa\Util\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Locks down the contract that specific setters silently route input through
 * the default Sanitizer. A regression removing Sanitizer::sanitize() from
 * any of these setters would ship non-transliterated / disallowed characters
 * to banks that reject them.
 *
 * Setters not listed here either do not sanitize by design (e.g. setBic,
 * setOriginAccountIBAN, setCountry, setPurposeCode — controlled identifiers)
 * or are already covered in a closer test (GroupHeader setters live in
 * GroupHeaderTest).
 */
class SanitizerOnSetterTest extends TestCase
{
    protected function setUp(): void
    {
        // Defensive: ensure no prior test has installed a custom / disabled
        // sanitizer that would leak through to the assertions below.
        Sanitizer::resetSanitizer();
    }

    protected function tearDown(): void
    {
        Sanitizer::resetSanitizer();
    }

    /**
     * @dataProvider baseTransferInformationSetters
     */
    public function testBaseTransferInformationSetterSanitizes(
        string $setter,
        string $getter,
        string $input,
        string $expected
    ): void {
        $obj = new CustomerCreditTransferInformation(100, 'DE12500105170648489890', 'Placeholder');
        $obj->$setter($input);

        $this->assertSame($expected, $obj->$getter());
    }

    public static function baseTransferInformationSetters(): iterable
    {
        return [
            'setName'                    => ['setName', 'getName', 'Jörg', 'Joerg'],
            'setEndToEndIdentification'  => ['setEndToEndIdentification', 'getEndToEndIdentification', 'Müller-E2E', 'Mueller-E2E'],
            'setCreditorReference'       => ['setCreditorReference', 'getCreditorReference', 'RFä42', 'RFae42'],
            'setCreditorReferenceType'   => ['setCreditorReferenceType', 'getCreditorReferenceType', 'ISO&11649', 'ISO 11649'],
            'setRemittanceInformation'   => ['setRemittanceInformation', 'getRemittanceInformation', 'Rechnung Nr. ä42', 'Rechnung Nr. ae42'],
            'setTownName'                => ['setTownName', 'getTownName', 'München', 'Muenchen'],
            'setPostCode'                => ['setPostCode', 'getPostCode', '12345ä', '12345ae'],
            'setStreetName'              => ['setStreetName', 'getStreetName', 'Hauptstraße 1', 'Hauptstrasse 1'],
            'setBuildingNumber'          => ['setBuildingNumber', 'getBuildingNumber', '14ä', '14ae'],
            'setFloorNumber'             => ['setFloorNumber', 'getFloorNumber', '3ö', '3oe'],
            'combined transliterate+strip' => ['setName', 'getName', 'Jörg & Händel', 'Joerg   Haendel'],
        ];
    }

    /**
     * @dataProvider customerDirectDebitTransferInformationSetters
     */
    public function testCustomerDirectDebitTransferInformationSetterSanitizes(
        string $setter,
        string $getter,
        string $input,
        string $expected
    ): void {
        $obj = new CustomerDirectDebitTransferInformation(100, 'DE12500105170648489890', 'Placeholder');
        $obj->$setter($input);

        $this->assertSame($expected, $obj->$getter());
    }

    public static function customerDirectDebitTransferInformationSetters(): iterable
    {
        return [
            'setMandateId'         => ['setMandateId', 'getMandateId', 'MANDä-1', 'MANDae-1'],
            'setOriginalMandateId' => ['setOriginalMandateId', 'getOriginalMandateId', 'OLDMä-42', 'OLDMae-42'],
        ];
    }

    /**
     * @dataProvider paymentInformationSetters
     */
    public function testPaymentInformationSetterSanitizes(
        string $setter,
        string $getter,
        string $input,
        string $expected
    ): void {
        $obj = new PaymentInformation('id', 'DE12500105170648489890', 'BIC', 'Placeholder');
        $obj->$setter($input);

        $this->assertSame($expected, $obj->$getter());
    }

    public static function paymentInformationSetters(): iterable
    {
        return [
            'setCreditorId'                          => ['setCreditorId', 'getCreditorId', 'DE67ZZZä00000123456', 'DE67ZZZae00000123456'],
            'setOriginName'                          => ['setOriginName', 'getOriginName', 'Jörg GmbH', 'Joerg GmbH'],
            'setOriginBankPartyIdentification'       => ['setOriginBankPartyIdentification', 'getOriginBankPartyIdentification', 'BNKä-1', 'BNKae-1'],
            'setOriginBankPartyIdentificationScheme' => ['setOriginBankPartyIdentificationScheme', 'getOriginBankPartyIdentificationScheme', 'BNK&', 'BNK '],
        ];
    }

    public function testConstructorArgumentNameIsSanitized(): void
    {
        $obj = new CustomerCreditTransferInformation(100, 'DE12500105170648489890', 'Jörg');

        $this->assertSame('Joerg', $obj->getName());
    }

    public function testConstructorArgumentIdentificationIsSanitized(): void
    {
        $obj = new CustomerCreditTransferInformation(100, 'DE12500105170648489890', 'Placeholder', 'Jörg');

        $this->assertSame('Joerg', $obj->getEndToEndIdentification());
    }

    public function testPaymentInformationConstructorSanitizesOriginName(): void
    {
        $obj = new PaymentInformation('id', 'DE12500105170648489890', 'BIC', 'Jörg GmbH');

        $this->assertSame('Joerg GmbH', $obj->getOriginName());
    }

    public function testAddressSettersTreatEmptyStringAsNull(): void
    {
        $obj = new CustomerCreditTransferInformation(100, 'DE12500105170648489890', 'Placeholder');

        // These setters short-circuit the Sanitizer call when the value is
        // empty (per BaseTransferInformation::setTownName et al. — the
        // "!empty ? sanitize : null" branch).
        $obj->setTownName('');
        $obj->setPostCode('');
        $obj->setStreetName('');
        $obj->setBuildingNumber('');
        $obj->setFloorNumber('');

        $this->assertNull($obj->getTownName());
        $this->assertNull($obj->getPostCode());
        $this->assertNull($obj->getStreetName());
        $this->assertNull($obj->getBuildingNumber());
        $this->assertNull($obj->getFloorNumber());
    }
}
