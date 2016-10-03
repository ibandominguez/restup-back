<?php

namespace IbanDominguez\RestUp;

use Exception;

class Resource {

  public function __construct($title, array $fields)
  {
    $this->title = $title;
    $this->fields = $fields;
    $this->validate();
  }

  private function validate()
  {
     //
  }

}
