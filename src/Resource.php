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
   * @var array
   */
  public $fields = [];

  /**
   * @var array
   */
  public $routesNames = ['index', 'show', 'save', 'update', 'delete'];

  /**
   * @var array
   */
  public $types = ['string', 'number', 'date', 'datetime'];

  /**
   * @var string
   */
  public $groupsSeparator = '<[*^!$-groups-$!^]*>';

  /**
   * @var string
   */
  public $groupSeparator = '<[*^=$->group<-$=^]*>';

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
  public function __construct($title, array $fields, $options = [])
  {
    $this->title = $title;
    $this->fields = $fields;
    $this->options = $options;
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
   * @return Psr\Http\Message\ResponseInterface
   */
  public function index(Request $request, Response $response)
  {
    $query = $this->db->prepare("
      select * from resources
      join (
        select fields.resource_id, group_concat(fields.title, '$this->groupSeparator', fields.value SEPARATOR '$this->groupsSeparator') as data
        from fields
        group by fields.resource_id
      ) as fields on fields.resource_id = resources.id
      where resources.type = '$this->title'
    ");
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $parsedResults = [];

    foreach ($results as $result):
      $fieldGroups = explode($this->groupsSeparator, $result['data']);
      $fields = [];

      foreach ($fieldGroups as $group):
        $group = explode($this->groupSeparator, $group);
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
   * @return Psr\Http\Message\ResponseInterface
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
   * @return Psr\Http\Message\ResponseInterface
   */
  public function save(Request $request, Response $response)
  {
    $this->db->prepare("insert into resources (type) values ('$this->title')")->execute();
    $request = $request->withAttribute('id', $this->db->lastInsertId());
    $res = $this->updateFields($request);

    return $response->withJson($res, 200);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return Psr\Http\Message\ResponseInterface
   */
  public function update(Request $request, Response $response)
  {
    $id = $request->getAttribute('id');
    $request->withAttribute('id', $id);
    $this->db->prepare("update resources set updated_at = NOW() where id = ?")->execute([$id]);
    $this->db->prepare("delete from fields where resource_id = ?")->execute([$id]);
    $res = $this->updateFields($request);

    return $response->withJson($res, 201);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @param Psr\Http\Message\ResponseInterface
   * @return Psr\Http\Message\ResponseInterface
   */
  public function delete(Request $request, Response $response)
  {
    $id = $request->getAttribute('id');
    $this->db->prepare("delete from resources where id = ?")->execute([$id]);
    $this->db->prepare("delete from fields where resource_id = ?")->execute([$id]);

    return $response->withJson(null, 204);
  }

  /**
   * @param Psr\Http\Message\ServerRequestInterface
   * @return array
   */
  private function updateFields(Request $request)
  {
    $id = $request->getAttribute('id');
    $updateFields = ['id' => $id];

    foreach ($this->fields as $field):
      $key = $field['title'];
      $param = $request->getParam($key);

      if (!empty($param)):
        $this->db
          ->prepare("insert into fields (resource_id, type, title, value) values (?, ?, ?, ?)")
          ->execute([$id, $this->getFieldType($key), $key, $param]);

        $updateFields[$key] = $param;
      endif;
    endforeach;

    return $updateFields;
  }

  /**
   * @return array
   */
  public function getFieldRules()
  {
    $rules = [];

    foreach ($this->fields as $field):
      if (!empty($field['rules'])):
        $rules[$field['title']] = $field['rules'];
      endif;
    endforeach;

    return $rules;
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

    if (!in_array($field['type'], $this->types)):
      throw new Exception('Type must be one of the followings '.explode(',', $this->types));
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
    $exceptionRoutes = !empty($this->options['except']) ? $this->options['except'] : [];
    $protectedRoutes = !empty($this->options['protected']) ? $this->options['protected'] : [];

    foreach ($this->routesNames as $route):
      if (!in_array($route, $exceptionRoutes)):
        $this->routes[] = new Route($route, $this, in_array($route, $protectedRoutes));
      endif;
    endforeach;
  }

  /**
   * @param string
   * @return string
   */
  private function getFieldType($title)
  {
    $type = null;

    foreach ($this->fields as $field):
      if ($field['title'] == $title):
        $type = $field['type'];
      endif;
    endforeach;

    return $type;
  }

}
