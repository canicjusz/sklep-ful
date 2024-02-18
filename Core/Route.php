<?php
namespace Core;

class Route
{
  static protected $routes = [];
  static protected $dynamic_routes = [];
  public $middlewares = [];
  public $controller;
  public $path;
  public $method;

  public function __construct(string $method, string $path, object|array $controller)
  {
    $this->method = $method;
    $this->controller = $controller;
    $this->path = $path;
  }
  
  static public function &addDynamic(string $method, string $path, object|array $controller, array $parameter_constraints): Route {
    [$dynamic_path, $parameter_names, $parameter_suffixes] = Path::translateIntoRegex($path, $parameter_constraints);
    // $dynamic_path = preg_replace('/\//', '\\/', $path);
    //todo reverse the $path
    $new_route = new Route($method, $path, $controller);
    self::$dynamic_routes[$method][$dynamic_path] = ['route' => &$new_route, 'parameters' => array_combine($parameter_names, $parameter_suffixes)];
    return $new_route;
  }

  static public function &add(string $method, string $path, object|array $controller, array|null $parameter_constraints): Route {
    if($parameter_constraints){
      return self::addDynamic($method, $path, $controller, $parameter_constraints);
    }
    if (self::getNonDynamicRoute($path, $method)) {
      new \Exception("Can't reinstate route with the $method HTTP method and path '$path'.");
    }
    $new_route = new Route($method, $path, $controller);
    self::$routes[$method][$path] = &$new_route;
    return $new_route;
  } 

  static public function get(string $path, object|array $controller, array|null $parameter_constraints = null): Route
  {
      return self::add('GET', $path, $controller, $parameter_constraints);
  }

  static public function post(string $path, object|array $controller, array|null $parameter_constraints = null): Route
  {
      return self::add('POST', $path, $controller, $parameter_constraints);
  }

  static public function delete(string $path, object|array $controller, array|null $parameter_constraints = null): Route
  {
      return self::add('DELETE', $path, $controller, $parameter_constraints);
  }

  static public function patch(string $path, object|array $controller, array|null $parameter_constraints = null): Route
  {
      return self::add('PATCH', $path, $controller, $parameter_constraints);
  }

  static public function put(string $path, object|array $controller, array $parameter_constraints = null): Route
  {
      return self::add('PUT', $path, $controller, $parameter_constraints);
  }

  public function middleware(string $name): Route
  {
    $this->middlewares[] = $name;
    return $this;
  }

  static public function getNonDynamicRoute(string $path, string $method): Route|null {
    return self::$routes[$method][$path] ?? null;
  }

  static private function getParametersValue(array $matches, array $parameters): array{
    $paired_array = [];
    reset($parameters);
    for($i = 0; $i < count($matches); $i++){
      $parameter_suffix = current($parameters);
      $parameter_name = key($parameters);
      $match_no_trailing_slash = substr($matches[$i][0], 0, -1);
      $splitted_match = explode('/', $match_no_trailing_slash);
      $no_matches = count($splitted_match) === 1 && $splitted_match[0] === '';
      if($no_matches){
        $paired_array[$parameter_name] = null;
      }else{
        $ordered_matches = array_reverse($splitted_match);
        $is_parameter_array = $parameter_suffix === '[]' || $parameter_suffix === '[]?';
        $paired_array[$parameter_name] = $is_parameter_array ? $ordered_matches : $ordered_matches[0];
      }
      next($parameters);
    }
    return $paired_array;
  }

  static private function getDynamicRoute(string $path, string $method){
    $reversed_path = Path::reverse($path);
    foreach(self::$dynamic_routes[$method] as $pattern => ['route' => $route, 'parameters' => $parameters]){
      if(preg_match_all($pattern, $reversed_path, $matches)){
        $parts = array_slice($matches, 1);
        return [$route, $parameters, $parts];
      }
    }
    return null;
  }

  static public function resolve(string $uri, string $method): void
  {
    $path = Path::getFromURI($uri);
    if($route = self::getNonDynamicRoute($path, $method)){
      $request = new Request($path, $method);
    }else if($dynamic_route_attributes = self::getDynamicRoute($path, $method)){
      [$route, $parameters, $matches] = $dynamic_route_attributes;
      $parameters_value = self::getParametersValue($matches, $parameters);
      $request = new Request($path, $method, $parameters_value);
    }else{
      ErrorRoute::resolve(ErrorRoute::CODE_MAP['NOT_FOUND']);
      exit;
    }
    $method_upper = strtoupper($method);
    $exception = "The callback of the '$path' route using the $method_upper HTTP method must be either a closure or an array with a qualified name of class, followed by a method name.";
    Middleware::resolve($route->middlewares, $request);
    $callback = validateCallback($_ENV['CONTROLLER_NAMESPACE'], $route->controller, $exception);
    Renderer::renderPage($callback, $request);
  }
//todo: remove repeating templates
  // static public function redirect(string $path): void {
  //   header("Location: /{$_ENV['BASE_PATH']}$path", true, 404);
  // }
}