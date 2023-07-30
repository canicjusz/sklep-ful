<?php
namespace Core;

const MIDDLEWARE_NAMESPACE = '\\Middleware\\';

class Middleware
{
  static protected $middlewares = [];

  static public function add(string $name, $middleware)
  {
    if (isset(self::$middlewares[$name])) {
      new \Exception("Can't reinstate middleware named '$name'.");
    }
    self::$middlewares[$name] = $middleware;
  }

  static public function get(string $name)
  {
    return self::$middlewares[$name];
  }

  static public function exists(string $name)
  {
    return (bool) self::get($name);
  }

  static public function resolve($middlewares_to_be_called)
  {
    foreach ($middlewares_to_be_called as $name) {
      if (!Middleware::exists($name)) {
        new \Exception("Middleware $name doesn't exist.");
      }
      $middleware = Middleware::get($name);
      $exception = "The callback of the '$name' middleware must be either a closure or an array with a qualified name of class, followed by a method name.";
      $callback = Resolver::validateCallback(MIDDLEWARE_NAMESPACE, $middleware, $exception);
      call_user_func($callback);
    }
  }
}