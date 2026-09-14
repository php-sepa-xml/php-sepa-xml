<?php

namespace Digitick\Sepa\Tests\Unit\Util;

use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\Util\Sanitizer;
use Error;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionProperty;

/**
 * Locks down the 3.x compatibility layer for the properties that were public
 * on PaymentInformation and GroupHeader before 3.2.0. Outside access must keep
 * working, trigger E_USER_DEPRECATED, and route writes through the setters so
 * the sanitizer can no longer be bypassed. Every other protected property must
 * stay inaccessible.
 */
class DeprecatedPublicPropertiesTest extends TestCase
{
    /**
     * @var array<int, array{0: int, 1: string}>
     */
    private $errors = [];

    protected function setUp(): void
    {
        Sanitizer::resetSanitizer();
        $this->errors = [];
        set_error_handler(function (int $errno, string $errstr): bool {
            $this->errors[] = [$errno, $errstr];

            return true;
        });
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        Sanitizer::resetSanitizer();
    }

    public static function formerlyPublicProperties(): iterable
    {
        return [
            'PaymentInformation::$id' => [PaymentInformation::class, 'id', 'getId', 'setId'],
            'PaymentInformation::$categoryPurposeCode' => [PaymentInformation::class, 'categoryPurposeCode', 'getCategoryPurposeCode', 'setCategoryPurposeCode'],
            'PaymentInformation::$originName' => [PaymentInformation::class, 'originName', 'getOriginName', 'setOriginName'],
            'PaymentInformation::$originBankPartyIdentification' => [PaymentInformation::class, 'originBankPartyIdentification', 'getOriginBankPartyIdentification', 'setOriginBankPartyIdentification'],
            'PaymentInformation::$originBankPartyIdentificationScheme' => [PaymentInformation::class, 'originBankPartyIdentificationScheme', 'getOriginBankPartyIdentificationScheme', 'setOriginBankPartyIdentificationScheme'],
            'PaymentInformation::$originAccountIBAN' => [PaymentInformation::class, 'originAccountIBAN', 'getOriginAccountIBAN', 'setOriginAccountIBAN'],
            'PaymentInformation::$originAgentBIC' => [PaymentInformation::class, 'originAgentBIC', 'getOriginAgentBIC', 'setOriginAgentBIC'],
            'GroupHeader::$initiatingPartyIdentificationScheme' => [GroupHeader::class, 'initiatingPartyIdentificationScheme', 'getInitiatingPartyIdentificationScheme', 'setInitiatingPartyIdentificationScheme'],
        ];
    }

    /**
     * @dataProvider formerlyPublicProperties
     */
    public function testPropertyIsProtected(string $class, string $property): void
    {
        $this->assertTrue((new ReflectionProperty($class, $property))->isProtected());
    }

    /**
     * @dataProvider formerlyPublicProperties
     */
    public function testReadReturnsValueAndTriggersDeprecation(
        string $class,
        string $property,
        string $getter,
        string $setter
    ): void {
        $obj = $this->createObject($class);
        $obj->$setter('ABCD1234');

        $this->assertSame('ABCD1234', $obj->$property);
        $this->assertSingleDeprecation($class, $property, $getter);
    }

    /**
     * @dataProvider formerlyPublicProperties
     */
    public function testWriteGoesThroughSetterAndTriggersDeprecation(
        string $class,
        string $property,
        string $getter,
        string $setter
    ): void {
        $obj = $this->createObject($class);
        $obj->$property = 'ABCD1234';

        $this->assertSame('ABCD1234', $obj->$getter());
        $this->assertSingleDeprecation($class, $property, $setter);
    }

    /**
     * @dataProvider formerlyPublicProperties
     */
    public function testIssetReflectsValueAndTriggersDeprecation(
        string $class,
        string $property,
        string $getter,
        string $setter
    ): void {
        $obj = $this->createObject($class);
        $obj->$setter('ABCD1234');

        $this->assertTrue(isset($obj->$property));
        $this->assertSingleDeprecation($class, $property, $getter);
    }

    public static function sanitizedProperties(): iterable
    {
        return [
            'PaymentInformation::$originName' => [PaymentInformation::class, 'originName', 'getOriginName'],
            'PaymentInformation::$originBankPartyIdentification' => [PaymentInformation::class, 'originBankPartyIdentification', 'getOriginBankPartyIdentification'],
            'PaymentInformation::$originBankPartyIdentificationScheme' => [PaymentInformation::class, 'originBankPartyIdentificationScheme', 'getOriginBankPartyIdentificationScheme'],
            'GroupHeader::$initiatingPartyIdentificationScheme' => [GroupHeader::class, 'initiatingPartyIdentificationScheme', 'getInitiatingPartyIdentificationScheme'],
        ];
    }

    /**
     * @dataProvider sanitizedProperties
     */
    public function testDirectWriteIsSanitized(string $class, string $property, string $getter): void
    {
        $obj = $this->createObject($class);
        $obj->$property = 'Jörg & Co';

        $this->assertSame('Joerg   Co', $obj->$getter());
    }

    // Property names below come from data providers, as dynamic names from outside code
    // would, so static analysis doesn't reject the deliberate access to non-public ones.

