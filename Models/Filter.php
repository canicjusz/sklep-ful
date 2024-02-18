<?php

namespace Models;

use Core\QueryBuilder;
use Models\Product;

class Filter
{
  public static function getManyForCategory($category, $manufacturer, $max_price, $min_price)
  {
    $products = Product::getManyIDs($category, $manufacturer, $max_price, $min_price);
    $result = QueryBuilder::select(['f.type', 'f.name', 'f_v.value', 'filter_ID' => 'f.ID', 'value_ID' => 'f_v.ID'])
      ->from(QueryBuilder::select(['DISTINCT filter_value_ID'])->from('product_filter_value')->optionalWhere('product_ID in ?', [$products]), 'p_f_v')
      ->join('filter_value', 'f_v', 'f_v.ID = p_f_v.filter_value_ID')
      ->join('filter', 'f', 'f.ID=f_v.ID_filter')
      ->groupBy('f_v.ID')->orderBy('f.ID')->execute();
    $filters = [];
    while ($featched = $result->fetch_assoc()) {
      $filter_id = $featched['filter_ID'];
      $value_id = $featched['value_ID'];
      $value_text = $featched['value'];
      if (!isset($filters[$filter_id])) {
        $filters[$filter_id] = [
          'name' => $featched['name'],
          'type' => $featched['type'],
          'values' => []
        ];
      }
      $filters[$filter_id]['values'][$value_id] = $value_text;
    }
    return $filters;
  }
  // public static function getManyForProducts($product_ids)
  // {
  //   $result = QueryBuilder::select(['product_ID' => 'p.ID', 'filter_ID' => 'f_v.ID_filter', 'values_array' => 'GROUP_CONCAT(DISTINCT CONCAT(f_v.value))'])
  //     ->from('filter_value', 'f_v')
  //     ->join('product_filter_value', 'p_f_v', 'p_f_v.filter_value_ID = f_v.ID')
  //     ->join('product', 'p', 'p_f_v.product_ID = p.ID')
  //     ->where('p.ID in ?', [$parent_category])
  //     ->execute();
  //   // $query = "SELECT p.ID as product_ID, f_v.ID_filter as filter_ID, GROUP_CONCAT(DISTINCT CONCAT(f_v.value)) as values_array FROM filter_value as f_v 
  //   //   JOIN product_filter_value as p_f_v ON p_f_v.filter_value_ID = f_v.ID
  //   //   JOIN product as p ON p_f_v.product_ID = p.ID
  //   //   WHERE p.ID in ?
  //   //   GROUP BY f_v.ID_filter, p.ID;";
  //   // $result = Database::getInstance()->execute_query($query, [$parent_category]);
  //   while ($fetched = $result->fetch_assoc()) {
  //     $fetched['ID'] = $categories . '/' . $fetched['ID'];
  //     $products[] = $fetched;
  //   }
  // }
}
