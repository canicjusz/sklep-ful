<?php 
namespace Controller;

use Core\View;
use Models\Sidebar as SidebarModel;

class Sidebar {
  public function index($categories, $input)
  {
    //todo: tutaj jest dużo niepotrzebnych wartości
    
      // 'categories' => $categories,
      // 'manufacturer' => $input['manufacturer'] ?? '',
    $colors = $input['colors'] ?? '';
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
    $last_three_categories = array_slice($categories, -2, 2);
    // dwd('hej', $last_three_categories);
    $variables = SidebarModel::get($last_three_categories, $colors);
    // dwd($variables);
    View::open('sidebar.php')->load($variables);
  }
}