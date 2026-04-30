<?php

namespace Digitick\Sepa\Tests\Unit\Util;

use Digitick\Sepa\Util\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Sanitizer class.
 */
class SanitizerTest extends TestCase
{
    protected function tearDown(): void
    {
        Sanitizer::resetSanitizer();
    }

    /**
     * Tests german characters' translation with default sanitizer.
     */
    public function testGermanCharacters(): void
    {
        $string = 'ÄÖÜäöüß';

        $this->assertEquals('AeOeUeaeoeuess', Sanitizer::sanitize($string));
    }

    /**
     * Tests some special characters' translation with default sanitizer.
     */
    public function testSpecialCharacters(): void
    {
        $string = "Az09#_<&*:?,-/(+.)' ";

        $this->assertEquals("Az09     :?,-/(+.)' ", Sanitizer::sanitize($string));
    }

    /**
     * Tests german characters' translation with disabled sanitizer.
     */
    public function testGermanCharactersWithDisabledSanitizer(): void
    {
        $string = 'ÄÖÜäöüß';

        Sanitizer::disableSanitizer();

        $this->assertEquals('ÄÖÜäöüß', Sanitizer::sanitize($string));
    }

    /**
     * Tests some special characters' translation with disabled sanitizer.
     */
    public function testSpecialCharactersWithDisabledSanitizer(): void
    {
        $string = "Az09#_<&*:?,-/(+.)' ";

        Sanitizer::disableSanitizer();

        $this->assertEquals("Az09#_<&*:?,-/(+.)' ", Sanitizer::sanitize($string));
    }

    /**
     * Tests custom sanitizer.
     */
    public function testCustomSanitizer(): void
    {
        $string = "Az09#_<&*:?,-/(+.)' ";

        Sanitizer::setSanitizer(function (string $value): string {
            return strtoupper($value);
        });

        $this->assertEquals("AZ09#_<&*:?,-/(+.)' ", Sanitizer::sanitize($string));
    }

    /**
     * Tests reset sanitizer to default.
     */
    public function testResetSanitizer(): void
    {
        $string = "ÄÖÜäöüß";

        Sanitizer::setSanitizer(function (string $value): string {
            return strtoupper($value);
        });
        Sanitizer::resetSanitizer();

        $this->assertEquals("AeOeUeaeoeuess", Sanitizer::sanitize($string));
    }

    /**
     * Sanitizer holds process-global mutable state (`self::$callback`). Without a
     * tearDown that resets it, a custom callback set by one test leaks into every
     * subsequent test in the suite — order-dependent failures that are painful to
     * diagnose. See IMPROVEMENTS.md #8.
     *
     * This test asserts the default sanitizer is in effect at the start of the
     * test, so it only passes when tearDown actually resets state.
     */
    public function testCustomSanitizerDoesNotLeakAcrossTests(): void
    {
        Sanitizer::setSanitizer(function (string $value): string {
            return strtoupper($value);
        });

        $this->assertSame('LEAK', Sanitizer::sanitize('leak'));

        $this->tearDown();

        $this->assertSame(
            'AeOeUeaeoeuess',
            Sanitizer::sanitize('ÄÖÜäöüß'),
            'tearDown must restore the default sanitizer to prevent state leakage between tests'
        );
    }
}
