<?php 
namespace Controller;

use Core\View;
use Models\Gallery as GalleryModel;

class Gallery
{

  public function index()
  {
    $variables = GalleryModel::get();
    View::open('gallery.php')->load($variables);
  }
}