    public static function nullableProperties(): iterable
    {
        return [
            'PaymentInformation::$categoryPurposeCode' => [PaymentInformation::class, 'categoryPurposeCode', 'getCategoryPurposeCode', 'setCategoryPurposeCode'],
            'PaymentInformation::$originBankPartyIdentification' => [PaymentInformation::class, 'originBankPartyIdentification', 'getOriginBankPartyIdentification', 'setOriginBankPartyIdentification'],
            'PaymentInformation::$originBankPartyIdentificationScheme' => [PaymentInformation::class, 'originBankPartyIdentificationScheme', 'getOriginBankPartyIdentificationScheme', 'setOriginBankPartyIdentificationScheme'],
            'PaymentInformation::$originAgentBIC' => [PaymentInformation::class, 'originAgentBIC', 'getOriginAgentBIC', 'setOriginAgentBIC'],
            'GroupHeader::$initiatingPartyIdentificationScheme' => [GroupHeader::class, 'initiatingPartyIdentificationScheme', 'getInitiatingPartyIdentificationScheme', 'setInitiatingPartyIdentificationScheme'],
        ];
    }

    /**
     * @dataProvider nullableProperties
     */
    public function testNullCanStillBeWritten(
        string $class,
        string $property,
        string $getter,
        string $setter
    ): void {
        $obj = $this->createObject($class);
        $obj->$setter('ABCD1234');
        $obj->$property = null;

        $this->assertNull($obj->$getter());
        $this->assertFalse(isset($obj->$property));
    }

    /**
     * @dataProvider nullableProperties
     */
    public function testUnsetNullsTheProperty(
        string $class,
        string $property,
        string $getter,
        string $setter
    ): void {
        $obj = $this->createObject($class);
        $obj->$setter('ABCD1234');
        unset($obj->$property);

        $this->assertNull($obj->$getter());
        $this->assertSingleDeprecation($class, $property, $setter);
    }

    public static function otherProtectedProperties(): iterable
    {
        return [
            'PaymentInformation::$transfers' => [PaymentInformation::class, 'transfers'],
            'GroupHeader::$controlSumCents' => [GroupHeader::class, 'controlSumCents'],
        ];
    }

    /**
     * @dataProvider otherProtectedProperties
     */
    public function testReadingOtherProtectedPropertyStillThrows(string $class, string $property): void
    {
        $obj = $this->createObject($class);

        $this->expectException(Error::class);
        $value = $obj->$property;
    }

    /**
     * @dataProvider otherProtectedProperties
     */
    public function testWritingOtherProtectedPropertyStillThrows(string $class, string $property): void
    {
        $obj = $this->createObject($class);

        $this->expectException(Error::class);
        $obj->$property = 100;
    }

    /**
     * @dataProvider otherProtectedProperties
     */
    public function testUnsettingOtherProtectedPropertyStillThrows(string $class, string $property): void
    {
        $obj = $this->createObject($class);

        $this->expectException(Error::class);
        unset($obj->$property);
    }

    /**
     * @dataProvider otherProtectedProperties
     */
    public function testIssetOnOtherProtectedPropertyIsFalseWithoutDeprecation(string $class, string $property): void
    {
        $obj = $this->createObject($class);

        $this->assertFalse(isset($obj->$property));
        $this->assertSame([], $this->errors);
    }

    public static function undefinedProperties(): iterable
    {
        return [
            'PaymentInformation::$doesNotExist' => [PaymentInformation::class, 'doesNotExist'],
            'GroupHeader::$doesNotExist' => [GroupHeader::class, 'doesNotExist'],
        ];
    }

    /**
     * @dataProvider undefinedProperties
     */
    public function testReadingUndefinedPropertyWarns(string $class, string $property): void
    {
        $obj = $this->createObject($class);

        $this->assertNull($obj->$property);
        $this->assertCount(1, $this->errors);
        $this->assertSame(E_USER_WARNING, $this->errors[0][0]);
        $this->assertStringContainsString('Undefined property', $this->errors[0][1]);
    }

    public function testNoClassInSrcDeclaresPublicInstanceProperties(): void
    {
        $srcDir = dirname(__DIR__, 3) . '/src';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS));

        $offenders = [];
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $class = 'Digitick\\Sepa\\' . str_replace('/', '\\', substr($file->getPathname(), strlen($srcDir) + 1, -4));
            if (!class_exists($class) && !trait_exists($class)) {
                continue;
            }
            foreach ((new ReflectionClass($class))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic() && $property->getDeclaringClass()->getName() === $class) {
                    $offenders[] = $class . '::$' . $property->getName();
                }
            }
        }

        $this->assertSame([], $offenders, 'Public instance properties bypass setters; make them protected.');
    }

    /**
     * @return PaymentInformation|GroupHeader
     */
    private function createObject(string $class)
    {
        return $class === GroupHeader::class ? $this->createGroupHeader() : $this->createPaymentInformation();
    }

    private function createPaymentInformation(): PaymentInformation
    {
        return new PaymentInformation('PmtInfId', 'DE89370400440532013000', 'COBADEFFXXX', 'Origin Name');
    }

    private function createGroupHeader(): GroupHeader
    {
        return new GroupHeader('MsgId', 'Initiating Party');
    }

    private function assertSingleDeprecation(string $class, string $property, string $replacement): void
    {
        $this->assertCount(1, $this->errors);
        $this->assertSame(E_USER_DEPRECATED, $this->errors[0][0]);
        $this->assertStringContainsString($class . '::$' . $property, $this->errors[0][1]);
        $this->assertStringContainsString($replacement . '()', $this->errors[0][1]);
    }
}
