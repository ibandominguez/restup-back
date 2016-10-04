<?php

namespace IbanDominguez\RestUp;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Route
{

  /**
   * @param string
   * @param string
   * @param string
   * @param array
   * @return void
   */
  public function __construct($name, $path, $method, array $fields)
  {
    $this->name = $name;
    $this->path = $path;
    $this->method = $method;
    $this->fields = $fields;
  }

  /**
   * @return callable
   */
  public function makeClousure()
  {
    $self = $this;

    return function(Request $request, Response $response) use ($self) {
      return $response->withJson($self);
    };
  }

}
