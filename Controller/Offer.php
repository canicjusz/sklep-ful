<?php 
namespace Controller;

use Core\{View};
use Models\Offer as OfferModel;

class Offer
{
  public function index(int $product_id)
  {
    $horizontal = static::horizontalCarouselImage($id);
		$vertical = static::verticalCarouselImage($id);
		$product_offer = static::productOffer($id);
    $variables = ['horizontal' => $horizontal, 'vertical' => $vertical, 'product_offer' => $product_offer];
    View::open('offer.php')->load($variables);
  }
}