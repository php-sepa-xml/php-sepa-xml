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

namespace Digitick\Sepa\Util;

class StringHelper
{
    /**
     * @param string $inputString
     * @return string
     */
    public static function sanitizeString($inputString)
    {
        $map = array(
            // German
            'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
            // others
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Å' => 'A', 'Ă' => 'A', 'Æ' => 'A',
            'Þ' => 'B', 'Ç' => 'C', 'Ĉ' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ĕ' => 'E',
            'Ġ' => 'G', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N', 'Ń' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O',
            'Ŝ' => 'S', 'Š' => 'S', 'Ș' => 'S', 'Ț' => 'T',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ý' => 'Y',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'å' => 'a', 'ă' => 'a', 'æ' => 'a',
            'þ' => 'b', 'ç' => 'c', 'ĉ' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ĕ' => 'e', 'ƒ' => 'f',
            'ġ' => 'g', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n', 'ń' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ð' => 'o',
            'ŝ' => 's', 'ș' => 's', 'š' => 's', 'ț' => 't',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ÿ' => 'y',
            'Ð' => 'Dj','Ž' => 'Z', 'ž' => 'z',
        );

        $mapped = strtr($inputString, $map);
        $sanitized = preg_replace('/[^A-Za-z0-9:?,\-\/(+.) ]/', ' ', $mapped);

        return $sanitized;
    }
}
