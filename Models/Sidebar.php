<?php
namespace Models;

use Core\Database;

class Sidebar {
  //zajebane ze stacka https://stackoverflow.com/a/10332361
  private static function createTree(&$list, $parent){
    $tree = [];
    foreach ($parent as $k=>$l){
      if(isset($list[$l['ID']])){
        $l['children'] = static::createTree($list, $list[$l['ID']]);
      }
      $tree[] = $l;
    } 
    return $tree;
  }

  private static function mainCategoryName($category){
    if($category == null){
      return 'Wybierz kategorię';
    }
    $query = "SELECT name from category where ID = ?;";
    $result= Database::getInstance()->execute_query($query, [$category]);
    $featched = $result->fetch_assoc();
    return $featched['name'];
  }

  private static function categories($attribute_categories){
//     na górze ma być nadrzędna kategoria (główna)
// potem ma być lista kategorii
// mogą być one rozwijane i tam będą kolejne podkategorie, ale one nawet jak mają dzieci to są nie rozwijane
    if(count($attribute_categories) < 2){
      $attribute_categories = [null, ...$attribute_categories];
    }

    // dwd($attribute_categories);
    // $main_category = $attribute_categories[0] ?? null;
    // $mid_category = $attribute_categories[1] ?? null;
    // $last_subcategory = $attribute_categories[2] ?? null;
    // $i = 0
    // $last_category = end($attribute_categories);
    reset($attribute_categories);
    $sry = function (&$arr) use (&$attribute_categories, &$sry) {
      $category = current($attribute_categories);
      if($category == null){
        $query_part = "is NULL";
        $query_arguments = [];
      }else{
        $query_part = "= ?";
        $query_arguments = [$category];
      }
      if(key($attribute_categories) == 0){
        $arr['name'] = static::mainCategoryName($category);
      }
      //check if has children
      $query = "SELECT c.ID, c.name, c.parent, c.level, EXISTS (select ID from category as c1 where c1.parent = c.ID) as has_children from category as c where c.parent $query_part;";
      $result= Database::getInstance()->execute_query($query, $query_arguments);
      $arr['children'] = [];
      while ($featched = $result->fetch_assoc())
      {
        $featched['is_active'] = in_array($featched['ID'], $attribute_categories);
        $arr['children'][$featched['ID']] = $featched;
      }
      $next_category = next($attribute_categories);
      // dwd(key($attribute_categories));
      if($next_category){
        $sry($arr['children'][$next_category]);
      }
    };




    $cos = [];
    $sry($cos, $attribute_categories);
    // dwd($cos);
    return $cos;
  }

  private static function children_categories($parent_category){
    $query = "with recursive cte (id, name, parent) as (select id, name, parent from category
    where id = 2 union all select c.id, c.name, c.parent from category c 
    inner join cte on c.parent = cte.id) select GROUP_CONCAT(id SEPARATOR ',') as array from cte;";
    $result = Database::getInstance()->execute_query($query, [$parent_category]);
    $fetched = $result->fetch_assoc();
    return '('.$fetched['array'].')';
  }

  private static function manufacturers(){
    $children_categories_array = static::children_categories($parent_category);
    $query = "select m.name,
  c.ID,
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
        WHERE m.ID = 1
        AND c.ID in $children_categories_array
        AND p.visible = true AND p.stock > 0 GROUP BY m.ID";

        select manufacturer_ID, count(*) from (select m.ID as manufacturer_ID, p.ID as product_ID
        FROM product as p
          JOIN product_manufacturer as p_m ON p_m.product_ID = p.ID
          JOIN manufacturer as m ON m.ID = p_m.manufacturer_ID
            WHERE
            p.visible = true AND p.stock > 0 GROUP BY p.ID) as x GROUP BY manufacturer_ID;
  }

  private static function filters(){
    // $manufacturer = $request->input['manufacturer'] ?? '';
    // $colors = $request->input['colors'] ?? '';
    // $min_price = $request->input['min'] ?? '';
    // $max_price = $request->input['max'] ?? '';
    // $pp = $request->input['pp'] ?? 5;
    // $page = $request->input['page'] ?? 1;
    // $display = $request->input['display'] ?? 'grid';
    // $order_by = match($request->input['order'] ?? ''){
    //   'price_asc' => 'curr_price ASC',
    //   'price_desc' => 'curr_price DESC',
    //   'name_desc' => 'name DESC',
    //   default => 'name ASC'
    // };
  }

  private static function colors($query_colors){
    $query = "SELECT filter.name, filter.type, filter_value.value, filter_value.id as value_ID,
     (filter_value.id in (?)) as is_selected FROM filter JOIN filter_value
      ON filter_value.ID_filter = filter.ID WHERE type = 'color';";
    $result = Database::getInstance()->execute_query($query, [$query_colors]);
    $colors = [];
    while ($featched = $result->fetch_assoc())
    {
      $colors[] = $featched;
    }
    return $colors;
  }

  private static function manufacturers(){
    $query = "SELECT ID, name FROM manufacturer";
    $result = Database::getInstance()->execute_query($query);
    $manufacturers = [];
    while ($featched = $result->fetch_assoc())
    {
      $manufacturers[] = $featched;
    }
    return $manufacturers;
  }

  public static function get($categories, $colors){
    $colors = static::colors($colors);
    $manufacturers = static::manufacturers();
    $category_tree = static::categories($categories);
    return ['colors' => $colors, 'manufacturers' => $manufacturers, 'categories'=>$category_tree];
  }
}