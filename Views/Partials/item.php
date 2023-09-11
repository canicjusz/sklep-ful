<?php 
  use Core\{Partial};
  $head->css('item.css', true);
?>
        <a href='<?= local_url("product/{$item['ID']}") ?>'>
<div class="product_tile">
  <div class="item__photo-container">
    <img class="item__photo" src="<?= local_photo($item['image_name'])?>" alt="">
  </div>
  <div class="product_tile-hover">
    <div class="dsc">
      <div class="dsc1"><?= $item['name'] ?></div>
      <div class="dsc2">Kod produktu: <?= $item['serial_number'] ?></div>
    </div>
    <div class="price">
      <div class="price1"><?= $item['catalog_price'] ?></div>
      <div class="price2"><?= $item['promo_price'] ?></div>
    </div>
  </div>
  </a>
  <div class="addtocart">
    <?php 
    Partial::open('counter.php')->load(); ?>
      <div class="counter_container">
        <template id="counter">
          <button type="button" class="counter__button counter__button-decrease"></button>
          <input type="number" class="counter__input">
          <button type="button" class="counter__button counter__button-increase"></button>
        </template>
        <custom-counter class="counter" data-value="1" data-max="10" data-min="1"></custom-counter>
      </div>
      <div class="buybutton">
        <i class="buybutton-icon"></i>
      </div>
    </div>
</div>
</a>