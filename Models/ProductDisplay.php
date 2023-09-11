<?php
namespace Models;

use Core\Database;

class ProductDisplay {
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

  private static function children_categories($parent_category){
    $query = "with recursive cte (id, name, parent) as (select id, name, parent from category
    where id = ? union all select c.id, c.name, c.parent from category c 
    inner join cte on c.parent = cte.id) select GROUP_CONCAT(id SEPARATOR ',') as array from cte;";
    $result = Database::getInstance()->execute_query($query, [$parent_category]);
    $fetched = $result->fetch_assoc();
    return '('.$fetched['array'].')';
  }

  private get_filter_values($product_ids){
    $query = "SELECT p.ID as product_ID, f_v.ID_filter as filter_ID, GROUP_CONCAT(DISTINCT CONCAT(f_v.value)) as values_array FROM filter_value as f_v 
      JOIN product_filter_value as p_f_v ON p_f_v.filter_value_ID = f_v.ID
      JOIN product as p ON p_f_v.product_ID = p.ID
      WHERE p.ID in ?
      GROUP BY f_v.ID_filter, p.ID;";
    $result = Database::getInstance()->execute_query($query, [$parent_category]);
    while ($fetched = $result->fetch_assoc())
    {
      $fetched['ID'] = $categories . '/' . $fetched['ID'];
      $products[] = $fetched;
    }
  }

  public static function getProducts($order_by, $manufacturer, $colors, $category, $min_price, $max_price, $offset, $amount, $categories){
    $products = [];
    $children_categories_array = static::children_categories($parent_category);
    $get_all_ids = "SELECT GROUP_CONCAT(p.ID SEPARATOR ',') FROM product as p JOIN product_category as p_c ON p_c.product_ID = p.ID WHERE p_c.category_ID = ($children_categories_array);"
    $get_all_imgs
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
    $inner_query = "(SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    c.id as category
    FROM product as p
      JOIN product_category as p_c ON p_c.product_ID = p.ID
      JOIN category as c ON c.ID = p_c.category_ID GROUP BY p.ID) as p";
    "SELECT p.ID, 
    GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') as flag_names,
    (select p_i.image_name from product_image as p_i where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name,
    IF(GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') LIKE '%promo%', promo_price, catalog_price) as curr_price,
      p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock FROM $inner_query
        LEFT JOIN product_flag as p_f on p_f.product_ID = p.ID
        LEFT JOIN flag as fl on p_f.flag_ID = fl.ID
        JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
        JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
        JOIN filter as f ON f.ID = f_v.ID_filter
        JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
        JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID GROUP BY p.ID;
      "
    $query = "SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock,
    GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') as flag_names,
    (select p_i.image_name from product_image as p_i 
      where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name,
    c.id as category,
    IF(GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') LIKE '%promo%', promo_price, catalog_price) as curr_price
    FROM product as p
      LEFT JOIN product_flag as p_f on p_f.product_ID = p.ID
      LEFT JOIN flag as fl on p_f.flag_ID = fl.ID 
      JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
      JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
      JOIN filter as f ON f.ID = f_v.ID_filter
      JOIN product_category as p_c ON p_c.product_ID = p.ID
      JOIN category as c ON c.ID = p_c.category_ID
      JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
      JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
        WHERE (? = '' OR m.ID = ?) 
        AND (? = '' OR (f.type = 'color' AND f_v.ID IN (?)))
        AND p.visible = true AND p.stock > 0 GROUP BY p.ID
        --   HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
        --     where id = ? union all select c.id, c.name, c.parent from category c 
        --     inner join cte on c.parent = cte.id
        -- ) select GROUP_CONCAT(DISTINCT id, name SEPARATOR ', ') from cte) LIKE category_for_like)
        HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
              where id = ? union all select c.id, c.name, c.parent from category c 
              inner join cte on c.parent = cte.id
          ) select GROUP_CONCAT(DISTINCT CONCAT('.', id, '.') SEPARATOR ',') from cte) LIKE CONCAT('%.', category, '.%')) 
        AND (? = '' OR curr_price > ?)
        AND (? = '' OR curr_price < ?)
          ORDER BY {$order_by}
          LIMIT ?, ?;";
    $result = Database::getInstance()->execute_query($query, [$manufacturer, $manufacturer, $colors, $colors, 
    $category, $category, $min_price, $min_price, $max_price,
    $max_price, $offset, $amount]);
    // dwd(Database::getInstance()->info);
    while ($fetched = $result->fetch_assoc())
    {
      $fetched['ID'] = $categories . '/' . $fetched['ID'];
      $products[] = $fetched;
    }
    // dwd($offset, $boundary, count($products));
    return $products;
  }

  public static function countProducts($manufacturer, $colors, $category, $min_price, $max_price){
    $query = "SELECT COUNT(*) as count FROM (SELECT (select p_i.image_name from product_image as p_i 
    where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name,
  CONCAT('%', c.ID, c.name, '%') as category_for_like,
  IF(GROUP_CONCAT(DISTINCT fl.name SEPARATOR ', ') LIKE '%promo%', promo_price, catalog_price) as curr_price
    FROM product as p
      LEFT JOIN product_flag as p_f on p_f.product_ID = p.ID
      LEFT JOIN flag as fl on p_f.flag_ID = fl.ID 
      JOIN product_filter_value as p_f_v ON p_f_v.product_ID = p.ID
      JOIN filter_value as f_v ON f_v.ID = p_f_v.filter_value_ID
      JOIN filter as f ON f.ID = f_v.ID_filter
      JOIN product_category as p_c ON p_c.product_ID = p.ID
      JOIN category as c ON c.ID = p_c.category_ID
      JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
      JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
        WHERE (? = '' OR m.ID = ?) 
        AND (? = '' OR (f.type = 'color' AND f_v.ID IN (?)))
        AND p.visible = true AND p.stock > 0 GROUP BY p.ID
          HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
            where id = ? union all select c.id, c.name, c.parent from category c 
            inner join cte on c.parent = cte.id
        ) select GROUP_CONCAT(DISTINCT id, name SEPARATOR ', ') from cte) LIKE category_for_like)
        AND (? = '' OR curr_price > ?)
        AND (? = '' OR curr_price < ?)) as xD;";
      $result = Database::getInstance()->execute_query($query, [$manufacturer, $manufacturer, $colors, $colors, 
      $category, $category, $min_price, $min_price, $max_price,
      $max_price]);
      $fetched = $result->fetch_assoc();
      return $fetched['count'];
  }

  public static function generateNavigation($pages, $currentPage, $middle_count = 3){
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
  
      if($visiblePages[0] == 2){
          array_unshift($visiblePages, 1);
      }else if ($visiblePages[0] > 2){
          array_unshift($visiblePages, 1, '...');
      }
      if(end($visiblePages)+1 == $pages){
          $visiblePages[] = $pages;
      }else if (end($visiblePages)+1 < $pages){
          array_push($visiblePages, '...', $pages);
      }
    return $visiblePages;
  }
}
?>