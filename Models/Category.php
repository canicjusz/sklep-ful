<?php

namespace Models;

use Core\QueryBuilder;

class Category
{
  public static function getName($id)
  {
    if ($id == null) {
      return 'Wybierz kategorię';
    }
    $result = QueryBuilder::select(['name'])->from('category')->where('ID=?', [$id])->execute();
    $featched = $result->fetch_assoc();
    return $featched['name'];
  }

  public static function getTree($attribute_categories)
  {
    //     na górze ma być nadrzędna kategoria (główna)
    // potem ma być lista kategorii
    // mogą być one rozwijane i tam będą kolejne podkategorie, ale one nawet jak mają dzieci to są nie rozwijane
    if (count($attribute_categories) < 3) {
      $attribute_categories = [null, ...$attribute_categories];
    }

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

      $parent_category['name'] = static::getName($category);

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
    if ($remove_top_category_level) {
      $columns = array_column($category_tree['children'], 'is_active', 'ID');
      $index_to_detatch = array_search(true, $columns);
      $category_tree = $category_tree['children'][$index_to_detatch];
    }
    return $category_tree;
  }

  public static function getRootPath($category)
  {
    $query =
      QueryBuilder::with('cte')
      ->following(QueryBuilder::select(['id', 'name', 'parent'])->from('cte'))
      ->recursive(
        QueryBuilder::select(['id', 'name', 'parent'])->from('category')->where('id=?', [$category]),
        QueryBuilder::select(['c.id', 'c.name', 'c.parent'])->from('category', 'c')->join('cte', '', 'c.id = cte.parent')
      );
    $result = $query->execute();
    $categories_ids = [];
    while ($fetched = $result->fetch_assoc()) {
      $categories_ids[] = $fetched['id'];
    }
    return array_reverse($categories_ids);
  }

  public static function getChildrenIDs($parent_category)
  {
    $result =
      QueryBuilder::with('cte')
      ->following(QueryBuilder::select(['array' => 'GROUP_CONCAT(id)'])->from('cte'))
      ->recursive(
        QueryBuilder::select(['id', 'name', 'parent'])->from('category')->where('id=?', [$parent_category]),
        QueryBuilder::select(['c.id', 'c.name', 'c.parent'])->from('category', 'c')->join('cte', '', 'c.parent = cte.id')
      )
      ->execute();
    $fetched = $result->fetch_assoc();
    return '(' . $fetched['array'] . ')';
  }

  public static function getChildren(int $current_id, int|null $parent_id)
  {
    $sign = $parent_id === null ? 'IS NULL' : '= ?';
    $result = QueryBuilder::select(['ID', 'name', 'image_name', 'description', 'is_current' => '(ID = ?)'], [$current_id])
      ->from('category')
      ->where("parent $sign", [$parent_id])
      ->execute();
    $categories = [];
    while ($category = $result->fetch_assoc()) {
      $categories[] = $category;
    }
    return $categories;
  }

  public static function getDescription($current_id)
  {
    $result =
      QueryBuilder::select(['name', 'description'])
      ->from('category')
      ->where('ID=?', [$current_id])
      ->execute();
    $fetched = $result->fetch_assoc();
    return $fetched;
  }
}
