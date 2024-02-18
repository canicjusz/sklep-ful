<?php
namespace Models;

use Core\QueryBuilder;

class Gallery {

  public static function homeBanners(){
    $banners = [];
    $result = QueryBuilder::select(['title', 'description', 'image_name', 'link', 'type', 'alt', 'mask'])
      ->from('banner')
      ->where('visible=true')
      ->andWhere("type='home_top'")
      ->limit(0, 5)
      ->execute();
    // "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    // WHERE visible=true AND type = 'home_top' LIMIT 0,5";
    // $result = Database::getInstance()->query($query);
    while($fetched = $result->fetch_assoc()){
      $banners[] = $fetched;
    }
    return $banners;
  }
  public static function homeTiles(){
    $tiles = [];
    $result = QueryBuilder::select(['title', 'description', 'image_name', 'link', 'type', 'alt', 'mask'])
      ->from('banner')
      ->where('visible=true')
      ->andWhere("type='home_tile'")
      ->execute();
    // $query = "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    // WHERE visible=true AND type = 'home_tile'";
    // $result = Database::getInstance()->query($query);
    while($fetched = $result->fetch_assoc()){
      $tiles[] = $fetched;
    }
    return $tiles;
  }
}
?>