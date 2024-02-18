<?php

namespace Models;

use Core\QueryBuilder;

class Banner
{
  // public static function getBottomBanner(){
  //   // $query = "SELECT title, description, image_name, link, visible, type, alt, mask from banner where type='store' and visible='1';";
  //   // $result = Database::getInstance()->query($query);
  //   // $fetched = $result->fetch_assoc();
  //   $result = 
  //     QueryBuilder::select(['title', 'description', 'image_name', 'link', 'visible', 'type', 'alt', 'mask'])
  //       ->from('banner')
  //       ->where('type="store"')
  //       ->andWhere('visible=1')
  //       ->execute();
  //   $fetched = $result->fetch_assoc();
  //   return $fetched;
  // }
  public static function getCarouselBanners()
  {
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
    while ($fetched = $result->fetch_assoc()) {
      $banners[] = $fetched;
    }
    return $banners;
  }
  public static function getTiles()
  {
    $tiles = [];
    $result = QueryBuilder::select(['title', 'description', 'image_name', 'link', 'type', 'alt', 'mask'])
      ->from('banner')
      ->where('visible=true')
      ->andWhere("type='home_tile'")
      ->execute();
    // $query = "SELECT title, description, image_name, link, type, alt, mask FROM `banner` 
    // WHERE visible=true AND type = 'home_tile'";
    // $result = Database::getInstance()->query($query);
    while ($fetched = $result->fetch_assoc()) {
      $tiles[] = $fetched;
    }
    return $tiles;
  }
  public static function getBottomBanner()
  {
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
