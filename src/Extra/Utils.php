<?php

declare(strict_types=1);

/*
 * Burn After Reading: A secure, self-destructing message platform
 * Copyright (C) 2025  Snail Paste, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Extra;

class Utils
{
  public static function isBetween(int $value, int $min, int $max): bool
  {
    return $value >= $min && $value <= $max;
  }

  public static function makeDBDateTime(string $datetime = 'now'): string
  {
    return (new \DateTime($datetime))->format('Y-m-d H:i:s');
  }
}
