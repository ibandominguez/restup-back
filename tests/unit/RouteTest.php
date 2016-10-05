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
      ['key' => 'title', 'type' => 'string', 'rules' => 'required|string'],
      ['key' => 'body', 'type' => 'string', 'rules' => 'required|string']
    ]));
  }

}
