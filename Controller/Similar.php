<?php

namespace Controller;

use Core\{View, Request};
use Models\Product as ProductModel;

class Similar
{
  public function index(array $categories, int $product_id)
  {
    $last_category = end($categories);
    $similar_products = ProductModel::getSimilar($last_category, $product_id);
    View::open('similar.php')->load(['similar_products' => $similar_products]);
  }
}
