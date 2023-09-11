<?php
namespace Models;

use Core\Database;

class Banner {
  public static function get(){
    global $mysqli;
    $query = "SELECT title, description, image_name, link, visible, type, alt, mask from banner where type='store' and visible='1';";
    $result = Database::getInstance()->query($query);
    $fetched = $result->fetch_assoc();
    return $fetched;
  }
}