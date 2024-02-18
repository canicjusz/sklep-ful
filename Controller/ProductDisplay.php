<?php

namespace Controller;

use Core\{Request, View};
use Models\Product as ProductModel;
use Models\Navigation as NavigationModel;

class ProductDisplay
{
  public function index(Request $request)
  {
    $categories = $request->parameters['category'];
    $extractedData = $request->misc['extracted_data'];
    $categories_count = count($categories);
    $current_category = $categories[$categories_count - 1];

    $products_count = ProductModel::count($extractedData, $current_category);

    $offset = ($extractedData['page'] - 1) * $extractedData['pp'];

    if ($products_count <= $offset) {
      $this->fixInput($extractedData['page'], $offset);
    }
    $categories_joined = join('/', $categories);
    // $boundary = $page * $pp;
    $pages_count = ceil($products_count / $extractedData['pp']);
    $products = ProductModel::getMany($current_category, $categories_joined, $extractedData['manufacturer'], $extractedData['min_price'], $extractedData['max_price'], $extractedData['colors'], $extractedData['order_by'], $offset, $extractedData['pp']);
    $navigation = NavigationModel::generate($pages_count, $extractedData['page']);

    $current_index = array_search($extractedData['page'], $navigation);
    $previous_page = $navigation[$current_index - 1] ?? NULL;
    $next_page = $navigation[$current_index + 1] ?? NULL;

    $variables = [
      'products' => $products, 'navigation' => $navigation,
      'pp' => $extractedData['pp'], 'order_by' => $extractedData['order_by'], 'display' => $extractedData['display'],
      'previous_page' => $previous_page, 'next_page' => $next_page, 'request' => $request
    ];
    // $request

    View::open('product_display.php')->load($variables);
  }

  private static function fixInput(string &$page, int &$offset)
  {
    $_GET['page'] = 1;
    $page = 1;
    $offset = 0;
  }
}
