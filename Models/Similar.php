<?php
namespace Models;

use Core\Database;

class Similar{ 
  static public function similarProducts($category, $product_id){
    $similar_product_array = [];
    $query = "SELECT p.ID, p.name, p.promo_price, p.catalog_price, p.serial_number, p.stock, c.ID as category,
    GROUP_CONCAT(DISTINCT f.name SEPARATOR ', ') as flag_names,
    (select p_i.image_name from product_image as p_i 
      where p_i.product_ID = p.ID ORDER BY p_i.main DESC LIMIT 1) as image_name 
    FROM product as p 
      JOIN product_flag as p_f on p_f.product_ID = p.ID JOIN flag as f on p_f.flag_ID = f.ID 
      JOIN product_category as p_c on p_c.product_ID = p.ID JOIN category as c on c.ID = p_c.category_ID
        WHERE p.visible = true AND p.stock > 0 AND p.ID != ?
        GROUP BY p.ID HAVING (? = '' OR (with recursive cte (id, name, parent) as (select id, name, parent from category
              where id = ? union all select c.id, c.name, c.parent from category c 
              inner join cte on c.parent = cte.id
          ) select GROUP_CONCAT(DISTINCT CONCAT('.', id, '.') SEPARATOR ',') from cte) LIKE CONCAT('%.', category, '.%')) 
            LIMIT 15;";
    // dwd($product_id, $category);
    $result = Database::getInstance()->execute_query($query, [$product_id, $category, $category]);
    while($fetched = $result -> fetch_assoc()){
      $similar_product_array[] = $fetched;
    }
    return $similar_product_array;
  }
}