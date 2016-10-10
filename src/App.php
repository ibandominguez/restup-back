<?php

namespace IbanDominguez\RestUp;

use Slim\App as Slim;
use Firebase\JWT\JWT;
use Exception;
use PDO;

class App
{
  /**
   * @var array
   */
  protected $config;

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
      new Slim(),
      $config
    );
  }

  /**
   * @return void
   */
  public function __construct(PDO $db, Slim $slim, array $config = [])
  {
    $this->db = $db;
    $this->slim = $slim;
    $this->config = $config;
    $this->setSlimDefaultConfigurations();
  }

  /**
   * @param string
   * @param array
   * @return IbanDominguez\RestUp\App
   */
  public function add($title, $fields, $options = [])
  {
    $this->resources[] = new Resource($title, $fields, $options);

    return $this;
  }

  /**
   * @param array
   * @return IbanDominguez\RestUp\App
   */
  public function auth($users = []) {
    $config = $this->config;

    $this->slim->post('/tokens', function($request, $response) use ($users, $config) {
      $email = $request->getParam('email');
      $password = $request->getParam('password');

      if (!empty($users[$email]) && $users[$email] == $password):
        return $response->withJson(['token' => JWT::encode([
          'iss' => 'http://example.org',
          'aud' => 'http://example.com',
          'iat' => 1356999524,
          'nbf' => 1357000000
        ], $config['JWT_KEY'])], 201);
      endif;

      return $response->withJson(['error' => 'invalid credentials'], 400);
    });

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
   * @return callable
   */
  public function getJWTMiddleware()
  {
    $key = $this->config['JWT_KEY'];

    return function($request, $response, $next) use ($key) {
      $token = $request->getHeaderLine('Authorization') ? str_replace('Bearer ', '', $request->getHeaderLine('Authorization')) : null;

      try {
        $decoded = JWT::decode($token, $key, ['HS256']);
      } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 401);
      }

      return $next($request, $response);
    };
  }

  /**
   * @return void
   */
  private function bindRoutes()
  {
    foreach ($this->resources as $resource):
      $resource->setDB($this->db);

      foreach ($resource->routes as $route):
        $referenceRoute = $this->slim->map([strtoupper($route->method)], $route->path, $route->makeClousure());
        ($route->protected) && $referenceRoute->add($this->getJWTMiddleware());
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

    $container['notAllowedHandler'] = function ($container) {
      return function ($request, $response, $exception) use ($container) {
        return $container['response']->withJson(['error' => 'Method not allowed'], 405);
      };
    };
  }

}
