<?php
namespace Models;

use Core\Database;

class Gallery {

  private static function homeBanners(){
    $banners = [];
    $query = "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    WHERE visible=true AND type = 'home_top' LIMIT 0,5";
    $result = Database::getInstance()->query($query);
    while($fetched = $result->fetch_assoc()){
      $banners[] = $fetched;
    }
    return $banners;
  }
  private static function homeTiles(){
    $tiles = [];
    $query = "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    WHERE visible=true AND type = 'home_tile'";
    $result = Database::getInstance()->query($query);
    while($fetched = $result->fetch_assoc()){
      $tiles[] = $fetched;
    }
    return $tiles;
  }

  public static function get(){
   $banners = static::homeBanners();
   $tiles = static::homeTiles();
  //  dwd($tiles);
   return ['home_tiles' => $tiles , 'home_top' => $banners];
  }
}
?>