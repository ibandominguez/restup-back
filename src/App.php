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
   * @var PDO
   */
  protected $db;

  /**
   * @var array
   */
  protected $resources = [];

  /**
   * @param array
   * @return IbanDominguez\RestUp\App
   */
  public static function create($config)
  {
    return new self(
      new PDO('mysql:host='.$config['DB_HOST'].';dbname='.$config['DB_NAME'], $config['DB_USER'], $config['DB_PASS']),
      new Slim(['settings' => ['displayErrorDetails' => !empty($config['M_DEBUG']) ? $config['M_DEBUG'] : false]])
    );
  }

  /**
   * @return void
   */
  public function __construct(PDO $db, Slim $slim)
  {
    $this->db = $db;
    $this->slim = $slim;
    $this->setSlimDefaultConfigurations();
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
  public function run($migrateDB = false)
  {
    ($migrateDB) && $this->migrateDB();
    $this->bindRoutes();
    $this->slim->run();
  }

  /**
   * @return void
   */
  private function bindRoutes()
  {
    foreach ($this->resources as $resource):
      $resource->setDB($this->db);

      foreach ($resource->routes as $route):
        $this->slim->map([strtoupper($route->method)], $route->path, $route->makeClousure());
      endforeach;
    endforeach;
  }

  /**
   * @return void
   */
  private function migrateDB()
  {
    $this->db->exec('CREATE TABLE IF NOT EXISTS resources(
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      type VARCHAR(50) NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
    )');

    $this->db->exec('CREATE TABLE IF NOT EXISTS fields(
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      resource_id INT(11),
      type VARCHAR(50) NOT NULL,
      title VARCHAR(50) NOT NULL,
      value TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
  }

  /**
   * @return void
   */
  private function setSlimDefaultConfigurations()
  {
    $container = $this->slim->getContainer();

    $container['notFoundHandler'] = function($container) {
      return function ($request, $response) use ($container) {
        return $container['response']->withJson(['error' => 'Not found'], 404);
      };
    };

    $container['errorHandler'] = function ($container) {
      return function ($request, $response, $exception) use ($container) {
        return $container['response']->withJson(['error' => 'Interval Server Error'], 500);
      };
    };
  }

}
