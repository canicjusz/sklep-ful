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

    while ($fetched = $result->fetch_assoc()) {
      $similar_product_array[] = $fetched;
    }
    return $similar_product_array;
  }

  public static function getManyIDs($category, $manufacturer, $max_price, $min_price)
  {
    $children_categories_array = Category::getChildrenIDs($category);
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
    $children_categories_array = Category::getChildrenIDs($category);

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

    while ($fetched = $result->fetch_assoc()) {
      $fetched['ID'] = $categories_joined . '/' . $fetched['ID'];
      $products[] = $fetched;
    }

    return $products;
  }

  public static function count($extractedData, $category)
  {
    extract($extractedData);
    $children_categories_array = Category::getChildrenIDs($category);

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

    $fetched = $result->fetch_assoc();
    return $fetched['count'];
  }

  public static function getDescription($product_id)
  {
    $result = QueryBuilder::select(['description', 'video_url'])
      ->from('product')
      ->where('ID=?', [$product_id])
      ->execute();

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

    while ($fetched = $result->fetch_assoc()) {
      $featured_array[] = $fetched;
    }
    return $featured_array;
  }
}
