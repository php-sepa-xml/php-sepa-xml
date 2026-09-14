<?php

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use DateTime;
use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\DomBuilder\CustomerDirectDebitTransferDomBuilder;
use Digitick\Sepa\Exception\InvalidArgumentException;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferFile\CustomerDirectDebitTransferFile;
use Digitick\Sepa\TransferFile\Facade\CustomerCreditFacade;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\TransferInformation\CustomerDirectDebitTransferInformation;
use Digitick\Sepa\Util\MessageFormat;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Covers BaseDomBuilder::validateSchema() / getSchemaValidationErrors(), the
 * facade passthroughs, and the XSDs bundled under doc/ISO20022 that they use
 * by default.
 */
class SchemaValidationTest extends TestCase
{
    public static function supportedFormats(): iterable
    {
        foreach ((new MessageFormat('pain.001.001.09'))->getSupportedMessageFormats() as $painFormat) {
            yield $painFormat => [$painFormat];
        }
    }

    /**
     * @dataProvider supportedFormats
     */
    public function testEverySupportedFormatHasABundledSchema(string $painFormat): void
    {
        $path = (new MessageFormat($painFormat))->getBundledSchemaPath();

        $this->assertNotNull($path);
        $this->assertFileEquals(XSD_DIR . $painFormat . '.xsd', $path);
    }

    public function testFormatOutsideTheSupportedListHasNoBundledSchema(): void
    {
        $this->assertNull((new MessageFormat('pain.001.001.99'))->getBundledSchemaPath());
    }

    public function testBundledSchemaLookupIgnoresCase(): void
    {
        $this->assertSame(
            (new MessageFormat('pain.001.001.09'))->getBundledSchemaPath(),
            (new MessageFormat('PAIN.001.001.09'))->getBundledSchemaPath()
        );
    }

    public function testBundledSchemasAreShippedInThePackage(): void
    {
        $gitattributes = (string) file_get_contents(dirname(__DIR__, 3) . '/.gitattributes');

        $this->assertDoesNotMatchRegularExpression('#^/doc(/ISO20022)?/?\s+export-ignore#m', $gitattributes);
    }

    public static function creditTransferFormats(): iterable
    {
        foreach (['03', '04', '05', '06', '07', '08', '09', '10', '12'] as $version) {
            yield 'pain.001.001.' . $version => ['pain.001.001.' . $version];
        }
    }

    /**
     * @dataProvider creditTransferFormats
     */
    public function testValidCreditTransferPassesBundledSchema(string $painFormat): void
    {
        $builder = $this->buildCreditTransfer(new CustomerCreditTransferDomBuilder($painFormat));

        $this->assertSame([], $builder->getSchemaValidationErrors());
        $this->assertTrue($builder->validateSchema());
    }

    public static function directDebitFormats(): iterable
    {
        foreach (['02', '03', '04', '05', '06', '07', '08', '09', '10', '11'] as $version) {
            yield 'pain.008.001.' . $version => ['pain.008.001.' . $version];
        }
    }

    /**
     * @dataProvider directDebitFormats
     */
    public function testValidDirectDebitPassesBundledSchema(string $painFormat): void
    {
        $builder = $this->buildDirectDebit($painFormat);

        $this->assertSame([], $builder->getSchemaValidationErrors());
        $this->assertTrue($builder->validateSchema());
    }

    public function testOmittedMandatoryAgentIsReported(): void
    {
        // The DK profile flag drops <DbtrAgt>, which the base ISO XSD requires.
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.03');
        $builder->setOmitAgentElementIfBicMissing(true);
        $this->buildCreditTransfer($builder, null);

        $errors = $builder->getSchemaValidationErrors();

        $this->assertFalse($builder->validateSchema());
        $this->assertNotEmpty($errors);
        $this->assertMatchesRegularExpression('/^Line \d+: /', $errors[0]);
        $this->assertStringContainsString('DbtrAgt', implode("\n", $errors));
    }

    public function testExplicitSchemaIsUsedInsteadOfBundledOne(): void
    {
        $builder = $this->buildCreditTransfer(new CustomerCreditTransferDomBuilder('pain.001.001.09'));

        $this->assertTrue($builder->validateSchema(XSD_DIR . 'pain.001.001.09.xsd'));
        $this->assertFalse($builder->validateSchema(XSD_DIR . 'pain.001.001.03.xsd'));
    }

