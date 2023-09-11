<?php 
  use Core\{Partial, Controller};
  use Controller\{Offer, ProductDescription, Similar};
  // use Controller\{Gallery, Featured, AboutUs};

  $offer = new Offer();
  $product_description = new ProductDescription();
  $similar = new Similar();
  // $head->css('home.css', true)->title('Welcome home');

  $product_id = $request->parameters['product'];
  $categories = $request->parameters['category'];
  // dwd($categories);

  // dwd($request->parameters);
?>
<?php Partial::open('header.php')->load(); ?>
<main>
  <?php
    Partial::open('breadcrumbs.php')->load();
    $offer->index($product_id);
    $product_description->index($product_id);
    $similar->index($categories, $product_id);
  ?>
</main>
  <?php
    // Controller::resolve([AboutUs::class, 'index']);
    Partial::open('footer.php')->load();
  ?>


<?php 
// include_once CONTROLLER_ROOT .'/product.controller.php';
// require TEMPLATE_ROOT .'/header.php';
// require TEMPLATE_ROOT .'/breadcrumbs.php';
// require CONTROLLER_ROOT.'/offer.controller.php';
// require VIEW_ROOT.'/page_navigation.view.php';
// require CONTROLLER_ROOT.'/description.controller.php';
// require VIEW_ROOT.'/parameters.view.php';
// require CONTROLLER_ROOT .'/similar.controller.php';
// require TEMPLATE_ROOT .'/footer.php';
