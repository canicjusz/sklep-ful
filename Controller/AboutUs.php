<?php 
namespace Controller;

use Core\View;
use Models\AboutUs as AboutUsModel;

class AboutUs
{

  public function index()
  {
    $company_info = AboutUsModel::getCompanyInfo();
    View::open('about_us.php')->load($company_info);
  }
}