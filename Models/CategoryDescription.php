<?php
namespace Models;

use Core\QueryBuilder;

class CategoryDescription {
  public static function getCategoryDescription($current_id){
    // $query = "SELECT name, description from category WHERE ID = ?;";
    // $result= Database::getInstance()->execute_query($query, [$current_id]);
    $result = 
      QueryBuilder::select(['name', 'description'])
        ->from('category')
        ->where('ID=?', [$current_id])
        ->execute();
    $fetched = $result->fetch_assoc();
    return $fetched;
  }
}