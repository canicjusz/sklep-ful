<?php
namespace Models;

use Core\QueryBuilder;

class Offer{
  public static function horizontalCarouselImage($id){
    $horizontal_carousel_image = [];
    $result = QueryBuilder::select(['image_name'])
      ->from('product_image')
      ->where('product_ID=?', [$id])
      ->orderBy('main', 'desc')
      ->execute();
    // "SELECT image_name FROM product_image WHERE product_ID= ? ORDER BY main desc";
    // $result = Database::getInstance()->execute_query($query, [$id]);
    while ($fetched = $result->fetch_assoc())
      {
        $horizontal_carousel_image[] = $fetched;
      }
    return $horizontal_carousel_image;
	}

	
	public static function verticalCarouselImage($id){
	$vertical_carousel_image = [];
	$result = QueryBuilder::select(['image_name'])
    ->from('product_image')
    ->where('product_ID=?', [$id])
    ->execute();
  // "SELECT image_name FROM product_image WHERE product_ID= ?";
	// $result = Database::getInstance()->execute_query($query, [$id]);
	while ($fetched = $result->fetch_assoc())
    {
      $vertical_carousel_image[] = $fetched;
    }
	return $vertical_carousel_image;
	}

  public static function productOffer($id){
    $query = "SELECT p.ID, p.name, p.variant_name, p.catalog_price, 
    p.promo_price, d.name as delivery_name, p.serial_number as serial_number, p.variant_group_ID,
    GROUP_CONCAT(DISTINCT f.name) as flag_names, m.name as manufacturer_name, m.image_name as manufacturer_image FROM product as p
      JOIN delivery as d ON d.ID = p.delivery_ID
      JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
      JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
      LEFT JOIN product_flag as p_f ON p_f.product_ID = p.ID
      LEFT JOIN flag as f ON p_f.flag_ID = f.ID
        WHERE p.visible = true AND p.stock > 0 AND p.ID = ? GROUP BY p.ID;";
    $result = Database::getInstance()->execute_query($query, [$id]);
    $fetched = $result->fetch_assoc();
    return $fetched;
  }
}