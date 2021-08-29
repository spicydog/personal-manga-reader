<?php

require_once 'utils.inc';

// Code for test
// download([
//   'name' => 'one-piece',
//   'chapter' => '1000',
//   'page' => 1,
// ]);

function download($job) {

  $result = [
    'success' => false,
    'job' => $job,
    'image' => false,
  ];

  $name = $job['name'];
  $chapter = $job['chapter'];
  $page = $job['page'];

  $url = "https://readonepiece.in/manga/$name-chapter-$chapter/";
  $html = file_get_contents($url);
  $content = get_inner_string($html, 'margin-top: 0px; margin-bottom: 0px;', '</body>');

  $imgs = explode('src=', $content);
  $imgs = array_map_double_qoutes($imgs);
  $imgs = array_filter_contain_neddle($imgs, 'one-piece-');
  $imgs = array_filter_extensions($imgs, ['png', 'jpg', 'jpeg']);
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
