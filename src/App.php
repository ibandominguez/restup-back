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
   * @return void
   */
  public function __construct(array $config)
  {
    $this->config = $config;
    $this->slim = new Slim(['settings' => ['displayErrorDetails' => true]]);
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
  public function run($migrateDB)
  {
    $this->bootDatabase();
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
  private function bootDatabase()
  {
    $this->db = new PDO(
      'mysql:host='.$this->config['DB_HOST'].';dbname='.$this->config['DB_NAME'],
      $this->config['DB_USER'],
      $this->config['DB_PASS']
    );
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
      title VARCHAR(50) NOT NULL,
      value TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
    )');
  }

}
