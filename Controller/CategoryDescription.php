<?php 
namespace Controller;

use Core\View;
use Models\CategoryDescription as CategoryDescriptionModel;

class CategoryDescription {
  public function index(int $current_id)
  {
    $description = CategoryDescriptionModel::getCategoryDescription($current_id);
    $variables = ['category_description' => $description];
    View::open('category_description.php')->load($variables);
  }
}