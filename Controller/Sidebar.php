<?php

namespace Controller;

use Core\{View, Request};
use Models\Sidebar as SidebarModel;

class Sidebar
{
  public function index(Request $request, array $categories, int $current_id)
  {
    $extractedData = $request->misc['extracted_data'];
    //todo: tutaj jest dużo niepotrzebnych wartości

    // 'categories' => $categories,
    // 'manufacturer' => $input['manufacturer'] ?? '',
    // $colors = $input['colors'] ?? '';
    //   'min_price' => $input['min'] ?? '',
    //   'max_price' => $input['max'] ?? '',
    //   'pp' => $input['pp'] ?? 5,
    //   'page' => $input['page'] ?? 1,
    //   'display' => $input['display'] ?? 'grid',
    //   'order_by' => match($input['order'] ?? ''){
    //         'price_asc' => 'curr_price ASC',
    //         'price_desc' => 'curr_price DESC',
    //         'name_desc' => 'name DESC',
    //         default => 'name ASC'
    //       }
    // ];
    //normalnie powinno być na dwa
    $how_many = count($categories) > 2 ? 2 : 3;
    $last_three_categories = array_slice($categories, -3);
    // dwd('hej', $last_three_categories);
    $manufacturers =
      SidebarModel::getManufacturers($current_id, $extractedData['manufacturer'], $extractedData['min_price'], $extractedData['max_price'], $extractedData['colors']);
    $filters = SidebarModel::getFilters($current_id, $extractedData['manufacturer'], $extractedData['min_price'], $extractedData['max_price']);
    $price_range = SidebarModel::getFilters($current_id, $extractedData['manufacturer'], $extractedData['min_price'], $extractedData['max_price']);
    $category_tree = SidebarModel::getCategories($last_three_categories);
    dwd($extractedData);
    // dwd($variables);
    $variables = ['filters' => $filters, 'price_range' => ['min' => $extractedData['min_price'], 'max' => $extractedData['max_price']], 'manufacturers' => $manufacturers, 'categories' => $category_tree, 'request' => $request];
    View::open('sidebar.php')->load($variables);
  }
}
