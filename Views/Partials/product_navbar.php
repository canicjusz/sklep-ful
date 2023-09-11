<?php 
$head
  ->script('product_navbar.js', true)->css('product_navbar.css', true)
  ->css('product_navbar.css', true)->css('icons.css', true)->css('select.css', true);

$display_request = clone $request;
$order_request = clone $request;
$pp_request = clone $request;
$page_request = clone $request;
?>
<template id="select">
  <div class="select__current-element">
    <span class="select__current-content"></span>
    <span class="select__icon"></span>
  </div>
  <ul class="select__list select__list--hidden">
    <slot name="item"></slot>
  </ul>
</template>
<nav class="product-navbar" id="navbar-product">
    <div class="product-navbar__mode">
        <span class="product-navbar__mode-text">Widok:</span>
        <a href="<?= $display_request->set_and_build("display", 'grid') ?>" 
        class="product-navbar__mode-link <?= $display == 'list' ? '' : 'product-navbar__mode-link--active' ?>" id="gridViewLink">
            <i class="product-navbar__mode-icon icon-grid"></i>
        </a>
        <a href="<?= $display_request->set_and_build("display", 'list') ?>" class="product-navbar__mode-link
        <?= $display == 'list' ? 'product-navbar__mode-link--active' : '' ?>" id="listViewLink">
            <i class="product-navbar__mode-icon icon-list"></i>
        </a>
    </div>
    <div class="product-navbar__right">
        <div class="product-navbar__section product-navbar__section-sort">
            <h3 class="product-navbar__section-title">Sortuj</h3>
            <custom-select class="select product-navbar__select" data-default-value="<?= $_GET['o'] ?? '' ?>">
                <li slot="item" class="select__list-item" data-value="">
                    <a href="<?= $order_request->set_and_build("order", '') ?>">Domyślnie</a>
                </li>
                <li slot="item" class="select__list-item" data-value="price_asc">
                    <a href="<?= $order_request->set_and_build("order", 'price_asc') ?>">Cena rosnąco</a>
                </li>
                <li slot="item" class="select__list-item" data-value="price_desc">
                    <a href="<?= $order_request->set_and_build("order", 'price_desc') ?>">Cena malejąco</a>
                </li>
                <li slot="item" class="select__list-item" data-value="name_asc">
                    <a href="<?= $order_request->set_and_build("order", 'name_asc') ?>">Nazwa rosnąco</a>
                </li>
                <li slot="item" class="select__list-item" data-value="name_desc">
                    <a href="<?= $order_request->set_and_build("order", 'name_desc') ?>">Nazwa malejąco</a>
                </li>
            </custom-select>
        </div>
        <div class="product-navbar__section product-navbar__section-show">
            <h3 class="product-navbar__section-title">Pokaż</h3>
            <custom-select class="select product-navbar__select" data-default-value="<?= $_GET['pp'] ?? 5 ?>">
                <li slot="item" class="select__list-item" data-value="5">
                    <a href="<?= $pp_request->set_and_build("pp", 5) ?>">5</a>
                </li>
                <li slot="item" class="select__list-item" data-value="10">
                    <a href="<?= $pp_request->set_and_build("pp", 10) ?>">10</a>
                </li>
                <li slot="item" class="select__list-item" data-value="15">
                    <a href="<?= $pp_request->set_and_build("pp", 15) ?>">15</a>
                </li>
                <li slot="item" class="select__list-item" data-value="20">
                    <a href="<?= $pp_request->set_and_build("pp", 20) ?>">20</a>
                </li>
            </custom-select>
        </div>
        <div class="product-navbar__section product-navbar__pages">
            <?php
                if($previous_page):
            ?>
                <a href="<?= $page_request->set_and_build("page", $previous_page); ?>" class="product-navbar__page-link icon-arrow-left"></a>
            <?php
                endif;
                foreach($navigation as $page_id):
                if($page_id != '...'):
            ?>
                <a href="<?= $page_request->set_and_build("page", $page_id) ?>" class="product-navbar__page-link <?= $curr_page == $page_id ? 'product-navbar__page-link--current' : '' ?>">
                    <?= $page_id ?>
                </a>
            <?php else: ?>
                <span class="product-navbar__page-dots">...</span>
            <?php endif; endforeach;
                if($next_page):
            ?>
            <a href="<?= $page_request->set_and_build("page", $next_page) ?>" class="product-navbar__page-link">></a>
        <?php endif; ?>
        </div>
    </div>
</nav>