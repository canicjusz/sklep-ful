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
      $class = $namespace . $class_name;
      $controller = new $class();
      if (!method_exists($controller, $method)) {
        throw new \Exception("'$method' doesn't exist on the $class class.");
      }
      $callback_array = [$controller, $method];
    }
    if (!is_callable($callback_array ?? $callback)) {
      throw new \Exception($exception_message);
    }
    return $callback_array ?? $callback;
  }
}