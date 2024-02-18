<?php

namespace Models;

use Core\QueryBuilder;

class Sidebar
{
  //zajebane ze stacka https://stackoverflow.com/a/10332361
  public static function createTree(&$list, $parent)
  {
    $tree = [];
    foreach ($parent as $k => $l) {
      if (isset($list[$l['ID']])) {
        $l['children'] = static::createTree($list, $list[$l['ID']]);
      }
      $tree[] = $l;
    }
    return $tree;
  }

  public static function mainCategoryName($category)
  {
    if ($category == null) {
      return 'Wybierz kategorię';
    }
    $result = QueryBuilder::select(['name'])->from('category')->where('ID=?', [$category])->execute();
    // $query = "SELECT name from category where ID = ?;";
    // $result= Database::getInstance()->execute_query($query, [$category]);
    $featched = $result->fetch_assoc();
    return $featched['name'];
  }

  public static function getCategories($attribute_categories)
  {
    //     na górze ma być nadrzędna kategoria (główna)
    // potem ma być lista kategorii
    // mogą być one rozwijane i tam będą kolejne podkategorie, ale one nawet jak mają dzieci to są nie rozwijane
    if (count($attribute_categories) < 3) {
      $attribute_categories = [null, ...$attribute_categories];
    }

    dwd('atrybuty', $attribute_categories);
    reset($attribute_categories);
    $remove_top_category_level = false;
    $create_category_tree = function (&$parent_category) use (&$attribute_categories, &$create_category_tree, &$remove_top_category_level) {
      $category = current($attribute_categories);
      if ($category == null) {
        $query_part = "is NULL";
        $query_arguments = [];
      } else {
        $query_part = "= ?";
        $query_arguments = [$category];
      }
      // $attribute_categories
      $parent_category['name'] = static::mainCategoryName($category);

      $do_children_exists = QueryBuilder::exists('category', 'c1')->where('c1.parent = c.ID')->getQuery();
      $query = QueryBuilder::select([
        'c.ID', 'c.name', 'c.parent', 'c.level',
        'has_children' => $do_children_exists
      ])->from('category', 'c')->where("c.parent $query_part", $query_arguments);
      $result = $query->execute();

      $parent_category['children'] = [];

      while ($featched = $result->fetch_assoc()) {
        $child_category_id = $featched['ID'];
        $is_the_category_active = in_array($child_category_id, $attribute_categories);
        $parent_category['children'][$child_category_id] = $featched;
        $parent_category['children'][$child_category_id]['is_active'] = $is_the_category_active;
      }
      $next_category = next($attribute_categories);
      $are_there_subcategories = count($parent_category['children']) !== 0;
      $is_node_too_high = count($attribute_categories) > 2;
      if ($next_category) {
        $create_category_tree($parent_category['children'][$next_category]);
      } else if ($are_there_subcategories && $is_node_too_high) {
        $remove_top_category_level = true;
      }
    };
    $category_tree = [];
    $create_category_tree($category_tree, $attribute_categories);
    dwd($category_tree);
    if ($remove_top_category_level) {
      $columns = array_column($category_tree['children'], 'is_active', 'ID');
      $index_to_detatch = array_search(true, $columns);
      $category_tree = $category_tree['children'][$index_to_detatch];
    }
    return $category_tree;
  }
  public static function getManufacturers($current_category, $manufacturer, $min_price, $max_price, $colors)
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
  // "SELECT filter.name, filter.type, filter_value.value, filter_value.id as value_ID FROM filter JOIN filter_value
  // ON filter_value.ID_filter = filter.ID"

  // (SELECT p.ID as product_ID
  //       FROM product as p
  //         JOIN 
  //           (SELECT product_ID, category_ID FROM product_category WHERE product_category.category_ID IN (1,2,3) ) as p_c ON p_c.product_ID = p.ID
  //         JOIN 
  //           (SELECT name, ID FROM manufacturer) as m ON m.ID = p.manufacturer_ID
  //         JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
  //         JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
  //         JOIN filter as f ON f.ID = f_v.ID_filter
  //           WHERE p.visible = true AND p.stock > 0
  //           GROUP BY p.ID);

  // private static function getPriceRange($category, $manufacturer){
  //   $children_categories_array = ProductDisplay::childrenCategories($category);
  //   $query = "SELECT GROUP_CONCAT(p.ID) as array
  //   FROM product as p
  //   JOIN
  // (SELECT product_ID, category_ID FROM product_category".Database::optional("WHERE product_category.category_ID IN ?", $children_categories_array).") as p_c ON p_c.product_ID = p.ID
  //           JOIN category as c ON c.ID = p_c.category_ID
  //           JOIN 
  //             (SELECT ID FROM manufacturer".Database::optional("WHERE manufacturer.ID = ?", $manufacturer).") as m ON m.ID = p.manufacturer_ID
  //           WHERE p.visible = true AND p.stock > 0
  //           )
  //   LEFT JOIN product_flag as p_f on p_f.product_ID = sq.ID
  //   JOIN flag as fl on p_f.flag_ID = fl.ID GROUP BY sq.ID) as xD"
  // }

