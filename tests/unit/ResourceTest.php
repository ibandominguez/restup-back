<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\Resource;
use InvalidArgumentException;

class ResourceTest extends TestCase
{

  protected function setUp()
  {
    set_error_handler(array($this, 'errorHandler'));
  }

  public function errorHandler($errno, $errstr, $errfile, $errline)
  {
    throw new InvalidArgumentException(sprintf(
      'Missing argument. %s %s %s %s',
      $errno,
      $errstr,
      $errfile,
      $errline
    ));
  }

  public function testThrowsAnExpetionIfParamsAreInvalid()
  {
    $this->expectException(InvalidArgumentException::class);
    new Resource();
  }

}
