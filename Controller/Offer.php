<?php

namespace Controller;

use Core\{View};
use Models\Product as ProductModel;
use Models\ProductImage as ProductImageModel;

class Offer
{
  public function index(int $product_id)
  {
    $horizontal = ProductImageModel::getHorizontalCarousel($product_id);
    $vertical = ProductImageModel::getVerticalCarousel($product_id);
    $product_offer = ProductModel::getOfferData($product_id);
    $variables = ['horizontal' => $horizontal, 'vertical' => $vertical, 'product_offer' => $product_offer];
    View::open('offer.php')->load($variables);
  }
}
