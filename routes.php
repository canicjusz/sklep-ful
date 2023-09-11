<?php
use Core\{Route, View, ErrorRoute, Request};
// use Controller\UserController;
Route::get(
  '/',
  // function () {
//   echo 'siema';
// }
  [Home::class, 'index']
);

Route::getDynamic(
  '/catalog/{category[]}',
  [Catalog::class, 'index'],
  ['category' => '[0-9]+']
)->middleware('redirectCategory');

// Route::get(
//   '/product/{category[]?}/{product}',
//   [Product::class, 'index']
// );

// http://localhost/sklep-ful/product/3/65/654/elo/2/34/hej
// http://localhost/sklep-ful/product/3/65/654/elo/2/34/434/hej
// http://localhost/sklep-ful/product/3/65/654/elo
// /2/34/434434/54/hej
// http://localhost/sklep-ful/product/3/65/654/elo/434/hej/12

// ^((?:\/\d+))((?:\/\d+)+)((?:\/[a-zA-Z]+)+)?((?:\/\d+)+)$
// /233/4234/3213/fdf/gdfg/4234/4234/34/3213
// trzeba reversowac regex i url i dodawac lazy loading do kazdego multi selecta
// ^((?:\/\d+)+)((?:\/[a-zA-Z]+)+)?((?:\/\d+)+?)?((?:\/\d+)+?)((?:\/\d+)+?)?$
// /3213/34/4234/4234/gdfg/fdf/4234/4324/435/233/4324
// ((?:\/\d+)) - parametr dynamiczny
// ((?:\/\d+))? - parametr dynamiczny opcjonalny
// ((?:\/\d+)+?) - multi select parametr
// ((?:\/\d+)+?)? - multi select parametr opcjonalny

Route::getDynamic(
  '/product/{category[]?}/{product}',
  [Product::class, 'index'],
  ['category' => '[0-9]+', 'product' => '[0-9]+']
);

ErrorRoute::add(
  ErrorRoute::CODE_MAP['NOT_FOUND'],
  [View::open('404.php'), 'load']
);

// Named routes allow the convenient generation of URLs or redirects for specific routes. You may specify a name for a route by chaining the name method onto the route definition:

//   Route::get('/user/profile', function () {
//       // ...
//   })->name('profile');

//   You may also specify route names for controller actions:

//   Route::get(
//       '/user/profile',
//       [UserProfileController::class, 'show']
//   )->name('profile');