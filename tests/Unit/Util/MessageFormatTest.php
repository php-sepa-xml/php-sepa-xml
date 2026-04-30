<?php

namespace Digitick\Sepa\Tests\Unit\Util;

use Digitick\Sepa\Util\MessageFormat;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MessageFormatTest extends TestCase
{
    /**
     * @dataProvider messageNameParserTestProvider
     * @param string $messageName
     * @param string $messageType
     * @param int $messageSubType
     * @param int $messageVariant
     * @param int $messageVersion
     * @return void
     */
    public function testMessageNameParser(string $messageName, string $messageType, int $messageSubType, int $messageVariant, int $messageVersion): void
    {
        $messageNameInst = new MessageFormat($messageName);
        $this->assertEquals($messageName, $messageNameInst->getMessageName());
        $this->assertEquals($messageType, $messageNameInst->getType());
        $this->assertEquals($messageSubType, $messageNameInst->getSubType());
        $this->assertEquals($messageVariant, $messageNameInst->getVariant());
        $this->assertEquals($messageVersion, $messageNameInst->getVersion());
    }

    /**
     * @dataProvider invalidPatternProvider
     * @param string $pattern
     * @return void
     *
     */
    public function testValidationPattern(string $pattern): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MessageFormat($pattern);
    }

    /** @return void */
    public function testMessageIsFunctions(): void
    {
        $messageFormatInst = new MessageFormat('pain.001.001.12');
        $this->assertTrue($messageFormatInst->isCreditTransfer());
        $this->assertFalse($messageFormatInst->isDirectDebit());
        $this->assertTrue($messageFormatInst->isSupported());
        $this->assertTrue($messageFormatInst->isOf('pain', '001'));
        $this->assertFalse($messageFormatInst->isOf('camt', '054'));

        $messageFormatInst = new MessageFormat('pain.008.001.11');
        $this->assertTrue($messageFormatInst->isDirectDebit());
        $this->assertFalse($messageFormatInst->isCreditTransfer());
        $this->assertTrue($messageFormatInst->isSupported());
        $this->assertTrue($messageFormatInst->isSupported('pain.008.001.10'));

        $messageFormatInst = new MessageFormat('camt.054.001.02');
        $this->assertFalse($messageFormatInst->isSupported());
        $this->assertFalse($messageFormatInst->isDirectDebit());
        $this->assertFalse($messageFormatInst->isCreditTransfer());
        $this->assertTrue($messageFormatInst->isOf('camt', '054'));
    }

    /**
     * @return iterable
     */
    public static function messageNameParserTestProvider(): iterable
    {
        return [
            'pain.001.001.03' => [
                'messageName' => 'pain.001.001.03',
                'messageType' => 'pain',
                'messageSubType' => 1,
                'messageVariant' => 1,
                'messageVersion' => 3,
            ],
            'pain.001.001.12' => [
                'messageName' => 'pain.001.001.12',
                'messageType' => 'pain',
                'messageSubType' => 1,
                'messageVariant' => 1,
                'messageVersion' => 12,
            ],
            'pain.001.002.03' => [
                'messageName' => 'pain.001.002.03',
                'messageType' => 'pain',
                'messageSubType' => 1,
                'messageVariant' => 2,
                'messageVersion' => 3,
            ],
            'pain.001.003.03' => [
                'messageName' => 'pain.001.003.03',
                'messageType' => 'pain',
                'messageSubType' => 1,
                'messageVariant' => 3,
                'messageVersion' => 3,
            ],
            'pain.008.001.02' => [
                'messageName' => 'pain.008.001.02',
                'messageType' => 'pain',
                'messageSubType' => 8,
                'messageVariant' => 1,
                'messageVersion' => 2,
            ],
            'pain.008.030.10' => [
                'messageName' => 'pain.008.030.10',
                'messageType' => 'pain',
                'messageSubType' => 8,
                'messageVariant' => 30,
                'messageVersion' => 10,
            ],
            'pain.008.002.02' => [
                'messageName' => 'pain.008.002.02',
                'messageType' => 'pain',
                'messageSubType' => 8,
                'messageVariant' => 2,
                'messageVersion' => 2,
            ],
            'pain.008.003.02' => [
                'messageName' => 'pain.008.003.02',
                'messageType' => 'pain',
                'messageSubType' => 8,
                'messageVariant' => 3,
                'messageVersion' => 2,
            ],
        ];
    }

    /**
     * Pins current (permissive) constructor behaviour: a regex-valid name that is
     * not on the supported-formats whitelist is accepted silently. `isSupported()`
     * is the only signal that the name is unknown — the constructor never calls it.
     *
     * If a future change enforces the whitelist in the constructor, this test will
     * fail and force an explicit decision (deprecation, throw, or opt-in flag)
     * rather than silently breaking downstream callers. See IMPROVEMENTS.md #1.
     *
     * @dataProvider unsupportedButRegexValidProvider
     */
    public function testConstructorAcceptsRegexValidUnsupportedFormat(string $messageName): void
    {
        $messageFormat = new MessageFormat($messageName);

        $this->assertSame($messageName, $messageFormat->getMessageName());
        $this->assertFalse(
            $messageFormat->isSupported(),
            sprintf('%s should not be on the whitelist but constructor accepted it', $messageName)
        );
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function unsupportedButRegexValidProvider(): iterable
    {
        return [
            'unknown SCT version' => ['pain.001.001.99'],
            'unknown SDD version' => ['pain.008.001.99'],
            'unknown variant' => ['pain.001.099.03'],
            'non-pain message' => ['camt.054.001.02'],
        ];
    }

    /**
     * Only wrong messageNames here
     * @return iterable
     */
    public static function invalidPatternProvider(): iterable
    {
        return [
            'pain.001.001.100' => ['pain.001.001.100'],
            'pain.008.003.012' => ['pain.008.100.012'],
            'wrong.008.001.001' => ['wrong.008.100.012'],
            'URI' => ['https://example.com/schemas/pain.001.001.03.xsd'],
        ];
    }
}
