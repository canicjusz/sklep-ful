<?php 
namespace Controller;

use Core\{View, Request};
use Models\ProductDescription as ProductDescriptionModel;

class ProductDescription
{
  public function index($product_id)
  {
    $general_description = ProductDescriptionModel::getGeneralDescription($product_id);
    $parameters = ProductDescriptionModel::getParameters($product_id);
    View::open('product_description.php')->load(['description' => $general_description, 'parameters' => $parameters]);
  }
}