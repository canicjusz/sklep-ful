<?php

namespace Controller;

use Core\View;
use Models\Banner as BannerModel;

class Banner
{
  public function index()
  {
    $banner = BannerModel::getCatalogBanner();
    View::open('banner.php')->load($banner);
  }
}
