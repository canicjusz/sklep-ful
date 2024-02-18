<?php

namespace Models;

use Core\QueryBuilder;

class ProductDisplay
{
  // function __construct(){
  //   global $mysqli;
  //   $mysqli = $mysqli;
  //   $products_per_page = $GLOBALS['query_pp'] ?? 5;
  //   $page = $GLOBALS['query_p'] ?? 1;
  //   $offset = ($page - 1) * $products_per_page;
  //   $boundary = $page * $products_per_page;
  //   $category = $GLOBALS['catalog_id'] ?? '';
  //   $manufacturer = $GLOBALS['query_m'] ?? '';
  //   $colors = $GLOBALS['query_c'] ?? '';
  //   $min_price = $GLOBALS['query_min'] ?? '';
  //   $max_price = $GLOBALS['query_max'] ?? '';
  //   $display_variation = $GLOBALS['query_d'] ?? 'grid';
  //   $order_by = match($GLOBALS['query_o'] ?? ''){
  //     'price_asc' => 'curr_price ASC',
  //     'price_desc' => 'curr_price DESC',
  //     'name_desc' => 'name DESC',
  //     default => 'name ASC'
  //   };
  // }

  public static function childrenCategories($parent_category)
  {
    $result =
      QueryBuilder::with('cte')
      ->following(QueryBuilder::select(['array' => 'GROUP_CONCAT(id)'])->from('cte'))
      ->recursive(
        QueryBuilder::select(['id', 'name', 'parent'])->from('category')->where('id=?', [$parent_category]),
        QueryBuilder::select(['c.id', 'c.name', 'c.parent'])->from('category', 'c')->join('cte', '', 'c.parent = cte.id')
      )
      ->execute();

    // ['array'=>'GROUP_CONCAT(id)'])
    //   ->from(
    //     QueryBuilder::with('cte')
    //       ->recursive(
    //         QueryBuilder::select(['id', 'name', 'parent'])->from('category')->where('id=?',[$parent_category]),
    //         QueryBuilder::select(['c.id', 'c.name', 'c.parent'])->from('category', 'c')->join('cte', '', 'c.parent = cte.id')->where('id=?',[$parent_category])
    //         )
    //     )
    // "with recursive cte (id, name, parent) as (select id, name, parent from category
    // where id = ? union all select c.id, c.name, c.parent from category c 
    // inner join cte on c.parent = cte.id) select GROUP_CONCAT(id) as array from cte;";
    // $result = Database::getInstance()->execute_query($query, [$parent_category]);
    $fetched = $result->fetch_assoc();
    return '(' . $fetched['array'] . ')';
  }

  public static function get_filter_values($product_ids)
  {
    $result = QueryBuilder::select(['product_ID' => 'p.ID', 'filter_ID' => 'f_v.ID_filter', 'values_array' => 'GROUP_CONCAT(DISTINCT CONCAT(f_v.value))'])
      ->from('filter_value', 'f_v')
      ->join('product_filter_value', 'p_f_v', 'p_f_v.filter_value_ID = f_v.ID')
      ->join('product', 'p', 'p_f_v.product_ID = p.ID')
      ->where('p.ID in ?', [$parent_category])
      ->execute();
    // $query = "SELECT p.ID as product_ID, f_v.ID_filter as filter_ID, GROUP_CONCAT(DISTINCT CONCAT(f_v.value)) as values_array FROM filter_value as f_v 
    //   JOIN product_filter_value as p_f_v ON p_f_v.filter_value_ID = f_v.ID
    //   JOIN product as p ON p_f_v.product_ID = p.ID
    //   WHERE p.ID in ?
    //   GROUP BY f_v.ID_filter, p.ID;";
    // $result = Database::getInstance()->execute_query($query, [$parent_category]);
    while ($fetched = $result->fetch_assoc()) {
      $fetched['ID'] = $categories . '/' . $fetched['ID'];
      $products[] = $fetched;
    }
  }

