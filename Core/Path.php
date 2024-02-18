<?php

namespace Core;

class Path {
  static private function translateConstrained(string $dynamic_path, string $path, array $parameter_constraints): string {
    foreach($parameter_constraints as $parameter => $constraint){
      $parameter_pattern = "/\{".$parameter."(\[\]\?|\[\]|\?)?\}\//";
      preg_match_all($parameter_pattern, $dynamic_path, $matches);
      $matches_count = count($matches[0]);
      $suffix = $matches[1][0];
      if($matches_count > 1){
        throw new \Exception("parameter: $parameter occurs more than once in the $path path.");
      }else if($matches_count < 1){
        throw new \Exception("parameter: $parameter doesn't occur in the $path path.");
      }
      switch($suffix){
        case '':
          $dynamic_path = preg_replace($parameter_pattern, "(" . $constraint . "\/)" , $dynamic_path);
          break;
        case '?':
          $dynamic_path = preg_replace($parameter_pattern, "(" . $constraint . "\/)?" , $dynamic_path);
          break;
        case '[]':
          $dynamic_path = preg_replace($parameter_pattern, "((?:" . $constraint . "\/)+?)" , $dynamic_path);
          break;
        case '[]?':
          $dynamic_path = preg_replace($parameter_pattern, "((?:" . $constraint . "\/)+?)?" , $dynamic_path);
          break;
      }
    }
    return $dynamic_path;
  }

  static private function translateNotConstrained(string $dynamic_path): string{
    $optional_multiple_pattern = "/\{[\w\d]+\[\]\?\}\//";
    $optional_multiple_replacement = "((?:[\w\d]+\/)+?)?";
    $multiple_pattern = "/\{[\w\d]+\[\]\}\//";
    $multiple_replacement = "((?:[\w\d]+\/+?))";
    $optional_single_pattern = "/\{[\w\d]+\?\}\//";
    $optional_single_replacement = "([\w\d]+\/)?";
    $single_pattern = "/\{[\w\d]+\}\//";
    $single_replacement = "([\w\d]+\/)";
    $escape_slash_pattern = "/(?<!\\\\)\//";
    $dynamic_path = preg_replace($optional_multiple_pattern, $optional_multiple_replacement , $dynamic_path);
    $dynamic_path = preg_replace($multiple_pattern, $multiple_replacement , $dynamic_path);
    $dynamic_path = preg_replace($optional_single_pattern, $optional_single_replacement , $dynamic_path);
    $dynamic_path = preg_replace($single_pattern, $single_replacement , $dynamic_path);
    $dynamic_path = preg_replace($escape_slash_pattern, "\/", $dynamic_path);
    return $dynamic_path;
  }

  static public function translateIntoRegex($path, $parameter_constraints): array {
    $dynamic_path = "^".implode('/',array_reverse(explode('/', $path)))."$";
    preg_match_all('/\{([\w\d]+)(\[\])?/', $dynamic_path, $name_suffix_pairs);
    $dynamic_path = self::translateConstrained($dynamic_path, $path, $parameter_constraints);
    $dynamic_path = self::translateNotConstrained($dynamic_path);
    $dynamic_path_regex = '/'.$dynamic_path.'/';
    $parameter_names = $name_suffix_pairs[1];
    $parameter_suffixes = $name_suffix_pairs[2];
    return [$dynamic_path_regex, $parameter_names, $parameter_suffixes];
  }

  public static function reverse(string $path): string{
    $array_path_reversed = array_reverse(explode('/', $path));
    $path_joined = implode('/', $array_path_reversed);
    return $path_joined;
  }

  public static function getFromURI(string $uri): string {
    $path = parse_url($uri, PHP_URL_PATH);

    $splitted_path = explode('/', $path);
    $last_part = end($splitted_path);
    $is_redundant_ending_slash = $last_part === '';
    $contains_php_document = str_ends_with($last_part, '.php');
    if ($is_redundant_ending_slash) {
      array_pop($splitted_path);
    }
    if ($contains_php_document) {
      array_pop($splitted_path);
    }
  
    $joined_path = join('/', $splitted_path);
    $path_wo_base = substr($joined_path, strlen($_ENV['BASE_PATH']));
    return empty($path_wo_base) ? '/' : $path_wo_base;
  }
}