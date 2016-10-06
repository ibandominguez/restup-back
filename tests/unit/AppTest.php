<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\App;
use Slim\App as Slim;

class AppTest extends TestCase
{

  public function testClassInstanciates()
  {
    $this->assertInstanceOf('IbanDominguez\RestUp\App', new App(
      new PDO('sqlite::memory:'),
      new Slim()
    ));
  }

  public function testItRegistersResource()
  {
    $app = new App(
      new PDO('sqlite::memory:'),
      new Slim()
    );

    $app->add('posts', [
      ['title' => 'title', 'type' => 'string', 'rules' => 'required|string'],
      ['title' => 'body', 'type' => 'string', 'rules' => 'required|string']
    ]);

    $resources = $app->getResources();

    $this->assertTrue(count($resources) == 1);
    $this->assertInstanceOf('IbanDominguez\RestUp\Resource', $resources[0]);
  }

}
