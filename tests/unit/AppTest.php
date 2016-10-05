<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\App;

class AppTest extends TestCase
{

  public function testClassIntanciates()
  {
    $this->assertInstanceOf('IbanDominguez\RestUp\App', new App([]));
  }

  public function testItRegistersResource()
  {
    $app = new App([]);

    $app->add('posts', [
      ['title' => 'title', 'type' => 'string', 'rules' => 'required|string'],
      ['title' => 'body', 'type' => 'string', 'rules' => 'required|string']
    ]);

    $resources = $app->getResources();

    $this->assertTrue(count($resources) == 1);
    $this->assertInstanceOf('IbanDominguez\RestUp\Resource', $resources[0]);
  }

}
