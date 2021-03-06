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

  $url = "https://kissmanga.org/chapter/$name/chapter_$chapter";

  $html = file_get_contents($url);

  $content = get_inner_string($html, 'centerDivVideo', '</div>');

  $imgs = explode('src=', $content);
  $imgs = array_filter($imgs, function($img) {
  	return strpos($img, 'https://') !== false;
  });
  $imgs = array_map(function($img) {
  	return get_inner_string($img, '"', '"');
  }, $imgs);
  $imgs = array_values($imgs);


  $job['last_page'] = count($imgs);

  $image_url = $imgs[$page-1];

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
