<?php
namespace Models;

use Core\QueryBuilder;

class AboutUs {
  public static function getCompanyInfo(){
    // $query = "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    // WHERE visible=true AND type = 'home_bottom'";
    // $result = Database::getInstance()->query($query);
    $result = 
      QueryBuilder::select(['title', 'description', 'image_name', 'link', 'type', 'alt', 'mask'])
        ->from('banner')
        ->where('visible=true')
        ->andWhere("type='home_bottom'")
        ->execute();
    $fetched = $result->fetch_assoc();
    dwd($fetched);
    return $fetched;
  }
}