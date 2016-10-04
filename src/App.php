<?php

namespace IbanDominguez\RestUp;

class App
{

  /**
   * @var array
   */
  protected $resources = [];

  /**
   * @param string
   * @param array
   * @return IbanDominguez\RestUp\App
   */
  public function add($title, $fields)
  {
    $this->resources[] = new Resource($title, $fields);

    return $this;
  }

  /**
   * @return array
   */
  public function getResources()
  {
    return $this->resources;
  }

}
