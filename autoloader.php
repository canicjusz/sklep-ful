<?php
function my_autoloader($class_name)
{
  $path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
  // echo __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
  include __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
}
// DIRECTORY_SEPARATOR

spl_autoload_register('my_autoloader');