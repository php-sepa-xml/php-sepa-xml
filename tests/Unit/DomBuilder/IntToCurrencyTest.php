<?php

namespace Digitick\Sepa\Tests\Unit\DomBuilder;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Isolated tests for BaseDomBuilder::intToCurrency (cents → string "xxx.yy").
 *
 * Context this guards: monetary formatting is regulated in SEPA — a flip from
 * sprintf('%F', …) to '%f' would silently introduce a locale-dependent
 * decimal separator (',' in many European locales) and produce schema-invalid
 * XML in production. The functional tests cover the integer-money path
 * end-to-end once per data set; this file locks the formatter behaviour down
 * at the unit level.
 */
class IntToCurrencyTest extends TestCase
{
    /**
     * @dataProvider amountProvider
     */
    public function testFormatting(int $cents, string $expected): void
    {
        $formatter = $this->formatter();

        $this->assertSame($expected, $formatter->publicIntToCurrency($cents));
    }

    public static function amountProvider(): iterable
    {
        return [
            'zero'            => [0, '0.00'],
            'one cent'        => [1, '0.01'],
            'ten cents'       => [10, '0.10'],
            'ninety-nine'     => [99, '0.99'],
            'one euro'        => [100, '1.00'],
            'typical'         => [12345, '123.45'],
            'thousand euros'  => [100000, '1000.00'],
            'max SEPA amount' => [99999999999, '999999999.99'],
        ];
    }

    /**
     * @dataProvider localeProvider
     */
    public function testFormattingIsLocaleInsensitive(string $localeName, array $locales): void
    {
        $original = setlocale(LC_ALL, '0');
        $applied = setlocale(LC_ALL, ...$locales);
        if ($applied === false) {
            $this->markTestSkipped(sprintf('%s locale unavailable on this system', $localeName));
        }

        try {
            $formatter = $this->formatter();
            $this->assertSame(
                '123.45',
                $formatter->publicIntToCurrency(12345),
                "intToCurrency must produce '.' as the decimal separator regardless of locale"
            );
        } finally {
            setlocale(LC_ALL, $original);
        }
    }

    public static function localeProvider(): iterable
    {
        return [
            'Spanish' => ['Spanish', ['es_ES.UTF-8', 'es_ES@UTF-8', 'spanish']],
            'French'  => ['French',  ['fr_FR.UTF-8', 'fr_FR@UTF-8', 'french']],
            'German'  => ['German',  ['de_DE.UTF-8', 'de_DE@UTF-8', 'german']],
        ];
    }

    private function formatter(): object
    {
        return new class('pain.001.001.09') extends CustomerCreditTransferDomBuilder {
            public function publicIntToCurrency(int $amount): string
            {
                return $this->intToCurrency($amount);
            }
        };
    }
}
