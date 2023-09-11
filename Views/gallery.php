<?php 
// use Core\{Partial, Controller};
// use Controller\{Carousel};

$head->css('gallery.css', true)->css('tile.css', true)
  ->css('home_carousel.css', true)->css('icons.css', true)
  ->script('home_carousel.js', true); ?>
<div class="gallery">
  <div class="home-carousel-container">
    <div class="home-carousel">
    <div class="home-carousel__arrow home-carousel__arrow--left">
      <i class="icon-arrow-left"></i>
    </div>
      <div class="home-carousel__container">
        <?php foreach($home_top as $banner): ?>
        <div class="home-carousel__element">
          <div class="home-carousel__element-content">
            <h1 class="home-carousel__title"><?= $banner['title'] ?></h1>
            <div class="home-carousel__description"><?= $banner['description'] ?></div>
            <div class="home-carousel__button"><a class="home-carousel__link" href="<?= $banner['link'] ?>">Zobacz więcej</a></div>
          </div>
          <div class="home-carousel__image-container">
            <img class="home-carousel__image <?= $banner['mask'] ? 'home-carousel__image--mask' : '' ?>" 
            src="<?= local_photo($banner['image_name']) ?>" alt="<?= $banner['alt'] ?>">
          </div>
        </div>
        <?php endforeach ?>
      </div>
      <div class="home-carousel__arrow home-carousel__arrow--right">
        <i class="icon-arrow-right"></i>
      </div>
    </div>
    <div class="home-carousel__dots-container">
      <?php for($i = 0; $i < count($home_top); $i++): ?>
        <i class="home-carousel__dot"></i>
      <?php endfor; ?>
    </div>
  </div>
  <div class="tile-container">
    <?php
      foreach($home_tiles as $tile):
    ?>
    <div class="tile__main">
        <div class="tile__image <?= $tile['mask'] ? 'tile__image--mask' : '' ?>">
            <img class="tile__image" 
            src="<?= local_photo($tile['image_name']) ?>">
        </div>
        <div class="tile__content">
            <div class="tile__title"><?= $tile['title'] ?> </div>
            <div class="tile__dsc"><?= $tile['description'] ?></div>
            <a class="tile__button" href="<?= $tile['link'] ?>">Zobacz więcej</a>
        </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>