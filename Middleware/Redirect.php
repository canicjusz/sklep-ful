<?php

namespace Middleware;

use Models\Category as CategoryModel;
// use Core\Request;

class Redirect
{
  function category($request)
  {
    // if(!isset($request->parameters['category'])){
    //   $request->parameters['category'] = [1];
    //   return;
    // }
    $last_category = end($request->parameters['category']);

    $categories_ids = CategoryModel::getRootPath($last_category);
    $correct_ending = implode('/', $categories_ids);
    $new_path = '/catalog/' . $correct_ending;
    // $correct_ending = implode('/', $categories_ids);

    if ($new_path != $request->path) {
      $request->set_path($new_path);
      redirect($request->build_url(), 307);
    }
  }
}
