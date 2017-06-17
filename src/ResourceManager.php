<?php

namespace IbanDominguez\RestUp;

use PDO;
use stdClass;

class ResourceManager
{
  /**
   * @param string
   * @param array
   * @return IbanDominguez\RestUp\ResourceManager
   */
  public function __construct($resource, $relations)
  {
    $this->db = new PDO('mysql:host=localhost;dbname=prueba', 'root', '');
    $this->resource = $resource;
    $this->relations = $relations;
  }

  /**
   * Sets the db to be used
   *
   * @param PDO
   * @return IbanDominguez\RestUp\ResourceManager
   */
  public function setDB(PDO $db)
  {
    $this->db = $db;
    return $this;
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
      return (array) json_decode($item->row);
    }, $results) : [];
  }

  /**
   * Retrieves a resource with the specified id
   * fields and relations
   *
   * @param int
   * @return array
   */
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
      and resources.id = ?
    ");

    $query->execute([$this->resource, $id]);

    $results = $query->fetch(PDO::FETCH_OBJ);

    return $results ? (array) json_decode($results->row) : null;
  }

  /**
   * Creates a resource and its
   * fields and relations
   *
   * @param array
   * @return array
   */
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

  /**
   * Updates the specified resource and its
   * fields and relations
   *
   * @param int
   * @param array
   * @return array
   */
  public function update($id, $fields)
  {
    $this->db->prepare('delete from fields where resource_id = ?')->execute([$id]);

    foreach ($fields as $key => $value):
      if (strpos($key, '_id') !== false):
        $query = $this->db->prepare('
          select relations.a_id, relations.type
          from relations
          join resources on resources.id = relations.a_id
          where relations.type = "one2many"
          and resources.type = ?
          and relations.b_id = ?
        ');
        $query->execute([str_replace('_id', '', $key), $id]);
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

  /**
   * Removes the specified resource and its
   * fields and relations
   *
   * @param int
   * @return bool
   */
  public function delete($id)
  {
    $this->db->prepare('delete from fields where resource_id = ?')->execute([$id]);
    $this->db->prepare('
      delete resources from resources
      inner join relations on relations.b_id = resources.id
      where relations.a_id = ?
      and relations.type = "one2many"
    ')->execute([$id]);
    return $this->db->prepare('delete from relations where a_id = ? or b_id = ?')->execute([$id, $id]);
  }
}
