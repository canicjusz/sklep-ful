<?php $head->css('category_selector.css', true)->script('category_selector.js', true);?>
    <div class="category_slider">
        <?php foreach($categories as $category):?>
            <a href="<?= local_url('catalog/'.$category['ID']) ?>">
                <div class="category_slider__box <?= $category['is_current'] ? 'orange' : ''?>">
                    <div class="category_slider__imgBox" onclick="changeColor(this)">
                        <img src="<?= $category['image_name'] ?>">
                    </div>
                    <span class="category_slider__text" ><?= $category['name'] ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>