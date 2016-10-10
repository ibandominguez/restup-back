# Restup
Build a rest api in a single file.

## Use Example
```php
<?php

require __DIR__.'/../vendor/autoload.php';

use IbanDominguez\RestUp\App;

App::create([
  'JWT_KEY' => 'supersecret',
  'DB_HOST' => 'localhost',
  'DB_NAME' => 'prueba',
  'DB_USER' => 'root',
  'DB_PASS' => '',
])
->auth([
  'admin@email.com' => 'admin'
])
->add('books', [
  ['title' => 'title', 'type' => 'string', 'rules' => 'required'],
  ['title' => 'date',  'type' => 'date',   'rules' => 'required|date'],
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
```

## Tests
```sh
git clone https://github.com/ibandominguez/restup-back.git
cd restup-back
phpunit
```

## Roadmap
* Image uploads
* JWT auth configurable routes

## Contributors
* Ibán Domínguez

## License
Mit
