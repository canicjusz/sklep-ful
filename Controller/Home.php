<?php 
namespace Controller;

use Core\{View, Request};

class Home
{
  public function index(Request $request)
  {
    View::open('home.php')->load();
  }
}