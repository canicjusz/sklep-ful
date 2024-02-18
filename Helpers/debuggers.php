<?php

function dd(mixed ...$datas): void
{
  echo '<pre>';
  foreach($datas as $data){
    var_dump($data);
    // var_dump(', ');
  }
  echo '</pre>';
  echo '<br>';
  die;
}

function dwd(mixed ...$datas): void
{
  echo '<pre>';
  foreach($datas as $data){
    var_dump($data);
    // var_dump(', ');
  }
  echo '</pre>';
  echo '<br>';
}