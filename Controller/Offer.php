<?php 
namespace Controller;

use Core\{View};
use Models\Offer as OfferModel;

class Offer
{
  public function index($product_id)
  {
    $variables = OfferModel::get($product_id);
    View::open('offer.php')->load($variables);
  }
}