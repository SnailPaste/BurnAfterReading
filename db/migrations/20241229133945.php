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

use Phinx\Migration\AbstractMigration;

final class V20241229133945 extends AbstractMigration
{
  /**
   * Change Method.
   *
   * Write your reversible migrations using this method.
   *
   * More information on writing migrations is available here:
   * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
   *
   * Remember to call "create()" or "update()" and NOT "save()" when working
   * with the Table class.
   */
  public function change(): void
  {
    $messages = $this->table('messages');
    $messages
      ->addColumn('link', 'string', ['length' => 64])
      ->addColumn('iv', 'string', ['length' => 255])
      ->addColumn('ciphertext', 'text')
      ->addColumn('expires', 'datetime')
      ->addIndex(['link'], ['unique' => true])
      ->create();
  }
}
