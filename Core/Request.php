<?php
namespace Core;

class Request {
  public $parameters;
  public $path;
  public $method;
  public $input;

  public function __construct(string $path, string $method, array $parameters = []){
    global ${'_' . $method};
    $this->input = self::splitQueryVariables(${'_' . $method});
    $this->path = $path;
    $this->method = $method;
    $this->parameters = $parameters;
  }

  // static private function optionalParameters($value, $parameter) {
  //   return str_ends_with($value, '?}');
  // }


  static public function splitQueryVariables(array $queryVariables){
    array_walk($queryVariables, function (&$value, $key){
      if(is_array($value) && count($value) === 1){
        $value = explode(',', $value[0]);
      }
    });
    return $queryVariables;
  }

  public function set_and_build($key, $value){
    $this->set_parameter($key, $value);
    return $this->build_url();
  }

  public function set_parameter($key, $value){
    $this->input[$key] = $value;
  }

  public function set_path($value){
    $this->path = $value;
  }

  public function build_url(){
    $url = '/'. $_ENV['HOME_PATH'] . $this->path;
    return empty($this->input) ? $url : $url . '?' . http_build_query($this->input);
  } 

  // static public function
}