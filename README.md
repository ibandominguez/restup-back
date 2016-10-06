# Restup
Build a rest api in a single file.

## Use Example
```php
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
  ['title' => 'title', 'type' => 'string'],
  ['title' => 'body',  'type' => 'string'],
  ['title' => 'other', 'type' => 'string']
])
->add('cars', [
  ['title' => 'brand', 'type' => 'string']
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
