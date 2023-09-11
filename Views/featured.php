<?php 
  use Core\{Partial};
?>
<?php $head->script('featured.js', true)->css('featured.css', true); ?>    
<div class="featured">
    <h2 class="featured__title">Polecane produkty</h2>
    <div class="featured__custom-break"></div>
    <div class="featured-carousel">
        <div class="featured-carousel__arrow featured-carousel__arrow--left">
            <i class="icon-arrow-left"></i>
        </div>
        <div class="featured-carousel__product-container">
        <?php foreach($featured as $item): ?>
          <div class="featured-carousel__product">
            <?php Partial::open('item.php')->load(['item'=>$item]); ?>
          </div>
        <?php endforeach ?>
        </div>
        <div class="featured-carousel__arrow featured-carousel__arrow--right">
            <i class="icon-arrow-right"></i>
        </div>
    </div>
    <a href="" class="featured__button">Zobacz wszystkie inspiracje</a>
</div>