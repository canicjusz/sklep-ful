<?php

namespace Controller;

use Core\View;
use Models\Banner as BannerModel;

class AboutUs
{

  public function index()
  {
    $company_info = BannerModel::getBottomBanner();
    View::open('about_us.php')->load($company_info);
  }
}
