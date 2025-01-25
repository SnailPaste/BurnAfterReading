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

namespace App\Controllers;

use App\Extra\Utils;
use League\Config\Configuration;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Random\RandomException;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class MainController
{
  public function create(Request $request, Response $response, Configuration $config, Twig $twig): Response
  {
    return $twig->render($response, 'create.html.twig', [
      'lifetimes' => $config->get('lifetimes'),
      'default_lifetime' => $config->get('default_lifetime'),
      'limits' => $config->get('limits'),
    ]);
  }

  public function store(Request $request, Response $response, Configuration $config, PDO $pdo): Response
  {
    $data = $request->getParsedBody();

    // Verify we have all the required input
    foreach (['iv', 'ciphertext', 'lifetime'] as $key) {
      if (!array_key_exists($key, $data)) {
        $response->getBody()->write(json_encode([
          'error' => 'Some required information was not provided.'
        ]));
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(400);
      }
    }

    // Fetch all valid lifetimes
    $lifetimes = $config->get('lifetimes');

    // Validate the incoming data
    if (!Utils::isBetween(strlen($data['iv']), 1, $config->get('limits.iv')) || !Utils::isBetween(strlen($data['ciphertext']), 1, $config->get('limits.ciphertext')) || !array_key_exists($data['lifetime'], $lifetimes)) {
      $response->getBody()->write(json_encode([
        'error' => 'Invalid data was provided.'
      ]));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(400);
    }

    // Generate a random link
    try {
      $link = bin2hex(random_bytes(32));
    } catch (RandomException) {
      $response->getBody()->write(json_encode([
        'error' => 'Failed to generate link.'
      ]));
      return $response->withStatus(500);
    }

    try {
      $statement = $pdo->prepare('INSERT INTO messages (link, iv, ciphertext, expires) VALUES (:link, :iv, :ciphertext, :expires)');
      $statement->bindValue('link', $link);
      $statement->bindValue('iv', $data['iv']);
      $statement->bindValue('ciphertext', $data['ciphertext']);
      $statement->bindValue('expires', Utils::makeDBDateTime($lifetimes[$data['lifetime']]));
      $statement->execute();
    } catch (PDOException) {
      $response->getBody()->write(json_encode([
        'error' => 'Failed to save message.'
      ]));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(500);
    }

    // Generate the URL for the view route
    $route_context = RouteContext::fromRequest($request);
    $url = $route_context->getRouteParser()->urlFor('view', ['link' => $link]);

    // Send back the URL and the link value
    $response->getBody()->write(json_encode([
      'url' => $url
    ]));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function view(Request $request, Response $response, Twig $twig, PDO $pdo, string $link): Response
  {
    // Try fetching the message associated with the link value, if it hasn't expired
    try {
      $get = $pdo->prepare('SELECT id FROM messages WHERE link = :link AND expires > :now');
      $get->bindValue('link', $link);
      $get->bindValue('now', Utils::makeDBDateTime());
      $get->execute();
      $message = $get->fetch();
      if (!$message) {
        throw new \Exception();
      }
    } catch (\Exception) {
      throw new HttpNotFoundException($request, 'Message does not exist or has been viewed or expired.');
    }
    return $twig->render($response, 'view.html.twig', ['link' => $link]);
  }

  public function retrieve(Request $request, Response $response, Configuration $config, PDO $pdo, string $link): Response
  {
    // Try fetching the message associated with the link value, if it hasn't expired
    try {
      $get = $pdo->prepare('SELECT id, iv, ciphertext FROM messages WHERE link = :link AND expires > :now');
      $get->bindValue('link', $link);
      $get->bindValue('now', Utils::makeDBDateTime());
      $get->execute();
      $message = $get->fetch();
      if (!$message) {
        throw new \Exception();
      }
    } catch (\Exception) {
      $response->getBody()->write(json_encode([
        'error' => 'Message not found'
      ]));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
    }

    // Burn after reading
    try {
      $delete = $pdo->prepare('DELETE FROM messages WHERE id = :id');
      $delete->bindValue('id', $message['id']);
      $delete->execute();
    } catch (PDOException) {
    }

    // Send the iv and ciphertext back to the browser
    $response->getBody()->write(json_encode([
      'iv' => $message['iv'],
      'ciphertext' => $message['ciphertext']
    ]));
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
