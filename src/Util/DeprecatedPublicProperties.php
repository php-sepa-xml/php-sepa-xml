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

use Error;

/**
 * Keeps outside access working for properties that were public before 3.2.0.
 *
 * Reading or writing one of those properties from outside the class triggers an
 * E_USER_DEPRECATED. Writes go through the matching setter, so values are
 * sanitized exactly as when the setter is called directly. Every other
 * inaccessible property keeps PHP's native behaviour.
 *
 * To be removed in 4.0, together with the deprecatedPublicPropertyMap()
 * implementations.
 *
 * @internal
 */
trait DeprecatedPublicProperties
{
    /**
     * Properties that were public before 3.2.0, mapped to their accessors.
     *
     * @return array<string, array{0: string, 1: string}> property name => [getter, setter]
     */
    abstract protected function deprecatedPublicPropertyMap(): array;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $map = $this->deprecatedPublicPropertyMap();
        if (!isset($map[$name])) {
            if (property_exists($this, $name)) {
                throw $this->inaccessiblePropertyError($name);
            }
            trigger_error(sprintf('Undefined property: %s::$%s', static::class, $name), E_USER_WARNING);

            return null;
        }

        $this->triggerPublicPropertyDeprecation($name, $map[$name][0]);

        return $this->$name;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value): void
    {
        $map = $this->deprecatedPublicPropertyMap();
        if (!isset($map[$name])) {
            if (property_exists($this, $name)) {
                throw $this->inaccessiblePropertyError($name);
            }
            $this->$name = $value;

            return;
        }

        $this->triggerPublicPropertyDeprecation($name, $map[$name][1]);

        if ($value === null) {
            // The setters only accept strings, but null was assignable while the property was public.
            $this->$name = null;

            return;
        }

        $this->{$map[$name][1]}($value);
    }

    /**
     * @param string $name
     */
    public function __isset($name): bool
    {
        $map = $this->deprecatedPublicPropertyMap();
        if (!isset($map[$name])) {
            return false;
        }

        $this->triggerPublicPropertyDeprecation($name, $map[$name][0]);

        return isset($this->$name);
    }

    /**
     * @param string $name
     */
    public function __unset($name): void
    {
        $map = $this->deprecatedPublicPropertyMap();
        if (!isset($map[$name])) {
            if (property_exists($this, $name)) {
                throw $this->inaccessiblePropertyError($name);
            }

            return;
        }

        $this->triggerPublicPropertyDeprecation($name, $map[$name][1]);

        // Null instead of a real unset, so later reads inside the class don't end up in __get().
        $this->$name = null;
    }

    private function triggerPublicPropertyDeprecation(string $name, string $replacement): void
    {
        $message = sprintf(
            'Accessing %s::$%s directly is deprecated since 3.2.0 and will not work in 4.0. Use %s::%s() instead.',
            static::class,
            $name,
            static::class,
            $replacement
        );

        @trigger_error($message, E_USER_DEPRECATED);
    }

    private function inaccessiblePropertyError(string $name): Error
    {
        return new Error(sprintf('Cannot access non-public property %s::$%s', static::class, $name));
    }
}
