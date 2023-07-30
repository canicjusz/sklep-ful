<?php

namespace Core;

const VIEW_PATH = __DIR__ . "/../Views/";

class View
{
  static public function load($name)
  {
    require VIEW_PATH . $name . '.php';
  }
}