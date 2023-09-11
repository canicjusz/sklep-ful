<?php
namespace Core;

class ErrorRoute {
  const CODE_MAP = [
    'NOT_FOUND' => 404
  ] ;
  static protected $routes = [];
  public $middlewares = [];
  public $controller;

  public function __construct(int $code, object|array $controller)
  {
    $this->controller = $controller;
  }

  static public function &add(int $code, object|array $controller): ErrorRoute
  {
    if (self::getRoute($code)) {
      new \Exception("Can't reinstate route.");
    }

    $new_route = new ErrorRoute($code, $controller);
    self::$routes[$code] = &$new_route;

    return $new_route;
  }

  public function middleware(string $name): ErrorRoute
  {
    $this->middlewares[] = $name;
    return $this;
  }

  static public function getRoute(int $code){
    return self::$routes[$code] ?? null;
  }

  static public function resolve(int $code, $message = null): void
  {
    // dwd($_SERVER["SERVER_PROTOCOL"] . " " . ($message ?? $code);
    $message ??= $_SERVER["SERVER_PROTOCOL"] . " " . $code;
    header($message, true, $code);
    if(!$route = self::getRoute($code)){
      new \Exception("Error route for code $code doesn't exist.");
    }
    $exception = "The callback of the.";
    Middleware::resolve($route->middlewares);
    $callback = Resolver::validateCallback($_ENV['CONTROLLER_NAMESPACE'], $route->controller, $exception);
    call_user_func($callback);
  }
}