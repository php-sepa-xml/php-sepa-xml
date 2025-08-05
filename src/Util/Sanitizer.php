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

class Sanitizer
{
    /**
     * @var (callable(string): string)|null
     */
    private static $callback = null;

    /**
     * Set the global sanitization strategy.
     */
    public static function setSanitizer(callable $callback): void
    {
        self::$callback = $callback;
    }

    /**
     * Get the current sanitizer (with fallback to default).
     */
    public static function getSanitizer(): callable
    {
        return self::$callback ?? [StringHelper::class, 'sanitizeString'];
    }

    /**
     * Apply sanitization.
     */
    public static function sanitize(string $value): string
    {
        return call_user_func(self::getSanitizer(), $value);
    }

    /**
     * Disable the sanitizer.
     */
    public static function disableSanitizer(): void
    {
        self::$callback = fn(string $value): string => $value;
    }

    /**
     * Reset the sanitizer to the default.
     */
    public static function resetSanitizer(): void
    {
        self::$callback = null;
    }
}