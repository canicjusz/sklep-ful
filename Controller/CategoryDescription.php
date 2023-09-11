<?php 
namespace Controller;

use Core\View;
use Models\CategoryDescription as CategoryDescriptionModel;

class CategoryDescription {
  public function index($current_id)
  {
    $variables = CategoryDescriptionModel::get($current_id);
    View::open('category_description.php')->load($variables);
  }
}