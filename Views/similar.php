<?php 
  use Core\Partial;
  $head->css('similar.css', true)->script('counter.js', true); 
?>
</head>
<body>
    <div class="bg" >
        <div class="text" id="other">Inne z kategorii</div>
        <div class="rectangle"></div>
        <div class="border"></div>

        <div class="product_tile">
        <?php
        foreach($similar_products as $item):
           ?>
        <div class="product_tile-hover"><?php Partial::open('item.php')->load(['item' => $item]); ?></div>
            <?php endforeach ?>
        </div>
            <div class="addtocart">
                <?php Partial::open('counter.php')->load(); ?>
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
    </div>