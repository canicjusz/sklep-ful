<?php
namespace Models;

use Core\Database;

class CategoryDescription {
  public static function get($current_id){
    $query = "SELECT name, description from category WHERE ID = ?;";
    $result= Database::getInstance()->execute_query($query, [$current_id]);
    $fetched = $result->fetch_assoc();
    return ['category_description' => $fetched];
  }
}