<?php

namespace IbanDominguez\RestUp;

class App {

  protected $resources;

  public function add($title, $fields)
  {
    $this->resources[] = new Resource($title, $fields);

    return $this;
  }

  public function getResources()
  {
    return $this->resources;
  }

}
