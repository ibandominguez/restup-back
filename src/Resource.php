<?php

namespace IbanDominguez\RestUp;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use IbanDominguez\RestUp\Route;
use Exception;
use PDO;

class Resource
{

  /**
   * @var array
   */
  public $routes = [];

  /**
   * @var PDO
   */
  protected $db;

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
   * @return array
   */
  public function setDB(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return array
   */
  public function index(Request $request, Response $response)
  {
    $query = $this->db->prepare("
      select * from resources
      join (
        select fields.resource_id, group_concat(fields.title, '[:]', fields.value SEPARATOR '[|]') as data
        from fields
        group by fields.resource_id
      ) as fields on fields.resource_id = resources.id
      where resources.type = '$this->title'
    ");
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $parsedResults = [];

    foreach ($results as $result):
      $fieldGroups = explode('[|]', $result['data']);
      $fields = [];

      foreach ($fieldGroups as $group):
        $group = explode('[:]', $group);
        $fields[$group[0]] = $group[1];
      endforeach;

      $parsedResults[] = array_merge($fields, [
        'id' => $result['id'],
        'created_at' => $result['created_at'],
        'updated_at' => $result['updated_at']
      ]);
    endforeach;

    return $response->withJson($parsedResults, 200);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return stdClass
   */
  public function show(Request $request, Response $response)
  {
    $query = $this->db->prepare("
      select * from fields
      join resources on resources.id = fields.resource_id
      where resources.type = '$this->title' and fields.resource_id = ?
    ");
    $query->execute([$request->getAttribute('id')]);
    $results = $query->fetchAll();
    $parsedResponse = [];

    foreach ($results as $result):
      $parsedResponse['id'] = empty($parsedResponse['id']) ? $result['resource_id'] : $parsedResponse['id'];
      $parsedResponse['created_at'] = empty($parsedResponse['created_at']) ? $result['created_at'] : $parsedResponse['created_at'];
      $parsedResponse['updated_at'] = empty($parsedResponse['updated_at']) ? $result['updated_at'] : $parsedResponse['updated_at'];
      $parsedResponse[$result['title']] = $result['value'];
    endforeach;

    return $response->withJson($parsedResponse, 200);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return bool
   */
  public function save(Request $request, Response $response)
  {
    $this->db->prepare("insert into resources (type) values ('$this->title')")->execute();
    $resourceId = $this->db->lastInsertId();
    $res = ['id' => $resourceId];

    foreach ($this->fields as $field):
      $key = $field['title'];
      $param = $request->getParam($key);

      if (!empty($param)):
        $this->db
          ->prepare("insert into fields (resource_id, title, value) values (?, ?, ?)")
          ->execute([$resourceId, $key, $param]);

        $res[$key] = $param;
      endif;
    endforeach;

    return $response->withJson($res, 201);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return bool
   */
  public function update(Request $request, Response $response)
  {
    $query = $this->db->prepare("update resources set type = '$this->title' where id = ?");

    return $query->execute([$request->getAttribute('id')]);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return null
   */
  public function delete(Request $request, Response $response)
  {
    $this->db->prepare("delete from resources where id = ?")->execute([$request->getAttribute('id')]);
    $this->db->prepare("delete from fields where resource_id = ?")->execute([$request->getAttribute('id')]);

    return $response->withJson(null, 204);
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
    if (empty($field['title']) || empty($field['type'])):
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
    foreach (['index', 'show', 'save', 'update', 'delete'] as $route):
      $this->routes[] = new Route($route, $this);
    endforeach;
  }

}