  private static function getFilterableProductIds($category, $manufacturer, $max_price, $min_price)
  {
    $children_categories_array = ProductDisplay::childrenCategories($category);
    $exists_promo_flag = QueryBuilder::exists('product_flag', 'p_f')
      ->join('flag', 'f', 'f.ID=p_f.flag_ID')
      ->where("f.name='promo'")
      ->andWhere('p_f.product_ID=p.ID')
      ->getQuery();
    $result = QueryBuilder::select(['array' => 'GROUP_CONCAT(p.ID)'])
      ->from('product', 'p')
      ->join(QueryBuilder::select(['product_ID', 'category_ID'])->from('product_category')->optionalWhere('product_category.category_ID IN ?', [$children_categories_array]), 'p_c', 'p_c.product_ID = p.ID')
      ->join('category', 'c', 'c.ID=p_c.category_ID')
      ->join(QueryBuilder::select(['ID'])->from('manufacturer')->optionalWhere('manufacturer.ID=?', [$manufacturer]), 'm', 'm.ID=p.manufacturer_ID')
      ->andOptionalWhere("IF($exists_promo_flag, promo_price, catalog_price) BETWEEN ? AND ?", [$min_price, $max_price])->execute();
    //     $query = "SELECT GROUP_CONCAT(p.ID) as array
    //   FROM product as p
    //   JOIN
    // (SELECT product_ID, category_ID FROM product_category" . Database::optional("WHERE product_category.category_ID IN ?", $children_categories_array) . ") as p_c ON p_c.product_ID = p.ID
    //           JOIN category as c ON c.ID = p_c.category_ID
    //           JOIN 
    //             (SELECT ID FROM manufacturer" . Database::optional("WHERE manufacturer.ID = ?", $manufacturer) . ") as m ON m.ID = p.manufacturer_ID
    //           WHERE p.visible = true AND p.stock > 0"
    //       . Database::optional("AND IF(EXISTS (SELECT * FROM product_flag as p_f JOIN flag as f ON f.ID=p_f.flag_ID WHERE f.name='promo' AND p_f.product_ID=p.ID), promo_price, catalog_price) BETWEEN ? AND ?", $min_price, $max_price) . ";";
    // $result = Database::getInstance()->query($query);
    $fetched = $result->fetch_assoc();
    return isset($fetched['array']) && strlen($fetched['array']) ? '(' . $fetched['array'] . ')' : null;
  }

  public static function getFilters($category, $manufacturer, $max_price, $min_price)
  {
    $products = static::getFilterableProductIds($category, $manufacturer, $max_price, $min_price);
    $result = QueryBuilder::select(['f.type', 'f.name', 'f_v.value', 'filter_ID' => 'f.ID', 'value_ID' => 'f_v.ID'])
      ->from(QueryBuilder::select(['DISTINCT filter_value_ID'])->from('product_filter_value')->optionalWhere('product_ID in ?', [$products]), 'p_f_v')
      ->join('filter_value', 'f_v', 'f_v.ID = p_f_v.filter_value_ID')
      ->join('filter', 'f', 'f.ID=f_v.ID_filter')
      ->groupBy('f_v.ID')->orderBy('f.ID')->execute();
    //$query = "SELECT f.type, f.name, f.ID as filter_ID, f_v.value, f_v.ID as value_ID
    //      FROM (SELECT DISTINCT filter_value_ID FROM product_filter_value" . Database::optional("WHERE product_ID in ?", $products) . ") as p_f_v
    //      JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //      JOIN filter as f ON f.ID = f_v.ID_filter GROUP BY f_v.ID ORDER BY f.ID;";
    #    $result = Database::getInstance()->query($query);
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

  // public static function getColors($query_colors){
  //   $query = "SELECT filter.name, filter.type, filter_value.value, filter_value.id as value_ID"
  //    .Database::optional(", (filter_value.id in (?)) as is_selected", $query_colors)." FROM filter JOIN filter_value
  //     ON filter_value.ID_filter = filter.ID WHERE type = 'color';";
  //     dwd($query);
  //   $result = Database::getInstance()->query($query);
  //   $colors = [];
  //   while ($featched = $result->fetch_assoc())
  //   {
  //     $colors[] = $featched;
  //   }
  //   return $colors;
  // }

  // private static function manufacturers(){
  //   $query = "SELECT ID, name FROM manufacturer";
  //   $result = Database::getInstance()->execute_query($query);
  //   $manufacturers = [];
  //   while ($featched = $result->fetch_assoc())
  //   {
  //     $manufacturers[] = $featched;
  //   }
  //   return $manufacturers;
  // }

  // public static function get($categories, $manufacturer, $colors, $min_price, $max_price){
  //   $categories_count = count($categories);
  //   $last_category = $categories[$categories_count-1];
  //   $manufacturers = static::manufacturers($manufacturer, $colors, $last_category, $min_price, $max_price);
  //   dwd($manufacturers);
  //   $category_tree = static::categories($categories);
  //   $colors = static::colors($colors);
  //   return ['colors' => $colors, 'manufacturers' => $manufacturers, 'categories'=>$category_tree];
  // }
}
