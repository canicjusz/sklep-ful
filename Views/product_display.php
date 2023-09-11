<?php
use Core\{Partial};

$head->css('product_display.css', true);

$product_navbar = Partial::open('product_navbar.php')->renderString(['navigation' => $navigation,
'pp' => $pp, 'order_by' => $order_by, 'display' => $display, 'previous_page' => $previous_page, 'next_page' => $next_page, 'request' => $request]);
?>

<div class="product-display">
    <?= $product_navbar; ?>
    <div class="product-display__grid">
    <?php foreach($products as $item):
        ?>
        <div class="product-tile-wrapper">
            <?php Partial::open('item.php')->load(['item'=>$item]); ?>
        </div>
    <?php
    endforeach;
    ?></div>
    <?= $product_navbar; ?>
</div>
