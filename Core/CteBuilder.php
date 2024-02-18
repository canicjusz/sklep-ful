<?php

namespace Core;

use Core\{Database, SelectBuilder};

class CteBuilder
{
  private $name;
  private $expression;
  private $select;
  private $is_recursive;

  public function __construct(string $name)
  {
    $this->name = $name;
    $this->expression;
    $this->select;
    $this->is_recursive;
  }

  public static function createInstance(string $name)
  {
    return new static($name);
  }

  public function recursive(SelectBuilder $anchor, SelectBuilder $recursive)
  {
    $this->is_recursive = true;
    $anchor_query = $anchor->getQuery();
    $recursive_query = $recursive->getQuery();
    $this->expression = "$anchor_query UNION ALL $recursive_query";
    return $this;
  }

  public function following(SelectBuilder $selectStatement)
  {
    $this->select = $selectStatement->getQuery();
    return $this;
  }

  public function getQuery()
  {
    $query = "WITH " . ($this->is_recursive ? "recursive " : '') . $this->name . " AS (" . $this->expression . ") " . $this->select;
    return $query;
  }

  public function execute()
  {
    $query = $this->getQuery();
    return Database::getInstance()->query($query);
  }
}
