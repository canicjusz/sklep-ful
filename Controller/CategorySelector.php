<?php

namespace Controller;

use Core\View;
use Models\Category as CategoryModel;

class CategorySelector
{
  public function index(int $current_id, int|null $parent_id)
  {
    $categories = CategoryModel::getChildren($current_id, $parent_id);
    $variables = ['categories' => $categories];
    View::open('category_selector.php')->load($variables);
  }
}
