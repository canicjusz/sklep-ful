<?php
namespace Core;

class Database extends \mysqli {
  private static $instance;

  private function __construct(string $host, string $user, string $password, string $database)
  {
    parent::__construct($host, $user, $password, $database);
  }

  public static function getInstance(): \mysqli {
    return static::$instance;
  }

  public static function createInstance(string $host, string $user, string $password, string $database): void {
    if(!isset(static::$instance)){
      static::$instance = new static($host, $user, $password, $database);
    }
  }

  public static function optional(string $query_excerpt, string|null ...$variables): string {
    if(!isset($variables[0]) || $variables[0] === ''){
      return '';
    }
    foreach($variables as $variable){
      $sanitized_variable = static::getInstance()->real_escape_string($variable);
      $pos = strpos($query_excerpt, "?");
      $query_excerpt = ' '.substr_replace($query_excerpt, $sanitized_variable, $pos, 1).' ';
    }
    return $query_excerpt;
  }
}