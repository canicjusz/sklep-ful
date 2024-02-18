<?php
namespace Models;

use Core\QueryBuilder;

class Parameters{
  public static function getParameters(){
    $result = QueryBuilder::select(['value', 'parameter'])
      ->from('parameter_value')
      ->execute(); 
    // "SELECT value, parameter FROM parameter_value";
    // $result = Database::getInstance()->query($query);
    $fetched = $result -> fetch_assoc();
    return $fetched;
  }
}