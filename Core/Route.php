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

  static public function &addDynamic(string $method, string $path, object|array $controller, array $parameters){
    // $dynamic_path = preg_replace('/\//', '\\/', $path);
    //todo reverse the $path
    $dynamic_path = "^".implode('/',array_reverse(explode('/', $path)))."$";
    preg_match_all('/\{([\w\d]+)(\[\])?/', $dynamic_path, $all_parameters);
    foreach($parameters as $parameter => $value){
      $parameter_pattern = "/\{".$parameter."(\[\]\?|\[\]|\?)?\}\//";
      preg_match_all($parameter_pattern, $dynamic_path, $matches);
      $matches_count = count($matches[0]);
      $suffix = $matches[1][0];
      if($matches_count > 1){
        throw new \Exception("parameter: $parameter occurs more than once in the $path path.");
      }else if($matches_count < 1){
        throw new \Exception("parameter: $parameter doesn't occur in the $path path.");
      }
      switch($suffix){
        case '':
          $dynamic_path = preg_replace($parameter_pattern, "(" . $value . "\/)" , $dynamic_path);
          break;
        case '?':
          $dynamic_path = preg_replace($parameter_pattern, "(" . $value . "\/)?" , $dynamic_path);
          break;
        case '[]':
          $dynamic_path = preg_replace($parameter_pattern, "((?:" . $value . "\/)+?)" , $dynamic_path);
          break;
        case '[]?':
          $dynamic_path = preg_replace($parameter_pattern, "((?:" . $value . "\/)+?)?" , $dynamic_path);
          break;
      }
    }
    $dynamic_path = preg_replace("/\{[\w\d]+\[\]\?\}\//", "((?:[\w\d]+\/)+?)?" , $dynamic_path);
    $dynamic_path = preg_replace("/\{[\w\d]+\[\]\}\//", "((?:[\w\d]+\/+?))" , $dynamic_path);
    $dynamic_path = preg_replace("/\{[\w\d]+\?\}\//", "([\w\d]+\/)?" , $dynamic_path);
    $dynamic_path = preg_replace("/\{[\w\d]+\}\//", "([\w\d]+\/)" , $dynamic_path);
    $dynamic_path = preg_replace("/(?<!\\\\)\//", "\/", $dynamic_path);
    $dynamic_path = '/'.$dynamic_path.'/';
    $new_route = new Route($method, $path, $controller);
    self::$dynamic_routes[$method][$dynamic_path] = ['route' => &$new_route, 'parameters' => array_combine($all_parameters[1], $all_parameters[2])];

    return $new_route;
  } 

  static private function &add(string $method, string $path, object|array $controller): Route
  {
    if (self::getNonDynamicRoute($path, $method)) {
      new \Exception("Can't reinstate route with the $method HTTP method and path '$path'.");
    }

    $new_route = new Route($method, $path, $controller);
    self::$routes[$method][$path] = &$new_route;
    return $new_route;
  }

  static public function getDynamic(string $path, object|array $controller, array $parameter_constrains = []): Route
  {
    return self::addDynamic('GET', $path, $controller, $parameter_constrains);
  }

  static public function postDynamic(string $path, object|array $controller, array $parameter_constrains = []): Route
  {
    return self::addDynamic('POST', $path, $controller, $parameter_constrains);
  }

  static public function deleteDynamic(string $path, object|array $controller, array $parameter_constrains = []): Route
  {
    return self::addDynamic('DELETE', $path, $controller, $parameter_constrains);
  }

  static public function patchDynamic(string $path, object|array $controller, array $parameter_constrains = []): Route
  {
    return self::addDynamic('PATCH', $path, $controller, $parameter_constrains);
  }

  static public function putDynamic(string $path, object|array $controller, array $parameter_constrains = []): Route
  {
    return self::addDynamic('PUT', $path, $controller, $parameter_constrains);
  }

  static public function get(string $path, object|array $controller): Route
  {
    return self::add('GET', $path, $controller);
  }

  static public function post(string $path, object|array $controller): Route
  {
    return self::add('POST', $path, $controller);
  }

  static public function delete(string $path, object|array $controller): Route
  {
    return self::add('DELETE', $path, $controller);
  }

  static public function patch(string $path, object|array $controller): Route
  {
    return self::add('PATCH', $path, $controller);
  }

  static public function put(string $path, object|array $controller): Route
  {
    return self::add('PUT', $path, $controller);
  }

  static public function getNonDynamicRoute($method, $path): Route|null {
    return self::$routes[$method][$path] ?? null;
  }

  static public function getRoute(string $path, string $method): array|null
  {
    if($nonDynamicRoute = self::getNonDynamicRoute($method, $path)){
      $request = new Request($path, $method);
      return [$nonDynamicRoute, $request];
    }
    $reversed_path = implode('/',array_reverse(explode('/', $path)));
    foreach(self::$dynamic_routes[$method] as $pattern => ['route' => $route, 'parameters' => $parameters]){
      if(preg_match_all($pattern, $reversed_path, $matches)){
        // dd($parameters, $matches);

        $paired_array = [];
        reset($parameters);
        for($i = 1; $i < count($matches); $i++){
          $parameter_suffix = current($parameters);
          $parameter_name = key($parameters);
          $matches_no_trailing_slash = substr($matches[$i][0], 0, -1);
          $splitted_matches = explode('/', $matches_no_trailing_slash);
          if(count($splitted_matches) == 1 && $splitted_matches[0] == ''){
            $paired_array[$parameter_name] = null;
          }else{
            $ordered_matches = array_reverse($splitted_matches);
            $paired_array[$parameter_name] = $parameter_suffix == '[]' ? $ordered_matches : $ordered_matches[0];
          }
          next($parameters);
        }
        $request = new Request($path, $method, $paired_array);
        return [$route, $request];
      }
    }
    return null;
  }

  public function middleware(string $name): Route
  {
    $this->middlewares[] = $name;
    return $this;
  }

  static public function resolve(string $path, string $method): void
  {
    // dwd(self::$routes);
    $clean_path = path_only($path);
    [$route, $request] = self::getRoute($clean_path, $method);
    if($route == null){
      ErrorRoute::resolve(ErrorRoute::CODE_MAP['NOT_FOUND']);
      exit;
    }
    $method_upper = strtoupper($method);
    $exception = "The callback of the '$path' route using the $method_upper HTTP method must be either a closure or an array with a qualified name of class, followed by a method name.";
    Middleware::resolve($route->middlewares, $request);
    $callback = Resolver::validateCallback($_ENV['CONTROLLER_NAMESPACE'], $route->controller, $exception);
    ob_start();
    call_user_func($callback, $request);
    $shit = ob_get_clean();
    $doc = new \DOMDocument();
    @$doc->loadHTML("<!DOCTYPE html>
    <html>
      <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
      </head>
      <body>
        $shit
      </body>
    </html>");
    $body = $doc->getElementsByTagName('body')[0];
    $custom_heads = $doc->getElementsByTagName('custom-head');
    $head = $doc->getElementsByTagName('head')[0];
    while($custom_head = $custom_heads->item(0)){
      while($first_child = $custom_head->childNodes->item(0)){
        $old_node = $custom_head->removeChild($first_child);
        $query = $old_node->nodeName;
        foreach ($old_node->attributes as $attribute) {
          $query .= '[@' . $attribute->name . '="' . $attribute->value .'"]';
        }
        // [@id="abc"][@for="xyz"]
        // if(prop_list_contains(new DOMXPath($dom), $head, $string))
        if(!(new \DOMXPath($doc))->evaluate($query, $head)['length']){
          $head->appendChild($old_node);
        }
      }
      $custom_head->parentNode->removeChild($custom_head);
    }
    $root = $doc->documentElement;
    $root->insertBefore($head, $body);
    echo $doc->saveHTML();
  }
//todo: remove repeating templates
  // static public function redirect(string $path): void {
  //   header("Location: /{$_ENV['HOME_PATH']}$path", true, 404);
  // }
}

function check_if_exists(DOMXPath $xp, $head, $prop, 
        array $required_matches){
    // Extract value from new node
    $compare = $xp->evaluate('string(@'.$required_matches[0].')', $prop);
    // Check for the value in the existing data
    $xpath = 'boolean(./property[@'. $required_matches[0] . ' = "' . $compare . '"])';

    return ( $xp->evaluate($xpath, $head) );
}