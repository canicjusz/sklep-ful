<?php

namespace Core;

class View
{
  const PATH = __DIR__ . "/../Views/";
  protected $dir;
  protected $file;

  public function __construct(string $file){
    $this->file = $file;
    $this->dir = '';
  }

  static public function open(string $file): self
  {
    $current_class = get_called_class();
    return new $current_class($file);
  }

  public function in(string $dir): self {
    $this->dir = $dir;
    return $this;
  }

  public function load(array $variables = []): void {
    $head = new Head();
    extract($variables);
    require static::PATH . $this->dir . '/' . $this->file;
    if(!$head->is_empty()){
      $head->render();
    }
  }

  public function renderString(array $variables = []): string{
    ob_start();
    $this->load($variables);
    return ob_get_clean();
  }
}