<?php

require __DIR__.'/../vendor/autoload.php';

use IbanDominguez\RestUp\App;

App::create([
  'JWT_KEY' => 'supersecret',
  'DB_HOST' => 'localhost',
  'DB_NAME' => 'prueba',
  'DB_USER' => 'root',
  'DB_PASS' => ''
])
->auth([
  'admin@email.com' => 'admin'
])
->add('books', [
  ['title' => 'title', 'type' => 'string', 'rules' => 'required'],
  ['title' => 'date',  'type' => 'date',   'rules' => 'required|date']
])
->add('posts', [
  ['title' => 'title', 'type' => 'string', 'rules' => 'required'],
  ['title' => 'body',  'type' => 'string'],
  ['title' => 'date',  'type' => 'date',   'rules' => 'date']
], [
  'except'    => ['show'],
  'protected' => ['save']
])
->run(true);
