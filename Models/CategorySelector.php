<?php
namespace Models;

use Core\Database;

class CategorySelector {
  private static function getCategories($current_id, $parent_id){
    if($parent_id == null){
      $sign = 'IS NULL';
      $arguments_arr = [$current_id];
    }else{
      $sign = '= ?';
      $arguments_arr = [$current_id, $parent_id];
    }
    $query = "SELECT ID, name, image_name, description, (ID = ?) as is_current from category WHERE parent $sign;";
    dwd($query, $current_id, $parent_id);
    $result= Database::getInstance()->execute_query($query, $arguments_arr);
    $categories = [];
    while ($category = $result->fetch_assoc())
    {
      $categories[] = $category;
    }
    return $categories;
  }

  public static function get($current_id, $parent_id){
    $categories = static::getCategories($current_id, $parent_id);
    return $categories;
  }
}