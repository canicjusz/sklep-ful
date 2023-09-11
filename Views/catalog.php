<?php
  use Core\{Partial, Controller};
  use Controller\{Sidebar, CategoryDescription, CategorySelector, Banner, ProductDisplay};

  $sidebar = new Sidebar();
  $category_description = new CategoryDescription();
  $category_selector = new CategorySelector();
  $banner = new Banner();
  $product_display = new ProductDisplay();
  $categories = $request->parameters['category'];

  $head->css('catalog.css', true);
?>

<?php 
  Partial::open('header.php')->load();
  Partial::open('breadcrumbs.php')->load();
?>

<main>
  <?php $sidebar->index($categories, $request->input);
  ?>
  <div class="left-wrapper">
    <?php
      $parent_index = count($categories) - 2;
      $parent_id = $parent_index >= 0 ? $categories[$parent_index] : null;
      $current_id = end($categories);
      // dwd()
      $category_description->index($current_id);
      $category_selector->index($current_id, $parent_id);
      $banner->index();
      $product_display->index($request, $current_id);
    ?>
  </div>
</main>
<?php Partial::open('footer.php')->load(); ?>