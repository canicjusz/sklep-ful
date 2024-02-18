<?php

namespace Core;

class Middleware
{
  static protected $middlewares = [];

  static public function add(string $name, object|array $middleware): void
  {
    if (Middleware::get($name)) {
      new \Exception("Can't reinstate middleware named '$name'.");
    }
    self::$middlewares[$name] = $middleware;
  }

  static public function get(string $name): object|array|null
  {
    return self::$middlewares[$name] ?? null;
  }

  static public function resolve(array $middlewares_to_be_called, $request): void
  {
    foreach ($middlewares_to_be_called as $name) {
      if (!Middleware::get($name)) {
        new \Exception("Middleware $name doesn't exist.");
      }
      $middleware = Middleware::get($name);
      $exception = "The callback of the '$name' middleware must be either a closure or an array with a qualified name of class, followed by a method name.";
      $callback = validateCallback($_ENV['MIDDLEWARE_NAMESPACE'], $middleware, $exception);
      call_user_func($callback, $request);
    }
  }
}
