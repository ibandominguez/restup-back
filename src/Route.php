<?php

namespace IbanDominguez\RestUp;

class Route {

  public function __construct($name, $path, $method, array $fields)
  {
    $this->name = $name;
    $this->path = $path;
    $this->method = $method;
    $this->fields = $fields;
  }

}
