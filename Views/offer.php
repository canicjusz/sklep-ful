<?php

use Core\Partial;

$head->css('offer.css', true)->css('carousel_vertical.css', true)
  ->css('carousel_horizontal.css', true)->css('icons.css', true)->js('carousels.js', true);

?>



<div class="main-container">
  <div class="main-text">
    <span><?= $product_offer['name'] ?></span>
    <img src="<?= local_photo($product_offer['manufacturer_image']) ?>" style="height: 17px;margin-right: 15px; " alt="">
  </div>
  <div class="main-container__display">
    <div class="main-container__product">

      <div class="slider-wrapper">
        <button class="slide-arrow-vertical slide-arrow-vertical--prev" id="slide-arrow-prev">
          <i class="icon-arrow-left" style="
      display: block;
      margin-bottom: 0px;
      margin-left: 2px;
      margin-top: -2px;
      margin-right: 0px;
       font-size: 15px;
       color: white;">

          </i>
        </button>
        <ul class="slides-container" id="slides-container">

          <?php foreach ($vertical as $vert) : ?>
            <div class="slide-vertical"><img class="image_149" src="<?= local_photo($vert['image_name']) ?>"></div>
          <?php endforeach ?>
        </ul>

        <button class="slide-arrow-vertical slide-arrow-vertical--next" id="slide-arrow-next">
          <i class="icon-arrow-right" style="
      display: block;
      margin-bottom: 0px;
      margin-left: 2px;
      margin-top: -2px;
      margin-right: 0px;
       font-size: 15px;
       color: white;">


          </i>
        </button>
      </div>

      <section class="slider-wrapper_horizontal">
        <button class="slide-arrow_horizontal" id="slide-arrow-prev_horizontal">
          <i class="icon-arrow-left" style="
      display: block;
      margin-bottom: 0px;
      margin-left: 18px;
      margin-top: 18px;
      margin-right: 0px;
       font-size: 15px;
       color: white;">
          </i>
        </button>

        <ul class="slides-container_horizontal" id="slides-container_horizontal">
          <?php foreach ($horizontal as $horiz) : ?>
            <div class="slide-horizontal"><img class="slide-horizontal__photo" src="<?= local_photo($horiz['image_name']) ?>"></div>
          <?php endforeach ?>
        </ul>

        <button class="slide-arrow_horizontal" id="slide-arrow-next_horizontal">
          <i class="icon-arrow-right" style="
      display: block;
      margin-bottom: 0px;
      margin-left: 18px;
      margin-top: 18px;
      margin-right: 0px;
       font-size: 15px;
       color: white;"></i>
        </button>

        <div class="info">
          <div class="prom">PROMOCJA</div>
          <div class="new">NOWOŚĆ</div>
        </div>
      </section>
    </div>
    <div class="container">
      <div class="shop-min">
        <div class="id">
          <span><?= $product_offer['serial_number'] ?></span>
          <div>
            <div class="main_id">
              <div class="Dostępne">
                <svg xmlns="http://www.w3.org/2000/svg" width="17.23" height="17.353" viewBox="0 0 17.23 17.353" style="margin-right:10px; ">
                  <g id="Group_5248" data-name="Group 5248" transform="translate(0.5 0.5)">
                    <path id="Path_4441" data-name="Path 4441" d="M25.23,13.426l-8.115,4.426L9,13.426,17.115,9Z" transform="translate(-9 -9)" fill="none" stroke="#80b61d" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                    <path id="Path_4442" data-name="Path 4442" d="M25.23,63v7.377L17.115,74.8,9,70.377V63" transform="translate(-9 -58.574)" fill="none" stroke="#80b61d" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                    <line id="Line_379" data-name="Line 379" y2="7.323" transform="translate(8.115 9.03)" fill="none" stroke="#80b61d" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                    <line id="Line_380" data-name="Line 380" x2="8.115" y2="4.426" transform="translate(4.057 2.213)" fill="none" stroke="#80b61d" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                  </g>
                </svg>
                <span>Dostępny </span>
              </div>
              <div class="vertical_line"></div>
              <div class="xd">
                <svg xmlns="http://www.w3.org/2000/svg" width="19.06" height="13.956" viewBox="0 0 19.06 13.956" style="margin-right:10px;">
                  <g id="Group_5250" data-name="Group 5250" transform="translate(0.5 0.5)">
                    <g id="Group_5251" data-name="Group 5251" transform="translate(0 0)">
                      <circle id="Ellipse_164" data-name="Ellipse 164" cx="1.5" cy="1.5" r="1.5" transform="translate(2.179 9.956)" fill="none" stroke="#1d1d1b" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                      <circle id="Ellipse_165" data-name="Ellipse 165" cx="1.5" cy="1.5" r="1.5" transform="translate(13.179 9.956)" fill="none" stroke="#1d1d1b" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                      <line id="Line_381" data-name="Line 381" x2="8" transform="translate(5.179 11.956)" fill="none" stroke="#1d1d1b" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                      <path id="Path_4443" data-name="Path 4443" d="M20.493,16.388V9H9V20.493h2.463" transform="translate(-9 -9)" fill="none" stroke="#1d1d1b" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                      <path id="Path_4444" data-name="Path 4444" d="M135,27h1.642a4.923,4.923,0,0,1,4.925,4.925v4.925h-1.642" transform="translate(-123.507 -25.358)" fill="none" stroke="#1d1d1b" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" />
                    </g>
                  </g>
                </svg>
                <span><?= $product_offer['delivery_name'] ?></span>
              </div>
            </div>
            <br>
            <div class="product">
              <h5 class="emil_mikolajczak_zjeb">WARIANTY<h5>
            </div>
          </div>
          <div class="container-product">
            <div class="item1">
              <img src="<?= local_photo('/categories/49.png') ?>" alt="light" class="img1">
              <div style="display: flex; align-items: center; justify-content: center;">
                <span>Biały</span>
              </div>
            </div>
            <div class="item2">
              <img src="<?= local_photo('/categories/49.png') ?>" alt="light" class="img1">
              <div class="black_variant">
                <span>Czarny</span>
              </div>
            </div>
          </div>
        </div>
        <div class="shopme">
          <div class="shopme-border">
            <div class="high">
              <s><?= $product_offer['catalog_price'] ?></s>
            </div>
            <div class="low">
              <span> <?= $product_offer['promo_price'] ?></span>
            </div>
            <div class="addtocart_offer">
              <div class="counter_container_offer">
                <?php Partial::open('counter.php')->load(); ?>
                <template id="custom-counter-offer">
                  <button type="button" class="counter__button counter__button-decrease"></button>
                  <input type="number" class="counter__input">
                  <button type="button" class="counter__button counter__button-increase"></button>
                </template>
                <custom-counter class="counter-offer" data-value="1" data-max="10" data-min="1"></custom-counter>
              </div>
              <div class="buybutton_offer">
                <div class="buybutton_offer_items">
                  <i class="buybutton-icon-offer"></i>
                  <span class="buybutton_text">DO KOSZYKA</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>