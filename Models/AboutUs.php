<?php
namespace Models;

use Core\Database;

class AboutUs {
  public static function get(){
    $query = "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    WHERE visible=true AND type = 'home_bottom'";
    $result = Database::getInstance()->query($query);
    $fetched = $result->fetch_assoc();
    return ['about_us' => $fetched];
  }
}