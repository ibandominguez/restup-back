<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\Resource;

class ResourceTest extends TestCase
{

  protected function setUp()
  {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
      throw new InvalidArgumentException(sprintf(
        'Missing argument. %s %s %s %s',
        $errno,
        $errstr,
        $errfile,
        $errline
      ));
    });
  }

  public function testThrowsAnExpetionIfParamsAreInvalid()
  {
    $this->expectException(InvalidArgumentException::class);
    new Resource();
  }

  public function testThrowsAnExpetionIfRouteConfigIsNotValid()
  {
    $this->expectException(Exception::class);
    new Resource('posts', [[]]);
  }

  public function testResouceCreatesRoutes()
  {
    $resource = new Resource('posts', [
      ['title' => 'title', 'type' => 'string', 'rules' => 'required|string'],
      ['title' => 'body', 'type' => 'string', 'rules' => 'required|string']
    ]);

    $routes = $resource->getRoutes();

    $this->assertTrue(count($routes) == 5);
    $this->assertInstanceOf('IbanDominguez\RestUp\Route', $routes[0]);
  }

  public function testInvalidTypes()
  {
    $this->expectException(Exception::class);
    new Resource('posts', [
      ['title' => 'title', 'type' => 'invalidtype']
    ]);
  }

  public function testItRetrievesRules()
  {
    $resource = new Resource('posts', [
      ['title' => 'title', 'type' => 'string', 'rules' => 'required']
    ]);

    $this->assertEquals($resource->getFieldRules()['title'], 'required');
  }

}
