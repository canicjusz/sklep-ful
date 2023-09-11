<?php
namespace Middleware;

use Models\RedirectCategory as RedirectCategoryModel;
// use Core\Request;

class Redirect
{
  function category($request)
  {
    // dwd($request->parameters);
    $last_category = end($request->parameters['category']);
    // dwd($request);
    $categories_ids = RedirectCategoryModel::getParentCategories($last_category);
    $correct_ending = implode('/', $categories_ids);
    $new_path = '/catalog/' . $correct_ending;
    // $correct_ending = implode('/', $categories_ids);
    // dwd($new_path, $request->path);
    dwd($categories_ids);
    if($new_path != $request->path){
      $request->set_path($new_path);
      redirect($request->build_url(), 307);
    }
  }
}