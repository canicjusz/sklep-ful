<?php

namespace Controller;

use Core\{View, Request};
use Models\Product as ProductModel;

class ProductDescription
{
  public function index(int $product_id)
  {
    $general_description = ProductModel::getDescription($product_id);
    $parameters = ProductModel::getParameters($product_id);
    View::open('product_description.php')->load(['description' => $general_description, 'parameters' => $parameters]);
  }
}
