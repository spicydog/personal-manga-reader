<?php

require_once 'utils.inc';

// Code for test
// download([
//   'name' => 'ft100yq',
//   'chapter' => '1',
//   'page' => 1,
// ]);
// download([
//   'name' => 'ft100yq',
//   'chapter' => '80',
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

  $url = "https://sorcererweekly.com/reader/series/$name/";
  $html = @file_get_contents($url);
  $chapters = explode('href=', $html);
  $chapters = array_filter_contain_neddle($chapters, 'https://sorcererweekly.com/reader/read/');
  $chapters = array_map_double_qoutes($chapters);
  $chapters = array_filter_contain_neddle($chapters, "/$chapter/");
  $chapters = array_values(array_reverse(array_values($chapters)));
  $url = $chapters[0] ?? '';

  $html = @file_get_contents($url);

  $pages = get_inner_string($html, 'tbtitle dropdown_parent dropdown_right mmh', 'span');
  $pages = explode('href=', $pages);
  $pages = array_values(array_filter_contain_neddle($pages, "/page/"));
  $pages = array_map_double_qoutes($pages);

  $job['last_page'] = count($pages);

  $url = $pages[$page-1];
  
  $html = @file_get_contents($url);
  $content = get_inner_string($html, '<img class="open"', '/>');
  $image_url = get_inner_string($content, '"', '"');

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
