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

use League\Config\Configuration;
use Nette\Schema\Expect;
use Slim\Views\Twig;

return [
  Configuration::class => function (): Configuration {
    // Define configuration schema
    $config = new Configuration([
      'database' => Expect::structure([
        'host' => Expect::string('127.0.0.1'),
        'port' => Expect::int(3306),
        'database' => Expect::string()->required(),
        'username' => Expect::string()->required(),
        'password' => Expect::string()->required()
      ]),
      'limits' => Expect::structure([
        'iv' => Expect::int(128),
        'ciphertext' => Expect::int(16384)
      ]),
      'lifetimes' => Expect::list()->default([
        '5 minutes',
        '10 minutes',
        '1 hour',
        '2 hours',
        '12 hours',
        '1 day',
        '7 days'
      ]),
      'default_lifetime' => Expect::string('1 day')
    ]);

    // Merge our configuration file information
    $config->merge(require dirname(__DIR__).'/config.php');

    return $config;
  },

  Twig::class => function () {
    return Twig::create(dirname(__DIR__).'/views', [
      'cache' => dirname(__DIR__).'/var/cache/twig',
      'auto_reload' => true
    ]);
  },

  PDO::class => function (Configuration $config): PDO {
    $c = $config->get('database');
    return new PDO("mysql:dbname={$c['database']};host={$c['host']};port={$c['port']}", $c['username'], $c['password'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
    ]);
  },
];
