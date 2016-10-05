<?php

namespace IbanDominguez\RestUp;

use Slim\App as Slim;
use PDO;

class App
{

  /**
   * @var Slim\App
   */
  protected $slim;

  /**
   * @var array
   */
  protected $resources = [];

  /**
   * @return void
   */
  public function __construct()
  {
    $this->slim = new Slim();
  }

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

  /**
   * @return void
   */
  public function run()
  {
    $this->bindRoutes();
    $this->slim->run();
  }

  /**
   * @return void
   */
  private function bindRoutes()
  {
    foreach ($this->resources as $resource):
      foreach ($resource->routes as $route):
        $this->slim->map([strtoupper($route->method)], $route->path, $route->makeClousure());
      endforeach;
    endforeach;
  }

}
