<?php
namespace Middleware;

// use Models\ExtractData as RedirectCategoryModel;
// use Core\Request;

class ExtractData
{
  function catalog($request)
  {
    $request->set_additional_data('extracted_data', 
    [
      'manufacturer' => $request->input['manufacturer'] ?? NULL,
      'colors' => $request->input['colors'] ?? NULL,
      'min_price' => $request->input['min'] ?? 0,
      'max_price' => $request->input['max'] ?? 1000,
      'pp' => $request->input['pp'] ?? 5,
      'page' => $request->input['page'] ?? 1,
      'display' => $request->input['display'] ?? 'grid',
      'order_by' => $request->input['order'] ?? ''
    ]);
    return;
  }
}