<?php
namespace Models;

use Core\QueryBuilder;

class Featured {
  public static function getFeaturedProducts(){
    $featured_array = [];
    $result = 
      QueryBuilder::select(['p.ID', 'p.name', 'p.promo_price', 'p.catalog_price', 'p.serial_number', 'p.stock', 
        'flag_names' => 'GROUP_CONCAT(DISTINCT f.name)', 
        'image_name' => QueryBuilder::select(['p_i.image_name'])
          ->from('product_image', 'p_i')
          ->where('p_i.product_ID = p.ID')
          ->orderBy('p_i.main', 'DESC')
          ->limit(1)
      ])
      ->from('product', 'p')
      ->join('product_flag', 'p_f', 'p_f.product_ID = p.ID')
      ->join('flag', 'f', 'p_f.flag_ID = f.ID')
      ->where('p.visible = true')
      ->andWhere('p.stock > 0')
      ->groupBy('p.ID')
      ->having("flag_names LIKE '%featured%'")
      ->limit(0, 25)
      ->execute();
    // $query = "SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    // GROUP_CONCAT(DISTINCT f.name) as flag_names,
    // (select p_i.image_name from product_image as p_i 
    //   where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name 
    // FROM product as p 
    //   JOIN product_flag as p_f on p_f.product_ID = p.ID JOIN flag as f on p_f.flag_ID = f.ID 
    //     WHERE p.visible = true AND p.stock > 0 GROUP BY p.ID HAVING flag_names LIKE '%featured%' LIMIT 0,25";
    // $result = Database::getInstance()->query($query);
    while($fetched = $result->fetch_assoc()){
      $featured_array[] = $fetched;
    }
    return $featured_array;
  }
}