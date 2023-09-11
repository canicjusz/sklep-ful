<?php
namespace Models;

use Core\Database;

class RedirectCategory {
  public static function getParentCategories($category){
    $query = "with recursive cte (id, name, parent) as (select id, name, parent from category
    where id = ? union all select c.id, c.name, c.parent from category c 
    inner join cte on c.id = cte.parent)
SELECT id, name, parent FROM cte";
    $result = Database::getInstance()->execute_query($query, [$category]);
    $categories_ids = [];
    while($fetched = $result->fetch_assoc()){
      $categories_ids[] = $fetched['id'];
    }
    // dwd($categories_ids);
    // $fetched = $result->fetch_assoc();
    return array_reverse($categories_ids);
  }
}