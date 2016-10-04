<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\Route;

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

}