  public static function getProducts($category, $categories_joined, $manufacturer, $min_price, $max_price, $colors, $order_by, $offset, $amount)
  {
    $order_by_transcribed = match ($order_by) {
      'price_asc' => 'curr_price ASC',
      'price_desc' => 'curr_price DESC',
      'name_desc' => 'name DESC',
      default => 'name ASC'
    };
    $products = [];
    $children_categories_array = static::childrenCategories($category);
    // $get_all_imgs

    $exists_promo_flag = QueryBuilder::exists('product_flag', 'p_f')
      ->join('flag', 'f', 'f.ID=p_f.flag_ID')
      ->where("f.name='promo'")
      ->andWhere('p_f.product_ID=p.ID')
      ->getQuery();

    $sub_query =
      QueryBuilder::select([
        'p.ID', 'p.manufacturer_ID', 'p.name', 'p.promo_price', 'p.catalog_price', 'p.serial_number',
        'p.stock', 'category' => 'c.id'
      ])
      ->from('product', 'p')
      ->join(QueryBuilder::select(['product_ID', 'category_ID'])->from('product_category')->optionalWhere('product_category.category_ID IN', [$children_categories_array]), 'p_c', 'p_c.product_ID = p.ID')
      ->join(QueryBuilder::select(['id'])->from('manufacturer')->optionalWhere('manufacturer.ID = ?', [$manufacturer]), 'm', 'm.ID = p.manufacturer_ID')
      ->join('category', 'c', 'c.ID=p_c.category_ID')
      ->join('product_filter_value', 'p_f_v', 'p_f_v.product_ID = p.ID')
      ->join('filter_value', 'f_v', 'f_v.ID = p_f_v.filter_value_ID')
      ->join('filter', 'f', 'f.ID=f_v.ID_filter')
      ->where('p.visible = true')
      ->andWhere('p.stock > 0')
      ->andOptionalWhere("IF($exists_promo_flag, promo_price, catalog_price) BETWEEN ? AND ?", [$min_price, $max_price])
      ->andOptionalWhere('f.type="color" AND f_v.ID IN (?)', [$colors]);
    // ->optionalBetween($min_price, $max_price)

    $result =
      QueryBuilder::select([
        'sq.ID', 'sq.name', 'sq.promo_price', 'sq.catalog_price', 'sq.serial_number', 'sq.stock',
        'curr_price' => "IF(GROUP_CONCAT(DISTINCT fl.name) LIKE '%promo%', promo_price, catalog_price)",
        'image_name' => QueryBuilder::select(['p_i.image_name'])
          ->from('product_image', 'p_i')
          ->where('p_i.product_ID = sq.ID')
          ->orderBy('p_i.main', 'DESC')
          ->limit(1)
      ])
      ->from($sub_query, 'sq')
      ->leftJoin('product_flag', 'p_f', 'p_f.product_ID = sq.ID')
      ->join('flag', 'fl', 'p_f.flag_ID = fl.ID')
      ->groupBy('sq.ID')
      ->orderBy($order_by_transcribed)
      ->limit($offset, $amount)
      ->execute();

    dwd('hejka');
    // $query = "
    // SELECT sq.ID, sq.name, sq.promo_price, sq.catalog_price, sq.serial_number, sq.stock,
    //   IF(GROUP_CONCAT(DISTINCT fl.name) LIKE '%promo%', promo_price, catalog_price) as curr_price,
    //   (select p_i.image_name from product_image as p_i where p_i.product_ID = sq.ID ORDER BY p_i.main DESC LIMIT 1) as image_name
    //     FROM (SELECT p.ID, p.manufacturer_ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock, c.id as category
    //       FROM product as p
    //         JOIN 
    //           (SELECT product_ID, category_ID FROM product_category" . Database::optional("WHERE product_category.category_ID IN ?", $children_categories_array) . ") as p_c ON p_c.product_ID = p.ID
    //         JOIN category as c ON c.ID = p_c.category_ID
    //         JOIN 
    //           (SELECT ID FROM manufacturer" . Database::optional("WHERE manufacturer.ID = ?", $manufacturer) . ") as m ON m.ID = p.manufacturer_ID
    //         JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
    //         JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //         JOIN filter as f ON f.ID = f_v.ID_filter
    //           WHERE p.visible = true AND p.stock > 0
    //           " . Database::optional("AND IF(EXISTS (SELECT * FROM product_flag as p_f JOIN flag as f ON f.ID=p_f.flag_ID WHERE f.name='promo' AND p_f.product_ID=p.ID), promo_price, catalog_price) BETWEEN ? AND ?", $min_price, $max_price)
    //   . Database::optional("AND f.type = 'color' AND f_v.ID IN (?)", $colors) .
    //   ") as sq
    //   LEFT JOIN product_flag as p_f on p_f.product_ID = sq.ID
    //   JOIN flag as fl on p_f.flag_ID = fl.ID GROUP BY sq.ID ORDER BY {$order_by_transcribed} LIMIT ?, ?;";

    // $result = Database::getInstance()->execute_query($query, [$offset, $amount]);
    // dwd(Database::getInstance()->info);
    while ($fetched = $result->fetch_assoc()) {
      $fetched['ID'] = $categories_joined . '/' . $fetched['ID'];
      $products[] = $fetched;
    }
    // dwd($offset, $boundary, count($products));
    return $products;

    //   SELECT f_v.ID_filter, GROUP_CONCAT(DISTINCT CONCAT('_',f_v.value,'_')) FROM filter_value as f_v 
    // JOIN product_filter_value as p_f_v ON p_f_v.filter_value_ID = f_v.ID
    //   JOIN product as p ON p_f_v.product_ID = p.ID
    //   WHERE p.ID = 6
    //   GROUP BY f_v.ID_filter;

    // SELECT product_ID, count(*) FROM (SELECT p.ID as product_ID, f_v.ID_filter as filter_ID, f_v.value as filter_value FROM filter_value as f_v 
    // JOIN product_filter_value as p_f_v ON p_f_v.filter_value_ID = f_v.ID
    //   JOIN product as p ON p_f_v.product_ID = p.ID
    //   WHERE p.ID in (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25)
    //   AND (
    //       (f_v.ID_filter = 1 AND f_v.ID IN (1, 2)) 
    //       OR
    //   	(f_v.ID_filter = 3 AND f_v.ID IN (1, 2, 13, 23, 19))
    //   )
    //   GROUP BY f_v.ID, p.ID) as xD Group BY product_ID;

    //old
    // GROUP_CONCAT(fl.name SEPARATOR ', ') as flag_names,
    // (select p_i.image_name from product_image as p_i where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name,
    // IF(GROUP_CONCAT(fl.name SEPARATOR ', ') LIKE '%promo%', promo_price, catalog_price) as curr_price,
    //   p.manufacturer_ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock FROM (SELECT p.manufacturer_ID, p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    // c.id as category
    // FROM product as p
    //   JOIN product_category as p_c ON p_c.product_ID = p.ID
    //   JOIN category as c ON c.ID = p_c.category_ID GROUP BY p.ID) as p
    //     LEFT JOIN product_flag as p_f on p_f.product_ID = p.ID
    //     LEFT JOIN flag as fl on p_f.flag_ID = fl.ID
    //     JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
    //     JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //     JOIN filter as f ON f.ID = f_v.ID_filter
    //     JOIN manufacturer as m ON m.ID = p.manufacturer_ID GROUP BY p.ID;
    // optional_column()
    //   $inner_query = "(SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    //   c.id as category
    //   FROM product as p
    //     JOIN product_category as p_c ON p_c.product_ID = p.ID
    //     JOIN category as c ON c.ID = p_c.category_ID GROUP BY p.ID) as p";
    //   "SELECT 
    //     p.ID, 
    //     GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') as flag_names,
    //     (select p_i.image_name from product_image as p_i where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name,
    //     IF(GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') LIKE '%promo%', promo_price, catalog_price) as curr_price,
    //       p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock FROM
    //         (SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    //           c.id as category
    //           FROM product) as p
    //         JOIN SELECT (SELECT category_ID FROM product_category WHERE ".optional_column_array("category_ID", $children_categories_array)." as p_c ON p_c.product_ID = p.ID
    //         JOIN category as c ON c.ID = p_c.category_ID GROUP BY p.ID
    //         LEFT JOIN SELECT (SELECT product_flag FROM product_category WHERE ".optional_column_array("category_ID", $children_categories_array)." as p_f on p_f.product_ID = p.ID
    //         LEFT JOIN product_flag as p_f on p_f.product_ID = p.ID
    //         JOIN flag as fl on p_f.flag_ID = fl.ID
    //         JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
    //         JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //         JOIN filter as f ON f.ID = f_v.ID_filter
    //         JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
    //         JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID GROUP BY p.ID;
    //     "
    //   $query = "SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    //   GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') as flag_names,
    //   (select p_i.image_name from product_image as p_i 
    //     where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name,
    //   c.id as category,
    //   IF(GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') LIKE '%promo%', promo_price, catalog_price) as curr_price
    //   FROM product as p
    //     LEFT JOIN product_flag as p_f on p_f.product_ID = p.ID
    //     LEFT JOIN flag as fl on p_f.flag_ID = fl.ID 
    //     JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
    //     JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //     JOIN filter as f ON f.ID = f_v.ID_filter
    //     JOIN product_category as p_c ON p_c.product_ID = p.ID
    //     JOIN category as c ON c.ID = p_c.category_ID
    //     JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
    //     JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
    //       WHERE (? = '' OR m.ID = ?) 
    //       AND (? = '' OR (f.type = 'color' AND f_v.ID IN (?)))
    //       AND p.visible = true AND p.stock > 0 GROUP BY p.ID
    //       --   HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
    //       --     where id = ? union all select c.id, c.name, c.parent from category c 
    //       --     inner join cte on c.parent = cte.id
    //       -- ) select GROUP_CONCAT(DISTINCT id, name SEPARATOR ', ') from cte) LIKE category_for_like)
    //       HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
    //             where id = ? union all select c.id, c.name, c.parent from category c 
    //             inner join cte on c.parent = cte.id
    //         ) select GROUP_CONCAT(DISTINCT CONCAT('.', id, '.') SEPARATOR ',') from cte) LIKE CONCAT('%.', category, '.%')) 
    //       AND (? = '' OR curr_price > ?)
    //       AND (? = '' OR curr_price < ?)
    //         ORDER BY {$order_by}
    //         LIMIT ?, ?;";
    //dac do where we wczesniejszym selekcie tym as p, where , stokc, visible, manufacturer, curr_price
  }

