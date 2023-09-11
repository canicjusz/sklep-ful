<?php
namespace Models;

use Core\Database;

class Parameters{
  private static function get(){
    $query = "SELECT value, parameter FROM parameter_value";
    $result = Database::getInstance()->query($query);
    $fetched = $result -> fetch_assoc();
    return $fetched;
  }
}