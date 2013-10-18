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
    public static function sanitizeString($inputString) {
        $searches =     array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß');
        $replacements = array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss');

        return str_replace($searches, $replacements, $inputString);
    }
}