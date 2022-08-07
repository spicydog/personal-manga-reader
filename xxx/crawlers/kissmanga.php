<?php

require_once 'utils.inc';

// Code for test
// download([
//   'name' => 'manga-cg980189',
//   'chapter' => '1',
//   'page' => 1,
// ]);
// download([
//   'name' => 'read_one_piece_manga_online_free4',
//   'chapter' => '996',
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

  $url = "https://kissmanga.org/chapter/$name/chapter-$chapter";
  $html = @file_get_contents($url);
  if (strlen($html) === 0) {
    $url = "https://kissmanga.org/chapter/$name/chapter_$chapter";
    $html = @file_get_contents($url);
  }

  $content = get_inner_string($html, 'centerDivVideo', '</div>');

  $imgs = explode('src=', $content);
  $imgs = array_filter_extensions($imgs, ['png', 'jpg', 'jpeg']);
  $imgs = array_map_double_qoutes($imgs);
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
