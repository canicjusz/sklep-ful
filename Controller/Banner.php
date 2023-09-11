<?php 
namespace Controller;

use Core\View;
use Models\Banner as BannerModel;

class Banner {
  public function index()
  {
    $variables = BannerModel::get();
    View::open('banner.php')->load($variables);
  }
}