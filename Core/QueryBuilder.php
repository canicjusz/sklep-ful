<?php

namespace Core;

use Core\{SelectBuilder, CteBuilder};

class QueryBuilder
{

  public static function select(array $columns, array|NULL $variables = NULL)
  {
    return SelectBuilder::createInstance($columns, $variables);
  }

  public static function with(string $name)
  {
    return CteBuilder::createInstance($name);
  }

  public static function exists(string $table, string $alias)
  {
    return SelectBuilder::createInstance(['*'], [], 'EXISTS(', ')')->from($table, $alias);
  }

  public static function count(string|SelectBuilder $table)
  {
    return SelectBuilder::createInstance(['count' => 'COUNT(*)'])->from($table, 'countingSubquery');
  }
}