    public function testFormatWithoutBundledSchemaNeedsExplicitPath(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.99');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('pain.001.001.99');
        $builder->validateSchema();
    }

    public function testMissingSchemaFileThrows(): void
    {
        $builder = new CustomerCreditTransferDomBuilder('pain.001.001.09');

        $this->expectException(InvalidArgumentException::class);
        $builder->validateSchema('/does/not/exist.xsd');
    }

    public function testLibxmlErrorHandlingIsRestored(): void
    {
        $previous = libxml_use_internal_errors(false);

        try {
            // An empty <Document/> is invalid, so libxml reports errors during validation.
            (new CustomerCreditTransferDomBuilder('pain.001.001.09'))->validateSchema();

            $this->assertFalse(libxml_use_internal_errors(false));
            $this->assertSame([], libxml_get_errors());
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    public function testFacadeValidatesRenderedDocument(): void
    {
        $facade = $this->createCreditFacade();

        $this->assertSame([], $facade->getSchemaValidationErrors());
        $this->assertTrue($facade->validateSchema());
        // Validating renders once; it must not re-flush payments into the transfer file.
        $this->assertStringContainsString('<NbOfTxs>1</NbOfTxs>', $facade->asXML());
    }

    public function testFacadeIsFinalizedByValidation(): void
    {
        $facade = $this->createCreditFacade();
        $facade->validateSchema();

        $this->expectException(LogicException::class);
        $facade->addPaymentInfo('secondPayment', [
            'id' => 'secondPayment',
            'debtorName' => 'My Company',
            'debtorAccountIBAN' => 'FI1350001540000056',
            'debtorAgentBIC' => 'PSSTFRPPMON',
        ]);
    }

    private function buildCreditTransfer(
        CustomerCreditTransferDomBuilder $builder,
        ?string $originBic = 'DEUTDEFFXXX'
    ): CustomerCreditTransferDomBuilder {
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', $originBic, 'Origin');
        $transfer = new CustomerCreditTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setBic('DEUTDEFF');
        $payment->addTransfer($transfer);

        $transferFile = new CustomerCreditTransferFile(new GroupHeader('MSG', 'Init'));
        $transferFile->addPaymentInformation($payment);
        $transferFile->accept($builder);

        return $builder;
    }

    private function buildDirectDebit(string $painFormat): CustomerDirectDebitTransferDomBuilder
    {
        $payment = new PaymentInformation('P1', 'DE88500105173441451911', 'DEUTDEFFXXX', 'Origin');
        $payment->setCreditorId('DE67ZZZ00000123456');
        $payment->setSequenceType(PaymentInformation::S_ONEOFF);
        $transfer = new CustomerDirectDebitTransferInformation(100, 'DE40500105174181777145', 'Bob');
        $transfer->setBic('INGDDEFFXXX');
        $transfer->setMandateId('MANDATE-1');
        $transfer->setMandateSignDate(new DateTime('2022-05-15'));
        $payment->addTransfer($transfer);

        $transferFile = new CustomerDirectDebitTransferFile(new GroupHeader('MSG', 'Init'));
        $transferFile->addPaymentInformation($payment);
        $builder = new CustomerDirectDebitTransferDomBuilder($painFormat);
        $transferFile->accept($builder);

        return $builder;
    }

    private function createCreditFacade(): CustomerCreditFacade
    {
        $facade = TransferFileFacadeFactory::createCustomerCredit('test123', 'Me', 'pain.001.001.09');
        $facade->addPaymentInfo('firstPayment', [
            'id' => 'firstPayment',
            'debtorName' => 'My Company',
            'debtorAccountIBAN' => 'FI1350001540000056',
            'debtorAgentBIC' => 'PSSTFRPPMON',
        ]);
        $facade->addTransfer('firstPayment', [
            'amount' => 500,
            'creditorIban' => 'FI1350001540000056',
            'creditorBic' => 'OKOYFIHH',
            'creditorName' => 'Their Company',
            'remittanceInformation' => 'Purpose of this credit',
        ]);

        return $facade;
    }
}
