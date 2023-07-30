<?php
function path_only($url)
{
  $path = parse_url($url, PHP_URL_PATH);
  $splitted_path = explode('/', $path);
  $last_part = end($splitted_path);
  if (str_ends_with($last_part, '.php')) {
    array_pop($splitted_path);
  }
  if ($last_part == '/') {
    array_pop($splitted_path);
  }
  return '/' . join($splitted_path);
}