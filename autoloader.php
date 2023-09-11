<?php
function my_autoloader($class_name)
{
  $path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
  // echo __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
  $file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
  if(file_exists($file)){
    include $file;
  }
}
// DIRECTORY_SEPARATOR

spl_autoload_register('my_autoloader');