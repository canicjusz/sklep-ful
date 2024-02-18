<?php

namespace Models;

use Core\QueryBuilder;

class Navigation
{
  public static function generate($pages, $currentPage, $middle_count = 3)
  {
    if ($pages < 1) return [];
    $visiblePages = [$currentPage];
    while (count($visiblePages) < $pages && count($visiblePages) < $middle_count) {
      $firstElement = $visiblePages[0];
      $lastElement = end($visiblePages);
      if ($firstElement > 1) {
        array_unshift($visiblePages, $firstElement - 1);
      }
      if ($lastElement < $pages) {
        $visiblePages[] = $lastElement + 1;
      }
    }

    if ($visiblePages[0] == 2) {
      array_unshift($visiblePages, 1);
    } else if ($visiblePages[0] > 2) {
      array_unshift($visiblePages, 1, '...');
    }
    if (end($visiblePages) + 1 == $pages) {
      $visiblePages[] = $pages;
    } else if (end($visiblePages) + 1 < $pages) {
      array_push($visiblePages, '...', $pages);
    }
    return $visiblePages;
  }
}
