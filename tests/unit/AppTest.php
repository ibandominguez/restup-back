<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\App;

class AppTest extends TestCase
{

  public function testClassIntanciates()
  {
    $this->assertInstanceOf('IbanDominguez\RestUp\App', new App());
  }

  public function testItRegistersRoutes()
  {
    $app = new App();

    $app->add('posts', [
      'fields' => [
        'title' => ['type' => 'string', 'rules' => 'required|string'],
        'content' => ['type' => 'string', 'rules' => 'required|string'],
      ]
    ]);

    $resources = $app->getResources();

    $this->assertTrue(count($resources) == 1);
    $this->assertInstanceOf('IbanDominguez\RestUp\Resource', $resources[0]);
  }

}
