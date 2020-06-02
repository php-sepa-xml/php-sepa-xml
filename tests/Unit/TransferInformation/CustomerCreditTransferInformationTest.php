<?php

namespace Digitick\Sepa\Tests\Unit\TransferInformation;

use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use PHPUnit\Framework\TestCase;

class CustomerCreditTransferInformationTest extends TestCase
{
    /**
     * Tests whether the EndToEndId equals the name if no other identifier was supplied
     */
    public function testEndToEndIndentifierEqualsName()
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp');
        $this->assertEquals('Their Corp', $information->getEndToEndIdentification());
    }

    /**
     * Tests whether the EndToEndId equals the supplied EndToEndId
     */
    public function testOptionalEndToEndIdentifier()
    {
        $information = new CustomerCreditTransferInformation('100', 'DE12500105170648489890', 'Their Corp', 'MyEndToEndId');
        $this->assertEquals('MyEndToEndId', $information->getEndToEndIdentification());
    }
}
