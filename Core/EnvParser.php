<?php

namespace Core;

class EnvParser
{
  static public function parse(string $filename)
  {
    $env = parse_ini_file(ROOT_PATH . DIRECTORY_SEPARATOR . $filename);
    foreach ($env as $env_variable => $env_value) {
      $_ENV[$env_variable] = $env_value;
    }
  }
}
