<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\Route;
use IbanDominguez\RestUp\Resource;

class RouteTest extends TestCase
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
    new Route();
  }

  public function testThrowsAnExpetionIfNameDoesNotExits()
  {
    $this->expectException(Exception::class);
    new Route('other', new Resource('posts', [
      ['title' => 'title', 'type' => 'string', 'rules' => 'required|string'],
      ['title' => 'body', 'type' => 'string', 'rules' => 'required|string']
    ]));
  }

  public function testClassIntanciates()
  {
    $this->assertInstanceOf('IbanDominguez\RestUp\Route',  new Route('index', new Resource('posts', [
      ['title' => 'title', 'type' => 'string', 'rules' => 'required|string'],
      ['title' => 'body', 'type' => 'string', 'rules' => 'required|string']
    ])));
  }

}
