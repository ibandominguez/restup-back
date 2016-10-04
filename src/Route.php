<?php

namespace IbanDominguez\RestUp;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Route
{

  public function __construct($name, $path, $method, array $fields)
  {
    $this->name = $name;
    $this->path = $path;
    $this->method = $method;
    $this->fields = $fields;
  }

  public function makeClousure()
  {
    return function(Request $request, Response $response) {
      //
    };
  }

  public function makeMiddleware()
  {
    return function(Request $request, Response $response, callable $next) {
      //
    };
  }

}
