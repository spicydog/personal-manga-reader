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


function download($job) {

  $result = [
    'success' => false,
    'job' => $job,
    'image' => false,
  ];

  $name = $job['name'];
  $chapter = $job['chapter'];
  $page = $job['page'];

  $url = "http://www.mangapanda.com/$name/$chapter/$page";

  $html = @file_get_contents($url);

  $last_page = intval(get_inner_string($html, '</select> of ', '</div>'));
  if ($last_page > 0) {
    $job['last_page'] = $last_page;
  }

  $image_url = get_inner_string($html,'id="img"','alt=');
  $image_url = substr($image_url, strpos($image_url, 'http://') );
  $image_url = substr($image_url, 0, strpos($image_url, '"') );

  if (strlen($image_url) > 10) {
    $image = file_get_contents($image_url);
    if (strlen($image) > 100) {
      $result['success'] = true;
      $result['image'] = $image;
      $result['job'] = $job;
    }
  }
  return $result;
}
