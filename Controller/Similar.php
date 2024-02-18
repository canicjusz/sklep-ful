<?php 
namespace Controller;

use Core\{View, Request};
use Models\Similar as SimilarModel;

class Similar
{
  public function index(array $categories, int $product_id)
  {
    $last_category = end($categories);
    $similar_products = SimilarModel::similarProducts($last_category, $product_id);
    View::open('similar.php')->load(['similar_products' => $similar_products]);
  }
}