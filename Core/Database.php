<?php
namespace Core;

class Database extends \mysqli {
  private static $instance;

  public function __construct($host, $user, $password, $database)
  {
    parent::__construct($host, $user, $password, $database);
  }

  public static function getInstance(){
    return static::$instance;
  }

  public static function createInstance($host, $user, $password, $database){
    if(!isset(static::$instance)){
      static::$instance = new static($host, $user, $password, $database);
    }
  }
}