  public static function countProducts($extractedData, $category)
  {
    extract($extractedData);
    $children_categories_array = static::childrenCategories($category);

    $exists_promo_flag = QueryBuilder::exists('product_flag', 'p_f')
      ->join('flag', 'f', 'f.ID=p_f.flag_ID')
      ->where("f.name='promo'")
      ->andWhere('p_f.product_ID=p.ID')
      ->getQuery();

    $sub_query =
      QueryBuilder::select([
        'p.ID', 'p.manufacturer_ID', 'p.name', 'p.promo_price', 'p.catalog_price', 'p.serial_number',
        'p.stock', 'category' => 'c.id'
      ])
      ->from('product', 'p')
      ->join(QueryBuilder::select(['product_ID', 'category_ID'])->from('product_category')->optionalWhere('product_category.category_ID IN', [$children_categories_array]), 'p_c', 'p_c.product_ID = p.ID')
      ->join(QueryBuilder::select(['id'])->from('manufacturer')->optionalWhere('manufacturer.ID = ?', [$manufacturer]), 'm', 'm.ID = p.manufacturer_ID')
      ->join('category', 'c', 'c.ID=p_c.category_ID')
      ->join('product_filter_value', 'p_f_v', 'p_f_v.product_ID = p.ID')
      ->join('filter_value', 'f_v', 'f_v.ID = p_f_v.filter_value_ID')
      ->join('filter', 'f', 'f.ID=f_v.ID_filter')
      ->where('p.visible = true')
      ->andWhere('p.stock > 0')
      ->andOptionalWhere("IF($exists_promo_flag, promo_price, catalog_price) BETWEEN ? AND ?", [$min_price, $max_price])
      ->andOptionalWhere('f.type="color" AND f_v.ID IN (?)', [$colors]);
    // ->optionalBetween($min_price, $max_price)

    $result =
      QueryBuilder::count(
        QueryBuilder::select([
          'sq.ID'
        ])
          ->from($sub_query, 'sq')
          ->leftJoin('product_flag', 'p_f', 'p_f.product_ID = sq.ID')
          ->join('flag', 'fl', 'p_f.flag_ID = fl.ID')
          ->groupBy('sq.ID'),
      )->execute();
    // $query = "SELECT COUNT(*) as count FROM (SELECT sq.ID, sq.name, sq.promo_price, sq.catalog_price, sq.serial_number, sq.stock,
    // IF(GROUP_CONCAT(DISTINCT fl.name) LIKE '%promo%', promo_price, catalog_price) as curr_price,
    // (select p_i.image_name from product_image as p_i where p_i.product_ID = sq.ID ORDER BY p_i.main DESC LIMIT 1) as image_name
    //   FROM (SELECT p.ID, p.manufacturer_ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock, c.id as category
    //     FROM product as p
    //       JOIN 
    //         (SELECT product_ID, category_ID FROM product_category" . Database::optional("WHERE product_category.category_ID IN ?", $children_categories_array) . ") as p_c ON p_c.product_ID = p.ID
    //       JOIN category as c ON c.ID = p_c.category_ID
    //       JOIN 
    //         (SELECT ID FROM manufacturer" . Database::optional("WHERE manufacturer.ID = ?", $manufacturer) . ") as m ON m.ID = p.manufacturer_ID
    //       JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
    //       JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
    //       JOIN filter as f ON f.ID = f_v.ID_filter
    //         WHERE p.visible = true AND p.stock > 0
    //         " . Database::optional("AND IF(EXISTS (SELECT * FROM product_flag as p_f JOIN flag as f ON f.ID=p_f.flag_ID WHERE f.name='promo' AND p_f.product_ID=p.ID), promo_price, catalog_price) BETWEEN ? AND ?", $min_price, $max_price)
    //   . Database::optional("AND f.type = 'color' AND f_v.ID IN (?)", $colors) .
    //   ") as sq
    // LEFT JOIN product_flag as p_f on p_f.product_ID = sq.ID
    // JOIN flag as fl on p_f.flag_ID = fl.ID GROUP BY sq.ID) as xD;";
    // $result = Database::getInstance()->query($query);
    $fetched = $result->fetch_assoc();
    return $fetched['count'];
  }

  public static function generateNavigation($pages, $currentPage, $middle_count = 3)
  {
    if ($pages < 1) return [];
    $visiblePages = [$currentPage];
    while (count($visiblePages) < $pages && count($visiblePages) < $middle_count) {
      $firstElement = $visiblePages[0];
      $lastElement = end($visiblePages);
      if ($firstElement > 1) {
        array_unshift($visiblePages, $firstElement - 1);
      }
      if ($lastElement < $pages) {
        $visiblePages[] = $lastElement + 1;
      }
    }

    if ($visiblePages[0] == 2) {
      array_unshift($visiblePages, 1);
    } else if ($visiblePages[0] > 2) {
      array_unshift($visiblePages, 1, '...');
    }
    if (end($visiblePages) + 1 == $pages) {
      $visiblePages[] = $pages;
    } else if (end($visiblePages) + 1 < $pages) {
      array_push($visiblePages, '...', $pages);
    }
    return $visiblePages;
  }
}
