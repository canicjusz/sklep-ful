<?php

namespace Controller;

use Core\View;
use Models\Category as CategoryModel;

class CategoryDescription
{
  public function index(int $current_id)
  {
    $description = CategoryModel::getDescription($current_id);
    $variables = ['category_description' => $description];
    View::open('category_description.php')->load($variables);
  }
}
