<?php

namespace Controller;

use Core\View;
use Models\Banner as BannerModel;

class Gallery
{

  public function index()
  {
    $banners = BannerModel::getCarouselBanners();
    $tiles = BannerModel::getTiles();
    $variables = ['home_tiles' => $tiles, 'home_top' => $banners];
    View::open('gallery.php')->load($variables);
  }
}
