<?php 
namespace Controller;

use Core\View;
use Models\Gallery as GalleryModel;

class Gallery
{

  public function index()
  {
    $banners = GalleryModel::homeBanners();
    $tiles = GalleryModel::homeTiles();
    $variables = ['home_tiles' => $tiles , 'home_top' => $banners];
    View::open('gallery.php')->load($variables);
  }
}