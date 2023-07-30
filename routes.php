<?php
use Core\Route;

Route::get(
  '/user',
  // function () {
//   echo 'siema';
// }
  [User::class, 'index']
)->middleware('auth')->middleware('auth');

// Named routes allow the convenient generation of URLs or redirects for specific routes. You may specify a name for a route by chaining the name method onto the route definition:

//   Route::get('/user/profile', function () {
//       // ...
//   })->name('profile');

//   You may also specify route names for controller actions:

//   Route::get(
//       '/user/profile',
//       [UserProfileController::class, 'show']
//   )->name('profile');