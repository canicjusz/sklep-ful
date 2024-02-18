<?php
namespace Models;

use Core\QueryBuilder;

class ProductDescription {
  public static function getGeneralDescription($product_id){
    $result = QueryBuilder::select(['description', 'video_url'])
      ->from('product')
      ->where('ID=?', [$product_id])
      ->execute(); 
    // $query = "SELECT description, video_url FROM product WHERE ID=?;";
    // $result = Database::getInstance()->execute_query($query, [$product_id]);
    $fetched = $result->fetch_assoc();
    return $fetched;
  }

  public static function getParameters($product_id){
    $result = QueryBuilder::select(['p.key', 'p_p.value'])
      ->from('parameter', 'p')
      ->join('product_parameter', 'p_p', 'p.ID = p_p.parameter_ID')
      ->where('p_p.product_ID=?', [$product_id])
      ->execute(); 
    // $query = "SELECT p.key, p_p.value FROM `parameter` as p JOIN `product_parameter` as p_p ON p.ID = p_p.parameter_ID WHERE p_p.product_ID=?;";
    // $result = Database::getInstance()->execute_query($query, [$product_id]);
    $parameters = [];
    while ($parameter = $result->fetch_assoc())
    {
      $parameters[] = $parameter;
    }
    return $parameters;
  }
}