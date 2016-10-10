<?php

require __DIR__.'/../vendor/autoload.php';

use IbanDominguez\RestUp\App;

App::create([
  'DB_HOST' => 'localhost',
  'DB_NAME' => 'prueba',
  'DB_USER' => 'root',
  'DB_PASS' => '',
])
->auth([
  'admin@email.com' => 'admin'
])
->add('posts', [
  ['title' => 'title', 'type' => 'string', 'rules' => 'required'],
  ['title' => 'body',  'type' => 'string'],
  ['title' => 'date',  'type' => 'date',   'rules' => 'date']
], [
  'except' => ['show']
])
->add('cars', [
  ['title' => 'brand', 'type' => 'string', 'rules' => 'required'],
  ['title' => 'made_at',  'type' => 'datetime']
])
->run(true);
