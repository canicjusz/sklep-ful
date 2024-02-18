<?php

namespace Core;

class Head {
  protected $content;
  protected $title;
  protected $description;

  public function __construct() {
    $this->content = '';
    $this->title = '';
    $this->description = '';
  }

  public function title(string $title){
    $this->title = $title;
    return $this;
  }

  public function description(string $description){
    $this->description = $description;
    return $this;
  }

  public function script(string $src, bool $local = false){
    if($local){
      $src = $_ENV['BASE_PATH'] . '/' . $_ENV['JS_PATH'] . '/' . $src;
    }
    $this->content .= "<script src='$src' defer></script>";
    return $this;
  }

  public function js(...$args){
    return $this->script(...$args);
  }

  public function css(string $src, bool $local = false){
    if($local){
      $src = $_ENV['BASE_PATH'] . '/' . $_ENV['CSS_PATH'] . '/' . $src;
    }
    $this->content .= "<link type='text/css' rel='stylesheet' href='$src' />";
    return $this;
  }

  public function is_empty(){
    return empty($this->content) && empty($this->title) && empty($this->description);
  }
  public function render(){
    if(!empty($this->description)){
      $this->content .= "<meta name='description' content='$this->description'>";
    }
    if(!empty($this->title)){
      $this->content .= "<title>$this->title</title>";
    }
    echo "<custom-head>$this->content</custom-head>";
  }
}