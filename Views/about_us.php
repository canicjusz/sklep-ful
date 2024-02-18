<?php $head->css('about_us.css', true); ?>
    <section class="whyUs">
        <img src="<?= local_photo($image_name) ?>" alt="" class="whyUs__img">
        <div class="whyUs__content">
            <h3 class="whyUs__heading"><?= $title ?>
</h3>
            <div class="whyUs__line">
                <hr class="whyUs__lineColorRed">
                <hr class="whyUs__lineColorWhite">
            </div>
            <p class="whyUs__paragraph">
            <?= $description ?>
            </p>
            <ul class="whyUs__list">
                <li class="whyUs__listItem">
                    <img class="whyUs__listItemIcon  whyUs__icon1 alt="Szybka wysyłka">
                    <span class="whyUs__listItemSpan">szybka wysyłka</span>
                </li>
                <li class="whyUs__listItem">
                    <img class="whyUs__listItemIcon  whyUs__icon2" alt="Szeroki asortyment">
                    <span class="whyUs__listItemSpan">szeroki asortyment</span>
                </li>
                <li class="whyUs__listItem">
                    <img class="whyUs__listItemIcon  whyUs__icon3" alt="Bezpieczne zwroty">
                    <span class="whyUs__listItemSpan">bezpieczne zwroty</span>
                </li>
            </ul>
            <button class="whyUs__btn">
                <span class="whyUs__btnSpan">
                    <a href="<?= $link ?>" class="whyUs__btnSpanAnchor">informacje o firmie</a>
                </span>
            </button>
        </div>
        <div class="whyUs__bg"></div>
    </section>