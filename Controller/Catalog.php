<?php 
namespace Controller;

use Core\{View, Request};

class Catalog
{
  public function index(Request $request)
  {
    // $variables = ;
    // $request
    View::open('catalog.php')->load(['request' => $request]);
  }
}