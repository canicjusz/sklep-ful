<?php 
  $head
    ->css('sidebar.css', true)->css('accordion.css', true)
    ->css('applied_filters.css', true)->css('manufacturer_checkbox.css', true)
    ->css('color_selector.css', true)->css('price_range.css', true)
    ->script('accordion.js', true)->script('applied_filters.js', true)
    ->script('manufacturer_checkbox.js', true)->script('color_selector.js', true)
    ->script('price_range.js', true);
  // dwd($categories);
  define('MAX_VISIBLE_MANUFACTURERS', 5);
  $extractedData = $request->misc['extracted_data'];
  $selected_colors = $extractedData['colors'];
  $manufacturer_request = clone $request;
  $manufacturer_keys = array_keys($manufacturers);
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

<div class="filtry">
  <h2 class="appliedfilters__text">FILTRY</h2>
  <?php foreach($filters as $filter_id => $filter): ?>
    <div class="filter" id="filter-<?= $filter_id ?>">
      <h3 class="filter__title"><?= $filter['name'] ?></h3>
      <?php 
        switch($filter['type']):
          case 'color': ?>
            <color-selector data-selected="<?= $selected_colors ?>" class="color-selector">
              <?php foreach($filter['values'] as $value_id => $value): ?>
                <li slot="item" class="color-selector__list-item" data-value-id="<?= $value_id ?>" data-value="<?= $value ?>"></li>
              <?php endforeach; ?>
            </color-selector>
      <?php
        break; endswitch; ?>
    </div>
  <?php endforeach; ?>
</div>
<!-- <div class="filtry-container">
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
</div> -->

<div class="producent">

    <div class="producent__title">Producent</div>

        <?php
        for($i = 0; $i < MAX_VISIBLE_MANUFACTURERS && $i < count($manufacturers); $i++): $manufacturer = $manufacturers[$manufacturer_keys[$i]]; ?>
          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa"><?= $manufacturer['name'] ?> <span><?=$manufacturer['products_amount'] ?></span></div>
          </div>
        <?php endfor; ?>
      <?php if (MAX_VISIBLE_MANUFACTURERS === $i): ?>
      <div class="show-more">
          <p id="showMoreButton">więcej (<?= $i - MAX_VISIBLE_MANUFACTURERS ?>)</p>
      </div>
      <?php endif; ?>
      <?php for(; $i < count($manufacturers); $i++): $manufacturer = $manufacturers[$manufacturer_keys[$i]]; ?>
          <div class="producent__parametr">
            <div class="producent__checkbox">
              <label class="checkbox__container">
                  <input type="checkbox">
                  <span class="checkmark">
                      <span class="checkmark2"></span>
                  </span>
              </label>
            </div>
            <div class="producent__nazwa"><?= $manufacturer['name'] ?> <span><?=$manufacturer['products_amount'] ?></span></div>
          </div>
        <?php endfor; ?>

</div>
    <div class="pricerange__borders">
        <div class="pricerange__title">CENA</div>
        <price-range data-bottom-value="<?= $price_range['min'] ?>" data-top-value="<?= $price_range['max'] ?>" class="range"></price-range>
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
<template id="range">
  <div class="range__container">
    <input type="range" class="range__input range__input--bottom">
    <input type="range" class="range__input range__input--top">
  </div>
  <input type="number" class="range__value range__value--bottom">
  <input type="number" class="range__value range__value--top">
  <div class="range__track-disabler"></div>
  <div class="range__track"></div>
</template>