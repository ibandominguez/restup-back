<?php

namespace IbanDominguez\RestUp;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use IbanDominguez\RestUp\Resource;
use Exception;

class Route
{

  /**
   * @var IbanDominguez\RestUp\Resource
   */
  protected $resource;

  /**
   * @var array
   */
  protected $routesNames = [
    'list'   => ['method' => 'GET',    'path' => '/%s'],
    'show'   => ['method' => 'GET',    'path' => '/%s/{id}'],
    'save'   => ['method' => 'POST',   'path' => '/%s'],
    'update' => ['method' => 'PUT',    'path' => '/%s/{id}'],
    'delete' => ['method' => 'DELETE', 'path' => '/%s/{id}'],
  ];

  /**
   * @param string
   * @param string
   * @param string
   * @param array
   * @return void
   */
  public function __construct($name, Resource $resource)
  {
    $this->name = $name;
    $this->resource = $resource;
    $this->path = $this->getPath($this->name);
    $this->method = $this->getMethod($this->name);
  }

  /**
   * @return callable
   */
  public function makeClousure()
  {
    $route = $this;

    return function(Request $request, Response $response) use ($route) {
      return $response->withJson($route);
    };
  }

  /**
   * @param string
   * @return string
   */
  private function getPath($name)
  {
    if (!$path = $this->routesNames[$name]['path']):
      throw new Exception('Route '.$name.' is not a valid route. Valid routes: '.implode(',', array_keys($this->routesNames)));
    endif;

    return sprintf($path, strtolower($this->resource->title));
  }

  /**
   * @param string
   * @return string
   */
  private function getMethod($name)
  {
    if (!$method = $this->routesNames[$name]['method']):
      throw new Exception('Route '.$name.' is not a valid route. Valid routes: '.implode(',', array_keys($this->routesNames)));
    endif;

    return $method;
  }

}
