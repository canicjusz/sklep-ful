<?php

namespace Controller;

use Core\View;
use Models\Product as ProductModel;

class Featured
{
  public function index()
  {
    $featured_array = ProductModel::getFeatured();
    $variables = ['featured' => $featured_array];
    View::open('featured.php')->load($variables);
  }
}
