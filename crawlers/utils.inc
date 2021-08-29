<?php

function get_inner_string($string, $begin, $end) {
  if($begin === 0)
    return substr( $string, 0, strpos($string, $end) );
  if($end === 0)
    return substr($string, strpos($string, $begin) + strlen($begin));

  $string = ' ' . $string;
  $init = strpos($string, $begin);

  if ($init == 0)
    return '';

  $init += strlen($begin);
  $len = strpos($string, $end, $init) - $init;

  return substr($string, $init, $len);
}

function array_filter_extensions($array, $extensions) {
  return array_filter($array, function($item) use ($extensions) {
    $found = false;
    foreach($extensions as $extension) {
      $extension = '.' . $extension;
      $extension = str_replace('..', '.', $extension);
      if (strpos($item, strtolower($extension)) !== false) {
        $found = true;
        break;
      }
      if (strpos($item, strtoupper($extension)) !== false) {
        $found = true;
        break;
      }
    }
    return $found;
  });
}

function array_filter_contain_neddle($array, $neddle) {
  return array_filter($array, function($item) use ($neddle) {
    return strpos($item, $neddle) !== false;
  });
}

function array_map_double_qoutes($array) {
  return array_map(function($img) {
    return get_inner_string($img, '"', '"');
  }, $array);
}