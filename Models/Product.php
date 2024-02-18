<?php

namespace Models;

use Core\QueryBuilder;
use Models\Category;

class Product
{
  public static function getSimilar($category, $product_id)
  {
    $similar_product_array = [];
    $optional_cte = QueryBuilder::with('cte')
      ->following(QueryBuilder::select(['GROUP_CONCAT(DISTINCT CONCAT(' . ', id, ' . '))'])->from('cte'))
      ->recursive(
        QueryBuilder::select(['id', 'name', 'parent'])->from('category')->where('id=?', [$category]),
        QueryBuilder::select(['c.id', 'c.name', 'c.parent'])->from('category', 'c')->join('cte', '', 'c.parent = cte.id')
      )->getQuery();
    $unfinished_query = QueryBuilder::select([
      'p.ID', 'p.name', 'p.promo_price', 'p.catalog_price', 'p.serial_number', 'p.stock', 'c.ID',
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
      ->join('product_category', 'p_c', 'p_c.product_ID = p.ID')
      ->join('category', 'c', 'c.ID = p_c.category_ID')
      ->where('p.visible = true')
      ->andWhere('p.stock > 0')
      ->andWhere('p.ID != ?', [$product_id])
      ->groupBy('p.ID');
    if (isset($category)) {
      $result = $unfinished_query->having("$optional_cte LIKE CONCAT('%.', category, '.%')")->limit(15)->execute();
    } else {
      $result = $unfinished_query->limit(15)->execute();
    }
    //    $query = "SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock, c.ID as category,
    //    GROUP_CONCAT(DISTINCT f.name) as flag_names,
    //    (select p_i.image_name from product_image as p_i 
    //      where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name 
    //    FROM product as p 
    //      JOIN product_flag as p_f on p_f.product_ID = p.ID JOIN flag as f on p_f.flag_ID = f.ID 
    //      JOIN product_category as p_c on p_c.product_ID = p.ID JOIN category as c on c.ID = p_c.category_ID
    //        WHERE p.visible = true AND p.stock > 0 AND p.ID != ?
    //        GROUP BY p.ID HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
    //              where id = ? union all select c.id, c.name, c.parent from category c 
    //              inner join cte on c.parent = cte.id
    //          ) select GROUP_CONCAT(DISTINCT CONCAT('.', id, '.')) from cte) LIKE CONCAT('%.', category, '.%')) 
    //            LIMIT 15;";
    // dwd($product_id, $category);
    //$result = Database::getInstance()->execute_query($query, [$product_id, $category, $category]);
    while ($fetched = $result->fetch_assoc()) {
      $similar_product_array[] = $fetched;
    }
    return $similar_product_array;
  }
  public static function getManyIDs($category, $manufacturer, $max_price, $min_price)
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
  public static function getMany($category, $categories_joined, $manufacturer, $min_price, $max_price, $colors, $order_by, $offset, $amount)
  {
    $order_by_transcribed = match ($order_by) {
      'price_asc' => 'curr_price ASC',
      'price_desc' => 'curr_price DESC',
      'name_desc' => 'name DESC',
      default => 'name ASC'
    };
    $products = [];
    $children_categories_array = Category::getChildren($category);
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
  public static function count($extractedData, $category)
  {
    extract($extractedData);
    $children_categories_array = Category::getChildren($category);

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
  public static function getDescription($product_id)
  {
    $result = QueryBuilder::select(['description', 'video_url'])
      ->from('product')
      ->where('ID=?', [$product_id])
      ->execute();
    // $query = "SELECT description, video_url FROM product WHERE ID=?;";
    // $result = Database::getInstance()->execute_query($query, [$product_id]);
    $fetched = $result->fetch_assoc();
    return $fetched;
  }

  public static function getParameters($product_id)
  {
    $result = QueryBuilder::select(['p.key', 'p_p.value'])
      ->from('parameter', 'p')
      ->join('product_parameter', 'p_p', 'p.ID = p_p.parameter_ID')
      ->where('p_p.product_ID=?', [$product_id])
      ->execute();
    // $query = "SELECT p.key, p_p.value FROM `parameter` as p JOIN `product_parameter` as p_p ON p.ID = p_p.parameter_ID WHERE p_p.product_ID=?;";
    // $result = Database::getInstance()->execute_query($query, [$product_id]);
    $parameters = [];
    while ($parameter = $result->fetch_assoc()) {
      $parameters[] = $parameter;
    }
    return $parameters;
  }
  public static function getOfferData($product_id)
  {
    $result = QueryBuilder::select([
      'p.ID', 'p.name', 'p.variant_name', 'p.catalog_price', 'p.promo_price', 'delivery_name' => 'd.name', 'p.serial_number', 'p.variant_group_ID',
      'flag_names' => 'GROUP_CONCAT(DISTINCT f.name)', 'manufacturer_name' => 'm.name', 'manufacturer_image' => 'm.image_name'
    ])
      ->from('product', 'p')
      ->join('delivery', 'd', 'd.ID = p.delivery_ID')
      ->join('product_manufacturer', 'p_m', 'p_m.product_ID = p.ID')
      ->join('manufacturer', 'm', 'm.ID = p_m.manufacturer_ID')
      ->leftJoin('product_flag', 'p_f', 'p_f.product_ID = p.ID')
      ->leftJoin('flag', 'f', 'p_f.flag_ID = f.ID')
      ->where('p.visible = true')
      ->andWhere('p.stock > 0')
      ->andWhere('p.ID = ?', [$product_id])
      ->groupBy('p.ID')
      ->execute();
    // $query = "SELECT p.ID, p.name, p.variant_name, p.catalog_price, 
    // p.promo_price, d.name as delivery_name, p.serial_number as serial_number, p.variant_group_ID,
    // GROUP_CONCAT(DISTINCT f.name) as flag_names, m.name as manufacturer_name, m.image_name as manufacturer_image FROM product as p
    //   JOIN delivery as d ON d.ID = p.delivery_ID
    //   JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
    //   JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
    //   LEFT JOIN product_flag as p_f ON p_f.product_ID = p.ID
    //   LEFT JOIN flag as f ON p_f.flag_ID = f.ID
    //     WHERE p.visible = true AND p.stock > 0 AND p.ID = ? GROUP BY p.ID;";
    // $result = Database::getInstance()->execute_query($query, [$product_id]);
    $fetched = $result->fetch_assoc();
    return $fetched;
  }
  public static function getFeatured()
  {
    $featured_array = [];
    $result =
      QueryBuilder::select([
        'p.ID', 'p.name', 'p.promo_price', 'p.catalog_price', 'p.serial_number', 'p.stock',
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
    while ($fetched = $result->fetch_assoc()) {
      $featured_array[] = $fetched;
    }
    return $featured_array;
  }
}
