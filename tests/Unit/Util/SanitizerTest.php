<?php

namespace Digitick\Sepa\Tests\Unit\Util;

use Digitick\Sepa\Util\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Sanitizer class.
 */
class SanitizerTest extends TestCase
{
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

        Sanitizer::setSanitizer(fn(string $value): string => strtoupper($value));

        $this->assertEquals("AZ09#_<&*:?,-/(+.)' ", Sanitizer::sanitize($string));
    }

    /**
     * Tests reset sanitizer to default.
     */
    public function testResetSanitizer(): void
    {
        $string = "ÄÖÜäöüß";

        Sanitizer::setSanitizer(fn(string $value): string => strtoupper($value));
        Sanitizer::resetSanitizer();

        $this->assertEquals("AeOeUeaeoeuess", Sanitizer::sanitize($string));
    }
}
