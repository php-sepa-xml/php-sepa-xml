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

namespace Digitick\Sepa\Tests;

use PHPUnit\Framework\Assert;

/**
 * XPath helpers that narrow the DOM extension's union return types
 * (DOMNodeList|false, DOMNode|null) with assertions, so test call sites
 * stay both PHPStan-clean and fail with a readable message instead of a
 * fatal error when a node is missing.
 */
trait XPathAssertions
{
    /**
     * Run an XPath query, asserting that the expression itself is valid.
     *
     * @return \DOMNodeList<\DOMNameSpaceNode|\DOMNode>
     */
    protected static function xpathQuery(\DOMXPath $xpath, string $expression, ?\DOMNode $context = null): \DOMNodeList
    {
        $list = $xpath->query($expression, $context);
        Assert::assertNotFalse($list, sprintf('XPath expression "%s" is malformed.', $expression));

        return $list;
    }

    /**
     * Return the node at $index of the query result, asserting it exists.
     */
    protected static function xpathNode(\DOMXPath $xpath, string $expression, ?\DOMNode $context = null, int $index = 0): \DOMNode
    {
        $node = self::xpathQuery($xpath, $expression, $context)->item($index);
        Assert::assertInstanceOf(
            \DOMNode::class,
            $node,
            sprintf('Expected a node at index %d for XPath expression "%s".', $index, $expression)
        );

        return $node;
    }

    /**
     * Return the text content of the node at $index of the query result,
     * asserting the node exists.
     */
    protected static function xpathText(\DOMXPath $xpath, string $expression, ?\DOMNode $context = null, int $index = 0): string
    {
        return self::xpathNode($xpath, $expression, $context, $index)->textContent;
    }
}
