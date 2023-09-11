<?php 
namespace Controller;

use Core\{View, Request};

class Product
{
  public function index(Request $request)
  {
    View::open('product.php')->load(['request'=> $request]);
  }
}