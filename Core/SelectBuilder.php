<?php

namespace Core;

use Core\Database;

class SelectBuilder
{
  private $statement;
  private $columns;
  private $table;
  private $restrictions;
  private $joins;
  private $group_by;
  private $order_by;
  private $limit;
  private $having;
  private $wrapper_start;
  private $wrapper_end;

  public function  __construct(string $statement, string $columns, string $wrapper_start, string $wrapper_end)
  {
    $this->statement = $statement;
    $this->columns = $columns;
    $this->table = '';
    $this->restrictions = '';
    $this->joins = '';
    $this->group_by = '';
    $this->order_by = '';
    $this->limit = '';
    $this->having = '';
    $this->wrapper_start = $wrapper_start;
    $this->wrapper_end = $wrapper_end;
  }

  private static function convertToSubquery(SelectBuilder $instance)
  {
    $subquery = $instance->getQuery();
    return "($subquery)";
  }

  // public static function with(string $name){
  //   return new static('with', implode(',', $columns));
  // }

  // public static function 

  public static function createInstance(array $columns, array|NULL $variables = NULL, string $wrapper_start = '', string $wrapper_end = '')
  {
    if (array_is_list($columns)) {
      return new static('select', implode(',', $columns), $wrapper_start, $wrapper_end);
    };
    $column_aliases = array_keys($columns);
    $selectable = array_reduce($column_aliases, function ($carry, $alias) use ($columns) {
      $column = $columns[$alias];
      if ($column instanceof SelectBuilder) {
        $column = static::convertToSubquery($column);
      }
      if (!is_string($alias)) {
        $carry .= ',' . $column;
      } else {
        $carry .= ',' . $column . " as " . $alias;
      }
      return $carry;
    }, '');
    $selectable_wo_inital_coma = substr($selectable, 1);
    if (isset($variables)) {
      return new static('select', static::sanitizeExcerpts($selectable_wo_inital_coma, $variables), $wrapper_start, $wrapper_end);
    }
    return new static('select', $selectable_wo_inital_coma, $wrapper_start, $wrapper_end);
  }

  public function from(string|SelectBuilder $table, string|NULL $alias = NULL)
  {
    if ($table instanceof SelectBuilder) {
      $table = static::convertToSubquery($table);
    }
    $query_excerpt = isset($alias) ? "$table as $alias" : $table;
    $this->table = " FROM $query_excerpt";
    return $this;
  }

  private static function sanitizeExcerpts(string $query_excerpt, array $variables)
  {
    foreach ($variables as $variable) {
      if (!is_scalar($variable)) {
        continue;
      }
      $sanitized_variable = Database::getInstance()->real_escape_string($variable);
      $pos = strpos($query_excerpt, "?");
      if ($pos !== false)
        $query_excerpt = ' ' . substr_replace($query_excerpt, $sanitized_variable, $pos, 1) . ' ';
    }
    return $query_excerpt;
  }

  public function where(string $condition, array|NULL $variables = NULL, string|NULL $prefix = NULL)
  {
    if (!isset($variables) || count($variables) < 1) {
      $this->restrictions .= " " . ($prefix ?? 'WHERE') . " " . $condition;
      return $this;
    }
    $this->restrictions .= " " . ($prefix ?? 'WHERE') . " " . static::sanitizeExcerpts($condition, $variables);
    return $this;
  }

  public function orWhere(string $condition, array|NULL $variables = NULL)
  {
    return $this->where($condition, $variables, 'OR');
  }

  public function andWhere(string $condition, array|NULL $variables = NULL)
  {
    return $this->where($condition, $variables, 'AND');
  }

  public function optionalWhere(string $condition, array $variables, string|NULL $prefix = NULL)
  {
    if (!isset($variables[0]) || substr_count($condition, '?') < count($variables)) {
      return $this;
    }
    $this->restrictions .= " " . ($prefix ?? 'WHERE') . " " . static::sanitizeExcerpts($condition, $variables);
    return $this;
  }

