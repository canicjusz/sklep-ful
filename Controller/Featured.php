<?php 
namespace Controller;

use Core\View;
use Models\Featured as FeaturedModel;

class Featured
{
  public function index()
  {
    $variables = FeaturedModel::get();
    View::open('featured.php')->load($variables);
  }
}