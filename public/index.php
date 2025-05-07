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

use App\Controllers\MainController;
use DI\Bridge\Slim\Bridge;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Build our container
$builder = new DI\ContainerBuilder();
$builder->addDefinitions(__DIR__.'/../src/di-config.php');
$container = $builder->build();

// Create our application
$app = Bridge::create($container);

// Add middleware
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

$app->addErrorMiddleware(false, false, false, null);

$app->get('/', [MainController::class, 'create'])->setName('create');
$app->post('/store', [MainController::class, 'store'])->setName('store');
$app->get('/view/{link}', [MainController::class, 'view'])->setName('view');
$app->get('/retrieve/{link}', [MainController::class, 'retrieve'])->setName('retrieve');

$app->run();
