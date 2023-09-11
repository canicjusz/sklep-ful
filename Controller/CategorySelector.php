<?php 
namespace Controller;

use Core\View;
use Models\CategorySelector as CategorySelectorModel;

class CategorySelector {
  public function index($current_id, $parent_id)
  {
    $categories = CategorySelectorModel::get($current_id, $parent_id);
    View::open('category_selector.php')->load(['categories' => $categories]);
  }
}