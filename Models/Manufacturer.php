<?php

namespace Models;

use Core\QueryBuilder;

class Manufacturer
{
  public static function getMany($current_category, $manufacturer, $min_price, $max_price, $colors)
  {
    $manufacturers_array = [];
    $exists_promo_flag = QueryBuilder::exists('product_flag', 'p_f')
      ->join('flag', 'f', 'f.ID=p_f.flag_ID')
      ->where("f.name='promo'")
      ->andWhere('p_f.product_ID=p.ID')
      ->getQuery();

    $children_categories_array = Category::getChildrenIDs($current_category);

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

    while ($manufacturer = $result->fetch_assoc()) {
      $manufacturers_array[] = ['name' => $manufacturer['name'], 'products_amount' => $manufacturer['products_amount'], 'id' => $manufacturer['ID']];
    }
    return $manufacturers_array;
  }
}
