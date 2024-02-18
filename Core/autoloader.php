<?php
function my_autoloader($class_with_namespace)
{
  $correct_slashes = str_replace('\\', DIRECTORY_SEPARATOR, $class_with_namespace);
  // echo __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
  $filepath = ROOT_PATH . DIRECTORY_SEPARATOR . $correct_slashes . '.php';
  //echo $file;
  if (file_exists($filepath)) {
    include $filepath;
  }
}
// DIRECTORY_SEPARATOR

spl_autoload_register('my_autoloader');
