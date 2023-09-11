<?php 
namespace Controller;

use Core\View;
use Models\ProductDisplay as ProductDisplayModel;

class ProductDisplay {
  public function index($request, $category)
  {
    $manufacturer = $request->input['manufacturer'] ?? '';
    $colors = $request->input['colors'] ?? '';
    $min_price = $request->input['min'] ?? '';
    $max_price = $request->input['max'] ?? '';
    $pp = $request->input['pp'] ?? 5;
    $page = $request->input['page'] ?? 1;
    $display = $request->input['display'] ?? 'grid';
    $order_by = match($request->input['order'] ?? ''){
      'price_asc' => 'curr_price ASC',
      'price_desc' => 'curr_price DESC',
      'name_desc' => 'name DESC',
      default => 'name ASC'
    };
    $products_count = ProductDisplayModel::countProducts($manufacturer, $colors, $category, $min_price, $max_price);

    $offset = ($page - 1) * $pp;

    if($products_count <= $offset){
      $this->fixInput($page, $offset, $pp);
    }
    $categories_joined = join('/', $request->parameters['category']);
    // dwd($products_count);
    // $boundary = $page * $pp;
    $pages_count = ceil($products_count/$pp);
    $products = ProductDisplayModel::getProducts($order_by, $manufacturer, $colors, $category, $min_price, $max_price, $offset, $pp, $categories_joined);
    $navigation = ProductDisplayModel::generateNavigation($pages_count, $page);

    $current_index = array_search($page, $navigation);
    $previous_page = $navigation[$current_index-1] ?? NULL;
    $next_page = $navigation[$current_index+1] ?? NULL;

    $variables = ['products' => $products, 'navigation' => $navigation,
      'pp' => $pp, 'order_by' => $order_by, 'display' => $display,
      'previous_page' => $previous_page, 'next_page' => $next_page, 'request' => $request];
    // $request
    
    View::open('product_display.php')->load($variables);
  }

  private static function fixInput(&$page, &$offset, $pp){
    $_GET['page'] = 1;
    $page = 1;
    $offset = 0;
  }
}