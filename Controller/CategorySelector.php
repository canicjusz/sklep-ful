<?php

namespace Controller;

use Core\View;
use Models\CategorySelector as CategorySelectorModel;

class CategorySelector
{
  public function index(int $current_id, int|null $parent_id)
  {
    $categories = CategorySelectorModel::getCategories($current_id, $parent_id);
    dwd('kategorie', $categories);
    $variables = ['categories' => $categories];
    View::open('category_selector.php')->load($variables);
  }
}
