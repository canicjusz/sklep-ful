<?php

namespace Models;

use Core\QueryBuilder;

class ProductImage
{
  public static function getManyForHorizontalCarousel($id)
  {
    $horizontal_carousel_image = [];
    $result = QueryBuilder::select(['image_name'])
      ->from('product_image')
      ->where('product_ID=?', [$id])
      ->orderBy('main', 'desc')
      ->execute();
    // "SELECT image_name FROM product_image WHERE product_ID= ? ORDER BY main desc";
    // $result = Database::getInstance()->execute_query($query, [$id]);
    while ($fetched = $result->fetch_assoc()) {
      $horizontal_carousel_image[] = $fetched;
    }
    return $horizontal_carousel_image;
  }
  public static function getManyForVerticalCarousel($id)
  {
    $vertical_carousel_image = [];
    $result = QueryBuilder::select(['image_name'])
      ->from('product_image')
      ->where('product_ID=?', [$id])
      ->execute();
    // "SELECT image_name FROM product_image WHERE product_ID= ?";
    // $result = Database::getInstance()->execute_query($query, [$id]);
    while ($fetched = $result->fetch_assoc()) {
      $vertical_carousel_image[] = $fetched;
    }
    return $vertical_carousel_image;
  }
}
