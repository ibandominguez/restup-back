<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\App;

class AppTest extends TestCase
{

  public function testClassIntanciates()
  {
    $this->assertInstanceOf('IbanDominguez\RestUp\App', new App());
  }

}