  public function orOptionalWhere(string $condition, array $variables)
  {
    return $this->optionalWhere($condition, $variables, 'OR');
  }

  public function andOptionalWhere(string $condition, array $variables)
  {
    return $this->optionalWhere($condition, $variables, 'AND');
  }

  // private function joinSubquery($instance, $alias, $condition, $type){
  //   $subquery = $instance->execute();
  //   $this->joins .= " $type join ($subquery) " . $alias ?? "" . ' on ' . $condition;
  // }

  public function join(string|SelectBuilder $table, string $alias, string $condition, string $type = 'INNER')
  {
    if ($table instanceof SelectBuilder) {
      $table = static::convertToSubquery($table);
    }
    $this->joins .= " $type JOIN $table " . ($alias ? " as $alias " : "") . ' on ' . $condition;
    return $this;
  }

  public function leftJoin(string|SelectBuilder $table, string $alias, string $condition)
  {
    return $this->join($table, $alias, $condition, 'LEFT');
  }

  public function rightJoin(string|SelectBuilder $table, string $alias, string $condition)
  {
    return $this->join($table, $alias, $condition, 'RIGHT');
  }

  public function groupBy(string ...$columns)
  {
    $this->group_by = " GROUP BY " . implode(',', $columns);
    return $this;
  }

  public function orderBy(string $column, string $order = '')
  {
    $this->order_by = " ORDER BY $column $order";
    return $this;
  }

  public function limit(string $from, string $to = '')
  {
    $sanitized_from = Database::getInstance()->real_escape_string($from);
    $sanitized_to = Database::getInstance()->real_escape_string($to);
    $this->limit = " LIMIT $sanitized_from" . ($to ? ',' . $sanitized_to : '');
    return $this;
  }

  public function having(string $condition)
  {
    $this->having = " HAVING $condition";
    return $this;
  }

  public function getQuery()
  {
    $query = $this->wrapper_start . "SELECT " . $this->columns . $this->table . $this->joins . $this->restrictions . $this->group_by . $this->having . $this->order_by . $this->limit . $this->wrapper_end;
    return $query;
  }

  public function execute()
  {
    $query = $this->getQuery();
    return Database::getInstance()->query($query);
  }
}

// QueryBuilder::select(['array' => 'GROUP_CONCAT(p.ID)'])
//   ->from('product', 'p')
//   ->join(
//     QueryBuilder::select(['product_ID', 'category_ID'])
//       ->optionalWhere('product_category.category_ID IN ?', $children_categories_array)
//       ->join('category', 'c', 'c.ID = p_c.category_ID')
//       ->join(
//         QueryBuilder::select(['ID'])
//           ->from('manufacturer')
//           ->optionalWhere('product_category.category_ID IN ?', $children_categories_array),
//         'm',
//         'm.ID = p.manufacturer_ID'
//       )
//       ->where('p.visible = true')
//       ->mandatory_and('p.stock > 0'),
//     'p_c',
//     'p_c.product_ID = p.ID'
//   )
//   ->leftJoin('product_flag', 'p_f', 'p_f.product_ID = sq.ID')
//   ->join('flag', 'fl', 'p_f.flag_ID = fl.ID')
//   ->groupBy('sq.ID')

// SELECT GROUP_CONCAT(p.ID) as array
//     FROM product as p
//     JOIN
//   (SELECT product_ID, category_ID FROM product_category".Database::optional("WHERE product_category.category_ID IN ?", $children_categories_array).") as p_c ON p_c.product_ID = p.ID
//             JOIN category as c ON c.ID = p_c.category_ID
//             JOIN 
//               (SELECT ID FROM manufacturer".Database::optional("WHERE manufacturer.ID = ?", $manufacturer).") as m ON m.ID = p.manufacturer_ID
//             WHERE p.visible = true AND p.stock > 0
//             )
//     LEFT JOIN product_flag as p_f on p_f.product_ID = sq.ID
//     JOIN flag as fl on p_f.flag_ID = fl.ID GROUP BY sq.ID) as xD