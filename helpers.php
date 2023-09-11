<?php
function path_only($url)
{
  $path = parse_url($url, PHP_URL_PATH);

  $splitted_path = explode('/', $path);
  $last_part = end($splitted_path);
  if ($last_part == '') {
    array_pop($splitted_path);
  }
  if (str_ends_with($last_part, '.php')) {
    array_pop($splitted_path);
  }

  $cleaned_path = substr(join('/', $splitted_path), strlen($_ENV['HOME_PATH'])+1);
  return empty($cleaned_path) ? '/' : $cleaned_path;
}

function local_photo($url){
  return '/' . $_ENV['HOME_PATH'] . '/' . $_ENV['PHOTO_PATH'] . '/' . $url;
}

function local_url($url){
  return '/' . $_ENV['HOME_PATH'] . '/' . $url;
}

function get_static($url){
  return '/' . $_ENV['HOME_PATH'] . '/' . $_ENV['STATIC_PATH'] . '/' . $url;
}

function redirect($url, $statusCode = 303)
{
   header('Location: ' . $url, true, $statusCode);
   die();
}