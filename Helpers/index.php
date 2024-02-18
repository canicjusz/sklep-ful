<?php
require 'debuggers.php';

function validateCallback(string $namespace, object|array $callback, string $exception_message)
{
  if (!is_callable($callback)) {
    if (!is_array($callback)) {
      throw new \Exception($exception_message);
    }
    if (count($callback) != 2) {
      throw new \Exception($exception_message);
    }
    $class_name = $callback[0];
    $method = $callback[1];
    $class_path = $namespace . $class_name;
    // dwd($class_path);
    $class = new $class_path();
    if (!method_exists($class, $method)) {
      throw new \Exception("'$method' doesn't exist on the $class class.");
    }
    $callback_array = [$class, $method];
  }
  $final_callback = $callback_array ?? $callback;
  if (!is_callable($final_callback)) {
    throw new \Exception($exception_message);
  }
  return $final_callback;
}
function local_photo($url)
{
  return $_ENV['BASE_PATH'] . '/' . $_ENV['PHOTO_PATH'] . '/' . $url;
}

function local_url($url)
{
  return $_ENV['BASE_PATH'] . '/' . $url;
}

function get_static($url)
{
  return $_ENV['BASE_PATH'] . '/' . $_ENV['STATIC_PATH'] . '/' . $url;
}

function redirect($url, $statusCode = 303)
{
  header('Location: ' . $url, true, $statusCode);
  die();
}
