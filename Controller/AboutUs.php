<?php 
namespace Controller;

use Core\View;
use Models\AboutUs as AboutUsModel;

class AboutUs
{

  public function index()
  {
    $variables = AboutUsModel::get();
    View::open('about_us.php')->load($variables);
  }
}