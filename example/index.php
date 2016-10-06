<?php

require __DIR__.'/../vendor/autoload.php';

use IbanDominguez\RestUp\App;

App::create([
  'M_DEBUG' => true,
  'DB_HOST' => 'localhost',
  'DB_NAME' => 'prueba',
  'DB_USER' => 'root',
  'DB_PASS' => '',
])
->add('posts', [
  ['title' => 'title', 'type' => 'string', 'rules' => 'required'],
  ['title' => 'body',  'type' => 'string'],
  ['title' => 'date',  'type' => 'date',   'rules' => 'date']
])
->add('cars', [
  ['title' => 'brand', 'type' => 'string']
])
->run(true);
