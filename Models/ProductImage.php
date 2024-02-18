<?php

namespace Models;

use Core\QueryBuilder;

class ProductImage
{
  public static function getHorizontalCarousel($id)
  {
    $horizontal_carousel_image = [];
    $result = QueryBuilder::select(['image_name'])
      ->from('product_image')
      ->where('product_ID=?', [$id])
      ->orderBy('main', 'desc')
      ->execute();

    while ($fetched = $result->fetch_assoc()) {
      $horizontal_carousel_image[] = $fetched;
    }
    return $horizontal_carousel_image;
  }

  public static function getVerticalCarousel($id)
  {
    $vertical_carousel_image = [];
    $result = QueryBuilder::select(['image_name'])
      ->from('product_image')
      ->where('product_ID=?', [$id])
      ->execute();

    while ($fetched = $result->fetch_assoc()) {
      $vertical_carousel_image[] = $fetched;
    }
    return $vertical_carousel_image;
  }
}
