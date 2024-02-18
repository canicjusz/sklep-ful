<?php

namespace Models;

use Core\QueryBuilder;

class CategorySelector
{
  public static function getCategories(int $current_id, int|null $parent_id)
  {
    $sign = $parent_id === null ? 'IS NULL' : '= ?';
    // if($parent_id == null){
    //   $sign = 'IS NULL';
    //   $arguments_arr = [];
    // }else{
    //   $sign = '= ?';
    //   $arguments_arr = [$parent_id];
    // }
    // $query = "SELECT ID, name, image_name, description, (ID = ?) as is_current from category WHERE parent $sign;";
    // $result= Database::getInstance()->execute_query($query, $arguments_arr);
    $result = QueryBuilder::select(['ID', 'name', 'image_name', 'description', 'is_current' => '(ID = ?)'], [$current_id])
      ->from('category')
      ->where("parent $sign", [$parent_id])
      ->execute();
    $categories = [];
    while ($category = $result->fetch_assoc()) {
      $categories[] = $category;
    }
    return $categories;
  }
  public static function getDescription($current_id)
  {
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
