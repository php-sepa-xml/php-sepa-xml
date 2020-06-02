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

namespace tests;

use PHPUnit\Framework\TestCase;

class TestFileSanityCheckTest extends TestCase
{
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp(): void
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     * @dataProvider painProvider
     */
    public function testSanity($pain)
    {
        $schema = __DIR__ . '/' . $pain . '.xsd';
        $this->dom->load(__DIR__ . '/' . $pain . '.xml');
        $validated = $this->dom->schemaValidate($schema);
        $this->assertTrue($validated);
    }

    public function painProvider()
    {
        return array(
            array('pain.001.001.03'),
            array('pain.001.002.03'),
            array('pain.001.003.03'),
            array('pain.008.001.02'),
            array('pain.008.002.02'),
            array('pain.008.003.02'),
        );
    }
}
