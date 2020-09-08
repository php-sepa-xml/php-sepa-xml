<?php

namespace Tests\Unit\Digitick\Sepa\Util;

use Digitick\Sepa\Util\StringHelper;

/**
 * Unit test for StringHelper
 */
class StringHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests german character translation
     */
    public function testGermanCharacters()
    {
        $string = 'ÄÖÜäöüß';

        $this->assertEquals('AEOEUEaeoeuess', StringHelper::sanitizeString($string));
    }

    /**
     * Tests german character translation
     */
    public function testSpecialCharacters()
    {
        $string = 'Az09#_:?,-(+.)';

        $this->assertEquals('Az09  :?,-(+.)', StringHelper::sanitizeString($string));
    }

}
