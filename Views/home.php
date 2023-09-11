<?php 
  use Core\{Partial, Controller};
  use Controller\{Gallery, Featured, AboutUs};

  $gallery = new Gallery();
  $featured = new Featured();
  $about_us = new AboutUs();
  $head->css('home.css', true)->title('Welcome home');
?>
<?php Partial::open('header.php')->load(); ?>
<main>
  <?php
    $gallery->index();
    $featured->index();
  ?>
</main>
  <?php
    $about_us->index();
    // Controller::resolve([AboutUs::class, 'index']);
    Partial::open('footer.php')->load();
  ?>
