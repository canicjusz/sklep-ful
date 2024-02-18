<?php

namespace Models;

use Core\QueryBuilder;

class Banner
{
  public static function getCatalogBanner()
  {
    $result =
      QueryBuilder::select(['title', 'description', 'image_name', 'link', 'visible', 'type', 'alt', 'mask'])
      ->from('banner')
      ->where('type="store"')
      ->andWhere('visible=1')
      ->execute();
    $fetched = $result->fetch_assoc();
    return $fetched;
  }

  public static function getCarouselBanners()
  {
    $banners = [];
    $result = QueryBuilder::select(['title', 'description', 'image_name', 'link', 'type', 'alt', 'mask'])
      ->from('banner')
      ->where('visible=true')
      ->andWhere("type='home_top'")
      ->limit(0, 5)
      ->execute();

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

    while ($fetched = $result->fetch_assoc()) {
      $tiles[] = $fetched;
    }
    return $tiles;
  }

  public static function getBottomBanner()
  {
    $result =
      QueryBuilder::select(['title', 'description', 'image_name', 'link', 'type', 'alt', 'mask'])
      ->from('banner')
      ->where('visible=true')
      ->andWhere("type='home_bottom'")
      ->execute();
    $fetched = $result->fetch_assoc();
    return $fetched;
  }
}
