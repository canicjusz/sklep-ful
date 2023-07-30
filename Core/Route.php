<?php
namespace Core;

const CONTROLLER_NAMESPACE = '\\Controller\\';

class Route
{
  static protected $routes = [];
  public $middlewares = [];
  public $controller;
  public $uri;
  public $method;

  public function __construct(string $method, string $uri, object|array $controller)
  {
    $this->method = $method;
    $this->uri = $uri;
    $this->controller = $controller;
  }

  static private function add(string $method, string $uri, object|array $controller): Route
  {
    if (isset(self::$routes[$method][$uri])) {
      new \Exception("Can't reinstate route with the $method HTTP method and path '$uri'.");
    }

    self::$routes[$method][$uri] = new Route($method, $uri, $controller);

    return self::$routes[$method][$uri];
  }

  static public function get(string $uri, object|array $controller): Route
  {
    return self::add('GET', $uri, $controller);
  }

  static public function post(string $uri, object|array $controller): Route
  {
    return self::add('POST', $uri, $controller);
  }

  static public function delete(string $uri, object|array $controller): Route
  {
    return self::add('DELETE', $uri, $controller);
  }

  static public function patch(string $uri, object|array $controller): Route
  {
    return self::add('PATCH', $uri, $controller);
  }

  static public function put(string $uri, object|array $controller): Route
  {
    return self::add('PUT', $uri, $controller);
  }

  static public function getRoute(string $uri, string $method): Route
  {
    return self::$routes[$method][$uri];
  }

  public function middleware(string $name)
  {
    $this->middlewares[] = $name;
    return $this;
  }

  static public function resolve(string $uri, string $method)
  {
    $clean_path = path_only($uri);
    dd($clean_path);
    $route = self::getRoute($clean_path, $method);
    $method_upper = strtoupper($method);
    $exception = "The callback of the '$uri' route using the $method_upper HTTP method must be either a closure or an array with a qualified name of class, followed by a method name.";
    Middleware::resolve($route->middlewares);
    $callback = Resolver::validateCallback(CONTROLLER_NAMESPACE, $route->controller, $exception);
    call_user_func($callback);
  }
}