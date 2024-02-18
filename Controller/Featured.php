<?php 
namespace Controller;

use Core\View;
use Models\Featured as FeaturedModel;

class Featured
{
  public function index()
  {
    $featured_array = FeaturedModel::getFeaturedProducts();
    $variables = ['featured' => $featured_array];
    View::open('featured.php')->load($variables);
  }
}