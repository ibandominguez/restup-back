<?php

namespace IbanDominguez\RestUp;

use PDO;
use stdClass;
use ICanBoogie\Inflector;

class ResourceManager
{
  public function __construct($resource, $relations)
  {
    $this->db = new PDO('mysql:host=localhost;dbname=prueba', 'root', '');
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->resource = $resource;
    $this->relations = $relations;
  }

  public function getDB() {
    return $this->db;
  }

  /**
   * Retrieves a list of the specified resource
   *
   * @return array
   */
  public function index()
  {
    $query = $this->db->prepare("
      select
        concat('{\"id\":', resources.id, ',', if(relations.data is null, '', relations.data), fields.data, ',\"created_at\":\"', resources.created_at, '\"}') as row
      from resources
      left join (
        select b_id, group_concat('\"', concat(resources.type, '_id'), '\":', a_id, ',') as data
        from relations
        join resources on resources.id = a_id
        where relations.type = 'one2many'
        group by b_id
      ) as relations on relations.b_id = resources.id
      join (
        select resource_id, group_concat('\"', title, '\":\"', value, '\"') as data
        from fields
        group by resource_id
      ) as fields on resources.id = fields.resource_id
      where resources.type = ?
    ");

    $query->execute([$this->resource]);

    $results = $query->fetchAll(PDO::FETCH_OBJ);

    return $results ? array_map(function($item) {
      return json_decode($item->row);
    }, $results) : [];
  }

  public function show($id)
  {
    $query = $this->db->prepare("
      select
        concat('{\"id\":', resources.id, ',', if(relations.data is null, '', relations.data), fields.data, ',\"created_at\":\"', resources.created_at, '\"}') as row
      from resources
      left join (
        select b_id, group_concat('\"', concat(resources.type, '_id'), '\":', a_id, ',') as data
        from relations
        join resources on resources.id = a_id
        where relations.type = 'one2many'
        group by b_id
      ) as relations on relations.b_id = resources.id
      join (
        select resource_id, group_concat('\"', title, '\":\"', value, '\"') as data
        from fields
        group by resource_id
      ) as fields on resources.id = fields.resource_id
      where resources.type = ?
      and resource.id = ?
    ");

    $query->execute([$this->resource, $id]);

    $results = $query->fetch(PDO::FETCH_OBJ);

    return $results ? json_decode($results->row) : null;
  }

  public function store($fields)
  {
    $this->db->prepare("insert into resources (type) values (?)")->execute([$this->resource]);
    $id = $this->db->lastInsertId();

    foreach ($fields as $key => $value):
      if (strpos($key, '_id') !== false):
        $this->db
          ->prepare("insert into relations (a_id, b_id, type) values (?, ?, ?)")
          ->execute([$value, $id, 'one2many']);
      else:
        $this->db
          ->prepare("insert into fields (resource_id, title, value) values (?, ?, ?)")
          ->execute([$id, $key, $value]);
      endif;
    endforeach;

    return array_merge([$id], $fields);
  }

  public function update($id, $fields)
  {
    $this->db->prepare('delete from fields where resource_id = ?')->execute([$id]);
    // $this->db->prepare('delete from relations where b_id = ? and type = "one2many"')->execute([$id]);

    foreach ($fields as $key => $value):
      if (strpos($key, '_id') !== false):
        $query = $this->db->prepare('
          select relations.a_id, relations.type
          from relations
          join resources on resources.id = relations.a_id
          where relations.type = "one2many"
          and resources.type = $key
          and relations.b_id = ?
        ');
        $query->execute([pluralize(str_replace('_id', '', $key)), $id]);
        $currentRelation = $query->fetch(PDO::FETCH_OBJ);

        $this->db
          ->prepare('update relations set a_id = ? where type = "one2many" and b_id = ?')
          ->execute([$value, $id]);
      else:
        $this->db
          ->prepare("insert into fields (resource_id, title, value) values (?, ?, ?)")
          ->execute([$id, $key, $value]);
      endif;
    endforeach;

    return array_merge([$id], $fields);
  }
}
