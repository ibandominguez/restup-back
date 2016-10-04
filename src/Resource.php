<?php

namespace IbanDominguez\RestUp;

use IbanDominguez\RestUp\Route;
use Exception;

class Resource
{

  /**
   * @var array
   */
  public $routes = [];

  /**
   * @param string
   * @param array
   * @param array
   * @return void
   */
  public function __construct($title, array $fields)
  {
    $this->title = $title;
    $this->fields = $fields;
    $this->validateFields();
    $this->handleRoutes();
  }

  /**
   * @return array
   */
  public function getRoutes()
  {
    return $this->routes;
  }

  /**
   * @return void
   */
  private function validateFields()
  {
     foreach ($this->fields as $field):
       $this->validateField($field);
     endforeach;
  }

  /**
   * @return void
   */
  private function validateField($field)
  {
    if (empty($field['key']) || empty($field['type'])):
      throw new Exception('Title and type keys are required');
    endif;

    foreach ($field as $key):
      if (empty($key) || !is_string($key)):
        throw new Exception('Keys are required and must be strings');
      endif;
    endforeach;
  }

  /**
   * @return void
   */
  private function handleRoutes()
  {
    $this->routes = [
      new Route('index', '/'.$this->title, 'GET', $this->fields),
      new Route('show', '/'.$this->title.'/{id}', 'GET', $this->fields),
      new Route('store', '/'.$this->title, 'POST', $this->fields),
      new Route('update', '/'.$this->title.'/{id}', 'PUT', $this->fields),
      new Route('delete', '/'.$this->title.'/{id}', 'DELETE', $this->fields)
    ];
  }

}
