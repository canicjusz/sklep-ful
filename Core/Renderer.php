<?php

namespace Core;

class Renderer {
  static private function getNodeSelectingQuery(\DOMElement $node): string {
    $query = $node->nodeName;
    foreach ($node->attributes as $attribute) {
      $query .= '[@' . $attribute->name . '="' . $attribute->value .'"]';
    }
    return $query;
  } 
  static public function renderPage($callback, Request $request): void {
    ob_start();
    call_user_func($callback, $request);
    $bodyContent = ob_get_clean();
    $doc = new \DOMDocument();
    @$doc->loadHTML("<!DOCTYPE html>
    <html>
      <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
      </head>
      <body>
        $bodyContent
      </body>
    </html>");
    $xpath = new \DOMXpath($doc);
    $body = $doc->getElementsByTagName('body')[0];
    $custom_heads = $doc->getElementsByTagName('custom-head');
    $head = $doc->getElementsByTagName('head')[0];
    while($custom_head = $custom_heads->item(0)){
      while($first_child = $custom_head->childNodes->item(0)){
        $old_node = $custom_head->removeChild($first_child);
        $query = self::getNodeSelectingQuery($old_node);
        $count_query = 'count('.$query.')';
        $similar_already_in_head = $xpath->evaluate($count_query, $head);
        if(!$similar_already_in_head){
          $head->appendChild($old_node);
        }
      }
      $custom_head->remove();
    }
    $root = $doc->documentElement;
    $root->insertBefore($head, $body);
    echo $doc->saveHTML();
  }
}