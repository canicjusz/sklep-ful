<?php 
  $head
    ->css('sidebar.css', true)->css('accordion.css', true)
    ->css('applied_filters.css', true)->css('manufacturer_checkbox.css', true)
    ->css('color_selector.css', true)->css('price_range.css', true)
    ->script('accordion.js', true)->script('applied_filters.js', true)
    ->script('manufacturer_checkbox.js', true)->script('color_selector.js', true)
    ->script('price_range.js', true);
  // dwd($categories);
?>

<div class="sidebar__superglue">
<aside class="sidebar">
<p class="sidebar__heading">
                <span class="sidebar__heading-text"><?= $categories['name'] ?></span>
                <!-- <a class="sidebar__btn sidebar__heading-btn icon" href="javascript:void(0);" onclick="toggleElementVisibility1()">
                    <i class="sidebar__btnIcon plus" id="changeIcon1"></i>
                </a> -->
            </p>
    <ul class="sidebar__list" id="sidebar__showHide1">
      <?php foreach($categories['children'] as $category):  ?>
        <li class="sidebar__listItem">
        <a class="sidebar__listItemAnchor <?= $category['is_active'] ? 'sidebar__listItemAnchor--active' : '' ?>" href="<?= local_url('catalog/'.$category['ID']) ?>">
          <?= $category['name']?>
          <?php if($category['has_children']): ?>
            <i class="sidebar__btnIcon icon-minus" id="changeIcon4"></i>
          <?php endif; ?>
        </a>
          <ul class="sidebar__nestedList">
            <?php if($category['is_active']): foreach($category['children'] as $sub_category): ?>
            <li class="sidebar__nestedListItem">
              <a class="sidebar__nestedListItemAnchor" href="<?= local_url('catalog/'.$sub_category['ID']) ?>">
                <?= $sub_category['name'] ?>
                <?php if($sub_category['has_children']): ?>
                  <i class="sidebar__btnIcon icon-plus" id="changeIcon4"></i>
                <?php endif; ?>
              </a>
            </li>
            <?php endforeach; endif; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ul>
</aside>

<div class="filtry"><div class="appliedfilters__text">FILTRY</div></div>
<div class="filtry-container">
<div class="word-container">
    <span class="word">Parametr</span>
    <span class="remove-button icon-x" data-word="Parametr"></span>
</div>
<div class="word-container">
    <span class="word">Parametr</span>
    <span class="remove-button icon-x" data-word="Parametr"></span>
</div>
<div class="word-container">
    <span class="word">Parametr</span>
    <span class="remove-button icon-x" data-word="Parametr"></span>
</div>
</div>

<div class="producent">

    <div class="producent__title">Producent</div>


          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa">oaza-memow.pl</div>
          </div>

          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa">oaza-memow.pl</div>
          </div>

          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa">oaza-memow.pl</div>
          </div>

          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa">oaza-memow.pl</div>
          </div>

          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa">oaza-memow.pl</div>
          </div>



              <div class="producent__container hidden">
            
                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

                    <div class="producent__parametr">
                      <div class="producent__checkbox">
                        <label class="checkbox__container">
                            <input type="checkbox">
                            <span class="checkmark">
                                <span class="checkmark2"></span>
                            </span>
                        </label>
                      </div>
                      <div class="producent__nazwa">Parametr</div>
                    </div>

              </div>


      
      <div class="show-more">
          <p id="showMoreButton">więcej (liczba)</p>
      </div>

</div>

<template id="color-selector">
<div class="color__title">
  <div class="color__titlehelp">
  KOLOR
  </div>
</div>
<div class="color__wrap">
  <ul class="color-selector__list color-selector__list--hidden">
    <slot name="item"></slot>
  </ul>
</div>
</template>

    <div class="color__selector">
        <color-selector class="color-selector" data-selected="1">
            <li slot="item" class="color-selector__list-item" data-value="1">black</li>
            <li slot="item" class="color-selector__list-item" data-value="2">red</li>
            <li slot="item" class="color-selector__list-item" data-value="3">green</li>
            <li slot="item" class="color-selector__list-item" data-value="4">blue</li>
            <li slot="item" class="color-selector__list-item" data-value="1">black</li>
            <li slot="item" class="color-selector__list-item" data-value="2">red</li>
            <li slot="item" class="color-selector__list-item" data-value="3">green</li>
            <li slot="item" class="color-selector__list-item" data-value="4">blue</li>
            <li slot="item" class="color-selector__list-item" data-value="1">black</li>
            <li slot="item" class="color-selector__list-item" data-value="2">red</li>
            <li slot="item" class="color-selector__list-item" data-value="3">green</li>
            <li slot="item" class="color-selector__list-item" data-value="4">blue</li>
            <li slot="item" class="color-selector__list-item" data-value="1">black</li>
            <li slot="item" class="color-selector__list-item" data-value="2">red</li>
            <li slot="item" class="color-selector__list-item" data-value="3">green</li>
        </color-selector> 
    </div> 
<template id="range">
  <div class="range__container">
    <input type="range" class="range__input range__input--min">
    <input type="range" class="range__input range__input--max">
  </div>
  <input type="number" class="range__value range__value--min">
  <input type="number" class="range__value range__value--max">
  <div class="range__track-disabler"></div>
  <div class="range__track"></div>
</template>
    <div class="pricerange__borders">
        <div class="pricerange__title">CENA</div>
        <price-range data-min="1" data-max="100" class="range"></price-range>
    </div>
</div>