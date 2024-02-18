<?php

namespace Models;

use Core\QueryBuilder;

class Manufacturer
{
  public static function getMany($current_category, $manufacturer, $min_price, $max_price, $colors)
  {
    $manufacturers_array = [];
    // dwd($current_category, $manufacturer, $min_price, $max_price, $colors);
    $exists_promo_flag = QueryBuilder::exists('product_flag', 'p_f')
      ->join('flag', 'f', 'f.ID=p_f.flag_ID')
      ->where("f.name='promo'")
      ->andWhere('p_f.product_ID=p.ID')
      ->getQuery();

    $children_categories_array = ProductDisplay::childrenCategories($current_category);

    $sub_query = QueryBuilder::select(['product_ID' => 'p.ID', 'manufacturer_name' => 'm.name', 'manufacturer_ID' => 'm.ID'])
      ->from('product', 'p')
      ->join(QueryBuilder::select(['product_ID', 'category_ID'])->from('product_category')->optionalWhere('product_category.category_ID IN', [$children_categories_array]), 'p_c', 'p_c.product_ID = p.ID')
      ->join('category', 'c', 'c.ID=p_c.category_ID')
      ->join(QueryBuilder::select(['id', 'name'])->from('manufacturer')->optionalWhere('manufacturer.ID = ?', [$manufacturer]), 'm', 'm.ID = p.manufacturer_ID')
      ->join('product_filter_value', 'p_f_v', 'p_f_v.product_ID = p.ID')
      ->join('filter_value', 'f_v', 'f_v.ID = p_f_v.filter_value_ID')
      ->join('filter', 'f', 'f.ID=f_v.ID_filter')
      ->where('p.visible = true')
      ->andWhere('p.stock > 0')
      ->andOptionalWhere("IF($exists_promo_flag, promo_price, catalog_price) BETWEEN ? AND ?", [$min_price, $max_price])
      ->andOptionalWhere('f.type="color" AND f_v.ID IN (?)', [$colors]);

    $result =
      QueryBuilder::select(['ID' => 'manufacturer_ID', 'name' => 'manufacturer_name', 'products_amount' => 'COUNT(product_ID)'])
      ->from($sub_query, 'sq')->groupBy('manufacturer_ID')
      ->execute();

    // $query = "SELECT manufacturer_ID as ID, manufacturer_name as name, COUNT(product_ID) as products_amount FROM 
    // (SELECT p.ID as product_ID, m.name as manufacturer_name, m.ID as manufacturer_ID
    //     FROM product as p
    //       JOIN 
    //         (SELECT product_ID, category_ID FROM product_category"
    //   . Database::optional("WHERE product_category.category_ID IN ?", $children_categories_array) . ") as p_c ON p_c.product_ID = p.ID
    //       JOIN category as c ON c.ID = p_c.category_ID
    //       JOIN 
    //         (SELECT name, ID FROM manufacturer" . Database::optional("WHERE manufacturer.ID = ?", $manufacturer) . ") as m ON m.ID = p.manufacturer_ID
    //       JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
    //       JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //       JOIN filter as f ON f.ID = f_v.ID_filter
    //         WHERE p.visible = true AND p.stock > 0
    //         " . Database::optional("AND IF(EXISTS (SELECT * FROM product_flag as p_f JOIN flag as f ON f.ID=p_f.flag_ID WHERE f.name='promo' AND p_f.product_ID=p.ID), promo_price, catalog_price) BETWEEN ? AND ?", $min_price, $max_price)
    //   . Database::optional("AND f.type = 'color' AND f_v.ID IN (?)", $colors) .
    //   "GROUP BY p.ID) as sq GROUP BY manufacturer_ID;";
    // dwd($query);
    // $result = Database::getInstance()->query($query);
    while ($manufacturer = $result->fetch_assoc()) {
      $manufacturers_array[] = ['name' => $manufacturer['name'], 'products_amount' => $manufacturer['products_amount'], 'id' => $manufacturer['ID']];
    }
    return $manufacturers_array;

    // select manufacturer_ID, count(*) from (select m.ID as manufacturer_ID, p.ID as product_ID
    // FROM product as p
    //   JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
    //   JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
    //     WHERE
    //     p.visible = true AND p.stock > 0 GROUP BY p.ID) as x GROUP BY manufacturer_ID;

    // (SELECT p.ID as product_ID
    // FROM product as p
    //   JOIN 
    //     (SELECT product_ID, category_ID FROM product_category WHERE product_category.category_ID IN (1,2,3) ) as p_c ON p_c.product_ID = p.ID
    //   JOIN 
    //     (SELECT name, ID FROM manufacturer) as m ON m.ID = p.manufacturer_ID
    //     WHERE p.visible = true AND p.stock > 0
    //     GROUP BY p.ID)
  }
}
