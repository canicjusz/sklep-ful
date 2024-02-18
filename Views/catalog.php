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
  $categories_count = count($categories);
  $current_id = $categories[$categories_count - 1];
?>

<?php 
  Partial::open('header.php')->load();
  Partial::open('breadcrumbs.php')->load();
?>

<main>
  <?php 
    // dwd('xD',$colors);
  // $sidebar->index($categories, $request->input);
  $sidebar->index($request, $categories, $current_id);
  ?>
  <div class="left-wrapper">
    <?php
      $parent_index = count($categories) - 2;
      $parent_id = $parent_index >= 0 ? $categories[$parent_index] : null;
      // dwd()
      $category_description->index($current_id);
      $category_selector->index($current_id, $parent_id);
      $banner->index();
      $product_display->index($request);
    ?>
  </div>
</main>
<?php Partial::open('footer.php')->load(); ?>