<?php
namespace Core;

class Resolver
{

  static public function validateCallback(string $namespace, object|array $callback, string $exception_message)
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
    if (!is_callable($callback_array ?? $callback)) {
      throw new \Exception($exception_message);
    }
    return $callback_array ?? $callback;
  }
}